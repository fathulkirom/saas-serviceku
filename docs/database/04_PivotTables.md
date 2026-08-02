# 04 — Pivot & Child Tables

> **Sprint 6.2D · Table Blueprint Only.** Spesifikasi tabel pivot (many-to-many) dan child/detail.
> **Tidak ada SQL.**

---

## Pivot Tables (N:M)

### PV01 — `request_devices`
| Poin | Spesifikasi |
|---|---|
| **Tujuan** | N:M Request ↔ Device. BR-019. |
| **PK** | Composite: `(request_id, device_id)`. |
| **FK** | `request_id` → requests(id) CASCADE; `device_id` → devices(id). |
| **Unique** | `(request_id, device_id)` UNIQUE. |
| **Index** | `(device_id)` — riwayat request per device. |
| **Soft Delete** | ❌ (ikut request). |

### PV02 — `role_permission`
| Poin | Spesifikasi |
|---|---|
| **Tujuan** | N:M Role ↔ Permission. Permission Engine. |
| **PK** | Composite: `(role_id, permission_id)`. |
| **FK** | `role_id` → roles(id) CASCADE; `permission_id` → permissions(id). |
| **Unique** | `(role_id, permission_id)` UNIQUE. |
| **Soft Delete** | ❌ (hard delete saat role berubah). |

### PV03 — `user_role`
| Poin | Spesifikasi |
|---|---|
| **Tujuan** | N:M User ↔ Role. Multi-role (target). Saat ini: kolom `role` di `users`. |
| **PK** | Composite: `(user_id, role_id)`. |
| **FK** | `user_id` → users(id) CASCADE; `role_id` → roles(id). |
| **Unique** | `(user_id, role_id)` UNIQUE. |
| **Status** | **TARGET** — belum implementasi. |

---

## Child/Detail Tables (1:N)

### CH01 — `work_orders`
| Poin | Spesifikasi |
|---|---|
| **Tujuan** | Sub-pekerjaan dalam ServiceOrder. Progressive (BR-018). |
| **FK** | `service_order_id` → service_orders(id) CASCADE. |
| **Index** | `(service_order_id, status)`. |
| **Status** | Target — opsional. |

### CH02 — `checklists`
| Poin | Spesifikasi |
|---|---|
| **Tujuan** | Checklist perangkat servis. |
| **FK** | `service_order_id` → service_orders(id). |
| **Status** | Opsional (non-retail). |

### CH03 — `technician_assignments`
| Poin | Spesifikasi |
|---|---|
| **Tujuan** | Assignment history teknisi ke ServiceOrder. |
| **FK** | `service_order_id` → service_orders(id); `technician_id` → users(id); `work_order_id` → work_orders(id) NULLABLE. |
| **Index** | `(technician_id, created_at)` — workload. |

### CH04 — `sale_items`
| Poin | Spesifikasi |
|---|---|
| **Tujuan** | Item keranjang POS. |
| **FK** | `sales_order_id` → sales_orders(id) CASCADE; `product_id` → products(id). |
| **Unique** | `(sales_order_id, product_id)` UNIQUE. |

### CH05 — `purchase_items`
| Poin | Spesifikasi |
|---|---|
| **Tujuan** | Item per PO. |
| **FK** | `purchase_order_id` → purchase_orders(id) CASCADE; `product_id` → products(id). |

### CH06 — `deposits`
| Poin | Spesifikasi |
|---|---|
| **Tujuan** | Setoran dari cash shift. |
| **FK** | `shift_id` → cash_shifts(id); `confirmed_by` → users(id). |

### CH07 — `plan_features`
| Poin | Spesifikasi |
|---|---|
| **Tujuan** | Fitur yang termasuk dalam plan (central DB). |
| **FK** | `plan_id` → plans(id). |

### CH08 — `subscription_history`
| Poin | Spesifikasi |
|---|---|
| **Tujuan** | Riwayat perubahan subscription tenant. |
| **FK** | `subscription_id` → subscriptions(id). |

---

## Inventory Tables

### INV01 — `inventory_items`
| Poin | Spesifikasi |
|---|---|
| **Tujuan** | Stok produk per cabang. |
| **PK** | `id` BIGINT. |
| **FK** | `branch_id` → branches(id); `product_id` → products(id). |
| **Unique** | `(branch_id, product_id)` UNIQUE. |
| **Index** | `(branch_id)`; `(product_id)`. |
| **Soft Delete** | ❌ (stok tidak dihapus). |
| **Note** | `qty` = VIEW/AGGREGATE dari SUM(inventory_movements.qty). Tidak ada kolom `qty` langsung. |

### INV02 — `inventory_movements`
| Poin | Spesifikasi |
|---|---|
| **Tujuan** | Append-only jejak mutasi stok. Data Is Sacred. |
| **PK** | `id` BIGINT. |
| **FK** | `inventory_item_id` → inventory_items(id); `reference_type` + `reference_id` (polymorphic: service_order, sales_order, purchase_order, replacement). |
| **Index** | `(inventory_item_id, created_at)`; `(reference_type, reference_id)`. |
| **Soft Delete** | ❌ (append-only, immutable). |
| **Audit** | ❌ (movement sendiri adalah audit trail). |

---

## Verifikasi

4 pivot + 11 child/detail + 2 inventory. Semua mengikuti konvensi FK `<entity>_id`. Semua PK = BIGINT (kecuali pivot composite). Konsisten dengan Sprint 6.2C ERD.
