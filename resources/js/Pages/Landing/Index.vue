<template>
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50">
        <!-- Navigation -->
        <nav class="sk-bg-card/80 backdrop-blur-sm border-b sk-border-light sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-14 md:h-16 items-center">
                    <div class="flex items-center">
                        <h1 class="text-xl md:text-2xl font-bold sk-text-primary-brand">ServiceKU</h1>
                    </div>
                    <!-- Desktop Nav -->
                    <div class="hidden sm:flex items-center gap-4">
                        <a href="#features" class="text-sm sk-text-secondary hover:sk-text-primary-brand">Fitur</a>
                        <a href="#pricing" class="text-sm sk-text-secondary hover:sk-text-primary-brand">Harga</a>
                        <a href="#contact" class="text-sm sk-text-secondary hover:sk-text-primary-brand">Kontak</a>
                        <Link :href="route('register')" class="px-3 md:px-4 py-2 sk-bg-card sk-text-primary-brand border sk-border-primary rounded-md text-xs md:text-sm hover:sk-bg-primary-soft whitespace-nowrap">
                            Daftar
                        </Link>
                        <Link :href="route('login')" class="px-3 md:px-4 py-2 sk-bg-primary text-white rounded-md text-xs md:text-sm hover:sk-bg-primary whitespace-nowrap">
                            Login
                        </Link>
                    </div>
                    <!-- Mobile Nav -->
                    <div class="sm:hidden flex items-center gap-2">
                        <Link :href="route('login')" class="px-3 py-1.5 sk-bg-primary text-white rounded-md text-xs hover:sk-bg-primary">
                            Login
                        </Link>
                        <KButton  @click="showMobileMenu = !showMobileMenu" class="p-2 sk-text-secondary hover:sk-text-primary">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path v-if="!showMobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </KButton>
                    </div>
                </div>
                <!-- Mobile Menu -->
                <div v-if="showMobileMenu" class="sm:hidden pb-3 border-t sk-border-light pt-2">
                    <div class="flex flex-col gap-2">
                        <a href="#features" @click="showMobileMenu = false" class="px-3 py-2 text-sm sk-text-secondary hover:sk-bg-hover rounded-md">Fitur</a>
                        <a href="#pricing" @click="showMobileMenu = false" class="px-3 py-2 text-sm sk-text-secondary hover:sk-bg-hover rounded-md">Harga</a>
                        <a href="#contact" @click="showMobileMenu = false" class="px-3 py-2 text-sm sk-text-secondary hover:sk-bg-hover rounded-md">Kontak</a>
                        <Link :href="route('register')" @click="showMobileMenu = false" class="px-3 py-2 text-sm sk-text-primary-brand hover:sk-bg-primary-soft rounded-md font-medium">
                            Daftar
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero -->
        <header class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12 md:pt-20 pb-12 md:pb-16">
            <div class="text-center">
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold sk-text-primary mb-4 md:mb-6 leading-tight">
                    Kelola Servis HP & Laptop<br>
                    <span class="sk-text-primary-brand">Jadi Lebih Mudah</span>
                </h2>
                <p class="text-base sm:text-lg md:text-xl sk-text-secondary mb-6 md:mb-8 max-w-2xl mx-auto px-2">
                    Satu aplikasi untuk mencatat servis, mengelola stok, kasir, dan laporan toko.
                </p>
                <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center items-center px-4">
                    <Link :href="route('register')" class="w-full sm:w-auto px-6 md:px-8 py-3 sk-bg-primary text-white rounded-lg text-sm md:text-lg font-semibold hover:sk-bg-primary shadow-lg shadow-indigo-200 text-center">
                        Mulai Trial Gratis
                    </Link>
                    <a href="#features" class="w-full sm:w-auto px-6 md:px-8 py-3 border sk-border sk-text-primary rounded-lg text-sm md:text-lg font-semibold hover:sk-bg-hover text-center">
                        Lihat Fitur
                    </a>
                </div>
            </div>
        </header>

        <!-- Stats -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12 md:pb-16">
            <div class="grid grid-cols-3 gap-4 md:gap-8 max-w-2xl mx-auto">
                <div class="text-center">
                    <p class="text-2xl md:text-3xl font-bold sk-text-primary-brand">{{ stats.tenants }}+</p>
                    <p class="text-xs md:text-sm sk-text-muted">Toko Aktif</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl md:text-3xl font-bold sk-text-primary-brand">{{ stats.features }}</p>
                    <p class="text-xs md:text-sm sk-text-muted">Fitur Unggulan</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl md:text-3xl font-bold sk-text-primary-brand">{{ stats.users }}+</p>
                    <p class="text-xs md:text-sm sk-text-muted">User Aktif</p>
                </div>
            </div>
        </div>

        <!-- Features -->
        <div id="features" class="sk-bg-card py-12 md:py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h3 class="text-2xl md:text-3xl font-bold text-center sk-text-primary mb-8 md:mb-12">Fitur Lengkap</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 md:gap-8">
                    <div v-for="feat in features" :key="feat.title" class="p-6 rounded-xl border sk-border-light hover:shadow-lg transition">
                        <div class="text-3xl mb-4">{{ feat.icon }}</div>
                        <h4 class="text-lg font-semibold sk-text-primary mb-2">{{ feat.title }}</h4>
                        <p class="text-sm sk-text-secondary">{{ feat.desc }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div id="pricing" class="py-12 md:py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h3 class="text-2xl md:text-3xl font-bold text-center sk-text-primary mb-4">Pilih Paket</h3>
                <p class="text-sm sk-text-muted text-center mb-8 max-w-md mx-auto">Fitur disesuaikan secara transparan berdasarkan tipe bisnis Anda.</p>
                
                <!-- Business Type Selector -->
                <div class="max-w-xs mx-auto mb-12">
                    <label class="block text-xs font-semibold sk-text-muted uppercase tracking-wider text-center mb-2">Tipe Bisnis Toko Anda</label>
                    <KSelect  v-model="selectedBusinessType" class="block w-full rounded-lg sk-border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm sk-bg-card py-2.5 px-3">
                        <option v-for="(label, key) in BUSINESS_TYPES" :key="key" :value="key">
                            💼 {{ label }}
                        </option>
                    </KSelect>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 md:gap-8 max-w-5xl mx-auto">
                    <div v-for="pkg in plans" :key="pkg.name" class="p-6 rounded-xl border flex flex-col justify-between" :class="pkg.featured ? 'border-indigo-500 shadow-lg shadow-indigo-100 sk-bg-card' : 'sk-border sk-bg-card'">
                        <div>
                            <h4 class="text-lg font-semibold sk-text-primary">{{ pkg.name }}</h4>

                            <!-- Pricing with promo -->
                            <div class="mt-2 flex items-baseline gap-2">
                                <template v-if="pkg.is_promo_active">
                                    <p class="text-lg sk-text-muted line-through">Rp {{ formatNumber(pkg.price) }}</p>
                                    <p class="text-3xl font-bold sk-text-danger">Rp {{ formatNumber(pkg.promo_price) }}</p>
                                    <span class="px-1.5 py-0.5 text-[10px] font-bold sk-bg-danger-soft sk-text-danger rounded">DISKON {{ pkg.discount_percent }}%</span>
                                </template>
                                <template v-else>
                                    <p class="text-3xl font-bold sk-text-primary-brand">Rp {{ formatNumber(pkg.price) }}</p>
                                </template>
                            </div>
                            <p class="text-sm sk-text-muted">/bulan</p>
                            <p v-if="pkg.is_promo_active" class="text-xs sk-text-danger mt-1">🎉 Promo terbatas! {{ pkg.trial_days > 0 ? 'Trial '+pkg.trial_days+' hari + ' : '' }}Daftar sekarang</p>
                            <p v-else-if="pkg.trial_days > 0" class="text-xs sk-text-success mt-1">✓ Trial {{ pkg.trial_days }} hari gratis</p>

                            <ul class="mt-6 space-y-2.5">
                                <li v-for="f in getFeaturesList(pkg.features)" :key="f" class="text-xs sk-text-secondary flex items-start gap-2">
                                    <svg class="h-3.5 w-3.5 sk-text-success shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>{{ f }}</span>
                                </li>
                            </ul>
                        </div>
                        <Link :href="route('register', { business_type: selectedBusinessType })" class="mt-6 block text-center px-4 py-2.5 rounded-md text-sm font-semibold" :class="pkg.featured ? 'sk-bg-primary text-white hover:sk-bg-primary' : 'sk-bg-hover sk-text-primary hover:sk-bg-hover'">
                            {{ pkg.is_promo_active ? 'Ambil Promo' : 'Mulai Trial' }}
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer id="contact" class="sk-bg-inverse sk-text-muted py-8 md:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h5 class="text-xl font-bold text-white">ServiceKU</h5>
                <p class="mt-2 text-sm">Aplikasi Manajemen Servis</p>
                <p class="mt-6 text-xs">© 2026 ServiceKU. All rights reserved.</p>
            </div>
        </footer>
    </div>
</template>

<script setup>
import KButton from '@/Components/KButton.vue';
import KSelect from '@/Components/KSelect.vue';

import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    stats: { type: Object, default: () => ({ tenants: 0, features: 8, users: 0 }) },
    plans: { type: Array, default: () => [] },
});

