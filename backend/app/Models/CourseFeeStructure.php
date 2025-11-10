<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseFeeStructure extends Model
{
    protected $fillable = [
        'course_id',
        'program_code',
        'intake',
        'tuition_fee_per_credit_hour',
        'semesters',
        'one_time_fees',
        'discounts',
        'payment_methods',
        'policies',
        'i18n',
        'is_active'
    ];

    protected $casts = [
        'semesters' => 'array',
        'one_time_fees' => 'array',
        'discounts' => 'array',
        'payment_methods' => 'array',
        'policies' => 'array',
        'i18n' => 'array',
        'tuition_fee_per_credit_hour' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
