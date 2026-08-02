# 06 — Scanning Provider

> **Sprint 6.2B · Blueprint Only.** Provider pemindaian — barcode, QR, IMEI, dan OCR masa depan.

---

## 1. Daftar Provider

| Provider | Teknologi | Kelebihan | Kekurangan | Target |
|---|---|---|---|---|
| **Kamera HP** | Kamera + JS (BarcodeDetector API / library) | Nol alat tambahan, semua HP bisa | Pencahayaan, sudut | **Default** |
| **USB Barcode Scanner** | HID (keyboard wedge) | Cepat, akurat, profesional | Butuh alat, kabel | Toko, gudang |
| **Bluetooth Scanner** | BLE / SPP | Nirkabel, mobile | Pairing, baterai, harga | Gudang, multi-cabang |
| **QR Scanner** | Kamera HP (sama dengan barcode) | 2D, bisa encode URL/data | — | **Default** |
| **IMEI Scanner** | Kamera HP + OCR (future) / keyboard | Bisa ketik manual, scan dari *#06#, scan stiker | OCR kompleks | Semua |
| **OCR (Future)** | Kamera + AI/ML | Scan teks dari stiker, KTP, nota | Kompleks, butuh AI | Enterprise |

---

## 2. Metode Input IMEI

| Metode | Keandalan | Kompleksitas |
|---|---|---|
| **Ketik manual** (`*#06#`) | 100% (kalau benar) | ⭐ |
| **Scan stiker IMEI** (kamera OCR, future) | 80% (tergantung kualitas stiker) | ⭐⭐⭐ |
| **Scan barcode IMEI** (jika tersedia) | 100% | ⭐ |
| **Auto-detect dari device** (future: ADB/iOS) | 90% | ⭐⭐⭐⭐ |

> **Default:** ketik manual + scan barcode. OCR untuk IMEI dari stiker = future.

---

## 3. Flow Scanning (Blueprint)

```
User klik "Scan" → ScanningInterface::scan(type)
    → provider.resolve() → HP Camera terbuka (browser)
    → deteksi barcode/QR/IMEI → return hasil
    → isi field otomatis (device IMEI, product SKU, dll.)
```

---

## 4. Aturan

1. **Kamera HP = default** — semua device modern punya kamera + browser API.
2. **USB/Bluetooth scanner = upgrade** — untuk gudang/toko volume tinggi.
3. **IMEI = wajib bisa input manual** — fallback jika scan gagal.
4. **ScanningInterface::scan(type)** — type menentukan format hasil (barcode→SKU, IMEI→validasi checksum Luhn).
5. **OCR = future** — bergantung AI Provider.

---

## 5. Verifikasi

Konsisten dengan `docs/data-architecture/19_DataValidation.md` (validasi IMEI Luhn), prinsip **Simple by Default** (kamera HP), **Progressive Complexity** (scanner hardware untuk gudang).
