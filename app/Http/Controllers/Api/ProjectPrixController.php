<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PrixCatalogue;
use App\Models\ProjectPrix;
use App\Models\Projet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectPrixController extends Controller
{
    public function index(Projet $projet): JsonResponse
    {
        $lignes = $projet->projectPrix()->with('prixCatalogue')->get();
        return response()->json($lignes);
    }

    public function store(Request $request, Projet $projet): JsonResponse
    {
        $validated = $request->validate([
            'prix_catalogue_id' => 'required|exists:prix_catalogues,id',
            'quantite'             => 'required|numeric|min:0',
            'prix_unitaire_ht'     => 'required|numeric|min:0',
            'ordre'                => 'required|integer|min:0',
            'numero_prix'          => 'required|integer|min:1',
        ]);

        // Validation against user's catalogue
        $article = PrixCatalogue::findOrFail($validated['prix_catalogue_id']);
        if ($article->user_id !== auth()->id() && $article->user_id !== null) {
            abort(403, 'Article non autorisé');
        }

        $projectPrix = ProjectPrix::create([
            'projet_id'            => $projet->id,
            'prix_catalogue_id' => $validated['prix_catalogue_id'],
            'quantite'             => $validated['quantite'],
            'prix_unitaire_ht'     => $validated['prix_unitaire_ht'],
            'ordre'                => $validated['ordre'],
            'numero_prix'          => $validated['numero_prix'],
        ]);

        $projectPrix->load('prixCatalogue');
        return response()->json($projectPrix, 201);
    }

    public function update(Request $request, Projet $projet, ProjectPrix $prix): JsonResponse
    {
        $this->authorize_ligne($projet, $prix);

        $validated = $request->validate([
            'quantite'         => 'sometimes|required|numeric|min:0',
            'prix_unitaire_ht' => 'sometimes|required|numeric|min:0',
            'ordre'            => 'sometimes|integer|min:0',
            'numero_prix'      => 'sometimes|integer|min:1',
        ]);

        $prix->update($validated);
        $prix->load('prixCatalogue');
        return response()->json($prix);
    }

    public function destroy(Projet $projet, ProjectPrix $prix): JsonResponse
    {
        $this->authorize_ligne($projet, $prix);
        $prix->delete();
        return response()->json(null, 204);
    }

    public function reorder(Request $request, Projet $projet): JsonResponse
    {
        $validated = $request->validate([
            'order'   => 'required|array',
            'order.*' => 'uuid|exists:project_prix,id',
        ]);

        foreach ($validated['order'] as $index => $id) {
            ProjectPrix::where('id', $id)
                ->where('projet_id', $projet->id)
                ->update(['ordre' => $index, 'numero_prix' => $index + 1]);
        }

        return response()->json(['message' => 'Reordered']);
    }

    private function authorize_ligne(Projet $projet, ProjectPrix $prix): void
    {
        if ($prix->projet_id !== $projet->id) {
            abort(403, 'Prix item does not belong to this project');
        }
    }
}
