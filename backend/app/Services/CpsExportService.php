<?php

namespace App\Services;

use App\Models\Projet;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Font;

class CpsExportService
{
    private PhpWord $phpWord;


    private array $styleTitle    = ['name' => 'Arial', 'size' => 16, 'bold' => true, 'color' => '003366'];
    private array $styleHeading  = ['name' => 'Arial', 'size' => 13, 'bold' => true, 'color' => '1a3d6e'];
    private array $styleH3       = ['name' => 'Arial', 'size' => 11, 'bold' => true, 'color' => '333333'];
    private array $styleBody       = ['name' => 'Arial', 'size' => 10];
    private array $styleLabel      = ['name' => 'Arial', 'size' => 10, 'bold' => true];
    private array $styleCoverLine  = ['name' => 'Arial', 'size' => 14, 'bold' => true, 'color' => '003366'];
    private array $paraCenter      = ['alignment' => 'center'];
    private array $paraNormal      = ['alignment' => 'left', 'spaceBefore' => 80, 'spaceAfter' => 80];
    private array $paraHeading     = ['alignment' => 'left', 'spaceBefore' => 200, 'spaceAfter' => 100];
    private array $paraCover       = ['alignment' => 'center', 'spaceBefore' => 120, 'spaceAfter' => 120];
    private int $coverImageWidth   = 435;

