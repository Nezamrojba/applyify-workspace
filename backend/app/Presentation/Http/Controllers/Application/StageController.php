<?php

namespace App\Presentation\Http\Controllers\Application;

use App\Application\Services\StageService;
use App\Application\Services\StageRules;
use App\Application\Services\AuditService;
use App\Application\Services\NotificationService;
use App\Models\Application;
use App\Models\ApplicationStage;
use App\Models\ApplicationDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StageController
{
    public function submit(Request $request, Application $application, string $stageKey, StageService $stages, StageRules $rules, AuditService $audit)
    {
        $user = $request->user();
        if ($user->role !== 'student' || $application->student_id !== $user->id) abort(403);
        if (!$rules->hasAllRequired($application->id, $stageKey)) {
            return response()->json(['message' => 'Required documents missing for '.$stageKey], 422);
        }
        $stages->submit($application, $stageKey);
        $audit->record($request->user(), 'stage.submitted', 'application', $application->id, ['stage_key' => $stageKey]);
        return response()->json(['ok' => true]);
    }

    public function approveStage(Request $request, Application $application, string $stageKey, StageService $stages, StageRules $rules, AuditService $audit, NotificationService $notifications)
    {
        $user = $request->user();
        if ($user->role !== 'staff' && $user->role !== 'super_admin') abort(403);
        
        // CRITICAL: Never allow stage approval if payment is not approved
        if ($application->payment_status !== 'approved') {
            return response()->json([
                'message' => 'Payment must be approved before stages can be approved. Current payment status: ' . ($application->payment_status ?? 'pending')
            ], 422);
        }
        
        // Ensure application status is active (payment approved applications should be active)
        if ($application->status !== 'active') {
            return response()->json([
                'message' => 'Application must be active (payment approved) before stages can be approved'
            ], 422);
        }
        
        if ($user->role === 'super_admin') {
            // Super admin can approve all stages (but still need payment approval)
        } else if ($user->role === 'staff') {
            // Staff can approve if they are assigned to the application
            $isAssigned = $application->assigned_staff_id === $user->id;
            
            if (!$isAssigned) {
                abort(403);
            }
        }
        
        $stage = ApplicationStage::where('application_id', $application->id)
            ->where('stage_key', $stageKey)
            ->firstOrFail();
        
        if ($stage->status !== 'draft' && $stage->status !== 'submitted') {
            return response()->json(['message' => 'Stage must be in draft or submitted status before approval'], 422);
        }

        $requiredDocs = $rules->requiredDocs($stageKey);
        $approvedDocs = ApplicationDocument::where('application_id', $application->id)
            ->where('stage_key', $stageKey)
            ->whereIn('doc_type', $requiredDocs)
            ->where('status', 'approved')
            ->pluck('doc_type')
            ->all();

        foreach ($requiredDocs as $docType) {
            if (!in_array($docType, $approvedDocs, true)) {
                return response()->json(['message' => 'All required documents must be approved before approving the stage'], 422);
            }
        }

        return DB::transaction(function () use ($application, $stageKey, $stages, $audit, $request, $notifications, $user) {
            $stages->approveStage($application, $stageKey);
            $notifications->notifyStageSubmitted($application, $stageKey, $user);
            $audit->record($user, 'stage.approved', 'application', $application->id, ['stage_key' => $stageKey]);

            $application->load('stages');
            return response()->json([
                'ok' => true,
                'application' => [
                    'id' => $application->id,
                    'stages' => $application->stages->toArray()
                ]
            ]);
        });
    }
}
