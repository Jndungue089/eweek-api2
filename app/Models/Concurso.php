<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Concurso extends Model
{
    protected $fillable = [
        'title',
        'description',
        'place',
        'has_voting',
        'is_voting_open',
        'voting_starts_at',
        'voting_ends_at'
    ];

    protected $casts = [
        'has_voting' => 'boolean',
        'is_voting_open' => 'boolean',
        'voting_starts_at' => 'datetime',
        'voting_ends_at' => 'datetime'
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
    public function participants()
    {
        return $this->belongsToMany(User::class, 'user_concursos', 'concursoId', 'userId');
    }
}
