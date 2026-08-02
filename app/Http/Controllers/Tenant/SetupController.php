<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Branch;
use App\Models\Tenant\CashRegister;
use App\Models\Tenant\ChecklistTemplate;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Product;
use App\Models\Tenant\Role;
use App\Models\Tenant\StockLocation;
use App\Models\Tenant\Supplier;
use App\Models\Tenant\TenantSetting;
use App\Models\Tenant\User;
use App\Services\FeatureEngine;
use Illuminate\Http\Request;

/**
 * Setup Controller — Sprint 7.5F (Final).
 *
 * Tenant Go-Live checklist: ONLY operational (toko) items.
 * Platform config reserved for Super Admin — Sprint 8.0 Platform Administration.
 *
 * Severity model (three levels):
 *   - done     ✅ completed
 *   - info     ℹ️  purely informational, does NOT affect progress
 *   - warning  ⚠️  should be addressed, business can still operate
 *   - blocking 🔴  business operation cannot continue without this
 *
 * Zero new models. Pure data aggregation from existing tables.
 *
 * Reserved for Sprint 8.0 Platform Administration:
 *   - SMTP / Email Server configuration
 *   - WhatsApp Provider configuration
 *   - Payment Gateway Platform
 *   - Cloud Storage (GDrive)
 *   - Queue / Redis / Backup / Monitoring
 *   - License & Subscription management
 */
class SetupController extends Controller
{
    /** Go-Live Dashboard — no redirect, no auto-dismiss */
    public function index()
    {
        $checklist = $this->buildChecklist();
        $health = $this->healthCheck();
        $dataIssues = $this->dataConsistencyCheck();
        $progress = $this->calculateProgress($checklist);

        return inertia('Pengaturan/Setup', [
            'checklist' => $checklist,
            'health' => $health,
            'dataIssues' => $dataIssues,
            'progress' => $progress,
            'isReady' => $progress >= 100,
        ]);
    }

    // ──────────────────────────────────────────────
    //  CHECKLIST — Three-level severity
    //    blocking = business CANNOT operate without
    //    warning  = should be addressed, ops continue
    //    info     = purely informational
    // ──────────────────────────────────────────────

