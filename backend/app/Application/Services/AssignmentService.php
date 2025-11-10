<?php

namespace App\Application\Services;

use App\Models\Application;
use App\Models\StaffAssignment;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssignmentService
{
    /**
     * Assigns a staff member to an application using a fair, load-balanced algorithm.
     *
     * This method ensures fairness by:
     * 1. Only considering active staff members
     * 2. Counting actual active applications (not soft-deleted, not completed)
     * 3. Finding staff with the lowest current load
     * 4. If multiple staff have the same lowest load, randomly selecting among them
     * 5. Respecting the maximum active assignments limit
     *
     * @param Application $application The application to assign
     * @param int $maxActive Maximum number of active assignments per staff
     * @return int|null The assigned staff ID, or null if no staff available
     */
    public function assignRandomStaff(Application $application, int $maxActive): ?int
    {
        // Get all active staff members
        $activeStaff = User::query()
            ->where('role', 'staff')
            ->where('is_active', true)
            ->get();

        if ($activeStaff->isEmpty()) {
            Log::warning('No active staff members available for assignment', [
                'application_id' => $application->id
            ]);
            return null;
        }

        // Calculate current load for each staff member
        // Load = count of active applications (not soft-deleted, not completed) assigned to them
        $staffLoads = [];
        foreach ($activeStaff as $staff) {
            $load = Application::where('assigned_staff_id', $staff->id)
                ->where('soft_deleted', false)
                ->where('status', '!=', 'completed')
                ->count();

            // Only consider staff who haven't reached their maximum capacity
            if ($load < $maxActive) {
                $staffLoads[] = [
                    'id' => $staff->id,
                    'load' => $load,
                    'name' => $staff->name, // For logging
                ];
            } else {
                Log::debug('Staff at maximum capacity', [
                    'staff_id' => $staff->id,
                    'staff_name' => $staff->name,
                    'current_load' => $load,
                    'max_active' => $maxActive,
                ]);
            }
        }

        Log::info('Staff load calculation', [
            'application_id' => $application->id,
            'total_active_staff' => $activeStaff->count(),
            'available_staff_count' => count($staffLoads),
            'staff_loads' => $staffLoads,
            'max_active' => $maxActive,
        ]);

        if (empty($staffLoads)) {
            Log::warning('All active staff members have reached maximum capacity', [
                'application_id' => $application->id,
                'max_active' => $maxActive
            ]);
            return null;
        }

        // Find the minimum load
        $minLoad = min(array_column($staffLoads, 'load'));

        // Get all staff members with the minimum load
        $availableStaff = array_filter($staffLoads, function($staff) use ($minLoad) {
            return $staff['load'] === $minLoad;
        });

        // Reset array keys to ensure proper random selection
        $availableStaff = array_values($availableStaff);

        if (empty($availableStaff)) {
            Log::error('No available staff after filtering by minimum load', [
                'application_id' => $application->id,
                'min_load' => $minLoad,
                'total_staff_loads' => count($staffLoads)
            ]);
            return null;
        }

        // Randomly select one from the staff with minimum load
        // Use mt_rand for better randomness (cryptographically secure random is overkill here)
        // array_rand is also fine, but mt_rand gives us more control
        $randomIndex = mt_rand(0, count($availableStaff) - 1);
        $selectedStaff = $availableStaff[$randomIndex];
        $staffId = $selectedStaff['id'];

        // Log selection details for audit and fairness verification
        Log::info('Staff selection for assignment', [
            'application_id' => $application->id,
            'min_load' => $minLoad,
            'available_staff_count' => count($availableStaff),
            'available_staff' => array_map(function($s) {
                return ['id' => $s['id'], 'name' => $s['name'] ?? 'Unknown', 'load' => $s['load']];
            }, $availableStaff),
            'selected_staff_id' => $staffId,
            'selected_staff_name' => $selectedStaff['name'] ?? 'Unknown',
            'selected_staff_load' => $selectedStaff['load'],
            'random_index' => $randomIndex,
            'selection_method' => 'mt_rand_from_minimum_load_staff',
        ]);

        // Assign the application
        // Note: This method is called from within a transaction with lockForUpdate already applied
        // The application passed here should already be locked
        try {
            // Double-check that application isn't already assigned (race condition check)
            if ($application->assigned_staff_id) {
                Log::info('Application already assigned to staff', [
                    'application_id' => $application->id,
                    'existing_staff_id' => $application->assigned_staff_id,
                    'attempted_staff_id' => $staffId,
                ]);
                return $application->assigned_staff_id;
            }

            // Update the application with staff assignment
            $application->assigned_staff_id = $staffId;
            $application->save();

            // Create or update staff assignment record
            StaffAssignment::updateOrCreate(
                [
                    'staff_id' => $staffId,
                    'application_id' => $application->id,
                ],
                [
                    'status' => 'active',
                    'assigned_at' => Carbon::now(),
                ]
            );

            Log::info('Application assigned to staff successfully', [
                'application_id' => $application->id,
                'staff_id' => $staffId,
                'staff_name' => $selectedStaff['name'] ?? 'Unknown',
                'staff_load_before' => $selectedStaff['load'],
                'staff_load_after' => $selectedStaff['load'] + 1,
                'min_load' => $minLoad,
                'total_available_staff_at_min_load' => count($availableStaff),
                'selection_method' => 'random_from_minimum_load',
                'fairness' => 'load_balanced_with_random_tiebreaker',
            ]);

            return $staffId;
        } catch (\Exception $e) {
            Log::error('Failed to assign application to staff', [
                'application_id' => $application->id,
                'staff_id' => $staffId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }
}

