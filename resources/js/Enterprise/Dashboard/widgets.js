/**
 * ═══════════════════════════════════════════════════════════
 * WIDGET REGISTRATION — Complete Role & Business Type Matrix
 * ═══════════════════════════════════════════════════════════
 *
 * Mencakup SEMUA 9 role + 5 business type.
 * Data diambil dari backend (stats) — tidak ada placeholder.
 */

import { registry } from './DashboardWidgetRegistry.js';

// ── Metric Widgets (real data keys) ──
import RevenueWidget from './widgets/RevenueWidget.vue';
import ServiceWidget from './widgets/ServiceWidget.vue';
import SalesWidget from './widgets/SalesWidget.vue';
import StockWidget from './widgets/StockWidget.vue';
import InventoryValueWidget from './widgets/InventoryValueWidget.vue';
import PendingAllocationWidget from './widgets/PendingAllocationWidget.vue';
import NewCustomersWidget from './widgets/NewCustomersWidget.vue';
import ReadyPickupWidget from './widgets/ReadyPickupWidget.vue';
import TechAssignedWidget from './widgets/TechAssignedWidget.vue';

// ── Chart Widgets ──
import StatusChartWidget from './widgets/StatusChartWidget.vue';
import ServiceTrendWidget from './widgets/ServiceTrendWidget.vue';

// ── Content Widgets ──
import ActivityWidget from './widgets/ActivityWidget.vue';
import RecentServiceWidget from './widgets/RecentServiceWidget.vue';
import RecentSalesWidget from './widgets/RecentSalesWidget.vue';
import StockAlertWidget from './widgets/StockAlertWidget.vue';
import PurchaseWidget from './widgets/PurchaseWidget.vue';
import CashBalanceWidget from './widgets/CashBalanceWidget.vue';
import NetProfitWidget from './widgets/NetProfitWidget.vue';
import PayableWidget from './widgets/PayableWidget.vue';
import EmployeeCountWidget from './widgets/EmployeeCountWidget.vue';
import AttendanceTodayWidget from './widgets/AttendanceTodayWidget.vue';
import PayrollPendingWidget from './widgets/PayrollPendingWidget.vue';
import TotalAssetsWidget from './widgets/TotalAssetsWidget.vue';
import MaintenanceDueWidget from './widgets/MaintenanceDueWidget.vue';
import DepreciationWidget from './widgets/DepreciationWidget.vue';
import ActiveProjectsWidget from './widgets/ActiveProjectsWidget.vue';
import TasksDueWidget from './widgets/TasksDueWidget.vue';
import OpenIssuesWidget from './widgets/OpenIssuesWidget.vue';
import SalesTodayWidget from './widgets/SalesTodayWidget.vue';
import OpenOrdersWidget from './widgets/OpenOrdersWidget.vue';
import MarketplaceOrdersWidget from './widgets/MarketplaceOrdersWidget.vue';
import ActiveProductionWidget from './widgets/ActiveProductionWidget.vue';
import OEEWidget from './widgets/OEEWidget.vue';
import MaterialShortageWidget from './widgets/MaterialShortageWidget.vue';
import WarehouseUtilizationWidget from './widgets/WarehouseUtilizationWidget.vue';
import PickingQueueWidget from './widgets/PickingQueueWidget.vue';
import ShipmentsTodayWidget from './widgets/ShipmentsTodayWidget.vue';
import PendingApprovalsWidget from './widgets/PendingApprovalsWidget.vue';
import KnowledgeArticlesWidget from './widgets/KnowledgeArticlesWidget.vue';
import RecentDocsWidget from './widgets/RecentDocsWidget.vue';
import BusinessHealthWidget from './widgets/BusinessHealthWidget.vue';
import AIRecommendationsWidget from './widgets/AIRecommendationsWidget.vue';
import PredictedRevenueWidget from './widgets/PredictedRevenueWidget.vue';
import APIHealthWidget from './widgets/APIHealthWidget.vue';
import WebhookQueueWidget from './widgets/WebhookQueueWidget.vue';
import MarketplaceSyncWidget from './widgets/MarketplaceSyncWidget.vue';
import PlatformHealthWidget from './widgets/PlatformHealthWidget.vue';
import TenantGrowthWidget from './widgets/TenantGrowthWidget.vue';
import MRRWidget from './widgets/MRRWidget.vue';
import GovernanceScoreWidget from './widgets/GovernanceScoreWidget.vue';
import CriticalRisksWidget from './widgets/CriticalRisksWidget.vue';
import OpenFindingsWidget from './widgets/OpenFindingsWidget.vue';
import PlatformHealthScoreWidget from './widgets/PlatformHealthScoreWidget.vue';
import CPUUsageWidget from './widgets/CPUUsageWidget.vue';
import MemoryUsageWidget from './widgets/MemoryUsageWidget.vue';
import StorageUsageWidget from './widgets/StorageUsageWidget.vue';
import DatabaseHealthWidget from './widgets/DatabaseHealthWidget.vue';
import SlowQueriesWidget from './widgets/SlowQueriesWidget.vue';
import QueueHealthWidget from './widgets/QueueHealthWidget.vue';
import FailedJobsWidget from './widgets/FailedJobsWidget.vue';
import APIResponseTimeWidget from './widgets/APIResponseTimeWidget.vue';
import ErrorRateWidget from './widgets/ErrorRateWidget.vue';
import ActiveSessionsWidget from './widgets/ActiveSessionsWidget.vue';
import CacheHitRatioWidget from './widgets/CacheHitRatioWidget.vue';
import IntegrationHealthWidget from './widgets/IntegrationHealthWidget.vue';
import WebhookQueueOpsWidget from './widgets/WebhookQueueOpsWidget.vue';
import SecurityAlertsWidget from './widgets/SecurityAlertsWidget.vue';
import UptimeWidget from './widgets/UptimeWidget.vue';
import AIInfrastructureInsightWidget from './widgets/AIInfrastructureInsightWidget.vue';

