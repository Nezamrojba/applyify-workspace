<?php

namespace App\Presentation\Http\Controllers\PublicApi;

use App\Models\University;
use Illuminate\Database\Eloquent\Builder;

class UniversityController
{
    public function index()
    {
        $course = request('course');
        $min = request('min_accept');
        $max = request('max_accept');
        $q = University::query()->where('is_active', true);
        if ($course) {
            $q->whereHas('courses', function (Builder $b) use ($course) {
                $b->where('is_active', true)->where('name', 'like', '%'.$course.'%');
            });
        }
        if ($min !== null || $max !== null) {
            $q->whereHas('courses', function (Builder $b) use ($min, $max) {
                $b->where('is_active', true);
                if ($min !== null) $b->where('acceptance_percent', '>=', (int)$min);
                if ($max !== null) $b->where('acceptance_percent', '<=', (int)$max);
            });
        }
        return $q->orderBy('name')->paginate(20);
    }

    public function show(University $university)
    {
        if (!$university->is_active) return response()->json(['message' => 'Not Found'], 404);
        return $university->load(['courses' => fn($q) => $q->where('is_active', true)]);
    }
}
