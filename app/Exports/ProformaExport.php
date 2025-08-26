<?php

namespace App\Exports;

use App\Models\Proforma;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class ProformaExport implements FromArray, WithHeadings, WithTitle, WithEvents
{
    protected Proforma $proforma;

    public function __construct(Proforma $proforma)
    {
        $this->proforma = $proforma;
    }

    public function array(): array
    {
        $rows = [];
        $totalHT = 0;

        foreach ($this->proforma->articles as $a) {
            $ht = $a->quantity * $a->unit_price;
            $totalHT += $ht;

            $rows[] = [
                $this->proforma->reference,
                $this->proforma->date->format('d/m/Y'),
                $this->proforma->client->name ?? 'Client inconnu',
                $a->designation,
                $a->quantity,
                $a->unit_price, // No number_format, keep as float
                $ht             // Keep raw number
            ];
        }

        // Calcul TVA et TTC
        $tva = $totalHT * 0.18;
        $ttc = $totalHT + $tva;

        // Ajouter des lignes de totaux
        $rows[] = ['', '', '', '', '', 'Total HT', $totalHT];
        $rows[] = ['', '', '', '', '', 'TVA (18%)', $tva];
        $rows[] = ['', '', '', '', '', 'Total TTC', $ttc];

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Référence',
            'Date',
            'Client',
            'Désignation',
            'Quantité',
            'Prix unitaire (F CFA)',
            'Total HT (F CFA)'
        ];
    }

    public function title(): string
    {
        return 'Proforma ' . $this->proforma->reference;
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $sheet = $event->getSheet()->getDelegate();
                $filiale = $this->proforma->filiale;
                $logoPath = public_path($filiale->logo_path ?? 'images/default-logo.png');

                // Ajout du logo s'il existe
                if (file_exists($logoPath)) {
                    $drawing = new Drawing();
                    $drawing->setName('Logo');
                    $drawing->setPath($logoPath);
                    $drawing->setHeight(60);
                    $drawing->setCoordinates('A1');
                    $drawing->setWorksheet($sheet);
                }

                // Titre centré
                $sheet->setCellValue('C1', 'PROFORMA');
                $sheet->mergeCells('C1:G1');
                $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('C1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                // Infos principales
                $sheet->setCellValue('A3', 'Référence :');
                $sheet->setCellValue('B3', $this->proforma->reference);
                $sheet->setCellValue('A4', 'Date :');
                $sheet->setCellValue('B4', $this->proforma->date->format('d/m/Y'));
                $sheet->setCellValue('A5', 'Client :');
                $sheet->setCellValue('B5', $this->proforma->client->name ?? 'Client inconnu');

                // Largeur des colonnes
                $sheet->getColumnDimension('A')->setWidth(15);
                $sheet->getColumnDimension('B')->setWidth(12);
                $sheet->getColumnDimension('C')->setWidth(25);
                $sheet->getColumnDimension('D')->setWidth(30);
                $sheet->getColumnDimension('E')->setWidth(12);
                $sheet->getColumnDimension('F')->setWidth(20);
                $sheet->getColumnDimension('G')->setWidth(20);
            },

            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $rowCount = count($this->proforma->articles) + 7;

                // Mise en forme du tableau
                $headerRange = 'A7:G7';
                $tableRange = "A7:G{$rowCount}";

                $sheet->getStyle($headerRange)->getFont()->setBold(true);
                $sheet->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('D9D9D9');
                $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $styleArray = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ];
                $sheet->getStyle($tableRange)->applyFromArray($styleArray);

                // Ajout du footer s'il existe
                $footerText = $this->proforma->filiale->footer_text ?? null;
                if ($footerText) {
                    $footerRow = $rowCount + 5;
                    $sheet->setCellValue("A{$footerRow}", $footerText);
                    $sheet->mergeCells("A{$footerRow}:G" . ($footerRow + 2));
                    $sheet->getStyle("A{$footerRow}:G" . ($footerRow + 2))->applyFromArray([
                        'font' => [
                            'italic' => true,
                            'size' => 9,
                            'color' => ['rgb' => '888888'],
                        ],
                        'alignment' => [
                            'wrapText' => true,
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        ],
                    ]);
                }
            },
        ];
    }
}
