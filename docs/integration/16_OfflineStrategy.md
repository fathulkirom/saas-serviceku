# 16 — Offline Strategy

> **Sprint 6.2B · Blueprint Only.** Apa yang terjadi saat provider eksternal gagal — internet mati, storage penuh, WhatsApp disconnect, printer error.

---

## 1. Skenario Kegagalan & Respons

| Kegagalan | Dampak | Respons otomatis | Intervensi manual |
|---|---|---|---|
| **Internet mati** | Cloud storage, WA Cloud API, payment gateway gagal | Fallback ke local provider; antre operasi (queue) | ❌ Tidak bisa — internet wajib untuk SaaS |
| **Storage cloud gagal** (S3 down / GDrive rate limit) | Upload file gagal | Fallback ke Local Storage; retry nanti | Owner cek Status → pindah provider |
| **Storage penuh** (kuota habis) | Upload file ditolak | Notifikasi Owner; hentikan upload sampai kuota ditambah | Owner upgrade plan / hapus file lama |
| **WhatsApp Web disconnect** | Pesan WA gagal terkirim | Fallback ke Email; notifikasi Owner | Owner scan QR ulang |
| **WhatsApp Cloud API error** | Pesan WA gagal | Fallback ke Email / SMS | Owner cek API key / saldo |
| **Printer mati / error** | Cetak gagal | Simpan sebagai "pending print"; retry | Kasir cek printer; cetak ulang manual |
| **Payment gateway error** | Pembayaran gagal diproses | Fallback ke Cash; order tetap dibuat | CS arahkan pelanggan bayar cash |
| **AI provider error** | Klasifikasi/rekomendasi tidak ada | Klasifikasi fallback ke default/manual | CS isi manual |
| **Location provider error** | Geocoding gagal | Minta input alamat manual (teks) | CS isi alamat manual |

---

## 2. Offline Queue (Konsep)

```
Operasi gagal → simpan di offline_queue
    → retry dengan exponential backoff (1m, 5m, 15m, 1h)
    → gagal setelah N retry → notifikasi Owner
    → Owner resolve → retry manual / ganti provider
```

---

## 3. Degraded Mode

Saat provider utama gagal, ServiceKU masuk **Degraded Mode**:
- Semua fungsi inti (Request, Service, Sales, Cash, Inventory) **tetap berjalan**.
- Provider eksternal fallback ke local/default.
- UI menampilkan banner: "Beberapa layanan eksternal sedang tidak tersedia."

---

## 4. Aturan

1. **Core domain tidak boleh gagal karena provider** — fallback wajib ada.
2. **Setiap provider punya fallback** — minimal Local Storage / Cash / Manual.
3. **Notifikasi Owner** — jika provider error > N kali.
4. **Offline queue** — untuk operasi non-urgent (sync, backup).
5. **Degraded mode = informatif** — user tahu, tapi tetap bisa bekerja.

---

## 5. Verifikasi

Konsisten dengan `02_ProviderPattern.md` (fallback chain), `13_ProviderLifecycle.md` (degraded state), prinsip **Practical over Perfect** (tetap bisa kerja walau provider mati).
