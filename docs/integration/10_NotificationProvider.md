# 10 — Notification Provider

> **Sprint 6.2B · Blueprint Only.** Provider notifikasi ke user internal (CS, Teknisi, Owner) dan eksternal (Customer).

---

## 1. Daftar Provider

| Provider | Target | Kelebihan | Kekurangan |
|---|---|---|---|
| **Browser Notification** | Internal (CS, Teknisi, Owner) | Real-time, gratis, nol setup | Hanya saat browser terbuka |
| **Email** | Semua | Universal, gratis/murah, ada jejak | Tidak real-time |
| **SMS** | Customer (tanpa WA) | Semua HP bisa, reliable | Berbayar (~Rp 300-500/SMS) |
| **WhatsApp** | Customer | Paling banyak dipakai di Indonesia | Perlu provider messaging (lihat 04) |
| **Push Notification** | Mobile App (future) | Real-time, rich content | Butuh mobile app |

---

## 2. Channel per Event

| Event | Internal (CS/Teknisi) | Eksternal (Customer) |
|---|---|---|
| Request dibuat | Browser + Email (opsional) | WA / SMS |
| Teknisi assigned | Browser + WA | WA / SMS |
| Status berubah | Browser | WA / SMS |
| Servis selesai | Browser | WA / SMS + Email (invoice) |
| Garansi habis | Browser | WA / SMS (reminder) |
| Pembayaran sukses | Browser | WA / Email (nota) |

---

## 3. Aturan

1. **Browser Notification = default internal** — gratis, real-time.
2. **Email = default eksternal** — universal, gratis (Brevo existing).
3. **WhatsApp = upgrade** — tenant perlu setup messaging provider.
4. **SMS = fallback** — untuk customer tanpa WA.
5. **Customer dapat memilih channel** — WA atau Email (future: Customer Portal).

---

## 4. Verifikasi

Konsisten dengan `04_MessagingProvider.md`, prinsip **Simple by Default** (Browser + Email default).
