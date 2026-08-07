# Finance Deprecation Strategy

> Plan for future Finance module iterations & migration.

---

## ❌ Deprecated in Sprint 20

| Component | Reason | Replacement |
|-----------|--------|-------------|
| Manual finance tracking | Replaced by Journal Engine | `FinanceDefinitions::journalForm()` |
| Hardcoded COA | Replaced by Data Engine | `FinanceDefinitions::coaTable()` |
| Manual AR tracking | Replaced by AR Aging table | `FinanceDefinitions::arAgingTable()` |
| Manual AP tracking | Replaced by AP Aging table | `FinanceDefinitions::apAgingTable()` |
| Ad-hoc tax calculation | Replaced by Tax Engine | `FinanceDefinitions::taxTable()` |
| Spreadsheet budgeting | Replaced by Budget Engine | `FinanceDefinitions::budgetTable()` |
| Manual bank reconciliation | Replaced by Bank Engine | `FinanceDefinitions::cashBankTable()` |

---

## 🔮 Future Enhancements (Sprint 21+)

| Feature | Priority | Notes |
|---------|----------|-------|
| Fixed Asset Management | P2 | Depreciation, disposal, revaluation |
| Project Cost Accounting | P2 | Cost center, WIP, project P&L |
| Consolidation (multi-entity) | P3 | Parent-subsidiary consolidation |
| e-Faktur API Integration | P2 | Direct DJP submission |
| e-Bupot Integration | P3 | Withholding tax e-filing |
| Bank Feed (auto-import) | P3 | Direct bank API integration |
| Accrual Engine | P3 | Auto accrual for recurring expenses |
| Intercompany Accounting | P4 | Multi-entity transactions |
| IFRS/PSAK Compliance Pack | P4 | Standard report templates |

---

## ⚠️ Migration Notes

- Existing `transactions` table — keep as legacy, map to journal entries
- Legacy payment data — preserved, not migrated
- Old COA — create new COA structure, map old accounts
- All new transactions MUST go through Journal Engine
- Legacy data remains read-only

---

*Finance Deprecation — Sprint 20.0*
