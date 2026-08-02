# 05 — Request Ownership

> **Sprint 6.1D · Architecture Freeze · Blueprint Only.**
> Kepemilikan Request — siapa pemilik data, siapa penanggung jawab, siapa yang bisa melihat, dan bagaimana audit trail.

---

## 1. Model Kepemilikan Tiga Lapis

| Lapis | Pemilik | Makna |
|---|---|---|
| **Data** | **Tenant** | Request adalah aset tenant; tersimpan di DB tenant, tidak pernah lintas tenant. |
| **Tanggung jawab** | **User** (CS/Teknisi/Owner) yang di-assign atau yang membuat. Bisa di-delegasi (BR-011). |
| **Subjek** | **Customer** | Request *atas nama* customer. Customer melihat progress melalui portal/SMS/WA. Customer *bukan* pemilik data — tenant adalah pemilik. |

---

## 2. Siapa Membuat Request?

| Source | Role | Permission |
|---|---|---|
| Walk-in customer | CS/Admin/Owner mencatat | `request.create` (implied dari `work_on_services` / `manage_customers`) |
| Telepon / WhatsApp | CS/Admin/Owner | `request.create` |
| Booking website | Customer (self-service) | Tanpa login tenant; ter-verifikasi lewat OTP/captcha |
| Marketplace | Sistem (auto) | System-level; dipicu oleh integrasi marketplace |
| API | api_client | API key + rate limit (Subscription Engine) |
| Warranty claim | CS/Admin/Owner / Sistem (auto-detect warranty) | `request.create` |

---

## 3. Siapa Menangani Request?

| Role | Tanggung jawab |
|---|---|
| **CS** | Menerima, mencatat, meng-assign ke teknisi, follow-up, konfirmasi ke customer. |
| **Teknisi** | Mengerjakan (setelah fork ke ServiceOrder), update status, isi checklist. |
| **Owner / Admin / Manager** | Melihat semua request; override, delegasi, eskalasi, cancel. |
| **Kurir** (future role) | Menangani PickupTask / DeliveryTask. |
| **Customer** | Melihat status request-nya sendiri (via portal/WA, future). |

---

## 4. Delegation & Override (BR-011 — No Single Point Of Failure)

Request mendukung **Delegation** sesuai ADJ-06 (Sprint 6.1A):

| Mekanisme | Deskripsi |
|---|---|
| **Reassign** | Request dapat dialihkan dari satu user ke user lain (oleh CS/Admin/Owner). |
| **Delegation (temporary)** | User dengan permission delegation dapat memberi izin sementara ke user lain untuk menangani request-nya. Ada masa berlaku & audit. |
| **Override** | Owner/Admin dapat mengambil alih request siapa pun (force reassign). Tercatat di audit. |
| **Auto-assign** | Berdasarkan spesialisasi teknisi (BR-006) + beban kerja. |

---

## 5. Audit Trail (RequestHistory)

Setiap perubahan pada Request dicatat:

| Kapan | Apa yang dicatat |
|---|---|
| Request dibuat | `who` (source), `source_type`, `channel`, `timestamp` |
| Status berubah | `status lama → status baru`, `who`, `timestamp`, `note` |
| Assign / reassign | `user lama → user baru`, `who`, `timestamp` |
| Fork ke domain turunan | `forked_to: {type, id}`, `timestamp` |
| Cancel | `reason`, `who`, `timestamp`, `reversal_details` |

**Aturan:**
- RequestHistory bersifat **append-only**. Tidak ada update/delete pada history.
- `request_id` di domain turunan (ServiceOrder, SalesOrder, Warranty) bersifat **immutable** — tidak bisa diubah setelah fork.
- Origin trace lengkap: `Request → ServiceOrder → WorkOrder → … → Archive`.

---

## 6. Customer Ownership (Future — Customer Portal)

| Aspek | Status | Keterangan |
|---|---|---|
| Customer melihat request | Future (P2) | Melalui portal self-service / WhatsApp. |
| Customer membuat request | Future | Booking, WhatsApp, Website — Request menangkap source=customer. |
| Customer cancel request | Future | Hanya sebelum fork ke ServiceOrder; setelah fork = perlu CS. |
| Customer mengubah request | Future | Hanya pada request draft / sebelum assigned. |

> Customer **tidak** bisa menghapus request, mengubah data servis, atau mengakses data customer lain. Tenant tetap pemilik data.

---

## 7. Aturan Ownership

1. **Tenant adalah pemilik data.** Request tidak bisa dipindahkan ke tenant lain.
2. **Source dicatat** tapi tidak menentukan ownership — request tetap milik tenant, bukan milik marketplace/API client.
3. **Delegation harus tercatat** (audit). Tidak boleh ada "ambil alih diam-diam".
4. **Customer view** (future) adalah projection dari data tenant — bukan shared ownership.
5. RequestHistory tidak boleh dihapus/diubah — **append-only**.

---

## 8. Prinsip yang Dipenuhi

| Prinsip | Cara |
|---|---|
| No Single Point Of Failure | Multi-creator + Delegation + Override + Auto-assign. Tidak bergantung satu orang. |
| Data is Sacred | Audit trail append-only; origin trace immutable. |
| Tenant Data Isolation | Request scope tenant; customer melihat via projection tenant. |
| Permission over Role | Access Request berdasarkan permission (`request.create`, `request.assign`, `request.cancel`), bukan nama role. |
| Simple by Default | Walk-in request dibuat CS — sederhana. Override/delegation = fitur progresif (BR-011). |

---

## 9. Verifikasi

Konsisten dengan `docs/domain/Ownership.md` (Sprint 6.1) — Request menambah lapisan atas tanpa mengubah kepemilikan domain turunan yang sudah ada. Delegation mengacu pada ADJ-06 (Sprint 6.1A).
