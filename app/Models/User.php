<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'fullName',
        'phone',
        'age_range',
        'email',
        'course',
        'school',
        'grade',
        'gender',
        'profile',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function projetos()
    {
        return $this->belongsToMany(Projeto::class, 'projeto_user', 'userId', 'projetoId');
    }
    public function concursos()
    {
        return $this->belongsToMany(Concurso::class, 'user_concursos', 'userId', 'concursoId');
    }
    public function cursos()
    {
        return $this->belongsToMany(Curso::class, 'user_cursos', 'userId', 'cursoId');
    }

    public function respostas()
    {
        return $this->belongsToMany(Resposta::class, 'user_respostas', 'userId', 'answerId')
                    ->with('question'); // incluir a questão associada à resposta
    }

    public function votos()
{
    return $this->hasMany(Vote::class);
}

    
}
