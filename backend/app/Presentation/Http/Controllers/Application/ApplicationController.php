<?php

namespace App\Presentation\Http\Controllers\Application;

use App\Application\Services\AssignmentService;
use App\Application\Services\StageService;
use App\Models\Application;
use App\Models\Setting;
use App\Application\Services\AuditService;
use App\Jobs\AssignStaffJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApplicationController
{
    public function index(Request $request)
    {
        $user = $request->user();
        $q = Application::query()
            ->with(['stages', 'documents', 'university', 'course', 'staff', 'student'])
            ->where('soft_deleted', false);
        if ($user->role === 'student') {
            $q->where('student_id', $user->id);
        }
        if ($user->role === 'super_admin') {
            // Super admin can see all applications
        } else if ($user->role === 'staff') {
            // Staff can ONLY see applications assigned to them
            // Applications are assigned immediately upon creation, ensuring fair distribution
            // No more showing pending payments to all staff - only assigned staff can review
            $q->where('assigned_staff_id', $user->id);
        }
        return $q->orderByDesc('created_at')->paginate(20);
    }

    public function show(Request $request, Application $application)
    {
        $user = $request->user();
        if ($user->role === 'student' && $application->student_id !== $user->id) abort(403);
        if ($user->role === 'super_admin') {
            // Super admin can access all applications
        } else if ($user->role === 'staff') {
            // Staff can ONLY view applications assigned to them
            // Applications are assigned immediately upon creation, ensuring fair distribution
            if ($application->assigned_staff_id !== $user->id) {
                abort(403);
            }
        }
        return $application->load([
            'stages',
            'documents',
            'letters',
            'student',
            'university',
            'course',
            'staff'
        ]);
    }

    public function store(Request $request, AssignmentService $assigner, StageService $stages, AuditService $audit)
    {
        $user = $request->user();
        if ($user->role !== 'student') abort(403);
        $settings = $this->settings();
        if (!($settings['applications.enabled_for_students'] ?? true)) abort(403);
        $data = $request->validate([
            'university_id' => ['required','exists:universities,id'],
            'course_id' => ['required','exists:courses,id'],
            'passport_no' => ['required','string','max:64'],
            'payment_receipt_url' => ['required','string','url','max:500'],
        ]);
        
        // Additional validation: ensure payment_receipt_url is not empty after trimming
        if (empty(trim($data['payment_receipt_url']))) {
            return response()->json(['message' => 'Payment receipt URL is required'], 422);
        }
        $activeCount = Application::where('student_id', $user->id)->where('soft_deleted', false)->count();
        $maxApps = (int)($settings['student.max_active_apps'] ?? 3);
        if ($activeCount >= $maxApps) return response()->json(['message' => 'Max active applications reached'], 422);
        
        // Get max active assignments setting for staff assignment
        $settingValue = \App\Models\Setting::query()->where('key','staff.max_active_assignments')->value('value');
        $maxActive = 5; // default
        if ($settingValue !== null) {
            if (is_array($settingValue)) {
                $maxActive = (int) ($settingValue['value'] ?? $settingValue ?? 5);
            } else {
                $maxActive = (int) $settingValue;
            }
        }
        
        return DB::transaction(function () use ($user, $data, $assigner, $stages, $settings, $audit, $request, $maxActive) {
            // Create the application first
            $app = Application::create([
                'student_id' => $user->id,
                'university_id' => $data['university_id'],
                'course_id' => $data['course_id'],
                'passport_no' => $data['passport_no'],
                'status' => 'draft', // Start as draft until payment is approved
                'payment_status' => 'pending',
                'payment_receipt_url' => $data['payment_receipt_url'],
                'stage_index' => 1,
            ]);
            
            $stages->ensureInitialStages($app);
            
            // CRITICAL: Assign staff IMMEDIATELY upon application creation
            // This ensures fair distribution and only the assigned staff sees the application
            // Lock the application to prevent race conditions
            $lockedApp = Application::where('id', $app->id)
                ->lockForUpdate()
                ->first();
            
            $staffId = $assigner->assignRandomStaff($lockedApp, $maxActive);
            
            if (!$staffId) {
                // If assignment fails, log error but don't fail the application creation
                // The application will remain unassigned (should be rare)
                \Log::error('Failed to assign staff during application creation', [
                    'application_id' => $app->id,
                    'student_id' => $user->id,
                ]);
            }
            
            // Refresh to get the assigned staff
            $app->refresh();
            
            $audit->record($request->user(), 'application.created', 'application', $app->id, [
                'university_id' => $app->university_id,
                'course_id' => $app->course_id,
                'payment_status' => 'pending',
                'assigned_staff_id' => $app->assigned_staff_id,
            ]);
            
            return response()->json($app->load(['stages', 'staff']), 201);
        });
    }

    public function approvePayment(Request $request, Application $application, AuditService $audit)
    {
        $user = $request->user();
        if ($user->role !== 'staff' && $user->role !== 'super_admin') abort(403);
        
        // Only assigned staff (or super admin) can approve payment
        if ($user->role === 'staff' && $application->assigned_staff_id !== $user->id) {
            abort(403);
        }
        
        if ($application->payment_status !== 'pending') {
            return response()->json(['message' => 'Payment already processed'], 422);
        }
        
        // Application should already be assigned when created
        // When payment is approved, we just activate it - staff is already assigned
        if (!$application->assigned_staff_id) {
            // If somehow unassigned, try to assign now (shouldn't happen, but safety check)
            $settingValue = \App\Models\Setting::query()->where('key','staff.max_active_assignments')->value('value');
            $max = 5;
            if ($settingValue !== null) {
                if (is_array($settingValue)) {
                    $max = (int) ($settingValue['value'] ?? $settingValue ?? 5);
                } else {
                    $max = (int) $settingValue;
                }
            }
            $assigner = app(\App\Application\Services\AssignmentService::class);
            $app = Application::where('id', $application->id)->lockForUpdate()->first();
            $staffId = $assigner->assignRandomStaff($app, $max);
            if (!$staffId) {
                return response()->json([
                    'message' => 'Failed to assign staff. Please try again or contact administrator.',
                    'error' => 'no_staff_available'
                ], 422);
            }
            $application->refresh();
        }
        
        return DB::transaction(function () use ($application, $audit, $request) {
            // Update payment status and activate application
            // Staff is already assigned, so only that staff member will see it
            $application->update([
                'payment_status' => 'approved',
                'status' => 'active', // Change from draft to active
            ]);
            
            $application->refresh();
            
            $audit->record($request->user(), 'payment.approved', 'application', $application->id, [
                'payment_status' => 'approved',
                'application_status' => 'active',
                'assigned_staff_id' => $application->assigned_staff_id,
            ]);
            
            return response()->json([
                'ok' => true,
                'application' => $application->load(['stages', 'student', 'university', 'course', 'staff'])
            ]);
        });
    }
    
    public function rejectPayment(Request $request, Application $application, AuditService $audit)
    {
        $user = $request->user();
        if ($user->role !== 'staff' && $user->role !== 'super_admin') abort(403);
        
        // Only assigned staff (or super admin) can reject payment
        if ($user->role === 'staff' && $application->assigned_staff_id !== $user->id) {
            abort(403);
        }
        
        // Allow rejecting both pending and previously rejected payments (if resubmitted)
        if ($application->payment_status !== 'pending' && $application->payment_status !== 'rejected') {
            return response()->json(['message' => 'Payment already processed'], 422);
        }
        
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:500']
        ]);
        
        return DB::transaction(function () use ($application, $data, $audit, $request) {
            $application->update([
                'payment_status' => 'rejected',
                // Keep status as draft - application cannot proceed until payment is approved
                'status' => 'draft',
            ]);
            
            $audit->record($request->user(), 'payment.rejected', 'application', $application->id, [
                'payment_status' => 'rejected',
                'reason' => $data['reason'] ?? null
            ]);
            
            return response()->json([
                'ok' => true,
                'application' => $application->load(['stages', 'student', 'university', 'course'])
            ]);
        });
    }
    
    /**
     * Allow student to resubmit payment receipt after rejection
     */
    public function checkApplicationLimit(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'student') {
            return response()->json(['message' => 'Only students can check application limits'], 403);
        }
        
        $settings = $this->settings();
        $activeCount = Application::where('student_id', $user->id)
            ->where('soft_deleted', false)
            ->count();
        $maxApps = (int)($settings['student.max_active_apps'] ?? 3);
        
        return response()->json([
            'active_count' => $activeCount,
            'max_allowed' => $maxApps,
            'can_create' => $activeCount < $maxApps,
            'limit_reached' => $activeCount >= $maxApps
        ]);
    }
    
    public function updatePaymentReceipt(Request $request, Application $application, AuditService $audit)
    {
        $user = $request->user();
        if ($user->role !== 'student' || $application->student_id !== $user->id) abort(403);
        
        // Only allow updating receipt if payment was rejected or is pending (for resubmission)
        if ($application->payment_status !== 'rejected' && $application->payment_status !== 'pending') {
            return response()->json(['message' => 'Payment receipt can only be updated if payment was rejected or is pending'], 422);
        }
        
        $data = $request->validate([
            'payment_receipt_url' => ['required', 'string', 'url', 'max:500'],
        ]);
        
        return DB::transaction(function () use ($application, $data, $audit, $request) {
            $application->update([
                'payment_receipt_url' => $data['payment_receipt_url'],
                'payment_status' => 'pending', // Reset to pending for staff review
            ]);
            
            $audit->record($request->user(), 'payment.receipt_resubmitted', 'application', $application->id, [
                'payment_status' => 'pending',
                'payment_receipt_url' => $data['payment_receipt_url'],
            ]);
            
            return response()->json([
                'ok' => true,
                'application' => $application->load(['stages', 'student', 'university', 'course'])
            ]);
        });
    }

    public function softDelete(Request $request, Application $application, AuditService $audit)
    {
        $user = $request->user();
        if ($user->role !== 'staff' && $user->role !== 'super_admin') abort(403);
        $application->update(['soft_deleted' => true]);
        $audit->record($request->user(), 'application.soft_deleted', 'application', $application->id);
        return response()->json(['ok' => true]);
    }

    protected function settings(): array
    {
        return Setting::query()->pluck('value', 'key')->map(function ($v) { return is_array($v) ? ($v['value'] ?? null) : $v; })->all();
    }
}
