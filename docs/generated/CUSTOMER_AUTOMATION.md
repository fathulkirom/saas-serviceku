# Customer Automation

> 5 IFTTT automation rules for Customer Management lifecycle.

---

## 📋 Rule Catalog

### 1. New Customer Welcome
| Field | Value |
|-------|-------|
| Trigger | `CUSTOMER_CREATED` |
| Delay | 1 hour |
| Action | Send WA greeting + Create follow-up task |

### 2. Birthday Notification
| Field | Value |
|-------|-------|
| Trigger | `DATE_REACHED` (birth_date) |
| Condition | `IS_ACTIVE` = true |
| Action | Send WhatsApp birthday greeting with promo code |

### 3. No Visit 30 Days — Reactivation
| Field | Value |
|-------|-------|
| Trigger | `DATE_REACHED` (last_visit + 30d) |
| Condition | `TOTAL_SPENT` > Rp 1M |
| Action | Send WA with "We miss you" + discount offer |

### 4. VIP Upgrade
| Field | Value |
|-------|-------|
| Trigger | `RECORD_UPDATED` (total_spent) |
| Condition | `TOTAL_SPENT` >= threshold |
| Action | Send WA congratulation + upgrade task |

### 5. Customer Reactivated
| Field | Value |
|-------|-------|
| Trigger | `RECORD_UPDATED` (last_visit) |
| Condition | Previous `IS_INACTIVE` > 90 days |
| Action | Log activity "Customer reactivated after {days} days" |

---

## 🔗 Automation Chain

```
CUSTOMER_CREATED
  → 1h delay
    → Send WA (Message Template Engine)
    → Create Task (Task Manager)

DATE_REACHED
  → Birthday check
    → Send WA (Message Engine)
    → Create Coupon (Promo Engine)

RECORD_UPDATED
  → Check LTV threshold
    → Upgrade Level
    → Send Notification
    → Log Activity
```

---

*Customer Automation — Sprint 19.0*
