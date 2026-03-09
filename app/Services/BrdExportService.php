<?php

namespace App\Services;

use App\Models\Projet;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

class BrdExportService
{
    public function generate(Projet $projet): string
    {
        $projet->load(['projectPrix.prixCatalogue']);
        $projectPrix = $projet->projectPrix;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('BRD');


        $logoPath = public_path('opein.png');
        if (file_exists($logoPath)) {
            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setName('Logo');
            $drawing->setDescription('Logo');
            $drawing->setPath($logoPath);
            $drawing->setCoordinates('A1');
            $drawing->setHeight(60);
            $drawing->setOffsetX(10);
            $drawing->setOffsetY(10);
            $drawing->setWorksheet($sheet);
        }
        $sheet->getRowDimension(1)->setRowHeight(65);

        $sheet->mergeCells('A2:F2');
        $sheet->setCellValue('A2', 'BORDEREAU DU DÉTAIL ESTIMATIF');
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '1a3d6e']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(30);

        $sheet->mergeCells('A3:F3');
        $sheet->setCellValue('A3', $projet->intitule . ' — Réf. ' . $projet->reference);
        $sheet->getStyle('A3')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '1a3d6e']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'dde8f5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(3)->setRowHeight(25);

        
        // HEADER ROW
        
        $headers = ['N°', 'Désignation', 'Unité', 'Quantité', 'PU HT (MAD)', 'Total HT (MAD)'];
        $cols    = ['A', 'B', 'C', 'D', 'E', 'F'];

        $headerRow = 5;
        foreach ($headers as $i => $header) {
            $cell = $cols[$i] . $headerRow;
            $sheet->setCellValue($cell, $header);
        }
        $sheet->getStyle("A{$headerRow}:F{$headerRow}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '1a3d6e']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(20);

        
        // DATA ROWS
        
        $dataStartRow = $headerRow + 1;
        $currentRow   = $dataStartRow;
        $prevCategory = null;

        foreach ($projectPrix as $ligne) {
            $poste    = $ligne->prixCatalogue;
            $category = $poste->categorie ?? 'Général';

            // Category group header
            if ($category !== $prevCategory) {
                $sheet->mergeCells("A{$currentRow}:F{$currentRow}");
                $sheet->setCellValue("A{$currentRow}", strtoupper($category));
                $sheet->getStyle("A{$currentRow}:F{$currentRow}")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '1a3d6e']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'e8f0fb']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
                ]);
                $sheet->getRowDimension($currentRow)->setRowHeight(18);
                $currentRow++;
                $prevCategory = $category;
            }

            // Data row
            $sheet->setCellValue("A{$currentRow}", $ligne->numero_prix);
            $sheet->setCellValue("B{$currentRow}", $poste->designation);
            $sheet->setCellValue("C{$currentRow}", $poste->unite);
            $sheet->setCellValue("D{$currentRow}", (float)$ligne->quantite);
            $sheet->setCellValue("E{$currentRow}", (float)$ligne->prix_unitaire_ht);
            $sheet->setCellValue("F{$currentRow}", "=D{$currentRow}*E{$currentRow}");

            // Row style
            $isEven = ($currentRow % 2 === 0);
            $bgColor = $isEven ? 'f8f9fa' : 'FFFFFF';
            $sheet->getStyle("A{$currentRow}:F{$currentRow}")->applyFromArray([
                'font'      => ['size' => 10],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => $bgColor]],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);

            // Number formats
            $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$currentRow}")->getNumberFormat()->setFormatCode('#,##0.000');
            $sheet->getStyle("E{$currentRow}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("F{$currentRow}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("D{$currentRow}:F{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $sheet->getRowDimension($currentRow)->setRowHeight(18);
            $currentRow++;
        }

        $lastDataRow = $currentRow - 1;

        
        // TOTALS
        
        $totalHtRow  = $currentRow + 1;
        $totalTvaRow = $currentRow + 2;
        $totalTtcRow = $currentRow + 3;

        // TOTAL HT
        $sheet->mergeCells("A{$totalHtRow}:E{$totalHtRow}");
        $sheet->setCellValue("A{$totalHtRow}", 'TOTAL HT');
        $sheet->setCellValue("F{$totalHtRow}", "=SUM(F{$dataStartRow}:F{$lastDataRow})");
        $sheet->getStyle("A{$totalHtRow}:F{$totalHtRow}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 10],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'f0f0f0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BBBBBB']]],
        ]);
        $sheet->getStyle("F{$totalHtRow}")->getNumberFormat()->setFormatCode('#,##0.00 "MAD"');
        $sheet->getRowDimension($totalHtRow)->setRowHeight(20);

        // TVA
        $tvaPct = (float)$projet->taux_tva;
        $sheet->mergeCells("A{$totalTvaRow}:E{$totalTvaRow}");
        $sheet->setCellValue("A{$totalTvaRow}", "TVA ({$tvaPct}%)");
        $sheet->setCellValue("F{$totalTvaRow}", "=F{$totalHtRow}*{$tvaPct}/100");
        $sheet->getStyle("A{$totalTvaRow}:F{$totalTvaRow}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 10],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'f0f0f0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BBBBBB']]],
        ]);
        $sheet->getStyle("F{$totalTvaRow}")->getNumberFormat()->setFormatCode('#,##0.00 "MAD"');
        $sheet->getRowDimension($totalTvaRow)->setRowHeight(20);

        // TOTAL TTC
        $sheet->mergeCells("A{$totalTtcRow}:E{$totalTtcRow}");
        $sheet->setCellValue("A{$totalTtcRow}", 'TOTAL TTC');
        $sheet->setCellValue("F{$totalTtcRow}", "=F{$totalHtRow}+F{$totalTvaRow}");
        $sheet->getStyle("A{$totalTtcRow}:F{$totalTtcRow}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '003366']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'dde8f5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '1a3d6e']]],
        ]);
        $sheet->getStyle("F{$totalTtcRow}")->getNumberFormat()->setFormatCode('#,##0.00 "MAD"');
        $sheet->getRowDimension($totalTtcRow)->setRowHeight(22);

        
        // COLUMN WIDTHS
        
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(45);
        $sheet->getColumnDimension('C')->setWidth(10);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(16);
        $sheet->getColumnDimension('F')->setWidth(18);

        // Freeze header
        $sheet->freezePane("A{$dataStartRow}");

        
        // SAVE
        
        $filename = 'BRD_' . preg_replace('/[^A-Za-z0-9\-_]/', '_', $projet->reference) . '_' . date('Ymd_His') . '.xlsx';
        $dir      = storage_path('app/exports');
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $path = $dir . '/' . $filename;

        (new Xlsx($spreadsheet))->save($path);

        return $path;
    }
}
