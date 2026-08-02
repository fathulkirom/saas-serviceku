# 15 — Multi-Branch Strategy

> **Sprint 6.2A · Blueprint Only.** Strategi data untuk operasional multi-cabang — data mana yang lintas cabang, mana yang per cabang.

---

## 1. Scope Data per Cabang

| Domain | Scope | Bisa lintas cabang? | Aturan |
|---|---|---|---|
| **Customer** | Tenant | ✅ (shared) | Customer bisa servis di cabang mana pun |
| **Device** | Tenant | ✅ (shared) | Device bisa diservis di cabang mana pun |
| **Request** | Tenant + Branch asal + Pickup branch (BR-001) | ✅ | Request mencatat `branch_id` (asal) + `pickup_branch_id` |
| **Service Order** | Branch (pengerjaan) | ❌ (per cabang) | Service dikerjakan di satu cabang; pickup bisa beda cabang |
| **Sales Order** | Branch (transaksi) | ❌ (per cabang) | Penjualan terjadi di cabang tertentu |
| **Purchase Order** | Tenant | ✅ | PO bisa untuk stok cabang mana pun |
| **Supplier** | Tenant | ✅ (shared) | Supplier shared antar cabang |
| **Product** | Tenant | ✅ (shared) | Katalog shared; stok per cabang |
| **Inventory** | Branch | ❌ (per cabang) | Stok terpisah per cabang; transfer antar cabang dicatat |
| **Cash Shift / Deposit** | Branch | ❌ (per cabang) | Kas per cabang |
| **User** | Tenant (bisa ditugaskan ke cabang) | ✅ | User bisa bekerja di beberapa cabang (opsional) |
| **Policy** | Tenant | ✅ | Policy berlaku untuk semua cabang (kecuali override per cabang — future) |
| **Warranty** | Tenant | ✅ | Garansi mengikuti Service Order (tidak terikat cabang) |
| **Finance** | Tenant (agregat) + Branch (detail) | ✅ | Agregat finance bisa per cabang atau gabungan |

---

## 2. Transfer Antar Cabang

| Transfer | Mekanisme | Data |
|---|---|---|
| **Stok / Sparepart** | Inventory Movement: `transfer_out` (cabang asal) + `transfer_in` (cabang tujuan) | Dua baris movement; approval (target) |
| **Device (BR-001)** | Request dengan `pickup_branch_id` berbeda dari `branch_id` | Request mencatat kedua cabang; PickupTask + DeliveryTask |
| **User assignment** | User bisa di-assign ke >1 cabang (opsional) | `user_branch` pivot |

---

## 3. Laporan Multi-Cabang

| Laporan | Scope |
|---|---|
| Pendapatan per cabang | Branch (filter) |
| Pendapatan gabungan | Tenant (agregasi SUM per branch) |
| Stok per cabang | Branch |
| Stok gabungan | Tenant (agregasi) |
| Performa teknisi | Branch atau gabungan |

---

## 4. Aturan

1. **Stok & kas = per cabang** — tidak boleh campur tanpa transfer tercatat.
2. **Customer & Device = tenant-wide** — bisa servis di cabang mana pun.
3. **Multi-branch** = fitur plan Pro+ (`multi_branch` feature).
4. **Tenant 1 cabang (Basic)** = `branch_id` tetap ada (default) — struktur data tidak berubah.
5. **Cluster stock (BR-005)** = future (P2) — StockCluster/Gudang; additive, tidak mengubah struktur inti.

---

## 5. Verifikasi

Konsisten dengan `docs/domain/DomainRelationship.md` (Sprint 6.1), `docs/domain-validation/BusinessRealityValidation.md` (BR-001, BR-005), `docs/architecture-engine/SubscriptionEngine.md` (plan multi_branch).
