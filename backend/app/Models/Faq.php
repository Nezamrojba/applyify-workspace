<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $fillable = [
        'assigned_staff_id',
        'i18n',
        'order',
        'is_active'
    ];

    protected $casts = [
        'i18n' => 'array',
        'is_active' => 'boolean',
        'order' => 'integer'
    ];

    public function assignedStaff()
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }
}
