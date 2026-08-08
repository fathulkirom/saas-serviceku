<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\GoogleLoginController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Auth\TwoFactorSetupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DevLoginController;
use App\Http\Controllers\Tenant\AttendanceController;
use App\Http\Controllers\Tenant\AutomationAdminController;
use App\Http\Controllers\Tenant\BillingController;
use App\Http\Controllers\Tenant\BranchController;
use App\Http\Controllers\Tenant\CashController;
use App\Http\Controllers\Tenant\CashRegisterController;
use App\Http\Controllers\Tenant\ChecklistTemplateController;
use App\Http\Controllers\Tenant\CommissionController;
use App\Http\Controllers\Tenant\CustomerCommunicationController;
use App\Http\Controllers\Tenant\CustomerController;
use App\Http\Controllers\Tenant\CustomerPortalController;
use App\Http\Controllers\Tenant\CustomFieldController;
use App\Http\Controllers\Tenant\DailyDepositController;
use App\Http\Controllers\Tenant\DailyOperationsController;
use App\Http\Controllers\Tenant\DamagedStockController;
use App\Http\Controllers\Tenant\DemoController;
use App\Http\Controllers\Tenant\DocumentController;
use App\Http\Controllers\Tenant\DriveController;
use App\Http\Controllers\Tenant\EmergencyPurchaseController;
use App\Http\Controllers\Tenant\EventLogController;
use App\Http\Controllers\Tenant\ExpenseController;
use App\Http\Controllers\Tenant\FinanceController;
use App\Http\Controllers\Tenant\ImportController;
use App\Http\Controllers\Tenant\IndentController;
use App\Http\Controllers\Tenant\InventarisController;
use App\Http\Controllers\Tenant\InventoryController;
use App\Http\Controllers\Tenant\InventoryIntelligenceController;
use App\Http\Controllers\Tenant\KnowledgeBaseController;
use App\Http\Controllers\Tenant\MasterDataController;
use App\Http\Controllers\Tenant\MonitoringController;
use App\Http\Controllers\Tenant\OnboardingController;
use App\Http\Controllers\Tenant\OperationalControlController;
use App\Http\Controllers\Tenant\OperationalDashboardController;
use App\Http\Controllers\Tenant\PartnerTeknisiController;
use App\Http\Controllers\Tenant\PaymentController;
use App\Http\Controllers\Tenant\PickupDeliveryController;
use App\Http\Controllers\Tenant\PosController;
use App\Http\Controllers\Tenant\ProductController;
use App\Http\Controllers\Tenant\ProviderApiController;
use App\Http\Controllers\Tenant\PurchaseController;
use App\Http\Controllers\Tenant\PurchaseReturnController;
use App\Http\Controllers\Tenant\QuickReplyController;
use App\Http\Controllers\Tenant\ReconciliationController;
use App\Http\Controllers\Tenant\ReportController;
use App\Http\Controllers\Tenant\SaleController;
use App\Http\Controllers\Tenant\SaleInvoiceController;
use App\Http\Controllers\Tenant\SalePaymentController;
use App\Http\Controllers\Tenant\SaleStoreController;
use App\Http\Controllers\Tenant\SearchController;
use App\Http\Controllers\Tenant\ServiceChecklistController;use App\Http\Controllers\Tenant\ServiceComplaintController;use App\Http\Controllers\Tenant\ServiceClaimController;
use App\Http\Controllers\Tenant\ServiceController;
use App\Http\Controllers\Tenant\ServiceDeliveryController;
use App\Http\Controllers\Tenant\ServiceDocumentController;
use App\Http\Controllers\Tenant\ServiceExceptionController;
use App\Http\Controllers\Tenant\ServiceIntakeController;
use App\Http\Controllers\Tenant\ServicePartController;
use App\Http\Controllers\Tenant\ServicePhotoController;
use App\Http\Controllers\Tenant\ServiceToolsController;
use App\Http\Controllers\Tenant\ServiceTransferController;
use App\Http\Controllers\Tenant\ServiceWorkflowController;
use App\Http\Controllers\Tenant\SettingController;
use App\Http\Controllers\Tenant\SettingsController;
use App\Http\Controllers\Tenant\SetupController;
use App\Http\Controllers\Tenant\ShiftController;
use App\Http\Controllers\Tenant\SopController;
use App\Http\Controllers\Tenant\StockAllocationController;
use App\Http\Controllers\Tenant\SystemController;
use App\Http\Controllers\Tenant\TaxController;
use App\Http\Controllers\Tenant\TechnicianBonusController;
use App\Http\Controllers\Tenant\TechnicianWorkflowController;
use App\Http\Controllers\Tenant\TenantProfileController;
use App\Http\Controllers\Tenant\UniversalSearchController;
use App\Http\Controllers\Tenant\UserManagementController;
use App\Http\Controllers\Tenant\DelegationController;
use App\Http\Controllers\Tenant\RefundController;
use App\Http\Controllers\Tenant\WarehouseController;
use App\Http\Controllers\Tenant\WorkflowAdminController;
use Illuminate\Support\Facades\Route;

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
Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.post')->middleware('throttle:login');