    public function generate(Projet $projet): string
    {
        $projet->load(['projectPrix.prixCatalogue', 'projectArticles.article']);

        $this->phpWord = new PhpWord();
        $this->phpWord->setDefaultFontName('Arial');
        $this->phpWord->setDefaultFontSize(10);

        $section = $this->phpWord->addSection([
            'marginTop'    => 534,
            'marginBottom' => 1134,
            'marginLeft'   => 1418,
            'marginRight'  => 1134,
        ]);

        $this->phpWord->addTitleStyle(1, ['name' => 'Arial', 'size' => 12, 'bold' => true, 'color' => '003366'], ['spaceBefore' => 240, 'spaceAfter' => 120]);
        $this->phpWord->addTitleStyle(2, ['name' => 'Arial', 'size' => 11, 'bold' => true, 'color' => '1a3d6e'], ['spaceBefore' => 120, 'spaceAfter' => 60]);


        $logoPath = $this->resolveLogoPath();
        if ($logoPath !== null) {
            $coverRun = $section->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
            $coverRun->addImage($logoPath, ['width' => $this->coverImageWidth]);
            $section->addTextBreak(2);
        }

        $section->addText('ROYAUME DU MAROC', ['name' => 'Arial', 'size' => 14, 'bold' => true, 'color' => '333333'], $this->paraCenter);
        $section->addTextBreak(2);
        
        $section->addText('CAHIER DES PRESCRIPTIONS SPÉCIALES', ['name' => 'Arial', 'size' => 24, 'bold' => true, 'color' => '003366'], $this->paraCenter);
        $section->addText('(CPS)', ['name' => 'Arial', 'size' => 18, 'bold' => true, 'color' => '003366'], $this->paraCenter);
        $section->addText('STYLE_BUILD_CPS_20260309', ['name' => 'Arial', 'size' => 11, 'bold' => true, 'color' => 'CC0000'], $this->paraCenter);
        $section->addTextBreak(1);
        $section->addLine(['weight' => 2, 'color' => '003366', 'width' => 450, 'height' => 0]);
        $section->addTextBreak(2);


        $this->addLabelValue($section, 'Référence :', $projet->reference);
        $this->addLabelValue($section, 'Intitulé :', $projet->intitule);
        $this->addLabelValue($section, 'Date :', $projet->date_creation?->format('d/m/Y') ?? '');
        if ($projet->maitre_ouvrage) {
            $this->addLabelValue($section, 'Maître d\'ouvrage :', $projet->maitre_ouvrage);
        }
        if ($projet->objet_marche) {
            $this->addLabelValue($section, 'Objet du marché :', $projet->objet_marche);
        }
        if ($projet->lieu) {
            $this->addLabelValue($section, 'Lieu :', $projet->lieu);
        }
        if ($projet->delai_execution) {
            $this->addLabelValue($section, 'Délai d\'exécution :', $projet->delai_execution);
        }
        $section->addPageBreak();


        $section->addText('SOMMAIRE', $this->styleTitle, $this->paraCenter);
        $section->addTextBreak(1);
        $section->addTOC(['name' => 'Arial', 'size' => 11], ['name' => 'Arial', 'size' => 10], 1, 3);
        $section->addPageBreak();


        $section->addTitle('CAHIER DES CLAUSES ADMINISTRATIVES', 1);


        $adminSections = $projet->projectArticles
            ->filter(fn($s) => in_array($s->article->type, ['CPS_ADMIN', 'CPS_FIN']))
            ->sortBy('ordre');

        foreach ($adminSections as $sp) {
            $section->addTitle($sp->article->titre, 2);
            $this->addMultilineText($section, $sp->contenu_final);
            $section->addTextBreak(1);
        }


        $techSections = $projet->projectArticles
            ->filter(fn($s) => $s->article->type === 'CPS_TECH_COMMUNE')
            ->sortBy('ordre');

        if ($techSections->isNotEmpty()) {
            $section->addTitle('PARTIE COMMUNE TECHNIQUE', 1);
            foreach ($techSections as $sp) {
                $section->addTitle($sp->article->titre, 2);
                $this->addMultilineText($section, $sp->contenu_final);
                $section->addTextBreak(1);
            }
        }


        if ($projet->projectPrix->isNotEmpty()) {
            $section->addPageBreak();
            $section->addTitle('ARTICLE 56 - DESCRIPTION TECHNIQUE', 1);
            $section->addLine(['weight' => 1, 'color' => '999999', 'width' => 450, 'height' => 0]);

            foreach ($projet->projectPrix as $ligne) {
                $poste = $ligne->prixCatalogue;
                $section->addTextBreak(1);
                $section->addText(
                    "Prix N°{$ligne->numero_prix} : {$poste->designation}",
                    $this->styleH3,
                    $this->paraNormal
                );
                $section->addText("Unité : {$poste->unite}", $this->styleBody, $this->paraNormal);
                $this->addMultilineText($section, $poste->description_technique ?? '');
            }
        }


        if ($projet->inclure_brd_dans_cps && $projet->projectPrix->isNotEmpty()) {
            $section->addPageBreak();
            $section->addTitle('ARTICLE 57 - BORDEREAU DES PRIX ET DETAIL ESTIMATIF', 1);

            $tableStyle = [
                'borderSize'  => 6,
                'borderColor' => '999999',
                'cellMargin'  => 80,
                'width'       => 9000,
                'unit'        => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP,
            ];
            $this->phpWord->addTableStyle('brdTable', $tableStyle);
            $table = $section->addTable('brdTable');


            $table->addRow(400);
            $headerStyle = ['bgColor' => '1a3d6e'];
            $headerFont  = ['name' => 'Arial', 'size' => 9, 'bold' => true, 'color' => 'FFFFFF'];
            $table->addCell(500,  $headerStyle)->addText('N°',          $headerFont, ['alignment' => 'center']);
            $table->addCell(3500, $headerStyle)->addText('Désignation', $headerFont);
            $table->addCell(700,  $headerStyle)->addText('Unité',       $headerFont, ['alignment' => 'center']);
            $table->addCell(800,  $headerStyle)->addText('Quantité',    $headerFont, ['alignment' => 'center']);
            $table->addCell(1200, $headerStyle)->addText('PU HT (MAD)', $headerFont, ['alignment' => 'right']);
            $table->addCell(1300, $headerStyle)->addText('Total HT (MAD)', $headerFont, ['alignment' => 'right']);

            foreach ($projet->projectPrix as $ligne) {
                $table->addRow(300);
                $table->addCell(500)->addText((string)$ligne->numero_prix, $this->styleBody, ['alignment' => 'center']);
                $table->addCell(3500)->addText($ligne->prixCatalogue->designation, $this->styleBody);
                $table->addCell(700)->addText($ligne->prixCatalogue->unite, $this->styleBody, ['alignment' => 'center']);
                $table->addCell(800)->addText(number_format((float)$ligne->quantite, 2, ',', ' '), $this->styleBody, ['alignment' => 'right']);
                $table->addCell(1200)->addText(number_format((float)$ligne->prix_unitaire_ht, 2, ',', ' '), $this->styleBody, ['alignment' => 'right']);
                $table->addCell(1300)->addText(number_format((float)$ligne->total_ht, 2, ',', ' '), $this->styleBody, ['alignment' => 'right']);
            }


            $totalHt  = $projet->total_ht;
            $totalTva = $projet->total_tva;
            $totalTtc = $projet->total_ttc;

            $grayBg = ['bgColor' => 'f0f0f0'];
            $blueBg = ['bgColor' => 'dde8f5'];

            $table->addRow(350);
            $table->addCell(6500, array_merge($grayBg, ['gridSpan' => 5]))->addText('TOTAL HT', $this->styleLabel, ['alignment' => 'right']);
            $table->addCell(1300, $grayBg)->addText(number_format($totalHt, 2, ',', ' ') . ' MAD', $this->styleLabel, ['alignment' => 'right']);

            $table->addRow(350);
            $table->addCell(6500, array_merge($grayBg, ['gridSpan' => 5]))->addText("TVA ({$projet->taux_tva}%)", $this->styleLabel, ['alignment' => 'right']);
            $table->addCell(1300, $grayBg)->addText(number_format($totalTva, 2, ',', ' ') . ' MAD', $this->styleLabel, ['alignment' => 'right']);

            $table->addRow(400);
            $table->addCell(6500, array_merge($blueBg, ['gridSpan' => 5]))->addText('TOTAL TTC', ['name' => 'Arial', 'size' => 10, 'bold' => true, 'color' => '003366'], ['alignment' => 'right']);
            $table->addCell(1300, $blueBg)->addText(number_format($totalTtc, 2, ',', ' ') . ' MAD', ['name' => 'Arial', 'size' => 10, 'bold' => true, 'color' => '003366'], ['alignment' => 'right']);
        }


        $section->addTextBreak(2);
        $section->addText('CONDITIONS FINANCIÈRES', $this->styleHeading, $this->paraHeading);
        $section->addText("Taux de TVA applicable : {$projet->taux_tva}%", $this->styleBody, $this->paraNormal);
        $section->addText('Montant TOTAL HT : ' . number_format($projet->total_ht, 2, ',', ' ') . ' MAD', $this->styleBody, $this->paraNormal);
        $section->addText('Montant TVA : ' . number_format($projet->total_tva, 2, ',', ' ') . ' MAD', $this->styleBody, $this->paraNormal);
        $section->addText('Montant TOTAL TTC : ' . number_format($projet->total_ttc, 2, ',', ' ') . ' MAD', $this->styleLabel, $this->paraNormal);


        $filename = 'CPS_V2_' . preg_replace('/[^A-Za-z0-9\-_]/', '_', $projet->reference) . '_' . date('Ymd_His') . '.docx';
        $dir      = storage_path('app/exports');
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $path = $dir . '/' . $filename;

        IOFactory::createWriter($this->phpWord, 'Word2007')->save($path);

        return $path;
    }

    private function addMultilineText($section, ?string $text): void
    {
        $safeText = (string) ($text ?? '');
        $lines = explode("\n", str_replace("\r\n", "\n", $safeText));

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                $section->addTextBreak(1);
            } else {
                $section->addText($trimmed, $this->styleBody, $this->paraNormal);
            }
        }
    }

    private function addLabelValue($section, string $label, string $value): void
    {
        $line = trim($label . ' ' . $value);
        $run = $section->addTextRun($this->paraCover);
        $run->addText($line, $this->styleCoverLine);
    }

    private function resolveLogoPath(): ?string
    {
        $candidates = [
            public_path('logo.png'),

        ];

        foreach ($candidates as $path) {
            if (is_string($path) && file_exists($path)) {
                return $path;
            }
        }

        return null;
    }
}
