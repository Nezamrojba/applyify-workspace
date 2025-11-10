<?php

namespace App\Presentation\Http\Controllers\Admin;

use App\Models\Course;
use App\Models\CourseFeeStructure;
use Illuminate\Http\Request;

class CourseFeeStructureController
{
    public function index(Course $course)
    {
        return $course->feeStructures()->orderByDesc('created_at')->get();
    }

    public function store(Request $request, Course $course)
    {
        $data = $request->validate([
            'program_code' => ['nullable', 'string', 'max:255'],
            'intake' => ['nullable', 'string', 'max:255'],
            'tuition_fee_per_credit_hour' => ['required', 'numeric', 'min:0'],
            'semesters' => ['nullable', 'array'],
            'semesters.*.semester_type' => ['nullable', 'string'],
            'semesters.*.academic_period' => ['nullable', 'string'],
            'semesters.*.credit_hours' => ['nullable', 'numeric', 'min:0'],
            'semesters.*.tuition_fee' => ['nullable', 'numeric', 'min:0'],
            'semesters.*.library_fee' => ['nullable', 'numeric', 'min:0'],
            'semesters.*.student_club_fee' => ['nullable', 'numeric', 'min:0'],
            'semesters.*.ikad_fee' => ['nullable', 'numeric', 'min:0'],
            'semesters.*.visa_processing_fee' => ['nullable', 'numeric', 'min:0'],
            'semesters.*.medical_insurance_fee' => ['nullable', 'numeric', 'min:0'],
            'semesters.*.medical_examination_fee' => ['nullable', 'numeric', 'min:0'],
            'semesters.*.custom_fees' => ['nullable', 'array'],
            'semesters.*.custom_fees.*.name' => ['nullable', 'string', 'max:255'],
            'semesters.*.custom_fees.*.amount' => ['nullable', 'numeric', 'min:0'],
            'one_time_fees' => ['nullable', 'array'],
            'one_time_fees.*.name' => ['nullable', 'string', 'max:255'],
            'one_time_fees.*.amount' => ['nullable', 'numeric', 'min:0'],
            'one_time_fees.*.refundable' => ['nullable', 'boolean'],
            'discounts' => ['nullable', 'array'],
            'discounts.*.description' => ['nullable', 'string'],
            'discounts.*.percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'payment_methods' => ['nullable', 'array'],
            'payment_methods.en' => ['nullable', 'string'],
            'payment_methods.ar' => ['nullable', 'string'],
            'policies' => ['nullable', 'array'],
            'policies.en' => ['nullable', 'array'],
            'policies.ar' => ['nullable', 'array'],
            'policies.en.*' => ['nullable', 'string'],
            'policies.ar.*' => ['nullable', 'string'],
            'i18n' => ['nullable', 'array'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['course_id'] = $course->id;

        if (isset($data['is_active']) && $data['is_active']) {
            CourseFeeStructure::where('course_id', $course->id)->update(['is_active' => false]);
        }

        return CourseFeeStructure::create($data);
    }

    public function show(Course $course, CourseFeeStructure $feeStructure)
    {
        if ($feeStructure->course_id !== $course->id) {
            abort(404);
        }
        return $feeStructure;
    }

    public function update(Request $request, Course $course, CourseFeeStructure $feeStructure)
    {
        if ($feeStructure->course_id !== $course->id) {
            abort(404);
        }

        $data = $request->validate([
            'program_code' => ['sometimes', 'nullable', 'string', 'max:255'],
            'intake' => ['sometimes', 'nullable', 'string', 'max:255'],
            'tuition_fee_per_credit_hour' => ['sometimes', 'required', 'numeric', 'min:0'],
            'semesters' => ['sometimes', 'nullable', 'array'],
            'semesters.*.semester_type' => ['nullable', 'string'],
            'semesters.*.academic_period' => ['nullable', 'string'],
            'semesters.*.credit_hours' => ['nullable', 'numeric', 'min:0'],
            'semesters.*.tuition_fee' => ['nullable', 'numeric', 'min:0'],
            'semesters.*.library_fee' => ['nullable', 'numeric', 'min:0'],
            'semesters.*.student_club_fee' => ['nullable', 'numeric', 'min:0'],
            'semesters.*.ikad_fee' => ['nullable', 'numeric', 'min:0'],
            'semesters.*.visa_processing_fee' => ['nullable', 'numeric', 'min:0'],
            'semesters.*.medical_insurance_fee' => ['nullable', 'numeric', 'min:0'],
            'semesters.*.medical_examination_fee' => ['nullable', 'numeric', 'min:0'],
            'semesters.*.custom_fees' => ['nullable', 'array'],
            'semesters.*.custom_fees.*.name' => ['nullable', 'string', 'max:255'],
            'semesters.*.custom_fees.*.amount' => ['nullable', 'numeric', 'min:0'],
            'one_time_fees' => ['sometimes', 'nullable', 'array'],
            'one_time_fees.*.name' => ['nullable', 'string', 'max:255'],
            'one_time_fees.*.amount' => ['nullable', 'numeric', 'min:0'],
            'one_time_fees.*.refundable' => ['nullable', 'boolean'],
            'discounts' => ['sometimes', 'nullable', 'array'],
            'discounts.*.description' => ['nullable', 'string'],
            'discounts.*.percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'payment_methods' => ['sometimes', 'nullable', 'array'],
            'payment_methods.en' => ['nullable', 'string'],
            'payment_methods.ar' => ['nullable', 'string'],
            'policies' => ['sometimes', 'nullable', 'array'],
            'policies.en' => ['nullable', 'array'],
            'policies.ar' => ['nullable', 'array'],
            'policies.en.*' => ['nullable', 'string'],
            'policies.ar.*' => ['nullable', 'string'],
            'i18n' => ['sometimes', 'nullable', 'array'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (isset($data['is_active']) && $data['is_active'] && !$feeStructure->is_active) {
            CourseFeeStructure::where('course_id', $course->id)
                ->where('id', '!=', $feeStructure->id)
                ->update(['is_active' => false]);
        }

        $feeStructure->update($data);
        return $feeStructure;
    }

    public function destroy(Course $course, CourseFeeStructure $feeStructure)
    {
        if ($feeStructure->course_id !== $course->id) {
            abort(404);
        }
        $feeStructure->delete();
        return response()->json(['ok' => true]);
    }
}
