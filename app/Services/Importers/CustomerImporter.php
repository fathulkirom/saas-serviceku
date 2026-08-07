<?php

namespace App\Services\Importers;

use App\Models\Tenant\Customer;
use App\Services\BaseImporter;

class CustomerImporter extends BaseImporter
{
    protected function mapRow(array $row): array
    {
        return [
            'name' => trim($row['nama'] ?? $row['name'] ?? ''),
            'phone' => trim($row['telepon'] ?? $row['phone'] ?? ''),
            'phone_secondary' => trim($row['telepon_kedua'] ?? $row['phone_secondary'] ?? ''),
            'email' => trim($row['email'] ?? ''),
            'address' => trim($row['alamat'] ?? $row['address'] ?? ''),
            'is_member' => in_array(strtolower(trim($row['member'] ?? $row['is_member'] ?? '')), ['yes', 'ya', '1', 'true']),
        ];
    }

    protected function validate(array $mapped): ?string
    {
        if (empty($mapped['name'])) {
            return 'Nama wajib diisi.';
        }

        if (empty($mapped['phone'])) {
            return 'Telepon wajib diisi.';
        }

        if (Customer::where('phone', $mapped['phone'])->exists()) {
            return "Telepon {$mapped['phone']} sudah terdaftar.";
        }

        return null;
    }

    protected function importOne(array $mapped): void
    {
        Customer::create($mapped + ['branch_id' => auth()->user()?->branch_id ?? 1]);
    }
}
