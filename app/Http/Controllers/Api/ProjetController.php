<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Projet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjetController extends Controller
{
    public function index(): JsonResponse
    {
        $projets = Projet::withCount('projectPrix as lignes_count')
            ->where(function ($q) {
                $q->where('user_id', auth()->id())
                  ->orWhereNull('user_id');
            })
            ->orderByRaw('user_id IS NOT NULL') // Places null (shared) user_id before actual users
            ->orderByDesc('created_at')
            ->get();

        return response()->json($projets);
    }

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

        $projet = Projet::create(array_merge($validated, ['user_id' => auth()->id()]));

        return response()->json($projet, 201);
    }

    public function show(Projet $projet): JsonResponse
    {
        // Allow access if owner or seeded shared project (null user_id)
        abort_if($projet->user_id !== null && $projet->user_id !== auth()->id(), 403, 'Accès interdit');

        $projet->load([
            'projectPrix.prixCatalogue',
            'projectArticles.article',
            'projectArticles.variant',
            'exports',
        ]);

        return response()->json($projet);
    }

    public function update(Request $request, Projet $projet): JsonResponse
    {
        abort_if($projet->user_id !== null && $projet->user_id !== auth()->id(), 403, 'Accès interdit');

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
        $projet->load(['projectPrix.prixCatalogue', 'projectArticles.article', 'exports']);

        return response()->json($projet);
    }

    public function destroy(Projet $projet): JsonResponse
    {
        abort_if($projet->user_id !== null && $projet->user_id !== auth()->id(), 403, 'Accès interdit');
        $projet->delete();
        return response()->json(null, 204);
    }
}
