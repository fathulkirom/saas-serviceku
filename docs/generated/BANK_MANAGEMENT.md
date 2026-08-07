# Bank Management

> Cash & Bank account management with reconciliation and multi-currency.

---

## 🏧 Account Types

| Type | Description |
|------|-------------|
| Cash | Physical cash (petty cash, cash drawer) |
| Bank | Bank current account |
| Savings | Bank savings account |
| Deposit | Fixed deposit / time deposit |

---

## 🔄 Transactions

| Transaction | Description | Journal Impact |
|-------------|-------------|----------------|
| Deposit | Add funds to account | Debit Bank, Credit Source |
| Withdrawal | Remove funds from account | Debit Destination, Credit Bank |
| Transfer | Move funds between accounts | Debit To Account, Credit From Account |
| Payment Received | Customer payment into bank | Debit Bank, Credit AR |
| Payment Sent | Supplier payment from bank | Debit AP, Credit Bank |
| Bank Fee | Bank charges | Debit Expense, Credit Bank |
| Interest Income | Bank interest earned | Debit Bank, Credit Other Income |

---

## 🔍 Bank Reconciliation

### Process
1. Select bank account
2. Enter statement ending balance
3. Match transactions (auto-match + manual)
4. Identify outstanding items (deposits in transit, outstanding checks)
5. Calculate adjusted balance
6. Confirm reconciliation
7. Generate reconciliation report

### Matching Rules
- Auto-match by: amount + date (±3 days) + reference
- Manual match for remaining items
- Unmatched items flagged for investigation

---

## 📊 Dashboard

| Metric | Source |
|--------|--------|
| Cash Balance | Sum of all cash accounts |
| Bank Balance | Sum of all bank accounts |
| Today In/Out | Net cash flow today |
| Last Reconciled | Date of last reconciliation |
| Unreconciled Count | Accounts needing reconciliation |

---

## 🔐 Security

- Cash accounts: cashier + finance
- Bank accounts: finance + accounting only
- Reconciliation: finance + accounting only
- Transfer approval: dual authorization for > threshold

---

*Bank Management — Sprint 20.0*
