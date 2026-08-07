# POS Deprecation Strategy

> Plan for future POS/Sales module iterations & migration.

---

## ❌ Deprecated in Sprint 24

| Component | Reason | Replacement |
|-----------|--------|-------------|
| Manual cash register | Replaced by Payment Engine | `POSDefinitions::paymentTable()` |
| Paper promotion tracking | Replaced by Promotion Engine | `POSDefinitions::promotionTable()` |
| Manual loyalty cards | Replaced by Loyalty Engine | `POSDefinitions::loyaltyTable()` |
| Spreadsheet delivery log | Replaced by Delivery Engine | `POSDefinitions::deliveryTable()` |
| Ad-hoc marketplace tracking | Replaced by Marketplace Engine | `POSDefinitions::marketplaceTable()` |

---

## 🔮 Future Enhancements (Sprint 25+)

| Feature | Priority | Notes |
|---------|----------|-------|
| Thermal Printer Integration | P2 | Direct print to receipt printer |
| Cash Drawer Integration | P2 | Auto-open on cash sale |
| Barcode Scanner Hardware | P2 | USB/Bluetooth scanner |
| Customer-Facing Display | P3 | Secondary screen |
| Offline Mode | P3 | Queue + sync when online |
| Self-Checkout Kiosk | P4 | Customer self-service |
| WhatsApp Commerce Bot | P3 | Auto-order via WhatsApp |
| Instagram Shopping API | P4 | Direct Instagram integration |
| Loyalty Card (Physical) | P3 | NFC/QR physical card |
| Multi-Currency POS | P4 | Different currencies |

---

*POS Deprecation — Sprint 24.0*
