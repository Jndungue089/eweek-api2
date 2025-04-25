<?php

namespace App\Http\Controllers;

use App\Models\Projeto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjetoController extends Controller
{
    //
    public function index()
    {
        $projects = Projeto::with('persons')->get();
        if ($projects->isEmpty()) {
            return response()->json(['message' => 'Não há projetos cadastrados'], 200);
        }
        return response()->json($projects, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'photo' => 'nullable|file|image|max:2048',
            'equipments' => 'nullable|string',
            'accepted' => 'boolean',
            'hasPrototype' => 'boolean',
            'persons' => 'required|array|max:3',
            'persons.*' => 'integer|exists:users,id',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('projects', 'public');
        }

        $project = Projeto::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'photo' => $photoPath ? Storage::url($photoPath) : null,
            'equipments' => $validated['equipments'] ?? null,
            'accepted' => $validated['accepted'] ?? false,
            'hasPrototype' => $validated['hasPrototype'] ?? false,
        ]);

        $project->persons()->attach($validated['persons']);

        return response()->json([
            'message' => 'Projeto criado com sucesso!',
            'project' => $project->load('persons'),
        ], 201);
    }

    public function show($id)
    {
        $project = Projeto::with('persons')->findOrFail($id);
        return response()->json($project);
    }

    public function update(Request $request, $id)
    {
        $project = Projeto::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'photo' => 'nullable|file|image|max:2048',
            'equipments' => 'nullable|string',
            'accepted' => 'boolean',
            'hasPrototype' => 'boolean',
            'persons' => 'required|array|max:3',
            'persons.*' => 'integer|exists:users,id',
        ]);

        if ($request->hasFile('photo')) {
            // Se quiser apagar a anterior, adicione:
            // Storage::disk('public')->delete(str_replace('/storage/', '', $project->photo));
            $photoPath = $request->file('photo')->store('projects', 'public');
            $project->photo = Storage::url($photoPath);
        }

        $project->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'equipments' => $validated['equipments'] ?? null,
            'accepted' => $validated['accepted'] ?? $project->accepted,
            'hasPrototype' => $validated['hasPrototype'] ?? $project->hasPrototype,
        ]);

        $project->persons()->sync($validated['persons']);

        return response()->json(['message' => 'Projeto atualizado com sucesso!', 'project' => $project->load('persons')]);
    }

    public function destroy($id)
    {
        $project = Projeto::findOrFail($id);
        $project->delete();
        return response()->json(['message' => 'Projeto deletado com sucesso']);
    }

    public function accept($id)
    {
        $projeto = Projeto::findOrFail($id);
        $projeto->accepted = true;
        $projeto->save();

        return response()->json([
            'message' => 'Projeto aceito com sucesso.',
            'projeto' => $projeto
        ]);
    }
}
