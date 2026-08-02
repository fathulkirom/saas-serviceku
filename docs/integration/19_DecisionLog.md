# 19 — Decision Log (Sprint 6.2B)

> **Sprint 6.2B · Blueprint Only.** Semua keputusan arsitektur integrasi & provider.

---

## DEC-P01 — Provider Pattern sebagai standar tunggal
- **Keputusan:** Semua integrasi eksternal WAJIB menggunakan Provider Pattern (interface di domain, implementasi di infrastructure).
- **Alasan:** Vendor independence; swap provider tanpa ubah kode.
- **Status:** FINAL.

## DEC-P02 — Default provider = native/local/gratis
- **Keputusan:** Setiap provider type memiliki default (Local Storage, Browser Print, Kamera HP, WhatsApp Web, Cash, Browser Notification).
- **Alasan:** Simple by Default — toko kecil bisa langsung pakai tanpa konfigurasi.
- **Status:** FINAL.

## DEC-P03 — WhatsApp Web sebagai default messaging
- **Keputusan:** WhatsApp Web (QR pairing) = default. Cloud API / Evolution API = upgrade.
- **Alasan:** Gratis, cukup untuk toko kecil; upgrade saat volume tinggi. Risiko: bisa diblokir Meta → fallback Email.
- **Trade-off:** Tidak resmi, tapi praktis. Diterima.
- **Status:** FINAL.

## DEC-P04 — Provider Registry sebagai data
- **Keputusan:** Daftar provider disimpan sebagai data (config / DB), bukan hardcode.
- **Alasan:** Provider baru tinggal daftar; tidak deploy ulang.
- **Status:** FINAL.

## DEC-P05 — Fallback chain wajib
- **Keputusan:** Setiap provider yang diaktifkan tenant harus punya primary + fallback. Default provider = fallback terakhir.
- **Alasan:** No Single Point Of Failure; jika provider mati, toko tetap operasional.
- **Status:** FINAL.

## DEC-P06 — Credential terenkripsi per tenant
- **Keputusan:** API key, token, password provider disimpan di tenant DB, terenkripsi AES-256. Tidak di central DB.
- **Alasan:** Tenant data isolation; kebocoran satu tenant tidak membocorkan tenant lain.
- **Status:** FINAL.

## DEC-P07 — AI sebagai provider opsional
- **Keputusan:** Semua AI (OpenAI, Gemini, DeepSeek, Local LLM) = provider; tenant pilih. AI mati tidak mengganggu operasional.
- **Alasan:** Vendor independence; biaya AI ditanggung tenant; tanpa AI toko tetap berfungsi.
- **Status:** FINAL.

## DEC-P08 — Companion Mode (Desktop + HP), bukan native app
- **Keputusan:** HP berfungsi sebagai companion device via browser/PWA, bukan aplikasi native terpisah.
- **Alasan:** Tidak perlu develop & maintain native app; HP tetap bisa jadi perangkat utama untuk teknisi rumahan.
- **Status:** FINAL.

## DEC-P09 — Marketplace = Future (P2)
- **Keputusan:** Semua marketplace provider = P2; arsitektur siap (interface + Request channel = marketplace).
- **Alasan:** Bukan prioritas sekarang; tidak menghalangi 6.2B.
- **Status:** DEFERRED.

## DEC-P10 — Thermal printer sebagai upgrade
- **Keputusan:** Browser Print = default; Thermal (USB/Bluetooth/Network) = upgrade.
- **Alasan:** Browser print cukup untuk teknisi rumahan; thermal untuk toko profesional.
- **Status:** FINAL.

## DEC-P11 — OCR untuk IMEI = Future
- **Keputusan:** OCR scan IMEI dari stiker = future (bergantung AI Provider). Saat ini: ketik manual + scan barcode.
- **Status:** DEFERRED.

## DEC-P12 — Provider health check otomatis
- **Keputusan:** Setiap provider punya `healthCheck()`; degraded auto-switch; error → notifikasi Owner.
- **Status:** TARGET (implementasi 6.2+).

---

## Ringkasan

| Status | Jumlah |
|---|---|
| FINAL | 9 |
| TARGET | 1 |
| DEFERRED | 2 |
