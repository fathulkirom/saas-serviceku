<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ServiceKU — Platform Manajemen Servis HP & Laptop Terpercaya</title>
    <meta name="description" content="Kelola servis HP, laptop, dan Apple devices dengan sistem POS, inventaris, dan multi-cabang dalam satu platform SaaS.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        body { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
        .hero-gradient {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
        }
        .text-gradient {
            background: linear-gradient(90deg, #06b6d4, #8b5cf6, #ec4899, #06b6d4);
            background-size: 300% 100%;
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            animation: gradient-shift 4s ease infinite;
        }
        @keyframes gradient-shift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        @keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-15px); } }
        .animate-float { animation: float 6s ease-in-out infinite; }
        .animate-float-delayed { animation: float 8s ease-in-out 2s infinite; }
        .glass-card {
            background: rgba(255,255,255,0.08);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.1);
        }
        .feature-card {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255,255,255,0.08);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .feature-card:hover {
            border-color: rgba(139,92,246,0.3);
            transform: translateY(-8px);
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
        }
        .btn-primary {
            background: linear-gradient(135deg, #06b6d4, #8b5cf6, #ec4899);
            background-size: 200% 200%;
            transition: all 0.4s ease;
        }
        .btn-primary:hover {
            background-position: 100% 0;
            box-shadow: 0 20px 40px -10px rgba(139,92,246,0.4);
        }
        .pricing-card {
            background: rgba(15,23,42,0.8);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255,255,255,0.08);
        }
        .pricing-card-featured {
            border-color: rgba(139,92,246,0.5);
            box-shadow: 0 0 60px rgba(139,92,246,0.2);
        }
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.6;
        }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="antialiased text-slate-100 bg-slate-950">
    <!-- Floating blobs background -->
    <div class="fixed inset-0 -z-10 overflow-hidden">
        <div class="blob w-96 h-96 bg-cyan-500/30 -top-48 -left-48 animate-float"></div>
        <div class="blob w-80 h-80 bg-purple-500/30 top-1/3 -right-40 animate-float-delayed"></div>
        <div class="blob w-96 h-96 bg-pink-500/30 bottom-0 left-1/3 animate-float" style="animation-delay: 4s;"></div>
    </div>

    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 glass-card border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-cyan-500 to-purple-600 flex items-center justify-center text-white font-bold shadow-lg shadow-cyan-500/30">SK</div>
                    <span class="text-xl font-bold bg-gradient-to-r from-white to-slate-300 bg-clip-text text-transparent">ServiceKU</span>
                </div>
                <div class="hidden md:flex items-center gap-10">
                    <a href="#features" class="text-sm font-medium text-slate-300 hover:text-white transition-colors">Fitur</a>
                    <a href="#pricing" class="text-sm font-medium text-slate-300 hover:text-white transition-colors">Harga</a>
                    <a href="#contact" class="text-sm font-medium text-slate-300 hover:text-white transition-colors">Kontak</a>
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ route('login.find') }}" class="text-sm font-semibold text-slate-300 hover:text-white px-5 py-2.5 rounded-xl transition-colors">Masuk</a>
                    <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl btn-primary text-white text-sm font-bold hover:-translate-y-0.5 active:translate-y-0 transition-all">
                        Mulai Trial Gratis
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative min-h-screen flex items-center pt-24 pb-20">
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div class="text-center lg:text-left">
                    <div class="inline-flex items-center gap-3 px-5 py-2.5 rounded-full glass-card mb-8">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="text-emerald-300 text-xs font-semibold tracking-wide">Platform Manajemen Servis No. 1 di Indonesia</span>
                    </div>
                    <h1 class="text-5xl sm:text-6xl lg:text-7xl font-extrabold leading-[1.05] mb-6">
                        Kelola Servis
                        <span class="text-gradient">HP & Laptop</span>
                        Jadi Lebih Mudah
                    </h1>
                    <p class="text-lg sm:text-xl text-slate-400 max-w-2xl mx-auto lg:mx-0 mb-10">
                        Platform SaaS all-in-one untuk bengkel servis elektronik — dari <strong class="text-slate-200">one-man operation</strong> hingga <strong class="text-slate-200">enterprise multi-cabang</strong> dengan POS, inventaris komponen, dan laporan keuangan real-time.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center gap-5 justify-center lg:justify-start">
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-4 px-10 py-5 rounded-2xl btn-primary text-white text-base font-bold hover:-translate-y-1 active:translate-y-0 transition-all shadow-2xl">
                            🚀 Mulai Trial Gratis
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </a>
                        <a href="#features" class="inline-flex items-center gap-3 px-8 py-5 rounded-2xl glass-card text-slate-300 text-sm font-semibold hover:text-white hover:-translate-y-0.5 transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0a9 9 0 0118 0z"/></svg>
                            Lihat Demo
                        </a>
                    </div>
                    <div class="flex items-center justify-center lg:justify-start gap-12 mt-14">
                        <div class="text-center">
                            <p class="text-4xl font-extrabold text-white">500+</p>
                            <p class="text-sm text-slate-500 font-medium mt-1">Bengkel Aktif</p>
                        </div>
                        <div class="w-px h-14 bg-slate-800"></div>
                        <div class="text-center">
                            <p class="text-4xl font-extrabold text-white">8</p>
                            <p class="text-sm text-slate-500 font-medium mt-1">Fitur Unggulan</p>
                        </div>
                        <div class="w-px h-14 bg-slate-800"></div>
                        <div class="text-center">
                            <p class="text-4xl font-extrabold text-white">2K+</p>
                            <p class="text-sm text-slate-500 font-medium mt-1">User Aktif</p>
                        </div>
                    </div>
                </div>
                <div class="hidden lg:flex items-center justify-center">
                    <div class="relative w-full max-w-lg">
                        <div class="w-full glass-card rounded-3xl overflow-hidden animate-float">
                            <div class="px-8 py-6 border-b border-white/10">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-cyan-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold">SK</div>
                                        <span class="text-base font-bold text-white">Dashboard</span>
                                    </div>
                                    <div class="flex -space-x-2">
                                        <div class="w-7 h-7 rounded-full bg-cyan-400/30 border-2 border-slate-900"></div>
                                        <div class="w-7 h-7 rounded-full bg-purple-400/30 border-2 border-slate-900"></div>
                                        <div class="w-7 h-7 rounded-full bg-pink-400/30 border-2 border-slate-900"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="p-6 space-y-4">
                                <div class="grid grid-cols-3 gap-3">
                                    <div class="p-4 rounded-2xl bg-cyan-500/10 border border-cyan-500/20">
                                        <p class="text-[11px] text-cyan-300 font-semibold">Servis</p>
                                        <p class="text-2xl font-bold text-cyan-400">12</p>
                                    </div>
                                    <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20">
                                        <p class="text-[11px] text-emerald-300 font-semibold">Selesai</p>
                                        <p class="text-2xl font-bold text-emerald-400">8</p>
                                    </div>
                                    <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20">
                                        <p class="text-[11px] text-amber-300 font-semibold">Pending</p>
                                        <p class="text-2xl font-bold text-amber-400">3</p>
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    <div class="flex items-center gap-4 p-4 rounded-2xl bg-white/5 border border-white/10">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-sky-500/20 to-sky-400/10 flex items-center justify-center text-xs font-bold text-sky-400">MB</div>
                                        <div class="flex-1">
                                            <p class="text-sm font-semibold text-slate-200">MacBook Air M4</p>
                                            <p class="text-xs text-slate-500">NAND Flash replacement</p>
                                        </div>
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-300 border border-amber-500/20">Diagnosa</span>
                                    </div>
                                    <div class="flex items-center gap-4 p-4 rounded-2xl bg-white/5 border border-white/10">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500/20 to-violet-400/10 flex items-center justify-center text-xs font-bold text-violet-400">IP</div>
                                        <div class="flex-1">
                                            <p class="text-sm font-semibold text-slate-200">iPhone 16 Pro Max</p>
                                            <p class="text-xs text-slate-500">Battery replacement</p>
                                        </div>
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-500/10 text-blue-300 border border-blue-500/20">Dikerjakan</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="absolute -bottom-6 -right-6 px-6 py-4 rounded-2xl glass-card border border-white/10 animate-float-delayed">
                            <div class="flex items-center gap-3">
                                <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0a9 9 0 0118 0z"/></svg>
                                <span class="text-sm font-semibold text-slate-200">Service <span class="text-emerald-400">Completed</span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="absolute bottom-0 left-0 right-0">
            <svg viewBox="0 0 1440 120" fill="none" class="w-full h-auto">
                <path d="M0 120V60C240 0 480 120 720 60C960 0 1200 120 1440 60V120H0Z" fill="#020617"/>
            </svg>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="bg-slate-950 py-24 sm:py-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-4xl mx-auto mb-20">
                <span class="inline-flex items-center gap-2 px-5 py-2 rounded-full glass-card text-cyan-300 text-xs font-semibold mb-6">
                    Fitur Lengkap
                </span>
                <h2 class="text-4xl sm:text-5xl font-extrabold text-white mb-6">
                    Semua yang Anda Butuhkan untuk Mengelola Bengkel Servis
                </h2>
                <p class="text-xl text-slate-400">
                    Dari manajemen tiket servis hingga POS dan laporan keuangan — semua dalam satu platform terintegrasi.
                </p>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="feature-card p-8 rounded-3xl">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-cyan-500 to-cyan-600 flex items-center justify-center text-white shadow-lg shadow-cyan-500/30 mb-6">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">High-Density Ticket Management</h3>
                    <p class="text-slate-400 leading-relaxed">Kelola puluhan tiket servis dengan tabel padat, filter real-time, dan inline editing status.</p>
                </div>
                <div class="feature-card p-8 rounded-3xl">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center text-white shadow-lg shadow-emerald-500/30 mb-6">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Fast POS & Invoicing</h3>
                    <p class="text-slate-400 leading-relaxed">POS dua kolom tanpa scroll untuk penjualan sparepart & jasa. Generate invoice PDF dengan satu klik.</p>
                </div>
                <div class="feature-card p-8 rounded-3xl">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center text-white shadow-lg shadow-purple-500/30 mb-6">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Component-Level Inventory</h3>
                    <p class="text-slate-400 leading-relaxed">Lacak stok sparepart hingga level komponen (NAND, IC, flex cable) dengan alert stok menipis.</p>
                </div>
                <div class="feature-card p-8 rounded-3xl">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white shadow-lg shadow-amber-500/30 mb-6">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Multi-Cabang Terpadu</h3>
                    <p class="text-slate-400 leading-relaxed">Kelola banyak cabang dengan transfer stok dan servis antar cabang. Cocok untuk bengkel dengan beberapa lokasi.</p>
                </div>
                <div class="feature-card p-8 rounded-3xl">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-sky-500 to-sky-600 flex items-center justify-center text-white shadow-lg shadow-sky-500/30 mb-6">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Laporan Real-Time</h3>
                    <p class="text-slate-400 leading-relaxed">Pantau pendapatan, servis, dan inventaris dengan laporan harian/mingguan/bulanan yang bisa diexport.</p>
                </div>
                <div class="feature-card p-8 rounded-3xl">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-pink-500 to-rose-600 flex items-center justify-center text-white shadow-lg shadow-pink-500/30 mb-6">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924-1.756-3.35a1.724 1.724 0 001.066-2.573c-1.543.94-3.31-.826-2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Branding & Kustomisasi</h3>
                    <p class="text-slate-400 leading-relaxed">Sesuaikan logo, warna tema, dan informasi toko. Integrasi WhatsApp untuk notifikasi otomatis.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="bg-gradient-to-r from-cyan-600 via-purple-600 to-pink-600 py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-12 text-center">
                <div>
                    <p class="text-5xl font-extrabold text-white mb-2">500+</p>
                    <p class="text-sm text-white/80 font-medium">Bengkel Terdaftar</p>
                </div>
                <div>
                    <p class="text-5xl font-extrabold text-white mb-2">15K+</p>
                    <p class="text-sm text-white/80 font-medium">Servis Terselesaikan</p>
                </div>
                <div>
                    <p class="text-5xl font-extrabold text-white mb-2">98%</p>
                    <p class="text-sm text-white/80 font-medium">Kepuasan Pelanggan</p>
                </div>
                <div>
                    <p class="text-5xl font-extrabold text-white mb-2">24/7</p>
                    <p class="text-sm text-white/80 font-medium">Dukungan Teknis</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="bg-slate-950 py-24 sm:py-32" x-data="{
        selectedBusinessType: 'full_service',
        plans: {{ json_encode($plans) }},
        businessTypes: {
            full_service: 'Servis & Sparepart',
            aksesoris_service: 'Aksesoris & Jasa Servis',
            aksespare_service: 'Pusat Servis & Sparepart',
            gadget_full: 'HP/Laptop Baru + Servis',
            retail_only: 'Retail Saja (Jualan)'
        },
        mapping: {
            services: 'Manajemen Servis Perangkat',
            customers: 'Data Pelanggan',
            products: 'Stok Sparepart & Produk',
            sales: 'Kasir POS & Penjualan',
            reports: 'Laporan Keuangan & Analisis',
            settings: 'Setting Toko & WA Gateway',
            monitoring: 'Log Aktivitas & Audit',
            multi_branch: 'Multi Cabang Toko',
            transfer_stock: 'Kirim Stok Antar Cabang',
            users: 'Manajemen Karyawan',
            expenses: 'Input Pengeluaran Operasional',
            purchases: 'Pembelian Supplier',
            deposits: 'Setoran Kasir Harian',
            checklist: 'Template Ceklis Masuk',
            indents: 'Pre-Order / Inden Sparepart'
        },
        getFeaturesList(plan) {
            const features = plan.features || {};
            const type = this.selectedBusinessType;
            
            let typeFeatures = {};
            if (features[type] && typeof features[type] === 'object') {
                typeFeatures = features[type];
            } else {
                typeFeatures = features;
            }
            
            const list = [];
            
            const maxUsers = features.max_users || typeFeatures.max_users || 0;
            const maxBranches = features.max_branches || typeFeatures.max_branches || 0;

            if (maxUsers > 0) {
                list.push('Maksimal ' + maxUsers + ' Karyawan');
            } else {
                list.push('Karyawan Tidak Terbatas');
            }

            if (maxBranches > 0) {
                list.push('Maksimal ' + maxBranches + ' Cabang');
            } else {
                list.push('Cabang Tidak Terbatas');
            }

            for (const [key, label] of Object.entries(this.mapping)) {
                if (type === 'retail_only' && ['services', 'checklist'].includes(key)) {
                    continue;
                }

                const val = typeFeatures[key];
                if (val === true || val === 'full' || val === 1 || val === '1') {
                    list.push(label);
                } else if (val === 'read_only') {
                    list.push(label + ' (Hanya Lihat)');
                }
            }

            return list;
        },
        formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(num || 0);
        }
    }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-12">
                <span class="inline-flex items-center gap-2 px-5 py-2 rounded-full glass-card text-purple-300 text-xs font-semibold mb-6">
                    Pilihan Paket
                </span>
                <h2 class="text-4xl sm:text-5xl font-extrabold text-white mb-6">
                    Harga yang Sesuai dengan Kebutuhan Bisnis Anda
                </h2>
                <p class="text-xl text-slate-400">
                    Mulai dari satu teknisi hingga bengkel enterprise multi-cabang. Semua paket termasuk trial 14 hari.
                </p>
            </div>

            <div class="max-w-xs mx-auto mb-20 text-center">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-4">Tipe Bisnis Toko Anda</label>
                <select x-model="selectedBusinessType" class="block w-full rounded-xl bg-slate-900 border border-slate-700 text-slate-200 text-sm py-3 px-4 focus:border-purple-500 focus:ring-purple-500">
                    <template x-for="(label, key) in businessTypes" :key="key">
                        <option :value="key" x-text="label"></option>
                    </template>
                </select>
            </div>

            <div class="grid lg:grid-cols-3 gap-8 max-w-6xl mx-auto items-stretch">
                <template x-for="pkg in plans" :key="pkg.name">
                    <div class="relative pricing-card rounded-3xl p-10 flex flex-col justify-between transition-all duration-300"
                        :class="pkg.featured ? 'pricing-card-featured scale-[1.05] z-10' : ''">
                        
                        <template x-if="pkg.featured">
                            <div class="absolute -top-5 left-1/2 -translate-x-1/2 px-6 py-2 rounded-full bg-gradient-to-r from-cyan-500 via-purple-500 to-pink-500 text-white text-xs font-bold shadow-lg">
                                🔥 Paling Populer
                            </div>
                        </template>

                        <div>
                            <div class="mb-8" :class="pkg.featured ? 'mt-4' : ''">
                                <h3 class="text-2xl font-bold text-white" x-text="pkg.name"></h3>
                                <p class="text-sm text-slate-500 mt-2" x-text="pkg.name === 'Basic' ? 'Untuk bengkel kecil dengan 1 teknisi' : (pkg.name === 'Pro' ? 'Untuk bengkel berkembang multi-teknisi' : 'Untuk bengkel besar & korporasi')"></p>
                            </div>
                            
                            <div class="mb-8">
                                <template x-if="pkg.is_promo_active">
                                    <div class="flex items-baseline gap-3">
                                        <span class="text-sm text-slate-500 line-through">Rp <span x-text="formatNumber(pkg.price)"></span></span>
                                        <span class="text-4xl font-extrabold text-rose-400">Rp <span x-text="formatNumber(pkg.promo_price)"></span></span>
                                    </div>
                                </template>
                                <template x-if="!pkg.is_promo_active">
                                    <span class="text-5xl font-extrabold text-white">Rp <span x-text="formatNumber(pkg.price)"></span></span>
                                </template>
                                <span class="text-sm text-slate-500">/bulan</span>
                                <template x-if="pkg.is_promo_active">
                                    <span class="ml-3 inline-flex items-center px-3 py-1 rounded-md bg-rose-500/10 text-rose-300 text-xs font-semibold border border-rose-500/20">
                                        Hemat <span x-text="pkg.discount_percent"></span>%
                                    </span>
                                </template>
                            </div>

                            <ul class="space-y-4 mb-10">
                                <template x-for="f in getFeaturesList(pkg)" :key="f">
                                    <li class="flex items-start gap-3 text-sm text-slate-300">
                                        <svg class="w-5 h-5 text-emerald-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span x-text="f"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>

                        <div>
                            <a :href="'{{ route('register') }}?business_type=' + selectedBusinessType"
                                class="block w-full text-center px-8 py-4 rounded-2xl text-sm font-bold transition-all"
                                :class="pkg.featured ? 'btn-primary text-white hover:-translate-y-1' : 'border-2 border-slate-700 text-slate-200 hover:border-purple-500/30 hover:text-white hover:bg-slate-900'">
                                <span x-text="pkg.name === 'Enterprise' ? 'Hubungi Kami' : 'Mulai Trial'"></span>
                            </a>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="bg-slate-950 py-24">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl sm:text-5xl font-extrabold text-white mb-6">
                Siap Mengubah Cara Anda Mengelola Bengkel Servis?
            </h2>
            <p class="text-xl text-slate-400 max-w-2xl mx-auto mb-10">
                Bergabung dengan 500+ bengkel di Indonesia yang sudah menggunakan ServiceKU untuk meningkatkan efisiensi dan pendapatan.
            </p>
            <a href="{{ route('register') }}" class="inline-flex items-center gap-4 px-12 py-6 rounded-2xl btn-primary text-white text-xl font-bold hover:-translate-y-1 active:translate-y-0 transition-all shadow-2xl">
                🚀 Mulai Trial Gratis — 14 Hari
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
            <p class="text-sm text-slate-500 mt-6">Tidak perlu kartu kredit. Batalkan kapan saja.</p>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact" class="bg-slate-950 border-t border-slate-800 text-slate-400">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-12">
                <div class="lg:col-span-1">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-cyan-500 to-purple-600 flex items-center justify-center text-white font-bold shadow-lg shadow-cyan-500/30">SK</div>
                        <span class="text-xl font-bold text-white">ServiceKU</span>
                    </div>
                    <p class="text-slate-500 leading-relaxed mb-8">Platform manajemen servis terpercaya untuk bengkel HP, laptop, dan Apple devices di Indonesia.</p>
                    <div class="flex items-center gap-4">
                        <a href="#" class="w-10 h-10 rounded-xl bg-slate-900 hover:bg-slate-800 flex items-center justify-center text-slate-400 hover:text-white transition-all">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-xl bg-slate-900 hover:bg-slate-800 flex items-center justify-center text-slate-400 hover:text-white transition-all">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-xl bg-slate-900 hover:bg-slate-800 flex items-center justify-center text-slate-400 hover:text-white transition-all">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.373 0 0 5.373 0 12c0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23A11.509 11.509 0 0112 5.803c1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576C20.566 21.797 24 17.3 24 12c0-6.627-5.373-12-12-12z"/></svg>
                        </a>
                    </div>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-200 uppercase tracking-wider mb-6">Produk</h4>
                    <ul class="space-y-3">
                        <li><a href="#features" class="text-sm text-slate-400 hover:text-white transition-colors">Fitur</a></li>
                        <li><a href="#pricing" class="text-sm text-slate-400 hover:text-white transition-colors">Harga</a></li>
                        <li><a href="{{ route('register') }}" class="text-sm text-slate-400 hover:text-white transition-colors">Daftar</a></li>
                        <li><a href="{{ route('login') }}" class="text-sm text-slate-400 hover:text-white transition-colors">Masuk</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-200 uppercase tracking-wider mb-6">Bantuan</h4>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-sm text-slate-400 hover:text-white transition-colors">Dokumentasi</a></li>
                        <li><a href="#" class="text-sm text-slate-400 hover:text-white transition-colors">Pusat Bantuan</a></li>
                        <li><a href="#" class="text-sm text-slate-400 hover:text-white transition-colors">Status Sistem</a></li>
                        <li><a href="#" class="text-sm text-slate-400 hover:text-white transition-colors">Hubungi Kami</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-200 uppercase tracking-wider mb-6">Kontak</h4>
                    <ul class="space-y-4">
                        <li class="text-sm text-slate-400 flex items-center gap-3">
                            <svg class="w-5 h-5 text-slate-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            hello@serviceku.my.id
                        </li>
                        <li class="text-sm text-slate-400 flex items-center gap-3">
                            <svg class="w-5 h-5 text-slate-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h1C9.716 21 3 14.284 3 6V5z"/></svg>
                            +62 812-3456-7890
                        </li>
                        <li class="text-sm text-slate-400 flex items-center gap-3">
                            <svg class="w-5 h-5 text-slate-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Jakarta, Indonesia
                        </li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-slate-800 mt-16 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-sm text-slate-600">&copy; {{ date('Y') }} ServiceKU. All rights reserved.</p>
                <div class="flex items-center gap-8">
                    <a href="#" class="text-xs text-slate-500 hover:text-slate-300 transition-colors">Kebijakan Privasi</a>
                    <a href="#" class="text-xs text-slate-500 hover:text-slate-300 transition-colors">Syarat & Ketentuan</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>