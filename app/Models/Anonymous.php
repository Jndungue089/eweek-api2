<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Anonymous extends Model
{
    //
    protected $fillable = ['message'];
    protected $table = 'anonymous';
}
