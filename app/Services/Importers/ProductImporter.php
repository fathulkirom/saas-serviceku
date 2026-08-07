<?php

namespace App\Services\Importers;

use App\Models\Tenant\Product;
use App\Services\BaseImporter;

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
        if (empty($mapped['name'])) {
            return 'Nama produk wajib diisi.';
        }

        if ($mapped['cost_price'] < 0) {
            return 'Harga beli tidak valid.';
        }

        return null;
    }

    protected function importOne(array $mapped): void
    {
        Product::create($mapped + [
            'branch_id' => auth()->user()?->branch_id ?? 1,
            'code' => $mapped['sku'] ?? 'PRD'.uniqid(),
        ]);
    }
}
