<?php
namespace App\Providers;
use App\Models\Tenant\Service;
use App\Models\Tenant\Sale;
use App\Models\Tenant\Expense;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Product;
use App\Models\Tenant\Branch;
use App\Models\Tenant\Purchase;
use App\Models\Tenant\CashRegister;
use App\Models\Tenant\DailyDeposit;
use App\Models\Tenant\Indent;
use App\Models\Tenant\InventoryMutation;
use App\Models\Tenant\Supplier;
use App\Models\Tenant\User;
use App\Models\Tenant\Request;
use App\Models\Tenant\ServiceRequiredPart;
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
use App\Policies\RequestPolicy;
use App\Policies\ServiceRequiredPartPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Service::class => ServicePolicy::class,
        Sale::class => SalePolicy::class,
        Expense::class => ExpensePolicy::class,
        Customer::class => CustomerPolicy::class,
        Product::class => ProductPolicy::class,
        Branch::class => BranchPolicy::class,
        Purchase::class => PurchasePolicy::class,
        CashRegister::class => CashRegisterPolicy::class,
        DailyDeposit::class => DailyDepositPolicy::class,
        Indent::class => IndentPolicy::class,
        InventoryMutation::class => InventoryMutationPolicy::class,
        Supplier::class => SupplierPolicy::class,
        User::class => TenantUserPolicy::class,
        Request::class => RequestPolicy::class,
        ServiceRequiredPart::class => ServiceRequiredPartPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
