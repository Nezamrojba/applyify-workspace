<?php

namespace App\Presentation\Http\Controllers\Admin;

use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MetricsController
{
    public function index()
    {
        $activeApps = Application::where('soft_deleted', false)->count();
        $byStatus = Application::select('status', DB::raw('count(*) as c'))->groupBy('status')->pluck('c','status');
        $usersByRole = User::select('role', DB::raw('count(*) as c'))->groupBy('role')->pluck('c','role');
        $docsCount = ApplicationDocument::count();
        $docsBytes = (int) ApplicationDocument::sum('size_bytes');
        $avgAssign = DB::table('staff_assignments')
            ->join('applications', 'staff_assignments.application_id', '=', 'applications.id')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(SECOND, applications.created_at, staff_assignments.assigned_at)) as avg_s'))
            ->value('avg_s');
        
        $staffMaxLoadResult = DB::table('staff_assignments')
            ->join('applications', 'staff_assignments.application_id', '=', 'applications.id')
            ->where('applications.soft_deleted', false)
            ->where('applications.status', '!=', 'completed')
            ->select('staff_id', DB::raw('count(*) as load_count'))
            ->groupBy('staff_id')
            ->orderByDesc('load_count')
            ->first();
        
        $staffMaxLoad = $staffMaxLoadResult ? (int) $staffMaxLoadResult->load_count : 0;
        
        $storageUsedMB = round($docsBytes / (1024 * 1024), 2);
        
        return [
            'applications_active' => $activeApps,
            'applications_by_status' => $byStatus,
            'users_by_role' => $usersByRole,
            'documents_count' => $docsCount,
            'documents_total_bytes' => $docsBytes,
            'storage_used_mb' => $storageUsedMB,
            'avg_assignment_seconds' => (int) $avgAssign,
            'staff_max_load' => (int) $staffMaxLoad,
        ];
    }
}
