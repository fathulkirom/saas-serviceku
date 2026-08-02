# Future Roadmap — ServiceKU

> Peta pengembangan **target** ServiceKU. Semua item di sini adalah **roadmap** (belum diimplementasikan) dan tidak mengubah kondisi saat ini.
> Prioritas & urutan dapat berubah; konsisten dengan `docs/specification/PROJECT_SPECIFICATION.md` §13.

---

## 1. Tahapan Roadmap

| Fase | Fokus | Item utama |
|---|---|---|
| **Fase 1 — Konsolidasi (Sprint 6+)** | Memperkuat fondasi saat ini | Konsistensi permission, komponen K*, a11y, perfomance laporan |
| **Fase 2 — Engine Core** | Fondasi ERP Modular | Module Engine, Permission Engine, Feature Engine, Business Template |
| **Fase 3 — Role & User** | Fleksibilitas organisasi | Role Engine, User Engine (multi-role), Workflow Engine |
| **Fase 4 — Automation & Integrasi** | Menghubungkan | Workflow Builder, Automation, Webhook, Public API |
| **Fase 5 — Ekosistem** | Marketplace & AI | Module Marketplace, Plugin System, AI Assistant |

---

## 2. Item Roadmap Utama

### 2.1 Role Engine
- Role sebagai data; owner membuat/mengedit/menggabung role; role kustom.
- → `docs/architecture-engine/RoleEngine.md`.

### 2.2 Workflow Builder
- Owner/Super Admin mengatur workflow (status & transisi) per modul via UI.
- → `docs/architecture-engine/WorkflowEngine.md`.

### 2.3 Module Marketplace
- Toko modul tambahan (HRD, Accounting, CRM, Marketing) yang dapat diaktifkan tenant.
- → `docs/architecture-engine/ModuleEngine.md`.

### 2.4 Plugin System
- Ekstensi pihak ketiga via API/sandbox; tidak mengubah inti.
- → `docs/architecture-engine/ModuleEngine.md`, `FeatureEngine.md`.

### 2.5 Automation
- Aturan otomatis: notifikasi status, tagihan, reorder stok, follow-up pelanggan.
- → `WorkflowEngine.md` (hook `onEnter`).

### 2.6 Webhook
- Kirim event (servis selesai, pembayaran sukses, stok menipis) ke sistem eksternal.
- → `ModuleEngine.md` (modul `webhook` — future).

### 2.7 Public API
- API eksternal terotentikasi untuk integrasi (website, mobile, POS eksternal).
- Rate limit & kuota per plan (Subscription Engine).

### 2.8 AI Assistant
- Bantuan AI: rekomendasi harga servis, ringkasan laporan, penulisan pesan pelanggan, deteksi anomali.
- Dikontrol via plan (dimensi AI Subscription Engine).

### 2.9 Marketplace Integration
- Integrasi marketplace (Tokopedia/Shopee/etc.) untuk sinkron produk & pesanan.

---

## 3. Aturan Roadmap

1. Item roadmap **tidak boleh** didokumentasikan sebagai fitur aktif (jaga konsistensi dengan `docs/` & `docs/product/`).
2. Setiap item harus didaftarkan di Module/Feature registry terlebih dahulu sebelum dikembangkan (target).
3. Urutan mengikuti nilai bisnis & kompleksitas; perubahan dicatat di dokumen ini.
4. Backward compatibility: roadmap tidak boleh memaksa migrasi data besar tenant.

---

## 4. Matriks Status

| Item | Status | Engine terkait |
|---|---|---|
| Role Engine | Roadmap | RoleEngine |
| Workflow Builder | Roadmap | WorkflowEngine |
| Module Marketplace | Roadmap | ModuleEngine |
| Plugin System | Roadmap | ModuleEngine / FeatureEngine |
| Automation | Roadmap | WorkflowEngine |
| Webhook | Roadmap | ModuleEngine |
| Public API | Roadmap | SubscriptionEngine / ModuleEngine |
| AI Assistant | Roadmap | SubscriptionEngine |
| Marketplace Integration | Roadmap | ModuleEngine |

---

## 5. Verifikasi

Seluruh item pada dokumen ini adalah **target/roadmap** dan belum ada di source code. Status saat ini (Sprint 5.2 selesai) adalah fondasi dokumentasi; implementasi engine dimulai pada fase berikutnya.
