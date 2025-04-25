<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Concurso extends Model
{
    //
    protected $fillable = [
        'title',
        'description',
        'place'
    ];
}
