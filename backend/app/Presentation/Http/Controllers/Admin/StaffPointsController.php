<?php

namespace App\Presentation\Http\Controllers\Admin;

use App\Application\Services\PointsService;
use App\Models\StaffPoint;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class StaffPointsController
{
    public function index(Request $request)
    {
        $q = StaffPoint::query()->with([
            'staff:id,name,email,whatsapp',
            'application:id,course_id,student_id'
        ]);
        if ($sid = $request->query('staff_id')) $q->where('staff_id', $sid);
        if ($aid = $request->query('application_id')) $q->where('application_id', $aid);
        if (!is_null($request->query('released'))) $q->where('released', filter_var($request->query('released'), FILTER_VALIDATE_BOOLEAN));
        if ($from = $request->query('from')) {
            $q->whereDate('credited_at', '>=', Carbon::parse($from)->startOfDay());
        }
        if ($to = $request->query('to')) {
            $q->whereDate('credited_at', '<=', Carbon::parse($to)->endOfDay());
        }
        $perPage = (int) $request->query('per_page', 20);
        $perPage = max(1, min(100, $perPage));
        return $q->orderByDesc('credited_at')->paginate($perPage);
    }

    public function release(Request $request, int $point)
    {
        $record = StaffPoint::find($point);
        if (!$record) return response()->json(['message' => 'Point record not found'], 404);
        if ($record->released) return response()->json($record);
        $record->update(['released' => true, 'released_at' => Carbon::now()]);
        return response()->json($record);
    }

    public function summary(Request $request, PointsService $points)
    {
        $query = StaffPoint::query();
        if ($from = $request->query('from')) {
            $query->whereDate('credited_at', '>=', Carbon::parse($from)->startOfDay());
        }
        if ($to = $request->query('to')) {
            $query->whereDate('credited_at', '<=', Carbon::parse($to)->endOfDay());
        }
        if (!is_null($request->query('released'))) {
            $query->where('released', filter_var($request->query('released'), FILTER_VALIDATE_BOOLEAN));
        }

        $rate = $points->rate();

        $rows = (clone $query)
            ->join('users', 'users.id', '=', 'staff_points.staff_id')
            ->select([
                'staff_points.staff_id',
                DB::raw('users.name as staff_name'),
                DB::raw('users.email as staff_email'),
                DB::raw('users.whatsapp as staff_whatsapp'),
                DB::raw('COUNT(*) as records_count'),
                DB::raw('SUM(staff_points.points) as total_points'),
                DB::raw('SUM(CASE WHEN staff_points.released = 1 THEN staff_points.points ELSE 0 END) as released_points'),
                DB::raw('SUM(CASE WHEN staff_points.released = 0 THEN staff_points.points ELSE 0 END) as pending_points'),
                DB::raw('MAX(staff_points.credited_at) as last_credited_at')
            ])
            ->groupBy('staff_points.staff_id', 'users.name', 'users.email', 'users.whatsapp')
            ->orderByDesc(DB::raw('SUM(CASE WHEN staff_points.released = 0 THEN staff_points.points ELSE 0 END)'))
            ->get();

        $data = $rows->map(function ($row) use ($rate) {
            $totalPoints = (int) ($row->total_points ?? 0);
            $releasedPoints = (int) ($row->released_points ?? 0);
            $pendingPoints = (int) ($row->pending_points ?? 0);

            return [
                'staff_id' => (int) $row->staff_id,
                'name' => $row->staff_name,
                'email' => $row->staff_email,
                'whatsapp' => $row->staff_whatsapp,
                'records_count' => (int) $row->records_count,
                'total_points' => $totalPoints,
                'released_points' => $releasedPoints,
                'pending_points' => $pendingPoints,
                'total_amount' => $totalPoints * $rate,
                'released_amount' => $releasedPoints * $rate,
                'pending_amount' => $pendingPoints * $rate,
                'last_credited_at' => $row->last_credited_at,
            ];
        });

        $totals = [
            'total_points' => $data->sum('total_points'),
            'released_points' => $data->sum('released_points'),
            'pending_points' => $data->sum('pending_points'),
            'total_amount' => $data->sum('total_amount'),
            'released_amount' => $data->sum('released_amount'),
            'pending_amount' => $data->sum('pending_amount'),
            'staff_count' => $data->count(),
        ];

        return [
            'rate' => $rate,
            'totals' => $totals,
            'data' => $data,
        ];
    }
}
