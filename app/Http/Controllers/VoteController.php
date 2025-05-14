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
    public function votar(Request $request, $concursoId, $voterId, $votedId)
    {
        try {
            // Check if the voter has already voted in this contest
            $existe = Vote::where('voterId', $voterId)
                ->where('concursoId', $concursoId)
                ->exists();

            if ($existe) {
                return response()->json(['message' => 'Você já votou neste concurso.'], 400);
            }

            // Verify that voter and voted user are different
            if ($voterId == $votedId) {
                return response()->json(['message' => 'Você não pode votar em si mesmo.'], 400);
            }

            Vote::create([
                'voterId' => $voterId,
                'votedId' => $votedId,
                'concursoId' => $concursoId,
            ]);

            return response()->json(['message' => 'Voto registrado com sucesso!']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Concurso ou usuário não encontrado.'], 404);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Erro ao acessar o banco de dados.', 'error' => $e->getMessage()], 500);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro inesperado.', 'error' => $e->getMessage()], 500);
        }
    }

    public function votos(Request $request, $concursoId)
    {
        try {
            $concurso = Concurso::with(['votos.voter', 'votos.votedUser'])->withCount('votos')->findOrFail($concursoId);

            $votantes = $concurso->votos->map(function ($voto) {
                return [
                    'voter_id' => optional($voto->voter)->id,
                    'voter_nome' => optional($voto->voter)->fullName ?? 'Usuário removido',
                    'voter_email' => optional($voto->voter)->email ?? 'Email indisponível',
                    'voted_id' => optional($voto->votedUser)->id,
                    'voted_nome' => optional($voto->votedUser)->fullName ?? 'Usuário removido',
                    'voted_email' => optional($voto->votedUser)->email ?? 'Email indisponível',
                    'votado_em' => $voto->created_at->toDateTimeString(),
                ];
            });

            if ($concurso->votos->isEmpty()) {
                return response()->json(['message' => 'Não há votos!'], 200);
            }

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
