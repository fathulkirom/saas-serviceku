# MAIL-SAVE-FIX-01 — Settings "Simpan" Selalu Gagal (Provider Tidak Tersimpan)

> Date: 2026-08-08
> Owner report: "saat aku save kenapa masih Belum dikonfigurasi di statusnya"

---

## Diagnosis (root cause)

Live `system_settings` setelah owner Save: **semua nilai `mail_resend_*` tetap NULL** (`provider=NULL`, `api_key` kosong, `from_address=NULL`), dan **tidak ada satu pun log "System settings updated"**. Kesimpulan: klik "Simpan Pengaturan" **selalu gagal validasi** sejak halaman ini ditulis — bukan hanya untuk mail.

Penyebab pasti (ditelusuri di source Inertia):
- `Settings.vue` mengirim nilai computed (`registration_open`, `require_approval`, `maintenance_mode`) lewat opsi `data:` pada `form.post()`.
- **Inertia mengabaikan opsi `data` pada `useForm().post()`** — hanya data posisional (`form.data()`) yang dikirim (`@inertiajs/vue3` `submit()` → `router.post(url, form.data(), options)`; `router.post` → `visit({...options, data: positionalData})` yang menimpa).
- Jadi payload hanya berisi `registration_open_bool` (boolean) — tanpa `registration_open`/`require_approval`/`maintenance_mode` yang **wajib** ada di `SystemSettingsController@update` (`required|in:true,false`).
- Akibatnya `validate()` selalu melempar 422 → tidak ada yang tersimpan → status tetap "⚠️ Belum dikonfigurasi".

Bug yang sama juga mengenai form **Feature Flags** (`feature_two_factor_auth` dst. tidak pernah terkirim).

## Fix

`resources/js/Pages/Admin/Settings.vue` — ganti opsi `data:` yang mati dengan **`form.transform()`** (cara idiomatis Inertia):
- `updateSettings()`: `transform` memetakan `registration_open_bool` → `registration_open: 'true'|'false'`, dst.
- `updateFeatureFlags()`: memetakan `two_factor_auth` → `feature_two_factor_auth: 'true'|'false'`, dst.

## Verification

- `PilotMailSettingsTest` + `PlatformSyncTest` → **44/44 (187 assertions)** — termasuk 2 test baru:
  - `test_settings_save_frontend_payload_persists_mail_provider` (mirror payload frontend → provider+key tersimpan, `isConfigured()=true`, ada log "System settings updated")
  - `test_settings_save_without_registration_key_fails` (dokumentasi bug lama)
- `npm run build` → PASS; chunk baru `Settings-CCgsdL0Q.js` direferensikan manifest.
- Commit `b8c1f62`, di-push ke GitHub.
- Deployed via `./deploy.sh` (exit 0); file source + build + container diverifikasi live.

## Owner guidance

Buka **Pengaturan → Email Transaksional**, pilih Provider, isi field, lalu klik **Simpan Pengaturan**. Sekarang save benar-benar tersimpan dan badge akan berubah menjadi **✅ Terkonfigurasi** (setelah provider dipilih + API key/from diisi). Setelah itu klik **Kirim Email Tes**.

## Verdict

Bug UI blokir-save (seluruh form settings) ditemukan & diperbaiki; frontend + backend sinkron; sudah live.
