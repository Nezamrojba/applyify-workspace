<?php

namespace App\Jobs;

use App\Application\Services\AssignmentService;
use App\Models\Application;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AssignStaffJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [10, 30, 60]; // Retry after 10s, 30s, 60s

    public function __construct(public int $applicationId) {}

    public function handle(AssignmentService $service): void
    {
        $app = Application::find($this->applicationId);
        
        if (!$app) {
            Log::warning('Application not found for staff assignment', [
                'application_id' => $this->applicationId
            ]);
            return;
        }
        
        // Don't assign if already assigned
        if ($app->assigned_staff_id) {
            Log::info('Application already assigned to staff', [
                'application_id' => $this->applicationId,
                'assigned_staff_id' => $app->assigned_staff_id
            ]);
            return;
        }
        
        // Only assign if payment is approved and status is active
        if ($app->payment_status !== 'approved' || $app->status !== 'active') {
            Log::warning('Application not ready for staff assignment', [
                'application_id' => $this->applicationId,
                'payment_status' => $app->payment_status,
                'status' => $app->status
            ]);
            return;
        }
        
        // Get max active assignments setting
        $settingValue = Setting::query()->where('key','staff.max_active_assignments')->value('value');
        $max = 5; // default
        if ($settingValue !== null) {
            if (is_array($settingValue)) {
                $max = (int) ($settingValue['value'] ?? $settingValue ?? 5);
            } else {
                $max = (int) $settingValue;
            }
        }
        
        // Assign staff
        $staffId = $service->assignRandomStaff($app, $max);
        
        if (!$staffId) {
            Log::error('Failed to assign staff to application', [
                'application_id' => $this->applicationId,
                'max_active' => $max
            ]);
            // Don't throw exception, just log - we'll retry
            throw new \Exception('Failed to assign staff to application');
        }
    }
    
    public function failed(\Throwable $exception): void
    {
        Log::error('AssignStaffJob failed after all retries', [
            'application_id' => $this->applicationId,
            'error' => $exception->getMessage(),
        ]);
    }
}

