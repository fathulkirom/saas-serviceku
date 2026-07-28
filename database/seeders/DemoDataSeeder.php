<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $branchId = 1;
        $ownerId = 1;
        $csId = 2;
        $techId = 3;
        $cashierId = 4;

        // === CUSTOMERS ===
        $customers = [
            ['name' => 'Rina Wijaya', 'phone' => '081234567890', 'address' => 'Jl. Merdeka No. 10, Jakarta', 'is_member' => true],
            ['name' => 'Doni Prasetyo', 'phone' => '082345678901', 'address' => 'Jl. Sudirman No. 25, Jakarta', 'is_member' => false],
            ['name' => 'Mega Sari', 'phone' => '083456789012', 'address' => 'Jl. Gatot Subroto No. 5, Jakarta', 'is_member' => true],
            ['name' => 'Agus Hermawan', 'phone' => '084567890123', 'address' => 'Jl. Ahmad Yani No. 15, Bandung', 'is_member' => false],
            ['name' => 'Fitriani', 'phone' => '085678901234', 'address' => 'Jl. Diponegoro No. 8, Bandung', 'is_member' => true],
            ['name' => 'Hendra Gunawan', 'phone' => '086789012345', 'address' => 'Jl. Pahlawan No. 30, Surabaya', 'is_member' => false],
        ];
        $customerIds = [];
        foreach ($customers as $c) {
            $customerIds[] = DB::table('customers')->insertGetId([
                'branch_id' => $branchId, 'name' => $c['name'], 'phone' => $c['phone'],
                'address' => $c['address'], 'is_member' => $c['is_member'],
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // === SUPPLIERS ===
        $supplierId = DB::table('suppliers')->insertGetId([
            'name' => 'PT Elektronik Jaya', 'phone' => '021-8888888',
            'email' => 'sales@elektronikjaya.co.id', 'address' => 'Jl. Industri No. 88, Jakarta',
            'created_at' => $now, 'updated_at' => $now,
        ]);

        // === PRODUCTS ===
        $products = [
            ['name' => 'LCD iPhone 12', 'code' => 'LCD-IP12', 'unit' => 'pcs', 'cost_price' => 350000, 'selling_price' => 550000, 'stock_quantity' => 5, 'min_stock' => 2],
            ['name' => 'Baterai Samsung S22', 'code' => 'BAT-S22', 'unit' => 'pcs', 'cost_price' => 150000, 'selling_price' => 275000, 'stock_quantity' => 3, 'min_stock' => 2],
            ['name' => 'SSD 256GB NVMe', 'code' => 'TMP-SSD-256', 'unit' => 'pcs', 'cost_price' => 250000, 'selling_price' => 425000, 'stock_quantity' => 8, 'min_stock' => 3],
            ['name' => 'Charger MacBook Pro', 'code' => 'CHR-MBP', 'unit' => 'pcs', 'cost_price' => 120000, 'selling_price' => 225000, 'stock_quantity' => 2, 'min_stock' => 1],
            ['name' => 'Casing HP Silikon', 'code' => 'CAS-HP', 'unit' => 'pcs', 'cost_price' => 15000, 'selling_price' => 35000, 'stock_quantity' => 20, 'min_stock' => 5],
            ['name' => 'Tempered Glass', 'code' => 'TMP-GLAS', 'unit' => 'pcs', 'cost_price' => 8000, 'selling_price' => 20000, 'stock_quantity' => 50, 'min_stock' => 10],
        ];
        $productIds = [];
        foreach ($products as $p) {
            $productIds[] = DB::table('products')->insertGetId([
                'branch_id' => $branchId, 'name' => $p['name'], 'code' => $p['code'],
                'unit' => $p['unit'], 'cost_price' => $p['cost_price'], 'selling_price' => $p['selling_price'],
                'stock_quantity' => $p['stock_quantity'], 'min_stock' => $p['min_stock'],
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // === INVENTORY MUTATIONS (initial stock) ===
        foreach ($productIds as $i => $pid) {
            DB::table('inventory_mutations')->insert([
                'branch_id' => $branchId, 'product_id' => $pid, 'type' => 'masuk',
                'quantity' => $products[$i]['stock_quantity'],
                'reference_type' => 'initial', 'reference_id' => 'init',
                'note' => 'Stok awal', 'created_by' => $ownerId,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // === CHECKLIST TEMPLATES ===
        $tmplMasukId = DB::table('checklist_templates')->insertGetId([
            'name' => 'Ceklis Standar Masuk', 'type' => 'masuk', 'is_active' => true,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $tmplKeluarId = DB::table('checklist_templates')->insertGetId([
            'name' => 'Ceklis Standar Keluar', 'type' => 'keluar', 'is_active' => true,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        foreach ([
            [$tmplMasukId, 'LCD/Layar', 1], [$tmplMasukId, 'Touchscreen', 2], [$tmplMasukId, 'Keyboard', 3],
            [$tmplMasukId, 'Baterai', 4], [$tmplMasukId, 'Port USB', 5], [$tmplMasukId, 'Body (Lecet)', 6],
            [$tmplKeluarId, 'LCD - Normal', 1], [$tmplKeluarId, 'Fungsi Normal', 2], [$tmplKeluarId, 'Kelengkapan Sesuai', 3],
        ] as $item) {
            DB::table('checklist_items')->insert([
                'checklist_template_id' => $item[0], 'item_name' => $item[1], 'sort_order' => $item[2],
            ]);
        }

        // === SERVICES (3 completed + 2 active + 1 warranty) ===
        $serviceData = [
            ['customer_id' => $customerIds[0], 'problem' => 'LCD retak sebelah kiri, touch masih berfungsi',                 'charge' => 75000, 'sparepart_cost' => 550000, 'status' => 'selesai', 'days_ago' => 5, 'payment' => 'paid', 'warranty' => 30],
            ['customer_id' => $customerIds[1], 'problem' => 'HP sering restart sendiri, overheat saat dipakai', 'charge' => 50000, 'sparepart_cost' => 0, 'status' => 'dikerjakan', 'days_ago' => 1, 'payment' => null, 'warranty' => 0],
            ['customer_id' => $customerIds[2], 'problem' => 'Touchscreen tidak responsif di area bawah', 'charge' => 65000, 'sparepart_cost' => 35000, 'status' => 'selesai', 'days_ago' => 3, 'payment' => 'paid', 'warranty' => 14],
            ['customer_id' => $customerIds[3], 'problem' => 'Baterai cepat habis, perlu ganti baterai', 'charge' => 45000, 'sparepart_cost' => 0, 'status' => 'siap_diambil', 'days_ago' => 0, 'payment' => null, 'warranty' => 0],
            ['customer_id' => $customerIds[4], 'problem' => 'Port USB longgar, tidak bisa charging', 'charge' => 55000, 'sparepart_cost' => 0, 'status' => 'menunggu_alokasi', 'days_ago' => 0, 'payment' => null, 'warranty' => 0],
            ['customer_id' => $customerIds[5], 'problem' => 'Klaim garansi LCD retak kembali', 'charge' => 0, 'sparepart_cost' => 0, 'status' => 'menunggu_alokasi', 'days_ago' => 0, 'payment' => null, 'warranty' => 0, 'is_warranty' => true, 'parent_id' => 1],
        ];

        $serviceIds = [];
        foreach ($serviceData as $i => $sd) {
            $sid = DB::table('services')->insertGetId([
                'branch_id' => $branchId, 'customer_id' => $sd['customer_id'],
                'created_by' => $csId, 'technician_id' => $sd['status'] !== 'menunggu_alokasi' ? $techId : null,
                'status' => $sd['status'], 'problem_description' => $sd['problem'],
                'service_charge' => $sd['charge'],
                'total_cost' => $sd['charge'] + ($sd['sparepart_cost'] ?? 0),
                'payment_status' => $sd['payment'] ?? 'pending',
                'warranty_days' => $sd['warranty'] ?? 0,
                'warranty_expired_at' => ($sd['warranty'] ?? 0) > 0 ? $now->copy()->addDays($sd['warranty']) : null,
                'is_warranty_claim' => $sd['is_warranty'] ?? false,
                'parent_service_id' => $sd['parent_id'] ?? null,
                'tracking_code' => strtoupper(substr(md5(uniqid()), 0, 8)),
                'created_at' => $now->copy()->subDays($sd['days_ago']),
                'updated_at' => $now,
            ]);
            $serviceIds[] = $sid;

            // Checklist masuk
            $checked = ['LCD/Layar', 'Touchscreen', 'Port USB', 'Charger'];
            DB::table('service_checklists')->insert([
                'service_id' => $sid, 'checklist_template_id' => $tmplMasukId, 'template_id' => $tmplMasukId,
                'type' => 'masuk', 'checked_items' => json_encode($checked),
                'notes' => substr($sd['problem'], 0, 100),
                'created_at' => $now->copy()->subDays($sd['days_ago']), 'updated_at' => $now,
            ]);
        }

        // === SPAREPARTS (for completed services) ===
        DB::table('service_spareparts')->insert([
            'service_id' => $serviceIds[0], 'product_id' => $productIds[0],
            'quantity' => 1, 'unit_price' => 550000, 'subtotal' => 550000,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        DB::table('service_spareparts')->insert([
            'service_id' => $serviceIds[2], 'product_id' => $productIds[4],
            'quantity' => 1, 'unit_price' => 35000, 'subtotal' => 35000,
            'created_at' => $now, 'updated_at' => $now,
        ]);

        // === SALES (2 completed sales from services) ===
        $sale1Id = DB::table('sales')->insertGetId([
            'branch_id' => $branchId, 'customer_id' => $customerIds[0],
            'sale_type' => 'servis', 'service_id' => $serviceIds[0],
            'status' => 'paid', 'subtotal' => 625000, 'discount' => 0, 'total' => 625000,
            'payment_method' => 'Tunai', 'paid_amount' => 650000, 'change' => 25000,
            'created_at' => $now->copy()->subDays(3), 'updated_at' => $now->copy()->subDays(3),
        ]);
        $sale2Id = DB::table('sales')->insertGetId([
            'branch_id' => $branchId, 'customer_id' => $customerIds[2],
            'sale_type' => 'servis', 'service_id' => $serviceIds[2],
            'status' => 'paid', 'subtotal' => 100000, 'discount' => 0, 'total' => 100000,
            'payment_method' => 'QRIS', 'paid_amount' => 100000, 'change' => 0,
            'created_at' => $now->copy()->subDays(1), 'updated_at' => $now->copy()->subDays(1),
        ]);
        foreach ([
            ['sale_id' => $sale1Id, 'product_id' => $productIds[0], 'item_type' => 'sparepart', 'description' => 'LCD iPhone 12', 'quantity' => 1, 'price' => 550000, 'subtotal' => 550000],
            ['sale_id' => $sale1Id, 'product_id' => null, 'item_type' => 'jasa', 'description' => 'Biaya Jasa Servis', 'quantity' => 1, 'price' => 75000, 'subtotal' => 75000],
            ['sale_id' => $sale2Id, 'product_id' => $productIds[4], 'item_type' => 'sparepart', 'description' => 'Casing HP Silikon', 'quantity' => 1, 'price' => 35000, 'subtotal' => 35000],
            ['sale_id' => $sale2Id, 'product_id' => null, 'item_type' => 'jasa', 'description' => 'Biaya Jasa Servis', 'quantity' => 1, 'price' => 65000, 'subtotal' => 65000],
        ] as $item) {
            DB::table('sale_items')->insert($item);
        }

        // === PURCHASE ===
        $purchaseId = DB::table('purchases')->insertGetId([
            'branch_id' => $branchId, 'supplier_id' => $supplierId,
            'reference_number' => 'PO-202607-001', 'type' => 'po',
            'total' => 1050000, 'note' => 'PO bulan Juli', 'status' => 'received',
            'created_by' => $ownerId, 'created_at' => $now->copy()->subDays(7), 'updated_at' => $now->copy()->subDays(7),
        ]);
        DB::table('purchase_items')->insert([
            'purchase_id' => $purchaseId, 'product_id' => $productIds[0],
            'product_name' => 'LCD iPhone 12', 'quantity' => 3, 'unit_price' => 350000, 'subtotal' => 1050000,
        ]);

        // === EXPENSES ===
        DB::table('expenses')->insert([
            ['branch_id' => $branchId, 'description' => 'Token Listrik', 'amount' => 150000, 'expense_date' => $now->copy()->subDays(0), 'category' => 'listrik', 'created_by' => $ownerId, 'created_at' => $now, 'updated_at' => $now],
            ['branch_id' => $branchId, 'description' => 'Beli ATK', 'amount' => 45000, 'expense_date' => $now->copy()->subDays(1), 'category' => 'operasional', 'created_by' => $csId, 'created_at' => $now, 'updated_at' => $now],
            ['branch_id' => $branchId, 'description' => 'Gaji Teknisi', 'amount' => 3000000, 'expense_date' => $now->copy()->subDays(2), 'category' => 'gaji', 'created_by' => $ownerId, 'created_at' => $now, 'updated_at' => $now],
            ['branch_id' => $branchId, 'description' => 'Sewa Tempat', 'amount' => 2000000, 'expense_date' => $now->copy()->subDays(5), 'category' => 'sewa', 'created_by' => $ownerId, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // === DAILY DEPOSITS ===
        DB::table('daily_deposits')->insert([
            ['branch_id' => $branchId, 'amount' => 725000, 'deposit_date' => $now->copy()->subDays(1), 'created_by' => $cashierId, 'note' => 'Setoran 26 Juli'],
        ]);

        // === INVENTORY MUTATIONS (for sales) & Decrease stock ===
        DB::table('inventory_mutations')->insert([
            ['branch_id' => $branchId, 'product_id' => $productIds[0], 'type' => 'keluar', 'quantity' => 1, 'reference_type' => 'sale', 'reference_id' => (string)$sale1Id, 'note' => 'Penjualan #' . $sale1Id, 'created_by' => $cashierId, 'created_at' => $now, 'updated_at' => $now],
            ['branch_id' => $branchId, 'product_id' => $productIds[4], 'type' => 'keluar', 'quantity' => 1, 'reference_type' => 'sale', 'reference_id' => (string)$sale2Id, 'note' => 'Penjualan #' . $sale2Id, 'created_by' => $cashierId, 'created_at' => $now, 'updated_at' => $now],
        ]);
        DB::table('products')->where('id', $productIds[0])->decrement('stock_quantity', 1);
        DB::table('products')->where('id', $productIds[4])->decrement('stock_quantity', 1);

        // === COMMISSIONS ===
        DB::table('commissions')->insert([
            ['service_id' => $serviceIds[0], 'technician_id' => $techId, 'amount' => 62500, 'percentage' => 10, 'status' => 'pending', 'created_at' => $now, 'updated_at' => $now],
            ['service_id' => $serviceIds[2], 'technician_id' => $techId, 'amount' => 10000, 'percentage' => 10, 'status' => 'pending', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // === SHIFTS ===
        DB::table('shifts')->insert([
            ['name' => 'Pagi', 'start_time' => '08:00', 'end_time' => '17:00', 'branch_id' => $branchId, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Malam', 'start_time' => '17:00', 'end_time' => '22:00', 'branch_id' => $branchId, 'created_at' => $now, 'updated_at' => $now],
        ]);
        $shiftPagiId = 1;

        // === ATTENDANCES ===
        DB::table('attendances')->insert([
            ['user_id' => $techId, 'shift_id' => $shiftPagiId, 'date' => $now->copy()->subDays(0)->toDateString(), 'clock_in' => '08:05', 'clock_out' => '17:10', 'status' => 'hadir'],
            ['user_id' => $csId, 'shift_id' => $shiftPagiId, 'date' => $now->copy()->subDays(0)->toDateString(), 'clock_in' => '07:55', 'clock_out' => '17:00', 'status' => 'hadir'],
            ['user_id' => $cashierId, 'shift_id' => $shiftPagiId, 'date' => $now->copy()->subDays(0)->toDateString(), 'clock_in' => '08:00', 'clock_out' => '17:05', 'status' => 'hadir'],
        ]);

        // === PARTNER TEKNISI ===
        DB::table('partner_teknisis')->insert([
            ['name' => 'Rudi Service Center', 'phone' => '081111111', 'expertise' => 'hp', 'tariff' => 100000, 'is_active' => true, 'branch_id' => $branchId, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // === PICKUP DELIVERIES ===
        DB::table('pickup_deliveries')->insert([
            ['service_id' => $serviceIds[3], 'type' => 'delivery', 'address' => 'Jl. Gatot Subroto No. 5, Jakarta', 'scheduled_at' => $now->copy()->addDay(), 'status' => 'dijadwalkan', 'created_by' => $csId, 'branch_id' => $branchId, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // === ACTIVITY LOGS ===
        DB::table('activity_logs')->insert([
            ['user_id' => $ownerId, 'action' => 'seed', 'description' => 'Demo data seeded', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // === SYSTEM ALERTS ===
        DB::table('system_alerts')->insert([
            ['type' => 'low_stock', 'title' => 'Stok SSD 256GB menipis', 'message' => 'Sisa stok: ' . $products[2]['stock_quantity'], 'severity' => 'warning', 'context' => json_encode(['product_id' => $productIds[2]]), 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'info', 'title' => 'Demo data berhasil', 'message' => 'Seluruh data contoh telah diisi.', 'severity' => 'info', 'context' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        echo "✅ Demo data seeded: "
            . count($customers) . " customers, "
            . count($products) . " products, "
            . count($serviceData) . " services, "
            . "2 sales, 1 purchase, 4 expenses, 1 deposit, "
            . "2 commissions, 2 shifts, 3 attendances\n";
    }
}
