<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\Permission;
use App\Models\Tenant\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class PermissionEngineSeeder extends Seeder
{
    /**
     * All permission keys based on Blueprint v1.0 (Sprint 5.1 §PermissionMatrix, Sprint 6.3 §11).
     */
    protected array $permissions = [
        // Customer
        ['key' => 'customer.view', 'label' => 'Lihat Pelanggan', 'module' => 'customer', 'action' => 'view'],
        ['key' => 'customer.create', 'label' => 'Buat Pelanggan', 'module' => 'customer', 'action' => 'create'],
        ['key' => 'customer.update', 'label' => 'Ubah Pelanggan', 'module' => 'customer', 'action' => 'update'],
        ['key' => 'customer.delete', 'label' => 'Hapus Pelanggan', 'module' => 'customer', 'action' => 'delete'],

        // Service
        ['key' => 'service.view', 'label' => 'Lihat Servis', 'module' => 'service', 'action' => 'view'],
        ['key' => 'service.create', 'label' => 'Buat Servis', 'module' => 'service', 'action' => 'create'],
        ['key' => 'service.update', 'label' => 'Ubah Servis', 'module' => 'service', 'action' => 'update'],
        ['key' => 'service.delete', 'label' => 'Hapus Servis', 'module' => 'service', 'action' => 'delete'],
        ['key' => 'service.work', 'label' => 'Kerjakan Servis', 'module' => 'service', 'action' => 'work'],
        ['key' => 'service.assign', 'label' => 'Assign Teknisi', 'module' => 'service', 'action' => 'assign'],
        ['key' => 'service.void', 'label' => 'Void Servis', 'module' => 'service', 'action' => 'void'],
        ['key' => 'service.finish', 'label' => 'Selesaikan Servis', 'module' => 'service', 'action' => 'finish'],
        // BR-FIX-03: pickup is a granular capability (role-hardcoded list replaced)
        ['key' => 'service.pickup', 'label' => 'Proses Pickup Servis', 'module' => 'service', 'action' => 'pickup'],

        // Sales
        ['key' => 'sales.view', 'label' => 'Lihat Penjualan', 'module' => 'sales', 'action' => 'view'],
        ['key' => 'sales.create', 'label' => 'Buat Penjualan', 'module' => 'sales', 'action' => 'create'],
        ['key' => 'sales.update', 'label' => 'Ubah Penjualan', 'module' => 'sales', 'action' => 'update'],
        ['key' => 'sales.void', 'label' => 'Void Penjualan', 'module' => 'sales', 'action' => 'void'],
        ['key' => 'sales.refund', 'label' => 'Refund Penjualan', 'module' => 'sales', 'action' => 'refund'],
        ['key' => 'sales.delete', 'label' => 'Hapus Penjualan', 'module' => 'sales', 'action' => 'delete'],

        // Inventory / Product
        ['key' => 'inventory.view', 'label' => 'Lihat Inventaris', 'module' => 'inventory', 'action' => 'view'],
        ['key' => 'inventory.create', 'label' => 'Tambah Produk', 'module' => 'inventory', 'action' => 'create'],
        ['key' => 'inventory.update', 'label' => 'Ubah Produk', 'module' => 'inventory', 'action' => 'update'],
        ['key' => 'inventory.delete', 'label' => 'Hapus Produk', 'module' => 'inventory', 'action' => 'delete'],
        ['key' => 'inventory.adjust', 'label' => 'Adjust Stok', 'module' => 'inventory', 'action' => 'adjust'],
        ['key' => 'inventory.transfer', 'label' => 'Transfer Stok', 'module' => 'inventory', 'action' => 'transfer'],
        ['key' => 'inventory.quick_stock', 'label' => 'Quick Stock', 'module' => 'inventory', 'action' => 'quick_stock'],

        // Purchase
        ['key' => 'purchase.view', 'label' => 'Lihat Pembelian', 'module' => 'purchase', 'action' => 'view'],
        ['key' => 'purchase.create', 'label' => 'Buat Pembelian', 'module' => 'purchase', 'action' => 'create'],
        ['key' => 'purchase.update', 'label' => 'Ubah Pembelian', 'module' => 'purchase', 'action' => 'update'],
        ['key' => 'purchase.void', 'label' => 'Void Pembelian', 'module' => 'purchase', 'action' => 'void'],

        // Finance
        ['key' => 'finance.view', 'label' => 'Lihat Keuangan', 'module' => 'finance', 'action' => 'view'],
        ['key' => 'finance.manage', 'label' => 'Kelola Keuangan', 'module' => 'finance', 'action' => 'manage'],

        // Cash Register
        ['key' => 'cash_register.view', 'label' => 'Lihat Kas', 'module' => 'cash_register', 'action' => 'view'],
        ['key' => 'cash_register.manage', 'label' => 'Kelola Kas', 'module' => 'cash_register', 'action' => 'manage'],

        // Deposit
        ['key' => 'deposit.view', 'label' => 'Lihat Setoran', 'module' => 'deposit', 'action' => 'view'],
        ['key' => 'deposit.create', 'label' => 'Buat Setoran', 'module' => 'deposit', 'action' => 'create'],
        ['key' => 'deposit.confirm', 'label' => 'Konfirmasi Setoran', 'module' => 'deposit', 'action' => 'confirm'],

        // Expense
        ['key' => 'expense.view', 'label' => 'Lihat Pengeluaran', 'module' => 'expense', 'action' => 'view'],
        ['key' => 'expense.create', 'label' => 'Buat Pengeluaran', 'module' => 'expense', 'action' => 'create'],

        // Supplier
        ['key' => 'supplier.view', 'label' => 'Lihat Supplier', 'module' => 'supplier', 'action' => 'view'],
        ['key' => 'supplier.create', 'label' => 'Buat Supplier', 'module' => 'supplier', 'action' => 'create'],
        ['key' => 'supplier.update', 'label' => 'Ubah Supplier', 'module' => 'supplier', 'action' => 'update'],
        ['key' => 'supplier.delete', 'label' => 'Hapus Supplier', 'module' => 'supplier', 'action' => 'delete'],

        // Branch
        ['key' => 'branch.view', 'label' => 'Lihat Cabang', 'module' => 'branch', 'action' => 'view'],
        ['key' => 'branch.create', 'label' => 'Buat Cabang', 'module' => 'branch', 'action' => 'create'],
        ['key' => 'branch.update', 'label' => 'Ubah Cabang', 'module' => 'branch', 'action' => 'update'],
        ['key' => 'branch.delete', 'label' => 'Hapus Cabang', 'module' => 'branch', 'action' => 'delete'],

        // User Management
        ['key' => 'user.view', 'label' => 'Lihat User', 'module' => 'user', 'action' => 'view'],
        ['key' => 'user.create', 'label' => 'Buat User', 'module' => 'user', 'action' => 'create'],
        ['key' => 'user.update', 'label' => 'Ubah User', 'module' => 'user', 'action' => 'update'],
        ['key' => 'user.delete', 'label' => 'Hapus User', 'module' => 'user', 'action' => 'delete'],

        // Settings
        ['key' => 'settings.view', 'label' => 'Lihat Pengaturan', 'module' => 'settings', 'action' => 'view'],
        ['key' => 'settings.update', 'label' => 'Ubah Pengaturan', 'module' => 'settings', 'action' => 'update'],

        // Reports
        ['key' => 'report.view', 'label' => 'Lihat Laporan', 'module' => 'report', 'action' => 'view'],
        ['key' => 'report.export', 'label' => 'Export Laporan', 'module' => 'report', 'action' => 'export'],

        // Monitoring
        ['key' => 'monitoring.view', 'label' => 'Lihat Monitoring', 'module' => 'monitoring', 'action' => 'view'],

        // Dashboard
        ['key' => 'dashboard.view', 'label' => 'Lihat Dashboard', 'module' => 'dashboard', 'action' => 'view'],

        // Indent
        ['key' => 'indent.view', 'label' => 'Lihat Indent', 'module' => 'indent', 'action' => 'view'],
        ['key' => 'indent.create', 'label' => 'Buat Indent', 'module' => 'indent', 'action' => 'create'],
        ['key' => 'indent.update', 'label' => 'Ubah Indent', 'module' => 'indent', 'action' => 'update'],

        // Checklist
        ['key' => 'checklist.view', 'label' => 'Lihat Checklist', 'module' => 'checklist', 'action' => 'view'],
        ['key' => 'checklist.create', 'label' => 'Buat Checklist', 'module' => 'checklist', 'action' => 'create'],
        ['key' => 'checklist.update', 'label' => 'Ubah Checklist', 'module' => 'checklist', 'action' => 'update'],

        // Document (SOP/KB/QuickReply)
        ['key' => 'document.view', 'label' => 'Lihat Dokumen', 'module' => 'document', 'action' => 'view'],
        ['key' => 'document.create', 'label' => 'Buat Dokumen', 'module' => 'document', 'action' => 'create'],
        ['key' => 'document.update', 'label' => 'Ubah Dokumen', 'module' => 'document', 'action' => 'update'],
        ['key' => 'document.delete', 'label' => 'Hapus Dokumen', 'module' => 'document', 'action' => 'delete'],

        // Warranty (Target — table may not exist yet)
        ['key' => 'warranty.view', 'label' => 'Lihat Garansi', 'module' => 'warranty', 'action' => 'view'],
        ['key' => 'warranty.claim', 'label' => 'Klaim Garansi', 'module' => 'warranty', 'action' => 'claim'],

        // Compensation (Target)
        ['key' => 'compensation.view', 'label' => 'Lihat Kompensasi', 'module' => 'compensation', 'action' => 'view'],
        ['key' => 'compensation.manage', 'label' => 'Kelola Kompensasi', 'module' => 'compensation', 'action' => 'manage'],

        // Policy (Target)
        ['key' => 'policy.view', 'label' => 'Lihat Policy', 'module' => 'policy', 'action' => 'view'],
        ['key' => 'policy.manage', 'label' => 'Kelola Policy', 'module' => 'policy', 'action' => 'manage'],

        // Subscription
        ['key' => 'subscription.view', 'label' => 'Lihat Langganan', 'module' => 'subscription', 'action' => 'view'],
        ['key' => 'subscription.manage', 'label' => 'Kelola Langganan', 'module' => 'subscription', 'action' => 'manage'],

        // Module
        ['key' => 'module.view', 'label' => 'Lihat Modul', 'module' => 'module', 'action' => 'view'],
        ['key' => 'module.manage', 'label' => 'Kelola Modul', 'module' => 'module', 'action' => 'manage'],

        // Provider
        ['key' => 'provider.view', 'label' => 'Lihat Provider', 'module' => 'provider', 'action' => 'view'],
        ['key' => 'provider.manage', 'label' => 'Kelola Provider', 'module' => 'provider', 'action' => 'manage'],

        // Request (ADR-001 — Target)
        ['key' => 'request.view', 'label' => 'Lihat Request', 'module' => 'request', 'action' => 'view'],
        ['key' => 'request.create', 'label' => 'Buat Request', 'module' => 'request', 'action' => 'create'],
        ['key' => 'request.assign', 'label' => 'Assign Request', 'module' => 'request', 'action' => 'assign'],
        ['key' => 'request.cancel', 'label' => 'Batalkan Request', 'module' => 'request', 'action' => 'cancel'],
        ['key' => 'request.override', 'label' => 'Override Request', 'module' => 'request', 'action' => 'override'],

        // Delegation (Target)
        ['key' => 'delegation.grant', 'label' => 'Beri Delegasi', 'module' => 'delegation', 'action' => 'grant'],
        ['key' => 'delegation.revoke', 'label' => 'Cabut Delegasi', 'module' => 'delegation', 'action' => 'revoke'],
    ];

    /**
     * Role definitions.
     */
    protected array $roles = [
        ['key' => 'owner', 'label' => 'Owner', 'is_system' => true, 'description' => 'Pemilik toko — akses penuh'],
        ['key' => 'admin', 'label' => 'Admin', 'is_system' => true, 'description' => 'Administrator toko'],
        ['key' => 'manager', 'label' => 'Manager', 'is_system' => true, 'description' => 'Pengawas operasional'],
        ['key' => 'head_store', 'label' => 'Kepala Toko', 'is_system' => true, 'description' => 'Kepala toko / supervisor'],
        ['key' => 'cs', 'label' => 'Customer Service', 'is_system' => true, 'description' => 'Front desk / penerima servis'],
        ['key' => 'technician', 'label' => 'Teknisi', 'is_system' => true, 'description' => 'Teknisi servis'],
        ['key' => 'cashier', 'label' => 'Kasir', 'is_system' => true, 'description' => 'Kasir / POS'],
        ['key' => 'courier', 'label' => 'Kurir', 'is_system' => true, 'description' => 'Kurir pengiriman'],
        ['key' => 'custom', 'label' => 'Kustom', 'is_system' => true, 'description' => 'Role kustom tenant'],
    ];

    /**
     * Mapping of old capability keys → new permission keys.
     */
    protected array $rolePermissionMapping = [
        'owner' => [
            'user.view', 'user.create', 'user.update', 'user.delete',
            'settings.view', 'settings.update',
            'finance.view', 'finance.manage',
            'inventory.view', 'inventory.create', 'inventory.update', 'inventory.delete', 'inventory.adjust', 'inventory.quick_stock',
            'customer.view', 'customer.create', 'customer.update', 'customer.delete',
            'sales.view', 'sales.create', 'sales.update', 'sales.void', 'sales.refund', 'sales.delete',
            'cash_register.view', 'cash_register.manage',
            'deposit.view', 'deposit.create', 'deposit.confirm',
            'purchase.view', 'purchase.create', 'purchase.update', 'purchase.void',
            'branch.view', 'branch.create', 'branch.update', 'branch.delete',
            'indent.view', 'indent.create', 'indent.update',
            'service.view', 'service.create', 'service.update', 'service.delete', 'service.work', 'service.assign', 'service.void', 'service.finish', 'service.pickup',
            'report.view', 'report.export',
            'monitoring.view',
            'dashboard.view',
            'checklist.view', 'checklist.create', 'checklist.update',
            'document.view', 'document.create', 'document.update', 'document.delete',
            'supplier.view', 'supplier.create', 'supplier.update', 'supplier.delete',
            'expense.view', 'expense.create',
            'warranty.view', 'warranty.claim',
            'compensation.view', 'compensation.manage',
            'policy.view', 'policy.manage',
            'subscription.view', 'subscription.manage',
            'module.view', 'module.manage',
            'provider.view', 'provider.manage',
            'request.view', 'request.create', 'request.assign', 'request.cancel', 'request.override',
            'delegation.grant', 'delegation.revoke',
        ],
        'admin' => [
            'finance.view', 'finance.manage',
            'inventory.view', 'inventory.create', 'inventory.update', 'inventory.delete',
            'customer.view', 'customer.create', 'customer.update', 'customer.delete',
            'sales.view', 'sales.create', 'sales.update', 'sales.void', 'sales.delete',
            'cash_register.view', 'cash_register.manage',
            'deposit.view', 'deposit.create', 'deposit.confirm',
            'purchase.view', 'purchase.create', 'purchase.update', 'purchase.void',
            'indent.view', 'indent.create', 'indent.update',
            'service.view', 'service.create', 'service.update', 'service.delete', 'service.work', 'service.assign', 'service.void', 'service.finish', 'service.pickup',
            'report.view', 'report.export',
            'monitoring.view',
            'dashboard.view',
            'checklist.view', 'checklist.create', 'checklist.update',
            'supplier.view', 'supplier.create', 'supplier.update', 'supplier.delete',
            'expense.view', 'expense.create',
            'warranty.view', 'warranty.claim',
            'request.view', 'request.create', 'request.assign', 'request.cancel',
            'delegation.grant', 'delegation.revoke',
        ],
        'manager' => [
            'finance.view', 'finance.manage',
            'inventory.view', 'inventory.create', 'inventory.update',
            'customer.view', 'customer.create', 'customer.update', 'customer.delete',
            'sales.view', 'sales.create', 'sales.update',
            'cash_register.view', 'cash_register.manage',
            'deposit.view', 'deposit.create',
            'purchase.view', 'purchase.create', 'purchase.update',
            'indent.view', 'indent.create', 'indent.update',
            'service.view', 'service.create', 'service.update', 'service.work', 'service.finish', 'service.pickup',
            'dashboard.view',
            'checklist.view', 'checklist.create', 'checklist.update',
            'supplier.view', 'supplier.create', 'supplier.update', 'supplier.delete',
            'expense.view', 'expense.create',
            'request.view', 'request.create', 'request.assign',
            'delegation.grant', 'delegation.revoke',
        ],
        'head_store' => [
            'finance.view', 'finance.manage',
            'inventory.view',
            'customer.view', 'customer.create',
            'sales.view', 'sales.create', 'sales.update',
            'cash_register.view', 'cash_register.manage',
            'deposit.view', 'deposit.create',
            'service.view', 'service.create', 'service.work', 'service.finish', 'service.pickup',
            'dashboard.view',
            'expense.view', 'expense.create',
            'request.view', 'request.create',
        ],
        'cs' => [
            'customer.view', 'customer.create', 'customer.update', 'customer.delete',
            'indent.view', 'indent.create', 'indent.update',
            'service.view', 'service.create', 'service.work', 'service.assign', 'service.finish', 'service.pickup',
            'sales.view', 'sales.create',
            'dashboard.view',
            'request.view', 'request.create', 'request.assign',
        ],
        'technician' => [
            'service.view', 'service.work', 'service.finish',
            'checklist.view', 'checklist.create', 'checklist.update',
            'dashboard.view',
            'request.view',
        ],
        'cashier' => [
            'sales.view', 'sales.create', 'sales.update',
            'cash_register.view', 'cash_register.manage',
            // PILOT-READY-01: cashier opens the service page and performs
            // counter pickup (matches the menu + toolbar).
            'service.view', 'service.pickup',
            'dashboard.view',
        ],
        'courier' => [
            'dashboard.view',
            'request.view',
        ],
        'custom' => [],
    ];

    public function run(): void
    {
        if (!Schema::hasTable('permissions') || !Schema::hasTable('roles')) {
            return;
        }

        // Seed permissions
        foreach ($this->permissions as $permission) {
            Permission::updateOrCreate(
                ['key' => $permission['key']],
                $permission
            );
        }

        // Seed roles
        foreach ($this->roles as $roleData) {
            $role = Role::updateOrCreate(
                ['key' => $roleData['key']],
                $roleData
            );

            // Assign permissions to role
            $permKeys = $this->rolePermissionMapping[$role->key] ?? [];
            if (!empty($permKeys)) {
                $permIds = Permission::whereIn('key', $permKeys)->pluck('id');
                $role->permissions()->sync($permIds);
            }
        }
    }
}
