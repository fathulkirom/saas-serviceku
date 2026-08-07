# Payroll Engine

> Full payroll processing: salary, allowances, overtime, bonus, deductions, tax, BPJS.

---

## 💰 Payroll Components

| Component | Type | Description |
|-----------|------|-------------|
| Basic Salary | Income | Monthly base salary |
| Position Allowance | Income | Jabatan allowance |
| Transport Allowance | Income | Transport allowance |
| Meal Allowance | Income | Meal allowance |
| Overtime Pay | Income | Calculated from overtime hours |
| Bonus | Income | Performance/periodic bonus |
| Commission | Income | Sales/service commission |
| Incentive | Income | Special incentive |
| BPJS Health | Deduction | 1% employee (4% employer) |
| BPJS Labor | Deduction | JHT, JKM, JP |
| PPh 21 | Deduction | Income tax (progressive) |
| Loan Repayment | Deduction | Employee loan |
| Advance Deduction | Deduction | Salary advance recovery |
| Other Deduction | Deduction | Miscellaneous |

---

## 📊 Payroll Workflow

```
Payroll Period Generated
  → Calculate: Basic + Allowances + Overtime + Bonus
  → Calculate: Deductions (BPJS + Tax + Loan)
  → Net = Income - Deductions
  → Draft → Review → Approve
  → Auto Journal (Salary Expense)
  → Mark Paid → Generate Slip
```

---

## 🧾 Payroll Slip

| Section | Content |
|---------|---------|
| Header | Company, period, employee info |
| Income | Basic salary + all allowances + overtime + bonus |
| Deductions | BPJS + Tax + Loan + Other |
| Summary | Gross → Deductions → Net (Take Home Pay) |
| Footer | Approval signature |

---

## 🔗 Integration

| Integration | Description |
|-------------|-------------|
| Finance | Auto journal entry for salary expense |
| Tax Engine | PPh 21 calculation + reporting |
| Attendance | Overtime data source |
| Performance | Bonus/incentive data source |

---

*Payroll Engine — Sprint 21.0*
