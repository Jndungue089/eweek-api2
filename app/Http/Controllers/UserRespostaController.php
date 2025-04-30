<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserResposta;
use Illuminate\Http\Request;

class UserRespostaController extends Controller
{
    //
    public function index($userId)
    {
        $usuarios = User::with([
            'userRespostas.resposta.questao'
        ])->get();
    
        $resultado = $usuarios->map(function ($user) {
            return [
                'user' => [
                    'id' => $user->id,
                    'fullName' => $user->fullName,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'course' => $user->course,
                    'school' => $user->school,
                    'grade' => $user->grade,
                    'age_range' => $user->age_range,
                    'gender' => $user->gender,
                    'profile' => $user->profile,
                    // adicione outros campos se necessário
                ],
                'respostas' => $user->userRespostas->map(function ($userResposta) {
                    return [
                        'questao' => $userResposta->resposta->questao->question ?? null,
                        'resposta' => $userResposta->resposta->answer ?? null,
                    ];
                }),
            ];
        });
    
        return response()->json($resultado);
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

    public function getQuestoesComRespostasPorUsuario($userId)
{
    $userRespostas = UserResposta::with(['resposta.questao'])
        ->where('userId', $userId)
        ->get();

    $resultado = $userRespostas->map(function ($userResposta) {
        return [
            'questao' => $userResposta->resposta->questao->question ?? null,
            'resposta' => $userResposta->resposta->answer ?? null,
        ];
    });

    return response()->json($resultado);
}

}
