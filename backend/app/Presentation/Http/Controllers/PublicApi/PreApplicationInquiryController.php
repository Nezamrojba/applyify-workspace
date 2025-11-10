<?php

namespace App\Presentation\Http\Controllers\PublicApi;

use App\Models\PreApplicationInquiry;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PreApplicationInquiryController
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:32'],
            'question' => ['required', 'string', 'min:10'],
        ]);

        $staff = User::query()
            ->where('role', 'staff')
            ->where('is_active', true)
            ->inRandomOrder()
            ->first();

        $inquiry = PreApplicationInquiry::create([
            ...$data,
            'staff_id' => $staff?->id,
        ]);

        return response()->json([
            'message' => __('Thanks for reaching out! A staff member will contact you shortly.'),
            'inquiry_id' => $inquiry->id,
            'assigned_staff' => $staff ? [
                'id' => $staff->id,
                'name' => $staff->name,
                'email' => $staff->email,
                'whatsapp' => $staff->whatsapp,
            ] : null,
        ], 201);
    }
}

