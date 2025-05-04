<?php

namespace App\Http\Controllers;

use App\Models\Anonymous;
use Illuminate\Http\Request;

class AnonymousController extends Controller
{
    //
    public function index()
    {
        $anonymous = Anonymous::all();
        if ($anonymous->isEmpty()) {
            return response()->json(['message' => 'Não há mensagens anónimas cadastradas'], 200);
        }
        return response()->json($anonymous, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:255'
        ]);

        $anonymous = Anonymous::create($validated);

        return response()->json(['message' => 'Mensagem anónima criada com sucesso!', 'anonymous' => $anonymous], 201);
    }

    public function show($id)
    {
        $anonymous = Anonymous::findOrFail($id);
        return response()->json($anonymous);
    }

    public function update(Request $request, $id)
    {
        $anonymous = Anonymous::findOrFail($id);

        $validated = $request->validate([
            'message' => 'required|string|max:255'
        ]);

        $anonymous->update($validated);

        return response()->json(['message' => 'Mensagem anónima atualizada com sucesso!', 'anonymous' => $anonymous]);
    }

    public function destroy($id)
    {
        $anonymous = Anonymous::findOrFail($id);
        $anonymous->delete();
        return response()->json(['message' => 'Mensagem anónima deletada com sucesso']);
    }
}
