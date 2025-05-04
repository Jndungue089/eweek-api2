<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resposta extends Model
{
    //
    protected $fillable = [
        'answer',
        'questionId'
    ];

    public function question()
    {
        return $this->belongsTo(Questao::class, 'questionId');
    }

    public function userRespostas()
    {
        return $this->hasMany(UserResposta::class, 'answerId');
    }
}
