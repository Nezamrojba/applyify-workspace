<?php

namespace App\Presentation\Http\Controllers\Application;

use App\Models\Application;
use App\Models\ApplicationDocument;
use Illuminate\Http\Request;

class DocumentController
{
    public function index(Request $request, Application $application)
    {
        $this->authorizeApp($request, $application);
        return ApplicationDocument::where('application_id', $application->id)->orderByDesc('created_at')->get();
    }

    public function store(Request $request, Application $application)
    {
        $this->authorizeApp($request, $application, requireStaff: false);
        $data = $request->validate([
            'stage_key' => ['required','string'],
            'doc_type' => ['required','string'],
            'file_url' => ['required','string'],
            'file_type' => ['required','in:pdf,png,jpg,jpeg'],
            'size_bytes' => ['nullable','integer','min:1','max:10485760'],
        ]);
        $doc = ApplicationDocument::create([
            'application_id' => $application->id,
            'stage_key' => $data['stage_key'],
            'doc_type' => $data['doc_type'],
            'file_url' => $data['file_url'],
            'file_type' => $data['file_type'] === 'jpeg' ? 'jpg' : $data['file_type'],
            'size_bytes' => $data['size_bytes'] ?? null,
            'uploaded_by_user_id' => $request->user()->id,
            'status' => 'uploaded',
        ]);
        return response()->json($doc, 201);
    }

    protected function authorizeApp(Request $request, Application $application, bool $requireStaff = false): void
    {
        $user = $request->user();
        if ($requireStaff && $user->role !== 'staff' && $user->role !== 'super_admin') abort(403);
        if ($user->role === 'student' && $application->student_id !== $user->id) abort(403);
        if ($user->role === 'super_admin') return; // Super admin can access all
        if ($user->role === 'staff') {
            // Staff can ONLY access documents for applications assigned to them
            // Applications are assigned immediately upon creation, ensuring fair distribution
            if ($application->assigned_staff_id !== $user->id) {
                abort(403);
            }
        }
    }
}
