# Tax Engine

> Tax management with PPN, PPh, and future e-Faktur integration.

---

## 🧾 Tax Types

| Tax | Description | Rate |
|-----|-------------|------|
| PPN (VAT) | Pajak Pertambahan Nilai | 11% (2025+) |
| PPh 21 | Pajak Penghasilan Karyawan | Progressive |
| PPh 23 | Pajak Penghasilan Jasa/Sewa | 2% / 15% |
| PPh 25 | Angsuran PPh Badan | Calculated |
| PPh 29 | PPh Kurang Bayar | Year-end |

---

## ⚙️ Tax Configuration

| Setting | Options |
|---------|---------|
| Tax Inclusive | Price includes tax |
| Tax Exclusive | Tax added to price |
| Tax Group | PPn, PPh 23, etc. |
| Tax Code | Mapping to COA tax accounts |
| Filing Period | Monthly / Yearly |

---

## 📊 Tax Reports

| Report | Content |
|--------|---------|
| Tax Summary | All tax types by period |
| PPN Report | Output VAT - Input VAT |
| PPh 23 Report | Withholding tax summary |
| Tax Filing Status | Pending / Filed / Paid |

---

## 🔗 Integration Points

| Transaction | Tax Impact |
|-------------|------------|
| Sales Invoice | Output PPN |
| Purchase Invoice | Input PPN |
| Service Invoice | Output PPN |
| Employee Salary | PPh 21 |
| Vendor Payment | PPh 23 withholding |

---

## 🔮 Future: e-Faktur Integration

| Feature | Status |
|---------|--------|
| e-Faktur XML Export | Target |
| DJP API Integration | Target |
| Auto Numbering (NSFP) | Target |
| e-Bupot Integration | Target |

---

*Tax Engine — Sprint 20.0*