    /** Build auto-detecting checklist — 7 groups, dynamic severity */
    private function buildChecklist(): array
    {
        $s = fn(string $key, bool $isDone, int $count = -1) => [
            'status' => $isDone ? 'done' : $this->resolveSeverity($key),
            'count'  => $count >= 0 ? $count : null,
        ];

        return [
            // ── Group: Profil Toko ──
            ['key' => 'store_name',  'group' => 'Profil Toko', 'label' => 'Nama Toko',   'icon' => '🏷️', ...$s('store_name',  $this->hasSetting('store_name')),                    'url' => route('profile.index')],
            ['key' => 'logo',        'group' => 'Profil Toko', 'label' => 'Logo',        'icon' => '🖼️', ...$s('logo',        $this->hasSetting('logo')),                          'url' => route('profile.index')],
            ['key' => 'address',     'group' => 'Profil Toko', 'label' => 'Alamat',      'icon' => '📍', ...$s('address',     $this->hasSetting('address')),                       'url' => route('profile.index')],
            ['key' => 'phone',       'group' => 'Profil Toko', 'label' => 'Telepon',     'icon' => '📞', ...$s('phone',       $this->hasSetting('phone')),                         'url' => route('profile.index')],
            ['key' => 'npwp',        'group' => 'Profil Toko', 'label' => 'NPWP',        'icon' => '📋', ...$s('npwp',        $this->hasSetting('tax_number')),                    'url' => route('settings.index')],

            // ── Group: Cabang ──
            ['key' => 'branches',    'group' => 'Cabang', 'label' => 'Cabang',  'icon' => '🏢', ...$s('branches',    Branch::count() > 1,          Branch::count()),      'url' => '#'],
            ['key' => 'warehouses',  'group' => 'Cabang', 'label' => 'Gudang',  'icon' => '🏗️', ...$s('warehouses',  StockLocation::count() > 0,  StockLocation::count()), 'url' => '#'],
            ['key' => 'cash',        'group' => 'Cabang', 'label' => 'Kas',     'icon' => '💰', ...$s('cash',        CashRegister::exists()),                            'url' => route('cash-registers.index')],

            // ── Group: User ──
            ['key' => 'user_owner',      'group' => 'User', 'label' => 'Owner',     'icon' => '👑', ...$s('user_owner',      $this->roleCount('owner') > 0,      $this->roleCount('owner')),      'url' => route('sistem.index')],
            ['key' => 'user_cs',         'group' => 'User', 'label' => 'CS',         'icon' => '🎧', ...$s('user_cs',         $this->roleCount('cs') > 0,         $this->roleCount('cs')),         'url' => route('sistem.index')],
            ['key' => 'user_technician', 'group' => 'User', 'label' => 'Teknisi',    'icon' => '🔧', ...$s('user_technician', $this->roleCount('technician') > 0, $this->roleCount('technician')), 'url' => route('sistem.index')],
            ['key' => 'user_admin',      'group' => 'User', 'label' => 'Admin',      'icon' => '⚙️', ...$s('user_admin',      $this->roleCount('admin') > 0,      $this->roleCount('admin')),      'url' => route('sistem.index')],
            ['key' => 'user_gudang',     'group' => 'User', 'label' => 'Gudang',     'icon' => '📦', ...$s('user_gudang',     $this->roleCount('head_store') > 0, $this->roleCount('head_store')), 'url' => route('sistem.index')],
            ['key' => 'user_cashier',    'group' => 'User', 'label' => 'Kasir',      'icon' => '💵', ...$s('user_cashier',    $this->roleCount('cashier') > 0,    $this->roleCount('cashier')),    'url' => route('sistem.index')],

            // ── Group: Penomoran ──
            ['key' => 'num_service',  'group' => 'Penomoran', 'label' => 'Nomor Service',   'icon' => '🔖', ...$s('num_service',  $this->hasSetting('service_number_format')),  'url' => route('settings.index')],
            ['key' => 'num_invoice',  'group' => 'Penomoran', 'label' => 'Nomor Invoice',   'icon' => '🧾', ...$s('num_invoice',  $this->hasSetting('invoice_prefix')),         'url' => route('settings.index')],
            ['key' => 'num_purchase', 'group' => 'Penomoran', 'label' => 'Nomor Pembelian', 'icon' => '📄', ...$s('num_purchase', $this->hasSetting('purchase_number_format')), 'url' => route('settings.index')],
            ['key' => 'num_po',       'group' => 'Penomoran', 'label' => 'Nomor PO',        'icon' => '📋', ...$s('num_po',       $this->hasSetting('po_number_format')),       'url' => route('settings.index')],

            // ── Group: Printer ──
            ['key' => 'printer_nota',  'group' => 'Printer', 'label' => 'Printer Nota',  'icon' => '🖨️', ...$s('printer_nota',  $this->hasSetting('printer_configured')),       'url' => route('pengaturan.providers')],
            ['key' => 'printer_label', 'group' => 'Printer', 'label' => 'Printer Label', 'icon' => '🏷️', ...$s('printer_label', $this->hasSetting('printer_label_configured')), 'url' => route('pengaturan.providers')],

            // ── Group: Operasional ──
            ['key' => 'op_hours',       'group' => 'Operasional', 'label' => 'Jam Operasional',             'icon' => '🕐', ...$s('op_hours',       $this->hasSetting('operational_hours')),     'url' => route('settings.index')],
            ['key' => 'currency',       'group' => 'Operasional', 'label' => 'Mata Uang',                   'icon' => '💱', ...$s('currency',       $this->hasSetting('currency')),              'url' => route('settings.index')],
            ['key' => 'tax',            'group' => 'Operasional', 'label' => 'Pajak',                       'icon' => '📋', ...$s('tax',            $this->hasSetting('tax_rate')),              'url' => route('tax.index')],
            ['key' => 'default_checklist', 'group' => 'Operasional', 'label' => 'Checklist Penerimaan Default', 'icon' => '✅', ...$s('default_checklist', ChecklistTemplate::where('is_active', true)->count() > 0), 'url' => route('checklist-templates.index')],

            // ── Group: Data ──
            ['key' => 'data_customers',   'group' => 'Data', 'label' => 'Customer',   'icon' => '👤', ...$s('data_customers',   Customer::count() > 0,  Customer::count()),  'url' => route('customers.index')],
            ['key' => 'data_products',    'group' => 'Data', 'label' => 'Produk',     'icon' => '📦', ...$s('data_products',    Product::count() > 0,   Product::count()),   'url' => route('products.index')],
            ['key' => 'data_suppliers',   'group' => 'Data', 'label' => 'Supplier',   'icon' => '🏭', ...$s('data_suppliers',   Supplier::count() > 0,  Supplier::count()),  'url' => '#'],
            ['key' => 'data_spareparts',  'group' => 'Data', 'label' => 'Sparepart',  'icon' => '🔩', ...$s('data_spareparts',  Product::where('type', 'sparepart')->orWhere('category', 'sparepart')->count() > 0, Product::where('type', 'sparepart')->orWhere('category', 'sparepart')->count()), 'url' => route('products.index')],
            ['key' => 'data_technicians', 'group' => 'Data', 'label' => 'Teknisi',    'icon' => '🔧', ...$s('data_technicians', User::where('role', 'technician')->count() > 0, User::where('role', 'technician')->count()), 'url' => route('sistem.index')],
        ];
    }

