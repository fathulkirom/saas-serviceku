# 03 — Data Lifecycle

> **Sprint 6.2A · Blueprint Only.** Siklus hidup data per domain: kapan dibuat, kapan berubah, kapan terminal, kapan diarsipkan, berapa lama disimpan.
> **Retention periods** adalah rekomendasi blueprint — dapat disesuaikan per tenant (policy).

---

## 1. Lifecycle per Domain

| Domain | Dibuat saat | Status aktif | Terminal | Arsip setelah | Retensi minimum | Boleh hard delete? |
|---|---|---|---|---|---|---|
| **Tenant** | Registrasi OTP | trial/active | expired/suspended → nonaktif | 90 hari setelah nonaktif | 7 tahun (regulasi) | ❌ (soft only) |
| **Branch** | Owner buat | aktif | nonaktif | 30 hari setelah nonaktif | 7 tahun | ❌ |
| **User** | Owner undang/buat | aktif | suspended → nonaktif | 30 hari setelah nonaktif | 7 tahun | ❌ (berhistori) |
| **Role** | Seed / Owner buat (target) | aktif | nonaktif | 30 hari | Permanen | ❌ (sistem) |
| **Permission** | Registry modul | aktif | — | — | Permanen | ❌ |
| **Policy** | Owner buat | aktif | revisi (versi baru) → nonaktif | 7 tahun setelah nonaktif | Permanen (historis) | ❌ (versioning) |
| **Customer** | CS/Admin buat | aktif | inactive/blacklist | 7 tahun setelah inactive | 7 tahun (garansi) | ❌ |
| **Device** | CS daftarkan | aktif | ganti pemilik / arsip | 7 tahun setelah servis terakhir | 7 tahun | ❌ |
| **Supplier** | Owner/Admin buat | aktif | nonaktif | 3 tahun setelah nonaktif | 7 tahun | ❌ (PO historis) |
| **Service Partner** | Owner/Admin buat | aktif | nonaktif | 3 tahun | 7 tahun | ❌ |
| **Product** | Admin buat | aktif | discontinued | 3 tahun setelah discontinued | 7 tahun | ❌ (historis) |
| **Request** | CS/Sistem/API | lifecycle status (lihat Sprint 6.1D) | closed / cancelled | 1 tahun setelah closed | 7 tahun | ❌ |
| **Service Order** | Fork dari Request | 14 status (lihat Domain) | diambil / close / void / cancel | 1 tahun setelah terminal | 7 tahun (garansi) | ❌ |
| **Sales Order** | Fork dari Request / POS | draft→selesai→pending→success/failed/expired | refunded / void | 3 tahun setelah terminal | 7 tahun | ❌ |
| **Purchase Order** | Admin/Manager buat | draft→PO→terima→bayar→close | void | 3 tahun setelah terminal | 7 tahun | ❌ |
| **Warranty** | Service selesai | aktif → diklaim | resolved / expired | 1 tahun setelah resolved | 7 tahun | ❌ |
| **Claim** | Klaim dibuat | evaluasi → diterima/ditolak | resolved | 1 tahun | 7 tahun | ❌ |
| **Cash Shift** | Kasir buka | transaksi → tutup | final | 3 tahun | 7 tahun | ❌ |
| **Deposit** | Kasir/Owner buat | menunggu konfirmasi → dikonfirmasi/ditolak | final | 3 tahun | 7 tahun | ❌ |
| **Inventory Movement** | Auto (transaksi) | — | — | 7 tahun | 7 tahun | ❌ (jejak permanen) |
| **Finance Aggregate** | Auto (event) | — | — | Snapshots: 7 tahun | 7 tahun | ❌ |
| **Report** | User generate | — | — | Snapshots: 3 tahun | 3 tahun | ✅ (report snapshot) |
| **Audit Log** | Auto (setiap aksi) | — | — | 1 tahun | 7 tahun | ❌ (append-only) |
| **History Log** | Auto (setiap perubahan) | — | — | 1 tahun | 7 tahun | ❌ (append-only) |
| **Dashboard Widget** | Owner (target) | aktif | nonaktif | — | — | ✅ (konfigurasi) |

---

## 2. Pola Lifecycle Umum

```
Dibuat → Aktif (berubah-ubah) → Terminal (final) → Arsip (read-only) → Hapus (jika diizinkan)
```

| Tahap | Karakteristik |
|---|---|
| **Dibuat** | `created_at` diisi; audit `created` event. |
| **Aktif** | Status non-terminal; data dapat berubah; history dicatat. |
| **Terminal** | Status final (`closed`, `cancelled`, `void`, `nonaktif`, `resolved`); data immutable kecuali arsip. |
| **Arsip** | Dipindahkan ke storage arsip; read-only; bisa di-restore. |
| **Hapus** | Hanya untuk data non-transaksional (widget, report snapshot); soft-delete untuk yang lain. |

---

## 3. Aturan Retensi

1. **Minimum 7 tahun** untuk data transaksional & finansial (acuan regulasi pajak Indonesia).
2. **Audit/History log** disimpan minimal 7 tahun, lalu bisa diarsipkan/dipangkas (policy tenant).
3. **Data non-transaksional** (widget, report snapshot, draft) bisa dihapus lebih cepat — minimal 1 tahun.
4. **Data platform** (tenant registry, plan, payment) = permanen selama platform beroperasi.
5. Retensi di atas adalah **default** — tenant dapat mengatur via policy (kecuali regulasi minimum).

---

## 4. Verifikasi

Konsisten dengan `docs/domain/DomainLifecycle.md` (Sprint 6.1), `docs/request-engine/02_RequestLifecycle.md` (Sprint 6.1D), `docs/specification/WorkflowSpecification.md` (Sprint 5.1).
