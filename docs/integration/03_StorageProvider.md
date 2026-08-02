# 03 — Storage Provider

> **Sprint 6.2B · Blueprint Only.** Provider penyimpanan file — foto servis, invoice PDF, dokumen SOP, backup.

---

## 1. Daftar Provider

| Provider | Kelebihan | Kekurangan | Target tenant | Kompleksitas |
|---|---|---|---|---|
| **Local Storage** | Gratis, nol konfigurasi, cepat | Tidak redundant, hilang jika server rusak | Semua (default fallback) | ⭐ |
| **Amazon S3** | Industri standar, scalable, CDN | Berbayar, perlu konfigurasi IAM | Enterprise, multi-cabang | ⭐⭐⭐ |
| **Cloudflare R2** | Murah, no egress fee, S3-compatible | Ekosistem lebih kecil dari S3 | Enterprise, startup | ⭐⭐ |
| **Backblaze B2** | Sangat murah, S3-compatible | Konfigurasi butuh effort | Enterprise (hemat) | ⭐⭐ |
| **Google Drive** | Gratis (15 GB), familiar, sharing mudah | Rate limit, bukan untuk server | Toko kecil, teknisi rumahan | ⭐ |
| **OneDrive** | Mirip Google Drive, integrasi Microsoft | Rate limit, konfigurasi OAuth | Enterprise (Microsoft stack) | ⭐⭐ |
| **Dropbox** | Sederhana, sharing, brand dikenal | Mahal untuk bisnis, rate limit | Toko kecil (jarang) | ⭐ |
| **MinIO** | Self-hosted, S3-compatible, open source | Butuh server sendiri, maintenance | Enterprise on-premise | ⭐⭐⭐ |
| **NAS** | Milik tenant sendiri, kontrol penuh | Butuh infrastruktur, akses remote | Enterprise, multi-cabang | ⭐⭐⭐ |
| **Future** | — | — | — | — |

---

## 2. Strategi per Target Tenant

| Tenant | Provider default | Alasan |
|---|---|---|
| **Teknisi rumahan** | Local + Google Drive (opsional) | Nol biaya, cukup untuk 1-5 servis/hari |
| **Toko kecil** | Local + Google Drive | Gratis, cukup untuk foto servis |
| **Toko berkembang** | R2 / B2 | Murah, scalable, S3 API |
| **Multi-cabang** | S3 / R2 + NAS (cabang sendiri) | Performa, redundancy |
| **Enterprise** | S3 (primary) + NAS (arsip/backup) | Compliance, kontrol penuh |

---

## 3. Metadata File

Setiap file menyimpan metadata:

```json
{
  "tenant_id": "...",
  "domain": "service_order",
  "entity_id": "...",
  "original_name": "foto_servis.jpg",
  "mime_type": "image/jpeg",
  "size_bytes": 245000,
  "uploaded_by": "user_id",
  "uploaded_at": "2026-08-02T12:00:00Z",
  "checksum": "sha256:..."
}
```

---

## 4. Kuota per Plan

| Plan | Kuota storage | Dari Sprint 6.2A `07_AttachmentStrategy.md` |
|---|---|---|
| Trial | 100 MB | ⚠️ Perlu verifikasi dengan plan saat ini |
| Basic | 500 MB | — |
| Pro | 2 GB | — |
| Enterprise | 10 GB | Dapat dinegosiasi |

---

## 5. Sinkronisasi (Future)

- **Google Drive / OneDrive** = sinkronisasi dua arah (future: provider poll changes).
- **NAS / MinIO** = mirror dari cloud (hybrid strategy).
- Saat ini: **upload only** — aplikasi upload; tidak perlu sinkron balik.

---

## 6. Aturan

1. **Default = Local Storage** — selalu tersedia, tidak perlu konfigurasi.
2. **Cloud provider = opsi** — tenant upgrade ke S3/R2 saat butuh.
3. **Fallback chain**: Primary → Secondary → Local.
4. **Pindah provider** — migrate file antar provider (future tool).
5. **URL akses** — tidak hardcode path lokal di UI; selalu lewat `StorageInterface::url()`.

---

## 7. Verifikasi

Konsisten dengan `docs/data-architecture/07_AttachmentStrategy.md` (Sprint 6.2A), `docs/architecture-engine/SubscriptionEngine.md` (Sprint 5.2 — kuota).
