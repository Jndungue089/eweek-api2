<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    //
    use HasFactory;

    protected $fillable = ['userId', 'concursoId'];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }
    

    public function concurso()
    {
        return $this->belongsTo(Concurso::class, 'concursoId');
    }
    
}