    // ──────────────────────────────────────────────
    //  DYNAMIC SEVERITY RESOLUTION (Sprint 7.5F Final)
    //
    //  Severity is NEVER hardcoded. Evaluated based on:
    //    1. Tenant Business Type (retail_only, full_service, etc.)
    //    2. Enabled Features (FeatureEngine)
    //    3. Installed Modules (Module activation)
    //
    //  Principle: Only BLOCKING items stop business processes.
    //  WARNING = should fix, ops continue. INFO = ignored.
    // ──────────────────────────────────────────────

    /**
     * Resolve checklist item severity dynamically.
     *
     * @param string $itemKey The checklist item key
     * @return string 'blocking' | 'warning' | 'info'
     */
    private function resolveSeverity(string $itemKey): string
    {
        $bt = tenant()->getBusinessType();
        $fe = app(FeatureEngine::class);
        $t  = tenant();

        // Feature capabilities (derived from FeatureEngine)
        $hasServices     = $fe->can($t, 'services');
        $hasSales        = $fe->can($t, 'sales');
        $hasPurchases    = $fe->can($t, 'purchases');
        $hasInventory    = $fe->can($t, 'inventaris');
        $hasMultiBranch  = $fe->can($t, 'multi_branch');
        $hasChecklist    = $fe->can($t, 'checklist');

        // Business type characteristics
        $isRetailOnly      = $bt === 'retail_only';
        $isServiceDilempar = $bt === 'aksesoris_service'; // services exist but technicians are optional
        $hasInHouseTech    = in_array($bt, ['full_service', 'gadget_full', 'aksespare_service']);

        return match ($itemKey) {
            // ── Service-dependent items ──
            'user_technician', 'data_technicians' => match (true) {
                !$hasServices      => 'info',       // no services = no technician needed
                $isServiceDilempar => 'warning',    // services exist, techs optional (dilempar)
                default            => 'blocking',   // in-house tech is required
            },
            'num_service'      => $hasServices      ? 'blocking' : 'info',
            'user_cs'          => $hasServices      ? 'blocking' : 'info',
            'default_checklist'=> $hasChecklist     ? 'warning'  : 'info',

            // ── Sales-dependent items ──
            'cash'             => $hasSales         ? 'blocking' : 'info',
            'printer_nota',
            'printer_label'    => $hasSales         ? 'warning'  : 'info',
            'num_invoice'      => $hasSales         ? 'warning'  : 'info',
            'user_cashier'     => $hasSales         ? 'warning'  : 'info',

            // ── Purchases-dependent items ──
            'num_purchase',
            'num_po'           => $hasPurchases     ? 'warning'  : 'info',
            'data_suppliers'   => $hasPurchases     ? 'warning'  : 'info',

            // ── Inventory-dependent items ──
            'warehouses'       => $hasInventory     ? 'blocking' : 'info',
            'user_gudang'      => $hasInventory     ? 'warning'  : 'info',

            // ── Multi-branch-dependent items ──
            'branches'         => $hasMultiBranch   ? 'blocking' : 'warning',

            // ── Core identity (always relevant) ──
            'store_name'       => 'blocking',
            'user_owner'       => 'blocking',

            // ── Always warning (nice to have, not blocking) ──
            'logo', 'address', 'phone', 'user_admin',
            'op_hours', 'currency',
            'data_customers', 'data_products', 'data_spareparts'
                => 'warning',

            // ── Always informational (optional) ──
            'npwp', 'tax'      => 'info',

            default => 'warning',
        };
    }

