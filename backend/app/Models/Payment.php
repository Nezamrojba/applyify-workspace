<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['application_id','student_id','amount','currency','type','receipt_url','status'];
}

