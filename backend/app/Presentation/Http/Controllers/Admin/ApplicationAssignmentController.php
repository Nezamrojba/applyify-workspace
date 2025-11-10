<?php

namespace App\Presentation\Http\Controllers\Admin;

use App\Application\Services\AssignmentService;
use App\Jobs\AssignStaffJob;
use App\Models\Application;
use App\Models\User;
use Illuminate\Http\Request;

class ApplicationAssignmentController
{
    public function reassign(Request $request, Application $application, AssignmentService $service)
    {
        $staffId = $request->input('staff_id');
        if ($staffId) {
            $staff = User::where('id', $staffId)->where('role','staff')->first();
            if (!$staff) return response()->json(['message' => 'Staff not found'], 404);
            $application->update(['assigned_staff_id' => $staff->id]);
            return response()->json(['ok' => true, 'assigned_staff_id' => $staff->id]);
        }
        AssignStaffJob::dispatch($application->id);
        return response()->json(['ok' => true, 'queued' => true]);
    }
}

