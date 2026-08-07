<?php

namespace App\Enums;

/**
 * UPGRADE-01: Capacity Add-on Catalog.
 *
 * Capacity limits that can be extended beyond the base plan default.
 * Formula: Effective Limit = Base Plan Limit + Σ Active Add-on Capacity
 */
enum SubscriptionCapacity: string
{
    case ExtraUsers    = 'extra_users';
    case ExtraBranches = 'extra_branches';
    case ExtraStorage  = 'extra_storage';
    case ExtraApiRate  = 'extra_api_rate';
    case ExtraAiRate   = 'extra_ai_rate';
    case ExtraWaRate   = 'extra_wa_rate';

    public function label(): string
    {
        return match ($this) {
            self::ExtraUsers     => 'Tambahan User',
            self::ExtraBranches  => 'Tambahan Cabang',
            self::ExtraStorage   => 'Tambahan Storage (GB)',
            self::ExtraApiRate   => 'Tambahan Kuota API',
            self::ExtraAiRate    => 'Tambahan Kuota AI',
            self::ExtraWaRate    => 'Tambahan Kuota WhatsApp',
        };
    }

    public function unit(): string
    {
        return match ($this) {
            self::ExtraUsers     => 'user',
            self::ExtraBranches  => 'cabang',
            self::ExtraStorage   => 'GB',
            self::ExtraApiRate   => 'request',
            self::ExtraAiRate    => 'request',
            self::ExtraWaRate    => 'pesan',
        };
    }

    /**
     * Which capacities are active now (not future).
     * Storage/AI/API/WA become active when those features are actually deployed.
     */
    public static function active(): array
    {
        return [
            self::ExtraUsers,
            self::ExtraBranches,
        ];
    }
}
