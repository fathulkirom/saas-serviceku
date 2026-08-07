# Module Integration — ServiceKU v1.0

> Cross-module data flow, dependencies, and integration status.

---

## 🔗 Integration Map

```
                        ┌──────────────────────────────────────────────┐
                        │              NOTIFICATION CENTER              │
                        │   (WA, Email, Push, SMS — all modules feed)   │
                        └──────────────────────────────────────────────┘
                                              ↑
    ┌─────────┐    ┌─────────┐    ┌─────────┐  │  ┌─────────┐    ┌──────────┐
    │ SERVICE │◄──▶│   CRM   │    │INVENTORY│  │  │FINANCE  │    │WORKFLOW  │
    │  (Hub)  │    │Customer │    │ Stock   │  │  │   GL    │    │ Approval │
    └────┬────┘    └─────────┘    └────┬─────┘  │  └────┬─────┘    └────┬─────┘
         │                             │         │       │              │
         │  ┌──────────────┐          │         │       │              │
         ├──│ TECHNICIAN    │          │         │       │              │
         │  │ Portal        │          │         │       │              │
         │  └──────────────┘          │         │       │              │
         │                             │         │       │              │
    ┌────┴────┐                  ┌────┴─────┐   │  ┌────┴─────┐   ┌───┴──────┐
    │CUSTOMER │                  │PURCHASING│   │  │   POS    │   │   GRC    │
    │ Portal  │                  │ Supplier │   │  │  Sales   │   │Risk/Audit│
    └─────────┘                  └──────────┘   │  └──────────┘   └──────────┘
                                                │
                        ┌───────────────────────┴────────────────────────────┐
                        │                  EPOC (Monitoring)                  │
                        │         Platform Health, Queue, Cache, DB          │
                        └────────────────────────────────────────────────────┘
```

---

## 🔌 Key Integrations

### Service ↔ Inventory (✅ Wired)
```
Sparepart used in repair
  → ServicePartUsage created
  → Product::reduceStock() called
  → InventoryMutation recorded
  → If stock < min → SystemAlert created
  → Purchasing notified for reorder

Part returned
  → ServicePartReturn created
  → Product::increaseStock() called
  → InventoryMutation recorded
```

### Service ↔ Workflow (✅ Wired)
```
Status transition requested (frontend)
  → POST /services/{id}/workspace/transition
  → ServiceWorkflowValidator::validate()
  → State machine check (ALLOWED_TRANSITIONS)
  → Business rule check (payment, QC, diagnosis)
  → Transition executed
  → Timeline event created
  → Automation engine triggered
  → Notification sent
```

### Service ↔ Notification (✅ Wired)
```
Status change event fired
  → ServiceStatusUpdated event
  → AutomationSubscriber picks up
  → Checks trigger: STATUS_CHANGED
  → Matches automation rules
  → Sends via Notification Center
  → Channel: WhatsApp / Email / Push
```

### Service ↔ Finance (✅ Wired)
```
Service completed
  → Sale record created (type: servis)
  → Sale items from ServicePartUsage
  → Payment processed
  → Commission calculated (10% for technician)
  → Finance transaction recorded
```

### POS ↔ Inventory (✅ Wired)
```
Product sold
  → SaleItem created
  → Product stock deducted
  → InventoryMutation recorded
  → If stock < min → alert
```

### Purchasing ↔ Inventory (✅ Wired)
```
Purchase order received
  → Purchase status: received
  → Product stock increased
  → InventoryMutation recorded
  → Supplier payment record created
```

---

## ⚠️ Partially Wired

### Service ↔ CRM
- ✅ Service creation links to customer
- ✅ Customer history visible in Service Workspace sidebar
- ⚠️ Customer Portal "My Devices" tab not wired

### Service ↔ GRC
- ⚠️ Risk assessment for high-value repairs defined but not triggered
- ⚠️ Incident management for QC failures defined but not wired
- ⚠️ Compliance matrix defined but not linked to service workflow

### EPOC → All Modules
- ⚠️ Platform metrics definition exists
- ⚠️ Queue monitoring UI exists but reads no real data
- ⚠️ Performance logs defined but not collected

### AI → Service
- ⚠️ Diagnosis assist prompts defined
- ⚠️ No API call wiring yet

---

## 📊 Integration Status Summary

| Connection | Wired | Defined | Not Started |
|------------|:-----:|:-------:|:-----------:|
| Service ↔ Inventory | ✅ | — | — |
| Service ↔ Workflow | ✅ | — | — |
| Service ↔ Notification | ✅ | — | — |
| Service ↔ Finance | ✅ | — | — |
| POS ↔ Inventory | ✅ | — | — |
| POS ↔ Finance | ✅ | — | — |
| Purchasing ↔ Inventory | ✅ | — | — |
| Purchasing ↔ Finance | — | ⚠️ | — |
| Service ↔ CRM | ⚠️ | — | — |
| Service ↔ GRC | — | ⚠️ | — |
| WMS ↔ Inventory | ✅ | — | — |
| EPOC ↔ All | — | ⚠️ | — |
| AI ↔ Service | — | ⚠️ | — |
| HRM ↔ Finance | — | ⚠️ | — |
| Manufacturing ↔ Inventory | — | ⚠️ | — |
| Project ↔ Finance | — | ⚠️ | — |

---

*Module Integration — ServiceKU v1.0*
