<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\TenantSetting;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Product;
use App\Models\Tenant\Service;
use App\Models\Tenant\Sale;
use App\Models\Tenant\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class DemoController extends Controller
{
    public function index()
    {
        $stats = [
            'customers_count' => Customer::count(),
            'products_count' => Product::count(),
            'services_count' => Service::count(),
            'demo_mode' => TenantSetting::getValue('demo_mode', 'false') === 'true',
            'demo_data_generated' => TenantSetting::getValue('demo_data_generated', 'false') === 'true',
            'demo_generated_at' => TenantSetting::getValue('demo_generated_at'),
        ];

        return redirect()->route('pengaturan.index', ['tab' => 'demo']);
    }

    public function generate()
    {
        // Run demo data seeder
        try {
            Artisan::call('db:seed', [
                '--class' => 'Database\Seeders\Tenant\DemoDataSeeder',
                '--force' => true,
            ]);
            $output = Artisan::output();
        } catch (\Exception $e) {
            ActivityLog::log('demo_error', 'Gagal generate demo data: ' . $e->getMessage());
            return back()->with('error', 'Gagal generate data demo: ' . $e->getMessage());
        }

        TenantSetting::setValue('demo_mode', 'true');
        TenantSetting::setValue('demo_data_generated', 'true');
        TenantSetting::setValue('demo_generated_at', now()->format('Y-m-d H:i:s'));

        ActivityLog::log('demo', 'Demo data generated successfully');
        return back()->with('success', 'Data demo berhasil dibuat! 🎉');
    }

    public function reset()
    {
        // Hapus semua data demo (cadangan: customers, products, services, sales, dll)
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // Hapus dalam urutan yang aman
            DB::table('service_spareparts')->truncate();
            DB::table('service_checklists')->truncate();
            DB::table('inventory_mutations')->truncate();
            DB::table('sale_items')->truncate();
            DB::table('sales')->truncate();
            DB::table('services')->truncate();
            DB::table('indents')->truncate();
            DB::table('checklist_template_items')->delete();
            DB::table('checklist_templates')->delete();
            DB::table('customer_vehicles')->truncate();
            Customer::query()->delete();
            
            // Hapus produk juga (reset stok)
            Product::query()->delete();

            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            TenantSetting::setValue('demo_mode', 'false');
            TenantSetting::setValue('demo_data_generated', 'false');
            TenantSetting::setValue('demo_generated_at', null);

            ActivityLog::log('demo', 'All demo data has been reset');
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            ActivityLog::log('demo_error', 'Gagal reset demo data: ' . $e->getMessage());
            return back()->with('error', 'Gagal reset data: ' . $e->getMessage());
        }

        return back()->with('success', 'Semua data demo berhasil dihapus!');
    }

    public function toggleMode()
    {
        $current = TenantSetting::getValue('demo_mode', 'false');
        $newMode = $current === 'true' ? 'false' : 'true';
        TenantSetting::setValue('demo_mode', $newMode);

        $label = $newMode === 'true' ? 'diaktifkan' : 'dinonaktifkan';
        ActivityLog::log('demo', "Demo mode {$label}");

        return back()->with('success', "Mode demo {$label}.");
    }
}
