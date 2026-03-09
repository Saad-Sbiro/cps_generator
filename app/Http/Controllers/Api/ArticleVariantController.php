<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleVariantController extends Controller
{
    public function index(Article $article): JsonResponse
    {
        return response()->json($article->variants);
    }

    public function store(Request $request, Article $article): JsonResponse
    {
        $validated = $request->validate([
            'label'   => 'required|string|max:255',
            'contenu' => 'required|string',
        ]);

        $variant = $article->variants()->create([
            'label'      => $validated['label'],
            'contenu'    => $validated['contenu'],
            'is_default' => false,
        ]);

        return response()->json($variant, 201);
    }

    public function update(Request $request, Article $article, ArticleVariant $variant): JsonResponse
    {
        if ($variant->article_id !== $article->id) {
            abort(403, 'Variant not from this article');
        }

        $validated = $request->validate([
            'label'   => 'sometimes|required|string|max:255',
            'contenu' => 'sometimes|required|string',
        ]);

        $variant->update($validated);
        return response()->json($variant);
    }

    public function destroy(Article $article, ArticleVariant $variant): JsonResponse
    {
        if ($variant->article_id !== $article->id) {
            abort(403);
        }
        
        if ($variant->is_default) {
            abort(400, 'Cannot delete default variant');
        }

        $variant->delete();
        return response()->json(null, 204);
    }
}
