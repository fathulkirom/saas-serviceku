# 11 — Backup Provider

> **Sprint 6.2B · Blueprint Only.** Provider backup database tenant.

---

## 1. Daftar Provider

| Provider | Kelebihan | Kekurangan | Target |
|---|---|---|---|
| **Local** | Gratis, cepat restore | Tidak off-site; server rusak = backup hilang | **Default** |
| **Amazon S3** | Off-site, durable, lifecycle policy | Berbayar | Enterprise |
| **Cloudflare R2** | Murah, no egress fee | — | Enterprise (hemat) |
| **Google Drive** | Gratis (15 GB), familiar | Rate limit | Toko kecil |
| **NAS** | Milik tenant, kontrol penuh | Butuh infrastruktur | Enterprise on-premise |

---

## 2. Strategi Backup

| Aspek | Ketentuan |
|---|---|
| **Frekuensi** | Harian (otomatis cron) |
| **Retensi** | 7 hari (harian) + 4 minggu (mingguan) + 12 bulan (bulanan) |
| **Enkripsi** | Backup dienkripsi sebelum upload (AES-256) |
| **Verifikasi** | Uji restore berkala (1x/bulan) |
| **Notifikasi** | Notifikasi ke Owner jika backup gagal |

---

## 3. Aturan

1. **Local backup = default** — selalu berjalan; fallback jika cloud gagal.
2. **Cloud backup = opsi** — tenant pilih S3/R2/GDrive.
3. **Backup terenkripsi** — kunci enkripsi per tenant.
4. **Restore** — Super Admin atau Owner (self-service restore = future).
5. **Backup gagal** = notifikasi + retry.

---

## 4. Verifikasi

Konsisten dengan `CHECKLIST-OPERASIONAL-HARIAN.md` (ops backup), `docs/architecture-engine/Scalability.md` (Sprint 5.2).
