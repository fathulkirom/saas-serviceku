<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ServiceKU - Sistem Manajemen Bengkel & Servis</title>
    <meta name="description" content="Aplikasi kasir, inventaris, dan manajemen servis untuk bengkel elektronik modern.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        body { 
            font-family: 'Inter', system-ui, -apple-system, sans-serif; 
            background-color: #fafafa; /* zinc-50 */
            color: #18181b; /* zinc-900 */
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Subtle grid background for modern SaaS look */
        .bg-grid {
            background-size: 40px 40px;
            background-image: 
                linear-gradient(to right, rgba(0, 0, 0, 0.04) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(0, 0, 0, 0.04) 1px, transparent 1px);
            mask-image: linear-gradient(to bottom, black 20%, transparent 100%);
            -webkit-mask-image: linear-gradient(to bottom, black 20%, transparent 100%);
            z-index: -1;
        }

        .glass-header {
            background: rgba(250, 250, 250, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .btn-primary {
            background-color: #18181b; /* zinc-900 */
            color: #ffffff;
            transition: all 0.2s ease;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .btn-primary:hover {
            background-color: #27272a; /* zinc-800 */
            transform: translateY(-1px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .btn-secondary {
            background-color: #ffffff;
            color: #18181b;
            border: 1px solid #e4e4e7; /* zinc-200 */
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        .btn-secondary:hover {
            background-color: #f4f4f5; /* zinc-100 */
            border-color: #d4d4d8; /* zinc-300 */
        }

        .feature-card {
            background: #ffffff;
            border: 1px solid #e4e4e7; /* zinc-200 */
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
        }
        .feature-card:hover {
            border-color: #a1a1aa; /* zinc-400 */
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
            transform: translateY(-2px);
        }

        .pricing-card {
            background: #ffffff;
            border: 1px solid #e4e4e7;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        .pricing-card.featured {
            border: 2px solid #18181b; /* zinc-900 */
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            transform: scale(1.02);
            z-index: 10;
        }

        /* Mockup Window Styling */
        .mockup-window {
            border-radius: 16px;
            border: 1px solid rgba(0,0,0,0.1);
            background-color: #ffffff;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }
        .mockup-header {
            background-color: #f4f4f5;
            border-bottom: 1px solid #e4e4e7;
            padding: 12px 16px;
            display: flex;
            align-items: center;
        }
        .mockup-dots {
            display: flex;
            gap: 6px;
        }
        .mockup-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }
        .mockup-dot.red { background-color: #ff5f56; }
        .mockup-dot.yellow { background-color: #ffbd2e; }
        .mockup-dot.green { background-color: #27c93f; }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="antialiased selection:bg-zinc-200">
    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 glass-header">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-zinc-900 flex items-center justify-center text-white font-black text-sm">SK</div>
                    <span class="text-lg font-bold tracking-tight text-zinc-900">ServiceKU</span>
                </div>
                <div class="hidden md:flex items-center gap-8">
                    <a href="#features" class="text-sm font-semibold text-zinc-600 hover:text-zinc-900 transition-colors">Fitur</a>
                    <a href="#pricing" class="text-sm font-semibold text-zinc-600 hover:text-zinc-900 transition-colors">Harga</a>
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ route('login.find') }}" class="text-sm font-semibold text-zinc-600 hover:text-zinc-900 transition-colors hidden sm:block">Masuk</a>
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-4 py-2 rounded-lg btn-primary text-sm font-semibold">
                        Coba Gratis
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative pt-32 pb-24 overflow-hidden min-h-[90vh] flex flex-col justify-center">
        <div class="absolute inset-0 bg-grid"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white border border-zinc-200 shadow-sm text-zinc-600 text-xs font-bold uppercase tracking-wider mb-8">
                Platform Operasional Bengkel
            </div>
            
            <h1 class="text-5xl md:text-6xl lg:text-7xl font-black tracking-tighter text-zinc-900 mb-6 max-w-4xl mx-auto leading-[1.1]">
                Sistem Servis & Kasir. <br>
                <span class="text-zinc-400">Mudah & Otomatis.</span>
            </h1>
            
            <p class="text-lg md:text-xl text-zinc-600 max-w-2xl mx-auto mb-10 font-medium">
                Satu aplikasi untuk mengelola penerimaan servis, stok sparepart, dan laporan penjualan secara akurat dan profesional.
            </p>
            
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('register') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 rounded-xl btn-primary text-base font-bold">
                    Mulai Trial 14 Hari
                </a>
                <a href="#features" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 rounded-xl btn-secondary text-base font-bold">
                    Jelajahi Fitur
                </a>
            </div>

            <!-- UI Mockup Dashboard Abstract -->
            <div class="mt-20 flex justify-center max-w-6xl mx-auto">
                <div class="mockup-window w-full">
                    <div class="mockup-header">
                        <div class="mockup-dots">
                            <div class="mockup-dot red"></div>
                            <div class="mockup-dot yellow"></div>
                            <div class="mockup-dot green"></div>
                        </div>
                    </div>
                    <div class="bg-zinc-50 flex border-t border-zinc-100">
                        <!-- Sidebar -->
                        <div class="w-64 border-r border-zinc-200 bg-white p-6 hidden md:block min-h-[400px]">
                            <div class="space-y-4">
                                <div class="h-8 flex items-center gap-3 mb-8">
                                    <div class="w-6 h-6 rounded bg-zinc-900"></div>
                                    <div class="h-4 w-24 bg-zinc-200 rounded"></div>
                                </div>
                                <div class="space-y-2">
                                    <div class="h-10 w-full bg-zinc-100 rounded-lg flex items-center px-3 gap-3">
                                        <div class="w-4 h-4 bg-zinc-300 rounded"></div>
                                        <div class="h-3 w-16 bg-zinc-300 rounded"></div>
                                    </div>
                                    <div class="h-10 w-full rounded-lg flex items-center px-3 gap-3">
                                        <div class="w-4 h-4 bg-zinc-200 rounded"></div>
                                        <div class="h-3 w-20 bg-zinc-200 rounded"></div>
                                    </div>
                                    <div class="h-10 w-full rounded-lg flex items-center px-3 gap-3">
                                        <div class="w-4 h-4 bg-zinc-200 rounded"></div>
                                        <div class="h-3 w-14 bg-zinc-200 rounded"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Main Content Area -->
                        <div class="flex-1 p-8">
                            <div class="flex justify-between items-center mb-8">
                                <div>
                                    <div class="h-6 w-32 bg-zinc-800 rounded mb-2"></div>
                                    <div class="h-4 w-48 bg-zinc-300 rounded"></div>
                                </div>
                                <div class="h-10 w-32 bg-zinc-900 rounded-lg"></div>
                            </div>
                            <div class="grid grid-cols-3 gap-6 mb-8">
                                <div class="bg-white border border-zinc-200 rounded-xl p-5 shadow-sm">
                                    <div class="h-3 w-24 bg-zinc-300 rounded mb-4"></div>
                                    <div class="h-8 w-16 bg-zinc-800 rounded"></div>
                                </div>
                                <div class="bg-white border border-zinc-200 rounded-xl p-5 shadow-sm">
                                    <div class="h-3 w-24 bg-zinc-300 rounded mb-4"></div>
                                    <div class="h-8 w-20 bg-zinc-800 rounded"></div>
                                </div>
                                <div class="bg-white border border-zinc-200 rounded-xl p-5 shadow-sm">
                                    <div class="h-3 w-24 bg-zinc-300 rounded mb-4"></div>
                                    <div class="h-8 w-32 bg-zinc-800 rounded"></div>
                                </div>
                            </div>
                            <div class="bg-white border border-zinc-200 rounded-xl shadow-sm overflow-hidden">
                                <div class="h-12 bg-zinc-50 border-b border-zinc-200"></div>
                                <div class="p-4 space-y-4">
                                    <div class="h-10 bg-zinc-50 rounded"></div>
                                    <div class="h-10 bg-zinc-50 rounded"></div>
                                    <div class="h-10 bg-zinc-50 rounded"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="border-y border-zinc-200 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center divide-x divide-zinc-100">
                <div>
                    <p class="text-4xl font-black text-zinc-900 mb-1">500+</p>
                    <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Toko Aktif</p>
                </div>
                <div>
                    <p class="text-4xl font-black text-zinc-900 mb-1">15K+</p>
                    <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Servis Selesai</p>
                </div>
                <div>
                    <p class="text-4xl font-black text-zinc-900 mb-1">8</p>
                    <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Modul Inti</p>
                </div>
                <div>
                    <p class="text-4xl font-black text-zinc-900 mb-1">99%</p>
                    <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Reliabilitas</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-24 bg-zinc-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-3xl font-black text-zinc-900 mb-4 tracking-tight">
                    Fitur Lengkap Bengkel Modern
                </h2>
                <p class="text-zinc-600 font-medium">
                    Semua yang Anda butuhkan untuk mengoperasikan layanan servis dan penjualan dalam satu tempat.
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Feature 1 -->
                <div class="feature-card p-8 rounded-2xl">
                    <div class="w-12 h-12 rounded-xl bg-zinc-100 flex items-center justify-center text-zinc-900 mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-zinc-900 mb-2">Manajemen Tiket</h3>
                    <p class="text-sm text-zinc-600 leading-relaxed font-medium">Pencatatan servis masuk yang rapi dengan status tracking yang jelas. Meminimalkan risiko kehilangan barang.</p>
                </div>
                <!-- Feature 2 -->
                <div class="feature-card p-8 rounded-2xl">
                    <div class="w-12 h-12 rounded-xl bg-zinc-100 flex items-center justify-center text-zinc-900 mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-zinc-900 mb-2">Kasir (POS)</h3>
                    <p class="text-sm text-zinc-600 leading-relaxed font-medium">Lakukan transaksi untuk biaya jasa servis maupun penjualan produk secara instan dan akurat.</p>
                </div>
                <!-- Feature 3 -->
                <div class="feature-card p-8 rounded-2xl">
                    <div class="w-12 h-12 rounded-xl bg-zinc-100 flex items-center justify-center text-zinc-900 mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-zinc-900 mb-2">Inventaris Sparepart</h3>
                    <p class="text-sm text-zinc-600 leading-relaxed font-medium">Pantau pergerakan stok komponen. Sistem memberi tahu saat stok menipis secara otomatis.</p>
                </div>
                <!-- Feature 4 -->
                <div class="feature-card p-8 rounded-2xl">
                    <div class="w-12 h-12 rounded-xl bg-zinc-100 flex items-center justify-center text-zinc-900 mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-zinc-900 mb-2">Laporan Analitik</h3>
                    <p class="text-sm text-zinc-600 leading-relaxed font-medium">Lihat ringkasan laba rugi, performa teknisi, dan transaksi penjualan dalam satu dasbor yang bersih.</p>
                </div>
                <!-- Feature 5 -->
                <div class="feature-card p-8 rounded-2xl">
                    <div class="w-12 h-12 rounded-xl bg-zinc-100 flex items-center justify-center text-zinc-900 mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-zinc-900 mb-2">Dukungan Multi-Cabang</h3>
                    <p class="text-sm text-zinc-600 leading-relaxed font-medium">Buka cabang baru tanpa ribet. Sentralisasi laporan dan mutasi stok antar cabang dengan mudah.</p>
                </div>
                <!-- Feature 6 -->
                <div class="feature-card p-8 rounded-2xl">
                    <div class="w-12 h-12 rounded-xl bg-zinc-100 flex items-center justify-center text-zinc-900 mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-zinc-900 mb-2">WhatsApp Gateway</h3>
                    <p class="text-sm text-zinc-600 leading-relaxed font-medium">Otomatis kirim nota, update status servis, dan tagihan ke WhatsApp pelanggan Anda.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-24 bg-white" x-data="{
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
            services: 'Manajemen Servis',
            customers: 'Database Pelanggan',
            products: 'Stok Barang',
            sales: 'Point of Sales (Kasir)',
            reports: 'Laporan Finansial',
            settings: 'Pengaturan & WA',
            monitoring: 'Audit Log',
            multi_branch: 'Multi Cabang',
            transfer_stock: 'Mutasi Stok',
            users: 'Manajemen Staf',
            expenses: 'Pencatatan Biaya'
        },
        getFeaturesList(plan) {
            const features = plan.features || {};
            const type = this.selectedBusinessType;
            
            let typeFeatures = features[type] || features;
            if (typeof typeFeatures !== 'object') typeFeatures = features;
            
            const list = [];
            const maxUsers = typeFeatures.max_users || features.max_users || 0;
            const maxBranches = typeFeatures.max_branches || features.max_branches || 0;

            list.push(maxUsers > 0 ? `Maks. ${maxUsers} Karyawan` : 'Karyawan Tanpa Batas');
            list.push(maxBranches > 0 ? `Maks. ${maxBranches} Cabang` : 'Cabang Tanpa Batas');

            for (const [key, label] of Object.entries(this.mapping)) {
                if (type === 'retail_only' && key === 'services') continue;

                const val = typeFeatures[key];
                if (val === true || val === 'full' || val === 1 || val === '1') {
                    list.push(label);
                } else if (val === 'read_only') {
                    list.push(`${label} (Read Only)`);
                }
            }
            return list;
        },
        formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(num || 0);
        }
    }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <h2 class="text-3xl font-black text-zinc-900 mb-4 tracking-tight">Harga Transparan</h2>
                <p class="text-zinc-600 font-medium">Pilih paket berlangganan sesuai skala bisnis Anda.</p>
            </div>

            <div class="max-w-xs mx-auto mb-12">
                <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-2 text-center">Fokus Bisnis Anda</label>
                <select x-model="selectedBusinessType" class="block w-full rounded-xl bg-white border border-zinc-200 text-zinc-900 text-sm font-semibold py-2.5 px-4 focus:border-zinc-900 focus:ring-zinc-900 outline-none transition-colors shadow-sm">
                    <template x-for="(label, key) in businessTypes" :key="key">
                        <option :value="key" x-text="label"></option>
                    </template>
                </select>
            </div>

            <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto items-start">
                <template x-for="pkg in plans" :key="pkg.name">
                    <div class="pricing-card rounded-2xl p-8 relative flex flex-col h-full" :class="pkg.featured ? 'featured md:-translate-y-4' : ''">
                        <template x-if="pkg.featured">
                            <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-4 py-1 rounded-full bg-zinc-900 text-white text-[10px] font-bold tracking-widest uppercase shadow-sm">
                                Paling Diminati
                            </div>
                        </template>

                        <div class="mb-6 text-center">
                            <h3 class="text-2xl font-black text-zinc-900 mb-2" x-text="pkg.name"></h3>
                        </div>
                        
                        <div class="mb-8 pb-8 border-b border-zinc-100 text-center">
                            <template x-if="pkg.is_promo_active">
                                <div class="flex flex-col items-center justify-center gap-1 mb-1">
                                    <span class="text-sm font-medium text-zinc-400 line-through">Rp <span x-text="formatNumber(pkg.price)"></span></span>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-50 text-red-600">Hemat <span x-text="pkg.discount_percent"></span>%</span>
                                </div>
                            </template>
                            <div class="flex items-end justify-center gap-1">
                                <span class="text-4xl font-black text-zinc-900 tracking-tight">
                                    <span class="text-2xl font-bold align-top">Rp</span>
                                    <span x-text="formatNumber(pkg.is_promo_active ? pkg.promo_price : pkg.price)"></span>
                                </span>
                                <span class="text-sm font-bold text-zinc-400 mb-1">/bln</span>
                            </div>
                        </div>

                        <ul class="space-y-4 mb-8 flex-1">
                            <template x-for="f in getFeaturesList(pkg)" :key="f">
                                <li class="flex items-start gap-3 text-sm font-medium text-zinc-700">
                                    <svg class="w-5 h-5 text-zinc-900 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span x-text="f"></span>
                                </li>
                            </template>
                        </ul>

                        <a :href="'{{ route('register') }}?plan=' + pkg.slug + '&business_type=' + selectedBusinessType"
                            class="block w-full text-center px-6 py-3 rounded-xl text-sm font-bold transition-all"
                            :class="pkg.featured ? 'btn-primary' : 'btn-secondary'">
                            <span x-text="pkg.name === 'Enterprise' ? 'Hubungi Sales' : 'Mulai Trial'"></span>
                        </a>
                    </div>
                </template>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-zinc-200 bg-zinc-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-12">
                <div class="col-span-2 md:col-span-1">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-6 h-6 rounded bg-zinc-900 flex items-center justify-center text-white text-[10px] font-bold">SK</div>
                        <span class="text-base font-bold text-zinc-900">ServiceKU</span>
                    </div>
                    <p class="text-sm font-medium text-zinc-500 leading-relaxed">Platform kasir & manajemen modern untuk standar operasional bengkel servis dan retail elektronik.</p>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-zinc-900 uppercase tracking-wider mb-4">Produk</h4>
                    <ul class="space-y-3">
                        <li><a href="#features" class="text-sm font-medium text-zinc-500 hover:text-zinc-900 transition-colors">Fitur Utama</a></li>
                        <li><a href="#pricing" class="text-sm font-medium text-zinc-500 hover:text-zinc-900 transition-colors">Harga</a></li>
                        <li><a href="{{ route('login') }}" class="text-sm font-medium text-zinc-500 hover:text-zinc-900 transition-colors">Masuk</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-zinc-900 uppercase tracking-wider mb-4">Dukungan</h4>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-sm font-medium text-zinc-500 hover:text-zinc-900 transition-colors">Dokumentasi</a></li>
                        <li><a href="#" class="text-sm font-medium text-zinc-500 hover:text-zinc-900 transition-colors">FAQ</a></li>
                        <li><a href="#" class="text-sm font-medium text-zinc-500 hover:text-zinc-900 transition-colors">Kontak Support</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-zinc-900 uppercase tracking-wider mb-4">Hubungi Kami</h4>
                    <ul class="space-y-3">
                        <li class="text-sm font-medium text-zinc-500">support@serviceku.com</li>
                        <li class="text-sm font-medium text-zinc-500">+62 812 3456 7890</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-zinc-200 pt-8 flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-xs font-semibold text-zinc-400">&copy; {{ date('Y') }} ServiceKU. All rights reserved.</p>
                <div class="flex items-center gap-6">
                    <a href="#" class="text-xs font-semibold text-zinc-400 hover:text-zinc-600 transition-colors">Kebijakan Privasi</a>
                    <a href="#" class="text-xs font-semibold text-zinc-400 hover:text-zinc-600 transition-colors">Syarat & Ketentuan</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>