<?php

namespace App\Http\Controllers;

use App\Models\Palestra;
use Illuminate\Http\Request;

class PalestraController extends Controller
{
    //
    public function index()
    {
        $lectures = Palestra::all();
        if ($lectures->isEmpty()) {
            return response()->json(['message' => 'Não há palestras cadastradas'], 200);
        }
        return response()->json($lectures, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'persons' => 'required|array',
            'persons.*' => 'string',
            'place' => 'required|string|max:255',
            'date' => 'required|date'
        ]);

        $lecture = Palestra::create($validated);

        return response()->json(['message' => 'Palestra criada com sucesso!', 'lecture' => $lecture], 201);
    }

    public function show($id)
    {
        $lecture = Palestra::findOrFail($id);
        return response()->json($lecture);
    }

    public function update(Request $request, $id)
    {
        $lecture = Palestra::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'persons' => 'required|array',
            'persons.*' => 'string',
            'place' => 'required|string|max:255',
            'date' => 'required|date'
        ]);

        $lecture->update($validated);

        return response()->json(['message' => 'Palestra atualizada com sucesso!', 'lecture' => $lecture]);
    }

    public function destroy($id)
    {
        $lecture = Palestra::findOrFail($id);
        $lecture->delete();
        return response()->json(['message' => 'Palestra deletada com sucesso']);
    }
}
