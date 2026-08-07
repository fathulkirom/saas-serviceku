# Customer Deprecation Strategy

> Plan for future Customer module iterations & migration.

---

## ❌ Deprecated in Sprint 19

| Component | Reason | Replacement |
|-----------|--------|-------------|
| Old Customer list page | Replaced by Data Engine table | `DataTable` with `CRMDefinitions::customerTable()` |
| Manual customer form | Replaced by Form Engine | `SkForm` with `CRMDefinitions::customerForm()` |
| Hardcoded customer stats | Replaced by Reporting Engine | `ReportEngine` metrics |
| Manual WA sending | Replaced by Automation Engine | `AutomationTypes::SEND_WHATSAPP` action |
| Inline import script | Replaced by Enterprise Bulk Import | Data Engine Bulk Action |

---

## 🔮 Future Enhancements (Sprint 20+)

| Feature | Priority | Notes |
|---------|----------|-------|
| Customer Portal (self-service) | P2 | Login, see service status, invoices |
| Loyalty Points Engine | P2 | Points accumulation & redemption |
| Referral Program | P3 | Track referrals, auto-reward |
| Customer Merge | P3 | Deduplicate customers |
| GDPR Compliance | P2 | Data export, delete, consent |
| Advanced Segmentation | P3 | RFM analysis, predictive churn |
| WhatsApp Integration | P2 | Two-way chat in workspace |
| Google Business Messages | P4 | Multi-channel |

---

*Customer Deprecation — Sprint 19.0*
