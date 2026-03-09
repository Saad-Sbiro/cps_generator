<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Article::query();

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        // load variants to show in UI
        return response()->json($query->with('variants')->orderBy('type')->orderBy('ordre_defaut')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code'         => 'required|string|max:50|unique:articles,code',
            'titre'        => 'required|string|max:300',
            'type'         => 'required|in:CPS_ADMIN,CPS_FIN,CPS_TECH_COMMUNE,RC',
            'contenu'      => 'required|string',
            'ordre_defaut' => 'nullable|integer|min:0',
        ]);

        $validated['ordre_defaut'] = $validated['ordre_defaut'] ?? 0;

        $article = Article::create($validated);
        
        // Auto-create default variant using the default contenu
        $article->variants()->create([
            'label' => 'Variante par défaut',
            'contenu' => $validated['contenu'],
            'is_default' => true
        ]);

        $article->load('variants');
        return response()->json($article, 201);
    }

    public function show(Article $article): JsonResponse
    {
        return response()->json($article->load('variants'));
    }

    public function update(Request $request, Article $article): JsonResponse
    {
        $validated = $request->validate([
            'code'         => 'sometimes|required|string|max:50|unique:articles,code,' . $article->id,
            'titre'        => 'sometimes|required|string|max:300',
            'type'         => 'sometimes|required|in:CPS_ADMIN,CPS_FIN,CPS_TECH_COMMUNE,RC',
            'contenu'      => 'sometimes|required|string',
            'ordre_defaut' => 'nullable|integer|min:0',
        ]);

        $article->update($validated);

        // If default contenu is updated, also update the default variant if it hasn't been modified heavily
        if (isset($validated['contenu'])) {
            $defaultVariant = $article->variants()->where('is_default', true)->first();
            if ($defaultVariant) {
                // To keep it simple, just update the default variant's content to match
                $defaultVariant->update(['contenu' => $validated['contenu']]);
            }
        }

        return response()->json($article->load('variants'));
    }

    public function destroy(Article $article): JsonResponse
    {
        $article->delete();
        return response()->json(null, 204);
    }
}
