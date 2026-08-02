<template>
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50">
        <!-- Navigation -->
        <nav class="bg-white/80 backdrop-blur-sm border-b border-gray-100 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-14 md:h-16 items-center">
                    <div class="flex items-center">
                        <h1 class="text-xl md:text-2xl font-bold text-indigo-600">ServiceKU</h1>
                    </div>
                    <!-- Desktop Nav -->
                    <div class="hidden sm:flex items-center gap-4">
                        <a href="#features" class="text-sm text-gray-600 hover:text-indigo-600">Fitur</a>
                        <a href="#pricing" class="text-sm text-gray-600 hover:text-indigo-600">Harga</a>
                        <a href="#contact" class="text-sm text-gray-600 hover:text-indigo-600">Kontak</a>
                        <Link :href="route('register')" class="px-3 md:px-4 py-2 bg-white text-indigo-600 border border-indigo-200 rounded-md text-xs md:text-sm hover:bg-indigo-50 whitespace-nowrap">
                            Daftar
                        </Link>
                        <Link :href="route('login')" class="px-3 md:px-4 py-2 bg-indigo-600 text-white rounded-md text-xs md:text-sm hover:bg-indigo-700 whitespace-nowrap">
                            Login
                        </Link>
                    </div>
                    <!-- Mobile Nav -->
                    <div class="sm:hidden flex items-center gap-2">
                        <Link :href="route('login')" class="px-3 py-1.5 bg-indigo-600 text-white rounded-md text-xs hover:bg-indigo-700">
                            Login
                        </Link>
                        <KButton  @click="showMobileMenu = !showMobileMenu" class="p-2 text-gray-600 hover:text-gray-900">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path v-if="!showMobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </KButton>
                    </div>
                </div>
                <!-- Mobile Menu -->
                <div v-if="showMobileMenu" class="sm:hidden pb-3 border-t border-gray-100 pt-2">
                    <div class="flex flex-col gap-2">
                        <a href="#features" @click="showMobileMenu = false" class="px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md">Fitur</a>
                        <a href="#pricing" @click="showMobileMenu = false" class="px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md">Harga</a>
                        <a href="#contact" @click="showMobileMenu = false" class="px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md">Kontak</a>
                        <Link :href="route('register')" @click="showMobileMenu = false" class="px-3 py-2 text-sm text-indigo-600 hover:bg-indigo-50 rounded-md font-medium">
                            Daftar
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero -->
        <header class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12 md:pt-20 pb-12 md:pb-16">
            <div class="text-center">
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold text-gray-900 mb-4 md:mb-6 leading-tight">
                    Kelola Servis HP & Laptop<br>
                    <span class="text-indigo-600">Jadi Lebih Mudah</span>
                </h2>
                <p class="text-base sm:text-lg md:text-xl text-gray-600 mb-6 md:mb-8 max-w-2xl mx-auto px-2">
                    Satu aplikasi untuk mencatat servis, mengelola stok, kasir, dan laporan toko.
                </p>
                <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center items-center px-4">
                    <Link :href="route('register')" class="w-full sm:w-auto px-6 md:px-8 py-3 bg-indigo-600 text-white rounded-lg text-sm md:text-lg font-semibold hover:bg-indigo-700 shadow-lg shadow-indigo-200 text-center">
                        Mulai Trial Gratis
                    </Link>
                    <a href="#features" class="w-full sm:w-auto px-6 md:px-8 py-3 border border-gray-300 text-gray-700 rounded-lg text-sm md:text-lg font-semibold hover:bg-gray-50 text-center">
                        Lihat Fitur
                    </a>
                </div>
            </div>
        </header>

        <!-- Stats -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12 md:pb-16">
            <div class="grid grid-cols-3 gap-4 md:gap-8 max-w-2xl mx-auto">
                <div class="text-center">
                    <p class="text-2xl md:text-3xl font-bold text-indigo-600">{{ stats.tenants }}+</p>
                    <p class="text-xs md:text-sm text-gray-500">Toko Aktif</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl md:text-3xl font-bold text-indigo-600">{{ stats.features }}</p>
                    <p class="text-xs md:text-sm text-gray-500">Fitur Unggulan</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl md:text-3xl font-bold text-indigo-600">{{ stats.users }}+</p>
                    <p class="text-xs md:text-sm text-gray-500">User Aktif</p>
                </div>
            </div>
        </div>

        <!-- Features -->
        <div id="features" class="bg-white py-12 md:py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h3 class="text-2xl md:text-3xl font-bold text-center text-gray-900 mb-8 md:mb-12">Fitur Lengkap</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 md:gap-8">
                    <div v-for="feat in features" :key="feat.title" class="p-6 rounded-xl border border-gray-100 hover:shadow-lg transition">
                        <div class="text-3xl mb-4">{{ feat.icon }}</div>
                        <h4 class="text-lg font-semibold text-gray-900 mb-2">{{ feat.title }}</h4>
                        <p class="text-sm text-gray-600">{{ feat.desc }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div id="pricing" class="py-12 md:py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h3 class="text-2xl md:text-3xl font-bold text-center text-gray-900 mb-4">Pilih Paket</h3>
                <p class="text-sm text-gray-500 text-center mb-8 max-w-md mx-auto">Fitur disesuaikan secara transparan berdasarkan tipe bisnis Anda.</p>
                
                <!-- Business Type Selector -->
                <div class="max-w-xs mx-auto mb-12">
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider text-center mb-2">Tipe Bisnis Toko Anda</label>
                    <KSelect  v-model="selectedBusinessType" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm bg-white py-2.5 px-3">
                        <option v-for="(label, key) in BUSINESS_TYPES" :key="key" :value="key">
                            💼 {{ label }}
                        </option>
                    </KSelect>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 md:gap-8 max-w-5xl mx-auto">
                    <div v-for="pkg in plans" :key="pkg.name" class="p-6 rounded-xl border flex flex-col justify-between" :class="pkg.featured ? 'border-indigo-500 shadow-lg shadow-indigo-100 bg-white' : 'border-gray-200 bg-white'">
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900">{{ pkg.name }}</h4>

                            <!-- Pricing with promo -->
                            <div class="mt-2 flex items-baseline gap-2">
                                <template v-if="pkg.is_promo_active">
                                    <p class="text-lg text-gray-400 line-through">Rp {{ formatNumber(pkg.price) }}</p>
                                    <p class="text-3xl font-bold text-red-600">Rp {{ formatNumber(pkg.promo_price) }}</p>
                                    <span class="px-1.5 py-0.5 text-[10px] font-bold bg-red-100 text-red-700 rounded">DISKON {{ pkg.discount_percent }}%</span>
                                </template>
                                <template v-else>
                                    <p class="text-3xl font-bold text-indigo-600">Rp {{ formatNumber(pkg.price) }}</p>
                                </template>
                            </div>
                            <p class="text-sm text-gray-500">/bulan</p>
                            <p v-if="pkg.is_promo_active" class="text-xs text-red-500 mt-1">🎉 Promo terbatas! {{ pkg.trial_days > 0 ? 'Trial '+pkg.trial_days+' hari + ' : '' }}Daftar sekarang</p>
                            <p v-else-if="pkg.trial_days > 0" class="text-xs text-green-600 mt-1">✓ Trial {{ pkg.trial_days }} hari gratis</p>

                            <ul class="mt-6 space-y-2.5">
                                <li v-for="f in getFeaturesList(pkg.features)" :key="f" class="text-xs text-gray-600 flex items-start gap-2">
                                    <svg class="h-3.5 w-3.5 text-green-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>{{ f }}</span>
                                </li>
                            </ul>
                        </div>
                        <Link :href="route('register', { business_type: selectedBusinessType })" class="mt-6 block text-center px-4 py-2.5 rounded-md text-sm font-semibold" :class="pkg.featured ? 'bg-indigo-600 text-white hover:bg-indigo-700' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'">
                            {{ pkg.is_promo_active ? 'Ambil Promo' : 'Mulai Trial' }}
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer id="contact" class="bg-gray-900 text-gray-400 py-8 md:py-12">
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