// ═══════════════════════════════════════════════════════════
// ALL ROLES:
//   owner, admin, manager, head_store, cs, technician,
//   cashier, courier, custom
//
// ALL BUSINESS TYPES:
//   full_service, aksesoris_service, aksespare_service,
//   gadget_full, retail_only
// ═══════════════════════════════════════════════════════════

registry.registerAll([

  // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  // ROW 1 — METRIC WIDGETS (cols=1)
  // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  {
    id: 'revenue',
    title: 'Pendapatan',
    component: RevenueWidget,
    roles: ['owner', 'admin', 'manager', 'cashier', 'head_store'],
    features: ['sales'],
    permissions: ['manage_finance'],
    priority: 10, cols: 1,
  },

  {
    id: 'services_in',
    title: 'Servis Masuk',
    component: ServiceWidget,
    roles: ['owner', 'admin', 'manager', 'head_store', 'cs', 'technician'],
    denyBusinessTypes: ['retail_only'],
    features: ['services'],
    priority: 11, cols: 1,
  },

  {
    id: 'pending_allocation',
    title: 'Menunggu Alokasi',
    component: PendingAllocationWidget,
    roles: ['owner', 'admin', 'manager', 'cs'],
    denyBusinessTypes: ['retail_only'],
    features: ['services'],
    permissions: ['assign_technician'],
    priority: 12, cols: 1,
  },

  {
    id: 'sales_today',
    title: 'Transaksi',
    component: SalesWidget,
    roles: ['owner', 'admin', 'manager', 'cashier', 'head_store'],
    features: ['sales'],
    permissions: ['manage_sales'],
    priority: 13, cols: 1,
  },

  // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  // ROW 2 — METRIC WIDGETS
  // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  {
    id: 'new_customers',
    title: 'Pelanggan Baru',
    component: NewCustomersWidget,
    roles: ['owner', 'admin', 'manager', 'cs'],
    features: ['customers'],
    permissions: ['manage_customers'],
    priority: 20, cols: 1,
  },

  {
    id: 'ready_pickup',
    title: 'Siap Diambil',
    component: ReadyPickupWidget,
    roles: ['owner', 'admin', 'manager', 'cs', 'cashier', 'courier'],
    denyBusinessTypes: ['retail_only'],
    features: ['services'],
    priority: 21, cols: 1,
  },

  {
    id: 'tech_assigned',
    title: 'Tugas Saya',
    component: TechAssignedWidget,
    roles: ['technician'],
    features: ['services'],
    permissions: ['work_on_services'],
    priority: 22, cols: 1,
  },

  {
    id: 'stock_alert_metric',
    title: 'Stok Menipis',
    component: StockWidget,
    roles: ['owner', 'admin', 'manager', 'head_store'],
    features: ['products'],
    permissions: ['manage_products'],
    priority: 23, cols: 1,
  },

  {
    id: 'inventory_value',
    title: 'Nilai Stok',
    component: InventoryValueWidget,
    roles: ['owner', 'admin', 'manager', 'head_store'],
    features: ['products'],
    permissions: ['manage_products'],
    priority: 24, cols: 1,
  },

  {
    id: 'purchase_today',
    title: 'Pembelian Hari Ini',
    component: PurchaseWidget,
    roles: ['owner', 'admin', 'manager'],
    features: ['purchases'],
    permissions: ['manage_purchases'],
    priority: 25, cols: 1,
  },

  // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  // FINANCE WIDGETS (cols=1)
  // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  {
    id: 'cash_balance',
    title: 'Saldo Kas & Bank',
    component: CashBalanceWidget,
    roles: ['owner', 'admin', 'manager', 'cashier'],
    features: ['finance'],
    permissions: ['manage_finance'],
    priority: 26, cols: 1,
  },

  {
    id: 'net_profit',
    title: 'Laba Bersih (MTD)',
    component: NetProfitWidget,
    roles: ['owner', 'admin', 'manager'],
    features: ['finance'],
    permissions: ['manage_finance'],
    priority: 27, cols: 1,
  },

  {
    id: 'payable_outstanding',
    title: 'Hutang Usaha',
    component: PayableWidget,
    roles: ['owner', 'admin', 'manager'],
    features: ['finance'],
    permissions: ['manage_finance'],
    priority: 28, cols: 1,
  },

  // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  // HRM WIDGETS (cols=1)
  // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  {
    id: 'employee_count',
    title: 'Total Karyawan',
    component: EmployeeCountWidget,
    roles: ['owner', 'admin', 'manager', 'hrd'],
    features: ['employees'],
    permissions: ['manage_employees'],
    priority: 29, cols: 1,
  },

  {
    id: 'attendance_today',
    title: 'Hadir Hari Ini',
    component: AttendanceTodayWidget,
    roles: ['owner', 'admin', 'manager', 'hrd', 'supervisor'],
    features: ['employees'],
    permissions: ['manage_employees'],
    priority: 30, cols: 1,
  },

  {
    id: 'payroll_pending',
    title: 'Payroll Pending',
    component: PayrollPendingWidget,
    roles: ['owner', 'admin', 'manager', 'hrd'],
    features: ['employees'],
    permissions: ['manage_payroll'],
    priority: 31, cols: 1,
  },

  // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  // ASSET WIDGETS (cols=1)
  // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  {
    id: 'total_assets',
    title: 'Total Asset',
    component: TotalAssetsWidget,
    roles: ['owner', 'admin', 'manager', 'maintenance'],
    features: ['assets'],
    permissions: ['manage_assets'],
    priority: 32, cols: 1,
  },

  {
    id: 'maintenance_due',
    title: 'Maintenance Due',
    component: MaintenanceDueWidget,
    roles: ['owner', 'admin', 'manager', 'maintenance'],
    features: ['assets'],
    permissions: ['manage_assets'],
    priority: 33, cols: 1,
  },

  {
    id: 'depreciation_mtd',
    title: 'Penyusutan Bulanan',
    component: DepreciationWidget,
    roles: ['owner', 'admin', 'manager', 'finance'],
    features: ['assets'],
    permissions: ['manage_assets'],
    priority: 34, cols: 1,
  },

  // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  // PROJECT WIDGETS (cols=1)
  // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  {
    id: 'active_projects',
    title: 'Project Aktif',
    component: ActiveProjectsWidget,
    roles: ['owner', 'admin', 'manager', 'project_manager'],
    features: ['projects'],
    permissions: ['manage_projects'],
    priority: 35, cols: 1,
  },

  {
    id: 'tasks_due_today',
    title: 'Task Due Today',
    component: TasksDueWidget,
    roles: ['owner', 'admin', 'manager', 'project_manager', 'supervisor', 'technician'],
    features: ['projects'],
    permissions: ['manage_projects'],
    priority: 36, cols: 1,
  },

  {
    id: 'open_issues',
    title: 'Open Issues',
    component: OpenIssuesWidget,
    roles: ['owner', 'admin', 'manager', 'project_manager', 'supervisor'],
    features: ['projects'],
    permissions: ['manage_projects'],
    priority: 37, cols: 1,
  },

  // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  // POS / SALES WIDGETS (cols=1)
  // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  {
    id: 'sales_today_pos',
    title: 'Penjualan Hari Ini',
    component: SalesTodayWidget,
    roles: ['owner', 'admin', 'manager', 'cashier', 'sales'],
    features: ['sales'],
    permissions: ['manage_sales'],
    priority: 38, cols: 1,
  },

  {
    id: 'open_orders_pos',
    title: 'Open Orders',
    component: OpenOrdersWidget,
    roles: ['owner', 'admin', 'manager', 'sales', 'warehouse'],
    features: ['sales'],
    permissions: ['manage_sales'],
    priority: 39, cols: 1,
  },

  {
    id: 'marketplace_orders',
    title: 'Marketplace Orders',
    component: MarketplaceOrdersWidget,
    roles: ['owner', 'admin', 'manager', 'sales', 'warehouse'],
    features: ['sales'],
    permissions: ['manage_sales'],
    priority: 40, cols: 1,
  },

  // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  // MANUFACTURING WIDGETS (cols=1)
  // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  {
    id: 'active_production',
    title: 'Produksi Aktif',
    component: ActiveProductionWidget,
    roles: ['owner', 'admin', 'production_manager', 'production_supervisor'],
    features: ['manufacturing'],
    permissions: ['manage_manufacturing'],
    priority: 41, cols: 1,
  },

  {
    id: 'oee',
    title: 'OEE',
    component: OEEWidget,
    roles: ['owner', 'admin', 'production_manager', 'management'],
    features: ['manufacturing'],
    permissions: ['manage_manufacturing'],
    priority: 42, cols: 1,
  },

  {
    id: 'material_shortage',
    title: 'Material Shortage',
    component: MaterialShortageWidget,
    roles: ['owner', 'admin', 'production_manager', 'warehouse', 'purchasing'],
    features: ['manufacturing'],
    permissions: ['manage_manufacturing'],
    priority: 43, cols: 1,
  },

  // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  // WMS / WAREHOUSE WIDGETS (cols=1)
  // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  {
    id: 'warehouse_utilization',
    title: 'Utilization Gudang',
    component: WarehouseUtilizationWidget,
    roles: ['owner', 'admin', 'warehouse_manager', 'warehouse_supervisor'],
    features: ['warehouse'],
    permissions: ['manage_warehouse'],
    priority: 44, cols: 1,
  },

  {
    id: 'picking_queue',
    title: 'Picking Queue',
    component: PickingQueueWidget,
    roles: ['owner', 'admin', 'warehouse_manager', 'warehouse_supervisor', 'picking_staff'],
    features: ['warehouse'],
    permissions: ['manage_warehouse'],
    priority: 45, cols: 1,
  },

  {
    id: 'shipments_today',
    title: 'Shipments Today',
    component: ShipmentsTodayWidget,
    roles: ['owner', 'admin', 'warehouse_manager', 'logistics', 'courier'],
    features: ['warehouse'],
    permissions: ['manage_warehouse'],
    priority: 46, cols: 1,
  },

  // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  // DMS / DOCUMENT WIDGETS (cols=1)
  // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  {
    id: 'pending_approvals',
    title: 'Pending Approvals',
    component: PendingApprovalsWidget,
    roles: ['owner', 'admin', 'document_controller', 'department_manager', 'approver'],
    features: ['documents'],
    permissions: ['manage_documents'],
    priority: 47, cols: 1,
  },

  {
    id: 'knowledge_articles',
    title: 'Knowledge Articles',
    component: KnowledgeArticlesWidget,
    roles: ['owner', 'admin', 'knowledge_editor', 'employee'],
    features: ['documents'],
    permissions: ['manage_documents'],
    priority: 48, cols: 1,
  },

  {
    id: 'recent_docs',
    title: 'Recent Docs',
    component: RecentDocsWidget,
    roles: ['owner', 'admin', 'document_controller', 'employee'],
    features: ['documents'],
    permissions: ['manage_documents'],
    priority: 49, cols: 1,
  },

  // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  // AI WIDGETS (cols=1)
  // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  {
    id: 'business_health',
    title: 'Business Health',
    component: BusinessHealthWidget,
    roles: ['owner', 'admin', 'manager'],
    features: ['ai'],
    permissions: ['use_ai'],
    priority: 50, cols: 1,
  },

  {
    id: 'ai_recommendations',
    title: 'AI Recommendations',
    component: AIRecommendationsWidget,
    roles: ['owner', 'admin', 'manager', 'supervisor'],
    features: ['ai'],
    permissions: ['use_ai'],
    priority: 51, cols: 1,
  },

  {
    id: 'predicted_revenue',
    title: 'Predicted Revenue',
    component: PredictedRevenueWidget,
    roles: ['owner', 'admin', 'manager', 'finance'],
    features: ['ai'],
    permissions: ['use_ai'],
    priority: 52, cols: 1,
  },

  // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  // INTEGRATION WIDGETS (cols=1)
  // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  {
    id: 'api_health',
    title: 'API Health',
    component: APIHealthWidget,
    roles: ['owner', 'super_admin', 'admin', 'developer'],
    features: ['integration'],
    permissions: ['manage_integration'],
    priority: 53, cols: 1,
  },

  {
    id: 'webhook_queue',
    title: 'Webhook Queue',
    component: WebhookQueueWidget,
    roles: ['owner', 'super_admin', 'admin', 'developer'],
    features: ['integration'],
    permissions: ['manage_integration'],
    priority: 54, cols: 1,
  },

  {
    id: 'marketplace_sync',
    title: 'Marketplace Sync',
    component: MarketplaceSyncWidget,
    roles: ['owner', 'super_admin', 'admin', 'warehouse', 'marketing'],
    features: ['integration'],
    permissions: ['manage_integration'],
    priority: 55, cols: 1,
  },

  // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  // PLATFORM ADMIN WIDGETS (cols=1)
  // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  {
    id: 'platform_health',
    title: 'Platform Health',
    component: PlatformHealthWidget,
    roles: ['super_admin', 'platform_admin', 'devops'],
    features: ['platform'],
    permissions: ['manage_platform'],
    priority: 56, cols: 1,
  },

  {
    id: 'tenant_growth',
    title: 'Active Tenants',
    component: TenantGrowthWidget,
    roles: ['super_admin', 'platform_admin'],
    features: ['platform'],
    permissions: ['manage_platform'],
    priority: 57, cols: 1,
  },

  {
    id: 'mrr',
    title: 'MRR',
    component: MRRWidget,
    roles: ['super_admin', 'platform_admin', 'finance'],
    features: ['platform'],
    permissions: ['manage_platform'],
    priority: 58, cols: 1,
  },

  // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  // GRC WIDGETS (cols=1)
  // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  {
    id: 'governance_score',
    title: 'Governance Score',
    component: GovernanceScoreWidget,
    roles: ['owner', 'admin', 'director', 'risk_officer', 'compliance_officer', 'internal_auditor'],
    features: ['grc'],
    permissions: ['manage_grc'],
    priority: 59, cols: 1,
  },

  {
    id: 'critical_risks',
    title: 'Critical Risks',
    component: CriticalRisksWidget,
    roles: ['owner', 'admin', 'director', 'risk_officer', 'internal_auditor'],
    features: ['grc'],
    permissions: ['manage_grc'],
    priority: 60, cols: 1,
  },

  {
    id: 'open_findings_widget',
    title: 'Open Findings',
    component: OpenFindingsWidget,
    roles: ['owner', 'admin', 'risk_officer', 'compliance_officer', 'internal_auditor', 'external_auditor'],
    features: ['grc'],
    permissions: ['manage_grc'],
    priority: 61, cols: 1,
  },

  // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  // EPOC / PLATFORM OPERATIONS WIDGETS (cols=1)
  // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  {
    id: 'platform_health_score',
    title: 'Platform Health',
    component: PlatformHealthScoreWidget,
    roles: ['super_admin', 'platform_admin', 'devops', 'sre'],
    features: ['epoc'],
    permissions: ['manage_platform'],
    priority: 62, cols: 1,
  },
  {
    id: 'cpu_usage',
    title: 'CPU Usage',
    component: CPUUsageWidget,
    roles: ['super_admin', 'platform_admin', 'devops', 'sre', 'infrastructure_engineer'],
    features: ['epoc'],
    permissions: ['manage_platform'],
    priority: 63, cols: 1,
  },
  {
    id: 'memory_usage',
    title: 'Memory Usage',
    component: MemoryUsageWidget,
    roles: ['super_admin', 'platform_admin', 'devops', 'sre', 'infrastructure_engineer'],
    features: ['epoc'],
    permissions: ['manage_platform'],
    priority: 64, cols: 1,
  },
  {
    id: 'storage_usage',
    title: 'Storage Usage',
    component: StorageUsageWidget,
    roles: ['super_admin', 'platform_admin', 'devops', 'sre'],
    features: ['epoc'],
    permissions: ['manage_platform'],
    priority: 65, cols: 1,
  },
  {
    id: 'database_health',
    title: 'Database Health',
    component: DatabaseHealthWidget,
    roles: ['super_admin', 'platform_admin', 'devops', 'sre'],
    features: ['epoc'],
    permissions: ['manage_platform'],
    priority: 66, cols: 1,
  },
  {
    id: 'slow_queries_ops',
    title: 'Slow Queries',
    component: SlowQueriesWidget,
    roles: ['super_admin', 'platform_admin', 'devops', 'sre'],
    features: ['epoc'],
    permissions: ['manage_platform'],
    priority: 67, cols: 1,
  },
  {
    id: 'queue_health_ops',
    title: 'Queue Pending',
    component: QueueHealthWidget,
    roles: ['super_admin', 'platform_admin', 'devops', 'sre'],
    features: ['epoc'],
    permissions: ['manage_platform'],
    priority: 68, cols: 1,
  },
  {
    id: 'failed_jobs_ops',
    title: 'Failed Jobs',
    component: FailedJobsWidget,
    roles: ['super_admin', 'platform_admin', 'devops', 'sre'],
    features: ['epoc'],
    permissions: ['manage_platform'],
    priority: 69, cols: 1,
  },
  {
    id: 'api_response_time',
    title: 'API Response',
    component: APIResponseTimeWidget,
    roles: ['super_admin', 'platform_admin', 'devops', 'sre'],
    features: ['epoc'],
    permissions: ['manage_platform'],
    priority: 70, cols: 1,
  },
  {
    id: 'error_rate_ops',
    title: 'Error Rate',
    component: ErrorRateWidget,
    roles: ['super_admin', 'platform_admin', 'devops', 'sre'],
    features: ['epoc'],
    permissions: ['manage_platform'],
    priority: 71, cols: 1,
  },
  {
    id: 'active_sessions_ops',
    title: 'Active Sessions',
    component: ActiveSessionsWidget,
    roles: ['super_admin', 'platform_admin', 'devops'],
    features: ['epoc'],
    permissions: ['manage_platform'],
    priority: 72, cols: 1,
  },
  {
    id: 'cache_hit_ratio_ops',
    title: 'Cache Hit Ratio',
    component: CacheHitRatioWidget,
    roles: ['super_admin', 'platform_admin', 'devops', 'sre'],
    features: ['epoc'],
    permissions: ['manage_platform'],
    priority: 73, cols: 1,
  },
  {
    id: 'integration_health_ops',
    title: 'Integration Health',
    component: IntegrationHealthWidget,
    roles: ['super_admin', 'platform_admin', 'devops', 'sre'],
    features: ['epoc'],
    permissions: ['manage_platform'],
    priority: 74, cols: 1,
  },
  {
    id: 'webhook_queue_ops',
    title: 'Webhook Queue',
    component: WebhookQueueOpsWidget,
    roles: ['super_admin', 'platform_admin', 'devops'],
    features: ['epoc'],
    permissions: ['manage_platform'],
    priority: 75, cols: 1,
  },
  {
    id: 'security_alerts_ops',
    title: 'Security Alerts',
    component: SecurityAlertsWidget,
    roles: ['super_admin', 'platform_admin', 'security_engineer'],
    features: ['epoc'],
    permissions: ['manage_platform'],
    priority: 76, cols: 1,
  },
  {
    id: 'uptime',
    title: 'Uptime',
    component: UptimeWidget,
    roles: ['super_admin', 'platform_admin', 'devops', 'sre', 'owner'],
    features: ['epoc'],
    permissions: ['manage_platform'],
    priority: 77, cols: 1,
  },
  {
    id: 'ai_infra_insight',
    title: 'AI Infra Insights',
    component: AIInfrastructureInsightWidget,
    roles: ['super_admin', 'platform_admin', 'devops', 'sre'],
    features: ['epoc'],
    permissions: ['manage_platform'],
    priority: 78, cols: 1,
  },

  // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  // ROW 3 — CHART WIDGETS (cols=2)
  // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  {
    id: 'status_chart',
    title: 'Status Servis',
    component: StatusChartWidget,
    roles: ['owner', 'admin', 'manager', 'cs', 'technician'],
    denyBusinessTypes: ['retail_only'],
    features: ['services'],
    priority: 30, cols: 2,
  },

  {
    id: 'service_trend',
    title: 'Tren Servis',
    component: ServiceTrendWidget,
    roles: ['owner', 'admin', 'manager'],
    denyBusinessTypes: ['retail_only'],
    features: ['services'],
    priority: 31, cols: 2,
  },

  // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  // ROW 4 — CONTENT WIDGETS (cols=2)
  // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  {
    id: 'recent_services',
    title: 'Servis Terbaru',
    component: RecentServiceWidget,
    roles: ['owner', 'admin', 'manager', 'cs', 'technician'],
    denyBusinessTypes: ['retail_only'],
    features: ['services'],
    priority: 40, cols: 2,
  },

  {
    id: 'activity',
    title: 'Aktivitas',
    component: ActivityWidget,
    roles: ['owner', 'admin', 'manager', 'cs'],
    denyBusinessTypes: ['retail_only'],
    features: ['services'],
    priority: 41, cols: 2,
  },

  {
    id: 'recent_sales',
    title: 'Penjualan Terbaru',
    component: RecentSalesWidget,
    roles: ['owner', 'admin', 'manager', 'cashier', 'head_store'],
    features: ['sales'],
    permissions: ['manage_sales'],
    priority: 42, cols: 2,
  },

  {
    id: 'stock_alerts',
    title: 'Peringatan Stok',
    component: StockAlertWidget,
    roles: ['owner', 'admin', 'manager', 'head_store'],
    features: ['products'],
    permissions: ['manage_products'],
    priority: 43, cols: 2,
  },

]);

export default registry;
