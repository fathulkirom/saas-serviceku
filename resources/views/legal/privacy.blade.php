<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kebijakan Privasi - ServiceKU</title>
    <meta name="description" content="Kebijakan privasi ServiceKU tentang pengumpulan, penggunaan, dan perlindungan data pengguna.">
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
            <a href="{{ route('terms') }}" class="text-sm font-bold text-teal-700 hover:text-teal-900">Syarat Layanan</a>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:py-16">
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-10">
            <p class="text-sm font-bold uppercase tracking-wide text-teal-700">Terakhir diperbarui: 6 Agustus 2026</p>
            <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">Kebijakan Privasi</h1>
            <p class="mt-5 leading-8 text-slate-600">
                ServiceKU membantu pemilik usaha servis, retail, dan operasional toko mengelola data pelanggan, servis, stok, penjualan, cabang, laporan, serta akun pengguna. Kebijakan ini menjelaskan data yang kami kumpulkan, cara kami menggunakannya, dan pilihan yang tersedia untuk pengguna.
            </p>

            <div class="mt-10 space-y-9">
                <section>
                    <h2 class="text-xl font-black text-slate-950">Data Yang Kami Kumpulkan</h2>
                    <p class="mt-3 leading-8 text-slate-600">
                        Kami dapat menyimpan data akun seperti nama, email, peran pengguna, avatar Google bila login Google digunakan, riwayat login, dan preferensi tampilan. Untuk operasional toko, ServiceKU juga dapat menyimpan data pelanggan, perangkat, transaksi, servis, produk, stok, cabang, pembayaran, catatan aktivitas, dan lampiran yang diunggah pengguna.
                    </p>
                </section>

                <section>
                    <h2 class="text-xl font-black text-slate-950">Penggunaan Login Google</h2>
                    <p class="mt-3 leading-8 text-slate-600">
                        Jika pengguna memilih masuk dengan Google, ServiceKU meminta akses dasar untuk mengidentifikasi akun, yaitu nama, alamat email, ID akun Google, dan foto profil. Data ini digunakan untuk autentikasi, menghubungkan akun Google dengan akun ServiceKU, dan membantu pengguna masuk ke tenant atau toko yang benar. ServiceKU tidak meminta akses ke Gmail, Google Drive pribadi, kalender, kontak, atau data sensitif lain untuk fitur login Google.
                    </p>
                </section>

                <section>
                    <h2 class="text-xl font-black text-slate-950">Cara Kami Menggunakan Data</h2>
                    <p class="mt-3 leading-8 text-slate-600">
                        Data digunakan untuk menjalankan fitur aplikasi, mengamankan akses akun, memisahkan data antar tenant, membuat laporan operasional, menyimpan audit aktivitas, memproses transaksi, mengirim notifikasi yang diperlukan, dan meningkatkan stabilitas layanan.
                    </p>
                </section>

                <section>
                    <h2 class="text-xl font-black text-slate-950">Penyimpanan Dan Keamanan</h2>
                    <p class="mt-3 leading-8 text-slate-600">
                        ServiceKU menerapkan pemisahan data tenant, autentikasi pengguna, pembatasan akses berdasarkan peran, audit aktivitas, dan praktik keamanan aplikasi yang wajar. Password disimpan dalam bentuk hash. Akses internal ke data dibatasi untuk kebutuhan operasional, dukungan, keamanan, atau kewajiban hukum.
                    </p>
                </section>

                <section>
                    <h2 class="text-xl font-black text-slate-950">Berbagi Data</h2>
                    <p class="mt-3 leading-8 text-slate-600">
                        Kami tidak menjual data pengguna. Data dapat diproses oleh penyedia infrastruktur, email, pembayaran, atau layanan pihak ketiga lain yang digunakan untuk menjalankan ServiceKU. Integrasi pihak ketiga hanya digunakan sesuai konfigurasi yang diaktifkan oleh pengguna atau administrator tenant.
                    </p>
                </section>

                <section>
                    <h2 class="text-xl font-black text-slate-950">Hak Pengguna</h2>
                    <p class="mt-3 leading-8 text-slate-600">
                        Pengguna atau pemilik tenant dapat meminta akses, koreksi, pembatasan, ekspor, atau penghapusan data sesuai kemampuan sistem dan ketentuan hukum yang berlaku. Beberapa data audit, transaksi, atau catatan keamanan dapat tetap disimpan bila diperlukan untuk kewajiban hukum, akuntansi, keamanan, atau penyelesaian sengketa.
                    </p>
                </section>

                <section>
                    <h2 class="text-xl font-black text-slate-950">Kontak</h2>
                    <p class="mt-3 leading-8 text-slate-600">
                        Untuk pertanyaan privasi atau permintaan terkait data, hubungi pengelola ServiceKU melalui email dukungan yang tercantum pada halaman aplikasi atau kanal resmi yang diberikan kepada tenant.
                    </p>
                </section>
            </div>
        </article>
    </main>
</body>
</html>
