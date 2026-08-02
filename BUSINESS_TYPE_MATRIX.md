# BUSINESS TYPE MATRIX — ServiceKU v0.9.0-beta

## Available Business Types

| Key | Label | Services | Technician | Inventory | POS |
|-----|-------|:--------:|:----------:|:---------:|:---:|
| `full_service` | 🔧 Servis & Jual Sparepart | ✅ In-house | ✅ Required | ✅ | ✅ |
| `aksesoris_service` | 📱 Aksesoris + Terima Servis (Dilempar) | ✅ Dilempar | ❌ Optional | ✅ | ✅ |
| `aksespare_service` | 🛠️ Aksesoris & Sparepart + Ada Teknisi | ✅ In-house | ✅ Required | ✅ | ✅ |
| `gadget_full` | 💻 HP/Laptop/MacBook Baru & Second + Servis | ✅ In-house | ✅ Required | ✅ | ✅ |
| `retail_only` | 🏪 Jualan Saja (Tidak Terima Servis) | ❌ | ❌ | ✅ | ✅ |

## Setup Assistant Severity per Business Type

| Checklist Item | full_service | aksesoris_service | aksespare_service | gadget_full | retail_only |
|---------------|:-----------:|:-----------------:|:-----------------:|:-----------:|:-----------:|
| Nama Toko | 🔴 BLOCKING | 🔴 BLOCKING | 🔴 BLOCKING | 🔴 BLOCKING | 🔴 BLOCKING |
| Logo | ⚠️ WARNING | ⚠️ WARNING | ⚠️ WARNING | ⚠️ WARNING | ⚠️ WARNING |
| Cabang | 🔴 BLOCKING | 🔴 BLOCKING | 🔴 BLOCKING | 🔴 BLOCKING | ⚠️ WARNING |
| Gudang | 🔴 BLOCKING | 🔴 BLOCKING | 🔴 BLOCKING | 🔴 BLOCKING | ℹ️ INFO |
| Kas | 🔴 BLOCKING | 🔴 BLOCKING | 🔴 BLOCKING | 🔴 BLOCKING | 🔴 BLOCKING |
| Owner | 🔴 BLOCKING | 🔴 BLOCKING | 🔴 BLOCKING | 🔴 BLOCKING | 🔴 BLOCKING |
| CS | 🔴 BLOCKING | 🔴 BLOCKING | 🔴 BLOCKING | 🔴 BLOCKING | ℹ️ INFO |
| Teknisi | 🔴 BLOCKING | ⚠️ WARNING | 🔴 BLOCKING | 🔴 BLOCKING | ℹ️ INFO |
| Nomor Service | 🔴 BLOCKING | 🔴 BLOCKING | 🔴 BLOCKING | 🔴 BLOCKING | ℹ️ INFO |
| Nomor Invoice | ⚠️ WARNING | ⚠️ WARNING | ⚠️ WARNING | ⚠️ WARNING | ⚠️ WARNING |
| Printer Nota | ⚠️ WARNING | ⚠️ WARNING | ⚠️ WARNING | ⚠️ WARNING | ⚠️ WARNING |
| Checklist Penerimaan | ⚠️ WARNING | ⚠️ WARNING | ⚠️ WARNING | ⚠️ WARNING | ℹ️ INFO |

## Feature Dependencies

| Feature | Depends On | Blocked By |
|---------|-----------|------------|
| services | customers | retail_only |
| checklist | services | retail_only |
| work_on_services | services | aksesoris_service (limited) |
| technician_workflow | services + technician role | retail_only |
| inventory_mutations | products | — |
| pos_sales | products + customers | — |
| cash_register | sales | — |
| reporting | services + sales | — |

## Operational Characteristics

| Characteristic | Service Types | Retail Only |
|---------------|:------------:|:-----------:|
| Service Intake Flow | ✅ | ❌ |
| Technician Assignment | ✅ | ❌ |
| Diagnosis & Quotation | ✅ | ❌ |
| Part Request & Usage | ✅ | ❌ |
| QC & Delivery | ✅ | ❌ |
| Warranty Claims | ✅ | ❌ |
| POS Sales | ✅ | ✅ |
| Inventory Management | ✅ | ✅ |
| Customer Management | ✅ | ✅ |
| Supplier & Purchasing | ✅ | ✅ |
| Cash Register | ✅ | ✅ |
| Multi-Branch | ✅ | ✅ |
