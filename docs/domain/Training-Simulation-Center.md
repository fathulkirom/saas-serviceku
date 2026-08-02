# Training & Simulation Center — Blueprint

> **Status**: 🟡 FUTURE MODULE · **Implementation**: DEFERRED  
> **Roadmap**: Sprint 8.x (bukan Sprint 7.x)  
> **Priority**: Medium  
> **Blueprint Version**: ServiceKU v1.0 (Frozen)  
> **Decision**: Implementasi ditunda — Sprint 7.3 Customer Engine tetap prioritas utama.

---

## 1. Product Vision

ServiceKU bukan hanya ERP.

ServiceKU adalah **platform lengkap** untuk bisnis servis elektronik yang mencakup:

- ✅ **Operasional** — Request, Service, Sales, Inventory, Finance
- 🟡 **Training** — Onboarding, SOP, Simulasi, Sertifikasi
- 🟡 **Knowledge Management** — SOP interaktif, Video tutorial, FAQ, Best Practice
- 🟡 **Employee Development** — Learning path, Assessment, Badge, Leaderboard
- 🔮 **AI Coaching** — Petunjuk kontekstual, Evaluasi otomatis, Latihan adaptif

**Masalah Nyata di Lapangan:**

Ketika toko servis menerima karyawan baru:
- Takut salah klik — "Kalau aku pencet ini, nanti rusak nggak?"
- Takut merusak transaksi — "Ini beneran atau cuma latihan?"
- Takut menghapus data — "Data customer production ilang gimana?"
- Belum paham SOP — "Langkahnya gimana ya setelah checking?"
- Owner tidak berani memberi akses penuh — "Nanti dulu, belajar dulu 2 minggu"

Akibatnya:
- Onboarding 2–4 minggu (terlalu lama)
- Produktivitas turun (senior harus mendampingi terus)
- Risiko human error tinggi (salah klik di production)
- Karyawan baru stress (takut membuat kesalahan)

**Solusi**: Training & Simulation Center — lingkungan sandbox terisolasi penuh yang menggunakan **engine production yang sama**, dengan **data terpisah**. Karyawan berlatih menggunakan workflow nyata tanpa risiko terhadap data production. Setelah lulus assessment, owner menyetujui, dan akun production diaktifkan.

---

## 2. Business Goals

| # | Goal | Measured By |
|---|------|-------------|
| 1 | Onboarding karyawan baru dalam 1–3 hari | Time-to-productivity |
| 2 | Training SOP standar untuk semua role | Training completion rate |
| 3 | Demo tenant untuk calon pelanggan | Conversion rate |
| 4 | Workshop dan simulasi kasus nyata | Scenario completion |
| 5 | Sertifikasi internal teknisi | Assessment pass rate |
| 6 | Franchise training — seluruh cabang seragam | Cross-branch completion |
| 7 | Refresh training berkala | Certification renewal rate |
| 8 | Knowledge base terintegrasi dengan workflow | Article usage in workflow |

---

## 3. Module Scope

### 3.1 Core Training Modules

| Module | Description |
|--------|-------------|
| **Training Workspace** | Lingkungan latihan terisolasi per role |
| **Sandbox Workspace** | Database terpisah, data dummy, bisa di-reset |
| **Simulation Mode** | Mode simulasi yang menggunakan workflow engine production |
| **Scenario Builder** | Trainer membuat skenario langkah-demi-langkah |
| **Learning Center** | Portal belajar dengan course, lesson, video |
| **Quiz Engine** | Multiple choice, true/false, essay singkat |
| **Assessment** | Ujian praktik — menyelesaikan skenario tanpa hints |
| **Certification** | Sertifikat internal dengan masa berlaku |
| **Trainer Dashboard** | Monitor semua trainee, progress, nilai |
| **Training Progress** | Progress bar per module, time tracking |
| **Training Report** | Report completion, score, certification status |
| **Knowledge Base** | Artikel SOP yang terintegrasi dengan workflow |
| **Video Tutorial** | Video panduan per role |
| **Checklist** | Checklist interaktif untuk setiap prosedur |
| **Interactive SOP** | SOP dengan step-by-step yang bisa diklik |
| **FAQ** | Frequently Asked Questions per module |
| **Best Practice** | Tips dan trik dari tenant expert |

---

## 4. Sandbox Concept

### 4.1 Isolation Principle

Training Workspace harus **benar-benar terisolasi**.

