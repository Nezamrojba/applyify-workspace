<?php

namespace App\Presentation\Http\Controllers\Staff;

use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController
{
    // Staff: List FAQs assigned to them
    public function index(Request $request)
    {
        $staffId = $request->user()->id;
        
        return Faq::where('assigned_staff_id', $staffId)
            ->orderBy('order')
            ->orderBy('id')
            ->get();
    }

    // Staff: Update FAQ (question and answer in both languages)
    public function update(Request $request, Faq $faq)
    {
        // Check if FAQ is assigned to this staff member
        if ($faq->assigned_staff_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

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

        return $faq;
    }

    // Staff: Get single FAQ
    public function show(Request $request, Faq $faq)
    {
        if ($faq->assigned_staff_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $faq;
    }
}

