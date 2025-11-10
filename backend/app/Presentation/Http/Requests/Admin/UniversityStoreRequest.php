<?php

namespace App\Presentation\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UniversityStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'name' => ['required','string','max:255'],
            'country_id' => ['nullable','exists:countries,id'],
            'is_active' => ['sometimes','boolean'],
            'i18n' => ['sometimes','array'],
            'i18n.name' => ['sometimes','array'],
            'i18n.name.en' => ['required_with:i18n.name','string','max:255'],
            'i18n.name.ar' => ['nullable','string','max:255'],
            'i18n.location' => ['sometimes','array'],
            'i18n.location.en' => ['nullable','string','max:255'],
            'i18n.location.ar' => ['nullable','string','max:255']
        ];
    }
}
