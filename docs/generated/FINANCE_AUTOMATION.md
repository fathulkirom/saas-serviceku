# Finance Automation

> 10 IFTTT automation rules for Finance & Accounting lifecycle.

---

## 📋 Rule Catalog

### 1. Invoice Created → Notify Customer
| Field | Value |
|-------|-------|
| Trigger | `RECORD_CREATED` (invoice) |
| Condition | Customer has WhatsApp |
| Action | Send WA invoice notification |

### 2. Invoice Overdue → Reminder
| Field | Value |
|-------|-------|
| Trigger | `DATE_REACHED` (due_date) |
| Condition | Invoice status = open |
| Action | Send WA + Create follow-up task |

### 3. Payment Received → Auto Journal
| Field | Value |
|-------|-------|
| Trigger | `RECORD_UPDATED` (payment) |
| Action | Create automatic journal entry |

### 4. Supplier Payment Due → Reminder
| Field | Value |
|-------|-------|
| Trigger | `DATE_REACHED` (due_date - 3d) |
| Action | Create task for finance |

### 5. Journal Posted → Audit Log
| Field | Value |
|-------|-------|
| Trigger | `RECORD_UPDATED` (journal status → posted) |
| Action | Log activity with user info |

### 6. Budget Exceeded → Alert
| Field | Value |
|-------|-------|
| Trigger | `RECORD_UPDATED` (actual > budget) |
| Action | Push notification to owner + finance |

### 7. Bank Reconciliation Required
| Field | Value |
|-------|-------|
| Trigger | `DATE_REACHED` (weekly schedule) |
| Action | Create reconciliation task |

### 8. Month End Closing Reminder
| Field | Value |
|-------|-------|
| Trigger | `DATE_REACHED` (last day of month) |
| Action | Create task + push notification |

### 9. Year End Closing Reminder
| Field | Value |
|-------|-------|
| Trigger | `DATE_REACHED` (Dec 31) |
| Action | Create task + send WA |

### 10. Cash Balance Below Minimum
| Field | Value |
|-------|-------|
| Trigger | `RECORD_UPDATED` (cash balance < minimum) |
| Action | Push notification to owner + finance |

---

## 🔗 Automation Chain Example

```
SERVICE_COMPLETED
  → Create Invoice (Auto)
    → INVOICE_CREATED trigger
      → Send WA to customer
      → Create journal (Revenue + COGS)
        → JOURNAL_POSTED trigger
          → Log activity

PAYMENT_RECEIVED
  → Update AR status
  → Create journal (Cash + AR clearing)
  → Update cash balance
    → CASH_BELOW_MIN? → Alert owner
```

---

*Finance Automation — Sprint 20.0*
