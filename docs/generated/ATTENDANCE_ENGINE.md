# Attendance Engine

> Clock-in/out with GPS, photo, QR code, shift management, and correction workflow.

---

## 🕐 Attendance Methods

| Method | Description | Status |
|--------|-------------|--------|
| GPS | Location-based clock in/out | ✅ Active |
| Photo | Selfie verification | ✅ Active |
| QR Code | Scan QR at location | 🔧 Ready |
| NFC | Tap NFC card/tag | 🔮 Future |
| Fingerprint | Biometric scanner | 🔮 Future |

---

## 📅 Shift Types

| Shift | Hours | Example |
|-------|-------|---------|
| Morning | 06:00–14:00 | Pagi |
| Afternoon | 14:00–22:00 | Siang |
| Night | 22:00–06:00 | Malam |
| Flexible | 8 hours anytime | Remote |
| Split | 2 blocks | 08:00–12:00 + 16:00–20:00 |
| Rotating | Weekly rotation | A→B→C |

---

## 📊 Attendance Status

| Status | Description |
|--------|-------------|
| Present | On time |
| Late | Clock in after shift start |
| Absent | No clock in |
| On Leave | Approved leave |
| Holiday | Public holiday / day off |
| Half Day | Half-day attendance |

---

## 🔧 Attendance Correction

- Employee requests correction with reason
- Manager/HRD approves
- Original + corrected record preserved
- Audit trail logged

---

## 📈 Overtime

| Type | Rate |
|------|------|
| Weekday | 1.5x first hour, 2x subsequent |
| Weekend | 2x all hours |
| Holiday | 3x all hours |

---

## 🔗 Integration

| Integration | Description |
|-------------|-------------|
| Payroll | Overtime hours → overtime pay |
| Performance | Attendance score as KPI component |
| Automation | Late arrival → alert |

---

*Attendance Engine — Sprint 21.0*
