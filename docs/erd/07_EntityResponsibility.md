# 07 — Entity Responsibility

> **Sprint 6.2C · Conceptual Blueprint.** Untuk setiap entity: Mengapa ada? Aggregate Root? Owner? Lifecycle? Business Responsibility? Wajib/Opsional? Future Expansion?
> Format ringkas — 33 entity.

---

## Entity Catalog

### E01 — tenants
- **Mengapa ada?** Fondasi multi-tenant. Setiap tenant = 1 DB. Isolasi data penuh.
- **Aggregate Root?** ✅
- **Owner:** Super Admin (buat); Owner (kelola pengaturan tenant).
- **Lifecycle:** registrasi→trial→active→expired/suspended→nonaktif→arsip.
- **Business Responsibility:** Menyimpan identitas tenant, business type, status, branding.
- **Wajib?** ✅ (platform)
- **Future:** Custom domain, tenant hierarchy (franchise).

### E02 — branches
- **Mengapa ada?** Multi-cabang (BR-001). Stok & kas per cabang.
- **Aggregate Root?** ✅
- **Owner:** Owner (`manage_branches`).
- **Lifecycle:** dibuat→aktif→nonaktif.
- **Business Responsibility:** Unit operasional dengan stok, kas, dan user assignment.
- **Wajib?** ✅ (minimal 1, default branch).
- **Future:** StockCluster/Gudang (BR-005, P2).

### E03 — users
- **Mengapa ada?** Aktor dalam tenant: Owner, Admin, Manager, CS, Teknisi, Kasir.
- **Aggregate Root?** ✅
- **Owner:** Owner (`manage_users`).
- **Lifecycle:** diundang→aktif→suspended→nonaktif.
- **Business Responsibility:** Identitas, role, credentials. Minimal 1 owner aktif.
- **Wajib?** ✅
- **Future:** Multi-role (user_role pivot), specialization (BR-006), delegation (BR-011).

### E04 — roles
- **Mengapa ada?** Kumpulan permission. 7 role resmi + kustom (target).
- **Aggregate Root?** ✅
- **Owner:** Platform (seed); Owner (kustom, target).
- **Lifecycle:** seed→(target) buat→aktif→nonaktif.
- **Business Responsibility:** Menentukan permission user.
- **Wajib?** ✅
- **Future:** Role kustom, merge role.

### E05 — permissions
- **Mengapa ada?** Pusat otorisasi. Permission atomic = `module.action`.
- **Aggregate Root?** ✅
- **Owner:** Platform (registry).
- **Lifecycle:** didaftarkan→aktif.
- **Business Responsibility:** Setiap akses dicek via permission, bukan nama role.
- **Wajib?** ✅
- **Future:** Permission granular (row-level, branch-level).

### E06 — positions
- **Mengapa ada?** Jabatan struktural (pelengkap role). Role = fungsional; Position = struktural.
- **Aggregate Root?** ✅
- **Owner:** Owner (target).
- **Lifecycle:** dibuat→aktif→nonaktif.
- **Business Responsibility:** Hierarki organisasi; laporan per posisi.
- **Wajib?** ❌ (opsional, target).
- **Future:** Hierarchy, approval chain.

### E07 — customers
- **Mengapa ada?** Pelanggan = sumber bisnis. Data PII (L3).
- **Aggregate Root?** ✅
- **Owner:** Tenant; dikelola CS/Admin.
- **Lifecycle:** dibuat→aktif→inactive/blacklist→arsip. Tidak hard delete.
- **Business Responsibility:** Identitas pelanggan, kontak, histori.
- **Wajib?** ✅
- **Future:** Segmentasi, loyalitas, customer portal.

### E08 — devices
- **Mengapa ada?** Perangkat yang diservis. IMEI/serial = identitas unik.
- **Aggregate Root?** ✅
- **Owner:** Tenant; dikelola CS/Admin.
- **Lifecycle:** didaftarkan→aktif→ganti pemilik/arsip. Tidak hard delete jika berriwayat.
- **Business Responsibility:** Riwayat servis perangkat; lifetime cost (BR-014).
- **Wajib?** ✅
- **Future:** Device model compatibility, part otomatis, IoT telemetry.

### E09 — requests
- **Mengapa ada?** **ADR-001** — Core Entry Point tunggal. Semua interaksi operasional masuk sebagai Request.
- **Aggregate Root?** ✅
- **Owner:** Tenant; dibuat oleh CS/Admin/Owner/System/API/Customer.
- **Lifecycle:** 14 status (created→processing→completed→closed/cancelled). Sprint 6.1D.
- **Business Responsibility:** Funnel semua channel (walk-in, pickup, WA, marketplace, API). Fork ke domain turunan.
- **Wajib?** ✅
- **Future:** AI auto-classify, customer self-service.

