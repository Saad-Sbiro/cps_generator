<?php

namespace App\Services;

use App\Models\Projet;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class RcExportService
{
    private array $styleTitle   = ['name' => 'Arial', 'size' => 16, 'bold' => true, 'color' => '6b2c00'];
    private array $styleHeading = ['name' => 'Arial', 'size' => 12, 'bold' => true, 'color' => '6b2c00'];
    private array $styleH3      = ['name' => 'Arial', 'size' => 11, 'bold' => true, 'color' => '333333'];
    private array $styleBody    = ['name' => 'Arial', 'size' => 10];
    private array $styleLabel   = ['name' => 'Arial', 'size' => 10, 'bold' => true];
    private array $paraCenter   = ['alignment' => 'center'];
    private array $paraNormal   = ['alignment' => 'left', 'spaceBefore' => 80, 'spaceAfter' => 80];
    private array $paraHeading  = ['alignment' => 'left', 'spaceBefore' => 200, 'spaceAfter' => 100];

    public function generate(Projet $projet): string
    {
        $projet->load(['projectArticles.article']);

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(10);

        $section = $phpWord->addSection([
            'marginTop'    => 1134,
            'marginBottom' => 1134,
            'marginLeft'   => 1418,
            'marginRight'  => 1134,
        ]);

        // ---------- PAGE DE GARDE ----------
        $logoPath = public_path('opein.png');
        if (file_exists($logoPath)) {
            $section->addImage($logoPath, [
                'width' => 120,
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
            ]);
            $section->addTextBreak(2);
        }

        $section->addText('ROYAUME DU MAROC', ['name' => 'Arial', 'size' => 14, 'bold' => true, 'color' => '333333'], $this->paraCenter);
        $section->addTextBreak(2);

        $section->addText('RÈGLEMENT DE CONSULTATION', ['name' => 'Arial', 'size' => 24, 'bold' => true, 'color' => '6b2c00'], $this->paraCenter);
        $section->addText('(RC)', ['name' => 'Arial', 'size' => 18, 'bold' => true, 'color' => '6b2c00'], $this->paraCenter);
        $section->addTextBreak(1);
        $section->addLine(['weight' => 2, 'color' => '6b2c00', 'width' => 450, 'height' => 0]);
        $section->addTextBreak(2);

        // ---------- PROJECT IDENTITY ON COVER ----------
        $this->addLabelValue($section, 'Référence :', $projet->reference);
        $this->addLabelValue($section, 'Intitulé :', $projet->intitule);
        $this->addLabelValue($section, 'Date :', $projet->date_creation?->format('d/m/Y') ?? '');
        if ($projet->maitre_ouvrage) {
            $this->addLabelValue($section, 'Maître d\'ouvrage :', $projet->maitre_ouvrage);
        }
        if ($projet->objet_marche) {
            $this->addLabelValue($section, 'Objet du marché :', $projet->objet_marche);
        }
        $section->addPageBreak();

        // ---------- RC SECTIONS ----------
        $rcSections = $projet->projectArticles
            ->filter(fn($s) => $s->article->type === 'RC')
            ->sortBy('ordre');

        if ($rcSections->isNotEmpty()) {
            foreach ($rcSections as $sp) {
                $section->addText($sp->article->titre, $this->styleHeading, $this->paraHeading);
                $this->addMultilineText($section, $sp->contenu_final);
                $section->addTextBreak(1);
            }
        } else {
            // Default RC content if no sections assigned
            $defaultSections = [
                ['ARTICLE 1 – OBJET DE LA CONSULTATION', "Le présent règlement de consultation a pour objet de définir les conditions dans lesquelles sera passé le marché relatif à :\n\n{$projet->intitule}"],
                ['ARTICLE 2 – MAÎTRE D\'OUVRAGE', $projet->maitre_ouvrage ?: 'À compléter'],
                ['ARTICLE 3 – NATURE DU MARCHÉ', "Marché à prix unitaires. Le marché sera conclu sur la base des prix unitaires.\nLes prix sont fermes et non révisables."],
                ['ARTICLE 4 – DÉLAI D\'EXÉCUTION', $projet->delai_execution ?: 'Le délai d\'exécution sera précisé dans l\'acte d\'engagement.'],
                ['ARTICLE 5 – FORME ET PRÉSENTATION DES OFFRES', "Les offres comprennent :\n- Le dossier administratif\n- Le dossier technique\n- L'offre financière"],
                ['ARTICLE 6 – CRITÈRES D\'ÉVALUATION', "Les offres techniques et financières seront évaluées selon les critères définis dans le présent règlement.\nLe marché sera attribué au soumissionnaire présentant l'offre la mieux-disante."],
            ];

            foreach ($defaultSections as [$titre, $contenu]) {
                $section->addText($titre, $this->styleHeading, $this->paraHeading);
                $this->addMultilineText($section, $contenu);
                $section->addTextBreak(1);
            }
        }

        // ---------- SAVE ----------
        $filename = 'RC_' . preg_replace('/[^A-Za-z0-9\-_]/', '_', $projet->reference) . '_' . date('Ymd_His') . '.docx';
        $dir      = storage_path('app/exports');
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $path = $dir . '/' . $filename;

        IOFactory::createWriter($phpWord, 'Word2007')->save($path);

        return $path;
    }

    private function addMultilineText($section, string $text): void
    {
        $lines = explode("\n", str_replace("\r\n", "\n", $text));
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
        $textRun = $section->addTextRun($this->paraNormal);
        $textRun->addText($label . ' ', $this->styleLabel);
        $textRun->addText($value, $this->styleBody);
    }
}
