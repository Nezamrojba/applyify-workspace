<?php

namespace App\Presentation\Http\Controllers\PublicApi;

use App\Models\Course;

class CourseController
{
    public function index()
    {
        $q = Course::query()
            ->select(['id', 'name', 'code', 'level', 'duration_months', 'acceptance_percent', 'university_id', 'i18n'])
            ->where('is_active', true)
            ->whereHas('university', fn($u) => $u->where('is_active', true));
        if ($uid = request('university_id')) $q->where('university_id', $uid);
        return $q->orderBy('name')->paginate(20);
    }
    public function show(Course $course)
    {
        $course->load(['university']);

        return $course->makeHidden([
            'total_tuition_fees',
            'procedure_fees',
            'payment_method',
            'allow_installments',
            'total_years',
        ]);
    }
}