    /**
     * Resolve health item severity dynamically.
     * Same principle as resolveSeverity() — checks business context.
     */
    private function resolveHealthSeverity(string $healthKey): string
    {
        $bt = tenant()->getBusinessType();
        $fe = app(FeatureEngine::class);
        $t  = tenant();

        $hasServices    = $fe->can($t, 'services');
        $hasSales       = $fe->can($t, 'sales');
        $hasInventory   = $fe->can($t, 'inventaris');
        $hasPurchases   = $fe->can($t, 'purchases');
        $hasMultiBranch = $fe->can($t, 'multi_branch');
        $isRetailOnly   = $bt === 'retail_only';
        $isServiceDilempar = $bt === 'aksesoris_service';

        return match ($healthKey) {
            // Service-dependent
            'no_technician'     => match (true) {
                !$hasServices      => 'info',
                $isServiceDilempar => 'warning',
                default            => 'blocking',
            },
            'no_cs'             => $hasServices    ? 'blocking' : 'info',
            'no_service_number' => $hasServices    ? 'blocking' : 'info',

            // Sales-dependent
            'no_cash'           => $hasSales       ? 'blocking' : 'info',
            'no_printer'        => $hasSales       ? 'warning'  : 'info',

            // Inventory-dependent
            'no_warehouse'      => $hasInventory   ? 'blocking' : 'info',

            // Purchases-dependent
            'no_supplier'       => $hasPurchases   ? 'warning'  : 'info',

            // Multi-branch
            'no_branch'         => $hasMultiBranch ? 'blocking' : 'warning',

            // Always relevant (core)
            'no_store_name'     => 'blocking',
            'stock_negative'    => 'blocking',  // bad data always blocking

            // Always warning
            'no_logo', 'no_address', 'no_phone', 'stock_low'
                => 'warning',

            // Always informational
            'no_tax' => 'info',

            default => 'warning',
        };
    }

