<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $adminTitles = [
            "OBJET DU MARCHE",
            "PROCEDURE DE PASSATION DU MARCHE",
            "CONSISTANCES DES PRESTATIONS",
            "MAITRISE D'OUVRAGE",
            "DEFINITIONS",
            "DOCUMENTS CONSTITUTIFS DU MARCHE",
            "IMPUTATION BUDGETAIRE",
            "REFERENCE AUX TEXTES GENERAUX ET SPECIAUX APPLICABLES AU MARCHE",
            "CONNAISSANCE DES LIEUX - SUJETIONS PARTICULIERES",
            "CONNAISSANCE DU DOSSIER",
            "VALIDITE ET DELAI DE NOTIFICATION DE L'APPROBATION DU MARCHE",
            "PIECES MISES A LA DISPOSITION DE L'ENTREPRENEUR",
            "ELECTION DU DOMICILE DE L'ENTREPRENEUR",
            "DOMICILIATION BANCAIRE",
            "NANTISSEMENT",
            "SOUS-TRAITANCE",
            "OBLIGATIONS DIVERSES DE L'ENTREPRENEUR",
            "RESPONSABILITES DE L'ENTREPRENEUR",
            "ECHANTILLONNAGE",
            "COMMENCEMENT DES TRAVAUX ET DELAI D'EXECUTION",
            "PENALITES POUR RETARDS",
            "NATURE DES PRIX",
            "REVISION DES PRIX",
            "CAUTIONNEMENT PROVISOIRE ET CAUTIONNEMENT DEFINITIF",
            "RETENUE DE GARANTIE",
            "ASSURANCES – RESPONSABILITE",
            "APPROVISIONNEMENTS",
            "AVANCES",
            "ENREGISTREMENT DE MARCHE",
            "PLANS DE RECOLEMENT",
            "RECEPTION PROVISOIRE",
            "INSTRUCTIONS – LETTRES",
            "MALFACONS",
            "PRESENCE DE L'ENTREPRENEUR- DIRECTION ET ENCADREMENT DU CHANTIER",
            "RÉUNIONS DE CHANTIER",
            "ENLEVEMENT DU MATERIEL ET DES MATERIAUX",
            "DELAI DE GARANTIE",
            "MODALITES DE REGLEMENT",
            "CHANGEMENT DANS LA MASSE DES OUVRAGES",
            "OUVRAGES OU TRAVAUX SUPPLEMENTAIRES",
            "RECEPTION DEFINITIVE",
            "DECES DE L'ENTREPRENEUR",
            "INCAPACITE CIVILE, D'EXERCICE, PHYSIQUE OU MENTALE DE L'ENTREPRENEUR",
            "AGREMENT DU MATÉRIEL",
            "LIQUIDATION OU REDRESSEMENT JUDICIAIRE",
            "RESILIATION DU MARCHE",
            "MESURES COERCITIVES",
            "CAS DE FORCE MAJEURE", 
            "REGLEMENT DES DIFFERENDS ET LITIGES",
            "INSTALLATION DE CHANTIER",
            "ORDONNANCEMENT ET PLANNING D'EXÉCUTION DES TRAVAUX",
            "LANGUE DE LIAISON",
            "PERSONNE CHARGÉE DU SUIVI DE l'EXÉCUTION DU MARCHE",
            "PROMOTION DE L'EMPLOI LOCAL",
            "CONSISTANCE DU MARCHE"
        ];

        foreach ($adminTitles as $index => $title) {
            $num = $index + 1;
            
            $article = Article::firstOrCreate(
                ['code' => "ART{$num}"],
                [
                    'titre'        => "ARTICLE - {$num} - {$title}",
                    'type'         => 'CPS_ADMIN',
                    'ordre_defaut' => $num,
                    'contenu'      => "Contenu de l'article $num à compléter...",
                ]
            );

            // Article model creates a default variant in its store method, but Seeder uses firstOrCreate
            // which bypasses the controller logic, so we must add it manually here
            if ($article->wasRecentlyCreated) {
                $article->variants()->create([
                    'label' => 'Variante par défaut',
                    'contenu' => $article->contenu,
                    'is_default' => true
                ]);
            }
        }

        // Keep 1 Tech Commune
        $tech = Article::firstOrCreate(
            ['code' => 'TECH_COMM'],
            [
                'titre'        => 'Prescriptions Techniques Communes',
                'type'         => 'CPS_TECH_COMMUNE',
                'ordre_defaut' => 1,
                'contenu'      => "PRESCRIPTIONS GÉNÉRALES APPLICABLES À TOUS LES TRAVAUX\n\n1. NORMES ET RÉGLEMENTATIONS\nTous les travaux seront exécutés conformément aux normes et réglementations marocaines en vigueur.",
            ]
        );
        if ($tech->wasRecentlyCreated) {
            $tech->variants()->create(['label' => 'Variante par défaut', 'contenu' => $tech->contenu, 'is_default' => true]);
        }

        // Keep RC
        $rcSections = [
            [
                'code'         => 'RC_ART1',
                'titre'        => 'Article 1 – Objet et Conditions de la Consultation',
                'type'         => 'RC',
                'ordre_defaut' => 1,
                'contenu'      => "1.1 Objet\nLe présent règlement de consultation a pour objet la définition des conditions...",
            ],
            [
                'code'         => 'RC_ART2',
                'titre'        => 'Article 2 – Conditions de Participation',
                'type'         => 'RC',
                'ordre_defaut' => 2,
                'contenu'      => "Peuvent participer à la présente consultation les personnes physiques ou morales...",
            ]
        ];

        foreach ($rcSections as $data) {
            $rc = Article::firstOrCreate(['code' => $data['code']], $data);
            if ($rc->wasRecentlyCreated) {
                $rc->variants()->create(['label' => 'Variante par défaut', 'contenu' => $rc->contenu, 'is_default' => true]);
            }
        }
    }
}
