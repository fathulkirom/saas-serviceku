# ROLE MATRIX — ServiceKU v0.9.0-beta

## Role Definitions

| Role | Key | Description | Dashboard |
|------|-----|-------------|-----------|
| **Owner** | `owner` | Pemilik toko, akses penuh | Owner Dashboard |
| **Admin** | `admin` | Administrator operasional | Owner Dashboard |
| **Manager** | `manager` | Manajer toko | Owner Dashboard |
| **Kepala Toko** | `head_store` | Kepala gudang/toko | Owner Dashboard |
| **CS** | `cs` | Customer Service, penerimaan servis | CS Dashboard |
| **Teknisi** | `technician` | Teknisi reparasi | Technician Dashboard |
| **Kasir** | `cashier` | Kasir, penjualan, pembayaran | Cashier Dashboard |
| **Kurir** | `courier` | Pengiriman & pickup | Courier Dashboard |
| **Kustom** | `custom` | Role kustom (via PermissionEngine) | Owner Dashboard |

## Role → Permission Matrix

| Permission | Owner | Admin | Manager | Head Store | CS | Technician | Cashier | Courier |
|-----------|:-----:|:-----:|:-------:|:----------:|:--:|:----------:|:-------:|:-------:|
| manage_users | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| manage_settings | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| manage_finance | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| manage_products | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| manage_customers | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| manage_sales | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |
| manage_cash_register | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |
| manage_deposits | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| manage_purchases | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| manage_branches | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| manage_indents | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ |
| void_transactions | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| assign_technician | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ |
| work_on_services | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| delete_models | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| quick_stock | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |

## Role → Page Access

| Page | Owner | Admin | Manager | CS | Technician | Cashier |
|------|:-----:|:-----:|:-------:|:--:|:----------:|:-------:|
| Dashboard | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Services (CRUD) | ✅ | ✅ | ✅ | ✅ | ✅* | ❌ |
| Service Intake | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| Kanban | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Customers | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| Products | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Inventory | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| POS / Sales | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ |
| Purchases | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Finance | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Reports | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Settings | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Setup Assistant | ✅ | ✅** | ✅ | ❌ | ❌ | ❌ |
| Users | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Branches | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |

> \* Technician sees only their assigned services  
> \** Admin access to Setup Assistant is configurable via TenantSetting `setup_card_show_admin`

## Setup Card Visibility (Sprint 7.5F)

| Role | Can See Setup Card? | Configurable? |
|------|:-------------------:|:-------------:|
| Owner | ✅ Always | — |
| Manager | ✅ Always | — |
| Admin | ⚙️ Default No | `setup_card_show_admin = true` |
| CS | ❌ Never | — |
| Technician | ❌ Never | — |
| Cashier | ❌ Never | — |
| Warehouse | ❌ Never | — |
| Courier | ❌ Never | — |
| Custom | ✅ If `manage_settings` permission | Via PermissionEngine |
