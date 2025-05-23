<?php

namespace App\Http\Controllers;

use App\Models\Concurso;
use App\Models\UserConcurso;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ConcursoController extends Controller
{
    //
    public function index()
    {
        $concursos = Concurso::with('users')->get();
        if (!$concursos) {
            return response()->json(['message' => 'Não há concursos cadastrados'], 200);
        }
        return response()->json($concursos, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'place' => 'required|string|max:255',
            'has_voting' => 'sometimes|boolean',
            'is_voting_open' => 'sometimes|boolean',
            'voting_starts_at' => 'nullable|date|required_if:has_voting,true',
            'voting_ends_at' => 'nullable|date|after:voting_starts_at|required_if:has_voting,true',
        ]);
    
        $concurso = Concurso::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'place' => $validated['place'],
            'has_voting' => $validated['has_voting'] ?? false,
            'is_voting_open' => $validated['is_voting_open'] ?? false,
            'voting_starts_at' => $validated['voting_starts_at'] ?? null,
            'voting_ends_at' => $validated['voting_ends_at'] ?? null,
        ]);
    
        return response()->json(['message' => 'Concurso criado com sucesso!', "concurso" => $concurso], 201);
    }

    public function show($id)
    {
        $cacheKey = 'concurso_' . $id;
        $concurso = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($id) {
            return Concurso::findOrFail($id);
        });
        return response()->json($concurso);
    }

    public function update(Request $request, $id)
    {
        $concurso = Concurso::findOrFail($id);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'place' => 'required|string|max:255',
            'has_voting' => 'sometimes|boolean',
            'is_voting_open' => 'sometimes|boolean',
            'voting_starts_at' => 'nullable|date|required_if:has_voting,true',
            'voting_ends_at' => 'nullable|date|after:voting_starts_at|required_if:has_voting,true',
        ]);
    
        $concurso->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'place' => $validated['place'],
            'has_voting' => $validated['has_voting'] ?? $concurso->has_voting,
            'is_voting_open' => $validated['is_voting_open'] ?? $concurso->is_voting_open,
            'voting_starts_at' => $validated['voting_starts_at'] ?? $concurso->voting_starts_at,
            'voting_ends_at' => $validated['voting_ends_at'] ?? $concurso->voting_ends_at,
        ]);
    
        // Limpa o cache após atualização
        Cache::forget('concurso_' . $id);
    
        return response()->json([
            'message' => 'Concurso atualizado com sucesso!', 
            'concurso' => $concurso->fresh()
        ]);
    }
    
    public function destroy(Request $request, $id)
    {
        $concurso = Concurso::findOrFail($id);
        $concurso->delete();
        $user = $request->user();
        $userConcurso = UserConcurso::where('concursoId', $id)->where('userId', $user->id)->get();
        return response()->json(['message' => 'Concurso deletado com sucesso']);
    }
    /**
     * Atualiza o período de votação de um concurso.
     */
    public function updateVotingPeriod(Request $request, $concursoId)
    {
        $request->validate([
            'has_voting' => 'required|boolean',
            'voting_starts_at' => 'nullable|date|after:now|required_if:has_voting,true',
            'voting_ends_at' => 'nullable|date|after:voting_starts_at|required_if:has_voting,true',
        ]);

        try {
            $concurso = Concurso::findOrFail($concursoId);

            $concurso->has_voting = $request->has_voting;

            if ($concurso->has_voting) {
                $concurso->voting_starts_at = $request->voting_starts_at
                    ? Carbon::parse($request->voting_starts_at)
                    : null;
                $concurso->voting_ends_at = $request->voting_ends_at
                    ? Carbon::parse($request->voting_ends_at)
                    : null;
            } else {
                // Se votação não está habilitada, limpar períodos
                $concurso->voting_starts_at = null;
                $concurso->voting_ends_at = null;
            }

            $concurso->save();

            return response()->json([
                'message' => 'Configuração de votação atualizada com sucesso.',
                'concurso' => [
                    'id' => $concurso->id,
                    'title' => $concurso->title,
                    'description' => $concurso->description,
                    'place' => $concurso->place,
                    'has_voting' => $concurso->has_voting,
                    'voting_starts_at' => $concurso->voting_starts_at?->toDateTimeString(),
                    'voting_ends_at' => $concurso->voting_ends_at?->toDateTimeString(),
                ],
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Concurso não encontrado.'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao atualizar configuração de votação.', 'error' => $e->getMessage()], 500);
        }
    }
    public function getConcursoParticipants($concursoId)
    {
        try {
            $users = DB::table('user_concursos')
                ->where('concursoId', $concursoId)
                ->join('users', 'user_concursos.userId', '=', 'users.id')
                ->select('users.id', 'users.fullName', 'users.email', 'users.course', 'users.school')
                ->get();
            return response()->json(['users' => $users]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao buscar participantes', 'error' => $e->getMessage()], 500);
        }
    }

    public function getParticipants($id)
    {
        try {
            $concurso = Concurso::findOrFail($id);
            $users = Cache::remember("participantes_concurso_$id", now()->addMinutes(10), function () use ($concurso) {
                return $concurso->participants()->get([
                    'users.id',
                    'users.fullName',
                    'users.email',
                    'users.phone',
                    'users.course',
                    'users.school',
                ]);
            });
            return response()->json(['users' => $users], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Concurso não encontrado'], 404);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao buscar participantes: ' . $e->getMessage()], 500);
        }
    }
}
