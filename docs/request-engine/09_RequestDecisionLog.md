# 09 — Request Decision Log

> **Sprint 6.1D · Architecture Freeze · Blueprint Only.**
> Semua keputusan yang diambil selama Sprint 6.1D. Status: FINAL / TARGET / DEFERRED / REJECTED.

---

## DEC-R01 — Request sebagai Core Entry Point
- **Keputusan:** Menetapkan Request sebagai satu-satunya pintu masuk operasional ServiceKU.
- **Alasan:** Menyatukan walk-in, pickup, home service, courier, corporate, booking, WhatsApp, marketplace, API, warranty claim dalam satu funnel. Channel baru tidak butuh entitas/tabel baru.
- **Alternatif ditolak:** Per-channel entity (PickupVisit, HomeVisit…), CustomerVisit+type, ServiceOrder+channel.
- **Status:** **FINAL** (ADR-001 ACCEPTED).

## DEC-R02 — CustomerVisit didepresiasi sebagai entry point
- **Keputusan:** CustomerVisit tetap ada sebagai data historis; tidak lagi menjadi entry point operasional baru.
- **Alasan:** Request(type=walk_in) menggantikan fungsinya. CustomerVisit tidak dihapus — backward compatible.
- **Status:** **FINAL**.

## DEC-R03 — Lifecycle Request Unified
- **Keputusan:** 14 status Request (4 wajib: created→processing→completed→closed/cancelled; 10 opsional per channel).
- **Alasan:** Satu lifecycle untuk semua channel; channel memilih subset status.
- **Status:** **FINAL**.

## DEC-R04 — Fork Request ke Domain Turunan
- **Keputusan:** Request tidak menjadi aggregate final — ia melakukan fork ke ServiceOrder/SalesOrder/Warranty/Booking pada status `processing`.
- **Alasan:** Pemisahan tanggung jawab: Request = entry point & tracking; domain turunan = eksekusi.
- **Status:** **FINAL**.

## DEC-R05 — 1 Request → N Device → N Domain Turunan
- **Keputusan:** Satu Request dapat membawa banyak device; setiap device di-fork ke domain turunannya sendiri (parallel).
- **Alasan:** BR-019 (multi-device visit) & corporate batch.
- **Status:** **FINAL**.

## DEC-R06 — PickupTask & DeliveryTask sebagai entitas terpisah
- **Keputusan:** Tugas logistik (jemput, antar) adalah entitas terpisah dari ServiceOrder — dalam satu Request.
- **Alasan:** Pemisahan tanggung jawab kurir vs teknisi; PickupTask bisa dikerjakan oleh orang berbeda.
- **Status:** **TARGET** (implementasi di 6.2+).

## DEC-R07 — Channel, Type, Source sebagai data registry
- **Keputusan:** Channel/type/source = data (registry), bukan enum hardcoded. Perilaku per channel = policy.
- **Status:** **FINAL**.

## DEC-R08 — RequestHistory append-only
- **Keputusan:** RequestHistory tidak bisa di-update/dihapus. Audit trail penuh.
- **Status:** **FINAL**.

## DEC-R09 — `request_id` sebagai origin trace immutable
- **Keputusan:** ServiceOrder, SalesOrder, Warranty menyimpan `request_id` FK; nilainya tidak bisa diubah setelah fork.
- **Status:** **FINAL**.

## DEC-R10 — Delegation & Override pada Request
- **Keputusan:** Request mendukung reassign, delegation (temporary), dan override (owner/admin force) dengan audit penuh.
- **Alasan:** BR-011 (No SPOF).
- **Status:** **TARGET** (implementasi di 6.2+, lihat ADJ-06).

## DEC-R11 — Walk-in tetap sederhana (5 status)
- **Keputusan:** Walk-in tidak dipaksa melewati status pickup/courier.
- **Alasan:** Simple by Default — channel paling umum tidak boleh dibebani kompleksitas.
- **Status:** **FINAL**.

## DEC-R12 — Request tidak menangani payment/billing
- **Keputusan:** Payment tetap di domain turunan (SalesOrder, Subscription). Request hanya menangkap intent & tracking.
- **Alasan:** Pemisahan tanggung jawab; payment = domain finance.
- **Status:** **FINAL**.

## DEC-R13 — Customer Portal (future) — Customer melihat, bukan memiliki
- **Keputusan:** Customer dapat melihat Request miliknya melalui portal/WA/mobile app — tapi data tetap milik tenant. Customer tidak bisa hapus/ubah setelah fork.
- **Status:** **DEFERRED** (P2 — future, setelah ERD & engine inti).

## DEC-R14 — Tidak ada backfill `request_id` untuk data existing
- **Keputusan:** `request_id` di tabel ServiceOrder/SalesOrder existing bersifat nullable. Tidak ada migrasi data wajib.
- **Alasan:** Backward compatible; data lama tetap berfungsi.
- **Status:** **FINAL**.

---

## Ringkasan

| Status | Jumlah | ID |
|---|---|---|
| FINAL | 11 | R01, R02, R03, R04, R05, R07, R08, R09, R11, R12, R14 |
| TARGET | 2 | R06, R10 |
| DEFERRED | 1 | R13 |
| REJECTED | 0 | — |
