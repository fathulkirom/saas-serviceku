# Finance & Accounting Architecture

> **Sprint 20.0** — Fifth ERP module, fully integrated Enterprise Platform.

---

## 🏗️ Architecture

```
Finance & Accounting Module
├── Chart of Accounts (COA)    → Data Engine (10 cols, 4 filters, 3 bulk actions)
├── Finance Workspace          → Workspace Engine (16 tabs)
├── Journal Entry              → Data Engine (10 cols, 3 filters, 4 bulk actions) + Form Engine (repeater lines)
├── AR Aging                   → Data Engine (11 cols, 4 filters, 3 bulk actions)
├── AP Aging                   → Data Engine (10 cols, 3 filters, 2 bulk actions)
├── Cash & Bank                → Data Engine (9 cols, 2 filters, 2 bulk actions)
├── Tax Management             → Data Engine (10 cols, 3 filters, 2 bulk actions)
├── Budget Management          → Data Engine (10 cols, 4 filters, 3 bulk actions)
├── Multi Currency             → Data Engine (8 cols, 1 filter, 2 bulk actions)
├── Automation Engine          → 10 rules (invoice, payment, journal, budget, closing)
├── Reporting Engine           → 13 reports (GL, TB, P&L, BS, CF, AR, AP, Tax, Expense, Revenue, Budget, TopExpense, BranchProfit)
└── Dashboard Engine           → 3 widgets (CashBalance, NetProfit, Payable)
```

---

## 📊 Finance Workspace (16 tabs)

| Tab | Content |
|-----|---------|
| Overview | Finance KPI dashboard — cash, revenue, expense, AR, AP, trends |
| Journal Entries | All journal entries with filter by type/status/date |
| General Ledger | Ledger per account with running balance |
| Trial Balance | Debit/credit summary as of date |
| Profit & Loss | Revenue - COGS - Expenses = Net Profit |
| Balance Sheet | Assets = Liabilities + Equity |
| Cash Flow | Operating + Investing + Financing |
| Accounts Receivable | Customer outstanding invoices with aging |
| Accounts Payable | Supplier outstanding invoices with due dates |
| Cash & Bank | All cash/bank accounts with reconciliation |
| Tax | PPN, PPh tracking with filing status |
| Budget | Budget vs Actual per account/department/branch |
| Timeline | Full finance activity timeline |
| Documents | Attachments |
| Activity Log | Audit trail |

---

## 🔗 Integration Points

| Source Module | Finance Impact |
|---------------|---------------|
| Service Completed | Auto Journal (Revenue + COGS) |
| Sales | Auto Journal (Revenue + COGS + AR) |
| Purchase | Auto Journal (Expense/Asset + AP) |
| Goods Receipt | Auto Journal (Inventory + AP clearing) |
| Payment Received | Auto Journal (Cash + AR clearing) |
| Payment Sent | Auto Journal (AP clearing + Cash) |
| Inventory Adjustment | Auto Journal (Stock variance) |
| Expense | Auto Journal (Expense + Cash/AP) |
| Transfer | Auto Journal (Bank to Bank) |

---

## 🔐 Role Matrix

| Role | Finance Access |
|------|---------------|
| Owner | Full — all tabs, all reports, all automations |
| Finance | Full — all operational finance |
| Accounting | Full — journal, ledger, reports, tax |
| Manager | Read-only reports + dashboard |
| Admin | Read-only dashboard + basic reports |
| Cashier | Cash & Bank tab + record payment |
| Purchasing | AP tab only |
| Warehouse | View only inventory-related journals |
| CS | View only AR for their customers |

---

*Finance Architecture — Sprint 20.0*
