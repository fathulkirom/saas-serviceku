# Master Data Guide — ServiceKU v1.0

> Complete master data catalog — managed via UI, NOT via seeder.

---

## ⚠️ Principle

**Seeder hanya untuk development/demo. Operasional menggunakan UI.**

---

## 📊 Master Data Catalog

### Device (PRIORITY: CRITICAL)
| Data | Status UI | Keterangan |
|------|:---------:|------------|
| Merk HP/Laptop | ❌ Belum ada UI | Harus bisa CRUD via Settings > Master Data |
| Kategori Perangkat | ❌ Belum ada UI | Smartphone, Tablet, Laptop, Smartwatch, AirPods |
| Model / Tipe | ❌ Belum ada UI | Perlu relasi ke Merk |
| Warna | ❌ Belum ada UI | List warna standar + custom |

### Service (PRIORITY: CRITICAL)
| Data | Status UI | Keterangan |
|------|:---------:|------------|
| Jenis Kerusakan | ❌ Belum ada UI | LCD, Baterai, Charging, Water Damage, dll |
| Jenis Servis | ❌ Belum ada UI | Ganti LCD, Ganti Baterai, Reball, dll |
| Jalur Kedatangan | ✅ Sudah ada | Walk-in, WhatsApp, Marketplace, dll |

### Parts & Supplier
| Data | Status UI | Keterangan |
|------|:---------:|------------|
| Sparepart | ✅ Product module | Sudah ada UI lengkap |
| Supplier | ✅ Supplier module | Sudah ada UI lengkap |

### Finance
| Data | Status UI | Keterangan |
|------|:---------:|------------|
| Pajak (PPN) | ❌ Belum ada UI | Default 11%, bisa diubah |
| Bank / Rekening | ❌ Belum ada UI | Untuk transfer & QRIS |
| Kas | ✅ Sudah ada | Cash register shift |

### Output / Printing
| Data | Status UI | Keterangan |
|------|:---------:|------------|
| Template WhatsApp | ✅ Sudah ada | CustomerMessageTemplate |
| Template Nota | ❌ Belum ada UI | Ukuran, logo, info toko |
| Template Garansi | ❌ Belum ada UI | Syarat & ketentuan |
| Template Label | ❌ Belum ada UI | Label servis untuk unit |

### Warranty
| Data | Status UI | Keterangan |
|------|:---------:|------------|
| Kebijakan Garansi | ❌ Belum ada UI | Durasi jasa (30d), sparepart (90d) |
| Metode Pembayaran | ✅ Sudah ada | Tunai, Transfer, QRIS, E-Wallet, Debit |

---

## 📋 Implementation Priority

| Priority | Items | Target |
|:--------:|-------|--------|
| 🔴 Critical | Merk, Kategori, Jenis Kerusakan, Jenis Servis, Pajak, Warranty | Harus ada sebelum go-live |
| 🟠 High | Bank, Template Nota, Template Garansi | Sebelum transaksi pertama |
| 🟡 Medium | Model, Warna, Printer, Template Label | Bisa menyusul |
| 🟢 Low | Storage, Processor | Nice to have |

---

*Master Data Guide — ServiceKU v1.0*
