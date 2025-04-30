<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    //
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'available',
        'amount',
        'subscriptions',
        'place',
        'start',
        'end',
        'isFull'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_cursos', 'cursoId', 'userId')
        ->withPivot('created_at', 'updated_at');;
    }
}
