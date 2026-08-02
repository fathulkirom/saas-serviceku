# 08 — Marketplace Provider

> **Sprint 6.2B · Blueprint Only.** Provider integrasi marketplace — sinkronisasi order, produk, stok.

---

## 1. Daftar Provider

| Provider | Status | Kompleksitas | Target tenant |
|---|---|---|---|
| **Tokopedia** | Future | ⭐⭐⭐ | Toko berkembang, multi-cabang |
| **Shopee** | Future | ⭐⭐⭐ | Toko berkembang, multi-cabang |
| **TikTok Shop** | Future | ⭐⭐⭐⭐ | Toko berkembang |
| **Lazada** | Future | ⭐⭐⭐ | Multi-cabang, enterprise |
| **Future** (Bukalapak, Blibli, dll.) | Future | — | — |

---

## 2. Marketplace Interface (Blueprint)

```php
MarketplaceInterface {
    syncProducts(): void              // Sinkron produk dari marketplace
    syncOrders(): Order[]             // Tarik order baru
    updateStock(sku, qty): void       // Update stok di marketplace
    updateOrderStatus(orderId, status): void
    handleWebhook(payload): void
}
```

---

## 3. Flow (Blueprint)

```
Marketplace order masuk → webhook / polling
    → Request(type=marketplace) auto-created
    → fork → SalesOrder / ServiceOrder
    → status update → MarketplaceInterface::updateOrderStatus()
    → stok berkurang → MarketplaceInterface::updateStock()
```

---

## 4. Aturan

1. **Marketplace = modul opsional** (Module Engine) — tenant mengaktifkan bila jualan di marketplace.
2. **Satu tenant bisa multi-marketplace** — Tokopedia + Shopee bersamaan.
3. **Stok terpusat** — Inventory Engine mengelola stok; marketplace provider hanya menerima update.
4. **Semua marketplace = Future (P2)** — bukan prioritas 6.2; arsitektur siap.

---

## 5. Verifikasi

Konsisten dengan `docs/request-engine/04_RequestChannel.md` (Sprint 6.1D — channel marketplace), `docs/architecture-engine/ModuleEngine.md` (marketplace = future module).
