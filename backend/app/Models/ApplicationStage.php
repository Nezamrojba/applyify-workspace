<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationStage extends Model
{
    protected $fillable = ['application_id','stage_key','status','data'];
    protected $casts = ['data' => 'array'];
    public function application() { return $this->belongsTo(Application::class); }
}

