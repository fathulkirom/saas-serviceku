# HR Automation

> 12 IFTTT automation rules for HRM lifecycle.

---

## 📋 Rule Catalog

### 1. Employee Created → Onboarding
| Field | Value |
|-------|-------|
| Trigger | `RECORD_CREATED` (employee) |
| Action | Create onboarding task (1h delay) |

### 2. Birthday Reminder
| Field | Value |
|-------|-------|
| Trigger | `DATE_REACHED` (birth_date) |
| Action | Send WA greeting + push notification |

### 3. Contract Expiring → Alert
| Field | Value |
|-------|-------|
| Trigger | `DATE_REACHED` (contract_end - 30d) |
| Action | Create task + push notification |

### 4. Leave Waiting Approval → Notify
| Field | Value |
|-------|-------|
| Trigger | `RECORD_CREATED` (leave request) |
| Action | Push notification to manager + HRD |

### 5. Attendance Missing → Alert
| Field | Value |
|-------|-------|
| Trigger | `DATE_REACHED` (10:00 AM) |
| Condition | No clock-in record |
| Action | Push notification to HRD + manager |

### 6. Late Arrival → Log
| Field | Value |
|-------|-------|
| Trigger | `RECORD_UPDATED` (clock_in > shift_start) |
| Action | Activity log |

### 7. Payroll Ready → Notify
| Field | Value |
|-------|-------|
| Trigger | `RECORD_UPDATED` (payroll status → draft) |
| Action | Push notification to HRD + finance + owner |

### 8. Payroll Approved → Auto Journal
| Field | Value |
|-------|-------|
| Trigger | `RECORD_UPDATED` (payroll status → approved) |
| Action | Create automatic journal entry |

### 9. Training Reminder
| Field | Value |
|-------|-------|
| Trigger | `DATE_REACHED` (training_date - 1d) |
| Action | Push notification to participants |

### 10. Performance Review Due
| Field | Value |
|-------|-------|
| Trigger | `DATE_REACHED` (review_cycle) |
| Action | Create task for manager |

### 11. Probation Ending
| Field | Value |
|-------|-------|
| Trigger | `DATE_REACHED` (probation_end) |
| Action | Create evaluation task for HRD |

### 12. Employee Resigned → Offboarding
| Field | Value |
|-------|-------|
| Trigger | `RECORD_UPDATED` (employment_status → resigned) |
| Action | Create offboarding task + activity log |

---

## 🔗 Automation Chain

```
EMPLOYEE_CREATED
  → Create onboarding task (1h delay)

LEAVE_CREATED
  → Push notification to manager

PAYROLL_APPROVED
  → Create journal entry (Salary Expense)
  → Update payroll status

PERFORMANCE_DUE
  → Create review task
  → Push notification
```

---

*HR Automation — Sprint 21.0*
