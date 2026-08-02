# ServiceKU — Domain Service

> **Sprint 6.1 · Blueprint Only.** **Domain Service** = logika bisnis yang **tidak alami** ditempatkan di Entity/Value Object (lintas aggregate / melibatkan banyak domain). Stateless, operasi murni, tanpa menyimpan data.
> Blueprint — bukan implementasi.

---

## 1. Prinsip Domain Service

- Menampung **proses bisnis** yang melibatkan beberapa aggregate.
- **Stateless** — tidak menyimpan state; hasil = fungsi dari input.
- Tidak mengelola data langsung (lewat Repository).
- Berbeda dari **Application Service** (orchestrator UI/API) dan **Infrastructure Service** (email, payment).

---

## 2. Daftar Domain Service

| # | Domain Service | Input | Output | Alami di sini karena… |
|---|---|---|---|---|
| 1 | **ServiceOrderingService** | Visit, Device, Customer, perkiraan biaya | Service Order + status awal | menyatukan Visit/Device/Inventory untuk membuat tiket |
| 2 | **ServicePricingService** | jenis servis, sparepart, policy harga | estimasi biaya jasa + part | menghitung harga lintas Policy & katalog |
| 3 | **StockAllocationService** | ServiceOrder, Inventory per branch | alokasi/kurangi sparepart, mutasi | menjaga invariant stok lintas WO/Service |
| 4 | **WarrantyEligibilityService** | ServiceOrder selesai, Policy garansi | masa garansi / tolak | menilai syarat garansi dari policy |
| 5 | **SupplierClaimService** | Warranty, Supplier, Claim | Supplier Claim + status | mengelola klaim lintas Warranty/Supplier |
| 6 | **ReplacementService** | Claim, Inventory | Replacement + mutasi stok | memetakan klaim → penggantian → stok |
| 7 | **CompensationCalculatorService** | ServiceOrder, Policy, User | Compensation (nominal + dasar) | menghitung kompensasi mengikuti policy |
| 8 | **CashSettlementService** | CashShift, Deposit, Expense | selisih kas, setoran final | menutup shift & merekonsiliasi kas |
| 9 | **PaymentGatewayService** | Sales/Subscription, metode | status pembayaran (pending/success/failed/expired) | integrasi payment eksternal (sudah ada di source) |
| 10 | **DepositConfirmService** | Deposit | status konfirmasi | hanya owner/admin |
| 11 | **SubscriptionEnforcementService** | Tenant, Feature, aksi | izin/tolak (full/read_only/none) | menegakkan plan feature (CheckPlanFeature) |
| 12 | **ReportAggregationService** | data transaksi, filter | laporan agregat | mengagregasi lintas modul |
| 13 | **TenantProvisioningService** | registrasi tenant, business type | DB tenant + onboarding template | provisioning multi-tenant |
| 14 | **TransferStockService** | asal branch, tujuan branch, item | mutasi transfer + approval | transfer lintas branch |

---

## 3. Pemetaan ke Source

| Domain Service | Kondisi saat ini (source) | Target |
|---|---|---|
| PaymentGatewayService | ✅ ada (`app/Services`) | — |
| SubscriptionEnforcementService | ✅ ada (`CheckPlanFeature`, `CheckSubscription`) | → Subscription Engine |
| StockAllocationService | ⚠️ sebagian (mutasi stok di controller) | → Inventory Engine / Service Engine |
| CompensationCalculatorService | ❌ belum ada (Compensation = target) | → Compensation Engine |
| WarrantyEligibilityService | ⚠️ sebagian (garansi di source, detail policy target) | → Warranty Engine |
| Lainnya | belum terpisah (logika di controller) | dipisah bertahap |

---

## 4. Aturan Domain Service

1. Satu Domain Service = **satu tanggung jawab proses bisnis**.
2. Tidak menyimpan state; tidak mengubah data tanpa lewat Repository + Aggregate Root.
3. Menghasilkan **Domain Event** untuk efek samping (mis. `StockAdjusted`, `CompensationCalculated`).
4. Nama: kata kerja/domain + `Service` (konsisten `docs/Naming.md`).
5. Jangan mengimplementasikan sekarang — blueprint hanya menetapkan tanggung jawab.

---

## 5. Verifikasi

PaymentGatewayService, Job (GenerateInvoicePdf), middleware plan/subscription terkonfirmasi ada. Katalog Domain Service di atas adalah **blueprint target** — pemisahan dari controller dilakukan bertahap (konsisten ArchitectureDecision).
