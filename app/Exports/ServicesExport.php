<?php

namespace App\Exports;

use App\Models\Tenant\Service;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ServicesExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return Service::with('customer', 'technician');
    }

    public function headings(): array
    {
        return ['ID', 'Pelanggan', 'Unit', 'Status', 'Teknisi', 'Masuk', 'Selesai'];
    }

    public function map($service): array
    {
        return [
            $service->id,
            $service->customer?->name ?? '-',
            $service->tipe_unit ?? '-',
            $service->status,
            $service->technician?->name ?? '-',
            $service->created_at->format('d/m/Y H:i'),
            $service->completed_at ? $service->completed_at->format('d/m/Y H:i') : '-',
        ];
    }
}
