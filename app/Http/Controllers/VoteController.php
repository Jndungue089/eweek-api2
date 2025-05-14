<?php

namespace App\Http\Controllers;

use App\Models\Concurso;
use App\Models\Vote;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class VoteController extends Controller
{
    //
    public function votar(Request $request, $concursoId, $userId)
    {
        try {
            $existe = Vote::where('userId', $userId)
                ->where('concursoId', $concursoId)
                ->exists();

            if ($existe) {
                return response()->json(['message' => 'Você já votou neste concurso.'], 400);
            }

            Vote::create([
                'userId' => $userId,
                'concursoId' => $concursoId,
            ]);

            return response()->json(['message' => 'Voto registrado com sucesso!']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Concurso não encontrado.'], 404);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Erro ao acessar o banco de dados.', 'error' => $e->getMessage()], 500);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro inesperado.', 'error' => $e->getMessage()], 500);
        }
    }
    public function votos(Request $request, $concursoId)
    {
        try {
            $concurso = Concurso::with(['votos.user'])->withCount('votos')->findOrFail($concursoId);
    
            $votantes = $concurso->votos->map(function ($voto) {
                return [
                    'id' => optional($voto->user)->id,
                    'nome' => optional($voto->user)->fullName ?? 'Usuário removido',,
                    'votado_em' => $voto->created_at->toDateTimeString(),
                ];
            });
            return response()->json([
                'concurso_id' => $concurso->id,
                'titulo' => $concurso->title,
                'descricao' => $concurso->description,
                'local' => $concurso->place,
                'total_votos' => $concurso->votos_count,
                'votantes' => $votantes,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Concurso não encontrado.'], 404);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Erro ao consultar votos.', 'error' => $e->getMessage()], 500);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro inesperado.', 'error' => $e->getMessage()], 500);
        }
    }
    
}
