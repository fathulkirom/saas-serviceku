# Putaway Engine

> Auto/manual putaway with location suggestion, zone strategy, barcode/QR.

---

## 📥 Putaway Flow

```
Goods Received → Inspection Passed
  → Auto Putaway (system suggests location)
  → Zone Strategy (ABC, FIFO, FEFO)
  → Capacity Check (bin capacity validation)
  → Barcode/QR Scan (confirm location)
  → Putaway Completed → Inventory Updated
```

---

## 📍 Zone Strategies

| Strategy | Description |
|----------|-------------|
| ABC | A items → front, B → middle, C → back |
| FIFO | First-In-First-Out rotation |
| FEFO | First-Expired-First-Out rotation |

---

*Putaway Engine — Sprint 26.0*
