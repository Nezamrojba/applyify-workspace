<?php

namespace App\Presentation\Http\Controllers\Student;

use App\Models\Course;
use App\Models\University;
use Illuminate\Http\Request;

class JourneyController
{
    public function universities(Request $request)
    {
        $q = University::query()
            ->where('is_active', true)
            ->orderBy('name');

        if ($search = $request->input('search')) {
            $q->where(fn ($builder) => $builder
                ->where('name', 'like', "%{$search}%")
                ->orWhere('i18n->name->en', 'like', "%{$search}%")
                ->orWhere('i18n->name->ar', 'like', "%{$search}%")
            );
        }

        return $q->get([
            'id',
            'name',
            'i18n',
            'country',
        ]);
    }

    public function courses(Request $request)
    {
        $query = Course::query()
            ->with([
                'activeFeeStructure',
                'university:id,name,i18n,country',
            ])
            ->where('is_active', true);

        if ($request->filled('university_id')) {
            $query->where('university_id', $request->integer('university_id'));
        }

        if ($search = $request->input('search')) {
            $query->where(fn ($builder) => $builder
                ->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('i18n->name->en', 'like', "%{$search}%")
                ->orWhere('i18n->name->ar', 'like', "%{$search}%")
            );
        }

        $courses = $query->orderBy('name')->get()->map(function (Course $course) {
            return [
                'id' => $course->id,
                'name' => $course->name,
                'code' => $course->code,
                'level' => $course->level,
                'duration_months' => $course->duration_months,
                'total_tuition_fees' => $course->total_tuition_fees,
                'acceptance_percent' => $course->acceptance_percent,
                'allow_installments' => $course->allow_installments,
                'payment_method' => $course->payment_method,
                'details' => $course->details,
                'i18n' => $course->i18n,
                'university' => [
                    'id' => $course->university?->id,
                    'name' => $course->university?->name,
                    'i18n' => $course->university?->i18n,
                    'location' => $course->university?->getAttribute('location'),
                    'country' => $course->university?->country,
                ],
                'active_fee_structure' => $course->activeFeeStructure,
            ];
        });

        return [
            'data' => $courses,
        ];
    }
}

