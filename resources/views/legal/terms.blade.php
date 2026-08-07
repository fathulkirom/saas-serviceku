<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Syarat Layanan - ServiceKU</title>
    <meta name="description" content="Syarat layanan ServiceKU untuk penggunaan aplikasi operasional servis, POS, stok, dan laporan.">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-5 sm:px-6">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-700">
                    <img src="/images/logo.svg" alt="" class="h-6 w-6 brightness-0 invert">
                </span>
                <span class="text-lg font-black text-slate-950">ServiceKU</span>
            </a>
            <a href="{{ route('privacy') }}" class="text-sm font-bold text-teal-700 hover:text-teal-900">Kebijakan Privasi</a>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:py-16">
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-10">
            <p class="text-sm font-bold uppercase tracking-wide text-teal-700">Terakhir diperbarui: 6 Agustus 2026</p>
            <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">Syarat Layanan</h1>
            <p class="mt-5 leading-8 text-slate-600">
                Dengan menggunakan ServiceKU, pengguna dan pemilik tenant menyetujui syarat layanan berikut. ServiceKU disediakan untuk membantu pengelolaan operasional servis, POS, stok, pelanggan, cabang, pembayaran, laporan, dan fitur pendukung lain.
            </p>

            <div class="mt-10 space-y-9">
                <section>
                    <h2 class="text-xl font-black text-slate-950">Akun Dan Tanggung Jawab Pengguna</h2>
                    <p class="mt-3 leading-8 text-slate-600">
                        Pengguna bertanggung jawab menjaga keamanan akun, password, perangkat, dan akses login Google yang terhubung. Administrator tenant bertanggung jawab mengelola user, peran, izin akses, data toko, serta kepatuhan penggunaan aplikasi oleh timnya.
                    </p>
                </section>

                <section>
                    <h2 class="text-xl font-black text-slate-950">Penggunaan Layanan</h2>
                    <p class="mt-3 leading-8 text-slate-600">
                        ServiceKU hanya boleh digunakan untuk kegiatan yang sah. Pengguna tidak boleh menyalahgunakan layanan untuk mengakses data tenant lain, mengganggu sistem, mengunggah file berbahaya, melakukan percobaan peretasan, atau memakai layanan untuk aktivitas yang melanggar hukum.
                    </p>
                </section>

                <section>
                    <h2 class="text-xl font-black text-slate-950">Data Tenant</h2>
                    <p class="mt-3 leading-8 text-slate-600">
                        Data operasional yang dimasukkan ke tenant tetap menjadi tanggung jawab pemilik tenant. ServiceKU menyediakan mekanisme pemisahan data dan kontrol akses, namun akurasi data pelanggan, transaksi, stok, laporan, dan dokumen yang dimasukkan oleh pengguna menjadi tanggung jawab tenant masing-masing.
                    </p>
                </section>

                <section>
                    <h2 class="text-xl font-black text-slate-950">Integrasi Pihak Ketiga</h2>
                    <p class="mt-3 leading-8 text-slate-600">
                        ServiceKU dapat terhubung dengan penyedia login, email, pembayaran, penyimpanan, atau layanan pihak ketiga lain. Penggunaan integrasi tersebut juga tunduk pada syarat dan kebijakan masing-masing penyedia. Pengguna bertanggung jawab memastikan konfigurasi integrasi valid dan aman.
                    </p>
                </section>

                <section>
                    <h2 class="text-xl font-black text-slate-950">Ketersediaan Dan Perubahan Fitur</h2>
                    <p class="mt-3 leading-8 text-slate-600">
                        Kami berupaya menjaga layanan tetap stabil, namun tidak menjamin layanan selalu bebas gangguan. Fitur dapat ditambah, diperbaiki, dibatasi, atau dihentikan untuk alasan keamanan, teknis, kepatuhan, atau pengembangan produk.
                    </p>
                </section>

                <section>
                    <h2 class="text-xl font-black text-slate-950">Pembayaran Dan Paket</h2>
                    <p class="mt-3 leading-8 text-slate-600">
                        Jika tenant menggunakan paket berbayar, biaya, masa aktif, fitur, trial, promo, dan ketentuan pembayaran mengikuti informasi paket yang tersedia di aplikasi atau kesepakatan resmi dengan pengelola ServiceKU.
                    </p>
                </section>

                <section>
                    <h2 class="text-xl font-black text-slate-950">Penghentian Akses</h2>
                    <p class="mt-3 leading-8 text-slate-600">
                        Akses dapat dibatasi atau dihentikan jika terjadi pelanggaran syarat layanan, risiko keamanan, penyalahgunaan sistem, tunggakan pembayaran, permintaan pemilik tenant, atau kewajiban hukum.
                    </p>
                </section>

                <section>
                    <h2 class="text-xl font-black text-slate-950">Kontak</h2>
                    <p class="mt-3 leading-8 text-slate-600">
                        Untuk pertanyaan tentang syarat layanan, hubungi pengelola ServiceKU melalui email dukungan yang tercantum pada halaman aplikasi atau kanal resmi yang diberikan kepada tenant.
                    </p>
                </section>
            </div>
        </article>
    </main>
</body>
</html>