    /** Health check — dynamic severity based on business context (NO platform checks) */
    private function healthCheck(): array
    {
        $items = [];
        $h = fn(string $key, string $message) => ['status' => $this->resolveHealthSeverity($key), 'message' => $message];

        // Stock issues
        $negStock = Product::where('stock_quantity', '<', 0)->count();
        if ($negStock > 0) $items[] = $h('stock_negative', "{$negStock} produk stok minus");

        $lowStock = Product::where('stock_status', 'low')->orWhereColumn('stock_quantity', '<=', 'min_stock')->count();
        if ($lowStock > 0) $items[] = $h('stock_low', "{$lowStock} produk stok rendah");

        // Missing operational data — each resolves severity dynamically
        if (StockLocation::count() === 0)                                $items[] = $h('no_warehouse',      'Belum ada gudang');
        if (!CashRegister::exists())                                     $items[] = $h('no_cash',           'Belum ada kas awal');
        if (User::where('role', 'technician')->count() === 0)            $items[] = $h('no_technician',     'Tidak ada teknisi');
        if (User::where('role', 'cs')->count() === 0)                    $items[] = $h('no_cs',             'Tidak ada CS');
        if (!$this->hasSetting('service_number_format'))                 $items[] = $h('no_service_number', 'Nomor service belum diatur');
        if (!$this->hasSetting('store_name'))                            $items[] = $h('no_store_name',     'Nama toko belum diatur');
        if (Branch::count() <= 1)                                        $items[] = $h('no_branch',         'Belum ada cabang');
        if (!$this->hasSetting('logo'))                                  $items[] = $h('no_logo',           'Logo belum diupload');
        if (!$this->hasSetting('printer_configured'))                    $items[] = $h('no_printer',        'Printer nota belum dikonfigurasi');
        if (Supplier::count() === 0)                                     $items[] = $h('no_supplier',       'Supplier kosong');
        if (!$this->hasSetting('address'))                               $items[] = $h('no_address',        'Alamat toko belum diatur');
        if (!$this->hasSetting('phone'))                                 $items[] = $h('no_phone',          'Telepon toko belum diatur');

        // Informational only
        if (!$this->hasSetting('tax_rate') && !$this->hasSetting('tax_number')) {
            $items[] = $h('no_tax', 'Pajak belum dikonfigurasi (opsional)');
        }

        // Reserved for Sprint 8.0 Platform Administration:
        // - SMTP health check (Super Admin only)
        // - Queue worker health (Super Admin only)
        // - Backup health (Super Admin only)

        return $items;
    }

    // ──────────────────────────────────────────────
    //  DATA CONSISTENCY
    // ──────────────────────────────────────────────

    /** Data consistency check */
    private function dataConsistencyCheck(): array
    {
        return [
            ['label' => 'Customer tanpa nomor HP', 'count' => Customer::whereNull('phone')->orWhere('phone', '')->count(), 'severity' => 'warning'],
            ['label' => 'Produk tanpa SKU',        'count' => Product::whereNull('sku')->orWhere('sku', '')->count(),       'severity' => 'warning'],
            ['label' => 'Stok minus',             'count' => Product::where('stock_quantity', '<', 0)->count(),             'severity' => 'blocking'],
            ['label' => 'Supplier kosong',         'count' => Supplier::count() === 0 ? 1 : 0,                               'severity' => 'warning'],
            ['label' => 'Branch tanpa gudang',     'count' => Branch::whereDoesntHave('locations')->count(),                 'severity' => 'warning'],
            ['label' => 'User tanpa role',         'count' => User::whereDoesntHave('roles')->count(),                       'severity' => 'warning'],
            ['label' => 'Device tanpa customer',   'count' => \App\Models\Tenant\Device::whereNull('customer_id')->count(),  'severity' => 'info'],
        ];
    }

    // ──────────────────────────────────────────────
    //  HELPERS
    // ──────────────────────────────────────────────

    /**
     * Calculate config completion %.
     * Only blocking + warning items count toward progress.
     * Info items do NOT affect progress.
     */
    private function calculateProgress(array $checklist): int
    {
        $countable = collect($checklist)->filter(fn($item) => $item['status'] !== 'info');
        $done = $countable->where('status', 'done')->count();
        $total = $countable->count();
        return $total > 0 ? (int) round(($done / $total) * 100) : 0;
    }

    private function hasSetting(string $key): bool
    {
        $value = TenantSetting::getValue($key);
        return $value !== null && $value !== '' && $value !== '0';
    }

    private function roleCount(string $role): int
    {
        return User::where('role', $role)->count();
    }

    // ──────────────────────────────────────────────
    //  SETUP ASSISTANT BEHAVIOR (Sprint 7.5F Final)
    // ──────────────────────────────────────────────

