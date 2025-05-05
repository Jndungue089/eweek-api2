<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\UserCurso;
use Illuminate\Http\Request;

class UserCursoController extends Controller
{
    public function index($userId)
    {
        $cursos = UserCurso::where('userId', $userId)->get();
        return response()->json($cursos);
    }

    // Inscrever um usuário num curso
    public function store(Request $request, $userId)
    {
        $validated = $request->validate([
            'cursoId' => 'required|exists:cursos,id'
        ]);

        $curso = Curso::find($validated['cursoId']);

        // Recalcular inscrições em tempo real
        $totalSubscriptions = UserCurso::where('cursoId', $curso->id)->count();
        if ($totalSubscriptions >= $curso->amount) {
            return response()->json(['message' => 'O curso está lotado.'], 400);
        }

        // Verifica se o usuário já está inscrito
        $exists = UserCurso::where('userId', $userId)
            ->where('cursoId', $curso->id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Usuário já inscrito neste curso.'], 409);
        }

        $userCurso = UserCurso::create([
            'userId' => $userId,
            'cursoId' => $curso->id
        ]);

        // Atualiza o curso com base nos dados reais
        $totalSubscriptions += 1;
        $curso->subscriptions = $totalSubscriptions;
        $curso->available = max(0, $curso->amount - $totalSubscriptions);
        $curso->isFull = $totalSubscriptions >= $curso->amount;
        $curso->save();

        return response()->json([
            'message' => 'Inscrição realizada com sucesso!',
            'data' => $userCurso
        ], 201);
    }


    public function show($userId, $cursoId)
    {
        $userCurso = UserCurso::where('userId', $userId)
            ->where('cursoId', $cursoId)
            ->first();

        if (!$userCurso) {
            return response()->json(['message' => 'Inscrição não encontrada'], 404);
        }

        return response()->json($userCurso);
    }

    public function update(Request $request, $userId, $cursoId)
    {
        $validated = $request->validate([
            'cursoId' => 'required|exists:cursos,id'
        ]);

        $userCurso = UserCurso::where('userId', $userId)
            ->where('cursoId', $cursoId)
            ->first();

        if (!$userCurso) {
            return response()->json(['message' => 'Inscrição não encontrada'], 404);
        }

        $userCurso->update(['cursoId' => $validated['cursoId']]);

        // Atualizar o número de inscrições no curso novo
        $curso = Curso::find($validated['cursoId']);
        $totalSubscriptions = UserCurso::where('cursoId', $curso->id)->count();
        $curso->subscriptions = $totalSubscriptions;
        $curso->available = max(0, $curso->amount - $totalSubscriptions);
        $curso->isFull = $totalSubscriptions >= $curso->amount;
        $curso->save();

        return response()->json(['message' => 'Inscrição atualizada!', 'data' => $userCurso]);
    }


    public function destroy($userId, $cursoId)
    {
        $userCurso = UserCurso::where('userId', $userId)
            ->where('cursoId', $cursoId)
            ->first();

        if (!$userCurso) {
            return response()->json(['message' => 'Inscrição não encontrada'], 404);
        }

        $userCurso->delete();

        $curso = Curso::find($cursoId);
        if ($curso) {
            $totalSubscriptions = UserCurso::where('cursoId', $curso->id)->count();
            $curso->subscriptions = $totalSubscriptions;
            $curso->available = max(0, $curso->amount - $totalSubscriptions);
            $curso->isFull = $totalSubscriptions >= $curso->amount;
            $curso->save();
        }

        return response()->json(['message' => 'Inscrição cancelada com sucesso']);
    }
}
