# MRP Engine (Material Requirement Planning)

> Material requirement calculation, shortage detection, purchase suggestion.

---

## 🧱 MRP Calculation

```
Gross Requirement (from BOM × Production Qty)
  - On Hand Inventory
  - Safety Stock
  + Reserved for Other Orders
  = Net Requirement
  → Purchase Suggestion (if net > 0)
```

---

## 📋 MRP Outputs

| Output | Description |
|--------|-------------|
| Material Requirement | What and how much |
| Shortage Alert | Items below safety stock |
| Purchase Suggestion | Suggested PO to purchasing |
| Production Suggestion | Suggested production order |
| Reservation | Reserve materials for order |
| Allocation | Allocate available stock |

---

*MRP Engine — Sprint 25.0*
