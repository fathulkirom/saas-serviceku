# Picking Engine

> 5 picking strategies with wave management, barcode/QR confirmation.

---

## 📤 Picking Strategies

| Strategy | Use Case |
|----------|----------|
| Wave | High-volume, time-window based |
| Batch | Multiple orders, same SKU |
| Zone | Large warehouse, zoned staff |
| Cluster | Pick-to-cart, multi-order |
| Single | Low volume, simple orders |

---

## 📋 Picking Flow

```
Order Released → Wave Created
  → Picking Task Assigned
  → Barcode/QR Scan Location
  → Pick + Confirm Qty
  → Exception? → Flag
  → All Picked → Send to Packing
```

---

*Picking Engine — Sprint 26.0*
