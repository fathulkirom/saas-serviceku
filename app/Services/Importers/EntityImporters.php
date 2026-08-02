<?php

namespace App\Services\Importers;

use App\Models\Tenant\Customer;
use App\Models\Tenant\Product;
use App\Models\Tenant\Device;
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
        if (empty($mapped['name'])) return 'Nama wajib diisi.';
        if (empty($mapped['phone'])) return 'Telepon wajib diisi.';

        $exists = Customer::where('phone', $mapped['phone'])->exists();
        if ($exists) return "Telepon {$mapped['phone']} sudah terdaftar.";

        return null;
    }

    protected function importOne(array $mapped): void
    {
        Customer::create($mapped + ['branch_id' => auth()->user()?->branch_id ?? 1]);
    }
}

class ProductImporter extends BaseImporter
{
    protected function mapRow(array $row): array
    {
        return [
            'sku' => trim($row['sku'] ?? ''),
            'name' => trim($row['nama'] ?? $row['name'] ?? ''),
            'category' => trim($row['kategori'] ?? $row['category'] ?? ''),
            'brand' => trim($row['brand'] ?? ''),
            'cost_price' => (float) ($row['harga_beli'] ?? $row['cost_price'] ?? 0),
            'selling_price' => (float) ($row['harga_jual'] ?? $row['selling_price'] ?? 0),
            'stock_quantity' => (int) ($row['stock'] ?? $row['stock_quantity'] ?? 0),
            'min_stock' => (int) ($row['minimum_stock'] ?? $row['min_stock'] ?? 0),
        ];
    }

    protected function validate(array $mapped): ?string
    {
        if (empty($mapped['name'])) return 'Nama produk wajib diisi.';
        if ($mapped['cost_price'] < 0) return 'Harga beli tidak valid.';
        return null;
    }

    protected function importOne(array $mapped): void
    {
        Product::create($mapped + ['branch_id' => auth()->user()?->branch_id ?? 1, 'code' => $mapped['sku'] ?? 'PRD' . uniqid()]);
    }
}

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
        if (empty($mapped['imei']) && empty($mapped['serial_number'])) return 'IMEI atau Serial Number wajib diisi.';
        if (!empty($mapped['imei']) && Device::where('imei', $mapped['imei'])->exists()) return "IMEI {$mapped['imei']} sudah terdaftar.";

        if (!empty($mapped['customer_phone'])) {
            $customer = Customer::where('phone', $mapped['customer_phone'])->first();
            if (!$customer) return "Customer dengan telepon {$mapped['customer_phone']} tidak ditemukan.";
        }

        return null;
    }

    protected function importOne(array $mapped): void
    {
        $customerId = null;
        if (!empty($mapped['customer_phone'])) {
            $customer = Customer::where('phone', $mapped['customer_phone'])->first();
            $customerId = $customer?->id;
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
