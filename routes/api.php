<?php

use App\Http\Controllers\ConcursoController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\PalestraController;
use App\Http\Controllers\ProjetoController;
use App\Http\Controllers\QuestaoController;
use App\Http\Controllers\RespostaController;
use App\Http\Controllers\UserConcursoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserCursoController;
use App\Http\Controllers\UserProjetosController;
use App\Http\Controllers\UserRespostaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas Públicas (sem autenticação)
|--------------------------------------------------------------------------
*/

// Auth Routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [UserController::class, 'store'])->name('register');
    Route::post('/login', [UserController::class, 'login'])->name('login');
});
Route::get('/users/search', [UserController::class, 'search']);

// Token
// Route::get('/token', function (Request $request) {
//     $token = $request->session()->token();

//     $token = csrf_token();

//     return response()->json(["csrf" => $token]);
// });

// Palestras
Route::prefix('palestras')->group(function () {
    Route::get('/', [PalestraController::class, 'index']);
    Route::get('/{id}', [PalestraController::class, 'show']);
});

// Projetos
Route::prefix('projetos')->group(function () {
    Route::get('/', [ProjetoController::class, 'index']);
    Route::get('/{id}', [ProjetoController::class, 'show']);
});

// Cursos
Route::prefix('cursos')->group(function () {
    Route::get('/', [CursoController::class, 'index']);
    Route::get('/{id}', [CursoController::class, 'show']);
});

// User Respostas
Route::prefix('users/{userId}/resposta')->group(function () {
    Route::get('/', [UserRespostaController::class, 'index']);
    Route::get('/{id}', [UserRespostaController::class, 'show']);
    Route::post('/', [UserRespostaController::class, 'store']);
    Route::put('/{id}', [UserRespostaController::class, 'update']);
    Route::delete('/{id}', [UserRespostaController::class, 'destroy']);
    Route::get('/', [UserRespostaController::class, 'getQuestoesComRespostasPorUsuario']);
});

// Questão
Route::prefix('questao')->group(function () {
    Route::get('/', [QuestaoController::class, 'index']);
    Route::get('/{id}', [QuestaoController::class, 'show']);
});

// Resposta
Route::prefix('resposta')->group(function () {
    Route::get('/', [RespostaController::class, 'index']);
    Route::get('/{id}', [RespostaController::class, 'show']);
    Route::post('/', [RespostaController::class, 'store']);
    Route::put('/{id}', [RespostaController::class, 'update']);
    Route::get('anonymous/', [RespostaController::class, 'anonymous']);
});


/*
|--------------------------------------------------------------------------
| Rotas Protegidas (com autenticação Sanctum)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // Usuários
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::post('/', [UserController::class, 'store']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
        Route::post('/logout', [UserController::class, 'logout']);
        Route::get('/check-username/{username}', [UserController::class, 'checkUsername']);
        Route::get('/get-user/{id}', [UserController::class, 'getUser']);
        Route::post('/logout', [UserController::class, 'logout']);
    });

    // Palestras
    Route::prefix('palestras')->group(function () {
        Route::post('/', [PalestraController::class, 'store']);
        Route::put('/{id}', [PalestraController::class, 'update']);
        Route::delete('/{id}', [PalestraController::class, 'destroy']);
    });

    // Projetos
    Route::prefix('projetos')->group(function () {
        Route::post('/', [ProjetoController::class, 'store']);
        Route::put('/{id}', [ProjetoController::class, 'update']);
        Route::delete('/{id}', [ProjetoController::class, 'destroy']);
        Route::post('/{id}/accept', [ProjetoController::class, 'accept']);

    });

    // Cursos
    Route::prefix('cursos')->group(function () {
        Route::post('/', [CursoController::class, 'store']);
        Route::put('/{id}', [CursoController::class, 'update']);
        Route::delete('/{id}', [CursoController::class, 'destroy']);
    });

    // User Cursos
    Route::prefix('users/{userId}/cursos')->group(function () {
        Route::get('/', [UserCursoController::class, 'index']);
        Route::get('/{cursoId}', [UserCursoController::class, 'show']);
        Route::post('/', [UserCursoController::class, 'store']);
        Route::put('/{cursoId}', [UserCursoController::class, 'update']);
        Route::delete('/{cursoId}', [UserCursoController::class, 'destroy']);
    });

    // User Projetos
    Route::prefix('users/{userId}/projetos')->group(function () {
        Route::get('/', [UserProjetosController::class, 'index']);
        Route::get('/{id}', [UserProjetosController::class, 'show']);
        Route::post('/', [UserProjetosController::class, 'store']);
        Route::put('/{id}', [UserProjetosController::class, 'update']);
        Route::delete('/{id}', [UserProjetosController::class, 'destroy']);
    });

    // Concursos
    Route::prefix('concursos')->group(function () {
        Route::get('/', [ConcursoController::class, 'index']);
        Route::get('/{concursoId}', [ConcursoController::class, 'show']);
        Route::post('/', [ConcursoController::class, 'store']);
        Route::put('/{concursoId}', [ConcursoController::class, 'update']);
        Route::delete('/{concursoId}', [ConcursoController::class, 'destroy']);
    });

    // User Concursos
    Route::prefix('users/{userId}/concursos')->group(function () {
        Route::get('/', [UserConcursoController::class, 'index']);
        Route::get('/{concursoId}', [UserConcursoController::class, 'show']);
        Route::post('/', [UserConcursoController::class, 'store']);
        Route::put('/{concursoId}', [UserConcursoController::class, 'update']);
        Route::delete('/{concursoId}', [UserConcursoController::class, 'destroy']);
    });

    // Questão
    Route::prefix('questao')->group(function () {
        Route::post('/', [QuestaoController::class, 'store']);
        Route::put('/{id}', [QuestaoController::class, 'update']);
        Route::delete('/{id}', [QuestaoController::class, 'destroy']);
    });
});
