<?php

namespace App\Presentation\Http\Controllers\Application;

use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\ApplicationStage;
use App\Application\Services\NotificationService;
use App\Application\Services\StageRules;
use App\Application\Services\StageService;
use Illuminate\Http\Request;
use App\Application\Services\AuditService;
use Illuminate\Support\Facades\DB;

class DocumentStatusController
{
    public function update(Request $request, Application $application, ApplicationDocument $document, AuditService $audit, NotificationService $notifications, StageRules $rules, StageService $stageService)
    {
        $user = $request->user();
        $enabled = \App\Models\Setting::query()->where('key','applications.enabled_for_staff')->value('value');
        $enabled = is_array($enabled) ? ($enabled['value'] ?? true) : ($enabled ?? true);
        if (!$enabled) abort(403);
        if ($user->role !== 'staff' && $user->role !== 'super_admin') abort(403);
        
        // CRITICAL: Never allow document approval if payment is not approved (except for payment receipt itself)
        // Payment receipt documents can be reviewed, but stage documents cannot be approved until payment is approved
        $isPaymentReceipt = $document->doc_type === 'payment_receipt' || str_contains($document->doc_type, 'payment');
        if (!$isPaymentReceipt && $application->payment_status !== 'approved') {
            return response()->json([
                'message' => 'Payment must be approved before documents can be approved. Current payment status: ' . ($application->payment_status ?? 'pending')
            ], 422);
        }
        
        if ($user->role === 'super_admin') {
            // Super admin can update all documents (but still need payment approval for non-payment documents)
        } else if ($user->role === 'staff') {
            // Staff can ONLY update documents for applications assigned to them
            // Applications are assigned immediately upon creation, ensuring fair distribution
            if ($application->assigned_staff_id !== $user->id) {
                abort(403);
            }
        }
        if ($document->application_id !== $application->id) abort(404);
        $data = $request->validate([
            'status' => ['required','in:uploaded,under_review,needs_reupload,approved,rejected'],
            'comment' => ['nullable','string']
        ]);
        $oldStatus = $document->status;
        
        return DB::transaction(function () use ($application, $document, $data, $oldStatus, $user, $audit, $notifications, $rules, $stageService) {
            $document->update($data);
            
            if ($oldStatus !== $data['status']) {
                $notifications->notifyDocumentStatusChanged($application, $document->doc_type, $data['status'], $user);
            }
            
            
            $audit->record($user, 'document.status_changed', 'application_document', $document->id, ['status' => $data['status'], 'application_id' => $application->id]);
            
            $application->refresh();
            $application->load('stages');
            
            return response()->json([
                ...$document->fresh()->toArray(),
                'application' => [
                    'id' => $application->id,
                    'stage_index' => $application->stage_index,
                    'status' => $application->status,
                    'stages' => $application->stages->toArray()
                ]
            ]);
        });
    }
}
