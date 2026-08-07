# Integration Deprecation

---

## ❌ Deprecated in Sprint 29

| Component | Reason | Replacement |
|-----------|--------|-------------|
| Manual API helpers | Replaced by API Gateway | `IntegrationDefinitions::apiKeyTable()` |
| Ad-hoc webhook scripts | Replaced by Webhook Engine | `IntegrationDefinitions::webhookTable()` |
| Hardcoded marketplace calls | Replaced by Connector Registry | `IntegrationDefinitions::connectorTable()` |
| Direct payment SDK | Replaced by Payment Gateway Connector | Registry-driven payment |
| Manual shipping API | Replaced by Shipping Connector | Registry-driven shipping |

---

## 🔮 Future Enhancements

| Feature | Priority |
|---------|----------|
| GraphQL Federation | P3 |
| API Monetization | P4 |
| Partner API Program | P4 |
| Event Bridge / Event Bus | P3 |
| iPaaS Low-Code Builder | P4 |

---

*Integration Deprecation — Sprint 29.0*
