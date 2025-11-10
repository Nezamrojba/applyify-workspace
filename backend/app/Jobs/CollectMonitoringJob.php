<?php

namespace App\Jobs;

use App\Models\Application;
use App\Models\StaffAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CollectMonitoringJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $active = Application::where('soft_deleted', false)->count();
        $avgAssign = StaffAssignment::join('applications', 'staff_assignments.application_id', '=', 'applications.id')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(SECOND, applications.created_at, staff_assignments.assigned_at)) as avg_s'))
            ->value('avg_s');
        Log::info('Monitoring', ['applications_active' => $active, 'avg_assignment_seconds' => (int) $avgAssign]);
    }
}

