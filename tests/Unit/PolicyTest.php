<?php
namespace Tests\Unit;
use Tests\TestCase;
use App\Models\Tenant\User;
use App\Policies\ServicePolicy;
use App\Policies\SalePolicy;
use App\Policies\ExpensePolicy;
use App\Policies\CustomerPolicy;
use App\Policies\ProductPolicy;
use App\Policies\BranchPolicy;
use App\Policies\PurchasePolicy;
use App\Policies\CashRegisterPolicy;
use App\Policies\DailyDepositPolicy;
use App\Policies\IndentPolicy;
use App\Policies\InventoryMutationPolicy;
use App\Policies\SupplierPolicy;
use App\Policies\TenantUserPolicy;

class PolicyTest extends TestCase
{
    // ============ SERVICE POLICY ============

    public function test_service_policy_view_any_for_technician()
    {
        $user = new User(['role' => 'technician']);
        $policy = new ServicePolicy();
        $this->assertTrue($policy->viewAny($user));
    }

    public function test_service_policy_view_any_for_cashier_should_fail()
    {
        $user = new User(['role' => 'cashier']);
        $policy = new ServicePolicy();
        $this->assertFalse($policy->viewAny($user));
    }

    public function test_service_policy_delete_for_owner()
    {
        $user = new User(['role' => 'owner']);
        $policy = new ServicePolicy();
        $service = new \App\Models\Tenant\Service();
        $this->assertTrue($policy->delete($user, $service));
    }

    public function test_service_policy_assign_for_cs()
    {
        $user = new User(['role' => 'cs']);
        $policy = new ServicePolicy();
        $this->assertTrue($policy->assign($user));
    }

    // ============ SALE POLICY ============

    public function test_sale_policy_view_any_for_owner()
    {
        $user = new User(['role' => 'owner']);
        $policy = new SalePolicy();
        $this->assertTrue($policy->viewAny($user));
    }

    public function test_sale_policy_view_any_for_technician()
    {
        $user = new User(['role' => 'technician']);
        $policy = new SalePolicy();
        $this->assertFalse($policy->viewAny($user));
    }

    // ============ EXPENSE POLICY ============

    public function test_expense_policy_view_any_for_owner()
    {
        $user = new User(['role' => 'owner']);
        $policy = new ExpensePolicy();
        $this->assertTrue($policy->viewAny($user));
    }

    public function test_expense_policy_view_any_for_cs()
    {
        $user = new User(['role' => 'cs']);
        $policy = new ExpensePolicy();
        $this->assertFalse($policy->viewAny($user));
    }

    public function test_expense_policy_create_for_manager()
    {
        $user = new User(['role' => 'manager']);
        $policy = new ExpensePolicy();
        $this->assertTrue($policy->create($user));
    }

    // ============ CUSTOMER POLICY ============

    public function test_customer_policy_view_any_for_cs()
    {
        $user = new User(['role' => 'cs']);
        $policy = new CustomerPolicy();
        $this->assertTrue($policy->viewAny($user));
    }

    public function test_customer_policy_view_any_for_technician()
    {
        $user = new User(['role' => 'technician']);
        $policy = new CustomerPolicy();
        $this->assertFalse($policy->viewAny($user));
    }

    public function test_customer_policy_delete_for_owner()
    {
        $user = new User(['role' => 'owner']);
        $policy = new CustomerPolicy();
        $customer = new \App\Models\Tenant\Customer();
        $this->assertTrue($policy->delete($user, $customer));
    }

    // ============ PRODUCT POLICY ============

    public function test_product_policy_view_any_for_cashier()
    {
        $user = new User(['role' => 'cashier']);
        $policy = new ProductPolicy();
        $this->assertTrue($policy->viewAny($user));
    }

    public function test_product_policy_view_any_for_courier()
    {
        $user = new User(['role' => 'courier']);
        $policy = new ProductPolicy();
        $this->assertFalse($policy->viewAny($user));
    }

    public function test_product_policy_create_for_owner()
    {
        $user = new User(['role' => 'owner']);
        $policy = new ProductPolicy();
        $this->assertTrue($policy->create($user));
    }

    public function test_product_policy_create_for_cs()
    {
        $user = new User(['role' => 'cs']);
        $policy = new ProductPolicy();
        $this->assertFalse($policy->create($user));
    }

    public function test_product_policy_quick_stock_for_owner()
    {
        $user = new User(['role' => 'owner']);
        $policy = new ProductPolicy();
        $this->assertTrue($policy->quickStock($user));
    }

    public function test_product_policy_quick_stock_for_admin()
    {
        $user = new User(['role' => 'admin']);
        $policy = new ProductPolicy();
        $this->assertFalse($policy->quickStock($user));
    }

