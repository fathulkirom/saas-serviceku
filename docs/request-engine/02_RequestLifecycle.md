# 02 — Request Lifecycle

> **Sprint 6.1D · Architecture Freeze · Blueprint Only.**
> State machine Request — status wajib, status opsional per channel, transisi yang diizinkan.
> **RequestLifecycle adalah state machine UNIFIED** yang menaungi semua jenis request.

---

## 1. Daftar Status Request

| Status | Wajib? | Deskripsi | Berlaku untuk channel |
|---|---|---|---|
| `draft` | Opsional | Belum dikirim; masih bisa diedit. | CS/Owner menyiapkan request manual |
| `created` | **WAJIB** | Request terkirim/resmi masuk sistem. | Semua channel |
| `scheduled` | Opsional | Ada janji waktu (appointment). | Booking, Pickup, Home Service, Corporate |
| `waiting_pickup` | Opsional | Menunggu penjemputan. | Pickup, Courier |
| `picked_up` | Opsional | Device sudah dijemput. | Pickup, Courier |
| `in_transit` | Opsional | Dalam perjalanan ke toko. | Courier (pengiriman jarak jauh) |
| `received` | Opsional | Device diterima di toko/cabang. | Pickup, Courier |
| `assigned` | Opsional | Dialokasikan ke CS/Teknisi. | Walk In, WhatsApp, Booking, Corporate |
| `processing` | **WAJIB** | Sedang dikerjakan (telah di-fork ke ServiceOrder/SalesOrder/dll). | Semua (kecuali cancelled sebelum fork) |
| `completed` | **WAJIB** | Pekerjaan/kunjungan selesai. | Semua |
| `delivered` | Opsional | Device dikembalikan ke customer. | Pickup, Courier, Home Service (selesai di tempat = delivered implisit) |
| `closed` | **WAJIB** | Terminal — semua beres. | Semua |
| `cancelled` | **WAJIB** | Terminal — dibatalkan. | Semua (sebelum completed/delivered) |
| `archived` | Opsional | Dipindahkan ke arsip (setelah closed/cancelled lama). | Semua |

> **4 status WAJIB (minimum):** `created → processing → completed → closed` (atau `cancelled`).
> Status lain **ditambahkan progresif** sesuai channel — tidak dipaksa pada channel yang tidak membutuhkan.

---

## 2. State Machine (Unified)

```mermaid
stateDiagram-v2
    [*] --> draft: Manual build (CS/Owner)
    draft --> created: Submit
    draft --> cancelled: Batalkan draft

    [*] --> created: Auto-create (API/WA/Marketplace)
    [*] --> created: Walk In / Phone / Garansi

    created --> scheduled: Booking / Pickup / Corporate
    created --> assigned: Walk In / WA / API (langsung alokasi)

    scheduled --> waiting_pickup: Pickup/Courier — jadwal dijalankan
    scheduled --> assigned: Booking — langsung ke teknisi
    scheduled --> cancelled

    waiting_pickup --> picked_up: Kurir/Teknisi jemput
    waiting_pickup --> cancelled

    picked_up --> in_transit: Perjalanan (opsional)
    picked_up --> received: Sampai di toko/cabang

    in_transit --> received

    received --> assigned: Alokasi ke CS/Teknisi
    assigned --> processing: FORK → ServiceOrder/SalesOrder/Warranty
    processing --> completed: Pekerjaan selesai
    completed --> delivered: Kembalikan device (Pickup/Courier/Home)
    completed --> closed: Walk In / Booking / WA / API
    delivered --> closed

    created --> cancelled
    scheduled --> cancelled
    waiting_pickup --> cancelled
    picked_up --> cancelled
    assigned --> cancelled
    processing --> cancelled (dengan reversal, lihat BR-015)

    closed --> archived
    cancelled --> archived
```

---

## 3. Status per Channel (Ringkas)

| Channel | Alur status yang dijalani |
|---|---|
| **Walk In** | `created → assigned → processing → completed → closed` |
| **Pickup** | `created → scheduled → waiting_pickup → picked_up → received → assigned → processing → completed → delivered → closed` |
| **Home Service** | `created → scheduled → assigned → processing(on-site) → completed → closed` |
| **Courier** | `created → scheduled → waiting_pickup → picked_up → in_transit → received → assigned → processing → completed → delivered → closed` |
| **Corporate** | `created → scheduled → assigned → processing → completed → delivered → closed` (dengan batch sub-requests) |
| **Booking** | `created → scheduled → assigned → processing → completed → closed` |
| **WhatsApp** | `created → assigned → processing → completed → closed` |
| **Marketplace** | `created → assigned → processing → completed → delivered → closed` |
| **API** | `created → assigned → processing → completed → closed` |
| **Warranty Claim** | `created → assigned → processing(klaim) → completed(resolved) → closed` |

---

## 4. Aturan Lifecycle

1. **Terminal state** (`closed`, `cancelled`) tidak dapat bertransisi keluar — kecuali `archived` (arsip).
2. **Fork ke domain turunan** terjadi pada transisi `assigned → processing` (atau `created → processing` untuk channel yang langsung tancap).
3. `cancelled` setelah fork → **wajib reversal** di domain turunan (ServiceOrder/SalesOrder void/rollback) + audit.
4. Status tidak boleh dilompati mundur (forward-only, kecuali `cancelled` yang bisa dari mana saja).
5. `delivered` implisit = `completed` untuk Home Service (pekerjaan selesai di tempat = sudah delivered).
6. `scheduled_at` wajib diisi saat masuk `scheduled`; jika lewat tanpa penjemputan, perlu manual follow-up (tidak auto-cancel).

---

## 5. Hubungan dengan Workflow Domain Turunan

Setelah Request masuk `processing`, domain turunan memiliki **workflow sendiri**:

```
Request.processing → fork → ServiceOrder (14 status sendiri)
Request.processing → fork → SalesOrder (draft→selesai→pending→success)
Request.processing → fork → Warranty (aktif→klaim→resolved)
Request.processing → fork → Booking (jadwal→hadir→selesai)
```

Workflow domain turunan **tidak mengubah status Request** secara langsung — Request mengikuti lifecycle-nya sendiri. Namun, domain turunan yang selesai memicu transisi `processing → completed` pada Request.

**Event yang menghubungkan:**
```
ServiceOrderCompleted → Request.processing → completed
SalesOrderCompleted  → Request.processing → completed (untuk retail)
ClaimResolved        → Request.processing → completed (untuk warranty)
```

---

## 6. Prinsip yang Dipenuhi

| Prinsip | Cara |
|---|---|
| Simple by Default | Walk In = 5 status minimal (created→assigned→processing→completed→closed) |
| Progressive Complexity | Status Pickup/courier hanya ditambahkan bila dibutuhkan |
| Configuration over Code | Status = data (Workflow Engine); tiap channel memilih subset |
| Grow Without Migration | Channel baru tinggal memilih subset status yang ada |
| Data is Sacred | `cancelled` + reversal = audit penuh, tidak ada hapus fisik |

---

## 7. Verifikasi

Status lifecycle konsisten dengan domain turunan (14 status ServiceOrder, 5 status Payment, 4 status Subscription). Status `processing` adalah jembatan ke domain turunan. Selaras dengan `docs/domain/DomainLifecycle.md`.
