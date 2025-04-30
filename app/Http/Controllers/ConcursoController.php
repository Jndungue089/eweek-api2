<?php

namespace App\Http\Controllers;

use App\Models\Concurso;
use App\Models\UserConcurso;
use Illuminate\Http\Request;

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
        ]);

        $concurso = Concurso::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'place' => $validated['place'],
        ]);

        return response()->json(['message' => 'Concurso criado com sucesso!', "concurso" => $concurso], 202);
    }

    public function show($id)
    {
        $concurso = Concurso::with('users')->findOrFail($id);
        return response()->json($concurso);
    }

    public function update(Request $request, $id)
    {
        $concurso = Concurso::findOrFail($id);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'place' => 'required|string|max:255',
        ]);
        $concurso->update(array_filter([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'place' => $validated['place'],
        ]));
        return response()->json(['message' => 'Concurso atualizdado com sucesso!', 'concurso' => $concurso]);
    }
    public function destroy(Request $request, $id)
    {
        $concurso = Concurso::findOrFail($id);
        $concurso->delete();
        $user = $request->user();
        $userConcurso = UserConcurso::where('concursoId', $id)->where('userId', $user->id )->get();
        return response()->json(['message' => 'Concurso deletado com sucesso']);
    }
}
