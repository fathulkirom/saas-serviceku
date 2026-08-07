# POS, Sales & Omnichannel Commerce Architecture

> **Sprint 24.0** — Ninth ERP module, fully integrated Enterprise Platform.

---

## 🏗️ Architecture

```
POS & Omnichannel Module
├── Sales Management       → Data Engine (13 cols, 6 filters, 4 bulk actions)
├── POS Workspace          → Workspace Engine (12 tabs)
├── Sales Form             → Form Engine (26+ fields, 8 sections)
├── Payment Transactions   → Data Engine (8 cols, 2 filters, 2 bulk actions)
├── Promotion Engine       → Data Engine (10 cols, 3 filters, 3 bulk actions)
├── Loyalty Engine         → Data Engine (10 cols, 1 filter, 2 bulk actions)
├── Delivery Management    → Data Engine (10 cols, 2 filters, 3 bulk actions)
├── Returns Management     → Data Engine (10 cols, 3 filters, 3 bulk actions)
├── Marketplace Orders     → Data Engine (11 cols, 3 filters, 3 bulk actions)
├── Automation Engine      → 15 rules (sales lifecycle, payment, promotion, loyalty, marketplace)
├── Reporting Engine       → 15 reports (summary, detail, daily, POS closing, cash register, product, salesman, branch, promotion, marketplace, loyalty, profit, margin, tax, returns)
└── Dashboard Engine       → 3 widgets (SalesToday, OpenOrders, MarketplaceOrders)
```

---

## 🛒 POS Workspace (12 tabs)

| Tab | Content |
|-----|---------|
| Overview | KPI — sales today, transactions, avg basket, open orders, pending delivery, profit, top products, recent transactions, channels |
| Items | Product catalog with barcode, QR, search, favorites, quick keys |
| Payments | Cash, transfer, QRIS, debit, credit, e-wallet, split, installment |
| Customer | Customer lookup, loyalty points, membership tier, history |
| Promotion | Active promotions, auto-apply, coupons, vouchers |
| Delivery | Pickup, courier, tracking, proof of delivery |
| Invoice | Invoice generation, print, email, WhatsApp |
| Returns | Return, exchange, refund, store credit |
| Timeline | Sales activity timeline |
| Activity Log | Audit trail |
| Documents | Invoices, receipts, delivery notes |
| History | Full sales history |

---

## 🔗 Cross-Module Integration

| Module | Integration |
|--------|-------------|
| Service | Service sales, sparepart sales, warranty |
| Inventory | Stock movement, reservation, batch, serial, IMEI |
| Purchasing | Auto reorder, supplier performance |
| CRM | Loyalty, membership, customer history |
| Finance | Auto journal, AR, cash, tax, profit |
| HRM | Sales commission, attendance, shift |
| Asset | POS device, cash drawer, receipt printer |
| Project | Project billing, milestone invoice |

---

## 🔐 Role Matrix

| Role | POS Access |
|------|-----------|
| Owner | Full — all channels, reports |
| Manager | Full — all operational |
| Sales Manager | Team performance + promos |
| Cashier | POS + payments |
| Sales | Quotations + sales orders |
| Warehouse | Delivery + returns |
| Courier | Delivery only |
| Finance | Reports + profit/margin |
| Admin | Read-only |
| Customer Portal | Own orders + loyalty |

---

*POS Architecture — Sprint 24.0*
