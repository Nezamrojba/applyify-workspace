<?php

namespace App\Presentation\Http\Controllers\Admin;

use App\Models\University;
use App\Presentation\Http\Requests\Admin\UniversityStoreRequest;
use App\Presentation\Http\Requests\Admin\UniversityUpdateRequest;

class UniversityController
{
    public function index()
    {
        return University::query()->with('country')->orderBy('name')->get();
    }

    public function store(UniversityStoreRequest $request)
    {
        $u = University::create($request->validated());
        return response()->json($u, 201);
    }

    public function show(University $university)
    {
        return $university->load('courses');
    }

    public function update(UniversityUpdateRequest $request, University $university)
    {
        $university->update($request->validated());
        return response()->json($university);
    }

    public function destroy(University $university)
    {
        $university->delete();
        return response()->json(['ok' => true]);
    }
}

