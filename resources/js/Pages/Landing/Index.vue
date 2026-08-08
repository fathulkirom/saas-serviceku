<template>
    <div class="min-h-screen" :style="{ background: 'linear-gradient(180deg, #EEF2FF 0%, #FFFFFF 40%, #F8FAFC 100%)' }">
        
        <!-- ═══════════ NAVIGATION ═══════════ -->
        <nav class="sticky top-0 z-50 border-b backdrop-blur-xl" :style="{ background: 'rgba(255,255,255,0.85)', borderColor: 'var(--border-light)' }">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-sm font-extrabold text-white" style="background: var(--color-primary)">SK</div>
                        <span class="text-xl font-extrabold tracking-tight" :style="{ color: 'var(--text-primary)' }">Service<span style="color: var(--color-primary)">KU</span></span>
                    </div>
                    <div class="hidden md:flex items-center gap-8">
                        <a href="#features" class="text-sm font-semibold transition-colors hover:text-indigo-600" :style="{ color: 'var(--text-secondary)' }">Fitur</a>
                        <a href="#how-it-works" class="text-sm font-semibold transition-colors hover:text-indigo-600" :style="{ color: 'var(--text-secondary)' }">Cara Kerja</a>
                        <a href="#pricing" class="text-sm font-semibold transition-colors hover:text-indigo-600" :style="{ color: 'var(--text-secondary)' }">Harga</a>
                    </div>
                    <div class="flex items-center gap-3">
                        <Link :href="route('login')" class="hidden sm:inline-flex px-4 py-2 text-sm font-bold rounded-xl transition-all" :style="{ color: 'var(--text-primary)', background: 'var(--bg-hover)' }">
                            Masuk
                        </Link>
                        <Link :href="route('register')" class="inline-flex px-5 py-2.5 text-sm font-bold text-white rounded-xl shadow-md hover:shadow-lg transition-all" style="background: var(--color-primary)">
                            Coba Gratis
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- ═══════════ HERO ═══════════ -->
        <header class="relative overflow-hidden">
            <!-- Decorative blobs -->
            <div class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/4 w-[600px] h-[600px] rounded-full opacity-20 blur-3xl" style="background: var(--color-primary)"></div>
            <div class="absolute bottom-0 left-0 translate-y-1/2 -translate-x-1/4 w-[400px] h-[400px] rounded-full opacity-10 blur-3xl" style="background: var(--color-warning)"></div>
            
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 md:pt-28 pb-16 md:pb-24 relative z-10">
                <div class="max-w-3xl mx-auto text-center">
                    <!-- Badge -->
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border text-xs font-bold mb-6 animate-slide-up" :style="{ background: 'var(--color-success-soft)', borderColor: 'var(--color-success-soft)', color: 'var(--color-success-text)' }">
                        <span class="w-1.5 h-1.5 rounded-full animate-pulse" style="background: var(--color-success)"></span>
                        Trial 14 Hari · Tanpa Kartu Kredit
                    </div>
                    
                    <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold leading-[1.1] tracking-tight mb-6" :style="{ color: 'var(--text-primary)' }">
                        Operasional Bengkel<br>
                        <span class="relative inline-block">
                            <span style="color: var(--color-primary)">Jadi Lebih Rapi</span>
                            <svg class="absolute -bottom-1 left-0 w-full" viewBox="0 0 200 8" fill="none"><path d="M0 4 Q50 0 100 4 Q150 8 200 4" stroke="#818CF8" stroke-width="3" stroke-linecap="round" fill="none"/></svg>
                        </span>
                    </h1>
                    
                    <p class="text-lg sm:text-xl leading-relaxed mb-10 max-w-xl mx-auto" :style="{ color: 'var(--text-secondary)' }">
                        ServiceKU menyatukan meja CS, meja teknisi, rak stok, kasir, dan laporan owner dalam <strong :style="{ color: 'var(--text-primary)' }">satu layar</strong>. Tanpa kertas, tanpa ribet.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <Link :href="route('register')" class="group inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-bold text-white rounded-2xl shadow-lg hover:shadow-xl transition-all hover:-translate-y-0.5" style="background: var(--color-primary)">
                            Mulai Trial Gratis 14 Hari
                            <svg class="w-5 h-5 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </Link>
                        <a href="#how-it-works" class="inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-bold rounded-2xl border transition-all hover:-translate-y-0.5" :style="{ borderColor: 'var(--border-color)', color: 'var(--text-primary)', background: 'var(--bg-card)' }">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Lihat Demo
                        </a>
                    </div>
                    
                    <!-- Trust badges -->
                    <div class="flex flex-wrap items-center justify-center gap-6 mt-10 pt-8 border-t" :style="{ borderColor: 'var(--border-light)' }">
                        <div class="flex items-center gap-2 text-xs font-semibold" :style="{ color: 'var(--text-muted)' }">
                            <svg class="w-4 h-4" :style="{ color: 'var(--color-success-text)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Data Aman & Terenkripsi
                        </div>
                        <div class="flex items-center gap-2 text-xs font-semibold" :style="{ color: 'var(--text-muted)' }">
                            <svg class="w-4 h-4" :style="{ color: 'var(--color-success-text)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Setup 5 Menit
                        </div>
                        <div class="flex items-center gap-2 text-xs font-semibold" :style="{ color: 'var(--text-muted)' }">
                            <svg class="w-4 h-4" :style="{ color: 'var(--color-success-text)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            Bisa Multi Cabang
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- ═══════════ HOW IT WORKS ═══════════ -->
        <div id="how-it-works" class="py-20 md:py-28" :style="{ background: 'var(--bg-card)' }">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-14">
                    <span class="text-xs font-extrabold uppercase tracking-[0.2em] mb-3 block" :style="{ color: 'var(--color-primary-text)' }">Cara Kerja</span>
                    <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight mb-4" :style="{ color: 'var(--text-primary)' }">Dari Meja CS ke Tangan Pelanggan</h2>
                    <p class="text-base max-w-lg mx-auto" :style="{ color: 'var(--text-secondary)' }">Satu alur yang rapi dari servis masuk sampai serah terima.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 relative">
                    <!-- Connector line (desktop) -->
                    <div class="hidden md:block absolute top-8 left-[15%] right-[15%] h-0.5" style="background: var(--border-light)"></div>
                    <div v-for="(step, i) in workflowSteps" :key="i" class="relative text-center p-6 rounded-2xl transition-all hover:-translate-y-1" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-light)' }">
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl mx-auto mb-4 shadow-sm relative z-10" :style="{ background: 'var(--color-primary-soft)' }">
                            {{ step.icon }}
                        </div>
                        <h4 class="font-bold text-sm mb-1" :style="{ color: 'var(--text-primary)' }">{{ step.title }}</h4>
                        <p class="text-xs leading-relaxed" :style="{ color: 'var(--text-muted)' }">{{ step.desc }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══════════ FEATURES ═══════════ -->
        <div id="features" class="py-20 md:py-28">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-14">
                    <span class="text-xs font-extrabold uppercase tracking-[0.2em] mb-3 block" :style="{ color: 'var(--color-primary-text)' }">Fitur</span>
                    <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight mb-4" :style="{ color: 'var(--text-primary)' }">Semua yang Dibutuhkan Bengkel Modern</h2>
                    <p class="text-base max-w-lg mx-auto" :style="{ color: 'var(--text-secondary)' }">Dari penerimaan servis, pengerjaan teknisi, manajemen stok, sampai laporan keuangan.</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div v-for="feat in featureList" :key="feat.title" class="group p-6 rounded-2xl border transition-all duration-300 hover:-translate-y-1 hover:shadow-lg" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-light)' }">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl mb-4 transition-transform group-hover:scale-110" :style="{ background: 'var(--color-primary-soft)' }">
                            {{ feat.icon }}
                        </div>
                        <h4 class="font-bold mb-2" :style="{ color: 'var(--text-primary)' }">{{ feat.title }}</h4>
                        <p class="text-sm leading-relaxed" :style="{ color: 'var(--text-secondary)' }">{{ feat.desc }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══════════ PRICING ═══════════ -->
        <div id="pricing" class="py-20 md:py-28" :style="{ background: 'var(--bg-card)' }">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-10">
                    <span class="text-xs font-extrabold uppercase tracking-[0.2em] mb-3 block" :style="{ color: 'var(--color-primary-text)' }">Harga</span>
                    <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight mb-4" :style="{ color: 'var(--text-primary)' }">Pilih Paket Sesuai Skala Toko</h2>
                    <p class="text-base max-w-md mx-auto mb-6" :style="{ color: 'var(--text-secondary)' }">Fitur disesuaikan secara transparan berdasarkan tipe bisnis Anda.</p>
                    
                    <!-- Business Type Selector -->
                    <div class="max-w-xs mx-auto">
                        <KSelect v-model="selectedBusinessType" class="block w-full rounded-xl shadow-sm text-sm py-3 px-4 font-semibold text-center" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)', color: 'var(--text-primary)' }">
                            <option v-for="(label, key) in BUSINESS_TYPES" :key="key" :value="key">💼 {{ label }}</option>
                        </KSelect>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto">
                    <div v-for="pkg in plans" :key="pkg.name" class="relative p-8 rounded-2xl border-2 flex flex-col transition-all hover:-translate-y-1" :class="pkg.featured ? 'shadow-xl' : ''" :style="pkg.featured ? { background: 'var(--bg-card)', borderColor: 'var(--color-primary)', boxShadow: '0 0 40px rgba(79,70,229,0.1)' } : { background: 'var(--bg-card)', borderColor: 'var(--border-light)' }">
                        <!-- Popular badge -->
                        <div v-if="pkg.featured" class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-4 py-1 rounded-full text-xs font-extrabold text-white shadow-md" style="background: var(--color-primary)">
                            PALING POPULER
                        </div>
                        
                        <div class="mb-6">
                            <h4 class="text-lg font-extrabold mb-3" :style="{ color: 'var(--text-primary)' }">{{ pkg.name }}</h4>
                            
                            <div class="flex items-baseline gap-2 flex-wrap">
                                <template v-if="pkg.is_promo_active">
                                    <span class="text-xl line-through" :style="{ color: 'var(--text-muted)' }">Rp{{ formatNumber(pkg.price) }}</span>
                                    <span class="text-4xl font-extrabold" :style="{ color: 'var(--color-danger-text)' }">Rp{{ formatNumber(pkg.promo_price) }}</span>
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-extrabold" :style="{ background: 'var(--color-danger-soft)', color: 'var(--color-danger-text)' }">-{{ pkg.discount_percent }}%</span>
                                </template>
                                <template v-else>
                                    <span class="text-4xl font-extrabold" :style="{ color: 'var(--color-primary-text)' }">Rp{{ formatNumber(pkg.price) }}</span>
                                </template>
                            </div>
                            <span class="text-sm font-medium" :style="{ color: 'var(--text-muted)' }">/bulan</span>
                            
                            <p v-if="pkg.is_promo_active" class="mt-2 text-xs font-bold" :style="{ color: 'var(--color-danger-text)' }">🎉 Promo terbatas! Daftar sekarang</p>
                            <p v-else-if="pkg.trial_days > 0" class="mt-2 text-xs font-bold" :style="{ color: 'var(--color-success-text)' }">✓ Trial {{ pkg.trial_days }} hari gratis</p>
                        </div>

                        <ul class="space-y-3 mb-8 flex-1">
                            <li v-for="f in getFeaturesList(pkg.features).slice(0, 8)" :key="f" class="flex items-start gap-2.5 text-sm" :style="{ color: 'var(--text-secondary)' }">
                                <svg class="w-4 h-4 shrink-0 mt-0.5" :style="{ color: 'var(--color-success-text)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                {{ f }}
                            </li>
                        </ul>

                        <Link :href="route('register', { business_type: selectedBusinessType })" class="block text-center py-3 rounded-xl text-sm font-extrabold transition-all hover:-translate-y-0.5" :class="pkg.featured ? 'text-white shadow-lg' : ''" :style="pkg.featured ? { background: 'var(--color-primary)' } : { color: 'var(--text-primary)', background: 'var(--bg-hover)' }">
                            {{ pkg.is_promo_active ? 'Ambil Promo' : 'Mulai Trial' }}
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══════════ CTA ═══════════ -->
        <div class="py-20 md:py-28 text-center relative overflow-hidden" :style="{ background: 'var(--color-primary)' }">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-10 left-10 w-40 h-40 rounded-full bg-white blur-2xl"></div>
                <div class="absolute bottom-10 right-10 w-60 h-60 rounded-full bg-white blur-2xl"></div>
            </div>
            <div class="relative z-10 max-w-2xl mx-auto px-4">
                <h2 class="text-3xl md:text-4xl font-extrabold text-white mb-4 tracking-tight">Siap Rapikan Operasional Bengkel?</h2>
                <p class="text-indigo-200 text-lg mb-8">Mulai trial gratis 14 hari. Tidak perlu kartu kredit. Batal kapan saja.</p>
                <Link :href="route('register')" class="inline-flex items-center gap-2 px-10 py-4 bg-white text-indigo-700 rounded-2xl text-base font-extrabold shadow-xl hover:shadow-2xl transition-all hover:-translate-y-0.5">
                    Buat Toko Gratis Sekarang
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </Link>
            </div>
        </div>

        <!-- ═══════════ FOOTER ═══════════ -->
        <footer class="py-16" :style="{ background: '#0F172A', color: '#94A3B8' }">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-12">
                    <div class="col-span-2 md:col-span-1">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-sm font-extrabold text-white" style="background: var(--color-primary)">SK</div>
                            <span class="text-xl font-extrabold text-white">ServiceKU</span>
                        </div>
                        <p class="text-sm leading-relaxed">Platform operasional bengkel servis HP, laptop, dan elektronik.</p>
                    </div>
                    <div>
                        <h5 class="text-white font-bold text-sm mb-4">Fitur Utama</h5>
                        <ul class="space-y-2 text-sm">
                            <li>Manajemen Servis</li>
                            <li>POS & Kasir</li>
                            <li>Stok & Inventaris</li>
                            <li>Laporan Keuangan</li>
                        </ul>
                    </div>
                    <div>
                        <h5 class="text-white font-bold text-sm mb-4">Perusahaan</h5>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#pricing" class="hover:text-white transition-colors">Harga</a></li>
                            <li><a href="#features" class="hover:text-white transition-colors">Fitur</a></li>
                            <li><a href="#" class="hover:text-white transition-colors">Kontak</a></li>
                        </ul>
                    </div>
                    <div>
                        <h5 class="text-white font-bold text-sm mb-4">Legal</h5>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#" class="hover:text-white transition-colors">Privasi</a></li>
                            <li><a href="#" class="hover:text-white transition-colors">Ketentuan</a></li>
                        </ul>
                    </div>
                </div>
                <div class="pt-8 border-t text-center text-xs" :style="{ borderColor: '#1E293B' }">
                    © 2026 ServiceKU. All rights reserved. · v0.9.0
                </div>
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
const showMobileMenu = ref(false);

