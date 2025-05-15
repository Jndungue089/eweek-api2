<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Projeto extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'photo',
        'equipments',
        'accepted',
        'hasPrototype',
        'voting_starts_at',
        'voting_ends_at'
    ];

    protected $casts = [
        'accepted' => 'boolean',
        'hasPrototype' => 'boolean',
        'voting_starts_at' => 'datetime',
        'voting_ends_at' => 'datetime'
    ];

    public function persons()
    {
        return $this->belongsToMany(User::class, 'projeto_user', 'projetoId', 'userId');
    }

    public function votos()
    {
        return $this->hasMany(Vote::class, 'projectId');
    }
}
