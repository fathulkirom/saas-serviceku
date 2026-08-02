# ServiceKU — Personality

> Karakter produk ServiceKU sebagai **SaaS ERP untuk bisnis servis HP/laptop, sparepart, retail, multi-cabang, multi-tenant**. Karakter ini memandu keputusan desain, penulisan, dan interaksi (selaras dengan `docs/` Sprint 4).

Personality ServiceKU dalam satu kalimat:
> **"Profesional yang tenang, andal seperti bengkel yang rapi, dan cepat seperti kasir berpengalaman."**

---

## 1. Professional (Profesional)

**Alasan:** ServiceKU melayani bisnis nyata dengan uang, stok, dan pelanggan. Tampilan & bahasa harus menimbulkan rasa profesional: warna solid, tipografi rapi (Plus Jakarta Sans), komponen konsisten (`K*`), dan tidak ada elemen "main-main". Peran & izin (owner/admin/cs/teknisi/kasir) menegaskan struktur kerja profesional.

---

## 2. Reliable (Andal)

**Alasan:** Tidak boleh ada tiket hilang, nota salah, atau stok tidak akurat. Produk dirancang transaksional (pencatatan DB per tenant), ada idempotensi sale, status servis yang jelas, dan sinkronisasi. Karakter "andal" tercermin dari: nomor servis/nota unik, status yang bisa dilacak, dan data yang tersimpan aman per toko.

---

## 3. Modern (Modern)

**Alasan:** Meski untuk bisnis tradisional, ServiceKU tampil sebagai SaaS kontemporer: dark mode, CSS variables/design token, rounded-xl, shadow halus, sidebar modern, PWA. Modern berarti "tidak terkesan ketinggalan zaman" dan memberi rasa bahwa tool ini membantu bisnis maju.

---

## 4. Fast (Cepat)

**Alasan:** Di front desk servis & kasir, kecepatan menentukan kepuasan pelanggan. ServiceKU meminimalkan langkah: dashboard role-based, global search (Cmd/Ctrl+K), POS ringkas, shortcut keyboard, tombol aksi satu langkah. "Cepat" bukan berarti tergesa — berarti efisien dan tanpa hambatan.

---

## 5. Minimal (Minimal)

**Alasan:** Prinsip *no visual noise* (lihat `DesignPrinciples.md`). Setiap layar punya satu fokus utama; tabel rapi; warna aksen seminimal mungkin; elemen dekoratif tidak mengalahkan data. Minimal = mengurangi beban kognitif pengguna yang sibuk.

---

## 6. Industrial (Industrial)

**Alasan:** Domain produk (servis HP/laptop, sparepart, bengkel/workshop) membawa nuansa "workshop yang terorganisir". Nuansa industrial muncul lewat: penyebutan komponen nyata (sparepart, indent, stok, teknisi), status yang lugas, tabel padat, dan warna dasar netral (slate/zinc) dengan aksen tegas. Industrial = fungsional dan kokoh, bukan kotor atau gelap.

---

## 7. Clean (Bersih)

**Alasan:** Layout bernapas (whitespace cukup, `max-w-7xl` container, `space-y-5` antar section), kartu `p-5`, border tipis, dan tanpa kekacauan visual. "Bersih" juga berarti data tertata (kolom terstruktur, alignment angka, filter yang jelas).

---

## 8. Trustworthy (Terpercaya)

**Alasan:** Produk memegang data keuangan & pelanggan. Karakter terpercaya dibangun dari: konfirmasi sebelum aksi destruktif (hapus, void, cancel), transparansi status ("Menunggu Alokasi", "Belum Bayar"), flash/toast yang jujur, dan izin per peran yang membatasi akses.

---

## 9. Calm (Kalem/Tenang)

**Alasan:** Toko servis adalah lingkungan yang sibuk dan kadang tegang (pelanggan menunggu, perbaikan rumit). ServiceKU menenangkan: warna primary yang tidak menekan, pesan error yang memandu solusi (bukan menyalahkan), loading yang lembut (skeleton), dan tidak ada alarm visual yang berlebihan. Kalem = pengguna tetap fokus.

---

## 10. Confident (Percaya Diri)

**Alasan:** ServiceKU yakin pada datanya: angka disajikan tegas (font-bold, `font-mono` untuk ID), status diberi warna semantik yang jelas, dan laporan disusun lengkap (9 jenis laporan). Percaya diri = UI tidak ragu-ragu, setiap angka punya konteks.

---

## Ringkasan Karakter (untuk keputusan cepat)

| Jika perlu memutuskan... | Arahkan ke |
|---|---|
| Warna | Biru profesional (`--primary`) + netral slate/zinc + semantik status |
| Bahasa | Indonesia profesional, kalimat pendek |
| Tampilan | Bersih, minimal, rapi, modern (design token) |
| Interaksi | Cepat, satu aksi utama, konfirmasi saat destruktif, feedback lembut |
| Data | Padat, terbaca, tegas, terpercaya |
| Perasaan umum | Kalem, andal, percaya diri — bukan ramai, bukan kaku |

Karakter ini menjadi dasar `docs/product/VisualLanguage.md`, `Interaction.md`, dan `CopyWriting.md`.