| Aspek | Production | Sandbox |
|-------|-----------|---------|
| Database | Data nyata tenant | Data training terpisah |
| Customer | Customer real | Customer dummy |
| Device | Device real | Device dummy |
| Transaksi | Uang nyata | Simulasi |
| Stok | Stok real | Stok dummy |
| Kas | Kas real | Kas virtual |
| Laporan | Laporan bisnis | Report training |
| WhatsApp | Kirim ke customer real | Kirim ke trainer / log |
| Email | Kirim ke email real | Kirim ke trainer |
| Event Log | event_logs production | event_logs training |
| Workflow | Workflow production | Workflow production (engine sama, data beda) |

### 4.2 Yang BOLEH Dilakukan di Sandbox

- ✅ Membuat Request
- ✅ Membuat Service
- ✅ Membuat Sales
- ✅ Membuat Customer (dummy)
- ✅ Membuat Device (dummy)
- ✅ Memakai Workflow (transisi state)
- ✅ Memakai Automation (rule jalan)
- ✅ Upload foto (ke folder training)
- ✅ Cetak invoice (simulasi)
- ✅ Pembayaran (simulasi)
- ✅ Semua fitur production — tapi di sandbox

### 4.3 Yang TIDAK BOLEH Dilakukan di Sandbox

- ❌ Mengubah transaksi production
- ❌ Mengubah stok production
- ❌ Mengubah kas production
- ❌ Mengubah laporan production
- ❌ Mengubah customer production
- ❌ Mengubah device production
- ❌ Mengirim WhatsApp ke customer real
- ❌ Mengirim email ke customer real
- ❌ Memproses pembayaran nyata
- ❌ Mencampur data production dengan training

---

## 5. Training Scenarios (per Role)

### 5.1 Customer Service (CS)

```
Scenario: "Menerima HP Rusak — Full Flow"

Step 1: Input customer (nama, telepon, alamat)
Step 2: Input device (brand, model, IMEI, keluhan)
Step 3: Buat Request — deskripsikan kerusakan
Step 4: Checklist kondisi fisik (layar, body, charging)
Step 5: Cetak tanda terima
Step 6: Alokasi ke teknisi
Step 7: Komunikasi WhatsApp ke customer (simulasi)
Step 8: Update status setelah teknisi selesai
Step 9: Konfirmasi ke customer — siap diambil
Step 10: Serah terima device

Expected: Semua transisi workflow valid
Time limit: 15 menit
Passing score: 80%
```

### 5.2 Technician

```
Scenario: "LCD Rusak — Diagnosa dan Perbaikan"

Step 1: Terima Work Order dari CS
Step 2: Diagnosa — cek LCD, flexibel, konektor
Step 3: Catat hasil diagnosa di repair notes
Step 4: Upload foto kerusakan
Step 5: Estimasi biaya dan waktu
Step 6: Tambah sparepart (LCD assembly)
Step 7: Kerjakan perbaikan
Step 8: QC — tes fungsi LCD, touchscreen, brightness
Step 9: QC passed → selesai
Step 10: Update Work Order → Done

Expected: Semua transisi valid, foto terupload
Time limit: 20 menit
Passing score: 85%
```

### 5.3 Cashier

```
Scenario: "Pembayaran Servis + Invoice"

Step 1: Buka Service yang sudah selesai
Step 2: Buat invoice — biaya servis + sparepart
Step 3: Terima pembayaran (cash/transfer simulasi)
Step 4: Cetak invoice
Step 5: Berikan garansi servis
Step 6: Tutup transaksi

Expected: Invoice tercetak, status payment lunas
Time limit: 10 menit
Passing score: 90%
```

### 5.4 Warehouse

```
Scenario: "Mutasi Stok Sparepart"

Step 1: Cek stok LCD assembly
Step 2: Buat purchase order (jika stok kurang)
Step 3: Terima barang dari supplier
Step 4: Update stok
Step 5: Transfer stok ke cabang lain (jika diperlukan)

Expected: Stok bertambah sesuai PO
Time limit: 15 menit
Passing score: 80%
```

### 5.5 Owner

```
Scenario: "Dashboard Monitoring"

Step 1: Buka Owner Dashboard
Step 2: Cek KPI hari ini
Step 3: Review servis yang overdue
Step 4: Approve quotation yang pending
Step 5: Cek laporan keuangan harian
Step 6: Review performa teknisi

Expected: Dapat membaca semua KPI
Time limit: 10 menit
Passing score: 80%
```

---

## 6. Learning Management System (LMS)

Training Center bukan hanya sandbox — tetapi juga **LMS bawaan**.

