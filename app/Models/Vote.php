<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasFactory;

    protected $fillable = [
        'voterId',
        'votedId',
        'concursoId',
        'projectId',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];


    public function voter()
    {
        return $this->belongsTo(User::class, 'voterId');
    }

    public function votedUser()
    {
        return $this->belongsTo(User::class, 'votedId');
    }

    public function concurso()
    {
        return $this->belongsTo(Concurso::class, 'concursoId');
    }

    public function projeto()
    {
        return $this->belongsTo(Projeto::class, 'projectId');
    }
}
