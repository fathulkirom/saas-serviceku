# ServiceKU — Future Expansion

> **Sprint 6.1 · Blueprint Only.** Daftar perluasan domain di masa depan — **bukan kondisi saat ini**. Setiap perluasan tidak mengubah arsitektur inti; diaktifkan via Module/Feature/Permission Engine tanpa migrasi data besar.
> Selaras dengan `docs/specification/PROJECT_SPECIFICATION.md` §14 & `docs/architecture-engine/FutureRoadmap.md`.

---

## 1. Prinsip Perluasan

1. Domain/modul baru **wajib** melalui `docs/domain/` + Module Engine (registry) sebelum dikembangkan.
2. Tidak mengubah skema inti (Tenant, Service, Customer, Inventory, Finance).
3. Diaktifkan per tenant (plan/module) — bukan global.
4. Backward compatible — perluasan tidak memaksa migrasi data besar.

---

## 2. Perluasan Domain (Blueprint)

### 2.1 Organisasi & SDM
| Domain baru | Deskripsi | Engine terkait |
|---|---|---|
| Position → hierarki | jabatan bertingkat, laporan per posisi | Role Engine |
| Attendance / Absensi | kehadiran, shift, lembur | (HR target) |
| Payroll / Slip Gaji | gaji dari kompensasi & absensi | Compensation Engine |
| HRD (module) | data karyawan, kontrak, cuti | Module Engine |

### 2.2 Pelanggan & Loyalty
| Domain baru | Deskripsi | Engine terkait |
|---|---|---|
| Segmentasi Customer | grouping, tag, klasifikasi | Customer Engine |
| Loyalty / Poin | poin, reward, tier member | Customer Engine |
| Kredit Limit | batas hutang pelanggan | Customer Engine |
| Blacklist | daftar pelanggan dilarang | Customer Engine |
| Subscription pelanggan | servis rutin/berlangganan | Service Engine |

### 2.3 Pasca-Jual & Kualitas
| Domain baru | Deskripsi | Engine terkait |
|---|---|---|
| QC / Quality Check | pemeriksaan hasil servis | Service Engine |
| Rating / Review | penilaian servis & partner | Service/Partner |
| Garansi berbayar | jaminan tambahan | Warranty Engine |
| Asuransi perangkat | klaim asuransi | Warranty Engine |

### 2.4 Supply & Inventory
| Domain baru | Deskripsi | Engine terkait |
|---|---|---|
| Multi-gudang | penyimpanan per lokasi | Inventory Engine |
| Batch & Expiry | lot, tanggal kedaluwarsa | Inventory Engine |
| Barcode / QR | scan produk | Inventory Engine |
| Forecasting | prediksi kebutuhan stok | Inventory Engine |
| Auto-reorder | PO otomatis dari reorder | Supplier Engine |

### 2.5 Keuangan & Akuntansi
| Domain baru | Deskripsi | Engine terkait |
|---|---|---|
| Double-entry accounting | jurnal, buku besar | Finance Engine |
| Neraca & Arus Kas | laporan keuangan formal | Finance Engine |
| Pajak (PPN) | perhitungan pajak | Finance Engine |
| Rekonsiliasi bank | pencocokan bank | Finance Engine |

### 2.6 Integrasi & Ekosistem
| Domain baru | Deskripsi | Engine terkait |
|---|---|---|
| Public API | integrasi eksternal | Module Engine / Subscription |
| Webhook | event ke sistem lain | Module Engine |
| WhatsApp Integration | notifikasi & chat pelanggan | Service/Subscription |
| Marketplace (e-commerce) | sinkron produk & pesanan | Module Engine |
| Plugin System | ekstensi pihak ketiga | Module Engine |
| Module Marketplace | toko modul tambahan | Module Engine |

### 2.7 Otomasi & AI
| Domain baru | Deskripsi | Engine terkait |
|---|---|---|
| Automation Rules | aturan otomatis (notifikasi, tagihan) | Workflow Engine |
| AI Assistant | rekomendasi harga, ringkasan laporan | Module Engine / Subscription |
| AI Foto Servis | deteksi kerusakan dari foto | Service Engine |

---

## 3. Dampak Perluasan ke Domain Inti

| Domain inti | Dampak perluasan |
|---|---|
| Service Order | + QC, SLA, self-service tracking, AI |
| Customer | + segmentasi, poin, kredit limit |
| Inventory | + multi-gudang, batch, barcode |
| Finance | + akuntansi formal |
| Compensation | + payroll penuh |
| Warranty | + garansi berbayar, asuransi |
| Subscription | + kuota API/storage/AI |

---

## 4. Aturan

1. Perluasan **tidak boleh** menghilangkan/merusak invariant Business Reality chain.
2. Tiap perluasan punya **permission & feature** sendiri (3 lapis akses).
3. Didokumentasikan di sini SEBELUM implementasi — blueprint first.
4. Prioritas mengikuti nilai bisnis (lihat FutureRoadmap).

---

## 5. Verifikasi

Seluruh item di dokumen ini adalah **target/roadmap** — belum ada di source. Domain inti (dokumen lain) adalah kondisi saat ini yang sudah terkonfirmasi.
