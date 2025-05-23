<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Palestra extends Model
{
    //
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'persons',
        'place',
        'date'
    ];
    protected $casts = [
        'persons' => 'array',
    ];
}
