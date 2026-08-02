# 05 — Printing Provider

> **Sprint 6.2B · Blueprint Only.** Provider pencetakan — nota, invoice, label barcode, laporan.

---

## 1. Daftar Provider

| Provider | Cara kerja | Kelebihan | Kekurangan | Target |
|---|---|---|---|---|
| **Browser Print** | `window.print()`, HTML/CSS | Nol setup, semua device, gratis | Tidak bisa thermal, margin browser | **Default** |
| **Thermal USB** | ESC/POS via USB/Serial | Cepat, kertas kecil, profesional | Butuh driver, khusus POS | Toko, multi-cabang |
| **Thermal Bluetooth** | ESC/POS via Bluetooth | Nirkabel, mobile-friendly | Pairing, baterai | Toko kecil |
| **Network Printer** | IPP / Socket (LAN/WiFi) | Multi-user, shared | Konfigurasi jaringan | Multi-cabang |
| **Cloud Print** | Google Cloud Print (discontinued) / vendor-specific | Remote printing | Ketergantungan internet | Future (alternatif) |

---

## 2. Target per Tenant

| Tenant | Printer | Alasan |
|---|---|---|
| **Teknisi rumahan** | Browser Print | Tidak punya printer thermal; cetak dari HP/laptop |
| **Toko kecil** | Thermal Bluetooth | Murah, mobile, cukup untuk nota servis |
| **Toko berkembang** | Thermal USB + Browser | POS thermal + laporan browser |
| **Multi-cabang** | Network Thermal | Shared printer, multi-kasir |
| **Enterprise** | Network + Cloud | Multi-lokasi, manajemen terpusat |

---

## 3. Format Cetakan

| Dokumen | Format | Provider |
|---|---|---|
| Nota servis (kecil) | ESC/POS (thermal, 58/80mm) | Thermal USB/BT/Network |
| Nota penjualan | ESC/POS + A4 fallback | Thermal → Browser |
| Invoice PDF | A4 PDF | Browser Print / Export PDF |
| Label barcode | ESC/POS / ZPL | Thermal (opsional) |
| Laporan | A4 HTML/PDF | Browser Print |

---

## 4. Aturan

1. **Browser Print = default** — selalu tersedia, tidak perlu setup.
2. **Thermal = upgrade** — tenant mengaktifkan di Settings.
3. **Preview sebelum cetak** — wajib di UI (kecuali auto-print POS).
4. **PrintInterface::print()** — menerima `document` + `printer_id`; provider yang menentukan format.

---

## 5. Verifikasi

Konsisten dengan prinsip **Simple by Default** (browser print), **Progressive Complexity** (thermal untuk toko berkembang), **Vendor Independence** (interface tidak bergantung merek printer).
