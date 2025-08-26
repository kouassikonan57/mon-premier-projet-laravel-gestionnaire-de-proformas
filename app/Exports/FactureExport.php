<?php

namespace App\Exports;

use App\Models\Facture;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class FactureExport implements FromArray, WithHeadings, WithTitle, WithEvents
{
    protected Facture $facture;

    public function __construct(Facture $facture)
    {
        $this->facture = $facture;
    }

    public function array(): array
    {
        $rows = [];
        $totalHT = 0;

        foreach ($this->facture->articles as $a) {
            $ht = $a->quantity * $a->unit_price;
            $totalHT += $ht;

            $rows[] = [
                'Référence'              => $this->facture->reference,
                'Date'                   => $this->facture->date->format('d/m/Y'),
                'Client'                 => $this->facture->client->name,
                'Désignation'            => $a->designation,
                'Quantité'               => $a->quantity,
                'Prix unitaire (F CFA)'  => number_format($a->unit_price, 0, ',', ' '),
                'Total HT (F CFA)'       => number_format($ht, 0, ',', ' ')
            ];
        }

        $tva = $totalHT * 0.18;
        $ttc = $totalHT + $tva;

        $rows[] = ['', '', '', '', '', 'Total HT', number_format($totalHT, 0, ',', ' ')];
        $rows[] = ['', '', '', '', '', 'TVA (18%)', number_format($tva, 0, ',', ' ')];
        $rows[] = ['', '', '', '', '', 'Total TTC', number_format($ttc, 0, ',', ' ')];

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
        return 'Facture ' . $this->facture->reference;
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $sheet = $event->getSheet()->getDelegate();

                $filiale = $this->facture->filiale;
                $logoPath = public_path($filiale->logo_path ?? 'images/default-logo.png');

                // Logo
                if (file_exists($logoPath)) {
                    $drawing = new Drawing();
                    $drawing->setName('Logo');
                    $drawing->setPath($logoPath);
                    $drawing->setHeight(60);
                    $drawing->setCoordinates('A1');
                    $drawing->setWorksheet($sheet);
                }

                // Titre
                $sheet->setCellValue('C1', 'FACTURE');
                $sheet->mergeCells('C1:F1');
                $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('C1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                // Infos
                $sheet->setCellValue('A3', 'Référence :');
                $sheet->setCellValue('B3', $this->facture->reference);
                $sheet->setCellValue('A4', 'Date :');
                $sheet->setCellValue('B4', $this->facture->date->format('d/m/Y'));
                $sheet->setCellValue('A5', 'Client :');
                $sheet->setCellValue('B5', $this->facture->client->name);

                // Style en-têtes
                $headerRange = 'A7:G7';
                $sheet->getStyle($headerRange)->getFont()->setBold(true);
                $sheet->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('D9D9D9');
                $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                // Colonnes
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

                $rowCount = count($this->facture->articles) + 7;
                $tableRange = "A7:G{$rowCount}";

                // Bordures
                $styleArray = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ];
                $sheet->getStyle($tableRange)->applyFromArray($styleArray);

                // Footer texte filiale
                $footerRow = $rowCount + 2;
                $footerText = $this->facture->filiale->footer_text ?? '';

                if (!empty($footerText)) {
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
                        ],
                    ]);
                }
            }
        ];
    }
}
