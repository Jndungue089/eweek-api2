<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\UserCurso;
use Illuminate\Http\Request;

class CursoController extends Controller
{
    //
    public function index()
    {
        $cursos = Curso::with('users')->get();
        if (!$cursos) {
            return response()->json(['message' => 'Não há cursos cadastrados'], 200);
        }
        return response()->json($cursos, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'available' => 'required|integer',
            'amount' => 'required|integer',
            'subscriptions' => 'required|integer',
            'place' => 'required|string|max:255',
            'start' => 'required',
            'end' => 'required',
            'isFull' => 'required',
        ]);

        $curso = Curso::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'available' => $validated['available'],
            'amount' => $validated['amount'],
            'subscriptions' => $validated['subscriptions'],
            'place' => $validated['place'],
            'start' => $validated['start'],
            'end' => $validated['end'],
            'isFull' => $validated['isFull'],
        ]);

        return response()->json(['message' => 'Curso criado com sucesso!', "curso" => $curso], 202);
    }

    public function show($id)
    {
        $curso = Curso::with('users')->findOrFail($id);
        return response()->json($curso, 200);
    }


    public function update(Request $request, $id)
    {
        $curso = Curso::findOrFail($id);
    
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'amount' => 'required|integer',
            'subscriptions' => 'required|integer',
            'place' => 'required|string|max:255',
            'start' => 'required',
            'end' => 'required',
        ]);
    
        // Lógica derivada
        $available = max(0, $validated['amount'] - $validated['subscriptions']);
        $isFull = $validated['subscriptions'] >= $validated['amount'];
    
        // Atualiza o curso
        $curso->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'available' => $available,
            'amount' => $validated['amount'],
            // 'subscriptions' => $validated['subscriptions'],
            'place' => $validated['place'],
            'start' => $validated['start'],
            'end' => $validated['end'],
            'isFull' => $isFull,
        ]);
    
        return response()->json([
            'message' => 'Curso atualizado com sucesso!',
            'curso' => $curso
        ]);
    }
    
    public function destroy(Request $request, $id)
    {
        $curso = Curso::findOrFail($id);
        $curso->delete();
        $user = $request->user();
        $userCurso = UserCurso::where('cursoId', $id)->where('userId', $user->id)->get();
        return response()->json(['message' => 'Curso deletado com sucesso']);
    }
}
