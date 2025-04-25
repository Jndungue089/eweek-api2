<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserConcurso extends Model
{
    //
    protected $fillable = [
        'concursoId',
        'userId'
    ];
}
