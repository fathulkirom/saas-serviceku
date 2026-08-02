# 10 — Soft Delete Strategy

> **Sprint 6.2A · Blueprint Only.** Aturan penghapusan data: soft delete, hard delete, atau tidak boleh dihapus.

---

## 1. Kategori

| Kategori | Aturan | Implementasi |
|---|---|---|
| **Tidak boleh dihapus** | Data permanen; tidak ada mekanisme hapus. | Audit log, History log, Inventory movement |
| **Soft delete** | Data ditandai `deleted_at`; tidak tampil di UI; bisa di-restore. | Hampir semua data operasional |
| **Soft delete + cascade** | Soft delete + semua data anak ikut soft delete. | Request → Service/Sales/Warranty (cascade soft) |
| **Hard delete** | Hapus fisik dari DB. Hanya untuk data non-transaksional. | Report snapshot, Draft (belum fork), Widget config |

---

## 2. Matriks per Domain

| Domain | Strategi | Bisa restore? | Catatan |
|---|---|---|---|
| **Tenant** | Soft delete | ✅ (Super Admin) | Tidak hard delete; data tenant tetap ada |
| **Branch** | Soft delete | ✅ | Hanya jika tidak ada transaksi aktif |
| **User** | Soft delete | ✅ | Berhistori = soft; restore = aktifkan kembali |
| **Role** | Tidak boleh (sistem) / Soft (kustom) | ✅ (kustom) | System role tidak bisa dihapus |
| **Permission** | Tidak boleh | — | Registry |
| **Policy** | Soft (versi) | — | Versi lama tetap ada (versioning); tidak soft delete |
| **Customer** | Soft delete | ✅ | PII — bisa dianonymize (UU PDP) |
| **Device** | Soft delete | ✅ | Berhistori servis = tidak boleh hard delete |
| **Supplier** | Soft delete | ✅ | — |
| **Partner** | Soft delete | ✅ | — |
| **Product** | Soft delete | ✅ | Harga historis tetap ada |
| **Request** | Soft delete + cascade | ✅ | Cascade soft ke Service/Sales/Warranty terkait; `cancelled` = terminal, bukan delete |
| **Service Order** | Soft delete | ✅ | Tidak hard delete — data is sacred |
| **Sales Order** | Soft delete | ✅ | Void/refund ≠ delete; terminal state |
| **Purchase Order** | Soft delete | ✅ | — |
| **Warranty / Claim** | Soft delete | ✅ | — |
| **Cash Shift** | Tidak boleh | — | Data keuangan = permanen |
| **Deposit** | Soft delete | ✅ (Owner/Admin) | — |
| **Inventory Movement** | Tidak boleh | — | Jejak permanen |
| **Finance Aggregate** | Tidak boleh | — | Immutable |
| **Report** | Hard delete | ❌ | Snapshot — user-generated |
| **Dashboard Widget** | Hard delete | ❌ | Konfigurasi UI |
| **Audit Log** | Tidak boleh | — | Append-only, immutable |
| **History Log** | Tidak boleh | — | Append-only |

---

## 3. Aturan Soft Delete

1. **`deleted_at` + `deleted_by`** — setiap soft delete mencatat timestamp & user.
2. **Data tersembunyi dari UI** — query default exclude `WHERE deleted_at IS NOT NULL`.
3. **Restore** — Owner/Admin dapat restore; data kembali normal.
4. **Cascade soft** — menghapus Request soft-deletes semua Service/Sales terkait (tapi tidak hard delete).
5. **Data terminal** (`cancelled`, `void`, `expired`) ≠ delete. Terminal = status; delete = aksi sadar.

---

## 4. Aturan Hard Delete

1. **Hanya untuk non-transaksional** — draft, report snapshot, widget config.
2. **Tidak bisa di-restore** — konfirmasi eksplisit di UI.
3. **Tidak berlaku untuk data PII/financial** — wajib soft delete atau anonymize.

---

## 5. Verifikasi

Konsisten dengan prinsip **Data Is Sacred**, `docs/domain/Aggregate.md` (invariant tidak hapus fisik), `docs/domain-validation/GapAnalysis.md` (6.1A — ADJ-15 data is sacred).
