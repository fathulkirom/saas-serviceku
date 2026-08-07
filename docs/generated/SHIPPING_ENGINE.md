# Shipping Engine

> Shipment management with courier, fleet, 3PL, tracking, manifest, POD.

---

## 🚚 Shipping Flow

```
Packed → Shipment Created
  → Assign Carrier/Courier/Fleet/3PL
  → Print Manifest
  → Dispatch → In Transit
  → Tracking Updates
  → Delivered → POD Captured
  → Failed? → Return to Sender
```

---

## 📊 Carrier Management

| Carrier Type | Description |
|-------------|-------------|
| Internal Fleet | Own vehicle + driver |
| Courier | Third-party courier |
| 3PL | Third-party logistics |

---

*Shipping Engine — Sprint 26.0*
