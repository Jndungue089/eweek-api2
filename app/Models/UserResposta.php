<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserResposta extends Model
{
    //
    protected $fillable = [
        'userId',
        'answerId'
    ];
}