const selectedBusinessType = ref('full_service');

const BUSINESS_TYPES = {
    full_service: 'Servis & Sparepart',
    aksesoris_service: 'Aksesoris & Jasa Servis',
    aksespare_service: 'Pusat Servis & Sparepart',
    gadget_full: 'HP/Laptop Baru + Servis',
    retail_only: 'Retail Saja (Jualan)',
};

const getFeaturesList = (planFeatures) => {
    const features = planFeatures || {};
    const type = selectedBusinessType.value;
    
    // Fallback jika database masih kosong/format fallback array
    if (!features || Object.keys(features).length === 0) {
        return ['Manajemen Servis', 'POS & Stok', 'Laporan Dasar'];
    }

    let typeFeatures = {};
    if (features[type] && typeof features[type] === 'object') {
        typeFeatures = features[type];
    } else {
        typeFeatures = features;
    }
    
    const list = [];
    
    const mapping = {
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
        indents: 'Pre-Order / Inden Sparepart',
    };

    const maxUsers = features.max_users || typeFeatures.max_users || 0;
    const maxBranches = features.max_branches || typeFeatures.max_branches || 0;

    if (maxUsers > 0) {
        list.push(`Maksimal ${maxUsers} Karyawan`);
    } else {
        list.push('Karyawan Tidak Terbatas');
    }

    if (maxBranches > 0) {
        list.push(`Maksimal ${maxBranches} Cabang`);
    } else {
        list.push('Cabang Tidak Terbatas');
    }

    for (const [key, label] of Object.entries(mapping)) {
        if (type === 'retail_only' && ['services', 'checklist'].includes(key)) {
            continue;
        }

        const val = typeFeatures[key];
        if (val === true || val === 'full' || val === 1 || val === '1') {
            list.push(label);
        } else if (val === 'read_only') {
            list.push(`${label} (Hanya Lihat)`);
        }
    }

    return list;
};
</script>