    // ============ BRANCH POLICY ============

    public function test_branch_policy_view_any_for_manager()
    {
        $user = new User(['role' => 'manager']);
        $policy = new BranchPolicy();
        $this->assertTrue($policy->viewAny($user));
    }

    public function test_branch_policy_create_for_owner()
    {
        $user = new User(['role' => 'owner']);
        $policy = new BranchPolicy();
        $this->assertTrue($policy->create($user));
    }

    public function test_branch_policy_create_for_admin()
    {
        $user = new User(['role' => 'admin']);
        $policy = new BranchPolicy();
        $this->assertFalse($policy->create($user));
    }

    // ============ PURCHASE POLICY ============

    public function test_purchase_policy_view_any_for_manager()
    {
        $user = new User(['role' => 'manager']);
        $policy = new PurchasePolicy();
        $this->assertTrue($policy->viewAny($user));
    }

    public function test_purchase_policy_view_any_for_cs()
    {
        $user = new User(['role' => 'cs']);
        $policy = new PurchasePolicy();
        $this->assertFalse($policy->viewAny($user));
    }

    // ============ CASH REGISTER POLICY ============

    public function test_cash_register_policy_view_any_for_cashier()
    {
        $user = new User(['role' => 'cashier']);
        $policy = new CashRegisterPolicy();
        $this->assertTrue($policy->viewAny($user));
    }

    public function test_cash_register_policy_open_for_cashier()
    {
        $user = new User(['role' => 'cashier']);
        $policy = new CashRegisterPolicy();
        $this->assertTrue($policy->open($user));
    }

    public function test_cash_register_policy_view_any_for_technician()
    {
        $user = new User(['role' => 'technician']);
        $policy = new CashRegisterPolicy();
        $this->assertFalse($policy->viewAny($user));
    }

    // ============ DAILY DEPOSIT POLICY ============

    public function test_daily_deposit_policy_create_for_cashier()
    {
        $user = new User(['role' => 'cashier']);
        $policy = new DailyDepositPolicy();
        $this->assertTrue($policy->create($user));
    }

    public function test_daily_deposit_policy_confirm_for_owner()
    {
        $user = new User(['role' => 'owner']);
        $policy = new DailyDepositPolicy();
        $deposit = new \App\Models\Tenant\DailyDeposit();
        $this->assertTrue($policy->confirm($user, $deposit));
    }

    // ============ INDENT POLICY ============

    public function test_indent_policy_view_any_for_technician()
    {
        $user = new User(['role' => 'technician']);
        $policy = new IndentPolicy();
        $this->assertTrue($policy->viewAny($user));
    }

    public function test_indent_policy_create_for_cs()
    {
        $user = new User(['role' => 'cs']);
        $policy = new IndentPolicy();
        $this->assertTrue($policy->create($user));
    }

    public function test_indent_policy_delete_for_admin()
    {
        $user = new User(['role' => 'admin']);
        $policy = new IndentPolicy();
        $indent = new \App\Models\Tenant\Indent();
        $this->assertFalse($policy->delete($user, $indent));
    }

    // ============ INVENTORY MUTATION POLICY ============

    public function test_inventory_mutation_policy_view_any_for_manager()
    {
        $user = new User(['role' => 'manager']);
        $policy = new InventoryMutationPolicy();
        $this->assertTrue($policy->viewAny($user));
    }

    public function test_inventory_mutation_policy_approve_for_owner()
    {
        $user = new User(['role' => 'owner']);
        $policy = new InventoryMutationPolicy();
        $mutation = new \App\Models\Tenant\InventoryMutation();
        $this->assertTrue($policy->approve($user, $mutation));
    }

    // ============ SUPPLIER POLICY ============

    public function test_supplier_policy_view_any_for_manager()
    {
        $user = new User(['role' => 'manager']);
        $policy = new SupplierPolicy();
        $this->assertTrue($policy->viewAny($user));
    }

    public function test_supplier_policy_view_any_for_cs()
    {
        $user = new User(['role' => 'cs']);
        $policy = new SupplierPolicy();
        $this->assertFalse($policy->viewAny($user));
    }

    // ============ TENANT USER POLICY ============

    public function test_tenant_user_policy_view_any_for_manager()
    {
        $user = new User(['role' => 'manager']);
        $policy = new TenantUserPolicy();
        $this->assertTrue($policy->viewAny($user));
    }

    public function test_tenant_user_policy_delete_owner_should_fail()
    {
        $owner = new User(['role' => 'owner']);
        $targetOwner = new User(['role' => 'owner']);
        $policy = new TenantUserPolicy();
        $this->assertFalse($policy->delete($owner, $targetOwner));
    }
}
