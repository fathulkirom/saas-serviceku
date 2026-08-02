<?php

namespace App\Enums;

/**
 * Shared Status Constants — Sprint 7.5F Standardization.
 *
 * Single source of truth for common status values across all modules.
 * Individual models may define additional domain-specific constants.
 *
 * Usage:
 *   use App\Enums\Status;
 *   $service->status = Status::PENDING;
 */

class Status
{
    // ── Generic ──
    public const PENDING    = 'pending';
    public const ACTIVE     = 'active';
    public const INACTIVE   = 'inactive';
    public const DRAFT      = 'draft';
    public const COMPLETED  = 'completed';
    public const CANCELLED  = 'cancelled';
    public const CLOSED     = 'closed';
    public const ARCHIVED   = 'archived';

    // ── Approval ──
    public const APPROVED   = 'approved';
    public const REJECTED   = 'rejected';
    public const SUBMITTED  = 'submitted';

    // ── Payment ──
    public const PAID       = 'paid';
    public const UNPAID     = 'unpaid';
    public const REFUNDED   = 'refunded';
    public const EXPIRED    = 'expired';
    public const FAILED     = 'failed';
    public const SUCCESS    = 'success';

    // ── Process ──
    public const PROCESSING = 'processing';
    public const WAITING    = 'waiting';
    public const IN_PROGRESS = 'in_progress';
    public const ON_HOLD    = 'on_hold';
    public const PAUSED     = 'paused';
    public const DONE       = 'done';

    // ── Assignment ──
    public const ASSIGNED   = 'assigned';
    public const ACCEPTED   = 'accepted';
    public const RETURNED   = 'returned';
    public const RESERVED   = 'reserved';
    public const USED       = 'used';

    // ── State ──
    public const OPEN       = 'open';
    public const RESOLVED   = 'resolved';
    public const VOID       = 'void';

    /**
     * All common statuses for validation.
     */
    public static function all(): array
    {
        return (new \ReflectionClass(static::class))->getConstants();
    }
}

/**
 * Role Constants.
 */
class Role
{
    public const OWNER      = 'owner';
    public const ADMIN      = 'admin';
    public const MANAGER    = 'manager';
    public const HEAD_STORE = 'head_store';
    public const CS         = 'cs';
    public const TECHNICIAN = 'technician';
    public const CASHIER    = 'cashier';
    public const COURIER    = 'courier';
    public const CUSTOM     = 'custom';

    /** Roles that can view Setup Progress Card by default */
    public static function canViewSetupCard(): array
    {
        return [self::OWNER, self::MANAGER];
    }

    /** Operational roles (never see setup card) */
    public static function operational(): array
    {
        return [self::CS, self::TECHNICIAN, self::CASHIER, self::HEAD_STORE, self::COURIER];
    }
}

/**
 * Business Type Constants.
 */
class BusinessType
{
    public const FULL_SERVICE        = 'full_service';
    public const AKSESORIS_SERVICE   = 'aksesoris_service';
    public const AKSESPARE_SERVICE   = 'aksespare_service';
    public const GADGET_FULL         = 'gadget_full';
    public const RETAIL_ONLY         = 'retail_only';

    /** Types that accept services */
    public static function acceptsServices(): array
    {
        return [self::FULL_SERVICE, self::AKSESORIS_SERVICE, self::AKSESPARE_SERVICE, self::GADGET_FULL];
    }

    /** Types with in-house technicians */
    public static function hasInHouseTech(): array
    {
        return [self::FULL_SERVICE, self::AKSESPARE_SERVICE, self::GADGET_FULL];
    }
}

/**
 * Module Constants.
 */
class Module
{
    public const CORE       = 'core';
    public const SERVICES   = 'services';
    public const INVENTORY  = 'inventory';
    public const POS        = 'pos';
    public const CRM        = 'crm';
    public const HR         = 'hr';
    public const FINANCE    = 'finance';
    public const REPORTS    = 'reports';
    public const SETTINGS   = 'settings';
}

/**
 * Permission Constants.
 */
class Permission
{
    public const MANAGE_USERS       = 'manage_users';
    public const MANAGE_SETTINGS    = 'manage_settings';
    public const MANAGE_FINANCE     = 'manage_finance';
    public const MANAGE_PRODUCTS    = 'manage_products';
    public const MANAGE_CUSTOMERS   = 'manage_customers';
    public const MANAGE_SALES       = 'manage_sales';
    public const MANAGE_CASH        = 'manage_cash_register';
    public const MANAGE_DEPOSITS    = 'manage_deposits';
    public const MANAGE_PURCHASES   = 'manage_purchases';
    public const MANAGE_BRANCHES    = 'manage_branches';
    public const MANAGE_INDENTS     = 'manage_indents';
    public const VOID_TRANSACTIONS  = 'void_transactions';
    public const ASSIGN_TECHNICIAN  = 'assign_technician';
    public const WORK_ON_SERVICES   = 'work_on_services';
    public const DELETE_MODELS      = 'delete_models';
    public const QUICK_STOCK        = 'quick_stock';
}
