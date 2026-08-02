# 07 — Attachment Strategy

> **Sprint 6.2A · Blueprint Only.** Strategi penyimpanan lampiran (foto, PDF, invoice, dokumen, voice note) — jenis file, penyimpanan, batasan.

---

## 1. Jenis Attachment per Domain

| Domain | Jenis | Format | Maks per item |
|---|---|---|---|
| **Request** | Foto device kondisi awal | JPG/PNG/WebP | 5 foto |
| **Service Order** | Foto progress, checklist visual | JPG/PNG, MP4 (video pendek) | 10 foto, 2 video |
| **Sales Order** | Invoice/Nota PDF | PDF | 1 (auto-generate) |
| **Purchase Order** | Invoice supplier (scan/foto) | JPG/PNG/PDF | 3 |
| **Warranty / Claim** | Foto bukti kerusakan | JPG/PNG | 5 |
| **Customer** | Foto KTP/identitas (jika perlu klaim) | JPG/PNG | 1 |
| **Device** | Foto unit, stiker IMEI | JPG/PNG | 3 |
| **Deposit / Expense** | Bukti setoran/pengeluaran | JPG/PNG/PDF | 1 |
| **Product** | Foto produk | JPG/PNG/WebP | 5 |
| **Document (SOP/KB)** | PDF, DOCX | PDF | 1 |
| **Voice Note** (future) | Pesan suara customer/teknisi | MP3/OGG | 1 per request |

---

## 2. Strategi Penyimpanan

| Aspek | Ketentuan |
|---|---|
| **Lokasi** | Storage tenant (S3-compatible / local disk untuk dev). Path: `{tenant_id}/{domain}/{id}/` |
| **Nama file** | `{domain}_{id}_{timestamp}.{ext}` — unik, tidak konflik. |
| **Ukuran maks** | Foto: 5 MB, Video: 30 MB (10 detik), PDF: 2 MB, Voice: 3 MB. Dikonfigurasi per plan. |
| **Kuota** | Per plan (Subscription Engine): Trial=100 MB, Basic=500 MB, Pro=2 GB, Enterprise=10 GB. |
| **Thumbnail** | Auto-generate untuk foto (200px) — tampil di galeri/daftar. |
| **Compress** | Foto dikompresi ke WebP untuk storage (kecuali original opsional). |
| **Cleanup** | Attachment Request dihapus bersama Request (soft-delete cascade). Attachment Service/Sales = permanen. |

---

## 3. Aturan Attachment

1. **Semua attachment wajib terikat** ke entity tenant — tidak boleh orphan.
2. **File di-scan virus** (opsional, tergantung infrastruktur).
3. **Customer PII dalam foto** (wajah, KTP) diperlakukan sebagai L3 PII — akses dibatasi.
4. **Voice note** (future) — transkripsi auto (AI) untuk pencarian.
5. Attachment tidak di-backup terpisah — mengikuti backup tenant.

---

## 4. Verifikasi

Konsisten dengan `docs/architecture-engine/Scalability.md` (Sprint 5.2), `docs/architecture-engine/SubscriptionEngine.md` (5.2 — kuota storage).
