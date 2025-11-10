<?php

namespace App\Application\Services;

use App\Models\Application;
use App\Models\User;
use App\Notifications\ApplicationNotification;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function notifyDocumentUploaded(Application $application, string $docType, User $uploadedBy): void
    {
        if ($uploadedBy->role === 'student') {
            $application->loadMissing('staff');
            $recipient = $application->staff;
            if (!$recipient) {
                Log::warning('NotificationService: No staff assigned to application', ['application_id' => $application->id]);
                return;
            }
            
            $docTypeLabel = str_replace('_', ' ', ucwords($docType, '_'));
            $message = "Student uploaded a new document: {$docTypeLabel}";
            
            try {
                $recipient->notify(new ApplicationNotification([
                    'type' => 'document_uploaded',
                    'application_id' => $application->id,
                    'message' => $message,
                    'doc_type' => $docType,
                    'uploaded_by' => $uploadedBy->name,
                ]));
                Log::info('NotificationService: Document upload notification sent to staff', [
                    'application_id' => $application->id,
                    'staff_id' => $recipient->id,
                    'doc_type' => $docType
                ]);
            } catch (\Exception $e) {
                Log::error('NotificationService: Failed to send document upload notification', [
                    'application_id' => $application->id,
                    'error' => $e->getMessage()
                ]);
            }
        } else {
            $recipient = $application->student;
            if (!$recipient) return;
            
            $docTypeLabel = str_replace('_', ' ', ucwords($docType, '_'));
            $message = "Staff uploaded a new document: {$docTypeLabel}";
            
            $recipient->notify(new ApplicationNotification([
                'type' => 'document_uploaded',
                'application_id' => $application->id,
                'message' => $message,
                'doc_type' => $docType,
                'uploaded_by' => $uploadedBy->name,
            ]));
        }
    }
    
    public function notifyDocumentStatusChanged(Application $application, string $docType, string $status, User $changedBy): void
    {
        $recipient = $changedBy->role === 'staff' 
            ? $application->student 
            : null;
            
        if (!$recipient) return;
        
        $docTypeLabel = str_replace('_', ' ', ucwords($docType, '_'));
        $statusLabel = str_replace('_', ' ', ucwords($status, '_'));
        $message = "Document status changed: {$docTypeLabel} - {$statusLabel}";
        
        $recipient->notify(new ApplicationNotification([
            'type' => 'document_status_changed',
            'application_id' => $application->id,
            'message' => $message,
            'doc_type' => $docType,
            'status' => $status,
            'changed_by' => $changedBy->name,
        ]));
    }
    
    public function notifyStageSubmitted(Application $application, string $stageKey, User $submittedBy): void
    {
        $application->loadMissing('staff');
        $recipient = $application->staff;
        
        if (!$recipient) {
            Log::warning('NotificationService: No staff assigned to application for stage submission', ['application_id' => $application->id]);
            return;
        }
        
        try {
            $recipient->notify(new ApplicationNotification([
                'type' => 'stage_submitted',
                'application_id' => $application->id,
                'message' => "Student submitted stage: {$stageKey}",
                'stage_key' => $stageKey,
                'submitted_by' => $submittedBy->name,
            ]));
            Log::info('NotificationService: Stage submission notification sent to staff', [
                'application_id' => $application->id,
                'staff_id' => $recipient->id,
                'stage_key' => $stageKey
            ]);
        } catch (\Exception $e) {
            Log::error('NotificationService: Failed to send stage submission notification', [
                'application_id' => $application->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    public function notifyLetterIssued(Application $application, string $letterType, User $issuedBy): void
    {
        $recipient = $application->student;
        
        if (!$recipient) return;
        
        $recipient->notify(new ApplicationNotification([
            'type' => 'letter_issued',
            'application_id' => $application->id,
            'message' => "New letter issued: {$letterType}",
            'letter_type' => $letterType,
            'issued_by' => $issuedBy->name,
        ]));
    }
    
    public function notifyMessageReceived(Application $application, User $fromUser): void
    {
        if ($fromUser->role === 'student') {
            $application->loadMissing('staff');
            $recipient = $application->staff;
            if (!$recipient) {
                Log::warning('NotificationService: No staff assigned to application for message', ['application_id' => $application->id]);
                return;
            }
        } else {
            $recipient = $application->student;
            if (!$recipient) return;
        }
        
        try {
            $recipient->notify(new ApplicationNotification([
                'type' => 'message_received',
                'application_id' => $application->id,
                'message' => "New message from {$fromUser->name}",
                'from_user' => $fromUser->name,
            ]));
            Log::info('NotificationService: Message notification sent', [
                'application_id' => $application->id,
                'recipient_id' => $recipient->id,
                'from_user_id' => $fromUser->id
            ]);
        } catch (\Exception $e) {
            Log::error('NotificationService: Failed to send message notification', [
                'application_id' => $application->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    public function notifyApplicationApproved(Application $application, User $approvedBy): void
    {
        $recipient = $application->student;
        
        if (!$recipient) return;
        
        $recipient->notify(new ApplicationNotification([
            'type' => 'application_approved',
            'application_id' => $application->id,
            'message' => 'Your application has been approved',
            'approved_by' => $approvedBy->name,
        ]));
    }
    
    public function notifyArrivalFinalized(Application $application, User $finalizedBy): void
    {
        $recipient = $application->student;
        
        if (!$recipient) return;
        
        try {
            $recipient->notify(new ApplicationNotification([
                'type' => 'arrival_finalized',
                'application_id' => $application->id,
                'message' => 'Your arrival information has been finalized. Check your application for airport pickup details and next steps.',
                'finalized_by' => $finalizedBy->name,
            ]));
            Log::info('NotificationService: Arrival finalized notification sent to student', [
                'application_id' => $application->id,
                'student_id' => $recipient->id,
            ]);
        } catch (\Exception $e) {
            Log::error('NotificationService: Failed to send arrival finalized notification', [
                'application_id' => $application->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}

