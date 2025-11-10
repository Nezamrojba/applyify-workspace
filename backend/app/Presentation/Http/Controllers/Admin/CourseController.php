<?php

namespace App\Presentation\Http\Controllers\Admin;

use App\Models\Course;
use App\Presentation\Http\Requests\Admin\CourseStoreRequest;
use App\Presentation\Http\Requests\Admin\CourseUpdateRequest;

class CourseController
{
    public function index()
    {
        $q = Course::query()->with('university');
        if ($uid = request('university_id')) $q->where('university_id', $uid);
        if ($min = request('min_accept')) $q->where('acceptance_percent', '>=', (int)$min);
        if ($max = request('max_accept')) $q->where('acceptance_percent', '<=', (int)$max);
        return $q->orderBy('name')->get();
    }

    public function show(Course $course)
    {
        return $course->load(['university', 'activeFeeStructure']);
    }

    public function store(CourseStoreRequest $request)
    {
        $c = Course::create($request->validated());
        return response()->json($c, 201);
    }

    public function update(CourseUpdateRequest $request, Course $course)
    {
        $course->update($request->validated());
        return response()->json($course);
    }

    public function destroy(Course $course)
    {
        $course->delete();
        return response()->json(['ok' => true]);
    }
}

