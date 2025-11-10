<?php

namespace App\Presentation\Http\Controllers\Application;

use App\Models\Application;
use App\Models\Message;
use Illuminate\Http\Request;

class MessageReadController
{
    public function mark(Request $request, Application $application, Message $message)
    {
        $user = $request->user();
        if ($message->application_id !== $application->id) abort(404);
        $isParticipant = ($user->id === $message->to_user_id) || ($user->id === $message->from_user_id);
        if (!$isParticipant) abort(403);
        if ($user->id !== $message->to_user_id) return response()->json(['ok' => true]);
        $message->update(['read' => true]);
        return response()->json(['ok' => true]);
    }
}

