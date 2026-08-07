# Inventory Workspace Guide

> Enterprise Inventory Workspace — 10 tabs, role-aware, all registry-driven.

---

## Tabs

| # | Tab ID | Label | Roles |
|---|--------|-------|-------|
| 1 | overview | Overview | All |
| 2 | movement | Stock Movement | All |
| 3 | purchase | Purchase History | All |
| 4 | sales | Sales History | All |
| 5 | service | Service Usage | All |
| 6 | transfer | Transfer | All |
| 7 | supplier | Supplier | All |
| 8 | pricing | Price History | All |
| 9 | serial | Serial/IMEI | Owner, Admin, Manager, HeadStore |
| 10 | documents | Documents | All |

---

## Actions (Role-Aware)

| Action | Roles |
|--------|-------|
| Tambah Stok | Owner, Admin, Manager, HeadStore |
| Adjustment | Owner, Admin, Manager |
| Transfer | Owner, Admin, Manager, HeadStore |
| Stock Opname | Owner, Admin, Manager |
| Print Label | Owner, Admin, Manager, HeadStore |
| Export | Owner, Admin, Manager |

---

## Sidebar Widgets

| Widget | Priority |
|--------|:--------:|
| Stock Summary | 10 |
| Warehouse Info | 20 |
| Supplier Card | 30 |
| Stock Alerts | 40 |

---

## Business Type Gating

All inventory features are gated by `features: ['products']` via FeatureEngine. Modules without product access (e.g., basic plans) will not see inventory features.

---

*Inventory Workspace — Sprint 17.0*