const workflowSteps = [
    { icon: '📝', title: 'CS Input Servis', desc: 'Catat pelanggan, perangkat, dan keluhan dalam 2 menit.' },
    { icon: '🔧', title: 'Teknisi Kerjakan', desc: 'Diagnosis, ganti part, tracking progress real-time.' },
    { icon: '💳', title: 'Kasir & Invoice', desc: 'Hitung biaya, cetak invoice, terima pembayaran.' },
    { icon: '📦', title: 'Serah Terima', desc: 'QC akhir, notifikasi WA, serah terima ke pelanggan.' },
];

const featureList = ref([
    { icon: '🔧', title: 'Manajemen Servis', desc: 'Tiket, tracking status, alokasi teknisi, riwayat lengkap perangkat.' },
    { icon: '💰', title: 'Kasir & POS', desc: 'Penjualan sparepart, invoice, multi pembayaran, laporan kasir harian.' },
    { icon: '📦', title: 'Inventaris & Stok', desc: 'Stok real-time, multi cabang, transfer antar gudang, PO ke supplier.' },
    { icon: '👥', title: 'CRM Pelanggan', desc: 'Database pelanggan, riwayat servis, membership, loyalty poin.' },
    { icon: '📊', title: 'Laporan & Analitik', desc: 'Laba rugi, performa teknisi, tren servis, revenue per cabang.' },
    { icon: '⚙️', title: 'Otomatisasi', desc: 'Notifikasi WA, trigger status, reminder garansi, follow-up otomatis.' },
]);

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

    if (maxUsers > 0) list.push(`Maksimal ${maxUsers} Karyawan`);
    else list.push('Karyawan Tidak Terbatas');

    if (maxBranches > 0) list.push(`Maksimal ${maxBranches} Cabang`);
    else list.push('Cabang Tidak Terbatas');

    for (const [key, label] of Object.entries(mapping)) {
        if (type === 'retail_only' && ['services', 'checklist'].includes(key)) continue;
        const val = typeFeatures[key];
        if (val === true || val === 'full' || val === 1 || val === '1') list.push(label);
        else if (val === 'read_only') list.push(`${label} (Hanya Lihat)`);
    }
    return list;
};

const formatNumber = (n) => {
    if (!n) return '0';
    return Number(n).toLocaleString('id-ID');
};
</script>
