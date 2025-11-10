<?php

namespace App\Presentation\Http\Controllers\Staff;

use App\Application\Services\PointsService;
use App\Models\StaffPoint;
use Illuminate\Http\Request;

class CommissionController
{
    public function summary(Request $request, PointsService $points)
    {
        $user = $request->user();
        $q = StaffPoint::where('staff_id', $user->id);
        $total = (int) $q->sum('points');
        $released = (int) $q->where('released', true)->sum('points');
        $unreleased = (int) StaffPoint::where('staff_id', $user->id)->where('released', false)->sum('points');
        $rate = $points->rate();
        return [
            'points_total' => $total,
            'points_released' => $released,
            'points_unreleased' => $unreleased,
            'amount_total' => $total * $rate,
            'amount_unreleased' => $unreleased * $rate,
        ];
    }
}

