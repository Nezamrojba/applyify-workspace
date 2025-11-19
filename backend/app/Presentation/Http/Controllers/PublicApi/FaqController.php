<?php

namespace App\Presentation\Http\Controllers\PublicApi;

use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController
{
    // Public: List active FAQs (for display to students/public)
    public function index()
    {
        return Faq::where('is_active', true)
            ->orderBy('order')
            ->orderBy('id')
            ->get(['id', 'i18n', 'order']);
    }
}

