# 16 — Data Security

> **Sprint 6.2A · Blueprint Only.** Strategi keamanan data ServiceKU.

---

## 1. Lapisan Keamanan

| Lapisan | Mekanisme |
|---|---|
| **Transport** | HTTPS (TLS 1.3) — semua komunikasi |
| **At rest** | Enkripsi database (L4 Sensitive); backup terenkripsi |
| **Application** | Permission Engine (setiap akses data via permission) |
| **Infrastructure** | Tenant isolation (1 DB per tenant); firewall; rate limiting |

---

## 2. Data Sensitif — Penanganan

| Data | Klasifikasi | Enkripsi at rest | Masking di log | Siapa yang boleh baca |
|---|---|---|---|---|
| Password hash | L4 | bcrypt/argon2 (hashed) | — (hashed) | — (tidak bisa dibaca balik) |
| Payment token | L4 | ✅ | ✅ `tok_****` | System only |
| API key | L4 | ✅ | ✅ | Super Admin |
| Customer phone | L3 | Opsional | ✅ `0812****1234` | CS, Admin, Manager, Owner |
| Customer address | L3 | Opsional | ✅ parsial | CS, Admin, Manager, Owner |
| IMEI / Serial | L3 | Opsional | ✅ `35****8901` | CS, Admin, Teknisi terkait |
| Financial amounts | L2 | ❌ | ❌ (tidak perlu) | Sesuai permission finance |
| Audit log | L4 | ❌ (append-only) | — | Owner, Super Admin |

---

## 3. Aturan Akses Data

| Kondisi | Aturan |
|---|---|
| **Tenant isolation** | User tenant A tidak bisa mengakses data tenant B. |
| **Permission-based** | Tidak ada akses berdasarkan nama role — selalu via permission. |
| **Data PII (L3)** | Akses baca dicatat di audit log. |
| **Data Sensitive (L4)** | Akses baca & tulis dicatat di audit log. |
| **Export data** | Export PII/financial = butuh permission + dicatat. |
| **API access** | API key + rate limit per plan. |

---

## 4. Incident Response (Blueprint)

| Tahap | Tindakan |
|---|---|
| **Deteksi** | Monitoring log akses tidak normal (rate spike, akses di luar jam). |
| **Isolasi** | Blokir akses user/API key yang mencurigakan. |
| **Audit** | Periksa audit log — data apa yang diakses. |
| **Notify** | Informasikan Owner tenant terkait. |
| **Remediate** | Patch celah; rotate API key; perkuat aturan. |

---

## 5. Verifikasi

Konsisten dengan `04_DataClassification.md`, `SECURITY.md` (project root), `docs/domain/Ownership.md`.
