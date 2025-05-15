<?php

namespace App\Http\Controllers;

use App\Models\Projeto;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
            'persons' => 'required|array|max:4',
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
            'persons' => 'required|array|max:4',
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
    /**
     * Atualiza o período de votação de um projeto.
     */
    public function updateVotingPeriod(Request $request, $projectId)
    {
        $request->validate([
            'voting_starts_at' => 'nullable|date|after:now',
            'voting_ends_at' => 'nullable|date|after:voting_starts_at',
        ]);

        try {
            $projeto = Projeto::findOrFail($projectId);

            $projeto->voting_starts_at = $request->voting_starts_at
                ? Carbon::parse($request->voting_starts_at)
                : null;
            $projeto->voting_ends_at = $request->voting_ends_at
                ? Carbon::parse($request->voting_ends_at)
                : null;

            $projeto->save();

            return response()->json([
                'message' => 'Período de votação atualizado com sucesso.',
                'projeto' => [
                    'id' => $projeto->id,
                    'name' => $projeto->name,
                    'voting_starts_at' => $projeto->voting_starts_at?->toDateTimeString(),
                    'voting_ends_at' => $projeto->voting_ends_at?->toDateTimeString(),
                ],
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Projeto não encontrado.'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao atualizar período de votação.', 'error' => $e->getMessage()], 500);
        }
    }
}
