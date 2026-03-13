<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleVariant;
use App\Models\ProjectArticle;
use App\Models\Projet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjectArticleController extends Controller
{
    public function index(Projet $projet): JsonResponse
    {
        Gate::authorize('view', $projet);

        $sections = $projet->projectArticles()->with(['article', 'variant'])->get();
        return response()->json($sections);
    }

    public function store(Request $request, Projet $projet): JsonResponse
    {
        Gate::authorize('update', $projet);

        $validated = $request->validate([
            'article_id'         => 'required|exists:articles,id',
            'article_variant_id' => 'nullable|exists:article_variants,id',
            'ordre'              => 'required|integer|min:0',
        ]);

        $article = Article::findOrFail($validated['article_id']);
        
        // Use variant if provided, otherwise default variant, otherwise base content
        if (!empty($validated['article_variant_id'])) {
            $variant = ArticleVariant::findOrFail($validated['article_variant_id']);
            $baseContenu = $variant->contenu;
            $variantId = $variant->id;
        } else {
            $defaultVariant = $article->defaultVariant();
            $baseContenu = $defaultVariant ? $defaultVariant->contenu : $article->contenu;
            $variantId = $defaultVariant ? $defaultVariant->id : null;
        }

        // Resolve placeholders from project data
        $contenu = str_replace(
            ['{{OBJET}}', '{{DELAI}}', '{{REFERENCE}}', '{{INTITULE}}', '{{MAITRE_OUVRAGE}}', '{{LIEU}}'],
            [
                $projet->objet_marche ?? '',
                $projet->delai_execution ?? '',
                $projet->reference,
                $projet->intitule,
                $projet->maitre_ouvrage ?? '',
                $projet->lieu ?? '',
            ],
            $baseContenu
        );

        $projectArticle = ProjectArticle::create([
            'projet_id'          => $projet->id,
            'article_id'         => $article->id,
            'article_variant_id' => $variantId,
            'ordre'              => $validated['ordre'],
            'contenu_final'      => $contenu,
        ]);

        $projectArticle->load(['article', 'variant']);
        return response()->json($projectArticle, 201);
    }

    public function update(Request $request, Projet $projet, ProjectArticle $article): JsonResponse
    {
        Gate::authorize('update', $projet);
        $this->authorize_section($projet, $article);

        $validated = $request->validate([
            'ordre'         => 'sometimes|integer|min:0',
            'contenu_final' => 'sometimes|required|string',
        ]);

        $article->update($validated);
        $article->load(['article', 'variant']);

        return response()->json($article);
    }

    public function destroy(Projet $projet, ProjectArticle $article): JsonResponse
    {
        Gate::authorize('update', $projet);
        $this->authorize_section($projet, $article);

        $article->delete();
        return response()->json(null, 204);
    }

    public function reorder(Request $request, Projet $projet): JsonResponse
    {
        Gate::authorize('update', $projet);

        $validated = $request->validate([
            'order'   => 'required|array',
            'order.*' => 'uuid|exists:project_articles,id',
        ]);

        foreach ($validated['order'] as $index => $id) {
            ProjectArticle::where('id', $id)
                ->where('projet_id', $projet->id)
                ->update(['ordre' => $index]);
        }

        return response()->json(['message' => 'Reordered']);
    }

    private function authorize_section(Projet $projet, ProjectArticle $article): void
    {
        if ($article->projet_id !== $projet->id) {
            abort(403, 'Section does not belong to this project');
        }
    }
}
