# Marketplace Engine

> Multi-platform marketplace integration with order, inventory, price, and settlement sync.

---

## 🛍️ Supported Platforms

| Platform | Order Sync | Inventory Sync | Price Sync | Settlement |
|----------|-----------|---------------|------------|------------|
| Shopee | ✅ | ✅ | ✅ | ✅ |
| Tokopedia | ✅ | ✅ | ✅ | ✅ |
| Lazada | ✅ | ✅ | ✅ | ✅ |
| Blibli | ✅ | ✅ | ✅ | ✅ |
| TikTok Shop | ✅ | ✅ | ✅ | ✅ |

---

## 🔄 Sync Flow

```
Order placed on marketplace
  → Order imported (auto/manual)
  → Inventory updated (deduct stock)
  → Fulfillment: pack → ship → tracking
  → Status synced back to marketplace
  → Settlement: payment reconciled
```

---

*Marketplace Engine — Sprint 24.0*
