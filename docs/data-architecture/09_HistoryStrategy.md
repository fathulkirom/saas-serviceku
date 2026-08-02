# 09 — History Strategy

> **Sprint 6.2A · Blueprint Only.** Strategi pelacakan perubahan data yang **bukan** transaksional — data master & konfigurasi yang berubah seiring waktu (policy, harga, customer, product).

---

## 1. Beda History vs Audit

| Aspek | History | Audit |
|---|---|---|
| **Tujuan** | Melacak **nilai** data yang berubah (versi). | Melacak **siapa** melakukan **apa**. |
| **Pertanyaan** | "Berapa harga produk ini bulan lalu?" | "Siapa yang mengubah harga?" |
| **Trigger** | Setiap update data master/policy. | Setiap aksi (termasuk read). |
| **Retensi** | Permanen (historis). | 7 tahun (lalu arsip). |

---

## 2. Strategi per Domain

| Domain | Strategi History | Penjelasan |
|---|---|---|
| **Policy** | **Versioning** | Setiap revisi = versi baru. Versi lama tetap berlaku untuk kompensasi historis. |
| **Product (harga)** | **Snapshot** | Setiap perubahan harga = snapshot harga lama. Laporan memakai harga saat transaksi. |
| **Customer** | **Change log** | Perubahan nama/telepon/alamat = log. Untuk deteksi perubahan data. |
| **Settings** | **Change log** | Perubahan pengaturan tenant dicatat. |
| **Role / Permission** | **Change log** | Perubahan role dicatat (audit sudah menangani siapa). |
| **Service Order** | **Status history** | Setiap transisi status = 1 baris history (sudah ada `ServiceHistory` di source). |
| **Inventory** | **Movement log** | Setiap mutasi = 1 baris permanen. Tidak ada "update" stok — selalu append movement. |

---

## 3. Konsep Snapshot (bukan SQL)

```
product_prices {
    product_id, price, cost_price, valid_from, valid_to (NULL = current)
}
```

- Harga saat ini = `valid_to IS NULL`.
- Harga historis = query range `valid_from <= transaksi_date < valid_to`.
- Tabel terpisah dari product — tidak mengubah `products` table.

---

## 4. Versioning Policy

```
policies {
    id, tenant_id, type, version, rules (JSON), valid_from, valid_to, status
}
```

- Setiap revisi policy = insert baru (version+1), set `valid_to` versi lama.
- Kompensasi/garansi yang sudah terjadi mengacu pada policy versi saat itu (immutable reference).

---

## 5. Aturan History

1. **Snapshot untuk data yang memengaruhi perhitungan finansial** — harga, policy, kompensasi.
2. **Change log untuk data master** — customer, product name, settings.
3. **Movement log untuk stok** — tidak ada update langsung qty; selalu append.
4. **Status history untuk transaksional** — Service, Sales, Request.
5. History tidak boleh dihapus/diubah — append-only (kecuali valid_to di-update saat versi baru).

---

## 6. Verifikasi

Konsisten dengan `docs/domain/DomainEvent.md` (Sprint 6.1 — event catalog), `docs/architecture-engine/WorkflowEngine.md` (Sprint 5.2). Policy versioning sesuai ADJ-09, ADJ-14 (Sprint 6.1A).