### 6.1 Course Structure

```
Course: "Service Center Operation"
  │
  ├── Module 1: Customer Service Fundamentals
  │     ├── Lesson 1.1: Menerima Customer
  │     ├── Lesson 1.2: Membuat Request
  │     ├── Lesson 1.3: Checklist Device
  │     ├── Video: "Cara Menerima HP Rusak"
  │     ├── SOP Interaktif: "Alur CS"
  │     └── Quiz: "CS Fundamentals"
  │
  ├── Module 2: Technician Repair
  │     ├── Lesson 2.1: Diagnosa Kerusakan
  │     ├── Lesson 2.2: Sparepart Management
  │     ├── Lesson 2.3: QC Procedure
  │     ├── Video: "Teknik Solder Dasar"
  │     └── Quiz: "Repair Fundamentals"
  │
  └── Module 3: Assessment Praktik
        ├── Skenario: LCD Replacement
        ├── Skenario: Battery Replacement
        └── Skenario: Motherboard Repair
```

### 6.2 Content Types

| Type | Description | Example |
|------|-------------|---------|
| **Lesson** | Materi teks dengan gambar | "Cara Membuat Request" |
| **Video** | Video tutorial internal | "Tutorial POS — 5 menit" |
| **SOP Interaktif** | SOP dengan step-by-step yang bisa diklik | "Alur Servis — klik每一步" |
| **Quiz** | Soal pilihan ganda / true-false | "Apa langkah setelah checking?" |
| **Assessment** | Ujian praktik di sandbox | "Selesaikan skenario tanpa hints" |
| **Checklist** | Checklist interaktif | "Checklist QC — 10 item" |
| **FAQ** | Pertanyaan umum per module | "Bagaimana cara indent part?" |
| **Best Practice** | Tips dari tenant expert | "Tips: Foto sebelum dan sesudah" |

### 6.3 Progress & Scoring

| Metric | Description |
|--------|-------------|
| **Progress** | Persentase course yang sudah diselesaikan (%) |
| **Score** | Nilai quiz dan assessment (0–100) |
| **Time** | Waktu yang dihabiskan per lesson |
| **Attempts** | Berapa kali mencoba assessment |
| **Badge** | Penghargaan otomatis (First Complete, Perfect Score, Speed Runner) |
| **Leaderboard** | Peringkat antar trainee (opsional — per tenant) |

### 6.4 Certification

| Tier | Criteria | Validity |
|------|----------|----------|
| **Basic** | Lulus semua quiz (≥80%) | 12 bulan |
| **Advanced** | Basic + lulus assessment praktik (≥85%) | 12 bulan |
| **Expert** | Advanced + menyelesaikan 50+ servis production dengan rating baik | 24 bulan |

Sertifikat berisi:
- Nama karyawan
- Module yang diselesaikan
- Tier (Basic/Advanced/Expert)
- Score
- Tanggal issue
- Tanggal expired
- Nomor sertifikat unik
- QR code verifikasi

---

## 7. Trainer Capabilities

Trainer dapat:

| Action | Description |
|--------|-------------|
| **Create Course** | Membuat course dengan module, lesson, video |
| **Create Quiz** | Membuat soal pilihan ganda, true/false, essay |
| **Create SOP** | Membuat SOP interaktif step-by-step |
| **Create Scenario** | Membuat skenario training dengan expected actions |
| **Assign Training** | Assign course ke karyawan tertentu |
| **View Progress** | Melihat progress semua trainee |
| **View Scores** | Melihat nilai quiz dan assessment |
| **Issue Certificate** | Menerbitkan sertifikat setelah lulus |
| **Reset Sandbox** | Mereset sandbox trainee ke state awal |
| **Bulk Actions** | Assign course ke multiple trainee sekaligus |

---

## 8. Employee Flow

```
Admin membuat akun Training untuk karyawan baru
  │
  ▼
Karyawan login ke Training Workspace
  │
  ├── Masuk Sandbox (data terisolasi)
  ├── Belajar SOP (baca lesson, tonton video)
  ├── Mengerjakan simulasi (skenario per role)
  ├── Quiz (test pemahaman teori)
  └── Assessment (ujian praktik di sandbox)
        │
        ▼
Lulus semua assessment? ─── Tidak ──→ Ulangi module yang gagal
        │
        Ya
        ▼
Trainer / Owner menyetujui
        │
        ▼
Akun Production diaktifkan
        │
        ▼
Karyawan mulai bekerja di Production
```

---

## 9. AI Roadmap (Future)

