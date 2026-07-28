<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $branchId = DB::table('branches')->first()?->id ?? 1;

        $categories = [
            ['category' => 'device_category', 'name' => 'HP Android'],
            ['category' => 'device_category', 'name' => 'iPhone'],
            ['category' => 'device_category', 'name' => 'Laptop Windows'],
            ['category' => 'device_category', 'name' => 'MacBook'],
            ['category' => 'device_category', 'name' => 'Tablet'],
            ['category' => 'device_category', 'name' => 'Smartwatch'],
            ['category' => 'brand', 'name' => 'Samsung'],
            ['category' => 'brand', 'name' => 'Apple'],
            ['category' => 'brand', 'name' => 'Xiaomi'],
            ['category' => 'brand', 'name' => 'Oppo'],
            ['category' => 'brand', 'name' => 'Vivo'],
            ['category' => 'brand', 'name' => 'ASUS'],
            ['category' => 'brand', 'name' => 'Lenovo'],
            ['category' => 'brand', 'name' => 'HP'],
            ['category' => 'brand', 'name' => 'Dell'],
            ['category' => 'brand', 'name' => 'Acer'],
            ['category' => 'unit', 'name' => 'pcs'],
            ['category' => 'unit', 'name' => 'set'],
            ['category' => 'unit', 'name' => 'meter'],
            ['category' => 'arrival_method', 'name' => 'Datang ke Toko'],
            ['category' => 'arrival_method', 'name' => 'WhatsApp'],
            ['category' => 'arrival_method', 'name' => 'Telepon'],
            ['category' => 'arrival_method', 'name' => 'Marketplace'],
            ['category' => 'arrival_method', 'name' => 'Rujukan'],
            ['category' => 'payment_method', 'name' => 'Tunai'],
            ['category' => 'payment_method', 'name' => 'Transfer Bank'],
            ['category' => 'payment_method', 'name' => 'QRIS'],
            ['category' => 'payment_method', 'name' => 'E-Wallet'],
            ['category' => 'payment_method', 'name' => 'Debit Card'],
            ['category' => 'equipment', 'name' => 'Charger'],
            ['category' => 'equipment', 'name' => 'Kabel Data'],
            ['category' => 'equipment', 'name' => 'Earphone'],
            ['category' => 'equipment', 'name' => 'Kardus Box'],
            ['category' => 'equipment', 'name' => 'Buku Manual'],
            ['category' => 'equipment', 'name' => 'SIM Card Tray'],
        ];

        $now = now();
        foreach ($categories as $data) {
            DB::table('master_data')->updateOrInsert(
                ['category' => $data['category'], 'name' => $data['name'], 'branch_id' => $branchId],
                array_merge($data, ['branch_id' => $branchId, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now])
            );
        }

        echo "Master data seeded: " . count($categories) . " entries\n";
    }
}
