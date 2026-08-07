# Service Operation Guide — ServiceKU v1.0

> Production operations guide for HP & Laptop service centers.

---

## 🎯 CS Reception — Target: < 1 Menit

### Rapid Intake Flow
```
1. Scan IMEI / Ketik IMEI → Auto-detect customer + device
2. Verifikasi data customer (auto-filled)
3. Pilih kelengkapan (SIM, Memory, Charger, Box, Nota) — checklist
4. Tulis keluhan customer (3 baris)
5. Ambil 2 foto (intake)
6. Klik Simpan → Selesai
```
**Target waktu: 45 detik.** Nomor servis auto-generated.

---

## 🔄 Service Workflow

| # | Status | Actor | Action |
|---|--------|-------|--------|
| 1 | Masuk | CS | Buat servis, checklist, foto |
| 2 | Assigned | CS/Manager | Assign teknisi |
| 3 | Diagnosa | Teknisi | Diagnosa + estimasi |
| 4 | Waiting Approval | Customer | Setuju / Tolak / Revisi |
| 5 | Dikerjakan | Teknisi | Perbaikan + sparepart |
| 6 | QC | QC | 22-point quality check |
| 7 | Ready Pickup | System | Notifikasi customer |
| 8 | Paid | Kasir | Pembayaran |
| 9 | Closed | System | Garansi aktif |

---

## 👥 Role Quick Reference

| Role | Bisa |
|------|------|
| **CS** | Buat servis, checklist, foto, assign teknisi, tracking |
| **Teknisi** | Diagnosa, perbaikan, sparepart, QC |
| **Kasir** | Pembayaran, invoice, serah terima |
| **Manager** | Semua di atas + approval, report |
| **Owner** | Full access + dashboard + settings |

---

## 📸 Photo Requirements

| Phase | Min | Kategori |
|-------|-----|----------|
| Intake | 2 | `intake` |
| Perbaikan | Opsional | `repair`, `disassembly` |
| QC | 2 | `completed`, `qc` |
| Serah Terima | 1 | `handover` |

---

## 💳 Payment Flow

```
Customer ready pickup → CS notifikasi
  → Customer datang
  → Kasir buka invoice
  → Pilih metode (Tunai / Transfer / QRIS)
  → Terima pembayaran
  → Cetak/Cetak invoice
  → Serah terima + foto + signature
  → Status: close → Garansi aktif
```

---

*Service Operation Guide — ServiceKU v1.0*
