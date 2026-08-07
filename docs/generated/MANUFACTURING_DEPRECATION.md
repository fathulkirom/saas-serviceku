# Manufacturing Deprecation Strategy

> Plan for future Manufacturing module iterations & migration.

---

## ❌ Deprecated in Sprint 25

| Component | Reason | Replacement |
|-----------|--------|-------------|
| Manual production tracking | Replaced by Production Engine | `ManufacturingDefinitions::productionTable()` |
| Spreadsheet BOM | Replaced by BOM Engine | `ManufacturingDefinitions::bomTable()` |
| Paper routing sheets | Replaced by Routing Engine | `ManufacturingDefinitions::routingTable()` |
| Whiteboard work center | Replaced by Work Center Engine | `ManufacturingDefinitions::workCenterTable()` |
| Ad-hoc QC log | Replaced by QC Engine | `ManufacturingDefinitions::qcTable()` |

---

## 🔮 Future Enhancements (Sprint 26+)

| Feature | Priority | Notes |
|---------|----------|-------|
| Advanced Scheduling (APS) | P3 | Finite capacity scheduling |
| MES Integration | P4 | Manufacturing Execution System |
| IoT Machine Monitoring | P4 | Real-time sensor data |
| Digital Twin | P4 | Virtual factory model |
| CAD Integration | P4 | Direct CAD→BOM |
| Cost Simulation | P3 | What-if cost analysis |
| Batch Traceability | P2 | Full batch genealogy |
| Serial Genealogy | P2 | Component→FG trace |

---

*Manufacturing Deprecation — Sprint 25.0*