// ========== PASSWORD RESET (public, no auth required) ==========
Route::get('/forgot-password', [ResetPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [ResetPasswordController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');

// ========== EMAIL VERIFICATION (semi-public, needs auth but not verified) ==========
Route::get('/email/verify', [EmailVerificationController::class, 'notice'])
    ->middleware('auth')->name('tenant.verification.notice');
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['auth', 'signed'])->name('tenant.verification.verify');
Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
    ->middleware(['auth', 'throttle:6,1'])->name('tenant.verification.resend');

// ========== 2FA (two-factor authentication) ==========
Route::get('/two-factor-challenge', [TwoFactorController::class, 'challenge'])
    ->name('two-factor.challenge');
Route::post('/two-factor-challenge', [TwoFactorController::class, 'verify'])
    ->name('two-factor.verify');
Route::post('/two-factor-challenge/email', [TwoFactorController::class, 'sendEmailCode'])
    ->name('two-factor.send-email');
Route::post('/two-factor-challenge/verify-email', [TwoFactorController::class, 'verifyEmailCode'])
    ->name('two-factor.verify-email');

// ========== 2FA SETUP (authenticated) ==========
Route::middleware(['auth'])->group(function () {
    Route::get('/two-factor-status', [TwoFactorSetupController::class, 'status'])
        ->name('two-factor.status');
    Route::post('/two-factor-enable', [TwoFactorSetupController::class, 'enable'])
        ->name('two-factor.enable');
    Route::post('/two-factor-confirm', [TwoFactorSetupController::class, 'confirm'])
        ->name('two-factor.confirm');
    Route::post('/two-factor-disable', [TwoFactorSetupController::class, 'disable'])
        ->name('two-factor.disable');
    Route::post('/two-factor-regenerate-codes', [TwoFactorSetupController::class, 'regenerateCodes'])
        ->name('two-factor.regenerate-codes');
});

// ========== DEV LOGIN (development only - accessible on tenant subdomain) ==========
if (app()->environment('local', 'development')) {
    Route::get('/dev-login', DevLoginController::class)->name('tenant.dev-login');
}

// ========== GOOGLE LOGIN (public tenant entrypoint) ==========
Route::get('/tenant/auth/google/redirect', [GoogleLoginController::class, 'redirect'])->name('google.login');
Route::get('/tenant/auth/google/callback', [GoogleLoginController::class, 'callback'])->name('google.callback');

Route::middleware([
    'tenancy.session',
    'auth',
    'check.subscription',
])->group(function () {

    // ========== DASHBOARD ==========
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/onboarding', [OnboardingController::class, 'index'])->name('onboarding.index');

    // ========== SERVICES ==========
    Route::resource('services', ServiceController::class)->only(['index', 'create', 'show', 'update', 'edit', 'destroy'])->middleware('check.plan.feature:services');
    Route::post('/services', [ServiceIntakeController::class, 'store'])->name('services.store')->middleware('check.plan.feature:services');
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
    Route::post('/services/{service}/close', [ServiceWorkflowController::class, 'close'])->name('services.close')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/partner', [ServiceWorkflowController::class, 'partner'])->name('services.partner')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/complete-partner', [ServiceWorkflowController::class, 'completePartner'])->name('services.complete-partner')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/complete', [ServiceDocumentController::class, 'complete'])->name('services.complete')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/checklists', [ServiceChecklistController::class, 'saveChecklist'])->name('services.checklists.store')->middleware('check.plan.feature:services');
    Route::get('/services/{service}/print-receipt', [ServiceDocumentController::class, 'printReceipt'])->name('services.print-receipt')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/warranty-claim', [ServiceClaimController::class, 'createWarrantyClaim'])->name('services.warranty-claim')->middleware('check.plan.feature:services');
    Route::post('/services/bulk-status', [ServiceWorkflowController::class, 'bulkUpdateStatus'])->name('services.bulk-status')->middleware('check.plan.feature:services');

    // ========== SERVICE TRANSFERS (BR-FIX-02: cross-branch custody) ==========
    Route::get('/service-transfers/create', [ServiceTransferController::class, 'create'])->name('service-transfers.create')->middleware('check.plan.feature:transfer_stock');
    Route::post('/service-transfers', [ServiceTransferController::class, 'store'])->name('service-transfers.store')->middleware('check.plan.feature:transfer_stock');
    Route::post('/service-transfers/{transfer}/send', [ServiceTransferController::class, 'send'])->name('service-transfers.send')->middleware('check.plan.feature:transfer_stock');
    Route::post('/service-transfers/{transfer}/receive', [ServiceTransferController::class, 'receive'])->name('service-transfers.receive')->middleware('check.plan.feature:transfer_stock');
    Route::post('/service-transfers/{transfer}/cancel', [ServiceTransferController::class, 'cancel'])->name('service-transfers.cancel')->middleware('check.plan.feature:transfer_stock');

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
    Route::post('/services/{service}/assign', [TechnicianWorkflowController::class, 'assignTechnician'])->name('services.assign')->middleware('check.plan.feature:services');
    Route::post('/work-orders/{workOrder}/accept', [TechnicianWorkflowController::class, 'accept'])->name('work-orders.accept')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/diagnosis', [TechnicianWorkflowController::class, 'storeDiagnosis'])->name('services.diagnosis.store')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/quotation', [TechnicianWorkflowController::class, 'createQuotation'])->name('services.quotation.create')->middleware('check.plan.feature:services');
    Route::post('/quotations/{quotation}/approve', [TechnicianWorkflowController::class, 'approveQuotation'])->name('quotations.approve')->middleware('check.plan.feature:services');
    Route::post('/quotations/{quotation}/reject', [TechnicianWorkflowController::class, 'rejectQuotation'])->name('quotations.reject')->middleware('check.plan.feature:services');
    Route::get('/api/customer/pending-quotations', [CustomerPortalController::class, 'pendingQuotations'])->name('customer.pending-quotations')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/parts', [TechnicianWorkflowController::class, 'requestPart'])->name('services.parts.request')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/qc', [TechnicianWorkflowController::class, 'storeQcCheck'])->name('services.qc.store')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/repair/start', [TechnicianWorkflowController::class, 'startRepair'])->name('services.repair.start')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/repair/complete', [TechnicianWorkflowController::class, 'completeRepair'])->name('services.repair.complete')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/repair/note', [TechnicianWorkflowController::class, 'addRepairNote'])->name('services.repair.note')->middleware('check.plan.feature:services');
    // Sprint 7.3G — Service Delivery & Pickup
    Route::post('/services/{service}/ready-pickup', [ServiceDeliveryController::class, 'markReady'])->name('services.ready-pickup')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/verify-payment', [ServiceDeliveryController::class, 'verifyPayment'])->name('services.verify-payment')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/pickup', [ServiceDeliveryController::class, 'pickup'])->name('services.pickup')->middleware('check.plan.feature:services');
    Route::get('/customers/{customerId}/warranties', [ServiceDeliveryController::class, 'customerWarranties'])->name('customers.warranties')->middleware('check.plan.feature:customers');
    // Sprint 7.3H — Service Exception & After Sales
    Route::post('/services/{service}/exception-warranty-claim', [ServiceExceptionController::class, 'createClaim'])->name('services.warranty-claim.create')->middleware('check.plan.feature:services');
    Route::post('/warranty-claims/{claim}/decide', [ServiceExceptionController::class, 'decideClaim'])->name('warranty-claims.decide')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/revise-diagnosis', [ServiceExceptionController::class, 'reviseDiagnosis'])->name('services.diagnosis.revise')->middleware('check.plan.feature:services');
    Route::get('/monitoring/unclaimed', [ServiceExceptionController::class, 'unclaimed'])->name('monitoring.unclaimed')->middleware('check.plan.feature:monitoring');

    // BR-FIX-04 (BR-012) — auditable refunds (separate financial reversal event)
    Route::post('/sales/{sale}/refund', [RefundController::class, 'store'])->name('sales.refund')->middleware('check.plan.feature:sales');
    Route::post('/warranty-claims/{claim}/refund', [RefundController::class, 'refundClaim'])->name('warranty-claims.refund')->middleware('check.plan.feature:sales');
    // Sprint 7.4 — Inventory Intelligence
    Route::get('/inventaris/dashboard', [InventoryIntelligenceController::class, 'dashboard'])->name('inventaris.dashboard')->middleware('check.plan.feature:inventaris');
    Route::get('/products/{product}/movements', [InventoryIntelligenceController::class, 'movements'])->name('products.movements')->middleware('check.plan.feature:inventaris');
    // Sprint 7.4 Revision — Real Service Center Part Flow
    Route::post('/services/{service}/parts/request', [ServicePartController::class, 'request'])->name('service-parts.request')->middleware('check.plan.feature:services');
    Route::post('/service-parts/{part}/cancel', [ServicePartController::class, 'cancelRequest'])->name('service-parts.cancel')->middleware('check.plan.feature:services');
    Route::post('/service-parts/{part}/approve', [ServicePartController::class, 'approveRequest'])->name('service-parts.approve')->middleware('check.plan.feature:services');
    Route::post('/service-parts/{part}/reject', [ServicePartController::class, 'rejectRequest'])->name('service-parts.reject')->middleware('check.plan.feature:services');
    Route::post('/service-parts/{part}/use', [ServicePartController::class, 'usePart'])->name('service-parts.use')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/parts/return-request', [ServicePartController::class, 'requestReturn'])->name('service-parts.return-request')->middleware('check.plan.feature:services');
    Route::post('/service-part-returns/{return}/process', [ServicePartController::class, 'processReturn'])->name('service-parts.process-return')->middleware('check.plan.feature:services');
    Route::get('/services/{service}/profit', [ServicePartController::class, 'profit'])->name('services.profit')->middleware(['check.plan.feature:services', 'permission:finance.view']);
    // Sprint 7.4A — Operational Refinement
    Route::get('/inventaris/operational', [OperationalDashboardController::class, 'warehouse'])->name('inventaris.operational')->middleware('check.plan.feature:inventaris');
    Route::get('/dashboard/cs-stats', [OperationalDashboardController::class, 'cs'])->name('dashboard.cs-stats')->middleware('check.plan.feature:services');
    Route::get('/dashboard/owner-kpi', [OperationalDashboardController::class, 'owner'])->name('dashboard.owner-kpi')->middleware(['check.plan.feature:dashboard', 'permission:finance.view']);

    // BR-010: Emergency Purchase — pembelian sparepart darurat
    Route::get('/inventaris/emergency-purchases', [EmergencyPurchaseController::class, 'index'])->name('inventaris.emergency-purchases')->middleware('check.plan.feature:inventaris');
    Route::post('/inventaris/emergency-purchases', [EmergencyPurchaseController::class, 'store'])->name('inventaris.emergency-purchases.store')->middleware('check.plan.feature:inventaris');

    // BR-014: Cross-Branch Complaint — komplain lintas cabang
    Route::get('/services/complaints', [ServiceComplaintController::class, 'index'])->name('services.complaints')->middleware('check.plan.feature:services');
    Route::post('/services/{service}/complaint', [ServiceComplaintController::class, 'store'])->name('services.complaint.store')->middleware('check.plan.feature:services');
    Route::put('/services/complaints/{complaint}', [ServiceComplaintController::class, 'update'])->name('services.complaint.update')->middleware('check.plan.feature:services');

    // BR-015: Technician Bonus & Compensation
    Route::get('/sistem/technician-bonus', [TechnicianBonusController::class, 'index'])->name('technician-bonus.index')->middleware('check.plan.feature:settings');
    Route::post('/sistem/technician-bonus/config', [TechnicianBonusController::class, 'saveConfig'])->name('technician-bonus.config')->middleware('check.plan.feature:settings');
    Route::post('/sistem/technician-bonus/{record}/approve', [TechnicianBonusController::class, 'approve'])->name('technician-bonus.approve')->middleware('check.plan.feature:settings');
    Route::post('/sistem/technician-bonus/approve-batch', [TechnicianBonusController::class, 'approveBatch'])->name('technician-bonus.approve-batch')->middleware('check.plan.feature:settings');
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
    Route::get('/dashboard/owner', [OperationalControlController::class, 'ownerDashboard'])->name('dashboard.owner')->middleware(['check.plan.feature:dashboard', 'permission:finance.view']);
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
    Route::get('/indents/{indent}/print', [IndentController::class, 'printNota'])->name('indents.print')->middleware('check.plan.feature:indents');

    // ========== BRANCHES (MULTI-CABANG) ==========
    Route::resource('branches', BranchController::class)->except(['edit', 'create', 'show'])->middleware('check.plan.feature:multi_branch');

    // ========== USERS (MANAJEMEN USER) ==========
    Route::resource('users', UserManagementController::class)->except(['create', 'edit', 'index', 'show'])->middleware('check.plan.feature:users');

    // ========== STOCK ALLOCATIONS (TRANSFER STOK) ==========
    Route::resource('stock-allocations', StockAllocationController::class)->except(['create', 'edit', 'update'])->middleware('check.plan.feature:transfer_stock');
    Route::post('/stock-allocations/{stock_allocation}/confirm', [StockAllocationController::class, 'confirm'])->name('stock-allocations.confirm')->middleware('check.plan.feature:transfer_stock');
    Route::post('/stock-allocations/{stock_allocation}/reject', [StockAllocationController::class, 'reject'])->name('stock-allocations.reject')->middleware('check.plan.feature:transfer_stock');

    // ========== EXPENSES ==========
    Route::resource('expenses', ExpenseController::class)->only(['index', 'store'])->middleware('check.plan.feature:expenses');

    // ========== PURCHASE RETURNS ==========
    Route::resource('purchase-returns', PurchaseReturnController::class)->except(['create', 'edit', 'update', 'show'])->middleware('check.plan.feature:purchases');
    Route::post('/purchase-returns/{purchaseReturn}/status', [PurchaseReturnController::class, 'updateStatus'])->name('purchase-returns.status')->middleware('check.plan.feature:purchases');

    // ========== DAMAGED STOCKS ==========
    Route::get('/inventory/damaged', [DamagedStockController::class, 'index'])->name('inventory.damaged')->middleware('check.plan.feature:products');
    Route::post('/inventory/damaged', [DamagedStockController::class, 'store'])->name('inventory.damaged.store')->middleware('check.plan.feature:products');

    // ========== REORDER ALERTS ==========
    Route::get('/inventory/reorder-alerts', [InventoryController::class, 'reorderAlerts'])->name('inventory.reorder-alerts')->middleware('check.plan.feature:products');

    // ========== STOCK FORECASTING ==========
    Route::get('/inventory/forecast', [InventoryController::class, 'forecast'])->name('inventory.forecast')->middleware('check.plan.feature:products');

    // ========== PARTNER TEKNISI ==========
    Route::resource('partner-teknisi', PartnerTeknisiController::class)->except(['create', 'edit', 'show'])->middleware('check.plan.feature:services');

    // ========== PICKUP & DELIVERY ==========
    Route::resource('pickup-deliveries', PickupDeliveryController::class)->except(['show', 'edit', 'update', 'create'])->middleware('check.plan.feature:services');
    Route::post('/pickup-deliveries/{pickupDelivery}/status', [PickupDeliveryController::class, 'updateStatus'])->name('pickup-deliveries.status')->middleware('check.plan.feature:services');

    // ========== TAX SETTINGS ==========
    Route::get('/tax', [TaxController::class, 'index'])->name('tax.index');
    Route::post('/tax', [TaxController::class, 'update'])->name('tax.update');
    Route::post('/tax/calculate', [TaxController::class, 'calculate'])->name('tax.calculate');

    // ========== PAYMENT RECONCILIATION ==========
    Route::get('/reconciliations', [ReconciliationController::class, 'index'])->name('reconciliations.index');
    Route::post('/reconciliations', [ReconciliationController::class, 'store'])->name('reconciliations.store');
    Route::post('/reconciliations/{paymentReconciliation}/status', [ReconciliationController::class, 'updateStatus'])->name('reconciliations.status');

    // ========== COMMISSIONS ==========
    Route::resource('commissions', CommissionController::class)->only(['index'])->middleware('check.plan.feature:services');
    Route::post('/commissions/{commission}/pay', [CommissionController::class, 'pay'])->name('commissions.pay')->middleware('check.plan.feature:services');
    Route::post('/commissions/pay-bulk', [CommissionController::class, 'payBulk'])->name('commissions.pay-bulk')->middleware('check.plan.feature:services');

    // ========== SHIFTS ==========
    Route::resource('shifts', ShiftController::class)->except(['create', 'edit', 'show'])->middleware('check.plan.feature:settings');

    // ========== ATTENDANCES ==========
    Route::get('/attendances', [AttendanceController::class, 'index'])->name('attendances.index');
    Route::post('/attendances/clock-in', [AttendanceController::class, 'clockIn'])->name('attendances.clock-in');
    Route::post('/attendances/clock-out', [AttendanceController::class, 'clockOut'])->name('attendances.clock-out');
    Route::post('/attendances/{attendance}/status', [AttendanceController::class, 'updateStatus'])->name('attendances.status');

    // ========== CUSTOM FIELDS ==========
    Route::resource('custom-fields', CustomFieldController::class)->except(['create', 'edit', 'show'])->middleware('check.plan.feature:settings');

    // ========== SERVICE PHOTOS ==========
    Route::post('/services/{service}/photos', [ServicePhotoController::class, 'store'])->name('services.photos.store')->middleware('check.plan.feature:services');
    Route::delete('/services/{service}/photos/{servicePhoto}', [ServicePhotoController::class, 'destroy'])->name('services.photos.destroy')->middleware('check.plan.feature:services');

    // ========== GOOGLE LOGIN (tenant level) ==========
    Route::post('/auth/google/unlink', [GoogleLoginController::class, 'unlink'])->name('google.unlink');

    // ========== QR SCANNER ==========
    Route::get('/tools/qr-scanner', function () {
        return inertia('Tools/QRScanner');
    })->name('qr-scanner')->middleware('check.plan.feature:services');

    // ========== GOOGLE DRIVE SETTINGS ==========
    Route::get('/drive/connect', [DriveController::class, 'connect'])->name('drive.connect');
    Route::get('/drive/callback', [DriveController::class, 'callback'])->name('drive.callback');
    Route::get('/drive/status', [DriveController::class, 'status'])->name('drive.status');
    Route::post('/drive/disconnect', [DriveController::class, 'disconnect'])->name('drive.disconnect');

    // ========== QUICK REPLIES ==========
    Route::resource('quick-replies', QuickReplyController::class)->except(['create', 'edit', 'show'])->middleware('check.plan.feature:settings');

    // ========== KNOWLEDGE BASE ==========
    Route::get('/knowledge-base/search', [KnowledgeBaseController::class, 'search'])->name('knowledge-base.search');
    Route::resource('knowledge-base', KnowledgeBaseController::class)->except(['create', 'edit', 'show'])->middleware('check.plan.feature:services');

    // ========== SOP ==========
    Route::resource('sops', SopController::class)->except(['create', 'edit', 'show'])->middleware('check.plan.feature:settings');
    Route::post('/sops/{sop}/mark-read', [SopController::class, 'markRead'])->name('sops.mark-read');

    // ========== DEMO MODE ==========
    Route::get('/demo', [DemoController::class, 'index'])->name('demo.index');
    Route::post('/demo/generate', [DemoController::class, 'generate'])->name('demo.generate');
    Route::post('/demo/reset', [DemoController::class, 'reset'])->name('demo.reset');
    Route::post('/demo/toggle-mode', [DemoController::class, 'toggleMode'])->name('demo.toggle');

    // ========== PURCHASES ==========
    Route::resource('purchases', PurchaseController::class)->except(['create', 'edit', 'show', 'update', 'destroy'])->middleware('check.plan.feature:purchases');

    // ========== DAILY DEPOSITS ==========
    Route::resource('daily-deposits', DailyDepositController::class)->only(['index', 'store'])->middleware('check.plan.feature:deposits');

    // ========== CHECKLIST TEMPLATES ==========
    Route::resource('checklist-templates', ChecklistTemplateController::class)->except(['create', 'edit', 'show'])->middleware('check.plan.feature:checklist');

    // ========== INVENTORY MUTATIONS ==========
    Route::get('/inventory/mutations', [InventoryController::class, 'mutations'])->name('inventory.mutations')->middleware('check.plan.feature:products');
    Route::post('/inventory/adjustments', [InventoryController::class, 'adjustment'])->name('inventory.adjustments.store')->middleware('check.plan.feature:products');
    Route::post('/products/{product}/quick-stock', [ProductController::class, 'quickStock'])->name('products.quick-stock')->middleware('check.plan.feature:products');

    // ========== CASH REGISTERS ==========
    Route::get('/cash-registers', [CashRegisterController::class, 'index'])->name('cash-registers.index')->middleware('check.plan.feature:cash_register');
    Route::post('/cash-registers/open', [CashRegisterController::class, 'open'])->name('cash-registers.open')->middleware('check.plan.feature:cash_register');
    Route::post('/cash-registers/close', [CashRegisterController::class, 'close'])->name('cash-registers.close')->middleware('check.plan.feature:cash_register');

    // ========== MASTER DATA ==========
    Route::get('/master-data', [MasterDataController::class, 'index'])->name('master-data.index')->middleware('check.plan.feature:master_data');
    Route::post('/master-data', [MasterDataController::class, 'store'])->name('master-data.store')->middleware('check.plan.feature:master_data');
    Route::post('/master-data/{masterData}', [MasterDataController::class, 'update'])->name('master-data.update')->middleware('check.plan.feature:master_data');
    Route::delete('/master-data/{masterData}', [MasterDataController::class, 'destroy'])->name('master-data.destroy')->middleware('check.plan.feature:master_data');

    // ========== MASTER LABOR SERVICES ==========
    Route::get('/master-services', [MasterDataController::class, 'laborIndex'])->name('master-services.index')->middleware('check.plan.feature:master_data');
    Route::post('/master-services', [MasterDataController::class, 'laborStore'])->name('master-services.store')->middleware('check.plan.feature:master_data');
    Route::post('/master-services/{masterLaborService}', [MasterDataController::class, 'laborUpdate'])->name('master-services.update')->middleware('check.plan.feature:master_data');
    Route::delete('/master-services/{masterLaborService}', [MasterDataController::class, 'laborDestroy'])->name('master-services.destroy')->middleware('check.plan.feature:master_data');

    // ========== REPORTS ==========
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index')->middleware('check.plan.feature:reports');
    Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales')->middleware('check.plan.feature:reports');
    Route::get('/reports/services', [ReportController::class, 'services'])->name('reports.services')->middleware('check.plan.feature:reports');
    Route::get('/reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory')->middleware('check.plan.feature:reports');
    Route::get('/reports/finance', [ReportController::class, 'finance'])->name('reports.finance')->middleware(['check.plan.feature:reports', 'permission:finance.view']);
    Route::get('/reports/commissions', [ReportController::class, 'commissions'])->name('reports.commissions')->middleware('check.plan.feature:reports');
    Route::get('/reports/productivity', [ReportController::class, 'productivity'])->name('reports.productivity')->middleware('check.plan.feature:reports');
    Route::get('/reports/customer-analytics', [ReportController::class, 'customerAnalytics'])->name('reports.customer-analytics')->middleware('check.plan.feature:reports');
    Route::get('/reports/revenue-comparison', [ReportController::class, 'revenueComparison'])->name('reports.revenue-comparison')->middleware('check.plan.feature:reports');
    Route::get('/reports/export/{type}', [ReportController::class, 'export'])->name('reports.export')->middleware('check.plan.feature:reports');

    // ========== TENANT PROFILE (Toko) ==========
    Route::get('/profile', [TenantProfileController::class, 'index'])->name('profile.index');

    // ========== USER PROFILE (Akun Saya) ==========
    Route::get('/my-profile', [UserManagementController::class, 'myProfile'])->name('user.profile');
    Route::put('/my-profile', [UserManagementController::class, 'updateProfile'])->name('user.profile.update');
    Route::put('/my-preferences', [UserManagementController::class, 'updatePreferences'])->name('user.preferences.update');
    Route::get('/users/{userManagement}/menu-access', [UserManagementController::class, 'getMenuAccess'])->name('users.menu-access');
    Route::post('/users/{userManagement}/menu-access', [UserManagementController::class, 'updateMenuAccess'])->name('users.menu-access.update');

    // ========== BILLING / VOUCHER ==========
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::post('/billing/apply-voucher', [BillingController::class, 'applyVoucher'])->name('billing.apply-voucher');
    Route::post('/billing/initiate', [PaymentController::class, 'initiate'])->name('payment.initiate');
    Route::get('/payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');
    Route::post('/payment/{payment}/confirm-manual', [PaymentController::class, 'confirmManual'])->name('payment.confirm-manual');
    Route::get('/payment/finish', [PaymentController::class, 'callback'])->name('payment.finish');
    Route::get('/payment/unfinish', function () {
        return inertia('Tenant/Payment', ['status' => 'pending']);
    })->name('payment.unfinish');
    Route::get('/payment/error', function () {
        return inertia('Tenant/Payment', ['status' => 'error']);
    })->name('payment.error');

    // ========== SETTINGS / BRANDING ==========
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index')->middleware('check.plan.feature:settings');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update')->middleware('check.plan.feature:settings');
    Route::post('/settings/logo', [SettingsController::class, 'uploadLogo'])->name('settings.upload-logo')->middleware('check.plan.feature:settings');
    Route::post('/settings/theme', [SettingsController::class, 'updateTheme'])->name('settings.theme')->middleware('check.plan.feature:settings');
    Route::post('/settings/layout', [SettingsController::class, 'updateLayoutPreferences'])->name('settings.layout.update');
    Route::post('/settings/maintenance', [SettingsController::class, 'updateMaintenance'])->name('settings.maintenance.update')->middleware('check.plan.feature:settings');
    Route::post('/settings/whatsapp-gateway', [SettingsController::class, 'updateWhatsappGateway'])->name('settings.whatsapp-gateway.update')->middleware('check.plan.feature:settings');
    Route::get('/settings/whatsapp-link', [SettingsController::class, 'getWhatsappLink'])->name('settings.whatsapp-link')->middleware('check.plan.feature:settings');

    // ========== MONITORING ==========
    Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index')->middleware('check.plan.feature:monitoring');
    Route::get('/monitoring/activities', [MonitoringController::class, 'activities'])->name('monitoring.activities')->middleware('check.plan.feature:monitoring');
    Route::get('/monitoring/logins', [MonitoringController::class, 'logins'])->name('monitoring.logins')->middleware('check.plan.feature:monitoring');
    Route::get('/monitoring/alerts', [MonitoringController::class, 'alerts'])->name('monitoring.alerts')->middleware('check.plan.feature:monitoring');
    Route::post('/monitoring/alerts/{system_alert}/dismiss', [MonitoringController::class, 'dismissAlert'])->name('monitoring.dismiss-alert')->middleware('check.plan.feature:monitoring');
    Route::post('/monitoring/alerts/dismiss-all', [MonitoringController::class, 'dismissAllAlerts'])->name('monitoring.dismiss-all-alerts')->middleware('check.plan.feature:monitoring');
    Route::post('/monitoring/check-low-stock', [MonitoringController::class, 'checkLowStock'])->name('monitoring.check-low-stock')->middleware('check.plan.feature:monitoring');

    // ========== SEARCH ==========
    Route::get('/search', [SearchController::class, 'search'])->name('search');

    // ========== CONSOLIDATED ROUTES ==========

    // Keuangan — transaction list (cs/cashier already get a restricted today-only
    // view via FinanceController::shouldRestrictToTodayCompletedTransactions).
    // The true P&L / profit endpoints are gated by permission:finance.view
    // (see /reports/finance, /dashboard/owner, /dashboard/owner-kpi).
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

        // BR-FIX-03 — Controlled delegation lifecycle (grant/revoke, branch + time scoped)
        Route::get('/delegations', [DelegationController::class, 'index'])->name('delegations.index');
        Route::post('/delegations', [DelegationController::class, 'store'])->name('delegations.store');
        Route::post('/delegations/{delegation}/revoke', [DelegationController::class, 'revoke'])->name('delegations.revoke');
    });

    // Dokumen
    Route::middleware(['check.plan.feature:settings'])->group(function () {
        Route::get('/dokumen', [DocumentController::class, 'index'])->name('dokumen.index');
    });

    // Pengaturan
    Route::get('/pengaturan', [SettingController::class, 'index'])->name('pengaturan.index');
    Route::post('/pengaturan/email', [SettingController::class, 'updateEmail'])->name('pengaturan.email');

    // Sprint 7.2E — Enterprise Admin Panel (thin controllers → engine delegation)
    Route::middleware(['check.plan.feature:settings'])->group(function () {
        // Provider Center
        Route::get('/pengaturan/providers', [ProviderApiController::class, 'page'])->name('pengaturan.providers');
        Route::post('/providers/{category}/{provider}/test', [ProviderApiController::class, 'test'])->name('tenant.providers.test');
        Route::post('/providers/{category}/{provider}/toggle', [ProviderApiController::class, 'toggle'])->name('tenant.providers.toggle');

        // Workflow Builder
        Route::get('/sistem/workflows', [WorkflowAdminController::class, 'index'])->name('sistem.workflows');
        Route::get('/sistem/workflows/graph', [WorkflowAdminController::class, 'graph'])->name('tenant.workflows.graph');

        // Automation Builder
        Route::get('/sistem/automations', [AutomationAdminController::class, 'index'])->name('sistem.automations');
        Route::post('/sistem/automations', [AutomationAdminController::class, 'store'])->name('tenant.automation.store');
        Route::put('/sistem/automations/{rule}', [AutomationAdminController::class, 'update'])->name('tenant.automation.update');
        Route::post('/sistem/automations/{rule}/toggle', [AutomationAdminController::class, 'toggle'])->name('tenant.automation.toggle');

        // Event Log
        Route::get('/monitoring/event-log', [EventLogController::class, 'index'])->name('tenant.event-log.index');
    });
});
