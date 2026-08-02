# 05 — System & Configuration Tables

> **Sprint 6.2D · Table Blueprint Only.** Spesifikasi tabel Platform (Central DB), Security, Configuration, dan Analytics.
> **Tidak ada SQL.**

---

## Platform Tables (Central DB — L1)

### S01 — `tenants`
| Poin | Spesifikasi |
|---|---|
| **Tujuan** | Registry tenant. 1 baris = 1 DB tenant. |
| **PK** | `id` UUID (keamanan — tidak bisa ditebak). |
| **Unique** | `subdomain` UNIQUE; `email` UNIQUE. |
| **Index** | `(status)` — filter tenant aktif. |
| **Soft Delete** | ✅ Soft delete. Tenant nonaktif = soft delete; 90 hari → arsip. |

### S02 — `plans`
| Poin | Spesifikasi |
|---|---|
| **Tujuan** | Paket subscription (trial/basic/pro/enterprise). |
| **PK** | `id` BIGINT. |
| **Unique** | `key` UNIQUE (trial/basic/pro/enterprise). |

### S03 — `plan_features`
| Poin | Spesifikasi |
|---|---|
| **Tujuan** | Fitur per plan + level (full/read_only/none). |
| **FK** | `plan_id` → plans(id). |

### S04 — `vouchers`
| Poin | Spesifikasi |
|---|---|
| **Tujuan** | Kode voucher/diskon platform. |
| **Unique** | `code` UNIQUE. |

### S05 — `platform_payments`
| Poin | Spesifikasi |
|---|---|
| **Tujuan** | Jejak pembayaran subscription tenant (Midtrans). |
| **FK** | `tenant_id` → tenants(id). |

### S06 — `super_admins`
| Poin | Spesifikasi |
|---|---|
| **Tujuan** | Admin platform. |
| **Unique** | `email` UNIQUE. |

---

## Configuration Tables (Tenant DB — L2)

### C01 — `branches`
| Poin | Spesifikasi |
|---|---|
| **PK** | `id` BIGINT AUTO_INCREMENT. |
| **Unique** | `(tenant_id, name)` UNIQUE. |
| **Index** | `(tenant_id, is_active)`. |
| **Soft Delete** | ✅. |

### C02 — `users`
| Poin | Spesifikasi |
|---|---|
| **PK** | `id` BIGINT AUTO_INCREMENT. |
| **Unique** | `(tenant_id, email)` UNIQUE. |
| **Index** | `(tenant_id, is_active)`; `(email)` — login. |
| **FK** | `branch_id` → branches(id) NULLABLE (multi-branch opsional). |
| **Note** | Kolom `role` VARCHAR (saat ini). Target: `user_role` pivot. `specialization` JSON NULLABLE (BR-006). |

### C03 — `roles`
| Poin | Spesifikasi |
|---|---|
| **PK** | `id` BIGINT AUTO_INCREMENT. |
| **Unique** | `(tenant_id, key)` UNIQUE. |
| **Note** | `is_system` BOOLEAN — system role tidak bisa dihapus. 7 role resmi = system. |

### C04 — `permissions`
| Poin | Spesifikasi |
|---|---|
| **PK** | `id` BIGINT AUTO_INCREMENT. |
| **Unique** | `key` UNIQUE (`module.action`). |
| **Note** | Registry — disalin dari central. |

### C05 — `role_permission` (Pivot)
| Lihat `04_PivotTables.md` PV02. |

### C06 — `user_role` (Pivot, Target)
| Lihat `04_PivotTables.md` PV03. |

### C07 — `positions`
| Poin | Spesifikasi |
|---|---|
| **Status** | Target — opsional. Jabatan struktural. FK: `parent_id` self-reference (hierarki). |

### C08 — `policies`
| Poin | Spesifikasi |
|---|---|
| **PK** | `id` BIGINT AUTO_INCREMENT. |
| **Unique** | `(tenant_id, type, version)` UNIQUE. |
| **Index** | `(tenant_id, type, is_active)`. |
| **Note** | `type`: compensation/warranty/pricing/human_error/commission. `rules` JSON. `valid_from`, `valid_to` — versioning. |

### C09 — `tenant_settings`
| Poin | Spesifikasi |
|---|---|
| **PK** | `id` BIGINT. |
| **Note** | `key` VARCHAR, `value` JSON. Key-value store untuk konfigurasi tenant (provider settings, preferences). |

### C10 — `provider_credentials`
| Poin | Spesifikasi |
|---|---|
| **PK** | `id` BIGINT. |
| **Unique** | `(tenant_id, provider_type, provider_key)` UNIQUE. |
| **Note** | `provider_type`: storage/messaging/payment/ai. `credentials` JSON ENCRYPTED (AES-256). `is_primary` BOOLEAN. |

---

## Analytics Tables (Tenant DB — L5)

### A01 — `dashboard_widgets`
| Poin | Spesifikasi |
|---|---|
| **Tujuan** | Widget dashboard per user. Permission-based (target). |
| **FK** | `user_id` → users(id). |
| **Note** | `widget_key`, `position`, `config` JSON. |

### A02 — `report_snapshots`
| Poin | Spesifikasi |
|---|---|
| **Tujuan** | Hasil generate laporan (cache). |
| **Note** | `report_type`, `parameters` JSON, `result_data` JSON. Hard delete diperbolehkan. |

---

## Verifikasi

10 tabel konfigurasi + 6 platform + 2 analytics = 18 tabel sistem. Semua mengikuti `18_DataStandards.md`. Provider credentials terenkripsi. Policy versioning. Konsisten dengan Sprint 6.2C ERD.
