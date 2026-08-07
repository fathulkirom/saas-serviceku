<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ServiceKU - Operasi Servis, POS, dan Stok dalam Satu Sistem</title>
    <meta name="description" content="ServiceKU membantu bengkel HP, laptop, elektronik, dan retail sparepart mengelola servis, teknisi, stok, POS, cabang, garansi, dan laporan.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        :root {
            --ink: #111827;
            --muted: #64748b;
            --line: #dbe3ef;
            --paper: #ffffff;
            --cream: #f8fafc;
            --teal: #0f766e;
            --teal-2: #14b8a6;
            --amber: #f59e0b;
            --rose: #f43f5e;
            --blue: #2563eb;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--cream);
            color: var(--ink);
        }

        .hero-scene {
            background:
                radial-gradient(circle at 18% 20%, rgba(20, 184, 166, .20), transparent 30%),
                radial-gradient(circle at 82% 18%, rgba(245, 158, 11, .18), transparent 26%),
                linear-gradient(135deg, #f8fafc 0%, #eef7f5 45%, #f7f2e8 100%);
        }

        .nav-glass {
            background: rgba(248, 250, 252, .86);
            backdrop-filter: blur(18px);
            border-bottom: 1px solid rgba(148, 163, 184, .28);
        }

        .brand-mark {
            box-shadow: 0 18px 40px rgba(15, 118, 110, .26);
        }

        .btn-main {
            background: #0f766e;
            color: white;
            box-shadow: 0 14px 32px rgba(15, 118, 110, .25);
        }

        .btn-main:hover {
            background: #115e59;
            transform: translateY(-1px);
        }

        .btn-soft {
            background: rgba(255, 255, 255, .82);
            color: #0f172a;
            border: 1px solid rgba(148, 163, 184, .36);
        }

        .btn-soft:hover {
            background: white;
            transform: translateY(-1px);
        }

        .app-frame {
            background: rgba(255, 255, 255, .92);
            border: 1px solid rgba(15, 23, 42, .10);
            box-shadow: 0 34px 90px rgba(15, 23, 42, .18);
        }

        .device-card,
        .module-card,
        .price-card {
            background: var(--paper);
            border: 1px solid var(--line);
            box-shadow: 0 12px 35px rgba(15, 23, 42, .06);
        }

        .module-card:hover,
        .price-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 46px rgba(15, 23, 42, .10);
        }

        .status-line {
            background: linear-gradient(90deg, var(--teal), var(--amber), var(--blue), var(--rose));
        }

        .timeline-step::before {
            content: '';
            position: absolute;
            left: 1.125rem;
            top: 2.75rem;
            bottom: -1.25rem;
            width: 1px;
            background: #cbd5e1;
        }

        .timeline-step:last-child::before {
            display: none;
        }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="antialiased selection:bg-teal-100 selection:text-teal-950">
    <nav class="fixed inset-x-0 top-0 z-50 nav-glass">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="ServiceKU">
                <span class="brand-mark flex h-10 w-10 items-center justify-center rounded-xl bg-teal-700">
                    <img src="/images/logo.svg" alt="" class="h-6 w-6 brightness-0 invert">
                </span>
                <span class="text-lg font-black tracking-tight text-slate-950">ServiceKU</span>
            </a>

            <div class="hidden items-center gap-7 md:flex">
                <a href="#workflow" class="text-sm font-bold text-slate-600 hover:text-slate-950">Workflow</a>
                <a href="#features" class="text-sm font-bold text-slate-600 hover:text-slate-950">Modul</a>
                <a href="#pricing" class="text-sm font-bold text-slate-600 hover:text-slate-950">Harga</a>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('login.find') }}" class="hidden text-sm font-bold text-slate-600 hover:text-slate-950 sm:inline">Masuk</a>
                <a href="{{ route('register') }}" class="btn-main inline-flex h-10 items-center justify-center rounded-xl px-4 text-sm font-extrabold transition">
                    Coba Gratis
                </a>
            </div>
        </div>
    </nav>

    <main>
        <section class="hero-scene relative overflow-hidden pt-28">
            <div class="mx-auto grid min-h-[calc(100vh-4rem)] max-w-7xl items-center gap-12 px-4 pb-12 sm:px-6 lg:grid-cols-[.92fr_1.08fr] lg:px-8">
                <div class="relative z-10">
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-teal-200 bg-white/70 px-3 py-1.5 text-xs font-black uppercase tracking-[.18em] text-teal-800 shadow-sm">
                        <span class="h-2 w-2 rounded-full bg-teal-500"></span>
                        Control tower untuk bengkel servis
                    </div>
                    <h1 class="max-w-4xl text-4xl font-black leading-[1.05] tracking-tight text-slate-950 sm:text-5xl lg:text-7xl">
                        Servis masuk, teknisi, stok, kasir, dan garansi bergerak dalam satu alur.
                    </h1>
                    <p class="mt-6 max-w-2xl text-base font-semibold leading-8 text-slate-600 sm:text-lg">
                        ServiceKU dibuat untuk operasional harian bengkel HP, laptop, elektronik, dan toko sparepart yang perlu rapi dari meja CS sampai laporan owner.
                    </p>
                    <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('register') }}" class="btn-main inline-flex h-12 items-center justify-center rounded-xl px-6 text-sm font-black transition">
                            Mulai Trial 14 Hari
                        </a>
                        <a href="#workflow" class="btn-soft inline-flex h-12 items-center justify-center rounded-xl px-6 text-sm font-black transition">
                            Lihat Alur Servis
                        </a>
                    </div>
                    <div class="mt-10 grid max-w-xl grid-cols-3 gap-4">
                        <div>
                            <p class="text-3xl font-black text-slate-950">8+</p>
                            <p class="mt-1 text-xs font-bold uppercase tracking-wide text-slate-500">Modul inti</p>
                        </div>
                        <div>
                            <p class="text-3xl font-black text-slate-950">24/7</p>
                            <p class="mt-1 text-xs font-bold uppercase tracking-wide text-slate-500">Tracking publik</p>
                        </div>
                        <div>
                            <p class="text-3xl font-black text-slate-950">Multi</p>
                            <p class="mt-1 text-xs font-bold uppercase tracking-wide text-slate-500">Cabang & role</p>
                        </div>
                    </div>
                </div>

                <div class="relative z-10 pb-8 lg:pb-0">
                    <div class="app-frame overflow-hidden rounded-[1.75rem]">
                        <div class="flex items-center justify-between border-b border-slate-200 bg-slate-950 px-5 py-4 text-white">
                            <div class="flex items-center gap-3">
                                <div class="flex gap-1.5">
                                    <span class="h-2.5 w-2.5 rounded-full bg-rose-400"></span>
                                    <span class="h-2.5 w-2.5 rounded-full bg-amber-300"></span>
                                    <span class="h-2.5 w-2.5 rounded-full bg-teal-300"></span>
                                </div>
                                <span class="text-xs font-black uppercase tracking-[.2em] text-slate-300">Live service desk</span>
                            </div>
                            <span class="rounded-full bg-teal-400/20 px-3 py-1 text-xs font-black text-teal-200">Online</span>
                        </div>

                        <div class="grid bg-white lg:grid-cols-[220px_1fr]">
                            <aside class="hidden border-r border-slate-200 bg-slate-50 p-5 lg:block">
                                <div class="mb-7 flex items-center gap-3">
                                    <div class="h-9 w-9 rounded-xl bg-teal-700"></div>
                                    <div>
                                        <div class="h-3 w-24 rounded bg-slate-900"></div>
                                        <div class="mt-2 h-2 w-16 rounded bg-slate-300"></div>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    @foreach (['Dashboard', 'Servis', 'POS', 'Stok', 'Teknisi', 'Laporan'] as $item)
                                        <div class="{{ $loop->index === 1 ? 'bg-teal-50 text-teal-800' : 'text-slate-500' }} flex h-10 items-center gap-3 rounded-xl px-3 text-xs font-extrabold">
                                            <span class="{{ $loop->index === 1 ? 'bg-teal-600' : 'bg-slate-300' }} h-2.5 w-2.5 rounded-full"></span>
                                            {{ $item }}
                                        </div>
                                    @endforeach
                                </div>
                            </aside>

                            <div class="p-4 sm:p-6">
                                <div class="mb-5 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                                    <div>
                                        <p class="text-xs font-black uppercase tracking-[.18em] text-teal-700">Hari ini</p>
                                        <h2 class="mt-1 text-2xl font-black text-slate-950">12 unit sedang dikerjakan</h2>
                                    </div>
                                    <button class="h-10 rounded-xl bg-slate-950 px-4 text-xs font-black text-white">+ Intake servis</button>
                                </div>

                                <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                                    @foreach ([['Masuk', '28', 'teal'], ['QC', '7', 'amber'], ['Siap Ambil', '15', 'blue'], ['Garansi', '3', 'rose']] as $metric)
                                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                            <p class="text-xs font-extrabold text-slate-500">{{ $metric[0] }}</p>
                                            <p class="mt-2 text-2xl font-black text-slate-950">{{ $metric[1] }}</p>
                                            <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-slate-200">
                                                <div class="h-full rounded-full {{ $metric[2] === 'teal' ? 'bg-teal-500 w-4/5' : '' }}{{ $metric[2] === 'amber' ? 'bg-amber-400 w-2/5' : '' }}{{ $metric[2] === 'blue' ? 'bg-blue-500 w-3/5' : '' }}{{ $metric[2] === 'rose' ? 'bg-rose-500 w-1/4' : '' }}"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="grid gap-4 xl:grid-cols-[1.15fr_.85fr]">
                                    <div class="device-card rounded-2xl p-4">
                                        <div class="mb-4 flex items-center justify-between">
                                            <h3 class="text-sm font-black text-slate-950">Antrian Prioritas</h3>
                                            <span class="rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-black text-amber-700">SLA aktif</span>
                                        </div>
                                        <div class="space-y-3">
                                            @foreach ([['MacBook Pro A2338', 'Ganti IC charging', 'Dikerjakan', 'teal'], ['iPhone 13', 'LCD blank setelah jatuh', 'Menunggu sparepart', 'amber'], ['Samsung A52', 'Klaim garansi servis', 'QC ulang', 'blue']] as $row)
                                                <div class="rounded-xl border border-slate-200 bg-white p-3">
                                                    <div class="flex items-start justify-between gap-3">
                                                        <div>
                                                            <p class="text-sm font-black text-slate-950">{{ $row[0] }}</p>
                                                            <p class="mt-1 text-xs font-semibold text-slate-500">{{ $row[1] }}</p>
                                                        </div>
                                                        <span class="whitespace-nowrap rounded-full px-2.5 py-1 text-[11px] font-black {{ $row[3] === 'teal' ? 'bg-teal-50 text-teal-700' : '' }}{{ $row[3] === 'amber' ? 'bg-amber-50 text-amber-700' : '' }}{{ $row[3] === 'blue' ? 'bg-blue-50 text-blue-700' : '' }}">{{ $row[2] }}</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="device-card rounded-2xl p-4">
                                        <h3 class="mb-4 text-sm font-black text-slate-950">Profit & Stok</h3>
                                        <div class="rounded-2xl bg-slate-950 p-4 text-white">
                                            <p class="text-xs font-bold text-slate-400">Omzet hari ini</p>
                                            <p class="mt-2 text-3xl font-black">Rp 8,7 jt</p>
                                            <div class="mt-4 grid grid-cols-5 items-end gap-2">
                                                @foreach ([35, 54, 42, 76, 68] as $height)
                                                    <span class="rounded-t-lg bg-teal-400" style="height: {{ $height }}px"></span>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="mt-4 space-y-3">
                                            <div class="flex items-center justify-between text-sm">
                                                <span class="font-bold text-slate-600">Baterai iPhone</span>
                                                <span class="font-black text-rose-600">Stok rendah</span>
                                            </div>
                                            <div class="flex items-center justify-between text-sm">
                                                <span class="font-bold text-slate-600">Adaptor laptop</span>
                                                <span class="font-black text-teal-700">Aman</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="workflow" class="bg-white py-20">
            <div class="mx-auto grid max-w-7xl gap-12 px-4 sm:px-6 lg:grid-cols-[.8fr_1.2fr] lg:px-8">
                <div>
                    <p class="text-xs font-black uppercase tracking-[.2em] text-teal-700">Workflow servis</p>
                    <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">Dari unit diterima sampai pelanggan mengambil barang, semua tercatat.</h2>
                    <p class="mt-5 text-base font-semibold leading-8 text-slate-600">Setiap perpindahan status punya jejak audit. CS, teknisi, kasir, owner, dan pelanggan melihat informasi sesuai kebutuhannya.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ([
                        ['01', 'Intake CS', 'Data pelanggan, kondisi unit, foto, keluhan, dan estimasi awal dicatat saat barang masuk.'],
                        ['02', 'Diagnosis teknisi', 'Teknisi membuat diagnosis, quotation, kebutuhan sparepart, dan catatan kerja.'],
                        ['03', 'QC & pembayaran', 'Unit yang selesai masuk QC, dibuat invoice, dibayar di POS, lalu siap diambil.'],
                        ['04', 'Garansi & riwayat', 'Garansi servis, klaim, timeline, dan histori customer tersimpan untuk kunjungan berikutnya.'],
                    ] as $step)
                        <div class="timeline-step relative rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <div class="mb-4 flex h-9 w-9 items-center justify-center rounded-xl bg-teal-700 text-xs font-black text-white">{{ $step[0] }}</div>
                            <h3 class="text-lg font-black text-slate-950">{{ $step[1] }}</h3>
                            <p class="mt-2 text-sm font-semibold leading-7 text-slate-600">{{ $step[2] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="features" class="bg-slate-950 py-20 text-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-12 flex flex-col justify-between gap-6 lg:flex-row lg:items-end">
                    <div class="max-w-2xl">
                        <p class="text-xs font-black uppercase tracking-[.2em] text-teal-300">Modul operasional</p>
                        <h2 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">Bukan cuma landing page cantik. Ini sistem kerja harian.</h2>
                    </div>
                    <p class="max-w-md text-sm font-semibold leading-7 text-slate-300">Semua modul dibuat saling terhubung agar data tidak tercecer di chat, nota manual, dan spreadsheet terpisah.</p>
                </div>

                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    @foreach ([
                        ['Servis Center', 'Tiket, status, teknisi, QC, warranty claim, pickup, dan tracking publik.', 'bg-teal-400'],
                        ['POS & Invoice', 'Penjualan langsung, pembayaran servis, void, retur, dan cetak invoice.', 'bg-amber-300'],
                        ['Inventory', 'Stok sparepart, mutasi, damaged stock, reorder alert, dan multi-branch.', 'bg-blue-400'],
                        ['Monitoring', 'Dashboard owner, audit log, performa teknisi, dan laporan finance.', 'bg-rose-400'],
                    ] as $module)
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-6 transition hover:bg-white/10">
                            <div class="mb-7 h-2 w-14 rounded-full {{ $module[2] }}"></div>
                            <h3 class="text-lg font-black">{{ $module[0] }}</h3>
                            <p class="mt-3 text-sm font-semibold leading-7 text-slate-300">{{ $module[1] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="pricing" class="bg-slate-50 py-20" x-data="{
            selectedBusinessType: 'full_service',
            plans: {{ json_encode($plans) }},
            businessTypes: {
                full_service: 'Servis & Sparepart',
                aksesoris_service: 'Aksesoris & Jasa Servis',
                aksespare_service: 'Pusat Servis & Sparepart',
                gadget_full: 'HP/Laptop Baru + Servis',
                retail_only: 'Retail Saja'
            },
            mapping: {
                services: 'Manajemen servis',
                customers: 'Database pelanggan',
                products: 'Stok barang',
                sales: 'POS dan invoice',
                reports: 'Laporan bisnis',
                settings: 'Pengaturan toko',
                monitoring: 'Monitoring dan audit',
                multi_branch: 'Multi cabang',
                transfer_stock: 'Mutasi stok',
                users: 'Manajemen staf',
                expenses: 'Catatan biaya'
            },
            getFeaturesList(plan) {
                const features = plan.features || {};
                const typeFeatures = typeof features[this.selectedBusinessType] === 'object' ? features[this.selectedBusinessType] : features;
                const list = [];
                const maxUsers = typeFeatures.max_users || features.max_users || 0;
                const maxBranches = typeFeatures.max_branches || features.max_branches || 0;
                list.push(maxUsers > 0 ? `Maks. ${maxUsers} karyawan` : 'Karyawan tanpa batas');
                list.push(maxBranches > 0 ? `Maks. ${maxBranches} cabang` : 'Cabang tanpa batas');
                for (const [key, label] of Object.entries(this.mapping)) {
                    if (this.selectedBusinessType === 'retail_only' && key === 'services') continue;
                    const val = typeFeatures[key];
                    if (val === true || val === 'full' || val === 1 || val === '1') list.push(label);
                    if (val === 'read_only') list.push(`${label} read only`);
                }
                return list.slice(0, 9);
            },
            formatNumber(num) {
                return new Intl.NumberFormat('id-ID').format(num || 0);
            }
        }">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-10 max-w-2xl text-center">
                    <p class="text-xs font-black uppercase tracking-[.2em] text-teal-700">Paket berlangganan</p>
                    <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">Mulai kecil, tetap siap naik kelas.</h2>
                    <p class="mt-4 text-sm font-semibold leading-7 text-slate-600">Pilih paket sesuai fokus toko. Data paket tetap mengikuti konfigurasi plan di sistem.</p>
                </div>

                <div class="mx-auto mb-10 max-w-sm">
                    <label class="mb-2 block text-xs font-black uppercase tracking-[.16em] text-slate-500">Fokus bisnis</label>
                    <select x-model="selectedBusinessType" class="h-12 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm font-extrabold text-slate-900 outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-100">
                        <template x-for="(label, key) in businessTypes" :key="key">
                            <option :value="key" x-text="label"></option>
                        </template>
                    </select>
                </div>

                <div class="grid gap-6 lg:grid-cols-3">
                    <template x-for="pkg in plans" :key="pkg.slug || pkg.name">
                        <article class="price-card relative flex rounded-2xl p-6 transition" :class="pkg.featured ? 'ring-2 ring-teal-700 lg:-translate-y-3' : ''">
                            <div class="flex min-h-[520px] w-full flex-col">
                                <template x-if="pkg.featured">
                                    <div class="absolute -top-3 left-6 rounded-full bg-teal-700 px-3 py-1 text-[11px] font-black uppercase tracking-wide text-white">Rekomendasi</div>
                                </template>
                                <h3 class="text-2xl font-black text-slate-950" x-text="pkg.name"></h3>
                                <div class="mt-6 border-b border-slate-200 pb-6">
                                    <template x-if="pkg.is_promo_active">
                                        <p class="mb-1 text-sm font-bold text-slate-400 line-through">Rp <span x-text="formatNumber(pkg.price)"></span></p>
                                    </template>
                                    <p class="text-4xl font-black tracking-tight text-slate-950">
                                        <span class="text-xl">Rp</span>
                                        <span x-text="formatNumber(pkg.is_promo_active ? pkg.promo_price : pkg.price)"></span>
                                    </p>
                                    <p class="mt-1 text-sm font-bold text-slate-500" x-text="pkg.trial_days > 0 ? 'per bulan, trial tersedia' : 'per bulan'"></p>
                                </div>
                                <ul class="mt-6 flex-1 space-y-3">
                                    <template x-for="feature in getFeaturesList(pkg)" :key="feature">
                                        <li class="flex gap-3 text-sm font-bold leading-6 text-slate-700">
                                            <span class="mt-1 flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-teal-100 text-[10px] text-teal-800">✓</span>
                                            <span x-text="feature"></span>
                                        </li>
                                    </template>
                                </ul>
                                <a :href="'{{ route('register') }}?plan=' + pkg.slug + '&business_type=' + selectedBusinessType"
                                   class="mt-8 inline-flex h-12 items-center justify-center rounded-xl text-sm font-black transition"
                                   :class="pkg.featured ? 'btn-main' : 'btn-soft'">
                                    <span x-text="pkg.name === 'Enterprise' ? 'Konsultasi Paket' : (pkg.trial_days > 0 ? 'Mulai Trial' : 'Pilih Paket')"></span>
                                </a>
                            </div>
                        </article>
                    </template>
                </div>
            </div>
        </section>

        <section class="bg-white py-20">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-[2rem] bg-slate-950">
                    <div class="status-line h-2"></div>
                    <div class="grid gap-8 p-8 text-white sm:p-10 lg:grid-cols-[1fr_auto] lg:items-center">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[.2em] text-teal-300">Siap operasional</p>
                            <h2 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">Bawa alur bengkelmu ke sistem yang lebih rapi minggu ini.</h2>
                            <p class="mt-4 max-w-2xl text-sm font-semibold leading-7 text-slate-300">Mulai dari trial, setup toko, import data produk/customer, lalu jalankan servis harian tanpa pindah-pindah aplikasi.</p>
                        </div>
                        <a href="{{ route('register') }}" class="inline-flex h-12 items-center justify-center rounded-xl bg-white px-6 text-sm font-black text-slate-950 transition hover:bg-teal-50">
                            Daftar Sekarang
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t border-slate-200 bg-slate-50">
        <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 py-10 sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-teal-700">
                    <img src="/images/logo.svg" alt="" class="h-5 w-5 brightness-0 invert">
                </span>
                <div>
                    <p class="text-sm font-black text-slate-950">ServiceKU</p>
                    <p class="text-xs font-bold text-slate-500">Operasi bengkel lebih rapi.</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-4 text-xs font-bold text-slate-500">
                <a href="{{ route('privacy') }}" class="hover:text-teal-700">Kebijakan Privasi</a>
                <a href="{{ route('terms') }}" class="hover:text-teal-700">Syarat Layanan</a>
                <span>&copy; {{ date('Y') }} ServiceKU. All rights reserved.</span>
            </div>
        </div>
    </footer>
</body>
</html>
