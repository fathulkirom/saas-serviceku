<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Tenant\ServiceController;
use App\Http\Controllers\Tenant\ServiceWorkflowController;
use App\Http\Controllers\Tenant\ServiceChecklistController;
use App\Http\Controllers\Tenant\ServiceDocumentController;
use App\Http\Controllers\Tenant\ServiceClaimController;
use App\Http\Controllers\Tenant\CustomerController;
use App\Http\Controllers\Tenant\ProductController;
use App\Http\Controllers\Tenant\SaleController;
use App\Http\Controllers\Tenant\SaleStoreController;
use App\Http\Controllers\Tenant\SalePaymentController;
use App\Http\Controllers\Tenant\SaleInvoiceController;
use App\Http\Controllers\Tenant\IndentController;
use App\Http\Controllers\Tenant\ExpenseController;
use App\Http\Controllers\Tenant\DailyDepositController;
use App\Http\Controllers\Tenant\ChecklistTemplateController;
use App\Http\Controllers\Tenant\FinanceController;
use App\Http\Controllers\Tenant\CashController;
use App\Http\Controllers\Tenant\InventarisController;
use App\Http\Controllers\Tenant\ServiceToolsController;
use App\Http\Controllers\Tenant\SystemController;
use App\Http\Controllers\Tenant\DocumentController;
use App\Http\Controllers\Tenant\SettingController;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the TenantRouteServiceProvider and are
| only accessible after tenant identification.
|
*/

// ========== LOGIN (public, no auth required) ==========
Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'create'])->name('login');
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'store'])->name('login.post')->middleware('throttle:login');

