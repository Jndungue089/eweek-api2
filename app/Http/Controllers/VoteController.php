<?php

namespace App\Http\Controllers;

use App\Models\Concurso;
use App\Models\Projeto;
use App\Models\Vote;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class VoteController extends Controller
{
    /**
     * Vota em um usuário em um concurso.
     */
    public function votarConcurso(Request $request, $concursoId, $voterId, $votedId)
    {
        $request->merge(['concursoId' => $concursoId, 'voterId' => $voterId, 'votedId' => $votedId]);

        $request->validate([
            'voterId' => 'required|integer|exists:users,id',
            'votedId' => 'required|integer|exists:users,id|different:voterId',
            'concursoId' => 'required|exists:concursos,id',
        ]);

        try {
            // Verificar se o concurso existe e permite votação
            $concurso = Concurso::findOrFail($concursoId);
            if (!$concurso->has_voting) {
                return response()->json(['message' => 'Este concurso não permite votação.'], 403);
            }

            // Verificar se a votação está ativa (se aplicável)
            $now = Carbon::now();
            if ($concurso->voting_starts_at && $now->lt($concurso->voting_starts_at)) {
                return response()->json(['message' => 'A votação ainda não começou.'], 403);
            }
            if ($concurso->voting_ends_at && $now->gt($concurso->voting_ends_at)) {
                return response()->json(['message' => 'A votação já encerrou.'], 403);
            }

            // Verificar se o votante está inscrito no concurso
            $isVoterInscrito = DB::table('user_concursos')
                ->where('userId', $voterId)
                ->where('concursoId', $concursoId)
                ->exists();

            if (!$isVoterInscrito) {
                return response()->json(['message' => 'Você não está inscrito neste concurso.'], 403);
            }

            // Verificar se o votado está inscrito no concurso
            $isVotedInscrito = DB::table('user_concursos')
                ->where('userId', $votedId)
                ->where('concursoId', $concursoId)
                ->exists();

            if (!$isVotedInscrito) {
                return response()->json(['message' => 'O usuário votado não está inscrito neste concurso.'], 403);
            }

            // Verifica se o usuário já votou neste concurso
            $existe = Vote::where('voterId', $voterId)
                ->where('concursoId', $concursoId)
                ->exists();

            if ($existe) {
                return response()->json(['message' => 'Você já votou neste concurso.'], 400);
            }

            $voto = Vote::create([
                'voterId' => $voterId,
                'votedId' => $votedId,
                'concursoId' => $concursoId,
                'enabled' => true,
            ]);

            return response()->json(['message' => 'Voto registrado com sucesso!', 'voto' => $voto], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Concurso não encontrado.'], 404);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao registrar voto.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Vota em um projeto.
     */
    public function votarProjeto(Request $request, $projectId, $voterId)
    {
        $request->merge(['projectId' => $projectId, 'voterId' => $voterId]);

        $request->validate([
            'voterId' => 'required|integer|exists:users,id',
            'projectId' => 'required|exists:projetos,id',
        ]);

        try {
            // Verificar se a votação está ativa
            $projeto = Projeto::findOrFail($projectId);
            $now = Carbon::now();
            if ($projeto->voting_starts_at && $now->lt($projeto->voting_starts_at)) {
                return response()->json(['message' => 'A votação ainda não começou.'], 403);
            }
            if ($projeto->voting_ends_at && $now->gt($projeto->voting_ends_at)) {
                return response()->json(['message' => 'A votação já encerrou.'], 403);
            }

            // Verifica se o usuário já votou neste projeto
            $existe = Vote::where('voterId', $voterId)
                ->where('projectId', $projectId)
                ->exists();

            if ($existe) {
                return response()->json(['message' => 'Você já votou neste projeto.'], 400);
            }

            $voto = Vote::create([
                'voterId' => $voterId,
                'projectId' => $projectId,
                'enabled' => true,
            ]);

            return response()->json(['message' => 'Voto registrado com sucesso!', 'voto' => $voto], 201);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao registrar voto.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Retorna votos de um concurso.
     */
    public function votosConcurso($concursoId)
    {
        try {
            $concurso = Concurso::with(['votos.voter', 'votos.votedUser'])->withCount('votos')->findOrFail($concursoId);

            $votantes = $concurso->votos->map(function ($voto) {
                return [
                    'id' => $voto->id,
                    'voter_id' => optional($voto->voter)->id,
                    'voter_nome' => optional($voto->voter)->fullName ?? 'Usuário removido',
                    'voter_email' => optional($voto->voter)->email ?? 'Email indisponível',
                    'voted_id' => optional($voto->votedUser)->id,
                    'voted_nome' => optional($voto->votedUser)->fullName ?? 'Usuário removido',
                    'voted_email' => optional($voto->votedUser)->email ?? 'Email indisponível',
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
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Concurso não encontrado.'], 404);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro inesperado.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Retorna votos de um projeto.
     */
    public function votosProjeto($projectId)
    {
        try {
            $projeto = Projeto::with(['votos.voter'])->withCount('votos')->findOrFail($projectId);

            $votantes = $projeto->votos->map(function ($voto) {
                return [
                    'id' => $voto->id,
                    'voter_id' => optional($voto->voter)->id,
                    'voter_nome' => optional($voto->voter)->fullName ?? 'Usuário removido',
                    'voter_email' => optional($voto->voter)->email ?? 'Email indisponível',
                    'votado_em' => $voto->created_at->toDateTimeString(),
                ];
            });

            return response()->json([
                'projeto_id' => $projeto->id,
                'titulo' => $projeto->name,
                'descricao' => $projeto->description,
                'total_votos' => $projeto->votos_count,
                'votantes' => $votantes,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Projeto não encontrado.'], 404);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro inesperado.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Retorna todos os concursos com seus votos.
     */
    public function concursosComVotos()
    {
        try {
            $concursos = Concurso::with(['votos.voter', 'votos.votedUser'])->withCount('votos')->get();

            $resultado = $concursos->map(function ($concurso) {
                $votantes = $concurso->votos->map(function ($voto) {
                    return [
                        'id' => $voto->id,
                        'voter_id' => optional($voto->voter)->id,
                        'voter_nome' => optional($voto->voter)->fullName ?? 'Usuário removido',
                        'voter_email' => optional($voto->voter)->email ?? 'Email indisponível',
                        'voted_id' => optional($voto->votedUser)->id,
                        'voted_nome' => optional($voto->votedUser)->fullName ?? 'Usuário removido',
                        'voted_email' => optional($voto->votedUser)->email ?? 'Email indisponível',
                        'votado_em' => $voto->created_at->toDateTimeString(),
                    ];
                });

                return [
                    'concurso_id' => $concurso->id,
                    'titulo' => $concurso->title,
                    'descricao' => $concurso->description,
                    'local' => $concurso->place,
                    'total_votos' => $concurso->votos_count,
                    'votantes' => $votantes,
                ];
            });

            return response()->json(['concursos' => $resultado]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao recuperar concursos.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Retorna todos os projetos com seus votos.
     */
    public function projetosComVotos()
    {
        try {
            $projetos = Projeto::with(['votos.voter'])->withCount('votos')->get();

            $resultado = $projetos->map(function ($projeto) {
                $votantes = $projeto->votos->map(function ($voto) {
                    return [
                        'id' => $voto->id,
                        'voter_id' => optional($voto->voter)->id,
                        'voter_nome' => optional($voto->voter)->fullName ?? 'Usuário removido',
                        'voter_email' => optional($voto->voter)->email ?? 'Email indisponível',
                        'votado_em' => $voto->created_at->toDateTimeString(),
                    ];
                });

                return [
                    'projeto_id' => $projeto->id,
                    'titulo' => $projeto->name,
                    'descricao' => $projeto->description,
                    'total_votos' => $projeto->votos_count,
                    'votantes' => $votantes,
                ];
            });

            return response()->json(['projetos' => $resultado]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao recuperar projetos.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Cancela (remove) um voto.
     */
    public function destroy($id)
    {
        try {
            $vote = Vote::findOrFail($id);
            $vote->delete();

            return response()->json(['message' => 'Voto cancelado com sucesso.']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Voto não encontrado.'], 404);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao cancelar voto.', 'error' => $e->getMessage()], 500);
        }
    }
    public function getVotos($id)
    {
        try {
            $concurso = Concurso::findOrFail($id);
            // Usar cache para reduzir carga
            $votos = Cache::remember("votos_concurso_$id", now()->addMinutes(10), function () use ($concurso) {
                return $concurso->votos()
                    ->with(['voter' => function ($query) {
                        $query->select('id', 'fullName', 'email');
                    }, 'votedUser' => function ($query) {
                        $query->select('id', 'fullName', 'email');
                    }])
                    ->get(['id', 'voterId', 'votedId', 'concursoId', 'created_at']);
            });

            // Mapear os dados para corresponder à estrutura esperada pelo frontend
            $votantes = $votos->map(function ($voto) {
                return [
                    'id' => $voto->id,
                    'voter_id' => $voto->voterId,
                    'voter_nome' => $voto->voter ? $voto->voter->fullName : 'Desconhecido',
                    'voter_email' => $voto->voter ? $voto->voter->email : 'N/A',
                    'voted_id' => $voto->votedId,
                    'voted_nome' => $voto->votedUser ? $voto->votedUser->fullName : 'Desconhecido',
                    'voted_email' => $voto->votedUser ? $voto->votedUser->email : 'N/A',
                    'votado_em' => $voto->created_at->toIso8601String(), // Usar created_at como votado_em
                ];
            });

            return response()->json(['votantes' => $votantes], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Concurso não encontrado'], 404);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao buscar votos: ' . $e->getMessage()], 500);
        }
    }
}
