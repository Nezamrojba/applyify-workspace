<?php

namespace App\Presentation\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CourseUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'name' => ['sometimes','string','max:255'],
            'code' => ['nullable','string','max:120'],
            'level' => ['nullable','string','max:120'],
            'acceptance_percent' => ['nullable','integer','min:0','max:100'],
            'duration_months' => ['nullable','integer','min:1','max:120'],
            'is_active' => ['sometimes','boolean'],
            'details' => ['nullable','string'],
            'total_tuition_fees' => ['nullable','numeric','min:0'],
            'procedure_fees' => ['nullable','numeric','min:0'],
            'payment_method' => ['nullable','string','max:64'],
            'allow_installments' => ['sometimes','boolean'],
            'total_years' => ['nullable','integer','min:1','max:10'],
            'i18n' => ['sometimes','array'],
            'i18n.name' => ['sometimes','array'],
            'i18n.name.en' => ['required_with:i18n.name','string','max:255'],
            'i18n.name.ar' => ['nullable','string','max:255'],
            'i18n.blurb' => ['sometimes','array'],
            'i18n.blurb.en' => ['nullable','string'],
            'i18n.blurb.ar' => ['nullable','string'],
            'i18n.intakes' => ['sometimes','array'],
            'i18n.overview' => ['sometimes','array'],
            'i18n.overview.en' => ['sometimes','array'],
            'i18n.overview.ar' => ['nullable','array'],
            'i18n.outcomes' => ['sometimes','array'],
            'i18n.outcomes.en' => ['sometimes','array'],
            'i18n.outcomes.ar' => ['nullable','array'],
            'i18n.requirements' => ['sometimes','array'],
            'i18n.requirements.en' => ['sometimes','array'],
            'i18n.requirements.ar' => ['nullable','array'],
            'i18n.careers' => ['sometimes','array'],
            'i18n.careers.en' => ['sometimes','array'],
            'i18n.careers.ar' => ['nullable','array'],
            'i18n.language' => ['sometimes','array'],
            'i18n.language.en' => ['nullable','string','max:255'],
            'i18n.language.ar' => ['nullable','string','max:255'],
            'i18n.accreditation' => ['sometimes','array'],
            'i18n.accreditation.en' => ['nullable','string','max:255'],
            'i18n.accreditation.ar' => ['nullable','string','max:255']
        ];
    }
}
