<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Projeto extends Model
{
    //
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'photo',
        'equipments',
        'accepted',
        'hasPrototype',
    ];

    protected $casts = [
        'accepted' => 'boolean',
        'hasPrototype' => 'boolean',
    ];
    public function persons()
    {
        return $this->belongsToMany(User::class, 'projeto_user', 'projetoId', 'userId');
    }
}
