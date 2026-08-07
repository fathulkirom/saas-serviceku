# Natural Language Engine

> NL → Data Query transformation. Ask in Indonesian, get data instantly.

---

## 🗣️ Example Queries

| Natural Language | AI Translates To |
|-----------------|-----------------|
| "Tampilkan service yang belum dialokasikan" | `services WHERE technician_id IS NULL AND status != 'completed'` |
| "Pelanggan paling sering datang" | `customers ORDER BY service_count DESC LIMIT 10` |
| "Barang yang hampir habis" | `products WHERE stock <= min_stock` |
| "Profit bulan ini" | `SUM(revenue - cost) WHERE month = current` |
| "Teknisi paling produktif" | `technicians ORDER BY jobs_completed DESC` |
| "Supplier terbaik" | `suppliers ORDER BY on_time_delivery_pct DESC` |
| "Invoice belum dibayar" | `invoices WHERE payment_status = 'unpaid'` |
| "Asset yang overdue maintenance" | `assets WHERE next_maintenance < today` |
| "Project terlambat" | `projects WHERE progress < expected AND status = 'in_progress'` |

---

*Natural Language Engine — Sprint 28.0*
