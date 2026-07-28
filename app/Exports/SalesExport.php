<?php

namespace App\Exports;

use App\Models\Tenant\Sale;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return Sale::with('customer');
    }

    public function headings(): array
    {
        return ['ID', 'Pelanggan', 'Total', 'Status', 'Tanggal'];
    }

    public function map($sale): array
    {
        return [
            $sale->id,
            $sale->customer?->name ?? '-',
            $sale->total,
            $sale->status,
            $sale->created_at->format('d/m/Y H:i'),
        ];
    }
}
