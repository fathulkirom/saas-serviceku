# 06 — Numbering Strategy

> **Sprint 6.2A · Blueprint Only.** Strategi penomoran untuk setiap entitas yang membutuhkan nomor unik.

---

## 1. Prinsip Penomoran

- **Human-readable** — mudah dibaca dan dikutip (telepon/WA).
- **Unique per tenant** — tidak perlu global (tenant isolation).
- **Immutable** — nomor tidak berubah setelah dibuat.
- **Searchable** — format mendukung pencarian & sorting.
- **Scalable** — tidak collision antar cabang, tidak bergantung auto-increment global.

---

## 2. Format per Domain

| Domain | Format | Contoh | Scope |
|---|---|---|---|
| **Request** | `REQ-{TENANT}-{YYYYMMDD}-{SEQ4}` | `REQ-TOKO-20260802-0001` | Per tenant, per hari |
| **Service Order** | `SVC-{TENANT}-{YYYYMMDD}-{SEQ5}` | `SVC-TOKO-20260802-00001` | Per tenant, per hari |
| **Sales Order** | `INV-{TENANT}-{YYYYMMDD}-{SEQ5}` | `INV-TOKO-20260802-00001` | Per tenant, per hari |
| **Purchase Order** | `PO-{TENANT}-{YYYYMMDD}-{SEQ4}` | `PO-TOKO-20260802-0001` | Per tenant |
| **Customer** | `CUST-{TENANT}-{SEQ6}` | `CUST-TOKO-000001` | Per tenant |
| **Device / Asset** | IMEI atau Serial (dari device fisik) + `DEV-{SEQ}` untuk internal | `DEV-000001` | Per tenant |
| **Cash Shift** | `SHIFT-{BRANCH}-{YYYYMMDD}-{SEQ2}` | `SHIFT-CAB1-20260802-01` | Per branch |
| **Deposit** | `DEP-{TENANT}-{YYYYMMDD}-{SEQ4}` | `DEP-TOKO-20260802-0001` | Per tenant |
| **Warranty** | `WAR-{SERVICE_NUMBER}` | `WAR-SVC-TOKO-20260802-00001` | Ikut Service Order |

---

## 3. Komponen Kode Tenant & Branch

| Komponen | Sumber | Format |
|---|---|---|
| `{TENANT}` | Slug/kode tenant (diambil dari subdomain atau settings) | 3-8 karakter, uppercase |
| `{BRANCH}` | Slug/kode cabang | 3-6 karakter, uppercase |
| `{YYYYMMDD}` | Tanggal pembuatan | ISO date |
| `{SEQn}` | Sequence harian (reset per hari) | Zero-padded |

---

## 4. Aturan Sequence

1. **Per tenant, per hari** — sequence di-reset setiap hari (3-5 digit cukup).
2. **No gap untuk transaksi** — sequence harus kontinu (gunakan DB lock/sequence).
3. **Gap ok untuk master** (customer, product) — sequence tidak wajib kontinu.
4. **Jika cabang menghasilkan nomor sendiri** — tambahkan `{BRANCH}` dalam format.
5. Nomor Request adalah **origin** — Service Order/Sales Order mewarisi dari Request (tidak di-generate ulang).

---

## 5. Format Alternatif (Target — Policy Tenant)

Tenant dapat mengatur format via policy:
- `{TENANT}` → `{BRANCH}` untuk bisnis multi-cabang besar.
- `{YYYYMMDD}` → `{YYMM}` untuk bisnis kecil (lebih pendek).
- `{SEQ}` digit → disesuaikan volume.

**Default:** format di atas (Tabel §2).

---

## 6. Verifikasi

Konsisten dengan `docs/Naming.md` (Sprint 4), `docs/domain/Entity.md` (Sprint 6.1). Format harus tetap human-readable dan searchable.
