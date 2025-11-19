<?php

namespace App\Presentation\Http\Controllers\Admin;

use App\Models\Faq;
use App\Models\User;
use Illuminate\Http\Request;

class FaqController
{
    // Super admin: List all FAQs with staff assignment
    public function index()
    {
        return Faq::with('assignedStaff:id,name,email')
            ->orderBy('order')
            ->orderBy('id')
            ->get();
    }

    // Super admin: Assign staff to FAQ
    public function assignStaff(Request $request, Faq $faq)
    {
        $data = $request->validate([
            'staff_id' => ['required', 'integer', 'exists:users,id']
        ]);

        $staff = User::findOrFail($data['staff_id']);
        if ($staff->role !== 'staff') {
            return response()->json(['error' => 'User must be a staff member'], 400);
        }

        $faq->update(['assigned_staff_id' => $data['staff_id']]);
        
        return Faq::with('assignedStaff:id,name,email')->find($faq->id);
    }

    // Super admin: Remove staff assignment
    public function unassignStaff(Faq $faq)
    {
        $faq->update(['assigned_staff_id' => null]);
        return response()->json(['ok' => true]);
    }

    // Super admin: Create new FAQ (without staff assignment)
    public function store(Request $request)
    {
        $data = $request->validate([
            'i18n.question.en' => ['required', 'string'],
            'i18n.question.ar' => ['required', 'string'],
            'i18n.answer.en' => ['required', 'string'],
            'i18n.answer.ar' => ['required', 'string'],
            'order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean']
        ]);

        $faq = Faq::create([
            'i18n' => [
                'question' => [
                    'en' => $data['i18n']['question']['en'],
                    'ar' => $data['i18n']['question']['ar']
                ],
                'answer' => [
                    'en' => $data['i18n']['answer']['en'],
                    'ar' => $data['i18n']['answer']['ar']
                ]
            ],
            'order' => $data['order'] ?? 0,
            'is_active' => $data['is_active'] ?? true
        ]);

        return Faq::with('assignedStaff:id,name,email')->find($faq->id);
    }

    // Super admin: Delete FAQ
    public function destroy(Faq $faq)
    {
        $faq->delete();
        return response()->json(['ok' => true]);
    }

    // Super admin: Update FAQ
    public function update(Request $request, Faq $faq)
    {
        $data = $request->validate([
            'i18n.question.en' => ['required', 'string'],
            'i18n.question.ar' => ['required', 'string'],
            'i18n.answer.en' => ['required', 'string'],
            'i18n.answer.ar' => ['required', 'string'],
            'order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean']
        ]);

        $faq->update([
            'i18n' => [
                'question' => [
                    'en' => $data['i18n']['question']['en'],
                    'ar' => $data['i18n']['question']['ar']
                ],
                'answer' => [
                    'en' => $data['i18n']['answer']['en'],
                    'ar' => $data['i18n']['answer']['ar']
                ]
            ],
            'order' => $data['order'] ?? $faq->order,
            'is_active' => $data['is_active'] ?? $faq->is_active
        ]);

        return Faq::with('assignedStaff:id,name,email')->find($faq->id);
    }
}

