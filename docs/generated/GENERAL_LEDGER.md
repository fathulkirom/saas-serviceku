# General Ledger

> Complete double-entry ledger with running balance, drill-down, and export.

---

## 📒 Ledger Views

| View | Description |
|------|-------------|
| Per Account | Ledger for a specific COA account |
| Per Branch | Ledger filtered by branch |
| Per Customer | Customer-related transactions |
| Per Supplier | Supplier-related transactions |
| Per Project | Project cost tracking (future) |
| All Accounts | Combined general ledger |

---

## 📋 Journal Entry Types

| Type | Description | Auto/Manual |
|------|-------------|-------------|
| Manual | User-created journal | Manual |
| Automatic | System-generated (service, sales, purchase) | Auto |
| Recurring | Scheduled recurring entries | Auto |
| Adjustment | Period-end adjustments | Manual |
| Closing | Period/year-end closing | Manual |
| Reversing | Auto-reverse next period | Auto |
| Opening Balance | Initial balance setup | Manual |

---

## 🔢 COA Structure

| Level | Description | Example |
|-------|-------------|---------|
| 1 - Header | Account group | 1000 - ASSETS |
| 2 - Sub | Sub-group | 1100 - Current Assets |
| 3 - Detail | Account category | 1110 - Cash & Bank |
| 4 - Item | Transactional account | 1111 - Cash Main |

---

## 📐 Double Entry Rules

- Every journal MUST balance (Total Debit = Total Credit)
- Debit = Left side (increase asset/expense, decrease liability/equity/revenue)
- Credit = Right side (increase liability/equity/revenue, decrease asset/expense)
- Journal can have N lines (minimum 2)
- Once posted, journal is immutable (void only, no edit)

---

## 🔍 Running Balance

- Calculated per account per date
- Opening balance + sum of debits - sum of credits
- Filterable by date range
- Drill-down to source transaction

---

*General Ledger — Sprint 20.0*
