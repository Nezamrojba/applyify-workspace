<?php

namespace App\Presentation\Http\Controllers\Student;

use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $totalApplications = Application::where('student_id', $user->id)
            ->where('soft_deleted', false)
            ->count();
        
        $applicationsByStatus = Application::where('student_id', $user->id)
            ->where('soft_deleted', false)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        
        $applicationIds = Application::where('student_id', $user->id)
            ->where('soft_deleted', false)
            ->pluck('id');
        
        $pendingDocuments = ApplicationDocument::whereIn('application_id', $applicationIds)
            ->whereIn('status', ['needs_reupload', 'rejected'])
            ->count();
        
        $uploadedDocuments = ApplicationDocument::whereIn('application_id', $applicationIds)
            ->where('status', 'uploaded')
            ->count();
        
        $unreadMessages = Message::where('to_user_id', $user->id)
            ->where('read', false)
            ->count();
        
        $recentApplications = Application::where('student_id', $user->id)
            ->where('soft_deleted', false)
            ->with(['university', 'course', 'staff', 'stages'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
        
        $activeApplications = Application::where('student_id', $user->id)
            ->where('soft_deleted', false)
            ->whereIn('status', ['draft', 'active', 'arriving'])
            ->count();
        
        return [
            'total_applications' => $totalApplications,
            'active_applications' => $activeApplications,
            'applications_by_status' => $applicationsByStatus,
            'pending_documents' => $pendingDocuments,
            'uploaded_documents' => $uploadedDocuments,
            'unread_messages' => $unreadMessages,
            'recent_applications' => $recentApplications,
        ];
    }
}

