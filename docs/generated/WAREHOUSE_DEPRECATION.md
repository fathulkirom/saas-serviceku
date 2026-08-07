# Warehouse Deprecation Strategy

> Plan for future WMS module iterations & migration.

---

## ❌ Deprecated in Sprint 26

| Component | Reason | Replacement |
|-----------|--------|-------------|
| Manual warehouse log | Replaced by Warehouse Engine | `WarehouseDefinitions::warehouseTable()` |
| Paper receiving slips | Replaced by Receiving Engine | `WarehouseDefinitions::receivingTable()` |
| Whiteboard picking | Replaced by Picking Engine | `WarehouseDefinitions::pickingTable()` |
| Manual packing list | Replaced by Packing Engine | `WarehouseDefinitions::packingTable()` |
| Spreadsheet shipment log | Replaced by Shipping Engine | `WarehouseDefinitions::shippingTable()` |
| Ad-hoc cycle count | Replaced by Cycle Count Engine | `WarehouseDefinitions::cycleCountTable()` |

---

## 🔮 Future Enhancements (Sprint 27+)

| Feature | Priority | Notes |
|---------|----------|-------|
| Voice Picking | P3 | Voice-guided picking |
| RFID Inventory | P3 | RFID tag scanning |
| Automated Storage (AS/RS) | P4 | Robot integration |
| Drone Inventory Count | P4 | Automated cycle count |
| Slotting Optimization | P3 | AI-based location optimization |
| Yard Management | P4 | Truck/container yard ops |
| Cold Chain | P4 | Temperature-controlled logistics |
| Dangerous Goods | P4 | Hazmat handling |

---

*Warehouse Deprecation — Sprint 26.0*
