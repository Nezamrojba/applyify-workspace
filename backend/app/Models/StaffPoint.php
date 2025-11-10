<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffPoint extends Model
{
    protected $fillable = ['staff_id','application_id','points','reason','credited_at','released','released_at'];

    protected $casts = [
        'credited_at' => 'datetime',
        'released_at' => 'datetime',
        'released' => 'boolean',
    ];

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}

