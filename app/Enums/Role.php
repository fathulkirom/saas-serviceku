<?php

namespace App\Enums;

class Role
{
    public const OWNER = 'owner';

    public const ADMIN = 'admin';

    public const MANAGER = 'manager';

    public const HEAD_STORE = 'head_store';

    public const CS = 'cs';

    public const TECHNICIAN = 'technician';

    public const CASHIER = 'cashier';

    public const COURIER = 'courier';

    public const CUSTOM = 'custom';

    public static function canViewSetupCard(): array
    {
        return [self::OWNER, self::MANAGER];
    }

    public static function operational(): array
    {
        return [self::CS, self::TECHNICIAN, self::CASHIER, self::HEAD_STORE, self::COURIER];
    }
}
