<?php

namespace App\Services\Importers;

use App\Models\Tenant\Customer;
use App\Models\Tenant\Device;
use App\Services\BaseImporter;

class DeviceImporter extends BaseImporter
{
    protected function mapRow(array $row): array
    {
        return [
            'imei' => trim($row['imei'] ?? ''),
            'serial_number' => trim($row['serial'] ?? $row['serial_number'] ?? ''),
            'brand' => trim($row['brand'] ?? ''),
            'model' => trim($row['model'] ?? ''),
            'storage' => trim($row['storage'] ?? ''),
            'color' => trim($row['color'] ?? ''),
            'customer_phone' => trim($row['customer'] ?? $row['customer_phone'] ?? ''),
        ];
    }

    protected function validate(array $mapped): ?string
    {
        if (empty($mapped['imei']) && empty($mapped['serial_number'])) {
            return 'IMEI atau Serial Number wajib diisi.';
        }

        if (! empty($mapped['imei']) && Device::where('imei', $mapped['imei'])->exists()) {
            return "IMEI {$mapped['imei']} sudah terdaftar.";
        }

        if (! empty($mapped['customer_phone']) && ! Customer::where('phone', $mapped['customer_phone'])->exists()) {
            return "Customer dengan telepon {$mapped['customer_phone']} tidak ditemukan.";
        }

        return null;
    }

    protected function importOne(array $mapped): void
    {
        $customerId = null;
        if (! empty($mapped['customer_phone'])) {
            $customerId = Customer::where('phone', $mapped['customer_phone'])->first()?->id;
        }

        Device::create([
            'customer_id' => $customerId,
            'imei' => $mapped['imei'],
            'serial_number' => $mapped['serial_number'],
            'brand' => $mapped['brand'],
            'model' => $mapped['model'],
            'storage' => $mapped['storage'],
            'color' => $mapped['color'],
        ]);
    }
}
