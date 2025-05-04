<?php

namespace App\Http\Controllers;

use App\Models\Resposta;
use App\Models\User;
use App\Models\UserResposta;
use Illuminate\Http\Request;

class RespostaController extends Controller
{
    //
    public function index()
    {
        $respostas = User::whereHas('respostas') // apenas usuários com respostas
            ->with(['respostas.question'])       // carrega as respostas e suas questões
            ->get();

        if ($respostas->isEmpty()) {
            return response()->json(['message' => 'Não há respostas cadastradas'], 200);
        }

        return response()->json($respostas, 200);
    }

    public function anonymous()
    {
        $respostas = Resposta::whereDoesntHave('userRespostas')->get();

        if ($respostas->isEmpty()) {
            return response()->json(['message' => 'Não há respostas cadastradas'], 200);
        }

        return response()->json($respostas, 200);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'answer' => 'required|string|max:255',
            'questionId' => 'required|exists:questaos,id',
            'userId' => 'nullable|integer|exists:users,id',
        ]);
        

        $resposta = Resposta::create([
            'answer' => $validated['answer'],
            'questionId' => $validated['questionId'],
        ]);
        if (!empty($validated['userId']) && $validated['userId'] != 0) {
            UserResposta::create([
                'userId' => $validated['userId'],
                'answerId' => $resposta->id,
            ]);
        }

        return response()->json(['message' => 'Resposta criada com sucesso!', "resposta" => $resposta], 202);
    }

    public function show($id)
    {
        $resposta = Resposta::findOrFail($id);
        return response()->json($resposta);
    }

    public function update(Request $request, $id)
    {
        $resposta = Resposta::findOrFail($id);
        $validated = $request->validate([
            'answer' => 'required|string|max:255',
            'questionId' => 'required|exists:questaos,id',
        ]);
        $resposta->update(array_filter([
            'answer' => $validated['answer'],
            'questionId' => $validated['questionId'],
        ]));
        return response()->json(['message' => 'Resposta atualizdada com sucesso!', 'resposta' => $resposta]);
    }
    public function destroy(Request $request, $id)
    {
        $resposta = Resposta::findOrFail($id);
        UserResposta::where('answerId', $resposta->id)->delete();
        $resposta->delete();
        return response()->json(['message' => 'Resposta deletada com sucesso']);
    }
}
