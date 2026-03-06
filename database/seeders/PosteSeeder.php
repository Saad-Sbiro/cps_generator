<?php

namespace Database\Seeders;

use App\Models\CataloguePoste;
use Illuminate\Database\Seeder;

class PosteSeeder extends Seeder
{
    public function run(): void
    {
        $postes = [
            // ──────────────── GÉNIE CIVIL ────────────────
            [
                'designation'            => 'Terrassements généraux en déblai',
                'unite'                  => 'm³',
                'prix_unitaire_ht_defaut'=> 85.00,
                'description_technique'  => "Terrassements en déblai toutes natures de terrains confondues (rocher exclu), y compris :\n- Décapage de la terre végétale sur une épaisseur de 20 cm\n- Fouilles en toutes natures de terrain sauf rocher\n- Évacuation des déblais excédentaires vers des zones de dépôt autorisées\n- Mise en forme, réglage et compactage du fond de fouille\n- Arrosage et compactage à 95% de l'OPM\nLa prestation comprend tous les travaux nécessaires à la bonne exécution des terrassements.",
                'categorie'              => 'Génie Civil',
            ],
            [
                'designation'            => 'Béton armé pour fondations superficielles',
                'unite'                  => 'm³',
                'prix_unitaire_ht_defaut'=> 1850.00,
                'description_technique'  => "Béton armé dosé à 350 kg/m³ de ciment CPJ 45 pour fondations, y compris :\n- Fourniture et mise en place des armatures en acier HA Fe E40, selon plans d'exécution\n- Coffrage métallique ou bois\n- Mise en place, vibration et cure du béton\n- Décoffrage soigné\n- Résistance caractéristique fc28 ≥ 25 MPa\nLe béton doit répondre aux prescriptions de la norme NM 10.1.008.",
                'categorie'              => 'Génie Civil',
            ],
            [
                'designation'            => 'Maçonnerie en parpaings creux 20x20x40',
                'unite'                  => 'm²',
                'prix_unitaire_ht_defaut'=> 320.00,
                'description_technique'  => "Maçonnerie en parpaings creux de 20x20x40 cm, conformes à la norme NM 10.1.011, montées au mortier de ciment dosé à 300 kg/m³, y compris :\n- Traçage et implantation\n- Pose soignée avec joints de 1 cm\n- Chaînages horizontaux et verticaux selon plans\n- Enduit de liaison sur faces d'appui\nTolérance d'aplomb et de planéité : 5 mm sous la règle de 2 m.",
                'categorie'              => 'Génie Civil',
            ],
            [
                'designation'            => 'Chape de béton lissée e=5cm',
                'unite'                  => 'm²',
                'prix_unitaire_ht_defaut'=> 145.00,
                'description_technique'  => "Chape de béton dosée à 300 kg/m³ CPJ 45, épaisseur 5 cm, y compris :\n- Fourniture des matériaux\n- Application sur support préparé et humidifié\n- Mise en place avec règle vibrante\n- Lissage à la taloche mécanique\n- Cure par arrosage ou film plastique pendant 7 jours\nTolérances : planéité sous règle de 2 m ≤ 3 mm, différence de niveau entre deux points ≤ 5 mm/m.",
                'categorie'              => 'Génie Civil',
            ],

            // ──────────────── ÉLECTRICITÉ ────────────────
            [
                'designation'            => 'Câblage courant fort - câble cuivre 2x2.5mm²',
                'unite'                  => 'ML',
                'prix_unitaire_ht_defaut'=> 28.00,
                'description_technique'  => "Fourniture et pose de câble d'énergie cuivre rigide 2x2.5mm² sous conduit IRL, y compris :\n- Câble de type H07V-U ou équivalent conforme NM CEI 60227\n- Pose en goulotte ou en conduit encastré\n- Étiquetage et repérage de chaque circuit\n- Essais d'isolement et de continuité\n- Raccordement aux dispositifs de protection\nLa section minimale des conducteurs de protection est de 2.5 mm².",
                'categorie'              => 'Électricité',
            ],
            [
                'designation'            => 'Tableau électrique général basse tension',
                'unite'                  => 'U',
                'prix_unitaire_ht_defaut'=> 8500.00,
                'description_technique'  => "Fourniture et installation d'un tableau électrique général basse tension (TGBT), y compris :\n- Coffret métallique IP 65, degré IK 10\n- Disjoncteur général tétra 4x63A\n- Disjoncteurs divisionnaires selon nombre de circuits\n- Interrupteur différentiel 30 mA pour circuits prise de courant\n- Borniers de raccordement, peignes de jeu de barres\n- Repérage et schéma électrique plastifié\n- Essais et mise en service",
                'categorie'              => 'Électricité',
            ],
            [
                'designation'            => 'Prise de courant 2P+T 16A encastrée',
                'unite'                  => 'U',
                'prix_unitaire_ht_defaut'=> 180.00,
                'description_technique'  => "Fourniture et pose de prise de courant encastrée 2P+T 16A 250V, y compris :\n- Prise conforme NM CEI 60884\n- Boîte d'encastrement avec griffes\n- Câblage en câble H07V-U 1.5mm² min\n- Plaque de finition assortie\n- Test de bon fonctionnement\nHauteur de pose : 30 cm du sol sauf indication contraire du maître d'ouvrage.",
                'categorie'              => 'Électricité',
            ],
            [
                'designation'            => 'Luminaire panneau LED 60x60 cm 36W',
                'unite'                  => 'U',
                'prix_unitaire_ht_defaut'=> 650.00,
                'description_technique'  => "Fourniture et installation de luminaire panneau LED encastrable 60x60 cm, flux lumineux 3600 lm, y compris :\n- Panneau LED 36W, indice de rendu des couleurs IRC ≥ 80\n- Température de couleur 4000K (blanc neutre)\n- Durée de vie ≥ 50 000 heures\n- Câblage et raccordement\n- Essais photométriques\n- Garantie fabricant 3 ans minimum",
                'categorie'              => 'Électricité',
            ],

            // ──────────────── PLOMBERIE ────────────────
            [
                'designation'            => 'Tuyauterie PVC pression PN16 Ø 50mm',
                'unite'                  => 'ML',
                'prix_unitaire_ht_defaut'=> 95.00,
                'description_technique'  => "Fourniture et pose de tuyauterie PVC pression PN16 diamètre nominal 50 mm, y compris :\n- Tube PVC conforme NM ISO 4422\n- Raccords (coudes, tés, manchons) en PVC\n- Colliers de fixation tous les 80 cm\n- Joints et colle PVC homologués\n- Essai de pression à 1.5 fois la pression nominale pendant 30 minutes\n- Fourreaux de traversée de parois avec bourrelet d'étanchéité",
                'categorie'              => 'Plomberie',
            ],
            [
                'designation'            => 'Robinet d\'arrêt à sphère laiton DN 25',
                'unite'                  => 'U',
                'prix_unitaire_ht_defaut'=> 420.00,
                'description_technique'  => "Fourniture et pose de robinet d'arrêt à sphère en laiton chromé DN 25 (1 pouce), y compris :\n- Corps en laiton dégazeifié conforme EN 1982\n- Pression nominale PN 40\n- Poignée papillon en inox\n- Joints PTFE\n- Raccordement fileté à l'installation\n- Essai d'étanchéité après pose\n- Protection anticorrosion pour pose en faux plafond",
                'categorie'              => 'Plomberie',
            ],
            [
                'designation'            => 'Réseau d\'évacuation EU/EP PVC Ø 100mm',
                'unite'                  => 'ML',
                'prix_unitaire_ht_defaut'=> 135.00,
                'description_technique'  => "Fourniture et pose de canalisation PVC pour eaux usées/eaux pluviales diamètre 100 mm, y compris :\n- Tube PVC CR8 conforme NM EN 1401\n- Pentes minimales : 1.5% pour EU, 0.5% pour collecteurs EP\n- Joints caoutchouc à lèvre\n- Regards de visite tous les 20 m maximum\n- Étanchéité des joints de traversée de parois\n- Essai d'étanchéité à l'eau\n- Enrobage sable pour pose en fouille",
                'categorie'              => 'Plomberie',
            ],
        ];

        foreach ($postes as $data) {
            CataloguePoste::firstOrCreate(['designation' => $data['designation']], $data);
        }
    }
}