### E10 — request_history
- **Mengapa ada?** Audit trail Request. Append-only.
- **Aggregate Root?** ❌ (Child of Request)
- **Owner:** System (auto).
- **Lifecycle:** dibuat setiap perubahan status → permanen.
- **Business Responsibility:** Data Is Sacred; BR-015 (Human Error reversal).
- **Wajib?** ✅

### E11 — request_devices (pivot)
- **Mengapa ada?** N:M Request↔Device. BR-019.
- **Aggregate Root?** ❌ (Pivot)
- **Owner:** System.
- **Business Responsibility:** Hubungkan 1 Request dengan N Device.
- **Wajib?** ✅

### E12 — service_orders
- **Mengapa ada?** Core domain — tiket servis dengan 14 status.
- **Aggregate Root?** ✅
- **Owner:** Tenant; CS/Admin buat; Teknisi kerja.
- **Lifecycle:** menunggu_alokasi→...→diambil/close. 14 status.
- **Business Responsibility:** Eksekusi servis; biaya; part; checklist.
- **Wajib?** ✅ (setelah fork dari Request)
- **Future:** SLA, estimasi waktu, self-service tracking.

### E13 — work_orders
- **Mengapa ada?** Sub-pekerjaan dalam ServiceOrder. Progressive (BR-018).
- **Aggregate Root?** ❌ (Child of ServiceOrder)
- **Owner:** Teknisi/Admin.
- **Lifecycle:** dibuka→dikerjakan→selesai.
- **Business Responsibility:** Memisahkan pekerjaan bertahap dalam satu tiket.
- **Wajib?** ❌ (opsional, target).

### E14 — checklists
- **Mengapa ada?** Checklist perangkat servis (non-retail).
- **Aggregate Root?** ❌ (Child of ServiceOrder)
- **Owner:** Teknisi.
- **Lifecycle:** diisi saat servis.
- **Business Responsibility:** Memastikan semua item diperiksa.
- **Wajib?** ❌ (opsional, non-retail).

### E15 — technician_assignments
- **Mengapa ada?** Assignment history teknisi. BR-006 (specialization).
- **Aggregate Root?** ❌ (Child of ServiceOrder)
- **Owner:** CS/Admin (assign); System (auto-assign target).
- **Lifecycle:** di-assign→selesai.
- **Business Responsibility:** Tracking siapa mengerjakan apa; multi-teknisi per tiket.
- **Wajib?** ❌ (opsional).

### E16 — inventory_items
- **Mengapa ada?** Stok produk per cabang.
- **Aggregate Root?** ✅
- **Owner:** System (auto); Owner/Admin (adjust).
- **Lifecycle:** dibuat saat produk ada stok→qty berubah via movement→(future) discontinued.
- **Business Responsibility:** Stok akurat; tidak negatif; ready untuk cluster (BR-005, P2).
- **Wajib?** ✅

### E17 — inventory_movements
- **Mengapa ada?** Append-only jejak mutasi. Data Is Sacred.
- **Aggregate Root?** ❌ (Child of InventoryItem)
- **Owner:** System (auto).
- **Lifecycle:** dibuat→permanen.
- **Business Responsibility:** Setiap mutasi tercatat. Qty = sum(movements).
- **Wajib?** ✅

### E18 — suppliers
- **Mengapa ada?** Pemasok sparepart; sumber Purchase & SupplierClaim.
- **Aggregate Root?** ✅
- **Owner:** Owner/Admin/Manager.
- **Business Responsibility:** Data supplier, saldo hutang.
- **Wajib?** ❌ (opsional; tenant tanpa pembelian = tidak perlu).

### E19 — purchase_orders
- **Mengapa ada?** Pembelian ke supplier; stok masuk; hutang.
- **Aggregate Root?** ✅
- **Owner:** Owner/Admin/Manager.
- **Lifecycle:** draft→PO→terima→bayar→close.
- **Business Responsibility:** Stok bertambah; hutang tercatat.
- **Wajib?** ❌ (opsional).

### E20 — sales_orders
- **Mengapa ada?** Transaksi penjualan (POS).
- **Aggregate Root?** ✅
- **Owner:** Kasir/Owner/Admin; void: Owner/Admin.
- **Lifecycle:** draft→selesai→pending→success/failed/expired→refunded/void.
- **Business Responsibility:** Stok keluar; kas bertambah.
- **Wajib?** ❌ (opsional; tenant tanpa POS tidak perlu).

### E21 — cash_shifts
- **Mengapa ada?** Shift kasir; buka/tutup kas.
- **Aggregate Root?** ✅
- **Owner:** Kasir; Owner/Admin (konfirmasi deposit).
- **Lifecycle:** buka→transaksi→tutup→final.
- **Business Responsibility:** Rekonsiliasi kas; tidak boleh 2 shift terbuka.
- **Wajib?** ❌ (opsional).

