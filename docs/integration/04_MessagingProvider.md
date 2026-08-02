# 04 — Messaging Provider

> **Sprint 6.2B · Blueprint Only.** Provider komunikasi dua arah dengan customer — WhatsApp, Email, SMS.

---

## 1. Daftar Provider

| Provider | Jenis | Biaya | Kompleksitas | Target tenant | Status |
|---|---|---|---|---|---|
| **WhatsApp Web** | WA tidak resmi (browser-based) | Gratis | ⭐ (pairing QR) | Toko kecil, teknisi rumahan | **Default** |
| **WhatsApp Cloud API** | WA resmi (Meta) | Per pesan (~Rp 500-1000) | ⭐⭐ | Toko berkembang | **Target** |
| **Evolution API** | WA Gateway self-hosted | Server + WA number | ⭐⭐⭐ | Enterprise, multi-cabang | **Future** |
| **WPPConnect** | WA Web library | Gratis | ⭐⭐ | Developer / technical | **Alternative** |
| **Email** | SMTP (Brevo, SES, Mailgun) | Gratis–murah | ⭐ | Semua | **Existing** |
| **SMS** | Gateway SMS | Per SMS (~Rp 300-500) | ⭐⭐ | Enterprise (fallback) | **Future** |

---

## 2. Strategi per Target Tenant

| Tenant | Provider messaging | Alasan |
|---|---|---|
| **Teknisi rumahan** | WhatsApp Web | Gratis, cukup untuk konfirmasi 1-5 pelanggan/hari |
| **Toko kecil** | WhatsApp Web + Email | Gratis, sudah memadai |
| **Toko berkembang** | WhatsApp Cloud API + Email | Resmi, reliable, template message |
| **Multi-cabang / Enterprise** | Evolution API + Cloud API + SMS | Multi-agent, self-hosted, fallback SMS |

---

## 3. Fitur per Provider

| Fitur | WA Web | WA Cloud API | Evolution API | Email |
|---|---|---|---|---|
| Kirim pesan teks | ✅ | ✅ | ✅ | ✅ |
| Kirim gambar/file | ✅ | ✅ | ✅ | ✅ (attachment) |
| Template message | ❌ | ✅ | ✅ | ✅ (HTML) |
| Multi-device/agent | ❌ | ✅ | ✅ | ✅ |
| Auto-reply / Bot | ❌ (manual) | ✅ (webhook) | ✅ (webhook) | ❌ |
| Pairing QR | ✅ | ❌ (business setup) | ✅ | ❌ |
| Biaya | Gratis | Per pesan | Server + nomor WA | Gratis–murah |

---

## 4. Kapan Pakai WhatsApp Web

| Kelebihan | Kekurangan |
|---|---|
| ✅ Gratis, tidak perlu approval Meta | ❌ Tidak resmi — bisa diblokir Meta |
| ✅ Cukup scan QR — seperti WhatsApp biasa | ❌ Tidak bisa multi-agent (1 nomor = 1 browser) |
| ✅ Cocok untuk toko kecil (1-10 pesan/hari) | ❌ Tidak ada webhook / auto-reply |
| ✅ Nol setup — tenant tinggal scan | ❌ Browser harus tetap terbuka |

> **Keputusan:** WhatsApp Web adalah **default** — praktis untuk toko kecil. Tenant upgrade ke Cloud API saat butuh reliability & multi-agent.

---

## 5. Fallback Strategy

```
Pesan notifikasi:
  WhatsApp → gagal? → Email → gagal? → SMS (enterprise) → gagal? → log + retry.

Pesan masuk (customer):
  WhatsApp → diterima → buat Request otomatis.
```

---

## 6. Verifikasi

Konsisten dengan `docs/request-engine/04_RequestChannel.md` (Sprint 6.1D — WhatsApp sebagai channel), prinsip **Simple by Default** (WA Web default, gratis), **Progressive Complexity** (Cloud API / Evolution API = upgrade).
