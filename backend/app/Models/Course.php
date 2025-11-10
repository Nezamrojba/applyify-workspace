<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'university_id','name','code','level','acceptance_percent','duration_months','is_active',
        'details','total_tuition_fees','procedure_fees','payment_method','allow_installments','total_years','i18n'
    ];
    protected $casts = [
        'i18n' => 'array',
        'is_active' => 'boolean',
        'allow_installments' => 'boolean',
    ];

    public function university()
    {
        return $this->belongsTo(University::class);
    }

    public function feeStructures()
    {
        return $this->hasMany(CourseFeeStructure::class);
    }

    public function activeFeeStructure()
    {
        return $this->hasOne(CourseFeeStructure::class)->where('is_active', true);
    }
}
