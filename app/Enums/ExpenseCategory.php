<?php

namespace App\Enums;

/**
 * #9 Finance Refinement: standardized expense categories for daily close.
 */
enum ExpenseCategory: string
{
    case EmergencyPurchase = 'emergency_purchase';
    case Operational      = 'operational';
    case Rent             = 'rent';
    case Utilities        = 'utilities';
    case Salary           = 'salary';
    case Supplies         = 'supplies';
    case Maintenance      = 'maintenance';
    case Marketing        = 'marketing';
    case Transportation   = 'transportation';
    case Other            = 'other';

    public function label(): string
    {
        return match ($this) {
            self::EmergencyPurchase => 'Pembelian Darurat',
            self::Operational      => 'Operasional',
            self::Rent             => 'Sewa Tempat',
            self::Utilities        => 'Listrik/Air/Internet',
            self::Salary           => 'Gaji & Bonus',
            self::Supplies         => 'Perlengkapan',
            self::Maintenance      => 'Perbaikan & Maintenance',
            self::Marketing        => 'Marketing & Iklan',
            self::Transportation   => 'Transportasi',
            self::Other            => 'Lain-lain',
        };
    }

    /** Quick options array for select dropdowns. */
    public static function options(): array
    {
        return array_map(fn($c) => [
            'value' => $c->value,
            'label' => $c->label(),
        ], self::cases());
    }
}
