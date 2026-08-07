# Warehouse Automation

> 15 IFTTT automation rules for Warehouse & Logistics.

---

## 📋 Rule Catalog

| # | Rule | Trigger | Action |
|---|------|---------|--------|
| 1 | Goods Received | RECORD_CREATED | Create putaway task + update inventory + activity log |
| 2 | Putaway Completed | RECORD_UPDATED | Update location + activity log |
| 3 | Picking Assigned | RECORD_UPDATED | Push notification to picker |
| 4 | Picking Completed | RECORD_UPDATED | Create packing task + activity log |
| 5 | Packing Completed | RECORD_UPDATED | Create shipment + activity log |
| 6 | Shipment Created | RECORD_CREATED | Push notification to logistics/courier |
| 7 | Shipment Delivered | RECORD_UPDATED | Activity log |
| 8 | Transfer Requested | RECORD_CREATED | Push notification to warehouse manager |
| 9 | Transfer Approved | RECORD_UPDATED | Update inventory (transfer out) + activity log |
| 10 | Cycle Count Scheduled | RECORD_CREATED | Create task for inventory controller |
| 11 | Stock Variance Detected | RECORD_UPDATED | Create investigation task + push notification |
| 12 | Warehouse Full | RECORD_UPDATED | Push notification to manager |
| 13 | Low Capacity Alert | RECORD_UPDATED | Push notification to manager |
| 14 | ASN Received | RECORD_CREATED | Create receiving task + activity log |
| 15 | 3PL Updated | RECORD_UPDATED | Activity log |

---

*Warehouse Automation — Sprint 26.0*
