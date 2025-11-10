<?php

namespace App\Presentation\Http\Controllers\Application;

use App\Application\Services\StageService;
use App\Application\Services\PointsService;
use App\Application\Services\AuditService;
use App\Application\Services\StageRules;
use App\Application\Services\NotificationService;
use App\Jobs\NotifyLetterIssuedJob;
use App\Models\Application;
use App\Models\Letter;
use App\Models\ApplicationDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LetterController
{
    public function store(Request $request, Application $application, StageService $stages, StageRules $rules, PointsService $points, AuditService $audit, NotificationService $notifications)
    {
        $user = $request->user();
        $enabled = \App\Models\Setting::query()->where('key','applications.enabled_for_staff')->value('value');
        $enabled = is_array($enabled) ? ($enabled['value'] ?? true) : ($enabled ?? true);
        if (!$enabled) abort(403);
        if ($user->role !== 'staff' && $user->role !== 'super_admin') abort(403);
        if ($user->role === 'super_admin') {
            // Super admin can issue letters for all applications
        } else if ($user->role === 'staff') {
            // Staff can ONLY issue letters for applications assigned to them
            // Applications are assigned immediately upon creation, ensuring fair distribution
            if ($application->assigned_staff_id !== $user->id) {
                abort(403);
            }
        }
        $data = $request->validate([
            'type' => ['required','string'],
            'file_url' => ['required','string'],
        ]);
        
        // Normalize letter type to uppercase
        $data['type'] = strtoupper($data['type']);
        if (!in_array($data['type'], ['MOL', 'EVAL', 'VISA'], true)) {
            return response()->json(['message' => 'Invalid letter type. Must be MOL, EVAL, or VISA'], 422);
        }
        
        $currentStageIndex = $application->stage_index;
        $currentStageKey = StageService::STAGES[$currentStageIndex - 1] ?? null;
        
        if (!$currentStageKey) {
            return response()->json(['message' => 'Invalid stage'], 422);
        }
        
        $stage = \App\Models\ApplicationStage::where('application_id', $application->id)
            ->where('stage_key', $currentStageKey)
            ->first();
            
        if (!$stage || ($stage->status !== 'submitted' && $stage->status !== 'approved')) {
            return response()->json(['message' => 'Stage must be submitted and approved before issuing letter'], 422);
        }
        
        $requiredDocs = $rules->requiredDocs($currentStageKey);
        $approvedDocs = ApplicationDocument::where('application_id', $application->id)
            ->where('stage_key', $currentStageKey)
            ->where('status', 'approved')
            ->pluck('doc_type')
            ->all();
            
        foreach ($requiredDocs as $docType) {
            if (!in_array($docType, $approvedDocs, true)) {
                return response()->json(['message' => 'All required documents must be approved before issuing letter'], 422);
            }
        }
        
           // Application should already be assigned when created
           // This should never happen, but log if it does
           if ($user->role === 'staff' && !$application->assigned_staff_id) {
               \Log::warning('Letter issued for unassigned application - should not happen', [
                   'application_id' => $application->id,
                   'staff_id' => $user->id,
               ]);
           }
        
        Letter::create([
            'application_id' => $application->id,
            'type' => $data['type'],
            'file_url' => $data['file_url'],
            'issued_by_staff_id' => $user->id,
            'issued_at' => Carbon::now(),
        ]);
        $stages->issueLetterAndAdvance($application, $data['type']);
        $points->creditForLetter($application, $data['type']);
        $notifications->notifyLetterIssued($application, $data['type'], $user);
        $audit->record($request->user(), 'letter.issued', 'application', $application->id, ['type' => $data['type']]);
        NotifyLetterIssuedJob::dispatch($application->id, $data['type']);
        
        $application->load(['stages', 'letters']);
        return response()->json([
            'ok' => true,
            'application' => [
                'id' => $application->id,
                'stage_index' => $application->stage_index,
                'status' => $application->status,
                'stages' => $application->stages->toArray(),
                'letters' => $application->letters->toArray()
            ]
        ]);
    }
}
