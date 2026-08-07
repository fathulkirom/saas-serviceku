# Leave Management

> Complete leave system: 7 leave types, balance tracking, approval workflow.

---

## 🏖️ Leave Types

| Type | Max Days/Year | Paid | Notes |
|------|---------------|------|-------|
| Annual Leave | 12 | Yes | Accrued monthly |
| Sick Leave | 14 | Yes | Doctor note required for >2 days |
| Permission | 3 | Yes | Short personal leave |
| Maternity | 90 | Yes | 1.5 months before + 1.5 months after |
| Paternity | 3 | Yes | Wife childbirth |
| Unpaid Leave | 30 | No | Extended personal leave |
| Special Leave | 3 | Yes | Marriage, family death, religious |

---

## 📋 Leave Workflow

```
Employee applies leave
  → System checks balance
  → Manager notified (automation)
  → Manager approves/rejects
  → HRD notified if > threshold
  → Balance updated
  → Attendance auto-marked as "On Leave"
```

---

## 📊 Leave Balance

| Employee | Annual | Sick | Permission | Total Used | Remaining |
|----------|--------|------|------------|------------|-----------|
| Tracked per employee per year | | | | | |

---

## 🔗 Integration

| Integration | Description |
|-------------|-------------|
| Attendance | Leave days → "On Leave" status |
| Payroll | Unpaid leave → salary deduction |
| Automation | Pending leave → approval notification |

---

*Leave Management — Sprint 21.0*
