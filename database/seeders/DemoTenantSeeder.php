<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\SystemLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Stancl\Tenancy\Database\Models\Domain;

class DemoTenantSeeder extends Seeder
{
    public function run(): void
    {
        $plan = Plan::where('slug', 'pro')->first();

        $existing = Tenant::where('email', 'demo@serviceku.app')->first();
        if ($existing) {
            $this->command->warn('Tenant demo sudah ada, melewati...');
            return;
        }

        $tenantId = 'tenant_demo';

        // Buat tenant tanpa trigger event (supaya tidak auto-migrate)
        $tenant = Tenant::withoutEvents(fn () => Tenant::create([
            'id' => $tenantId,
            'tenant_name' => 'Toko Servis ABC',
            'slug' => 'toko-servis-abc',
            'email' => 'demo@serviceku.app',
            'phone' => '08123456789',
            'plan_id' => $plan?->id,
            'subscription_status' => 'active',
            'subscribed_at' => now(),
            'is_active' => true,
        ]));

        Domain::create([
            'tenant_id' => $tenant->id,
            'domain' => 'toko-servis-abc.localhost',
        ]);

        // Buat database tenant secara manual
        $databaseName = 'tenant_' . $tenantId;
        DB::statement("CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        // Set koneksi ke tenant database
        config(['database.connections.tenant' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $databaseName,
            'username' => env('DB_USERNAME', 'serviceku'),
            'password' => env('DB_PASSWORD', 'serviceku_pass'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]]);

        DB::purge('mysql');
        DB::reconnect('tenant');
        DB::setDefaultConnection('tenant');

        // Jalankan migrasi tenant via Artisan call
        \Illuminate\Support\Facades\Artisan::call('migrate', [
            '--path' => 'database/migrations/tenant',
            '--database' => 'tenant',
            '--force' => true,
        ]);

        // ==== BRANCH ====
        $branchId = DB::table('branches')->insertGetId([
            'name' => 'Cabang Utama',
            'address' => 'Jl. Merdeka No. 123, Jakarta',
            'phone' => '021-1234567',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $branch2Id = DB::table('branches')->insertGetId([
            'name' => 'Cabang Bandung',
            'address' => 'Jl. Asia Afrika No. 45, Bandung',
            'phone' => '022-7654321',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ==== USERS ====
        $ownerId = DB::table('users')->insertGetId([
            'branch_id' => $branchId,
            'name' => 'Budi Santoso',
            'email' => 'demo@serviceku.app',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $csId = DB::table('users')->insertGetId([
            'branch_id' => $branchId,
            'name' => 'Siti Rahayu',
            'email' => 'cs@serviceku.app',
            'password' => Hash::make('password'),
            'role' => 'cs',
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $techId = DB::table('users')->insertGetId([
            'branch_id' => $branchId,
            'name' => 'Ahmad Teknisi',
            'email' => 'teknisi@serviceku.app',
            'password' => Hash::make('password'),
            'role' => 'technician',
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cashierId = DB::table('users')->insertGetId([
            'branch_id' => $branchId,
            'name' => 'Dewi Kasir',
            'email' => 'kasir@serviceku.app',
            'password' => Hash::make('password'),
            'role' => 'cashier',
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $courierId = DB::table('users')->insertGetId([
            'branch_id' => $branchId,
            'name' => 'Joko Kurir',
            'email' => 'kurir@serviceku.app',
            'password' => Hash::make('password'),
            'role' => 'courier',
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ==== CUSTOMERS ====
        $customers = [
            ['branch_id' => $branchId, 'name' => 'Rina Wijaya', 'phone' => '0811111111', 'is_member' => true],
            ['branch_id' => $branchId, 'name' => 'Doni Prasetyo', 'phone' => '0822222222', 'is_member' => false],
            ['branch_id' => $branchId, 'name' => 'Mega Sari', 'phone' => '0833333333', 'is_member' => true],
            ['branch_id' => $branch2Id, 'name' => 'Agus Hermawan', 'phone' => '0844444444', 'is_member' => false],
            ['branch_id' => $branch2Id, 'name' => 'Fitriani', 'phone' => '0855555555', 'is_member' => true],
        ];

        $customerIds = [];
        foreach ($customers as $c) {
            $customerIds[] = DB::table('customers')->insertGetId(array_merge($c, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // ==== CHECKLIST TEMPLATES ====
        $templateMasukId = DB::table('checklist_templates')->insertGetId([
            'name' => 'Ceklis Standar Masuk',
            'type' => 'masuk',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $templateKeluarId = DB::table('checklist_templates')->insertGetId([
            'name' => 'Ceklis Standar Keluar',
            'type' => 'keluar',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $itemsMasuk = [
            ['checklist_template_id' => $templateMasukId, 'item_name' => 'LCD/Layar', 'sort_order' => 1],
            ['checklist_template_id' => $templateMasukId, 'item_name' => 'Touchscreen', 'sort_order' => 2],
            ['checklist_template_id' => $templateMasukId, 'item_name' => 'Keyboard', 'sort_order' => 3],
            ['checklist_template_id' => $templateMasukId, 'item_name' => 'Touchpad', 'sort_order' => 4],
            ['checklist_template_id' => $templateMasukId, 'item_name' => 'Port USB', 'sort_order' => 5],
            ['checklist_template_id' => $templateMasukId, 'item_name' => 'Baterai', 'sort_order' => 6],
            ['checklist_template_id' => $templateMasukId, 'item_name' => 'Charger', 'sort_order' => 7],
            ['checklist_template_id' => $templateMasukId, 'item_name' => 'Body (Lecet/Penyok)', 'sort_order' => 8],
        ];

        $itemsKeluar = [
            ['checklist_template_id' => $templateKeluarId, 'item_name' => 'LCD/Layar - Normal', 'sort_order' => 1],
            ['checklist_template_id' => $templateKeluarId, 'item_name' => 'Semua Fungsi Normal', 'sort_order' => 2],
            ['checklist_template_id' => $templateKeluarId, 'item_name' => 'Baterai Normal', 'sort_order' => 3],
            ['checklist_template_id' => $templateKeluarId, 'item_name' => 'Kelengkapan Sesuai', 'sort_order' => 4],
        ];

        foreach ($itemsMasuk as $item) DB::table('checklist_items')->insert($item);
        foreach ($itemsKeluar as $item) DB::table('checklist_items')->insert($item);

        // ==== PRODUCTS ====
        $products = [
            ['branch_id' => $branchId, 'code' => 'LCD-IPH12', 'name' => 'LCD iPhone 12', 'unit' => 'pcs', 'cost_price' => 350000, 'selling_price' => 550000, 'stock_quantity' => 5, 'min_stock' => 2],
            ['branch_id' => $branchId, 'code' => 'BAT-S22', 'name' => 'Baterai Samsung S22', 'unit' => 'pcs', 'cost_price' => 150000, 'selling_price' => 275000, 'stock_quantity' => 3, 'min_stock' => 2],
            ['branch_id' => $branchId, 'code' => 'TMP-SSD-256', 'name' => 'SSD 256GB NVMe', 'unit' => 'pcs', 'cost_price' => 250000, 'selling_price' => 425000, 'stock_quantity' => 0, 'min_stock' => 3],
            ['branch_id' => $branchId, 'code' => 'CHR-MBP', 'name' => 'Charger MacBook Pro', 'unit' => 'pcs', 'cost_price' => 120000, 'selling_price' => 225000, 'stock_quantity' => 2, 'min_stock' => 1],
            ['branch_id' => $branchId, 'code' => 'CAS-HP', 'name' => 'Casing HP Silikon', 'unit' => 'pcs', 'cost_price' => 15000, 'selling_price' => 35000, 'stock_quantity' => 20, 'min_stock' => 5],
            ['branch_id' => $branch2Id, 'code' => 'TMP-GLAS', 'name' => 'Tempered Glass', 'unit' => 'pcs', 'cost_price' => 8000, 'selling_price' => 20000, 'stock_quantity' => 50, 'min_stock' => 10],
        ];

        $productIds = [];
        foreach ($products as $p) {
            $productIds[] = DB::table('products')->insertGetId(array_merge($p, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // ==== SERVICES ====
        $service1Id = DB::table('services')->insertGetId([
            'branch_id' => $branchId,
            'customer_id' => $customerIds[0],
            'created_by' => $csId,
            'technician_id' => $techId,
            'status' => 'selesai',
            'problem_description' => 'LCD retak sebelah kiri, touch masih berfungsi',
            'service_charge' => 75000,
            'total_cost' => 625000,
            'payment_status' => 'paid',
            'created_at' => now()->subDays(2),
            'updated_at' => now(),
        ]);

        // Checklist masuk
        DB::table('service_checklists')->insert([
            'service_id' => $service1Id,
            'template_id' => $templateMasukId,
            'type' => 'masuk',
            'checked_items' => json_encode(['LCD/Layar', 'Touchscreen', 'Port USB', 'Charger', 'Body (Lecet/Penyok)']),
            'note' => 'LCD retak, body lecet di sudut kanan bawah',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Sparepart
        DB::table('service_spareparts')->insert([
            'service_id' => $service1Id,
            'product_id' => $productIds[0],
            'quantity' => 1,
            'unit_price' => 550000,
            'subtotal' => 550000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Checklist keluar
        DB::table('service_checklists')->insert([
            'service_id' => $service1Id,
            'template_id' => $templateKeluarId,
            'type' => 'keluar',
            'checked_items' => json_encode(['LCD/Layar - Normal', 'Semua Fungsi Normal', 'Kelengkapan Sesuai']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Sale from service
        $sale1Id = DB::table('sales')->insertGetId([
            'branch_id' => $branchId,
            'customer_id' => $customerIds[0],
            'sale_type' => 'servis',
            'service_id' => $service1Id,
            'subtotal' => 625000,
            'discount' => 0,
            'total' => 625000,
            'payment_method' => 'cash',
            'paid_amount' => 650000,
            'change' => 25000,
            'created_at' => now()->subDays(1),
            'updated_at' => now()->subDays(1),
        ]);

        DB::table('sale_items')->insert([
            'sale_id' => $sale1Id,
            'product_id' => $productIds[0],
            'item_type' => 'sparepart',
            'description' => 'LCD iPhone 12',
            'quantity' => 1,
            'price' => 550000,
            'subtotal' => 550000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('sale_items')->insert([
            'sale_id' => $sale1Id,
            'item_type' => 'jasa',
            'description' => 'Biaya Servis',
            'quantity' => 1,
            'price' => 75000,
            'subtotal' => 75000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Service active
        $service2Id = DB::table('services')->insertGetId([
            'branch_id' => $branchId,
            'customer_id' => $customerIds[1],
            'created_by' => $csId,
            'technician_id' => $techId,
            'status' => 'dikerjakan',
            'problem_description' => 'HP sering restart sendiri, overheat',
            'service_charge' => 50000,
            'created_at' => now()->subHours(3),
            'updated_at' => now(),
        ]);

        DB::table('service_checklists')->insert([
            'service_id' => $service2Id,
            'template_id' => $templateMasukId,
            'type' => 'masuk',
            'checked_items' => json_encode(['Touchscreen', 'Keyboard', 'Baterai', 'Body (Lecet/Penyok)']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ==== SUPPLIER ====
        $supplierId = DB::table('suppliers')->insertGetId([
            'name' => 'PT Elektronik Jaya',
            'phone' => '021-8888888',
            'email' => 'sales@elektronikjaya.co.id',
            'address' => 'Jl. Industri No. 88, Jakarta',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ==== PURCHASE ====
        $purchaseId = DB::table('purchases')->insertGetId([
            'branch_id' => $branchId,
            'reference_number' => 'PO-20260718-0001',
            'type' => 'po',
            'supplier_id' => $supplierId,
            'total' => 1050000,
            'note' => 'PO bulan Juli',
            'created_by' => $ownerId,
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(5),
        ]);

        DB::table('purchase_items')->insert([
            'purchase_id' => $purchaseId,
            'product_id' => $productIds[0],
            'product_name' => 'LCD iPhone 12',
            'quantity' => 3,
            'unit_price' => 350000,
            'subtotal' => 1050000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ==== EXPENSES ====
        DB::table('expenses')->insert([
            'branch_id' => $branchId,
            'description' => 'Token Listrik',
            'amount' => 150000,
            'expense_date' => today(),
            'created_by' => $ownerId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('expenses')->insert([
            'branch_id' => $branchId,
            'description' => 'Beli ATK',
            'amount' => 45000,
            'expense_date' => today(),
            'created_by' => $csId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ==== DAILY DEPOSIT ====
        DB::table('daily_deposits')->insert([
            'branch_id' => $branchId,
            'amount' => 625000,
            'deposit_date' => today(),
            'created_by' => $csId,
            'note' => 'Setoran penjualan servis iPhone 12',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ==== MASTER DATA ====
        $this->callMasterDataSeeder();

        // ==== TENANT SETTINGS ====
        DB::table('tenant_settings')->insert([
            ['key' => 'store_name', 'value' => 'Toko Servis ABC'],
            ['key' => 'primary_color', 'value' => '#4F46E5'],
            ['key' => 'address', 'value' => 'Jl. Merdeka No. 123, Jakarta'],
            ['key' => 'phone', 'value' => '021-1234567'],
            ['key' => 'whatsapp_number', 'value' => '08123456789'],
        ]);

        // ==== ACTIVITY LOG ====
        DB::table('activity_logs')->insert([
            ['user_id' => $ownerId, 'action' => 'seed', 'description' => 'Demo data seeded', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Kembali ke central database
        DB::purge('tenant');
        DB::reconnect('mysql');
        DB::setDefaultConnection('mysql');

        // Sync stats ke master DB
        \App\Models\TenantStat::syncStats($tenant);

        \App\Models\SystemLog::info('Demo tenant created with sample data');

        $this->command->info('Tenant demo berhasil dibuat!');
        $this->command->info('Email: demo@serviceku.app');
        $this->command->info('Password: password');
    }

    private function callMasterDataSeeder(): void
    {
        $items = [
            ['category' => 'device_category', 'name' => 'HP Android'],
            ['category' => 'device_category', 'name' => 'iPhone'],
            ['category' => 'device_category', 'name' => 'Laptop Windows'],
            ['category' => 'device_category', 'name' => 'MacBook'],
            ['category' => 'device_category', 'name' => 'Tablet'],
            ['category' => 'brand', 'name' => 'Samsung'],
            ['category' => 'brand', 'name' => 'Apple'],
            ['category' => 'brand', 'name' => 'Xiaomi'],
            ['category' => 'brand', 'name' => 'Oppo'],
            ['category' => 'brand', 'name' => 'ASUS'],
            ['category' => 'brand', 'name' => 'Lenovo'],
            ['category' => 'brand', 'name' => 'HP'],
            ['category' => 'brand', 'name' => 'Dell'],
            ['category' => 'unit', 'name' => 'pcs'],
            ['category' => 'unit', 'name' => 'set'],
            ['category' => 'arrival_method', 'name' => 'Datang ke Toko'],
            ['category' => 'arrival_method', 'name' => 'WhatsApp'],
            ['category' => 'arrival_method', 'name' => 'Telepon'],
            ['category' => 'arrival_method', 'name' => 'Marketplace'],
            ['category' => 'payment_method', 'name' => 'Tunai'],
            ['category' => 'payment_method', 'name' => 'Transfer Bank'],
            ['category' => 'payment_method', 'name' => 'QRIS'],
            ['category' => 'payment_method', 'name' => 'E-Wallet'],
            ['category' => 'payment_method', 'name' => 'Debit Card'],
            ['category' => 'equipment', 'name' => 'Charger'],
            ['category' => 'equipment', 'name' => 'Kabel Data'],
            ['category' => 'equipment', 'name' => 'Earphone'],
            ['category' => 'equipment', 'name' => 'Kardus Box'],
            ['category' => 'equipment', 'name' => 'SIM Card Tray'],
        ];

        $now = now();
        foreach ($items as $data) {
            DB::table('master_data')->insert($data + ['branch_id' => 1, 'sort_order' => 0, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }
    }
}
