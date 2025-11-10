<?php

namespace App\Presentation\Http\Controllers\Application;

use App\Models\Application;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController
{
    public function store(Request $request, Application $application)
    {
        $user = $request->user();
        if ($user->role !== 'student' || $application->student_id !== $user->id) abort(403);
        $data = $request->validate([
            'type' => ['required','string'],
            'amount' => ['required','numeric','min:0'],
            'currency' => ['sometimes','string','max:8'],
            'receipt_url' => ['nullable','string']
        ]);
        $p = Payment::create([
            'application_id' => $application->id,
            'student_id' => $user->id,
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'MYR',
            'type' => $data['type'],
            'receipt_url' => $data['receipt_url'] ?? null,
            'status' => 'recorded',
        ]);
        return response()->json($p, 201);
    }
}