> **Status**: 🔮 FUTURE — Tidak termasuk dalam Sprint 8.x  
> **Target**: Setelah Training Center stable

### 9.1 AI Coach

Kemampuan yang direncanakan:

| Feature | Description |
|---------|-------------|
| **Contextual Hints** | AI memberi petunjuk berdasarkan step saat ini |
| **Error Explanation** | AI menjelaskan mengapa transisi ditolak |
| **Adaptive Training** | AI memberi latihan tambahan di area yang lemah |
| **Workflow Explanation** | AI menjelaskan alur workflow secara natural language |
| **Onboarding Assistant** | AI memandu karyawan baru dari hari pertama |
| **Voice Commands** | "AI, bagaimana cara membuat quotation?" |

### 9.2 AI Evaluation

| Feature | Description |
|---------|-------------|
| **Auto-grading** | AI mengevaluasi hasil assessment praktik |
| **Mistake Analysis** | AI mengidentifikasi pola kesalahan |
| **Performance Prediction** | AI memprediksi kesiapan production |

---

## 10. Architecture — Reuse Existing Engines

**WAJIB: ZERO new engines.**

```
Training & Simulation Center
  │
  ├── RequestEngine         → Trainee membuat request di sandbox
  ├── WorkflowEngine        → Validasi transisi state (engine SAMA dengan production)
  ├── AutomationEngine      → Rule automation berjalan (WhatsApp/Email sandbox mode)
  ├── PermissionEngine      → Role: trainer, trainee. Permission: training.*
  ├── FeatureEngine         → Module: training (enable/disable per tenant)
  ├── ProviderEngine        → ProviderAdapter sandbox mode
  ├── SettingsEngine        → Konfigurasi training (passing score, validity, dll)
  ├── Customer Engine       → Customer dummy di sandbox
  ├── Inventory Engine      → Stok dummy di sandbox
  ├── Service Engine        → Service simulasi di sandbox
  ├── Sales Engine          → Penjualan simulasi di sandbox
  ├── Reporting Engine      → Training report (completion, score, certification)
  ├── Monitoring Engine     → Training progress monitoring
  └── Event Platform        → event_logs mencatat aktivitas training (terpisah dari production)
```

---

## 11. Future Database Requirements

> **JANGAN membuat migration sekarang. Catatan untuk Sprint 8.x.**

### 11.1 Training Content Tables

| Table | Purpose |
|-------|---------|
| `training_courses` | Course — judul, deskripsi, role target, is_active |
| `training_modules` | Module dalam course — judul, order, course_id |
| `training_lessons` | Lesson — konten teks, module_id, order, duration_estimate |
| `training_videos` | Video — URL, thumbnail, lesson_id, duration |
| `training_quizzes` | Quiz — judul, module_id, passing_score |
| `training_quiz_questions` | Soal quiz — pertanyaan, tipe, pilihan (JSON), jawaban benar |
| `training_sops` | SOP interaktif — judul, steps (JSON), module_id |
| `training_checklists` | Checklist — judul, items (JSON), module_id |
| `training_faqs` | FAQ — pertanyaan, jawaban, module_id |
| `training_best_practices` | Best practice — judul, konten, module_id, author_id |
| `training_knowledge_articles` | Artikel knowledge base — judul, konten, tags |

### 11.2 Training Execution Tables

| Table | Purpose |
|-------|---------|
| `training_scenarios` | Skenario — judul, role, steps (JSON), time_limit |
| `training_sessions` | Sesi training per trainee — user_id, scenario_id, status, started_at, completed_at |
| `training_step_results` | Hasil per step — session_id, step_order, completed, time_spent, error_count |
| `training_progress` | Progress per module — user_id, module_id, progress_pct, completed_at |
| `training_scores` | Nilai quiz/assessment — user_id, quiz_id, score, passed, attempts |
| `training_assessments` | Assessment praktik — user_id, scenario_id, score, passed |
| `training_certificates` | Sertifikat — user_id, module, tier, score, issued_at, expires_at, certificate_number |
| `training_badges` | Badge yang diperoleh — user_id, badge_key, earned_at |
| `training_leaderboard` | Leaderboard (opsional) — user_id, total_score, rank |

### 11.3 Sandbox Tables

| Table | Purpose |
|-------|---------|
| `training_sandbox_state` | State sandbox per trainee — user_id, snapshot (JSON), last_reset_at |
| `training_sandbox_tenants` | Tenant training mirror — tenant_id, training_tenant_id |

### 11.4 Existing Table Additions

