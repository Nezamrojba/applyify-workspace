<?php

namespace App\Presentation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([]);
        }
        return $user->notifications()->orderBy('created_at', 'desc')->paginate(20);
    }
    
    public function unread(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([]);
            }
            return $user->unreadNotifications()->orderBy('created_at', 'desc')->get();
        } catch (\Exception $e) {
            Log::error('Notification unread error', ['error' => $e->getMessage()]);
            return response()->json([]);
        }
    }
    
    public function markAsRead(Request $request, string $id)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['ok' => false, 'message' => 'Unauthenticated'], 401);
            }
            $notification = $user->notifications()->find($id);
            if ($notification) {
                $notification->markAsRead();
            }
            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            Log::error('Notification markAsRead error', ['error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'Failed to mark as read'], 500);
        }
    }
    
    public function markAllAsRead(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['ok' => false, 'message' => 'Unauthenticated'], 401);
            }
            $user->unreadNotifications->markAsRead();
            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            Log::error('Notification markAllAsRead error', ['error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'Failed to mark all as read'], 500);
        }
    }
    
    public function count(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['count' => 0]);
            }
            return response()->json(['count' => $user->unreadNotifications()->count()]);
        } catch (\Exception $e) {
            Log::error('Notification count error', ['error' => $e->getMessage()]);
            return response()->json(['count' => 0]);
        }
    }
}
