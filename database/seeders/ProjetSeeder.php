<?php

namespace Database\Seeders;

use App\Models\CataloguePoste;
use App\Models\LignePrixProjet;
use App\Models\Projet;
use App\Models\SectionModele;
use App\Models\SectionProjet;
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

        // ---------- Attach sections ----------
        $sectionCodes = [];
        for ($i = 1; $i <= 55; $i++) {
            $sectionCodes[] = "ART{$i}";
        }
        $sectionCodes = array_merge($sectionCodes, ['RC_ART1', 'RC_ART2']);
        $ordre = 0;

        foreach ($sectionCodes as $code) {
            $modele = SectionModele::where('code', $code)->first();
            if (!$modele) continue;

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
                $modele->contenu
            );

            SectionProjet::create([
                'projet_id'         => $projet->id,
                'section_modele_id' => $modele->id,
                'ordre'             => $ordre++,
                'contenu_final'     => $contenu,
            ]);
        }

        // ---------- Attach BRD lignes ----------
        $lignesData = [
            ['categorie' => 'Genie Civil',   'designation_contains' => 'Terrassements',   'qte' => 250.0,  'ordre' => 0],
            ['categorie' => 'Genie Civil',   'designation_contains' => 'Beton arme',       'qte' => 45.5,   'ordre' => 1],
            ['categorie' => 'Genie Civil',   'designation_contains' => 'Maconne',          'qte' => 380.0,  'ordre' => 2],
            ['categorie' => 'Electricite',   'designation_contains' => 'Tableau',          'qte' => 2.0,    'ordre' => 3],
            ['categorie' => 'Electricite',   'designation_contains' => 'Cablage',          'qte' => 1200.0, 'ordre' => 4],
            ['categorie' => 'Electricite',   'designation_contains' => 'Luminaire',        'qte' => 48.0,   'ordre' => 5],
            ['categorie' => 'Plomberie',     'designation_contains' => 'Tuyauterie',       'qte' => 340.0,  'ordre' => 6],
            ['categorie' => 'Plomberie',     'designation_contains' => 'Robinet',          'qte' => 24.0,   'ordre' => 7],
        ];

        $numero = 1;
        foreach ($lignesData as $item) {
            $poste = CataloguePoste::where('categorie', 'like', "%{$item['categorie']}%")
                ->where('designation', 'ilike', "%{$item['designation_contains']}%")
                ->first();

            if (!$poste) continue;

            LignePrixProjet::create([
                'projet_id'        => $projet->id,
                'poste_id'         => $poste->id,
                'quantite'         => $item['qte'],
                'prix_unitaire_ht' => $poste->prix_unitaire_ht_defaut,
                'ordre'            => $item['ordre'],
                'numero_prix'      => $numero++,
            ]);
        }
    }
}
