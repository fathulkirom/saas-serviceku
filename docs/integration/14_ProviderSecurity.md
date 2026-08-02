# 14 — Provider Security

> **Sprint 6.2B · Blueprint Only.** Keamanan provider — credential, rotation, permission.

---

## 1. Credential Storage

| Aspek | Ketentuan |
|---|---|
| **Penyimpanan** | Tenant DB, kolom terenkripsi (AES-256). |
| **Enkripsi** | Encrypt at rest; decrypt hanya saat provider dipakai. |
| **Kunci enkripsi** | Per tenant; tidak shared. |
| **Backup** | Credential tidak ikut backup (atau backup terenkripsi dengan kunci terpisah). |
| **Log** | Credential tidak boleh muncul di log (`****` masking). |

---

## 2. Authentication per Provider Type

| Provider Type | Auth Method | Credential yang disimpan |
|---|---|---|
| **Storage — S3/R2** | Access Key + Secret Key | `key`, `secret` (encrypted) |
| **Storage — Google Drive** | OAuth 2.0 | `refresh_token` (encrypted) |
| **Storage — NAS** | Username + Password / SSH Key | `username`, `password` / `ssh_key` |
| **Messaging — WA Cloud API** | Permanent Token | `token` |
| **Messaging — WA Web** | QR pairing (session) | Session data (encrypted) |
| **Payment — Midtrans** | Server Key + Client Key | `server_key`, `client_key` |
| **AI — OpenAI** | API Key | `api_key` |
| **Email — Brevo/SES** | API Key / SMTP credential | `api_key` / `smtp_password` |

---

## 3. Credential Rotation

| Trigger | Tindakan |
|---|---|
| **Owner rotate manual** | UI Settings → "Rotate Key" → generate/input baru |
| **Compromised** | Super Admin / Owner force rotate; provider lama dinonaktifkan |
| **Scheduled rotation** (future) | Auto-rotate untuk provider yang mendukung (S3 IAM, API key regenerasi) |

---

## 4. Permission

| Aksi | Permission |
|---|---|
| Melihat daftar provider | `provider.read` |
| Install / Uninstall provider | `provider.manage` (Owner only) |
| Konfigurasi credential | `provider.manage` (Owner only) |
| Melihat status provider | `provider.read` |
| Melihat credential | ❌ Tidak bisa (masked) — hanya input/edit |

---

## 5. Aturan

1. **Credential = milik tenant** — tidak disimpan di central DB.
2. **Satu tenant tidak bisa melihat credential tenant lain** — tenant isolation.
3. **Credential lama dihapus saat rotate** — tidak menumpuk.
4. **Audit** — setiap perubahan credential dicatat (tanpa menyimpan credential-nya).

---

## 6. Verifikasi

Konsisten dengan `docs/data-architecture/16_DataSecurity.md` (Sprint 6.2A), `docs/data-architecture/04_DataClassification.md` (L4 Sensitive).
