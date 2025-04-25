<?php

namespace App\Http\Controllers;

use App\Models\Concurso;
use App\Models\UserConcurso;
use Illuminate\Http\Request;

class UserConcursoController extends Controller
{
    //
    public function index($userId)
    {
        $concursos = UserConcurso::where('userId', $userId)->get();
        return response()->json($concursos);
    }

    // Inscrever um usuário num curso
    public function store(Request $request, $userId)
    {
        $validated = $request->validate([
            'concursoId' => 'required|exists:concursos,id'
        ]);

        $userConcurso = UserConcurso::create([
            'userId' => $userId,
            'concursoId' => $validated['concursoId']
        ]);

        return response()->json([
            'message' => 'Inscrição realizada com sucesso!',
            'data' => $userConcurso
        ], 201);
    }

    public function show($userId, $concursoId)
    {
        $userConcurso = UserConcurso::where('userId', $userId)
                              ->where('concursoId', $concursoId)
                              ->first();

        if (!$userConcurso) {
            return response()->json(['message' => 'Inscrição não encontrada'], 404);
        }

        return response()->json($userConcurso);
    }

    public function update(Request $request, $userId, $concursoId)
    {
        $validated = $request->validate([
            'concursoId' => 'required|exists:cursos,id'
        ]);

        $userConcurso = UserConcurso::where('userId', $userId)
                              ->where('concursoId', $concursoId)
                              ->first();

        if (!$userConcurso) {
            return response()->json(['message' => 'Inscrição não encontrada'], 404);
        }

        $userConcurso->update(['concursoId' => $validated['concursoId']]);

        return response()->json(['message' => 'Inscrição atualizada!', 'data' => $userConcurso]);
    }

    public function destroy($userId, $concursoId)
    {
        $userConcurso = UserConcurso::where('userId', $userId)
                              ->where('concursoId', $concursoId)
                              ->first();

        if (!$userConcurso) {
            return response()->json(['message' => 'Inscrição não encontrada'], 404);
        }

        $userConcurso->delete();
        return response()->json(['message' => 'Inscrição cancelada com sucesso']);
    }
}
