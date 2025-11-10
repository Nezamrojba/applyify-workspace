<?php

namespace App\Presentation\Http\Controllers\Application;

use App\Models\Application;
use App\Models\Message;
use App\Application\Services\NotificationService;
use Illuminate\Http\Request;

class MessageController
{
    public function index(Request $request, Application $application)
    {
        $this->authorizeApp($request, $application);
        return Message::where('application_id', $application->id)
            ->with(['fromUser', 'toUser'])
            ->orderBy('created_at')
            ->get();
    }

    public function store(Request $request, Application $application, NotificationService $notifications)
    {
        $this->authorizeApp($request, $application);
        $data = $request->validate([
            'body' => ['required','string'],
            'attachments' => ['nullable','array']
        ]);
        $to = $request->user()->role === 'student' ? $application->assigned_staff_id : $application->student_id;
        if (!$to) {
            return response()->json(['message' => 'No recipient found for this message'], 422);
        }
        $msg = Message::create([
            'application_id' => $application->id,
            'from_user_id' => $request->user()->id,
            'to_user_id' => $to,
            'body' => $data['body'],
            'attachments' => $data['attachments'] ?? null,
            'read' => false,
        ]);
        $notifications->notifyMessageReceived($application, $request->user());
        return response()->json($msg->load(['fromUser', 'toUser']), 201);
    }

    public function update(Request $request, Application $application, Message $message)
    {
        $this->authorizeApp($request, $application);
        if ($message->application_id !== $application->id) abort(404);
        if ($message->from_user_id !== $request->user()->id) {
            return response()->json(['message' => 'You can only edit your own messages'], 403);
        }
        
        $data = $request->validate([
            'body' => ['required','string'],
        ]);
        
        $message->update([
            'body' => $data['body'],
            'edited_at' => now(),
        ]);
        
        return response()->json($message->fresh()->load(['fromUser', 'toUser']));
    }
    
    public function destroy(Request $request, Application $application, Message $message)
    {
        $this->authorizeApp($request, $application);
        if ($message->application_id !== $application->id) abort(404);
        if ($message->from_user_id !== $request->user()->id) {
            return response()->json(['message' => 'You can only delete your own messages'], 403);
        }
        
        $message->delete();
        
        return response()->json(['ok' => true]);
    }

    protected function authorizeApp(Request $request, Application $application): void
    {
        $user = $request->user();
        if ($user->role === 'student' && $application->student_id !== $user->id) abort(403);
        if ($user->role === 'super_admin') return; // Super admin can access all
        if ($user->role === 'staff') {
            // Staff can ONLY access messages for applications assigned to them
            // Applications are assigned immediately upon creation, ensuring fair distribution
            if ($application->assigned_staff_id !== $user->id) {
                abort(403);
            }
        }
    }
}

