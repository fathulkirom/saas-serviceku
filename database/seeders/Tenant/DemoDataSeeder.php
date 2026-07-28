<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\Customer;
use App\Models\Tenant\Product;
use App\Models\Tenant\Service;
use App\Models\Tenant\Branch;
use App\Models\Tenant\User;
use App\Models\Tenant\ActivityLog;
use App\Models\Tenant\ChecklistTemplate;
use App\Models\Tenant\ChecklistTemplateItem;
use App\Models\Tenant\TenantSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::first();
        if (!$branch) {
            $branch = Branch::create([
                'name' => 'Cabang Utama',
                'address' => 'Jl. Raya Demo No. 123',
                'phone' => '08123456789',
                'is_active' => true,
            ]);
        }

        $user = User::where('role', 'owner')->first() ?? User::first();
        if (!$user) return;

        // ========== CUSTOMERS ==========
        $customers = [
            ['name' => 'Budi Santoso', 'phone' => '081111111111', 'address' => 'Jl. Merdeka No. 1', 'email' => 'budi@example.com'],
            ['name' => 'Siti Rahmawati', 'phone' => '081111111112', 'address' => 'Jl. Sudirman No. 10', 'email' => 'siti@example.com'],
            ['name' => 'Ahmad Hidayat', 'phone' => '081111111113', 'address' => 'Jl. Gatot Subroto No. 5', 'email' => 'ahmad@example.com'],
            ['name' => 'Dewi Lestari', 'phone' => '081111111114', 'address' => 'Jl. Diponegoro No. 22', 'email' => 'dewi@example.com'],
            ['name' => 'Rudi Hermawan', 'phone' => '081111111115', 'address' => 'Jl. Pahlawan No. 7', 'email' => 'rudi@example.com'],
            ['name' => 'Ani Wijaya', 'phone' => '081111111116', 'address' => 'Jl. Kemerdekaan No. 15'],
            ['name' => 'Hendra Gunawan', 'phone' => '081111111117', 'address' => 'Jl. Ahmad Yani No. 3'],
            ['name' => 'Rina Marlina', 'phone' => '081111111118', 'address' => 'Jl. Siliwangi No. 8'],
        ];

        $createdCustomers = [];
        foreach ($customers as $data) {
            $createdCustomers[] = Customer::create($data);
        }

        // ========== PRODUCTS (Sparepart) ==========
        $products = [
            ['name' => 'LCD iPhone 11', 'cost_price' => 350000, 'selling_price' => 550000, 'stock_quantity' => 5, 'unit' => 'pcs', 'min_stock' => 2],
            ['name' => 'Baterai iPhone 11', 'cost_price' => 150000, 'selling_price' => 250000, 'stock_quantity' => 8, 'unit' => 'pcs', 'min_stock' => 2],
            ['name' => 'LCD Samsung A12', 'cost_price' => 250000, 'selling_price' => 400000, 'stock_quantity' => 3, 'unit' => 'pcs', 'min_stock' => 1],
            ['name' => 'Touchscreen Samsung A12', 'cost_price' => 120000, 'selling_price' => 200000, 'stock_quantity' => 4, 'unit' => 'pcs', 'min_stock' => 2],
            ['name' => 'Baterai Samsung A12', 'cost_price' => 100000, 'selling_price' => 180000, 'stock_quantity' => 6, 'unit' => 'pcs', 'min_stock' => 2],
            ['name' => 'Kabel Charger USB-C', 'cost_price' => 15000, 'selling_price' => 35000, 'stock_quantity' => 20, 'unit' => 'pcs', 'min_stock' => 5],
            ['name' => 'Tempered Glass', 'cost_price' => 8000, 'selling_price' => 20000, 'stock_quantity' => 30, 'unit' => 'pcs', 'min_stock' => 10],
            ['name' => 'Charger Head Fast Charging', 'cost_price' => 30000, 'selling_price' => 65000, 'stock_quantity' => 10, 'unit' => 'pcs', 'min_stock' => 3],
            ['name' => 'Case HP Silikon', 'cost_price' => 10000, 'selling_price' => 25000, 'stock_quantity' => 15, 'unit' => 'pcs', 'min_stock' => 5],
            ['name' => 'Port Charger iPhone', 'cost_price' => 45000, 'selling_price' => 85000, 'stock_quantity' => 7, 'unit' => 'pcs', 'min_stock' => 3],
        ];

        foreach ($products as $data) {
            $data['branch_id'] = $branch->id;
            Product::create($data);
        }

        // ========== CHECKLIST TEMPLATES ==========
        $templateMasuk = ChecklistTemplate::create([
            'name' => 'Ceklis Penerimaan HP (Demo)',
            'type' => 'masuk',
            'is_active' => true,
        ]);
        $itemsMasuk = [
            'Layar hidup & normal', 'Touchscreen berfungsi', 'Baterai mengisi',
            'Kamerea berfungsi', 'Speaker & mic normal', 'Tombol fisik berfungsi',
            'Tidak ada retak', 'Body mulus tanpa penyok',
        ];
        foreach ($itemsMasuk as $item) {
            ChecklistTemplateItem::create([
                'checklist_template_id' => $templateMasuk->id,
                'item_name' => $item,
                'is_checked' => false,
            ]);
        }

        $templateKeluar = ChecklistTemplate::create([
            'name' => 'Ceklis Serah Terima (Demo)',
            'type' => 'keluar',
            'is_active' => true,
        ]);
        $itemsKeluar = [
            'Layar normal', 'Touchscreen normal', 'Baterai normal',
            'Semua fitur berfungsi', 'Kelengkapan: charger', 'Kelengkapan: dus',
        ];
        foreach ($itemsKeluar as $item) {
            ChecklistTemplateItem::create([
                'checklist_template_id' => $templateKeluar->id,
                'item_name' => $item,
                'is_checked' => false,
            ]);
        }

        // ========== DEMO SERVICES ==========
        $sampleServices = [
            ['customer_id' => $createdCustomers[0]->id, 'problem' => 'LCD retak setelah jatuh', 'status' => 'selesai', 'charge' => 50000, 'sparepart' => 'LCD iPhone 11'],
            ['customer_id' => $createdCustomers[1]->id, 'problem' => 'Baterai cepat habis, penuh 30 menit', 'status' => 'dikerjakan', 'charge' => 30000, 'sparepart' => 'Baterai iPhone 11'],
            ['customer_id' => $createdCustomers[2]->id, 'problem' => 'Touchscreen tidak merespon', 'status' => 'selesai', 'charge' => 40000, 'sparepart' => 'Touchscreen Samsung A12'],
            ['customer_id' => $createdCustomers[3]->id, 'problem' => 'Tidak bisa dicharge', 'status' => 'menunggu_alokasi', 'charge' => 25000, 'sparepart' => null],
        ];

        foreach ($sampleServices as $svc) {
            $product = $svc['sparepart'] ? Product::where('name', $svc['sparepart'])->first() : null;
            $service = Service::create([
                'branch_id' => $branch->id,
                'customer_id' => $svc['customer_id'],
                'created_by' => $user->id,
                'technician_id' => $user->id,
                'status' => $svc['status'],
                'problem_description' => $svc['problem'],
                'condition_note' => 'Kondisi HP: body mulus, layar retak bagian kiri atas',
                'service_charge' => $svc['charge'],
                'total_cost' => $svc['charge'] + ($product ? $product->selling_price : 0),
            ]);

            if ($product) {
                DB::table('service_spareparts')->insert([
                    'service_id' => $service->id,
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'unit_price' => $product->selling_price,
                    'subtotal' => $product->selling_price,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // ========== SET FLAG ==========
        TenantSetting::setValue('demo_data_generated', 'true');
        TenantSetting::setValue('demo_generated_at', now()->format('Y-m-d H:i:s'));

        ActivityLog::log('demo', 'Demo data generated: ' . count($customers) . ' customers, ' . count($products) . ' products, ' . count($sampleServices) . ' services');
    }
}
