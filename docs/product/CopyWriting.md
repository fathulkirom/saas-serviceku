# ServiceKU — Copywriting

> Aturan penulisan UI ServiceKU — **Bahasa Indonesia profesional**. Selaras dengan tone/voice (`docs/product/BrandIdentity.md`), status & istilah (`docs/Naming.md`), dan komponen (`docs/Component.md`).
> Copy yang sudah ada di source code adalah acuan konkret — pertahankan konsistensinya.

---

## 1. Prinsip Umum

- **Bahasa Indonesia** untuk seluruh UI, pesan, dan dokumentasi produk (kode/identifier tetap English).
- **Kalimat pendek & langsung.** Satu ide per kalimat.
- **Imperatif untuk aksi** ("Simpan", "Hapus", "Selesaikan").
- **Jujur & menenangkan** — terutama untuk error dan konfirmasi.
- **Konsisten istilah**: Servis, Pelanggan, Produk, Penjualan, Kas, Inventaris, Indent, Sparepart, Cabang, Setoran, dsb.
- Hindari jargon teknis (kecuali untuk super admin), hindari bahasa merendahkan, hindari emoji di teks formal.

---

## 2. Button (Tombol)

| Jenis | Aturan | Contoh |
|---|---|---|
| Aksi simpan | Verb + (opsional) objek | "Simpan", "Simpan Perubahan", "Simpan & Selesaikan" |
| Aksi batal | "Batal" (modal) / "Kembali" (navigasi) | "Batal" |
| Aksi destruktif | Verb yang jelas + konfirmasi | "Hapus", "Batalkan", "Void" |
| Proses (loading) | "Memproses...", "Menyimpan...", "Mengupload..." | "Menyimpan..." |
| Aksi status servis | Verb sesuai status | "Terima Pekerjaan", "Mulai Pekerjaan", "Selesaikan Pekerjaan", "Setujui Konfirmasi" |
| Tambah | "+ Tambah ..." | "+ Tambah Sparepart" |

Aturan: tombol pendek (1–3 kata), huruf kapital di awal kata (title case), tidak memakai tanda baca akhir. Konsisten dengan pola `KButton` (lihat source: "Simpan", "Batal", "Kirim ke Partner", "Cetak Tanda Terima").

---

## 3. Modal & Dialog

Struktur copy:
1. **Judul** — pertanyaan atau aksi, singkat.
   - Konfirmasi: "Batalkan Servis?" / "Hapus Produk?"
   - Form: "Assign Teknisi", "Kirim ke Partner", "Complete Servis".
2. **Deskripsi** — konteks + konsekuensi (jika destruktif), 1–2 kalimat.
   - "Servis #123 akan dibatalkan. Tindakan ini tidak dapat dibatalkan."
   - "Servis #123 akan dikerjakan oleh partner eksternal."
3. **Aksi** — tombol primer + "Batal".

Aturan: judul tidak berakhir titik; deskripsi pendek & jelas; selalu sebutkan konteks (nomor servis/nota) saat relevan.

---

## 4. Error

Format: **apa yang gagal + (jika ada) solusi/aksi lanjutan.**

| Konteks | Contoh Copy |
|---|---|
| Field | "Nomor HP wajib diisi." / "Email tidak valid." |
| Fitur tidak tersedia | "Fitur tidak tersedia pada paket Anda. Silakan upgrade." (pola nyata) |
| Akses terbatas | "Akses dibatasi. Upgrade paket untuk mengubah data ini." (pola nyata) |
| Langganan habis | "Masa langganan habis. Silakan perbarui paket." |
| Gagal aksi | "Gagal menyimpan. Coba lagi." |

Aturan: jangan menyalahkan pengguna; jangan tampilkan teknis; beri jalan keluar; tetap tenang (tidak pakai huruf kapital semua / seru).

---

## 5. Success

Format: "Berhasil + aksi + objek." Singkat & spesifik.

| Konteks | Contoh |
|---|---|
| Simpan | "Berhasil disimpan." |
| Status servis | "Servis berhasil diselesaikan." / "Berhasil mengubah status." |
| Pembayaran | "Pembayaran berhasil." |
| Aksi umum | "Berhasil." / "Perubahan disimpan." |

Aturan: konsisten dengan flash `success` (pola nyata "Berhasil", "Servis berhasil ..."). Tidak berlebihan — jika hasil langsung terlihat (badge berubah), toast singkat sudah cukup.

---

## 6. Confirmation

- **Judul pertanyaan** langsung ("Batalkan Servis?", "Hapus Produk ini?").
- **Deskripsi konsekuensi** eksplisit, terutama jika tidak bisa dibatalkan ("Tindakan ini tidak dapat dibatalkan.").
- **Tombol** yang tegas: "Ya, Batalkan" / "Ya, Hapus" (primer bahaya) dan "Tidak"/"Batal" (sekunder).
- Untuk aksi dengan alasan (mis. cancel servis), sertakan field singkat opsional/wajib.

Aturan: jangan pakai "Anda yakin?" saja tanpa menjelaskan dampak; selalu beri opsi batal yang jelas.

---

## 7. Tooltip

- **Singkat** (3–6 kata), menjelaskan fungsi, bukan mengulang label.
- Contoh: tombol print → "Cetak Nota"; tombol ikon → "Tampilkan Sidebar".
- Gunakan `title`/tooltip untuk ikon-only dan elemen yang perlu penjelasan singkat.
- Bahasa Indonesia; huruf kapital di awal.

---

## 8. Empty State

Format: **pesan singkat + (opsional) ajakan.**

| Konteks | Contoh |
|---|---|
| Daftar kosong | "Belum ada data tiket servis." / "Belum ada data penjualan." |
| Hasil filter kosong | "Tidak ada hasil." |
| Kosong + CTA | "Tidak ada data. Buat tiket baru." |

Pola nyata: "Tidak ada data tiket servis." + tombol "+ Buat Tiket Baru"; "Belum ada data penjualan" + "+ Transaksi Penjualan Baru" (KTable empty).

Aturan: kalimat pernyataan (bukan instruksi), lalu CTA terpisah bila relevan; jangan kosong tanpa penjelasan.

---

## 9. Status & Label (konsisten dengan sistem)

| Konteks | Label UI |
|---|---|
| Status servis | Menunggu Alokasi, Diterima, On Progress, Konfirmasi, Siap Diambil, Waiting Parts, Partner, Finish, Cancel (lihat `useServiceStatus.js`) |
| Pembayaran servis | Lunas / Belum Bayar |
| Aksi tombol | Simpan, Batal, Kirim, Selesaikan Pekerjaan, Klaim Garansi |

Gunakan label status **persis** seperti yang sudah didefinisikan (`docs/Naming.md` §7) — jangan buat varian baru.

---

## 10. Checklist Copy

1. Bahasa Indonesia profesional & konsisten.
2. Tombol = verb singkat, title case, tanpa titik.
3. Modal = judul + deskripsi + aksi jelas.
4. Error = jelas + solusi + tenang.
5. Success = "Berhasil ..." singkat.
6. Konfirmasi = jelaskan konsekuensi + opsi batal.
7. Tooltip = singkat, penjelas fungsi.
8. Empty state = pernyataan + (opsional) CTA.
9. Istilah & status sesuai `docs/Naming.md`.
10. Tidak ada emoji di teks formal (ikon dibolehkan di judul section sesuai pola).
