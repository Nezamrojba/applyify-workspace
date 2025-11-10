<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationDocument extends Model
{
    protected $fillable = ['application_id','stage_key','doc_type','file_url','file_type','size_bytes','uploaded_by_user_id','status','comment'];
    public function application() { return $this->belongsTo(Application::class); }
}
