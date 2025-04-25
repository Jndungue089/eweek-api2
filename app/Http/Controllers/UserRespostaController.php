<?php

namespace App\Http\Controllers;

use App\Models\UserResposta;
use Illuminate\Http\Request;

class UserRespostaController extends Controller
{
    //
    public function index($userId)
    {
        $userRespostas = UserResposta::where('userId', $userId)->get();
        return response()->json($userRespostas);
    }

    // Inscrever um usuário num curso
    public function store(Request $request, $userId)
    {
        $validated = $request->validate([
            'answerId' => 'required|exists:respostas,id'
        ]);

        $userResposta = UserResposta::create([
            'userId' => $userId,
            'answerId' => $validated['answerId']
        ]);

        return response()->json([
            'message' => 'Resposta de usuário registada com sucesso!',
            'userResposta' => $userResposta
        ], 201);
    }

    public function show($userId, $answerId)
    {
        $userResposta = UserResposta::where('userId', $userId)
                              ->where('answerId', $answerId)
                              ->first();

        if (!$userResposta) {
            return response()->json(['message' => 'Resposta de usuário não encontrada'], 404);
        }

        return response()->json($userResposta);
    }

    public function update(Request $request, $userId, $answerId)
    {
        $validated = $request->validate([
            'answerId' => 'required|exists:respostas,id'
        ]);

        $userResposta = UserResposta::where('userId', $userId)
                              ->where('cursoId', $answerId)
                              ->first();

        if (!$userResposta) {
            return response()->json(['message' => 'Resposta de usuário não encontrada'], 404);
        }

        $userResposta->update(['cursoId' => $validated['cursoId']]);

        return response()->json(['message' => 'Resposta de usuário atualizada!', 'data' => $userResposta]);
    }

    public function destroy($userId, $answerId)
    {
        $userResposta = UserResposta::where('userId', $userId)
                              ->where('answerId', $answerId)
                              ->first();

        if (!$userResposta) {
            return response()->json(['message' => 'Resposta de usuário não encontrada'], 404);
        }

        $userResposta->delete();

        return response()->json(['message' => 'Resposta de usuário deletada com sucesso']);
    }
}