    /**
     * Lightweight summary for Dashboard Setup Progress Card.
     *
     * Three-level severity model:
     *   - blocking: business CANNOT operate without this
     *   - warning:  should fix, ops continue
     *   - info:     purely informational
     *
     * Overall status:
     *   - READY:               config 100%, no health blocking/warning
     *   - READY_WITH_WARNING:  config 100%, health has warnings
     *   - NOT_READY:           config < 100% OR health has blocking items
     */
    public function summary(): array
    {
        $checklist = $this->buildChecklist();
        $configProgress = $this->calculateProgress($checklist);
        $healthItems = $this->healthCheck();
        $healthStatus = $this->healthStatus($healthItems);

        // Count by severity
        $blockingItems = collect($checklist)->where('status', 'blocking')->values()->toArray();
        $warningItems  = collect($checklist)->where('status', 'warning')->values()->toArray();
        $infoItems     = collect($checklist)->where('status', 'info')->values()->toArray();

        $hidden = TenantSetting::getValue('setup_card_hidden', false);
        $firstLoginDismissed = TenantSetting::getValue('setup_first_login_dismissed', false);

        // Overall status
        $overallStatus = 'NOT_READY';
        if ($configProgress >= 100) {
            $overallStatus = ($healthStatus === 'blocking' || $healthStatus === 'warning')
                ? 'READY_WITH_WARNING'
                : 'READY';
        }

        return [
            // Configuration
            'configProgress'   => $configProgress,
            'configComplete'   => $configProgress >= 100,
            'configDone'       => collect($checklist)->where('status', 'done')->count(),
            'configTotal'      => collect($checklist)->filter(fn($i) => $i['status'] !== 'info')->count(),
            'remainingCount'   => collect($checklist)->whereNotIn('status', ['done', 'info'])->count(),

            // Severity breakdown
            'blockingCount'    => count($blockingItems),
            'blockingItems'    => $blockingItems,
            'warningCount'     => count($warningItems),
            'warningItems'     => $warningItems,
            'infoCount'        => count($infoItems),

            // Health (separate from config)
            'healthStatus'         => $healthStatus,
            'healthItems'          => $healthItems,
            'healthBlockingCount'  => collect($healthItems)->where('status', 'blocking')->count(),
            'healthWarningCount'   => collect($healthItems)->where('status', 'warning')->count(),
            'healthInfoCount'      => collect($healthItems)->where('status', 'info')->count(),

            // Overall
            'overallStatus'   => $overallStatus,

            // Visibility
            'isHidden'             => $hidden === true || $hidden === '1' || $hidden === 'true',
            'firstLoginDismissed'  => $firstLoginDismissed === true || $firstLoginDismissed === '1' || $firstLoginDismissed === 'true',
        ];
    }

    /** Derive health status from health items */
    private function healthStatus(array $healthItems): string
    {
        $hasBlocking = collect($healthItems)->contains('status', 'blocking');
        $hasWarning  = collect($healthItems)->contains('status', 'warning');

        if ($hasBlocking) return 'blocking';
        if ($hasWarning)  return 'warning';
        return 'ready';
    }

    /**
     * Dismiss the dashboard setup card permanently.
     * Owner/Manager clicks "Sembunyikan" → never show card again.
     * Setup page at /setup remains accessible.
     */
    public function dismiss(Request $request)
    {
        TenantSetting::setValue('setup_card_hidden', 'true');

        if ($request->wantsJson()) {
            return response()->json(['dismissed' => true]);
        }

        return back()->with('success', 'Kartu setup disembunyikan.');
    }

    /**
     * Explicit first-login dismissal.
     * Owner clicks "Nanti" or "Jangan tampilkan lagi" →
     *   - No more redirect to /setup on next login
     *   - Dashboard card may still show (separate setting)
     */
    public function dismissFirstLogin(Request $request)
    {
        TenantSetting::setValue('setup_first_login_dismissed', 'true');

        if ($request->wantsJson()) {
            return response()->json(['dismissed' => true]);
        }

        return redirect()->route('dashboard')->with('success', 'Setup Assistant dapat diakses kapan saja dari menu Pengaturan.');
    }
}
