# 12 — Search Strategy

> **Sprint 6.2A · Blueprint Only.** Strategi pencarian data — field yang dicari, metode, dan prioritas.

---

## 1. Kebutuhan Pencarian per Domain

| Domain | Field dicari | Metode |
|---|---|---|
| **Request** | `request_number`, customer name, device IMEI, phone | Exact (number) + Full-text (nama) |
| **Service Order** | `service_number`, customer name, device IMEI/serial, phone, status | Exact + Full-text |
| **Sales Order** | `invoice_number`, customer name, phone | Exact + Full-text |
| **Customer** | Nama, telepon, alamat | Full-text + Exact (telepon) |
| **Device** | IMEI, serial number, merek, model | Exact (IMEI/serial) + Full-text (merek/model) |
| **Product** | Nama, SKU, barcode | Exact (SKU/barcode) + Full-text (nama) |
| **Supplier** | Nama, telepon | Full-text + Exact (telepon) |
| **Purchase Order** | `po_number`, supplier name | Exact + Full-text |
| **Cash / Deposit** | `deposit_number`, user name | Exact + Full-text |
| **Warranty** | `service_number` terkait, customer | Exact |

---

## 2. Metode Pencarian

| Metode | Gunakan untuk | Implementasi (konsep) |
|---|---|---|
| **Exact match** | Nomor (service, invoice, IMEI, telepon) | Index B-tree pada kolom; query `WHERE number = ?` |
| **Prefix match** | Auto-complete (nama customer, produk) | Index B-tree; query `LIKE 'prefix%'` |
| **Full-text** | Nama, alamat, catatan | Full-text index (MySQL FULLTEXT / SQLite FTS5) |
| **Fuzzy** (future) | Koreksi ejaan nama | Future; untuk sekarang exact + prefix cukup. |
| **Global Search** (Cmd+K) | Semua domain | Aggregated query; prioritas: Service > Request > Customer > Device > Sales |

---

## 3. Prioritas Hasil Pencarian

| Prioritas | Domain | Alasan |
|---|---|---|
| 1 | Service Order | Paling sering dicari (tiket aktif) |
| 2 | Request | Entry point tracking |
| 3 | Customer | Profil pelanggan |
| 4 | Device | Riwayat servis perangkat |
| 5 | Sales Order | Cek transaksi |
| 6 | Product | Cari sparepart |
| 7 | Supplier / PO / lainnya | Jarang dicari |

---

## 4. Aturan Search

1. **Semua pencarian scope tenant** — tidak bisa mencari data tenant lain.
2. **Pencarian global (Cmd+K)** = aggregate dari semua domain; limit per domain = 5 hasil; total = 20-30.
3. **Full-text index** = untuk nama, alamat, catatan; dibuat via migration (bukan sekarang).
4. **Exact match** = untuk nomor, IMEI, barcode, telepon — index B-tree standar.
5. **Customer PII dalam hasil pencarian** — dibatasi oleh permission (CS melihat, Kasir tidak).

---

## 5. Verifikasi

Konsisten dengan `docs/domain/Entity.md` (searchable fields), `GlobalSearch` component (source).