### E22 — payments (pada sales_orders)
- **Mengapa ada?** Status pembayaran (pending/success/failed/expired/refunded).
- **Aggregate Root?** ❌ (Child of SalesOrder / Subscription).
- **Owner:** System (auto dari gateway) / Kasir (cash).
- **Business Responsibility:** Jejak pembayaran; hybrid multi-payment.
- **Wajib?** ✅ (jika ada SalesOrder).

### E23 — warranties
- **Mengapa ada?** Garansi dari servis selesai.
- **Aggregate Root?** ✅
- **Owner:** CS/Admin/Owner.
- **Lifecycle:** aktif→diklaim→resolved/expired.
- **Business Responsibility:** Jaminan pasca-servis; periode mengikuti policy.
- **Wajib?** ❌ (opsional; hanya service selesai).

### E24 — warranty_claims
- **Mengapa ada?** Klaim garansi. BR-012.
- **Aggregate Root?** ❌ (Child of Warranty)
- **Owner:** CS/Admin.
- **Lifecycle:** dibuat→evaluasi→diterima/ditolak→resolved.
- **Business Responsibility:** Resolution type (re-service/replacement/refund/reject).
- **Wajib?** ❌ (opsional).

### E25 — replacements
- **Mengapa ada?** Barang pengganti dari supplier claim. BR-013.
- **Aggregate Root?** ❌ (Child of SuplierClaim)
- **Owner:** System/Admin.
- **Lifecycle:** dibuat→stok masuk.
- **Business Responsibility:** Inventori bertambah; finance tercatat.
- **Wajib?** ❌ (opsional, target).

### E26 — compensations
- **Mengapa ada?** Kompensasi teknisi/karyawan. BR-016.
- **Aggregate Root?** ✅
- **Owner:** Owner/Admin/Manager.
- **Lifecycle:** event→hitung→approval→bayar→selesai.
- **Business Responsibility:** Mengikuti policy; tercatat di finance.
- **Wajib?** ❌ (opsional, target).

### E27 — policies
- **Mengapa ada?** Aturan bisnis sebagai data. Configuration over Code.
- **Aggregate Root?** ✅
- **Owner:** Owner.
- **Lifecycle:** dibuat→aktif→revisi (versi baru)→nonaktif.
- **Business Responsibility:** Kompensasi, garansi, harga, human error — semua via policy.
- **Wajib?** ❌ (opsional, target — krusial).

### E28 — attachments
- **Mengapa ada?** Lampiran polymorphic untuk semua domain.
- **Aggregate Root?** ❌ (Polymorphic child)
- **Owner:** User yang upload; entity yang dilampiri.
- **Lifecycle:** upload→aktif→soft delete (bersama entity induk).
- **Business Responsibility:** Foto, PDF, dokumen. Sprint 6.2A §07 + Sprint 6.2B §03.
- **Wajib?** ❌ (opsional).
- **Future:** Voice note, video.

### E29 — provider_credentials
- **Mengapa ada?** Kredensial provider eksternal. Sprint 6.2B.
- **Aggregate Root?** ❌ (Child of Tenant)
- **Owner:** Owner.
- **Lifecycle:** dibuat→aktif→rotated→dihapus.
- **Business Responsibility:** Simpan API key terenkripsi per tenant.
- **Wajib?** ❌ (opsional; hanya jika tenant mengaktifkan cloud provider).

### E30 — notifications
- **Mengapa ada?** Notifikasi ke user internal & customer.
- **Aggregate Root?** ✅
- **Owner:** System (auto).
- **Lifecycle:** dibuat→terkirim/gagal.
- **Business Responsibility:** Komunikasi status; multi-channel (Browser, Email, WA, SMS).
- **Wajib?** ❌ (opsional).

### E31 — dashboard_widgets
- **Mengapa ada?** Widget dashboard per user. Permission-based.
- **Aggregate Root?** ❌ (Child of User)
- **Owner:** User (preferensi); Owner (default widget).
- **Business Responsibility:** Tampilkan data agregat sesuai permission.
- **Wajib?** ❌ (opsional).

### E32 — subscriptions
- **Mengapa ada?** Paket & status langganan tenant.
- **Aggregate Root?** ✅
- **Owner:** Owner (bayar); Super Admin (override).
- **Lifecycle:** trial→active→expired/suspended→active.
- **Business Responsibility:** Kontrol akses fitur & batas.
- **Wajib?** ✅ (setiap tenant punya subscription).

### E33 — audit_logs & history_logs
- **Mengapa ada?** Jejak audit & perubahan data. Data Is Sacred.
- **Aggregate Root?** N/A (Append-only log, bukan aggregate).
- **Owner:** System (auto).
- **Lifecycle:** dibuat→permanen.
- **Business Responsibility:** Compliance, investigasi, reversal.
- **Wajib?** ✅ (audit); ❌ (history opsional).

---

## Verifikasi

33 entity dianalisis. Setiap entity memiliki alasan bisnis, lifecycle, dan ownership yang jelas. Konsisten dengan seluruh Sprint 6.1–6.2B.
