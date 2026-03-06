<?php

namespace Database\Seeders;

use App\Models\PrixCatalogue;
use Illuminate\Database\Seeder;

class PrixCatalogueSeeder extends Seeder
{
    public function run(): void
    {
        $postes = [
            // INSTALLATION DE CHANTIER
            [
                'categorie' => 'INSTALLATION DE CHANTIER',
                'sous_categorie' => null,
                'designation' => 'Installation de chantier, repliement et nettoyage des lieux',
                'unite' => 'Forfait',
                'prix_unitaire_ht_defaut' => 50000.00
            ],
            // TERRASSEMENTS
            [
                'categorie' => 'TERRASSEMENTS',
                'sous_categorie' => 'Fouilles',
                'designation' => 'Fouilles en rigoles dans terrain de toute nature',
                'unite' => 'm3',
                'prix_unitaire_ht_defaut' => 85.00
            ],
            [
                'categorie' => 'TERRASSEMENTS',
                'sous_categorie' => 'Fouilles',
                'designation' => 'Fouilles en puits dans terrain de toute nature',
                'unite' => 'm3',
                'prix_unitaire_ht_defaut' => 95.00
            ],
            [
                'categorie' => 'TERRASSEMENTS',
                'sous_categorie' => 'Remblais',
                'designation' => 'Remblaiement des fouilles avec terres provenant des deblais',
                'unite' => 'm3',
                'prix_unitaire_ht_defaut' => 20.00
            ],
            [
                'categorie' => 'TERRASSEMENTS',
                'sous_categorie' => 'Deblais',
                'designation' => 'Evacuation des terres excedentaires y compris foisonnement à la decharge publique',
                'unite' => 'm3',
                'prix_unitaire_ht_defaut' => 45.00
            ],
            // ASSAINISSEMENT
            [
                'categorie' => 'ASSAINISSEMENT',
                'sous_categorie' => 'Canalisations PVC',
                'designation' => 'Canalisation en PVC serie assainissement PN 6 diametre 200 mm',
                'unite' => 'ml',
                'prix_unitaire_ht_defaut' => 120.00
            ],
            [
                'categorie' => 'ASSAINISSEMENT',
                'sous_categorie' => 'Canalisations PVC',
                'designation' => 'Canalisation en PVC serie assainissement PN 6 diametre 315 mm',
                'unite' => 'ml',
                'prix_unitaire_ht_defaut' => 250.00
            ],
            [
                'categorie' => 'ASSAINISSEMENT',
                'sous_categorie' => 'Regards',
                'designation' => 'Regard de visite 80x80 cm',
                'unite' => 'U',
                'prix_unitaire_ht_defaut' => 1500.00
            ],
            // BETON ARME EN FONDATION
            [
                'categorie' => 'BETON ARME EN FONDATION',
                'sous_categorie' => 'Beton',
                'designation' => 'Beton de propreté dosé a 150 kg/m3',
                'unite' => 'm3',
                'prix_unitaire_ht_defaut' => 500.00
            ],
            [
                'categorie' => 'BETON ARME EN FONDATION',
                'sous_categorie' => 'Beton',
                'designation' => 'Beton B25 pour semelles isolees ou filantes, amorces poteaux',
                'unite' => 'm3',
                'prix_unitaire_ht_defaut' => 1200.00
            ],
            [
                'categorie' => 'BETON ARME EN FONDATION',
                'sous_categorie' => 'Acier',
                'designation' => 'Aciers hautes adherences (FeE 500) façonnés et posés en fondation',
                'unite' => 'kg',
                'prix_unitaire_ht_defaut' => 13.50
            ],
            // BETON ARME EN ELEVATION
            [
                'categorie' => 'BETON ARME EN ELEVATION',
                'sous_categorie' => 'Beton',
                'designation' => 'Beton B25 pour poteaux, poutres et chainages',
                'unite' => 'm3',
                'prix_unitaire_ht_defaut' => 1350.00
            ],
            [
                'categorie' => 'BETON ARME EN ELEVATION',
                'sous_categorie' => 'Planchers',
                'designation' => 'Plancher en corps creux 16+4 compris poutrelles et dalle de compression',
                'unite' => 'm2',
                'prix_unitaire_ht_defaut' => 280.00
            ],
            [
                'categorie' => 'BETON ARME EN ELEVATION',
                'sous_categorie' => 'Acier',
                'designation' => 'Aciers hautes adherences (FeE 500) façonnés et posés en élévation',
                'unite' => 'kg',
                'prix_unitaire_ht_defaut' => 14.00
            ]
        ];

        foreach ($postes as $poste) {
            PrixCatalogue::firstOrCreate(
                [
                    'designation' => $poste['designation'],
                    'categorie'   => $poste['categorie']
                ],
                $poste
            );
        }
    }
}
