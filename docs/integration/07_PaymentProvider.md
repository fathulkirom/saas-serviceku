# 07 — Payment Provider

> **Sprint 6.2B · Blueprint Only.** Provider pembayaran — cash, transfer, QRIS, gateway.

---

## 1. Daftar Provider

| Provider | Jenis | Biaya | Target tenant |
|---|---|---|---|
| **Cash** | Tunai di toko | Gratis | **Default** — semua |
| **Transfer Bank** | Manual transfer (konfirmasi manual) | Gratis (biaya bank) | Toko kecil |
| **QRIS** | QR Code standar Indonesia | 0.3%–0.7% | Toko kecil, berkembang |
| **Midtrans** | Payment gateway (QRIS, VA, CC, GoPay, OVO, dll.) | 1.5%–3% | Toko berkembang, multi-cabang |
| **Xendit** | Payment gateway alternatif | 1.5%–3% | Enterprise, multi-cabang |
| **Tripay** | Payment gateway (lebih murah) | 1%–2% | Toko kecil–berkembang |
| **Future** | — | — | — |

---

## 2. Strategi per Tenant

| Tenant | Provider | Alasan |
|---|---|---|
| **Teknisi rumahan** | Cash + Transfer | Tanpa alat, tanpa biaya |
| **Toko kecil** | Cash + QRIS | Cukup print QR; biaya rendah |
| **Toko berkembang** | Midtrans (QRIS + VA + e-wallet) | Banyak opsi bayar pelanggan |
| **Multi-cabang / Enterprise** | Midtrans / Xendit + Cash | Multi-channel, rekonsiliasi |

---

## 3. Flow Pembayaran (Blueprint)

```
SalesOrder dibuat → PaymentInterface::createTransaction(order)
    → provider = Midtrans → buat Snap token / redirect URL
    → pelanggan bayar → Midtrans webhook → PaymentInterface::handleCallback()
    → status = success → SalesOrder final → stok berkurang
```

---

## 4. Hybrid Payment

Satu Sales Order bisa punya **multi-payment**:
- DP via QRIS + Pelunasan Cash.
- Split payment antar metode.

---

## 5. Aturan

1. **Cash = default** — selalu tersedia, tanpa konfigurasi.
2. **Gateway = opsi** — tenant daftar sendiri (Midtrans/Xendit), isi API key di Settings.
3. **Webhook route** — satu endpoint untuk semua gateway (`/api/payment/callback/{provider}`).
4. **Idempotent** — callback diproses sekali; tidak double-konfirmasi.
5. **Credentials terenkripsi** — API key gateway disimpan terenkripsi.

---

## 6. Verifikasi

Konsisten dengan existing Midtrans integration (source), prinsip **Vendor Independence** (tidak terkunci Midtrans), **Simple by Default** (cash default).
