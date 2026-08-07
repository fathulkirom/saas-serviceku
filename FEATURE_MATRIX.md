# FEATURE MATRIX — ServiceKU v0.9.0-beta

## Feature → Business Type Matrix

| Feature | full_service | aksesoris_service | aksespare_service | gadget_full | retail_only |
|---------|:-----------:|:-----------------:|:-----------------:|:-----------:|:-----------:|
| **services** | ✅ Full | ✅ Full | ✅ Full | ✅ Full | ❌ None |
| **customers** | ✅ Full | ✅ Full | ✅ Full | ✅ Full | ✅ Full |
| **products** | ✅ Full | ✅ Full | ✅ Full | ✅ Full | ✅ Full |
| **sales** | ✅ Full | ✅ Full | ✅ Full | ✅ Full | ✅ Full |
| **reports** | ✅ Full | ✅ Full | ✅ Full | ✅ Full | ✅ Full |
| **settings** | ✅ Full | ✅ Full | ✅ Full | ✅ Full | ✅ Full |
| **monitoring** | ✅ Full | ✅ Full | ✅ Full | ✅ Full | ✅ Full |
| **multi_branch** | ✅ Full | ✅ Full | ✅ Full | ✅ Full | ✅ Full |
| **transfer_stock** | ✅ Full | ✅ Full | ✅ Full | ✅ Full | ✅ Full |
| **users** | ✅ Full | ✅ Full | ✅ Full | ✅ Full | ✅ Full |
| **expenses** | ✅ Full | ✅ Full | ✅ Full | ✅ Full | ✅ Full |
| **purchases** | ✅ Full | ✅ Full | ✅ Full | ✅ Full | ✅ Full |
| **deposits** | ✅ Full | ✅ Full | ✅ Full | ✅ Full | ✅ Full |
| **checklist** | ✅ Full | ✅ Full | ✅ Full | ✅ Full | ❌ None |
| **indents** | ✅ Full | ✅ Full | ✅ Full | ✅ Full | ✅ Full |
| **cash_register** | ✅ Full | ✅ Full | ✅ Full | ✅ Full | ✅ Full |
| **master_data** | ✅ Full | ✅ Full | ✅ Full | ✅ Full | ✅ Full |
| **inventaris** | ✅ Full | ✅ Full | ✅ Full | ✅ Full | ✅ Full |

## Feature → Module Matrix

| Feature | Module(s) | Dependency |
|---------|-----------|------------|
| services | Service, WorkOrder, Quotation, QC, Delivery, Warranty | customers |
| customers | Customer, Device, Interaction, Communication, Tag | — |
| products | Product, InventoryMutation, StockLocation | — |
| sales | Sale, CashRegister, Shift, Discount, Promotion | products, customers |
| inventaris | InventoryMutation, StockOpname, StockTransfer, TechnicianInventory | products |
| purchases | PurchaseOrder, Purchase, Supplier | products |
| checklist | ChecklistTemplate, ServiceChecklist | services |
| multi_branch | Branch | — |
| transfer_stock | StockAllocation | multi_branch |
| monitoring | EventLog, ActivityLog, SystemAlert | — |
| reports | ReportController (finance, sales, services, inventory, commissions, productivity) | services, sales |
| settings | TenantSetting, ProviderAdapter, WhatsAppService, GoogleDriveService | — |

## Module Activation (per-tenant)

Tenants can disable modules via `tenant_modules` table:
- Module explicitly disabled → FeatureEngine returns `none`
- Module enabled (default) → passes through to Plan check
- No entry → defaults to Plan check

### Available Modules
> Sinkron dengan `database/seeders/Tenant/ModuleRegistrySeeder.php`.
> Modul bertanda **Future** belum aktif (lihat docs/specification/ModuleSpecification.md §3).

| Module Key | Name | Default |
|-----------|------|---------|
| `dashboard` | Dashboard | ✅ |
| `customer` | Customer | ✅ |
| `service` | Service Center | ✅ |
| `inventory` | Inventory | ✅ |
| `sales` | POS / Sales | ✅ |
| `purchase` | Purchase | ✅ |
| `supplier` | Supplier | ✅ |
| `cash_register` | Cash Register | ✅ |
| `expense` | Expense | ✅ |
| `finance` | Finance & Accounting | ✅ |
| `report` | Reports & Analytics | ✅ |
| `monitoring` | Monitoring | ✅ |
| `branch` | Multi Branch | ✅ |
| `user` | User Management | ✅ |
| `settings` | Settings | ✅ |
| `warranty` | Warranty | 🔄 Future |
| `provider` | Provider Integrations | ✅ |
| `subscription` | Subscription | ✅ |
| `marketplace` | Marketplace | 🔄 Future |
| `ai` | AI Assistant | 🔄 Future |
