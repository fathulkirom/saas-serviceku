# Service Warranty Guide — Sprint 36A

> Jasa warranty, sparepart warranty, void conditions, claims, and reminders.

---

## 🛡️ Warranty Types

| Type | Coverage | Default Duration |
|------|----------|-----------------|
| Garansi Jasa | Labor/workmanship | 30 days |
| Garansi Sparepart | Parts replaced | 90 days |

---

## 📋 Warranty Lifecycle

```
Service close
  → Warranty auto-generated (service_warranties record)
  → Jasa warranty: 30 days from close date
  → Sparepart warranty: 90 days from close date
  → Reminder sent 3 days before expiry
```

---

## ❌ Void Warranty Conditions

Warranty becomes void if:
- Physical damage after pickup
- Water damage after pickup
- Opened by unauthorized technician
- Used non-genuine parts (customer request)
- Customer declined recommended repair

---

## 📞 Warranty Claim Flow

```
Customer reports issue within warranty period
  → CS opens warranty claim
  → Technician verifies claim validity
  → Valid → Repair under warranty (no charge)
  → Invalid → New service with quotation
```

---

## 🔔 Warranty Reminders

- 3 days before expiry → Notification to customer
- Expired → Status updated to `expired`
- Claim history tracked per service and per customer

---

*Service Warranty Guide — Sprint 36A*
