<?php

namespace App\Http\Controllers;

use App\Models\Questao;
use Illuminate\Http\Request;

class QuestaoController extends Controller
{
    //
    public function index()
    {
        $questoes = Questao::all();
        if (!$questoes) {
            return response()->json(['message' => 'Não há questões cadastradas'], 200);
        }
        return response()->json($questoes, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:255',
        ]);

        $questao = Questao::create([
            'question' => $validated['question'],
        ]);

        return response()->json(['message' => 'Questão criada com sucesso!', "questao" => $questao], 202);
    }

    public function show($id)
    {
        $questao = Questao::findOrFail($id);
        return response()->json($questao);
    }

    public function update(Request $request, $id)
    {
        $questao = Questao::findOrFail($id);
        $validated = $request->validate([
            'question' => 'required|string|max:255',
        ]);
        $questao->update(array_filter([
            'question' => $validated['question'],
        ]));
        return response()->json(['message' => 'Questao atualizdada com sucesso!', 'questao' => $questao]);
    }
    public function destroy(Request $request, $id)
    {
        $questao = Questao::findOrFail($id);
        $questao->delete();
        return response()->json(['message' => 'Questão deletada com sucesso']);
    }
}
