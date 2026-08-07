# Payment Engine

> Multi-payment support: 10 payment methods, split payment, installment.

---

## 💳 Payment Methods

| Method | Description |
|--------|-------------|
| Cash | Physical cash |
| Transfer | Bank transfer |
| QRIS | QR code payment |
| Debit | Debit card |
| Credit Card | Credit card |
| E-Wallet | GoPay, OVO, Dana, ShopeePay |
| Split Payment | Multiple methods per transaction |
| Installment | Credit card installment |
| Store Credit | Customer store credit |
| Gift Card | Prepaid gift card |
| Voucher | Discount voucher |

---

## 🔄 Payment Flow

```
Customer selects items
  → Choose payment method(s)
  → Split payment? → Multiple methods
  → Process payment(s)
  → All paid? → Confirm sale
  → Partial? → Mark as partial payment
  → Generate invoice
```

---

*Payment Engine — Sprint 24.0*
