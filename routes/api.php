<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AnonymousController, ConcursoController, CursoController, PalestraController, ProjetoController,
    QuestaoController, RespostaController, UserConcursoController, UserController, UserCursoController,
    UserProjetosController, UserRespostaController, VoteController
};

Route::get('/health', fn () => response()->json(['status' => 'ok'], 200));

/*
|--------------------------------------------------------------------------
| Auth Routes (sem autenticação)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/register', [UserController::class, 'store']);
    Route::post('/login', [UserController::class, 'login']);
});
Route::get('/users/search', [UserController::class, 'search']);

/*
|--------------------------------------------------------------------------
| Rotas Públicas
|--------------------------------------------------------------------------
*/
Route::apiResources([
    'palestras' => PalestraController::class,
    'projetos' => ProjetoController::class,
    'cursos' => CursoController::class,
    'questao' => QuestaoController::class,
    'resposta' => RespostaController::class,
    'anonymous' => AnonymousController::class,
]);

Route::get('/palestras/{id}', [PalestraController::class, 'show']);
Route::get('/projetos/{id}', [ProjetoController::class, 'show']);
Route::get('/cursos/{id}', [CursoController::class, 'show']);
Route::get('/questao/{id}', [QuestaoController::class, 'show']);
Route::get('/resposta/{id}', [RespostaController::class, 'show']);
Route::get('/anonymous/{id}', [AnonymousController::class, 'show']);

// User respostas especial
Route::get('/users/{userId}/resposta', [UserRespostaController::class, 'getQuestoesComRespostasPorUsuario']);

/*
|--------------------------------------------------------------------------
| Rotas Protegidas
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Usuários
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
        Route::post('/logout', [UserController::class, 'logout']);
        Route::get('/check-username/{username}', [UserController::class, 'checkUsername']);
        Route::get('/get-user/{id}', [UserController::class, 'getUser']);

        // Cursos do usuário
        Route::apiResource('{userId}/cursos', UserCursoController::class);
        Route::apiResource('{userId}/projetos', UserProjetosController::class);
        Route::apiResource('{userId}/concursos', UserConcursoController::class);
        Route::apiResource('{userId}/resposta', UserRespostaController::class);
    });

    // Palestras, Projetos, Cursos (somente CRUD autenticado)
    Route::apiResource('palestras', PalestraController::class)->except(['index', 'show']);
    Route::apiResource('projetos', ProjetoController::class)->except(['index', 'show']);
    Route::apiResource('cursos', CursoController::class)->except(['index', 'show']);

    // Projetos e Concursos: ações extras
    Route::post('/projetos/{id}/accept', [ProjetoController::class, 'accept']);
    Route::patch('/projetos/{id}/voting-period', [ProjetoController::class, 'updateVotingPeriod']);
    Route::patch('/concursos/{id}/voting-period', [ConcursoController::class, 'updateVotingPeriod']);

    // Concursos
    Route::apiResource('concursos', ConcursoController::class)->except(['index', 'show']);
    Route::get('/users/concursos/{concursoId}', [ConcursoController::class, 'getParticipants']);

    // Questões (somente modificações protegidas)
    Route::post('/questao', [QuestaoController::class, 'store']);
    Route::put('/questao/{id}', [QuestaoController::class, 'update']);
    Route::delete('/questao/{id}', [QuestaoController::class, 'destroy']);

    // Votação
    Route::prefix('concursos')->group(function () {
        Route::post('{concursoId}/votar/{voterId}/{votedId}', [VoteController::class, 'votarConcurso']);
        Route::get('{concursoId}/votos', [VoteController::class, 'getVotos']);
    });

    Route::prefix('projetos')->group(function () {
        Route::post('{projectId}/votar/{voterId}', [VoteController::class, 'votarProjeto']);
        Route::get('{projectId}/votos', [VoteController::class, 'votosProjeto']);
    });

    Route::get('/concursos-com-votos', [VoteController::class, 'concursosComVotos']);
    Route::get('/projetos-com-votos', [VoteController::class, 'projetosComVotos']);
    Route::delete('/votos/{id}', [VoteController::class, 'destroy']);
});
