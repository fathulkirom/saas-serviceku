# 11 — Risk Assessment · 12 — Scalability Audit · 13 — Security Audit · 14 — Performance Audit

> **Sprint 6.2E · Architecture Freeze Review v1.0.** Dokumen gabungan.

---

## Part A — Risk Assessment (11)

### Critical Risks (Merah)
| Risk | Deskripsi | Mitigasi | Status |
|---|---|---|---|
| **R01** | WhatsApp Web diblokir Meta | Fallback ke Email; upgrade path ke WA Cloud API | 🔴 Critical — terima sebagai trade-off (Practical over Perfect) |
| **R02** | Data PII bocor (UU PDP) | L3 classification; masking log; audit akses; encrypt credentials | 🔴 Diterima — mitigasi di desain |

### High Risks (Oranye)
| Risk | Deskripsi | Mitigasi |
|---|---|---|
| **R03** | Central DB down → semua tenant tidak bisa akses | Central DB minimal; tenant DB independen; failover/replica |
| **R04** | `request_id` immutable — jika salah assign, tidak bisa koreksi | `request_id` hanya di-set sekali oleh sistem (bukan manual). Human error = correction via reversal (BR-015) |
| **R05** | Kompleksitas 52 tabel — developer baru butuh waktu onboarding | Dokumen lengkap; 01_TableCatalog.md sebagai peta |

### Medium Risks (Kuning)
| Risk | Mitigasi |
|---|---|
| **R06** Partisi future belum implementasi — tabel log bisa lambat | Arsip rutin; indeks; partisi = P2 |
| **R07** Multi-role user_role pivot belum implementasi | Target; backward compatible (kolom `role` tetap berfungsi) |
| **R08** StockCluster/Gudang (BR-005) di-defer | InventoryItem siap scope branch/cluster; additive |

### Low Risks (Hijau)
| Risk | Mitigasi |
|---|---|
| **R09** Marketplace integration = P2 | Interface siap; `requests(type=marketplace)` |
| **R10** Customer Portal = P2 | Data siap; projection dari tabel existing |

---

## Part B — Scalability Audit (12)

| Uji Skalabilitas | Desain | Status |
|---|---|---|
| Tenant 1 → 10.000 | 1 DB per tenant; central DB minimal | ✅ |
| 1 cabang → 100 cabang | Branch table; stok/kas per cabang; laporan agregat | ✅ |
| 10 servis/hari → 10.000/hari | Indeks optimal; arsip; queue (Jobs) | ✅ |
| 1 attachment → 1 TB storage | Provider pattern (S3/R2); kuota per plan | ✅ |
| 1 user → 1.000 user per tenant | RBAC; permission engine; multi-role (target) | ✅ |
| Audit log 1M → 100M baris | Partisi (blueprint); arsip 1 tahun | ✅ (P2 partisi) |
| Request history besar | Partisi (blueprint); arsip | ✅ (P2) |

---

## Part C — Security Audit (13)

| Aspek | Desain | Status |
|---|---|---|
| Tenant Isolation | 1 DB per tenant; no cross-query | ✅ |
| Credential Encryption | AES-256; per tenant; tidak shared | ✅ |
| Data Classification | L0–L4; masking log; audit akses | ✅ |
| Permission | Permission Engine (target); `module.action` | ✅ |
| Audit Trail | Append-only `audit_logs` + `request_history` | ✅ |
| Soft Delete | Transaksional tidak hard delete | ✅ |
| API Security (future) | API key + rate limit per plan | ✅ |
| OWASP Top 10 | — | ⚠️ Belum di-audit (perlu pentest) |

---

## Part D — Performance Audit (14)

| Tabel | Estimasi 5 tahun | Strategi | Risiko |
|---|---|---|---|
| `audit_logs` | 2.5M–25M | Indeks + arsip + partisi (P2) | ⚠️ Tanpa partisi = lambat > 5M |
| `request_history` | 1M–10M | Indeks + arsip | ⚠️ |
| `inventory_movements` | 500K–5M | Indeks + arsip | ✅ |
| `service_orders` | 50K–2.5M | Indeks composite | ✅ |
| `notifications` | 500K–5M | Arsip > 1 tahun | ✅ |

**Rekomendasi:** Implementasi partisi untuk `audit_logs` dan `request_history` saat tenant mencapai > 5M baris. Blueprint sudah siap.

---

## Verdict

- **Critical risks:** 2 (WA Web + PII) — diterima dengan mitigasi.
- **High risks:** 3 — semuanya termitigasi.
- **Skalabilitas:** Memadai. Partisi = P2.
- **Keamanan:** Desain kuat. Pentest direkomendasikan.
- **Performa:** Indeks + arsip cukup. Partisi untuk tabel log > 5M.
