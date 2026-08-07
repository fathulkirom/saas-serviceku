# Service Estimation — Sprint 36A

> Estimation, quotation, approval, and revision workflow.

---

## 💰 Estimation Components

| Component | Description |
|-----------|-------------|
| Jasa (Labor) | Service charge based on labor type and complexity |
| Sparepart | Required parts with cost and margin |
| Diskon | Discount percentage or fixed amount |
| Pajak | Tax (PPN 11%) |
| DP | Down payment (minimum % of total) |
| Estimasi Selesai | Estimated completion date/time |

---

## 📋 Estimation Workflow

```
Diagnosis complete
  → Technician identifies required labor + parts
  → System generates quotation (ServiceQuotation)
  → CS sends to customer for approval
  → Customer approves/rejects/revises
  → Approved → Proceed to repair
  → Rejected → Cancel or revise
```

---

## 🔄 Revision History

- Every quotation revision is versioned
- Previous versions preserved for audit
- Reason for revision recorded
- Who approved/rejected recorded with timestamp

---

## 💳 Payment Terms

| Term | Description |
|------|-------------|
| DP Minimum | 30% of total estimate |
| Full Payment | Required before `close` |
| Partial Payment | Allowed with manager approval |
| Payment Methods | Cash, Transfer, QRIS |

---

*Service Estimation — Sprint 36A*
