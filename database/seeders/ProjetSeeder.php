<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ArticleVariant;
use App\Models\PrixCatalogue;
use App\Models\ProjectArticle;
use App\Models\ProjectPrix;
use App\Models\Projet;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProjetSeeder extends Seeder
{
    public function run(): void
    {
        // Create (or reuse) the demo user
        $demoUser = User::firstOrCreate(
            ['email' => 'demo@cps-generator.ma'],
            [
                'name'     => 'Utilisateur Demo',
                'password' => Hash::make('password123'),
            ]
        );

        // Skip if demo project already owned by demo user
        if (Projet::where('reference', 'PRJ-2025-001')->where('user_id', $demoUser->id)->exists()) {
            return;
        }

        $projet = Projet::create([
            'user_id'              => $demoUser->id,
            'reference'            => 'PRJ-2025-001',
            'intitule'             => 'Rehabilitation et amenagement du siege social - Extension Batiment B',
            'date_creation'        => '2025-06-01',
            'taux_tva'             => 20.00,
            'inclure_brd_dans_cps' => true,
            'maitre_ouvrage'       => 'Ministere de l\'Equipement et de l\'Eau - Delegation Regionale de Rabat',
            'objet_marche'         => 'Travaux de rehabilitation et d\'amenagement du siege social, extension batiment B',
            'lieu'                 => 'Rabat, Maroc',
            'delai_execution'      => '6 mois',
        ]);

        // ---------- Attach articles ----------
        $articleCodes = [];
        for ($i = 1; $i <= 55; $i++) {
            $articleCodes[] = "ART{$i}";
        }
        $articleCodes = array_merge($articleCodes, ['RC_ART1', 'RC_ART2']);
        $ordre = 0;

        foreach ($articleCodes as $code) {
            $article = Article::where('code', $code)->first();
            if (!$article) continue;

            $variant = $article->defaultVariant();
            $baseContenu = $variant ? $variant->contenu : $article->contenu;

            $contenu = str_replace(
                ['{{OBJET}}', '{{DELAI}}', '{{REFERENCE}}', '{{INTITULE}}', '{{MAITRE_OUVRAGE}}', '{{LIEU}}'],
                [
                    $projet->objet_marche,
                    $projet->delai_execution,
                    $projet->reference,
                    $projet->intitule,
                    $projet->maitre_ouvrage,
                    $projet->lieu,
                ],
                $baseContenu
            );

            ProjectArticle::create([
                'projet_id'          => $projet->id,
                'article_id'         => $article->id,
                'article_variant_id' => $variant ? $variant->id : null,
                'ordre'              => $ordre++,
                'contenu_final'      => $contenu,
            ]);
        }

        // ---------- Attach prix ----------
        $lignesData = [
            ['categorie' => 'TERRASSEMENTS', 'designation_contains' => 'rigoles', 'qte' => 250.0, 'ordre' => 0],
            ['categorie' => 'TERRASSEMENTS', 'designation_contains' => 'puits',   'qte' => 45.5,  'ordre' => 1],
            ['categorie' => 'TERRASSEMENTS', 'designation_contains' => 'Remblai', 'qte' => 380.0, 'ordre' => 2],
            ['categorie' => 'BETON',         'designation_contains' => 'poteaux', 'qte' => 2.0,   'ordre' => 3],
        ];

        $numero = 1;
        foreach ($lignesData as $item) {
            $catalogueArticle = PrixCatalogue::where('categorie', 'like', "%{$item['categorie']}%")
                ->where('designation', 'ilike', "%{$item['designation_contains']}%")
                ->first();

            if (!$catalogueArticle) continue;

            ProjectPrix::create([
                'projet_id'            => $projet->id,
                'prix_catalogue_id' => $catalogueArticle->id,
                'quantite'             => $item['qte'],
                'prix_unitaire_ht'     => $catalogueArticle->prix_unitaire_ht_defaut,
                'ordre'                => $item['ordre'],
                'numero_prix'          => $numero++,
            ]);
        }
    }
}
