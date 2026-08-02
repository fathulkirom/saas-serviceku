# 15 — Provider Configuration

> **Sprint 6.2B · Blueprint Only.** Bagaimana tenant memilih dan mengonfigurasi provider — tanpa menyentuh kode.

---

## 1. Alur Konfigurasi

```
Owner buka Settings → tab "Integrasi"
    → pilih tipe provider (Storage / Messaging / Payment / ...)
    → lihat daftar provider yang tersedia (Registry)
    → klik "Aktifkan" pada provider yang dipilih
    → isi credential (API key, dll.)
    → "Tes Koneksi" → sukses → status Active
    → pilih provider sebagai Primary / Fallback
```

---

## 2. UI Settings (Konsep)

```
Settings > Integrasi

┌─ Storage ─────────────────────────────┐
│ Primary:  [Amazon S3        ▾]        │
│ Fallback: [Local Storage    ▾]        │
│ Status:   🟢 Active                   │
│ [Configure] [Test] [Deactivate]        │
└───────────────────────────────────────┘

┌─ Messaging ───────────────────────────┐
│ Primary:  [WhatsApp Web     ▾]        │
│ Fallback: [Email (Brevo)    ▾]        │
│ Status:   🟢 Active (QR paired)       │
│ [Show QR] [Disconnect]                │
└───────────────────────────────────────┘

┌─ Payment ─────────────────────────────┐
│ Primary:  [Cash              ▾]        │
│ Secondary: [Midtrans         ▾]        │
│ Status:   🟢 Active (Cash)            │
│           🟡 Degraded (Midtrans)       │
│ [Configure Midtrans]                   │
└───────────────────────────────────────┘
```

---

## 3. Konfigurasi per Plan

| Provider type | Trial | Basic | Pro | Enterprise |
|---|---|---|---|---|
| Storage — Cloud (S3/R2/GDrive) | ❌ | ❌ | ✅ | ✅ |
| Storage — NAS/MinIO | ❌ | ❌ | ❌ | ✅ |
| Messaging — WA Cloud API | ❌ | ❌ | ✅ | ✅ |
| Messaging — SMS | ❌ | ❌ | ❌ | ✅ |
| Payment — Gateway (Midtrans) | ❌ | ✅ | ✅ | ✅ |
| AI — Cloud | ❌ | ❌ | ❌ | ✅ |
| AI — Local LLM | ❌ | ❌ | ❌ | ✅ |
| Marketplace | ❌ | ❌ | ❌ | ✅ |

> **Default provider (selalu tersedia):** Local Storage, Browser Print, Kamera HP, WhatsApp Web, Cash, Browser Notification, Email (Brevo default).

---

## 4. Aturan

1. **Konfigurasi via UI** — tidak ada config file, tidak ada environment variable tenant-specific.
2. **Default provider = pre-installed** — tidak bisa di-uninstall.
3. **Cloud provider = tenant install sendiri** — isi credential, test, aktifkan.
4. **Primary + Fallback** — tenant pilih dua; jika primary gagal, auto-switch.
5. **Test button** — setiap provider punya `testConnection()` untuk validasi credential.

---

## 5. Verifikasi

Konsisten dengan prinsip **Configuration over Code**, **Simple by Default** (default provider siap pakai), **Progressive Complexity** (cloud provider = upgrade plan).
