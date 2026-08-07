<?php

/**
 * UPGRADE-01: Subscription Matrix — THE single source of truth.
 *
 * Every application surface (landing, register, middleware, admin, tenant)
 * reads plan entitlements from this catalog. A hardcoded fallback or inline
 * plan check must never override these definitions.
 *
 * GOLDEN RULE: data never deleted, access/capacity may change.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Base Plans
    |--------------------------------------------------------------------------
    |
    | Each plan defines its baseline: included modules, included features,
    | and default capacity limits. Tenant entitlements = base plan + active
    | add-ons (modules, features, capacities).
    |
    */
    'plans' => [

        'trial' => [
            'name'        => 'Trial',
            'trial_days'  => 14,
            'max_users'   => 2,
            'max_branches'=> 1,
            'modules' => [
                'service', 'sales', 'inventory', 'customers',
                'cash_register', 'user_management', 'settings', 'demo',
            ],
            'features' => [
                'custom_fields' => 'read_only',
            ],
        ],

        'basic' => [
            'name'        => 'Basic',
            'max_users'   => 3,
            'max_branches'=> 1,
            'modules' => [
                'service', 'sales', 'inventory', 'customers',
                'cash_register', 'user_management', 'settings',
                'master_data', 'reports', 'warranty',
            ],
            'features' => [
                'custom_fields'  => 'full',
                'google_drive'   => 'full',
                'email_verification' => 'none', // available as add-on
            ],
        ],

        'pro' => [
            'name'        => 'Pro',
            'max_users'   => 10,
            'max_branches'=> 5,
            'modules' => [
                'service', 'sales', 'inventory', 'customers',
                'cash_register', 'user_management', 'settings',
                'master_data', 'reports', 'warranty',
                'finance', 'multi_branch', 'purchasing', 'import',
            ],
            'features' => [
                'custom_fields'    => 'full',
                'google_drive'     => 'full',
                'email_verification' => 'full',
                'two_factor_auth'  => 'full',
                'whatsapp'         => 'read_only',
            ],
        ],

        'enterprise' => [
            'name'        => 'Enterprise',
            'max_users'   => 999,
            'max_branches'=> 999,
            'modules' => [
                'service', 'sales', 'inventory', 'customers',
                'cash_register', 'user_management', 'settings',
                'master_data', 'reports', 'warranty',
                'finance', 'multi_branch', 'purchasing', 'import',
            ],
            'features' => [
                'custom_fields'    => 'full',
                'google_drive'     => 'full',
                'email_verification' => 'full',
                'two_factor_auth'  => 'full',
                'whatsapp'         => 'full',
                'analytics'        => 'full',
                'automation'       => 'full',
            ],
            // Enterprise → negotiated/custom limits; these are fallback defaults.
            'custom_limits' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Module & Feature Access Levels
    |--------------------------------------------------------------------------
    */
    'module_access' => [
        'none'     => ['create' => false, 'read' => false, 'update' => false, 'delete' => false],
        'read_only'=> ['create' => false, 'read' => true,  'update' => false, 'delete' => false],
        'full'     => ['create' => true,  'read' => true,  'update' => true,  'delete' => true],
    ],

    'feature_access' => [
        'none'      => 0,
        'read_only' => 1,
        'full'      => 2,
        'quota'     => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Downgrade Protection
    |--------------------------------------------------------------------------
    |
    | When a tenant downgrades, excess users/branches/modules are NOT deleted.
    | They become inactive/locked. The owner chooses which resources stay active.
    |
    */
    'downgrade' => [
        'delete_data'           => false,  // NEVER delete data on downgrade
        'suspend_excess_users'  => true,   // Mark over-limit users as inactive
        'lock_excess_branches'  => true,   // Lock branches beyond entitlement
        'restrict_excess_modules'=> true,  // Make modules read-only, not deleted
    ],
];