| Table | New Field | Purpose |
|-------|-----------|---------|
| `users` | `training_status` | Enum: training, ready, production |
| `users` | `certification_level` | JSON — ringkasan sertifikasi |
| `users` | `training_completed_at` | Timestamp — kapan training selesai |
| `tenants` | `training_enabled` | Boolean |
| `tenant_settings` | `training_passing_score` | Default passing score |
| `tenant_settings` | `training_certificate_validity_days` | Masa berlaku sertifikat |

---

## 12. Roadmap

| Sprint | Deliverable | Status |
|--------|------------|--------|
| 7.3 | Customer Engine | 🔄 IN PROGRESS |
| 7.4 | Service Workspace | PLANNED |
| 7.5 | Technician Workspace | PLANNED |
| 7.6 | Inventory Workspace | PLANNED |
| 7.7 | Finance Workspace | PLANNED |
| 7.8 | Owner Dashboard | PLANNED |
| **8.1** | **Training — Sandbox Engine + Tenant Isolation** | 🟡 FUTURE |
| **8.2** | **Training — Scenario Builder + Quiz Engine** | 🟡 FUTURE |
| **8.3** | **Training — LMS (Courses, Lessons, Videos, SOP)** | 🟡 FUTURE |
| **8.4** | **Training — Assessment + Certification** | 🟡 FUTURE |
| **8.5** | **Training — Trainer Dashboard + Reports** | 🟡 FUTURE |
| **8.6** | **Training — Knowledge Base + FAQ + Best Practice** | 🟡 FUTURE |
| **9.x** | **AI Coach + Adaptive Training** | 🔮 FUTURE |

---

## 13. Decision Log

### D-007: Training & Simulation Center Ditunda — Fokus pada Customer Engine

**Tanggal**: 2026-08-02  
**Status**: ACCEPTED  
**Decision**: DEFERRED to Sprint 8.x

**Konteks**:
ServiceKU membutuhkan Training Center untuk onboarding, SOP, dan sertifikasi. Namun, saat ini tim sedang mengerjakan Customer Engine (Sprint 7.3) yang merupakan **prioritas bisnis tertinggi** — digunakan setiap hari oleh CS, teknisi, kasir, manager, dan owner.

**Keputusan**:
Training & Simulation Center didokumentasikan dalam Blueprint ini tetapi **implementasi DITUNDA** hingga seluruh Core Business Modules (Sprint 7.3–7.8) selesai dan production-stable.

**Alasan Teknis**:
1. Customer Engine memberikan business value langsung — CS butuh Customer 360 sekarang
2. Training Module bergantung pada kestabilan SEMUA engine (Workflow, Automation, Provider, dll)
3. Membangun Training terlalu awal = melatih orang di workflow yang belum final = training tidak akurat
4. Semua engine harus production-stable sebelum bisa digunakan sebagai dasar training
5. Provider sandbox mode memerlukan perubahan di ProviderAdapter — harus dikerjakan sebagai bagian dari engine, bukan training module

**Alasan Bisnis**:
1. Customer Engine = digunakan hari ini, setiap hari
2. Training Center = digunakan saat onboarding (setiap 1–3 bulan)
3. Business impact Customer Engine > Training Center dalam jangka pendek

**Konsekuensi**:
- Tidak ada development Training Module di Sprint 7.x
- Blueprint ini menjadi acuan saat Sprint 8.x dimulai
- Semua engine harus mendukung sandbox mode (dikerjakan sebagai refinement engine, bukan training module)
- Knowledge Base dan SOP interaktif dapat mulai dikerjakan sebagai modul terpisah di Sprint 7.8 (Owner Dashboard)

---

## 14. Prerequisites Before Sprint 8.1

Sebelum implementasi Training Center dimulai:

- [ ] WorkflowEngine mendukung flag `sandbox` (data terpisah, engine sama)
- [ ] ProviderAdapter mendukung sandbox mode (WhatsApp log-only, email ke trainer)
- [ ] EventLog membedakan event production vs training (`source = training`)
- [ ] PermissionEngine memiliki role `trainer` + permission `training.*`
- [ ] FeatureEngine memiliki module `training` (enable/disable per tenant)
- [ ] Tenant isolation: tenant training mirror bisa dibuat dan di-reset
- [ ] Semua Core Business Modules (7.3–7.8) production-stable
- [ ] Knowledge Base foundation sudah ada (artikel, SOP)

---

**Blueprint selesai. Kembali ke Sprint 7.3 — Customer Engine.**

