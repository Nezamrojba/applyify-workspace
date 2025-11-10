<?php

namespace App\Presentation\Http\Controllers\Application;

use App\Models\Application;
use App\Models\ApplicationStage;
use App\Application\Services\AuditService;
use App\Application\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinalizeController
{
    public function finalize(Request $request, Application $application, AuditService $audit, NotificationService $notifications)
    {
        $user = $request->user();
        $enabled = \App\Models\Setting::query()->where('key','applications.enabled_for_staff')->value('value');
        $enabled = is_array($enabled) ? ($enabled['value'] ?? true) : ($enabled ?? true);
        if (!$enabled) abort(403);
        if ($user->role !== 'staff' && $user->role !== 'super_admin') abort(403);
        if ($user->role === 'staff' && $application->assigned_staff_id !== $user->id) abort(403);
        
        $stage4 = ApplicationStage::where('application_id', $application->id)
            ->where('stage_key', 'stage4')
            ->first();
        
        if (!$stage4 || $stage4->status !== 'approved') {
            return response()->json([
                'message' => 'Stage 4 must be approved before finalizing arrival'
            ], 422);
        }
        
        $data = $request->validate([
            'airport_contact_name' => ['required', 'string', 'max:255'],
            'airport_contact_phone' => ['required', 'string', 'max:32'],
            'airport_contact_whatsapp' => ['nullable', 'string', 'max:32'],
            'arrival_date' => ['nullable', 'date'],
            'arrival_flight' => ['nullable', 'string', 'max:255'],
        ]);
        
        return DB::transaction(function () use ($application, $request, $audit, $notifications, $stage4, $data) {
            $application->update(['status' => 'arriving']);
            
            $stage4->update([
                'data' => array_merge($stage4->data ?? [], [
                    'airport_contact_name' => $data['airport_contact_name'],
                    'airport_contact_phone' => $data['airport_contact_phone'],
                    'airport_contact_whatsapp' => $data['airport_contact_whatsapp'] ?? null,
                    'arrival_date' => $data['arrival_date'] ?? null,
                    'arrival_flight' => $data['arrival_flight'] ?? null,
                    'finalized_at' => now()->toISOString(),
                    'finalized_by' => $request->user()->id,
                ])
            ]);
            
            $audit->record($request->user(), 'application.arrival_finalized', 'application', $application->id, $data);
            $notifications->notifyArrivalFinalized($application, $request->user());
            
            return response()->json([
                'ok' => true,
                'application' => $application->fresh()->load(['stages', 'student', 'staff'])
            ]);
        });
    }
}
