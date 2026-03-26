<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProjectSummaryExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    private Collection $summary;

    public function __construct(Collection $summary)
    {
        $this->summary = $summary;
    }

    public function collection(): Collection
    {
        return $this->summary->map(function (array $item) {
            $part = $item['part'];
            $description = $part->description
                ? preg_replace('/\s+/', ' ', str_replace(["\r", "\n"], ' ', $part->description))
                : '-';

            return [
                'Nazwa produktu' => $part->name,
                'Kod QR (opis)' => $part->qr_code ?? '-',
                'Opis' => $description,
                'Kategoria' => $part->category?->name ?? '-',
                'Dostawca' => $part->supplier ?? '-',
                'Lok' => $part->location ?? '-',
                'Laczna ilosc w projekcie' => $item['total_quantity'],
            ];
        });
    }

    public function headings(): array
    {
        return ['Nazwa produktu', 'Kod QR (opis)', 'Opis', 'Kategoria', 'Dostawca', 'Lok', 'Laczna ilosc w projekcie'];
    }
}
