<?php

namespace App\Enums;

/**
 * UPGRADE-01: Subscription Module Catalog.
 *
 * Modules are BIG business capabilities. A module can be:
 *   - included in a base plan
 *   - purchased as an add-on
 *
 * The same module entity drives both included and add-on access —
 * subscription only controls entitlement, not the module itself.
 */
enum SubscriptionModule: string
{
    case Service         = 'service';
    case Sales           = 'sales';
    case Inventory       = 'inventory';
    case Finance         = 'finance';
    case Purchasing      = 'purchasing';
    case Reports         = 'reports';
    case MultiBranch     = 'multi_branch';
    case MasterData      = 'master_data';
    case Warranty        = 'warranty';
    case Customers       = 'customers';
    case CashRegister    = 'cash_register';
    case UserManagement  = 'user_management';
    case Settings        = 'settings';
    case Import          = 'import';
    case Demo            = 'demo';

    /** Human-readable label for the catalog UI. */
    public function label(): string
    {
        return match ($this) {
            self::Service         => 'Servis / Reparasi',
            self::Sales           => 'Penjualan / POS',
            self::Inventory       => 'Inventaris & Stok',
            self::Finance         => 'Keuangan',
            self::Purchasing      => 'Pembelian / Purchasing',
            self::Reports         => 'Laporan',
            self::MultiBranch     => 'Multi Cabang',
            self::MasterData      => 'Data Master / Referensi',
            self::Warranty        => 'Garansi',
            self::Customers       => 'Pelanggan / CRM',
            self::CashRegister    => 'Kasir',
            self::UserManagement  => 'Manajemen User',
            self::Settings        => 'Pengaturan',
            self::Import          => 'Import Data',
            self::Demo            => 'Demo Data',
        };
    }

    /** Short description shown in plan comparison. */
    public function description(): string
    {
        return match ($this) {
            self::Service         => 'Manajemen servis, teknisi, diagnosis, QC, dan workflow repair.',
            self::Sales           => 'Point of Sale, penjualan sparepart, invoice customer.',
            self::Inventory       => 'Stok barang, stok minimum, transfer antar cabang, supplier.',
            self::Finance         => 'Laporan keuangan, pengeluaran, pendapatan, rekonsiliasi.',
            self::Purchasing      => 'Purchase order, pembelian ke supplier, penerimaan barang.',
            self::Reports         => 'Laporan operasional, ringkasan harian/bulanan, analytics.',
            self::MultiBranch     => 'Multi cabang dengan data terpisah, transfer stok, user per cabang.',
            self::MasterData      => 'Kategori perangkat, merek, unit, metode kedatangan, kelengkapan.',
            self::Warranty        => 'Klaim garansi, supplier warranty, garansi toko, QC garansi.',
            self::Customers       => 'Data pelanggan, riwayat servis, komunikasi, membership.',
            self::CashRegister    => 'Kasir, shift, tutup kas harian, pencatatan transaksi.',
            self::UserManagement  => 'Tambah/hapus user, role, permission, assignment cabang.',
            self::Settings        => 'Pengaturan toko, profil, WhatsApp, email, Google Drive.',
            self::Import          => 'Import data massal dari file CSV/Excel.',
            self::Demo            => 'Generate data demo untuk testing dan onboarding.',
        };
    }
}
