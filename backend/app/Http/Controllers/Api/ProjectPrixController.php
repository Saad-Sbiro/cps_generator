<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PrixCatalogue;
use App\Models\ProjectPrix;
use App\Models\Projet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Notifications\ProjectEdited;

class ProjectPrixController extends Controller
{
    public function index(Projet $projet): JsonResponse
    {
        Gate::authorize('view', $projet);

        $lignes = $projet->projectPrix()->with('prixCatalogue')->orderBy('ordre')->get();
        return response()->json($lignes);
    }

    public function store(Request $request, Projet $projet): JsonResponse
    {
        Gate::authorize('update', $projet);

        $validated = $request->validate([
            'prix_catalogue_id' => 'required|exists:prix_catalogues,id',
            'unite'             => 'nullable|string|max:50',
            'quantite'             => 'required|numeric|min:0',
            'prix_unitaire_ht'     => 'required|numeric|min:0',
            'ordre'                => 'required|integer|min:0',
            'numero_prix'          => 'required|integer|min:1',
        ]);


        $article = PrixCatalogue::findOrFail($validated['prix_catalogue_id']);
        if ($article->user_id !== auth()->id() && $article->user_id !== null) {
            abort(403, 'Article non autorisé');
        }

        $projectPrix = ProjectPrix::create([
            'projet_id'            => $projet->id,
            'prix_catalogue_id' => $validated['prix_catalogue_id'],
            'unite'             => $validated['unite'] ?? $article->unite,
            'quantite'             => $validated['quantite'],
            'prix_unitaire_ht'     => $validated['prix_unitaire_ht'],
            'ordre'                => $validated['ordre'],
            'numero_prix'          => $validated['numero_prix'],
        ]);

        $projectPrix->load('prixCatalogue');
        
        $this->notifyOwner($projet, 'a ajouté le prix', $article->numero_prix ?? 'N/A');

        return response()->json($projectPrix, 201);
    }

    public function update(Request $request, Projet $projet, ProjectPrix $prix): JsonResponse
    {
        Gate::authorize('update', $projet);
        $this->authorize_ligne($projet, $prix);

        $validated = $request->validate([
            'unite'            => 'nullable|string|max:50',
            'quantite'         => 'sometimes|required|numeric|min:0',
            'prix_unitaire_ht' => 'sometimes|required|numeric|min:0',
            'ordre'            => 'sometimes|integer|min:0',
            'numero_prix'      => 'sometimes|integer|min:1',
        ]);

        $prix->update($validated);
        $prix->load('prixCatalogue');
        
        $this->notifyOwner($projet, 'a modifié le prix', $prix->prixCatalogue->numero_prix ?? 'N/A');

        return response()->json($prix);
    }

    public function destroy(Projet $projet, ProjectPrix $prix): JsonResponse
    {
        Gate::authorize('update', $projet);
        $this->authorize_ligne($projet, $prix);

        $numero = $prix->prixCatalogue->numero_prix ?? 'N/A';
        $prix->delete();

        $this->notifyOwner($projet, 'a supprimé le prix', $numero);

        return response()->json(null, 204);
    }

    public function sync(Request $request, Projet $projet): JsonResponse
    {
        Gate::authorize('update', $projet);

        $validated = $request->validate([
            'prix'           => 'required|array',
            'prix.*.id'    => 'required|uuid|exists:project_prix,id',
            'prix.*.ordre' => 'required|integer|min:0',
        ]);

        foreach ($validated['prix'] as $item) {
            ProjectPrix::where('id', $item['id'])
                ->where('projet_id', $projet->id)
                ->update(['ordre' => $item['ordre'], 'numero_prix' => $item['ordre'] + 1]);
        }

        return response()->json(['message' => 'Synchronisé']);
    }

    private function authorize_ligne(Projet $projet, ProjectPrix $prix): void
    {
        if ($prix->projet_id !== $projet->id) {
            abort(403, 'Prix item does not belong to this project');
        }
    }

    private function notifyOwner(Projet $projet, string $action, string $details): void
    {
        $user = request()->user();
        if ($projet->user && $user && (string) $projet->user_id !== (string) $user->id) {
            $projet->user->notify(new ProjectEdited([
                'projet_id' => $projet->id,
                'projet_title' => $projet->intitule,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'action' => $action,
                'message' => $user->name . ' ' . $action . ' "' . $details . '" dans le projet "' . $projet->intitule . '".'
            ]));
        }
    }
}
