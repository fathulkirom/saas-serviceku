# SERVICE FLOW — ServiceKU v0.9.0-beta

## End-to-End Service Lifecycle

```
CUSTOMER BARU
    │
    ▼
SERVICE INTAKE
    │  Customer walk-in / drop-off
    │  Device registration (IMEI/Serial)
    │  Keluhan (problem description)
    │
    ▼
CHECKLIST PENERIMAAN
    │  Physical condition check
    │  Accessories check
    │  Power-on test
    │  Customer signature
    │
    ▼
SNAPSHOT
    │  Photo capture (pre-repair)
    │  Device condition documentation
    │
    ▼
ASSIGN TECHNICIAN
    │  Technician selection
    │  Work order created
    │  Status: menunggu_alokasi → diterima
    │
    ▼
DIAGNOSIS
    │  Problem identification
    │  Root cause analysis
    │  Diagnosis notes + photos
    │
    ▼
QUOTATION
    │  Repair cost estimation
    │  Part list + labor
    │  Customer approval required
    │
    ▼
CUSTOMER APPROVAL
    │  WhatsApp/Email notification
    │  Customer confirms/revises
    │  Status: menunggu_konfirmasi_pelanggan
    │
    ▼
PART REQUEST
    │  Technician requests parts
    │  Warehouse approval
    │  Part booked from inventory
    │
    ▼
INVOICE GENERATED
    │  Service + parts invoice
    │  Customer billing
    │
    ▼
STOCK REDUCE
    │  Inventory mutation recorded
    │  Stock decremented (with integrity guard)
    │
    ▼
REPAIR
    │  Technician worklog entries
    │  Repair photos
    │  Status: dikerjakan
    │
    ▼
QC (QUALITY CONTROL)
    │  Post-repair testing
    │  QC checklist
    │  Pass/Fail decision
    │
    ▼
READY FOR PICKUP
    │  Customer notification
    │  Status: siap_diambil
    │
    ▼
PAYMENT
    │  Invoice payment
    │  Cash / Transfer / QR
    │  Payment verification
    │
    ▼
DELIVERY / PICKUP
    │  Customer pickup
    │  Handover checklist
    │  Status: selesai
    │
    ▼
WARRANTY ACTIVE
    │  Auto-generated warranty
    │  Warranty period tracking
    │  Status: active → expired
    │
    ▼
WARRANTY CLAIM (optional)
    │  Customer returns within warranty
    │  Claim evaluation
    │  Approved / Rejected
    │  Free repair or prorated
    │
    ▼
REOPEN (optional)
    │  Service reopened for revision
    │  New diagnosis
    │  Additional repair
    │
    ▼
CLOSE
    │  Service fully resolved
    │  All payments settled
    │  Archive
```

## Status Transition Map

```
                    ┌──────────────┐
                    │ menunggu_    │
                    │ alokasi      │
                    └──────┬───────┘
                           │ assign
                           ▼
                    ┌──────────────┐
                    │ diterima     │
                    └──────┬───────┘
                           │ start diagnosis
                           ▼
                    ┌──────────────┐
              ┌─────│ diagnosa     │─────┐
              │     └──────────────┘     │
              │ create quotation         │ indent needed
              ▼                          ▼
      ┌──────────────┐           ┌──────────────┐
      │ menunggu_    │           │ indent       │
      │ konfirmasi   │           └──────┬───────┘
      └──────┬───────┘                  │ parts arrive
             │ approved                 ▼
             ▼                   ┌──────────────┐
      ┌──────────────┐           │ dikerjakan   │←──────┐
      │ dikerjakan   │───────────┘              │       │
      └──────┬───────┘                          │       │
             │ repair complete                  │       │
             ▼                                  │       │
      ┌──────────────┐                          │       │
      │ qc check     │──────────────────────────┘       │
      └──────┬───────┘ (fail → back to repair)          │
             │ pass                                      │
             ▼                                           │
      ┌──────────────┐                                   │
      │ siap_diambil │                                   │
      └──────┬───────┘                                   │
             │ payment                                    │
             ▼                                           │
      ┌──────────────┐                                   │
      │ selesai      │───────────────────────────────────┘
      └──────────────┘ (reopen → back to diagnosis)
```

## Stock Integrity Guards

- `Product::reduceStock()` throws `RuntimeException` if qty > stock
- `HasOptimisticLocking` trait prevents concurrent edits
- `lock_version` column on `products` and `services`
- Event logging on every inventory mutation

## Event Tracking

All state transitions dispatch Laravel native `event()` which is captured by:
```
Event::listen('*', [EventLogger::class, 'handle']);
```
Logged to `event_logs` table with:
- event class name
- entity type + ID
- actor (user ID)
- payload (JSON)
- timestamp
