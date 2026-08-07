<?php

namespace App\Enums;

class BusinessType
{
    public const FULL_SERVICE = 'full_service';

    public const AKSESORIS_SERVICE = 'aksesoris_service';

    public const AKSESPARE_SERVICE = 'aksespare_service';

    public const GADGET_FULL = 'gadget_full';

    public const RETAIL_ONLY = 'retail_only';

    public static function acceptsServices(): array
    {
        return [self::FULL_SERVICE, self::AKSESORIS_SERVICE, self::AKSESPARE_SERVICE, self::GADGET_FULL];
    }

    public static function hasInHouseTech(): array
    {
        return [self::FULL_SERVICE, self::AKSESPARE_SERVICE, self::GADGET_FULL];
    }
}
