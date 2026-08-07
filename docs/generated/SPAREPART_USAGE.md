# Sparepart Usage — Sprint 36B

> Integrated sparepart management for technician workflow.

---

## 🔩 Usage Types

| Type | Description | Inventory Impact |
|------|-------------|-----------------|
| Replace | Replace damaged part with new | Deduct from stock |
| New Install | Install new part (upgrade) | Deduct from stock |
| Return | Return unused part to inventory | Add to stock |
| Swap | Exchange with known-good part | Net zero |

---

## 🔍 Search & Scan

- **Text Search**: Search by part name, SKU, or category
- **Barcode Scan**: Scan part barcode with device camera
- **QR Scan**: Scan QR code on part packaging

---

## 📦 Integration with Inventory

- Parts requested via technician → auto-create `ServiceRequiredPart` record
- Parts used → auto-create `ServicePartUsage` record
- Stock deducted from `technician_inventories` or main warehouse
- Parts returned → auto-create `ServicePartReturn` record

---

## 📊 Parts Usage Reporting

- Parts used per technician per day
- Parts cost per service
- Most frequently used parts
- Parts usage accuracy vs diagnosis

---

*Sparepart Usage — Sprint 36B*