// ========== PASSWORD RESET (public, no auth required) ==========
Route::get('/forgot-password', [App\Http\Controllers\Auth\ResetPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [App\Http\Controllers\Auth\ResetPasswordController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('password.update');

// ========== EMAIL VERIFICATION (semi-public, needs auth but not verified) ==========
Route::get('/email/verify', [App\Http\Controllers\Auth\EmailVerificationController::class, 'notice'])
    ->middleware('auth')->name('tenant.verification.notice');
Route::get('/email/verify/{id}/{hash}', [App\Http\Controllers\Auth\EmailVerificationController::class, 'verify'])
    ->middleware(['auth', 'signed'])->name('tenant.verification.verify');
Route::post('/email/verification-notification', [App\Http\Controllers\Auth\EmailVerificationController::class, 'resend'])
    ->middleware(['auth', 'throttle:6,1'])->name('tenant.verification.resend');

// ========== 2FA (two-factor authentication) ==========
Route::get('/two-factor-challenge', [App\Http\Controllers\Auth\TwoFactorController::class, 'challenge'])
    ->name('two-factor.challenge');
Route::post('/two-factor-challenge', [App\Http\Controllers\Auth\TwoFactorController::class, 'verify'])
    ->name('two-factor.verify');
Route::post('/two-factor-challenge/email', [App\Http\Controllers\Auth\TwoFactorController::class, 'sendEmailCode'])
    ->name('two-factor.send-email');
Route::post('/two-factor-challenge/verify-email', [App\Http\Controllers\Auth\TwoFactorController::class, 'verifyEmailCode'])
    ->name('two-factor.verify-email');

// ========== 2FA SETUP (authenticated) ==========
Route::middleware(['auth'])->group(function () {
    Route::get('/two-factor-status', [App\Http\Controllers\Auth\TwoFactorSetupController::class, 'status'])
        ->name('two-factor.status');
    Route::post('/two-factor-enable', [App\Http\Controllers\Auth\TwoFactorSetupController::class, 'enable'])
        ->name('two-factor.enable');
    Route::post('/two-factor-confirm', [App\Http\Controllers\Auth\TwoFactorSetupController::class, 'confirm'])
        ->name('two-factor.confirm');
    Route::post('/two-factor-disable', [App\Http\Controllers\Auth\TwoFactorSetupController::class, 'disable'])
        ->name('two-factor.disable');
    Route::post('/two-factor-regenerate-codes', [App\Http\Controllers\Auth\TwoFactorSetupController::class, 'regenerateCodes'])
        ->name('two-factor.regenerate-codes');
});

// ========== DEV LOGIN (development only - accessible on tenant subdomain) ==========
if (app()->environment('local', 'development')) {
    Route::get('/dev-login', App\Http\Controllers\DevLoginController::class)->name('tenant.dev-login');
}

Route::middleware([
    'tenancy.session',
    'auth',
    'check.subscription',
])->group(function () {

    // ========== DASHBOARD ==========
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/onboarding', [App\Http\Controllers\Tenant\OnboardingController::class, 'index'])->name('onboarding.index');

    // ========== SERVICES ==========
    Route::resource('services', ServiceController::class)->only(['index', 'create', 'show', 'update', 'edit', 'destroy'])->middleware('check.plan.feature:services');
    Route::post('/services', [ServiceWorkflowController::class, 'store'])->name('services.store')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/take-over', [ServiceWorkflowController::class, 'takeOver'])->name('services.take-over')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/assign-technician', [ServiceWorkflowController::class, 'assignTechnician'])->name('services.assign-technician')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/indent', [ServiceWorkflowController::class, 'setIndent'])->name('services.indent')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/resume-from-indent', [ServiceWorkflowController::class, 'resumeFromIndent'])->name('services.resume-from-indent')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/accept', [ServiceWorkflowController::class, 'accept'])->name('services.accept')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/start', [ServiceWorkflowController::class, 'start'])->name('services.start')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/finish', [ServiceWorkflowController::class, 'finish'])->name('services.finish')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/request-reallocation', [ServiceWorkflowController::class, 'requestReallocation'])->name('services.request-reallocation')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/confirm-customer', [ServiceWorkflowController::class, 'confirmCustomer'])->name('services.confirm-customer')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/confirm-internal', [ServiceWorkflowController::class, 'confirmInternal'])->name('services.confirm-internal')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/approve-confirmation', [ServiceWorkflowController::class, 'approveConfirmation'])->name('services.approve-confirmation')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/cancel', [ServiceWorkflowController::class, 'cancel'])->name('services.cancel')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/partner', [ServiceWorkflowController::class, 'partner'])->name('services.partner')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/complete-partner', [ServiceWorkflowController::class, 'completePartner'])->name('services.complete-partner')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/complete', [ServiceDocumentController::class, 'complete'])->name('services.complete')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/checklists', [ServiceChecklistController::class, 'saveChecklist'])->name('services.checklists.store')->middleware('check.plan.feature:services');
    Route::get('/services/{service}/print-receipt', [ServiceDocumentController::class, 'printReceipt'])->name('services.print-receipt')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/warranty-claim', [ServiceClaimController::class, 'createWarrantyClaim'])->name('services.warranty-claim')->middleware('check.plan.feature:services');
    Route::post('/services/bulk-status', [ServiceWorkflowController::class, 'bulkUpdateStatus'])->name('services.bulk-status')->middleware('check.plan.feature:services');

    // ========== SERVICE TRANSFERS ==========
    Route::get('/service-transfers/create', [App\Http\Controllers\Tenant\ServiceTransferController::class, 'create'])->name('service-transfers.create')->middleware('check.plan.feature:transfer_stock');
    Route::post('/service-transfers', [App\Http\Controllers\Tenant\ServiceTransferController::class, 'store'])->name('service-transfers.store')->middleware('check.plan.feature:transfer_stock');

    // ========== CUSTOMERS ==========
    Route::resource('customers', CustomerController::class)->except(['edit'])->middleware('check.plan.feature:customers');
    Route::post('/customers/ajax-store', [CustomerController::class, 'storeApi'])->name('customers.ajax-store')->middleware('check.plan.feature:customers');
    Route::post('/customers/{customer}/register-member', [CustomerController::class, 'registerMember'])->name('customers.register-member')->middleware('check.plan.feature:customers');

    // ========== PRODUCTS ==========
    Route::resource('products', ProductController::class)->except(['edit'])->middleware('check.plan.feature:products');

    // ========== SALES ==========
    Route::resource('sales', SaleController::class)->only(['index', 'create', 'show'])->middleware('check.plan.feature:sales');
    Route::post('/sales', [SaleStoreController::class, 'store'])->name('sales.store')->middleware('check.plan.feature:sales');
    Route::post('/sales/draft-from-service/{service}', [SaleStoreController::class, 'draftFromService'])->name('sales.draft-from-service')->middleware('check.plan.feature:sales');
    Route::post('/sales/{sale}/pay-draft', [SalePaymentController::class, 'payDraft'])->name('sales.pay-draft')->middleware('check.plan.feature:sales');
    Route::get('/sales/{sale}/print', [SaleInvoiceController::class, 'print'])->name('sales.print')->middleware('check.plan.feature:sales');
    Route::get('/sales/{sale}/print-checklist', [SaleInvoiceController::class, 'printChecklist'])->name('sales.print-checklist')->middleware('check.plan.feature:sales');
    Route::post('/sales/{sale}/void', [SalePaymentController::class, 'void'])->name('sales.void')->middleware('check.plan.feature:sales');
    Route::post('/sales/{sale}/items', [SaleStoreController::class, 'updateItems'])->name('sales.items.update')->middleware('check.plan.feature:sales');

    // ========== INDENTS ==========
    Route::resource('indents', IndentController::class)->middleware('check.plan.feature:indents');
    Route::get('/indents/{indent}/print', [App\Http\Controllers\Tenant\IndentController::class, 'printNota'])->name('indents.print')->middleware('check.plan.feature:indents');

    // ========== BRANCHES (MULTI-CABANG) ==========
    Route::resource('branches', App\Http\Controllers\Tenant\BranchController::class)->except(['edit'])->middleware('check.plan.feature:multi_branch');

    // ========== USERS (MANAJEMEN USER) ==========
    Route::resource('users', App\Http\Controllers\Tenant\UserManagementController::class)->except(['create', 'edit'])->middleware('check.plan.feature:users');

    // ========== STOCK ALLOCATIONS (TRANSFER STOK) ==========
    Route::resource('stock-allocations', App\Http\Controllers\Tenant\StockAllocationController::class)->except(['edit', 'update'])->middleware('check.plan.feature:transfer_stock');
    Route::post('/stock-allocations/{stock_allocation}/confirm', [App\Http\Controllers\Tenant\StockAllocationController::class, 'confirm'])->name('stock-allocations.confirm')->middleware('check.plan.feature:transfer_stock');
    Route::post('/stock-allocations/{stock_allocation}/reject', [App\Http\Controllers\Tenant\StockAllocationController::class, 'reject'])->name('stock-allocations.reject')->middleware('check.plan.feature:transfer_stock');

    // ========== EXPENSES ==========
    Route::resource('expenses', ExpenseController::class)->only(['index', 'store'])->middleware('check.plan.feature:expenses');

    // ========== PURCHASE RETURNS ==========
    Route::resource('purchase-returns', App\Http\Controllers\Tenant\PurchaseReturnController::class)->except(['edit', 'update'])->middleware('check.plan.feature:purchases');
    Route::post('/purchase-returns/{purchaseReturn}/status', [App\Http\Controllers\Tenant\PurchaseReturnController::class, 'updateStatus'])->name('purchase-returns.status')->middleware('check.plan.feature:purchases');

    // ========== DAMAGED STOCKS ==========
    Route::get('/inventory/damaged', [App\Http\Controllers\Tenant\DamagedStockController::class, 'index'])->name('inventory.damaged')->middleware('check.plan.feature:products');
    Route::post('/inventory/damaged', [App\Http\Controllers\Tenant\DamagedStockController::class, 'store'])->name('inventory.damaged.store')->middleware('check.plan.feature:products');

    // ========== REORDER ALERTS ==========
    Route::get('/inventory/reorder-alerts', [App\Http\Controllers\Tenant\InventoryController::class, 'reorderAlerts'])->name('inventory.reorder-alerts')->middleware('check.plan.feature:products');

    // ========== STOCK FORECASTING ==========
    Route::get('/inventory/forecast', [App\Http\Controllers\Tenant\InventoryController::class, 'forecast'])->name('inventory.forecast')->middleware('check.plan.feature:products');

    // ========== PARTNER TEKNISI ==========
    Route::resource('partner-teknisi', App\Http\Controllers\Tenant\PartnerTeknisiController::class)->except(['create', 'edit', 'show'])->middleware('check.plan.feature:services');

    // ========== PICKUP & DELIVERY ==========
    Route::resource('pickup-deliveries', App\Http\Controllers\Tenant\PickupDeliveryController::class)->except(['show', 'edit', 'update'])->middleware('check.plan.feature:services');
    Route::post('/pickup-deliveries/{pickupDelivery}/status', [App\Http\Controllers\Tenant\PickupDeliveryController::class, 'updateStatus'])->name('pickup-deliveries.status')->middleware('check.plan.feature:services');

    // ========== TAX SETTINGS ==========
    Route::get('/tax', [App\Http\Controllers\Tenant\TaxController::class, 'index'])->name('tax.index');
    Route::post('/tax', [App\Http\Controllers\Tenant\TaxController::class, 'update'])->name('tax.update');
    Route::post('/tax/calculate', [App\Http\Controllers\Tenant\TaxController::class, 'calculate'])->name('tax.calculate');

    // ========== PAYMENT RECONCILIATION ==========
    Route::get('/reconciliations', [App\Http\Controllers\Tenant\ReconciliationController::class, 'index'])->name('reconciliations.index');
    Route::post('/reconciliations', [App\Http\Controllers\Tenant\ReconciliationController::class, 'store'])->name('reconciliations.store');
    Route::post('/reconciliations/{paymentReconciliation}/status', [App\Http\Controllers\Tenant\ReconciliationController::class, 'updateStatus'])->name('reconciliations.status');

    // ========== COMMISSIONS ==========
    Route::resource('commissions', App\Http\Controllers\Tenant\CommissionController::class)->only(['index'])->middleware('check.plan.feature:services');
    Route::post('/commissions/{commission}/pay', [App\Http\Controllers\Tenant\CommissionController::class, 'pay'])->name('commissions.pay')->middleware('check.plan.feature:services');
    Route::post('/commissions/pay-bulk', [App\Http\Controllers\Tenant\CommissionController::class, 'payBulk'])->name('commissions.pay-bulk')->middleware('check.plan.feature:services');

    // ========== SHIFTS ==========
    Route::resource('shifts', App\Http\Controllers\Tenant\ShiftController::class)->except(['create', 'edit', 'show'])->middleware('check.plan.feature:settings');

    // ========== ATTENDANCES ==========
    Route::get('/attendances', [App\Http\Controllers\Tenant\AttendanceController::class, 'index'])->name('attendances.index');
    Route::post('/attendances/clock-in', [App\Http\Controllers\Tenant\AttendanceController::class, 'clockIn'])->name('attendances.clock-in');
    Route::post('/attendances/clock-out', [App\Http\Controllers\Tenant\AttendanceController::class, 'clockOut'])->name('attendances.clock-out');
    Route::post('/attendances/{attendance}/status', [App\Http\Controllers\Tenant\AttendanceController::class, 'updateStatus'])->name('attendances.status');
    Route::get('/attendances/report', [App\Http\Controllers\Tenant\AttendanceController::class, 'report'])->name('attendances.report');

    // ========== CUSTOM FIELDS ==========
    Route::resource('custom-fields', App\Http\Controllers\Tenant\CustomFieldController::class)->except(['create', 'edit', 'show'])->middleware('check.plan.feature:settings');



    // ========== SERVICE PHOTOS ==========
    Route::post('/services/{service}/photos', [App\Http\Controllers\Tenant\ServicePhotoController::class, 'store'])->name('services.photos.store')->middleware('check.plan.feature:services');
    Route::delete('/services/{service}/photos/{servicePhoto}', [App\Http\Controllers\Tenant\ServicePhotoController::class, 'destroy'])->name('services.photos.destroy')->middleware('check.plan.feature:services');

    // ========== GOOGLE LOGIN (tenant level) ==========
    Route::get('/auth/google/redirect', [App\Http\Controllers\Auth\GoogleLoginController::class, 'redirect'])->name('google.login');
    Route::get('/auth/google/callback', [App\Http\Controllers\Auth\GoogleLoginController::class, 'callback'])->name('google.callback');
    Route::post('/auth/google/unlink', [App\Http\Controllers\Auth\GoogleLoginController::class, 'unlink'])->name('google.unlink');

    // ========== QR SCANNER ==========
    Route::get('/tools/qr-scanner', function () { return inertia('Tools/QRScanner'); })->name('qr-scanner')->middleware('check.plan.feature:services');

    // ========== GOOGLE DRIVE SETTINGS ==========
    Route::get('/drive/connect', [App\Http\Controllers\Tenant\DriveController::class, 'connect'])->name('drive.connect');
    Route::get('/drive/callback', [App\Http\Controllers\Tenant\DriveController::class, 'callback'])->name('drive.callback');
    Route::get('/drive/status', [App\Http\Controllers\Tenant\DriveController::class, 'status'])->name('drive.status');
    Route::post('/drive/disconnect', [App\Http\Controllers\Tenant\DriveController::class, 'disconnect'])->name('drive.disconnect');

    // ========== QUICK REPLIES ==========
    Route::resource('quick-replies', App\Http\Controllers\Tenant\QuickReplyController::class)->except(['create', 'edit', 'show'])->middleware('check.plan.feature:settings');

    // ========== KNOWLEDGE BASE ==========
    Route::get('/knowledge-base/search', [App\Http\Controllers\Tenant\KnowledgeBaseController::class, 'search'])->name('knowledge-base.search');
    Route::resource('knowledge-base', App\Http\Controllers\Tenant\KnowledgeBaseController::class)->except(['create', 'edit', 'show'])->middleware('check.plan.feature:services');

    // ========== SOP ==========
    Route::resource('sops', App\Http\Controllers\Tenant\SopController::class)->except(['create', 'edit', 'show'])->middleware('check.plan.feature:settings');
    Route::post('/sops/{sop}/mark-read', [App\Http\Controllers\Tenant\SopController::class, 'markRead'])->name('sops.mark-read');

    // ========== DEMO MODE ==========
    Route::get('/demo', [App\Http\Controllers\Tenant\DemoController::class, 'index'])->name('demo.index');
    Route::post('/demo/generate', [App\Http\Controllers\Tenant\DemoController::class, 'generate'])->name('demo.generate');
    Route::post('/demo/reset', [App\Http\Controllers\Tenant\DemoController::class, 'reset'])->name('demo.reset');
    Route::post('/demo/toggle-mode', [App\Http\Controllers\Tenant\DemoController::class, 'toggleMode'])->name('demo.toggle');

    // ========== PURCHASES ==========
    Route::resource('purchases', App\Http\Controllers\Tenant\PurchaseController::class)->except(['edit', 'update', 'destroy'])->middleware('check.plan.feature:purchases');

    // ========== DAILY DEPOSITS ==========
    Route::resource('daily-deposits', DailyDepositController::class)->only(['index', 'store'])->middleware('check.plan.feature:deposits');

    // ========== CHECKLIST TEMPLATES ==========
    Route::resource('checklist-templates', ChecklistTemplateController::class)->except(['create', 'edit', 'show'])->middleware('check.plan.feature:checklist');

    // ========== INVENTORY MUTATIONS ==========
    Route::get('/inventory/mutations', [App\Http\Controllers\Tenant\InventoryController::class, 'mutations'])->name('inventory.mutations')->middleware('check.plan.feature:products');
    Route::post('/inventory/adjustments', [App\Http\Controllers\Tenant\InventoryController::class, 'adjustment'])->name('inventory.adjustments.store')->middleware('check.plan.feature:products');
    Route::post('/products/{product}/quick-stock', [App\Http\Controllers\Tenant\ProductController::class, 'quickStock'])->name('products.quick-stock')->middleware('check.plan.feature:products');

    // ========== CASH REGISTERS ==========
    Route::get('/cash-registers', [App\Http\Controllers\Tenant\CashRegisterController::class, 'index'])->name('cash-registers.index')->middleware('check.plan.feature:cash_register');
    Route::post('/cash-registers/open', [App\Http\Controllers\Tenant\CashRegisterController::class, 'open'])->name('cash-registers.open')->middleware('check.plan.feature:cash_register');
    Route::post('/cash-registers/close', [App\Http\Controllers\Tenant\CashRegisterController::class, 'close'])->name('cash-registers.close')->middleware('check.plan.feature:cash_register');

    // ========== MASTER DATA ==========
    Route::get('/master-data', [App\Http\Controllers\Tenant\MasterDataController::class, 'index'])->name('master-data.index')->middleware('check.plan.feature:master_data');
    Route::post('/master-data', [App\Http\Controllers\Tenant\MasterDataController::class, 'store'])->name('master-data.store')->middleware('check.plan.feature:master_data');
    Route::post('/master-data/{masterData}', [App\Http\Controllers\Tenant\MasterDataController::class, 'update'])->name('master-data.update')->middleware('check.plan.feature:master_data');
    Route::delete('/master-data/{masterData}', [App\Http\Controllers\Tenant\MasterDataController::class, 'destroy'])->name('master-data.destroy')->middleware('check.plan.feature:master_data');

    // ========== MASTER LABOR SERVICES ==========
    Route::get('/master-services', [App\Http\Controllers\Tenant\MasterDataController::class, 'laborIndex'])->name('master-services.index')->middleware('check.plan.feature:master_data');
    Route::post('/master-services', [App\Http\Controllers\Tenant\MasterDataController::class, 'laborStore'])->name('master-services.store')->middleware('check.plan.feature:master_data');
    Route::post('/master-services/{masterLaborService}', [App\Http\Controllers\Tenant\MasterDataController::class, 'laborUpdate'])->name('master-services.update')->middleware('check.plan.feature:master_data');
    Route::delete('/master-services/{masterLaborService}', [App\Http\Controllers\Tenant\MasterDataController::class, 'laborDestroy'])->name('master-services.destroy')->middleware('check.plan.feature:master_data');

    // ========== REPORTS ==========
    Route::get('/reports', [App\Http\Controllers\Tenant\ReportController::class, 'index'])->name('reports.index')->middleware('check.plan.feature:reports');
    Route::get('/reports/sales', [App\Http\Controllers\Tenant\ReportController::class, 'sales'])->name('reports.sales')->middleware('check.plan.feature:reports');
    Route::get('/reports/services', [App\Http\Controllers\Tenant\ReportController::class, 'services'])->name('reports.services')->middleware('check.plan.feature:reports');
    Route::get('/reports/inventory', [App\Http\Controllers\Tenant\ReportController::class, 'inventory'])->name('reports.inventory')->middleware('check.plan.feature:reports');
    Route::get('/reports/finance', [App\Http\Controllers\Tenant\ReportController::class, 'finance'])->name('reports.finance')->middleware('check.plan.feature:reports');
    Route::get('/reports/commissions', [App\Http\Controllers\Tenant\ReportController::class, 'commissions'])->name('reports.commissions')->middleware('check.plan.feature:reports');
    Route::get('/reports/productivity', [App\Http\Controllers\Tenant\ReportController::class, 'productivity'])->name('reports.productivity')->middleware('check.plan.feature:reports');
    Route::get('/reports/customer-analytics', [App\Http\Controllers\Tenant\ReportController::class, 'customerAnalytics'])->name('reports.customer-analytics')->middleware('check.plan.feature:reports');
    Route::get('/reports/revenue-comparison', [App\Http\Controllers\Tenant\ReportController::class, 'revenueComparison'])->name('reports.revenue-comparison')->middleware('check.plan.feature:reports');
    Route::get('/reports/export/{type}', [App\Http\Controllers\Tenant\ReportController::class, 'export'])->name('reports.export')->middleware('check.plan.feature:reports');

    // ========== TENANT PROFILE (Toko) ==========
    Route::get('/profile', [App\Http\Controllers\Tenant\TenantProfileController::class, 'index'])->name('profile.index');

    // ========== USER PROFILE (Akun Saya) ==========
    Route::get('/my-profile', [App\Http\Controllers\Tenant\UserManagementController::class, 'myProfile'])->name('user.profile');
    Route::put('/my-profile', [App\Http\Controllers\Tenant\UserManagementController::class, 'updateProfile'])->name('user.profile.update');
    Route::put('/my-preferences', [App\Http\Controllers\Tenant\UserManagementController::class, 'updatePreferences'])->name('user.preferences.update');
    Route::get('/users/{userManagement}/menu-access', [App\Http\Controllers\Tenant\UserManagementController::class, 'getMenuAccess'])->name('users.menu-access');
    Route::post('/users/{userManagement}/menu-access', [App\Http\Controllers\Tenant\UserManagementController::class, 'updateMenuAccess'])->name('users.menu-access.update');

    // ========== BILLING / VOUCHER ==========
    Route::get('/billing', [App\Http\Controllers\Tenant\BillingController::class, 'index'])->name('billing.index');
    Route::post('/billing/apply-voucher', [App\Http\Controllers\Tenant\BillingController::class, 'applyVoucher'])->name('billing.apply-voucher');
    Route::post('/billing/initiate', [App\Http\Controllers\Tenant\PaymentController::class, 'initiate'])->name('payment.initiate');
    Route::get('/payment/callback', [App\Http\Controllers\Tenant\PaymentController::class, 'callback'])->name('payment.callback');
    Route::get('/payment/history', [App\Http\Controllers\Tenant\PaymentController::class, 'history'])->name('payment.history');
    Route::post('/payment/{payment}/confirm-manual', [App\Http\Controllers\Tenant\PaymentController::class, 'confirmManual'])->name('payment.confirm-manual');
    Route::get('/payment/finish', [App\Http\Controllers\Tenant\PaymentController::class, 'callback'])->name('payment.finish');
    Route::get('/payment/unfinish', function () { return inertia('Tenant/Payment', ['status' => 'pending']); })->name('payment.unfinish');
    Route::get('/payment/error', function () { return inertia('Tenant/Payment', ['status' => 'error']); })->name('payment.error');

    // ========== SETTINGS / BRANDING ==========
    Route::get('/settings', [App\Http\Controllers\Tenant\SettingsController::class, 'index'])->name('settings.index')->middleware('check.plan.feature:settings');
    Route::post('/settings', [App\Http\Controllers\Tenant\SettingsController::class, 'update'])->name('settings.update')->middleware('check.plan.feature:settings');
    Route::post('/settings/logo', [App\Http\Controllers\Tenant\SettingsController::class, 'uploadLogo'])->name('settings.upload-logo')->middleware('check.plan.feature:settings');
    Route::post('/settings/theme', [App\Http\Controllers\Tenant\SettingsController::class, 'updateTheme'])->name('settings.theme')->middleware('check.plan.feature:settings');
    Route::post('/settings/layout', [App\Http\Controllers\Tenant\SettingsController::class, 'updateLayoutPreferences'])->name('settings.layout.update');
    Route::post('/settings/maintenance', [App\Http\Controllers\Tenant\SettingsController::class, 'updateMaintenance'])->name('settings.maintenance.update')->middleware('check.plan.feature:settings');
    Route::get('/settings/whatsapp-gateway', [App\Http\Controllers\Tenant\SettingsController::class, 'whatsappGateway'])->name('settings.whatsapp-gateway.show')->middleware('check.plan.feature:settings');
    Route::post('/settings/whatsapp-gateway', [App\Http\Controllers\Tenant\SettingsController::class, 'updateWhatsappGateway'])->name('settings.whatsapp-gateway.update')->middleware('check.plan.feature:settings');
    Route::get('/settings/whatsapp-link', [App\Http\Controllers\Tenant\SettingsController::class, 'getWhatsappLink'])->name('settings.whatsapp-link')->middleware('check.plan.feature:settings');

    // ========== MONITORING ==========
    Route::get('/monitoring', [App\Http\Controllers\Tenant\MonitoringController::class, 'index'])->name('monitoring.index')->middleware('check.plan.feature:monitoring');
    Route::get('/monitoring/activities', [App\Http\Controllers\Tenant\MonitoringController::class, 'activities'])->name('monitoring.activities')->middleware('check.plan.feature:monitoring');
    Route::get('/monitoring/logins', [App\Http\Controllers\Tenant\MonitoringController::class, 'logins'])->name('monitoring.logins')->middleware('check.plan.feature:monitoring');
    Route::get('/monitoring/alerts', [App\Http\Controllers\Tenant\MonitoringController::class, 'alerts'])->name('monitoring.alerts')->middleware('check.plan.feature:monitoring');
    Route::post('/monitoring/alerts/{system_alert}/dismiss', [App\Http\Controllers\Tenant\MonitoringController::class, 'dismissAlert'])->name('monitoring.dismiss-alert')->middleware('check.plan.feature:monitoring');
    Route::post('/monitoring/alerts/dismiss-all', [App\Http\Controllers\Tenant\MonitoringController::class, 'dismissAllAlerts'])->name('monitoring.dismiss-all-alerts')->middleware('check.plan.feature:monitoring');
    Route::post('/monitoring/check-low-stock', [App\Http\Controllers\Tenant\MonitoringController::class, 'checkLowStock'])->name('monitoring.check-low-stock')->middleware('check.plan.feature:monitoring');

    // ========== SEARCH ==========
    Route::get('/search', [App\Http\Controllers\Tenant\SearchController::class, 'search'])->name('search');

    // ========== CONSOLIDATED ROUTES ==========

    // Keuangan
    Route::middleware(['check.plan.feature:sales'])->group(function () {
        Route::get('/keuangan', [FinanceController::class, 'index'])->name('keuangan.index');
    });

    // Kas
    Route::middleware(['check.plan.feature:deposits'])->group(function () {
        Route::get('/kas', [CashController::class, 'index'])->name('kas.index');
    });

    // Inventaris
    Route::middleware(['check.plan.feature:products'])->group(function () {
        Route::get('/inventaris', [InventarisController::class, 'index'])->name('inventaris.index');
    });

    // Servis Tools
    Route::middleware(['check.plan.feature:services'])->group(function () {
        Route::get('/servis-tools', [ServiceToolsController::class, 'index'])->name('servis-tools.index');
    });

    // Sistem
    Route::middleware(['check.plan.feature:users'])->group(function () {
        Route::get('/sistem', [SystemController::class, 'index'])->name('sistem.index');
    });

    // Dokumen
    Route::middleware(['check.plan.feature:settings'])->group(function () {
        Route::get('/dokumen', [DocumentController::class, 'index'])->name('dokumen.index');
    });

    // Pengaturan
    Route::get('/pengaturan', [SettingController::class, 'index'])->name('pengaturan.index');

});

