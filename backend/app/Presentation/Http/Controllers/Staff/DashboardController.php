<?php

namespace App\Presentation\Http\Controllers\Staff;

use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\Message;
use App\Application\Services\PointsService;
use App\Models\StaffPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController
{
    public function index(Request $request, PointsService $points)
    {
        $user = $request->user();
        
        $totalApplications = Application::where('assigned_staff_id', $user->id)
            ->where('soft_deleted', false)
            ->count();
        
        $applicationsByStatus = Application::where('assigned_staff_id', $user->id)
            ->where('soft_deleted', false)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        
        $applicationIds = Application::where('assigned_staff_id', $user->id)
            ->where('soft_deleted', false)
            ->pluck('id');
        
        $pendingReviews = ApplicationDocument::whereIn('application_id', $applicationIds)
            ->whereIn('status', ['uploaded', 'needs_reupload'])
            ->count();
        
        $unreadMessages = Message::where('to_user_id', $user->id)
            ->where('read', false)
            ->count();
        
        $recentApplications = Application::where('assigned_staff_id', $user->id)
            ->where('soft_deleted', false)
            ->with(['student', 'university', 'course', 'stages'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
        
        $pointsQuery = StaffPoint::where('staff_id', $user->id);
        $totalPoints = (int) $pointsQuery->sum('points');
        $releasedPoints = (int) $pointsQuery->where('released', true)->sum('points');
        $unreleasedPoints = (int) $pointsQuery->where('released', false)->sum('points');
        $rate = $points->rate();
        
        return [
            'total_applications' => $totalApplications,
            'applications_by_status' => $applicationsByStatus,
            'pending_reviews' => $pendingReviews,
            'unread_messages' => $unreadMessages,
            'recent_applications' => $recentApplications,
            'commission' => [
                'points_total' => $totalPoints,
                'points_released' => $releasedPoints,
                'points_unreleased' => $unreleasedPoints,
                'amount_total' => $totalPoints * $rate,
                'amount_unreleased' => $unreleasedPoints * $rate,
            ],
        ];
    }
}

