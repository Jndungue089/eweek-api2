<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Concurso extends Model
{
    protected $fillable = [
        'title',
        'description',
        'place'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_concursos', 'concursoId', 'userId')
            ->withPivot('created_at', 'updated_at');
    }

    public function votos()
    {
        return $this->hasMany(Vote::class, 'concursoId');
    }
}
