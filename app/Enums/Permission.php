<?php

namespace App\Enums;

class Permission
{
    public const MANAGE_USERS = 'manage_users';

    public const MANAGE_SETTINGS = 'manage_settings';

    public const MANAGE_FINANCE = 'manage_finance';

    public const MANAGE_PRODUCTS = 'manage_products';

    public const MANAGE_CUSTOMERS = 'manage_customers';

    public const MANAGE_SALES = 'manage_sales';

    public const MANAGE_CASH = 'manage_cash_register';

    public const MANAGE_DEPOSITS = 'manage_deposits';

    public const MANAGE_PURCHASES = 'manage_purchases';

    public const MANAGE_BRANCHES = 'manage_branches';

    public const MANAGE_INDENTS = 'manage_indents';

    public const VOID_TRANSACTIONS = 'void_transactions';

    public const ASSIGN_TECHNICIAN = 'assign_technician';

    public const WORK_ON_SERVICES = 'work_on_services';

    public const DELETE_MODELS = 'delete_models';

    public const QUICK_STOCK = 'quick_stock';
}
