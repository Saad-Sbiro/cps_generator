<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Projet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjetController extends Controller
{
    /**
     * List projects owned by the user OR shared with the user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $projets = Projet::withCount('projectPrix as lignes_count')
            ->where('user_id', $user->id)
            ->orWhereHas('collaborators', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->orWhereNull('user_id') // seeded generic projects
            ->with(['user:id,name,email'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($projet) use ($user) {
                $projet->current_user_role = $user->roleInProjet($projet) ?? (($projet->user_id === $user->id) ? 'owner' : 'viewer');
                return $projet;
            });

        return response()->json($projets);
    }

    /**
     * Create a project.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reference'           => 'required|string|max:100|unique:projets,reference',
            'intitule'            => 'required|string|max:500',
            'date_creation'       => 'required|date',
            'taux_tva'            => 'required|numeric|min:0|max:100',
            'inclure_brd_dans_cps'=> 'required|boolean',
            'maitre_ouvrage'      => 'nullable|string|max:300',
            'objet_marche'        => 'nullable|string',
            'lieu'                => 'nullable|string|max:300',
            'delai_execution'     => 'nullable|string|max:200',
        ]);

        $user = $request->user();

        $projet = Projet::create(array_merge($validated, [
            'user_id' => $user->id,
        ]));

        $projet->load('user:id,name,email');

        return response()->json($projet, 201);
    }

    public function show(Projet $projet): JsonResponse
    {
        Gate::authorize('view', $projet);

        $projet->load([
            'user:id,name,email',
            'collaborators:id,name,email',
            'projectPrix.prixCatalogue',
            'projectArticles.article',
            'projectArticles.variant',
            'exports',
        ]);

        return response()->json($projet);
    }

    public function update(Request $request, Projet $projet): JsonResponse
    {
        Gate::authorize('update', $projet);

        $validated = $request->validate([
            'reference'           => 'sometimes|required|string|max:100|unique:projets,reference,' . $projet->id,
            'intitule'            => 'sometimes|required|string|max:500',
            'date_creation'       => 'sometimes|required|date',
            'taux_tva'            => 'sometimes|required|numeric|min:0|max:100',
            'inclure_brd_dans_cps'=> 'sometimes|required|boolean',
            'maitre_ouvrage'      => 'nullable|string|max:300',
            'objet_marche'        => 'nullable|string',
            'lieu'                => 'nullable|string|max:300',
            'delai_execution'     => 'nullable|string|max:200',
        ]);

        $projet->update($validated);
        $projet->load(['user:id,name,email', 'projectPrix.prixCatalogue', 'projectArticles.article', 'exports']);

        return response()->json($projet);
    }

    public function destroy(Projet $projet): JsonResponse
    {
        Gate::authorize('delete', $projet);

        $projet->delete();
        return response()->json(null, 204);
    }
}
