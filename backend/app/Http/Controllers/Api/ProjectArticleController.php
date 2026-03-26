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
use App\Notifications\ProjectEdited;

class ProjectArticleController extends Controller
{
    public function index(Projet $projet): JsonResponse
    {
        Gate::authorize('view', $projet);

        $sections = $projet->projectArticles()->with(['article', 'variant'])->orderBy('ordre')->get();
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
        

        if (!empty($validated['article_variant_id'])) {
            $variant = ArticleVariant::findOrFail($validated['article_variant_id']);
            $baseContenu = $variant->contenu;
            $variantId = $variant->id;
        } else {
            $defaultVariant = $article->defaultVariant();
            $baseContenu = $defaultVariant ? $defaultVariant->contenu : $article->contenu;
            $variantId = $defaultVariant ? $defaultVariant->id : null;
        }


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
        
        $this->notifyOwner($projet, 'a ajouté l\'article', $article->code ?? 'N/A');

        return response()->json($projectArticle, 201);
    }

    public function update(Request $request, Projet $projet, ProjectArticle $projectArticle): JsonResponse
    {
        Gate::authorize('update', $projet);
        $this->authorize_section($projet, $projectArticle);

        $validated = $request->validate([
            'ordre'         => 'sometimes|integer|min:0',
            'contenu_final' => 'sometimes|required|string',
        ]);

        $projectArticle->update($validated);
        $projectArticle->load(['article', 'variant']);

        $this->notifyOwner($projet, 'a modifié l\'article', $projectArticle->article->code ?? 'N/A');

        return response()->json($projectArticle);
    }

    public function destroy(Projet $projet, ProjectArticle $projectArticle): JsonResponse
    {
        Gate::authorize('update', $projet);
        $this->authorize_section($projet, $projectArticle);

        $code = $projectArticle->article->code ?? 'N/A';
        $projectArticle->delete();

        $this->notifyOwner($projet, 'a supprimé l\'article', $code);

        return response()->json(null, 204);
    }

    public function sync(Request $request, Projet $projet): JsonResponse
    {
        Gate::authorize('update', $projet);

        $validated = $request->validate([
            'articles'           => 'required|array',
            'articles.*.id'    => 'required|uuid|exists:project_articles,id',
            'articles.*.ordre' => 'required|integer|min:0',
        ]);

        foreach ($validated['articles'] as $item) {
            ProjectArticle::where('id', $item['id'])
                ->where('projet_id', $projet->id)
                ->update(['ordre' => $item['ordre']]);
        }

        return response()->json(['message' => 'Synchronisé']);
    }

    private function authorize_section(Projet $projet, ProjectArticle $projectArticle): void
    {
        if ($projectArticle->projet_id !== $projet->id) {
            abort(403, 'Section does not belong to this project');
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
