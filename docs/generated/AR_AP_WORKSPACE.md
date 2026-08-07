# AR & AP Workspace

> Accounts Receivable & Accounts Payable management, fully integrated.

---

## 📥 Accounts Receivable (AR)

### Features
| Feature | Description |
|---------|-------------|
| Customer Invoice | Generate invoice from service/sales |
| Outstanding Tracking | Real-time outstanding per customer |
| Partial Payment | Support multiple partial payments |
| Credit Memo | Reduce receivable (return, discount, correction) |
| Debit Memo | Increase receivable (additional charge) |
| Aging Analysis | 0-30, 31-60, 61-90, 90+ days buckets |
| Collection Status | Open, Partial, In Collection, Paid |
| Automatic Reminder | Automation-triggered WA reminders |
| Payment History | Full payment timeline per invoice |

### AR Workflow
```
Service/Sales Completed → Invoice Generated → AR Created
  → Payment Received → AR Updated (partial/full)
  → Overdue? → Automation: Send WA Reminder
  → 90+ days? → Mark "In Collection"
```

---

## 📤 Accounts Payable (AP)

### Features
| Feature | Description |
|---------|-------------|
| Supplier Invoice | Record purchase invoice |
| Outstanding Payable | Real-time outstanding per supplier |
| Partial Payment | Support multiple partial payments |
| Credit Note | Reduce payable (return, discount) |
| Debit Note | Increase payable (additional charge) |
| Due Date Tracking | Payment schedule with alerts |
| Payment History | Full payment timeline per invoice |

### AP Workflow
```
Purchase Order → Goods Receipt → Supplier Invoice → AP Created
  → Payment Sent → AP Updated (partial/full)
  → Due Soon? → Automation: Payment Reminder
```

---

## 🔗 Cross-Module Integration

| Transaction | Source | AR/AP Impact |
|-------------|--------|--------------|
| Service Completed | Service | AR (if not paid immediately) |
| Sales | Sales | AR (if credit) |
| Purchase Order | Purchasing | AP |
| Payment Received | Cash/Bank | AR clearing |
| Payment Sent | Cash/Bank | AP clearing |
| Return | Service/Sales | Credit Memo (AR) |
| Supplier Return | Purchasing | Credit Note (AP) |

---

*AR/AP Workspace — Sprint 20.0*
