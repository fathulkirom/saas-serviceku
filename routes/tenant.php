<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Tenant\SetupController;
use App\Http\Controllers\Tenant\ImportController;
use App\Http\Controllers\Tenant\OperationalControlController;
use App\Http\Controllers\Tenant\UniversalSearchController;
use App\Http\Controllers\Tenant\PosController;
use App\Http\Controllers\Tenant\DailyOperationsController;
use App\Http\Controllers\Tenant\OperationalDashboardController;
use App\Http\Controllers\Tenant\ServicePartController;
use App\Http\Controllers\Tenant\WarehouseController;
use App\Http\Controllers\Tenant\InventoryIntelligenceController;
use App\Http\Controllers\Tenant\ServiceExceptionController;
use App\Http\Controllers\Tenant\ServiceDeliveryController;
use App\Http\Controllers\Tenant\TechnicianWorkflowController;
use App\Http\Controllers\Tenant\ServiceIntakeController;
use App\Http\Controllers\Tenant\ServiceController;
use App\Http\Controllers\Tenant\ServiceWorkflowController;
use App\Http\Controllers\Tenant\ServiceChecklistController;
use App\Http\Controllers\Tenant\ServiceDocumentController;
use App\Http\Controllers\Tenant\ServiceClaimController;
use App\Http\Controllers\Tenant\CustomerController;
use App\Http\Controllers\Tenant\CustomerCommunicationController;
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
    // Sprint 7.3B — Customer Relationship Core
    Route::post('/customers/{customer}/interactions', [CustomerController::class, 'storeInteraction'])->name('customers.interactions.store')->middleware('check.plan.feature:customers');
    Route::post('/customers/{customer}/tags', [CustomerController::class, 'attachTag'])->name('customers.tags.attach')->middleware('check.plan.feature:customers');
    Route::delete('/customers/{customer}/tags/{tag}', [CustomerController::class, 'detachTag'])->name('customers.tags.detach')->middleware('check.plan.feature:customers');
    // Sprint 7.3C — Customer Communication
    Route::post('/customers/{customer}/communications/send', [CustomerCommunicationController::class, 'send'])->name('customers.communications.send')->middleware('check.plan.feature:customers');
    Route::get('/pengaturan/message-templates', [CustomerCommunicationController::class, 'templates'])->name('pengaturan.message-templates')->middleware('check.plan.feature:settings');
    Route::post('/pengaturan/message-templates', [CustomerCommunicationController::class, 'storeTemplate'])->name('message-templates.store')->middleware('check.plan.feature:settings');
    Route::put('/pengaturan/message-templates/{template}', [CustomerCommunicationController::class, 'updateTemplate'])->name('message-templates.update')->middleware('check.plan.feature:settings');
    // Sprint 7.3D — Customer Intelligence
    Route::post('/customers/{customer}/notes', [CustomerController::class, 'storeNote'])->name('customers.notes.store')->middleware('check.plan.feature:customers');
    Route::post('/customers/{customer}/complaints', [CustomerController::class, 'storeComplaint'])->name('customers.complaints.store')->middleware('check.plan.feature:customers');
    Route::post('/customers/{customer}/complaints/{complaint}/resolve', [CustomerController::class, 'resolveComplaint'])->name('customers.complaints.resolve')->middleware('check.plan.feature:customers');
    Route::get('/customers/search', [CustomerController::class, 'search'])->name('customers.search')->middleware('check.plan.feature:customers');
    // Sprint 7.3E-H — Service Intake Hardening
    Route::post('/services/{service}/checklist-results', [ServiceIntakeController::class, 'storeChecklistResults'])->name('services.checklist-results.store')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/capture-snapshot', [ServiceIntakeController::class, 'captureSnapshot'])->name('services.snapshot.capture')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/confirm-condition', [ServiceIntakeController::class, 'confirmCondition'])->name('services.confirm-condition')->middleware('check.plan.feature:services');
    Route::get('/devices/match', [ServiceIntakeController::class, 'matchDevice'])->name('devices.match')->middleware('check.plan.feature:services');
    Route::get('/devices/{device}/health', [ServiceIntakeController::class, 'deviceHealth'])->name('devices.health')->middleware('check.plan.feature:services');
    // Sprint 7.3F — Technician Workflow
    Route::get('/technician/dashboard', [TechnicianWorkflowController::class, 'technicianDashboard'])->name('technician.dashboard')->middleware('check.plan.feature:services');
    Route::post('/work-orders/{workOrder}/assign', [TechnicianWorkflowController::class, 'assignTechnician'])->name('work-orders.assign')->middleware('check.plan.feature:services');
    Route::post('/work-orders/{workOrder}/accept', [TechnicianWorkflowController::class, 'accept'])->name('work-orders.accept')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/diagnosis', [TechnicianWorkflowController::class, 'storeDiagnosis'])->name('services.diagnosis.store')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/quotation', [TechnicianWorkflowController::class, 'createQuotation'])->name('services.quotation.create')->middleware('check.plan.feature:services');
    Route::post('/quotations/{quotation}/approve', [TechnicianWorkflowController::class, 'approveQuotation'])->name('quotations.approve')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/parts', [TechnicianWorkflowController::class, 'requestPart'])->name('services.parts.request')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/qc', [TechnicianWorkflowController::class, 'storeQcCheck'])->name('services.qc.store')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/repair/start', [TechnicianWorkflowController::class, 'startRepair'])->name('services.repair.start')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/repair/complete', [TechnicianWorkflowController::class, 'completeRepair'])->name('services.repair.complete')->middleware('check.plan.feature:services');
    // Sprint 7.3G — Service Delivery & Pickup
    Route::post('/services/{service}/ready-pickup', [ServiceDeliveryController::class, 'markReady'])->name('services.ready-pickup')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/verify-payment', [ServiceDeliveryController::class, 'verifyPayment'])->name('services.verify-payment')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/pickup', [ServiceDeliveryController::class, 'pickup'])->name('services.pickup')->middleware('check.plan.feature:services');
    Route::get('/customers/{customerId}/warranties', [ServiceDeliveryController::class, 'customerWarranties'])->name('customers.warranties')->middleware('check.plan.feature:customers');
    // Sprint 7.3H — Service Exception & After Sales
    Route::post('/services/{service}/warranty-claim', [ServiceExceptionController::class, 'createClaim'])->name('services.warranty-claim.create')->middleware('check.plan.feature:services');
    Route::post('/warranty-claims/{claim}/decide', [ServiceExceptionController::class, 'decideClaim'])->name('warranty-claims.decide')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/revise-diagnosis', [ServiceExceptionController::class, 'reviseDiagnosis'])->name('services.diagnosis.revise')->middleware('check.plan.feature:services');
    Route::get('/monitoring/unclaimed', [ServiceExceptionController::class, 'unclaimed'])->name('monitoring.unclaimed')->middleware('check.plan.feature:monitoring');
    // Sprint 7.4 — Inventory Intelligence
    Route::get('/inventaris/dashboard', [InventoryIntelligenceController::class, 'dashboard'])->name('inventaris.dashboard')->middleware('check.plan.feature:inventaris');
    Route::get('/products/{product}/movements', [InventoryIntelligenceController::class, 'movements'])->name('products.movements')->middleware('check.plan.feature:inventaris');
    // Sprint 7.4 Revision — Real Service Center Part Flow
    Route::post('/services/{service}/parts/request', [ServicePartController::class, 'request'])->name('service-parts.request')->middleware('check.plan.feature:services');
    Route::post('/service-parts/{part}/cancel', [ServicePartController::class, 'cancelRequest'])->name('service-parts.cancel')->middleware('check.plan.feature:services');
    Route::post('/service-parts/{part}/approve', [ServicePartController::class, 'approveRequest'])->name('service-parts.approve')->middleware('check.plan.feature:services');
    Route::post('/service-parts/{part}/use', [ServicePartController::class, 'usePart'])->name('service-parts.use')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/parts/return-request', [ServicePartController::class, 'requestReturn'])->name('service-parts.return-request')->middleware('check.plan.feature:services');
    Route::post('/service-part-returns/{return}/process', [ServicePartController::class, 'processReturn'])->name('service-parts.process-return')->middleware('check.plan.feature:services');
    Route::get('/services/{service}/profit', [ServicePartController::class, 'profit'])->name('services.profit')->middleware('check.plan.feature:services');
    // Sprint 7.4A — Operational Refinement
    Route::get('/inventaris/operational', [OperationalDashboardController::class, 'warehouse'])->name('inventaris.operational')->middleware('check.plan.feature:inventaris');
    Route::get('/dashboard/cs-stats', [OperationalDashboardController::class, 'cs'])->name('dashboard.cs-stats')->middleware('check.plan.feature:services');
    Route::get('/dashboard/owner-kpi', [OperationalDashboardController::class, 'owner'])->name('dashboard.owner-kpi')->middleware('check.plan.feature:dashboard');
    Route::post('/service-parts/{part}/edit', [OperationalDashboardController::class, 'editRequest'])->name('service-parts.edit')->middleware('check.plan.feature:services');
    Route::post('/service-parts/{part}/priority', [OperationalDashboardController::class, 'setPriority'])->name('service-parts.priority')->middleware('check.plan.feature:services');
    // Sprint 7.4B — Daily Operations Hardening
    Route::post('/work-orders/{workOrder}/worklog', [DailyOperationsController::class, 'addWorklog'])->name('work-orders.worklog')->middleware('check.plan.feature:services');
    Route::post('/work-orders/{workOrder}/pause', [DailyOperationsController::class, 'pauseRepair'])->name('work-orders.pause')->middleware('check.plan.feature:services');
    Route::post('/work-orders/{workOrder}/resume', [DailyOperationsController::class, 'resumeRepair'])->name('work-orders.resume')->middleware('check.plan.feature:services');
    Route::post('/work-orders/{workOrder}/finish', [DailyOperationsController::class, 'finishWorkOrder'])->name('work-orders.finish')->middleware('check.plan.feature:services');
    Route::post('/parts/book', [DailyOperationsController::class, 'bookPart'])->name('parts.book')->middleware('check.plan.feature:inventaris');
    Route::post('/services/{service}/lock', [DailyOperationsController::class, 'lockService'])->name('services.lock')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/reopen', [DailyOperationsController::class, 'requestReopen'])->name('services.reopen')->middleware('check.plan.feature:services');
    Route::post('/service-reopens/{reopen}/approve', [DailyOperationsController::class, 'approveReopen'])->name('service-reopens.approve')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/price-change', [DailyOperationsController::class, 'requestPriceChange'])->name('services.price-change')->middleware('check.plan.feature:services');
    Route::post('/price-changes/{change}/approve', [DailyOperationsController::class, 'approvePrice'])->name('price-changes.approve')->middleware('check.plan.feature:services');
    // Sprint 7.5 — Retail, POS & Sales
    Route::post('/cashier-shifts/open', [PosController::class, 'openShift'])->name('shifts.open')->middleware('check.plan.feature:sales');
    Route::post('/cashier-shifts/{shift}/close', [PosController::class, 'closeShift'])->name('shifts.close')->middleware('check.plan.feature:sales');
    Route::post('/sales/{sale}/pay', [PosController::class, 'pay'])->name('sales.pay')->middleware('check.plan.feature:sales');
    Route::post('/sales/{sale}/return', [PosController::class, 'requestReturn'])->name('sales.return')->middleware('check.plan.feature:sales');
    Route::post('/sale-returns/{return}/approve', [PosController::class, 'approveReturn'])->name('sale-returns.approve')->middleware('check.plan.feature:sales');
    Route::post('/sales/{sale}/serials', [PosController::class, 'recordSerial'])->name('sales.serials')->middleware('check.plan.feature:sales');
    Route::get('/dashboard/cashier', [PosController::class, 'cashierDashboard'])->name('dashboard.cashier')->middleware('check.plan.feature:dashboard');
    // Sprint 7.5A — UX & Productivity
    Route::get('/search', [UniversalSearchController::class, 'search'])->name('search');
    // Sprint 7.5C — Operational Control & Management
    Route::get('/services/kanban', [OperationalControlController::class, 'kanban'])->name('services.kanban')->middleware('check.plan.feature:services');
    Route::get('/services/pickup-queue', [OperationalControlController::class, 'pickupQueue'])->name('services.pickup-queue')->middleware('check.plan.feature:services');
    Route::get('/services/approval-center', [OperationalControlController::class, 'approvalCenter'])->name('services.approval-center')->middleware('check.plan.feature:services');
    Route::get('/dashboard/technician-performance', [OperationalControlController::class, 'technicianPerformance'])->name('dashboard.tech-performance')->middleware('check.plan.feature:dashboard');
    Route::get('/dashboard/cs', [OperationalControlController::class, 'csDashboard'])->name('dashboard.cs')->middleware('check.plan.feature:dashboard');
    Route::get('/dashboard/owner', [OperationalControlController::class, 'ownerDashboard'])->name('dashboard.owner')->middleware('check.plan.feature:dashboard');
    Route::get('/dashboard/load-balancer', [OperationalControlController::class, 'loadBalancer'])->name('dashboard.load-balancer')->middleware('check.plan.feature:dashboard');
    Route::get('/dashboard/sla', [OperationalControlController::class, 'slaOverview'])->name('dashboard.sla')->middleware('check.plan.feature:dashboard');
    // Sprint 7.5E — Tenant Onboarding & Data Migration
    Route::get('/pengaturan/import', [ImportController::class, 'index'])->name('import.index')->middleware('check.plan.feature:settings');
    Route::post('/import/preview', [ImportController::class, 'preview'])->name('import.preview')->middleware('check.plan.feature:settings');
    Route::post('/import/process', [ImportController::class, 'import'])->name('import.process')->middleware('check.plan.feature:settings');
    // Sprint 7.5F — Tenant Go-Live & Setup Assistant
    Route::get('/setup', [SetupController::class, 'index'])->name('setup')->middleware('check.plan.feature:settings');
    Route::post('/setup/dismiss', [SetupController::class, 'dismiss'])->name('setup.dismiss')->middleware('check.plan.feature:settings');
    Route::post('/setup/dismiss-first-login', [SetupController::class, 'dismissFirstLogin'])->name('setup.dismiss-first-login')->middleware('check.plan.feature:settings');
    // Sprint 7.4B — Warehouse Operations
    Route::post('/stock-opnames', [WarehouseController::class, 'createOpname'])->name('stock-opnames.create')->middleware('check.plan.feature:inventaris');
    Route::post('/stock-opnames/{opname}/count', [WarehouseController::class, 'recordCount'])->name('stock-opnames.count')->middleware('check.plan.feature:inventaris');
    Route::post('/stock-opnames/{opname}/approve', [WarehouseController::class, 'approveOpname'])->name('stock-opnames.approve')->middleware('check.plan.feature:inventaris');
    Route::post('/product-serials/{serial}/assign', [WarehouseController::class, 'assignSerial'])->name('serials.assign')->middleware('check.plan.feature:inventaris');
    Route::post('/technician-stock/transfer', [WarehouseController::class, 'transferToTechnician'])->name('technician-stock.transfer')->middleware('check.plan.feature:inventaris');
    Route::post('/stock-transfers/{transfer}/receive', [WarehouseController::class, 'receiveTransfer'])->name('stock-transfers.receive')->middleware('check.plan.feature:inventaris');

    // ========== PRODUCTS ==========
    Route::resource('products', ProductController::class)->except(['create', 'edit', 'show'])->middleware('check.plan.feature:products');

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
    Route::resource('indents', IndentController::class)->except(['create', 'edit', 'show'])->middleware('check.plan.feature:indents');
    Route::get('/indents/{indent}/print', [App\Http\Controllers\Tenant\IndentController::class, 'printNota'])->name('indents.print')->middleware('check.plan.feature:indents');

    // ========== BRANCHES (MULTI-CABANG) ==========
    Route::resource('branches', App\Http\Controllers\Tenant\BranchController::class)->except(['edit', 'create', 'show'])->middleware('check.plan.feature:multi_branch');

    // ========== USERS (MANAJEMEN USER) ==========
    Route::resource('users', App\Http\Controllers\Tenant\UserManagementController::class)->except(['create', 'edit', 'index', 'show'])->middleware('check.plan.feature:users');

    // ========== STOCK ALLOCATIONS (TRANSFER STOK) ==========
    Route::resource('stock-allocations', App\Http\Controllers\Tenant\StockAllocationController::class)->except(['create', 'edit', 'update'])->middleware('check.plan.feature:transfer_stock');
    Route::post('/stock-allocations/{stock_allocation}/confirm', [App\Http\Controllers\Tenant\StockAllocationController::class, 'confirm'])->name('stock-allocations.confirm')->middleware('check.plan.feature:transfer_stock');
    Route::post('/stock-allocations/{stock_allocation}/reject', [App\Http\Controllers\Tenant\StockAllocationController::class, 'reject'])->name('stock-allocations.reject')->middleware('check.plan.feature:transfer_stock');

    // ========== EXPENSES ==========
    Route::resource('expenses', ExpenseController::class)->only(['index', 'store'])->middleware('check.plan.feature:expenses');

    // ========== PURCHASE RETURNS ==========
    Route::resource('purchase-returns', App\Http\Controllers\Tenant\PurchaseReturnController::class)->except(['create', 'edit', 'update', 'show'])->middleware('check.plan.feature:purchases');
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
    Route::resource('pickup-deliveries', App\Http\Controllers\Tenant\PickupDeliveryController::class)->except(['show', 'edit', 'update', 'create'])->middleware('check.plan.feature:services');
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
    Route::resource('purchases', App\Http\Controllers\Tenant\PurchaseController::class)->except(['create', 'edit', 'show', 'update', 'destroy'])->middleware('check.plan.feature:purchases');

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

    // Sprint 7.2E — Enterprise Admin Panel (thin controllers → engine delegation)
    Route::middleware(['check.plan.feature:settings'])->group(function () {
        // Provider Center
        Route::get('/pengaturan/providers', [App\Http\Controllers\Tenant\ProviderApiController::class, 'page'])->name('pengaturan.providers');
        Route::post('/providers/{category}/{provider}/test', [App\Http\Controllers\Tenant\ProviderApiController::class, 'test'])->name('tenant.providers.test');
        Route::post('/providers/{category}/{provider}/toggle', [App\Http\Controllers\Tenant\ProviderApiController::class, 'toggle'])->name('tenant.providers.toggle');

        // Workflow Builder
        Route::get('/sistem/workflows', [App\Http\Controllers\Tenant\WorkflowAdminController::class, 'index'])->name('sistem.workflows');
        Route::get('/sistem/workflows/graph', [App\Http\Controllers\Tenant\WorkflowAdminController::class, 'graph'])->name('tenant.workflows.graph');

        // Automation Builder
        Route::get('/sistem/automations', [App\Http\Controllers\Tenant\AutomationAdminController::class, 'index'])->name('sistem.automations');
        Route::post('/sistem/automations', [App\Http\Controllers\Tenant\AutomationAdminController::class, 'store'])->name('tenant.automation.store');
        Route::put('/sistem/automations/{rule}', [App\Http\Controllers\Tenant\AutomationAdminController::class, 'update'])->name('tenant.automation.update');
        Route::post('/sistem/automations/{rule}/toggle', [App\Http\Controllers\Tenant\AutomationAdminController::class, 'toggle'])->name('tenant.automation.toggle');

        // Event Log
        Route::get('/monitoring/event-log', [App\Http\Controllers\Tenant\EventLogController::class, 'index'])->name('tenant.event-log.index');
    });
});

