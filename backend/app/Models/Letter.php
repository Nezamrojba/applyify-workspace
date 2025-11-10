<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Letter extends Model
{
    protected $fillable = ['application_id','type','file_url','issued_by_staff_id','issued_at'];
}

