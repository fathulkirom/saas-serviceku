# 05 — Data Governance

> **Sprint 6.2A · Blueprint Only.** Aturan tata kelola data: kualitas, standar, penanggung jawab, penanganan pelanggaran.

---

## 1. Pilar Tata Kelola

| Pilar | Aturan |
|---|---|
| **Kualitas** | Data wajib divalidasi sebelum masuk (lihat `19_DataValidation.md`). Duplikasi harus dicegah/dideteksi. |
| **Keamanan** | Akses data = permission-based. Data sensitif dienkripsi. Log akses dicatat. (lihat `16_DataSecurity.md`) |
| **Retensi** | Data disimpan sesuai lifecycle (lihat `03_DataLifecycle.md`). Arsip sesuai jadwal. |
| **Audit** | Setiap perubahan data transaksional tercatat (lihat `08_AuditStrategy.md`). |
| **Kepatuhan** | Data PII mematuhi UU PDP. Data keuangan mematuhi regulasi pajak. |
| **Ketersediaan** | Backup rutin (lihat `CHECKLIST-OPERASIONAL-HARIAN.md`). Recovery teruji. |

---

## 2. Penanggung Jawab Data

| Role | Tanggung jawab |
|---|---|
| **Super Admin** | Tata kelola platform: tenant registry, plan, payment, backup, log platform. |
| **Owner** | Tata kelola tenant: user, policy, settings, retensi, akses data. |
| **Admin / Manager** | Kualitas data operasional: customer, product, transaksi. |
| **CS / Kasir / Teknisi** | Input data akurat sesuai SOP. |
| **System** | Auto-enforce: validasi, audit log, backup, retensi, soft delete. |

---

## 3. Penanganan Data Bermasalah

| Masalah | Penanganan |
|---|---|
| **Duplikat customer** | Deteksi (nama+telepon); merge tool (future); soft-delete duplikat. |
| **Data tidak lengkap** | Validasi di input; tolak jika wajib; peringatan jika opsional. |
| **Pelanggaran integritas** (stok negatif, finance orphan) | System mencegah (constraint); alert ke Owner jika terjadi. |
| **Permintaan hapus data (PII)** | Customer berhak minta hapus (UU PDP); anonymize, bukan hard delete transaksi. |
| **Kebocoran data** | Incident response: isolate, audit, notify, remediate. |

---

## 4. Aturan Governance

1. **Data = aset.** Setiap perubahan struktur data harus melalui ADR.
2. **No direct DB access** untuk operasional — semua melalui API/Repository.
3. **Backup terenkripsi** — data tenant tidak boleh dibaca tanpa kunci tenant.
4. **Audit akses data sensitif** — setiap read pada L3/L4 dicatat.
5. **Data retention policy** — tenant dapat mengatur (dalam batas regulasi minimum).

---

## 5. Verifikasi

Konsisten dengan `docs/specification/BusinessRules.md` (Sprint 5.1), `docs/domain-validation/ArchitectureAdjustment.md` (6.1A), `SECURITY.md` (project root).
