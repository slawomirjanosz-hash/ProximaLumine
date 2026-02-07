<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border as PhpBorder;
use PhpOffice\PhpSpreadsheet\Worksheet\Table;
use PhpOffice\PhpSpreadsheet\Worksheet\Table\TableStyleInfo;

class PartsExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{
    protected $parts;

    public function __construct($parts)
    {
        $this->parts = $parts;
    }

    public function collection()
    {
        return collect($this->parts->map(function ($p) {
            $description = $p->description ? wordwrap($p->description, 80, "\n", false) : '-';
            return [
                'produkt' => $p->name,
                'opis' => $description,
                'Dost.' => is_object($p->supplier) ? ($p->supplier->name ?? '-') : ($p->supplier ?? '-'),
                'Cena' => $p->net_price ?? '-',
                'Waluta' => $p->currency ?? '-',
                'kategoria' => $p->category->name ?? '-',
                'ilość' => $p->quantity,
                'lok.' => $p->location ?? '-',
                'Min' => $p->minimum_stock,
                'kod' => $p->qr_code ?? '-',
            ];
        })->toArray());
    }

    public function headings(): array
    {
        return ['produkt', 'opis', 'Dost.', 'Cena', 'Waluta', 'kategoria', 'ilość', 'lok.', 'Min', 'kod'];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $lastCol = $sheet->getHighestColumn();
                $range = "A1:{$lastCol}{$lastRow}";

                // Header style - light beige
                $sheet->getStyle("A1:{$lastCol}1")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF5F5DC');

                // Borders for all cells
                $sheet->getStyle($range)->getBorders()->getAllBorders()
                    ->setBorderStyle(PhpBorder::BORDER_THIN)
                    ->getColor()->setARGB('FF000000');

                // Alternating row color (light gray) for even rows starting from row 2
                for ($row = 2; $row <= $lastRow; $row += 2) {
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFECECEC');
                }

                // Ensure description column wraps text (column B)
                $sheet->getStyle("B2:B{$lastRow}")->getAlignment()->setWrapText(true);
                // Set column width for Opis (B) to roughly 80 characters and disable autosize for this column
                $sheet->getColumnDimension('B')->setAutoSize(false);
                $sheet->getColumnDimension('B')->setWidth(80);

                // Center align Kategoria (C) and Stan (D)
                $sheet->getStyle("C2:C{$lastRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D2:D{$lastRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                // Vertically center all data cells so single-line cells are centered next to wrapped descriptions
                $sheet->getStyle("A2:{$lastCol}{$lastRow}")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                // Try to add an Excel Table so it appears as a table in Excel (may be ignored on older lib versions)
                try {
                    $table = new Table('Table1', $range);
                    $styleInfo = new TableStyleInfo(true, true, 'TableStyleMedium9', true);
                    $table->setStyle($styleInfo);
                    $sheet->addTable($table);
                } catch (\Throwable $e) {
                    // ignore if table API isn't available
                }
            },
        ];
    }
}
