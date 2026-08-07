# Depreciation Engine

> 4 depreciation methods, monthly/annual posting, book value tracking, Finance integration.

---

## 📉 Depreciation Methods

| Method | Formula | Best For |
|--------|---------|----------|
| Straight Line | (Cost - Residual) / Useful Life | Buildings, furniture |
| Declining Balance | Book Value × Rate | Computers, electronics |
| Double Declining | 2 × Straight Line Rate | Vehicles, machinery |
| Units of Production | (Cost - Residual) / Total Units × Units Used | Production equipment |

---

## 📊 Depreciation Schedule

| Period | Beginning BV | Depreciation | Accumulated Dep | Ending BV |
|--------|-------------|--------------|-----------------|-----------|
| Year 1 | Purchase Value | Monthly × 12 | Sum | BV - Dep |
| Year 2 | Ending BV Y1 | ... | ... | ... |
| ... | ... | ... | ... | Residual Value |

---

## 🔄 Posting Types

| Type | Frequency | Journal |
|------|-----------|---------|
| Monthly | Every month | Debit Depreciation Expense, Credit Accumulated Depreciation |
| Annual | Year-end | Same as above, annual amount |

---

## 🔗 Finance Integration

```
Depreciation Posted
  → Auto Journal (Automation: asset.depreciation_posted)
  → Debit: Depreciation Expense (COA)
  → Credit: Accumulated Depreciation (COA)
  → Update Asset Book Value
```

---

*Depreciation Engine — Sprint 22.0*
