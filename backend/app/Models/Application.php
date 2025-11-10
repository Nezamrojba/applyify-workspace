<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = [
        'student_id','university_id','course_id','passport_no','status','payment_status','payment_receipt_url','assigned_staff_id','stage_index','soft_deleted'
    ];

    public function student() { return $this->belongsTo(User::class, 'student_id'); }
    public function staff() { return $this->belongsTo(User::class, 'assigned_staff_id'); }
    public function university() { return $this->belongsTo(University::class); }
    public function course() { return $this->belongsTo(Course::class); }
    public function stages() { return $this->hasMany(ApplicationStage::class); }
    public function documents() { return $this->hasMany(ApplicationDocument::class); }
    public function letters() { return $this->hasMany(Letter::class); }
}

