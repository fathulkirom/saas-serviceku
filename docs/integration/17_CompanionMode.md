# 17 — Companion Mode

> **Sprint 6.2B · Blueprint Only.** Arsitektur Desktop + HP sebagai perangkat pendamping — bukan aplikasi mobile terpisah.

---

## 1. Filosofi Companion Mode

```
Desktop = Pusat Kerja (Input, Monitoring, Dashboard, Transaksi)
HP      = Perangkat Pendukung (Kamera, Barcode, QR, IMEI, Tanda tangan, Upload)
```

Companion Mode **bukan** aplikasi mobile native. HP digunakan via **browser** (PWA ringan) untuk mengakses fitur hardware yang tidak ada di desktop.

---

## 2. Pembagian Tugas Desktop ↔ HP

| Fungsi | Desktop | HP (Companion) |
|---|---|---|
| **Input data** (form, tabel) | ✅ Utama | ❌ (terlalu kecil) |
| **Dashboard & Monitoring** | ✅ Utama | 👁 View-only |
| **Transaksi POS** | ✅ Utama | ❌ |
| **Laporan** | ✅ Utama | ❌ |
| **Kamera — foto servis** | ❌ (biasanya tidak ada) | ✅ Utama |
| **Scan barcode / QR** | ❌ | ✅ Utama (kamera) |
| **Scan IMEI** (stiker) | ❌ | ✅ Utama (kamera) |
| **Scan IMEI** (`*#06#`) | ✅ Manual input | ✅ Foto layar → OCR (future) |
| **Tanda tangan digital** | ❌ | ✅ Touch screen |
| **Upload file / foto** | ✅ Drag & drop | ✅ Galeri / Kamera |
| **WhatsApp pairing** | ✅ (QR tampil di desktop) | ✅ (Scan QR) |

---

## 3. Teknis Companion Mode

```
Desktop browser → buka halaman "Companion"
    → tampilkan QR Code / short URL
HP browser → scan QR / buka URL
    → HP terhubung ke sesi yang sama
    → HP menampilkan UI ringan (kamera, scan, tanda tangan)
    → Data dari HP langsung muncul di Desktop (WebSocket / polling)
```

- **WebSocket** (Reverb existing) — real-time komunikasi Desktop↔HP.
- **PWA** — HP bisa "install" shortcut ke homescreen untuk akses cepat.
- **Session pairing** — QR Code unik per sesi, expire 5 menit.

---

## 4. Alternatif: HP Sebagai Perangkat Utama

Untuk **teknisi rumahan** yang tidak punya desktop/laptop:
- HP berfungsi sebagai **full interface** (input, monitoring, transaksi ringan).
- Responsive UI — semua halaman ServiceKU mobile-friendly.
- Companion Mode tidak diperlukan (kamera & input di device yang sama).

---

## 5. Aturan

1. **Companion Mode = opsional** — untuk toko yang punya desktop + HP.
2. **Tidak perlu install aplikasi** — browser HP sudah cukup (PWA).
3. **Semua fitur tetap bisa diakses dari HP saja** — untuk teknisi rumahan.
4. **Scanning provider** (`06_ScanningProvider.md`) = jembatan HP ke hardware scanning.

---

## 6. Verifikasi

Konsisten dengan prinsip **Simple by Default** (HP bisa jadi perangkat utama untuk teknisi rumahan), **Practical over Perfect** (tidak perlu native app), `06_ScanningProvider.md`.
