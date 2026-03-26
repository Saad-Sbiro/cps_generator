<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PrixCatalogue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrixCatalogueController extends Controller
{
    public function categories(): JsonResponse
    {
        $categories = PrixCatalogue::where(function ($q) {
                $q->where('user_id', auth()->id())
                  ->orWhereNull('user_id');
            })
            ->select('categorie')
            ->distinct()
            ->pluck('categorie');

        return response()->json($categories);
    }

    public function index(Request $request): JsonResponse
    {
        $query = PrixCatalogue::where(function ($q) {
            $q->where('user_id', auth()->id())
              ->orWhereNull('user_id');
        });

        if ($cat = $request->get('categorie')) {
            $query->where('categorie', $cat);
        }
        if ($search = $request->get('search') ?? $request->get('query')) {
            $query->where('designation', 'ilike', "%{$search}%");
        }

        return response()->json($query->orderBy('categorie')->orderBy('designation')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'categorie'               => 'required|string|max:100',
            'sous_categorie'          => 'nullable|string|max:100',
            'designation'             => 'required|string',
            'unite'                   => 'required|string|max:50',
            'type_poste'              => 'nullable|string|in:quantitatif,forfait',
            'description_technique'    => 'nullable|string',
            'prix_unitaire_ht_defaut' => 'required|numeric|min:0',
        ]);

        $validated['user_id'] = auth()->id();
        $article = PrixCatalogue::create($validated);

        return response()->json($article, 201);
    }

    public function show(PrixCatalogue $prixCatalogue): JsonResponse
    {
        $this->authorize_article($prixCatalogue);
        return response()->json($prixCatalogue);
    }

    public function update(Request $request, PrixCatalogue $prixCatalogue): JsonResponse
    {
        $this->authorize_article($prixCatalogue);

        $validated = $request->validate([
            'categorie'               => 'sometimes|required|string|max:100',
            'sous_categorie'          => 'nullable|string|max:100',
            'designation'             => 'sometimes|required|string',
            'unite'                   => 'sometimes|required|string|max:50',
            'type_poste'              => 'nullable|string|in:quantitatif,forfait',
            'description_technique'    => 'nullable|string',
            'prix_unitaire_ht_defaut' => 'sometimes|required|numeric|min:0',
        ]);

        $prixCatalogue->update($validated);
        return response()->json($prixCatalogue);
    }

    public function destroy(PrixCatalogue $prixCatalogue): JsonResponse
    {
        $this->authorize_article($prixCatalogue);
        $prixCatalogue->delete();
        return response()->json(null, 204);
    }

    private function authorize_article(PrixCatalogue $prixCatalogue): void
    {
        if ($prixCatalogue->user_id !== null && $prixCatalogue->user_id !== auth()->id()) {
            abort(403, 'Article does not belong to you');
        }
    }
}
