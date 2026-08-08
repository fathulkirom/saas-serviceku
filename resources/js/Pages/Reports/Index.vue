<template>
    <AuthenticatedLayout>
    <div class="flex flex-col min-h-[calc(100vh-64px)] sk-bg-hover">
      <!-- Enterprise Header -->
      <div class="relative sk-bg-card border-b sk-border overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-50/50 via-indigo-50/50 to-purple-50/50 opacity-50"></div>
        <div class="relative px-6 sm:px-8 py-8 md:py-10 flex flex-col md:flex-row md:items-end justify-between gap-6 max-w-[1400px] mx-auto">
          <div class="flex items-center gap-5">
            <div class="w-16 h-16 sk-bg-card rounded-2xl flex items-center justify-center border sk-border shadow-sm shadow-indigo-100/50">
              <svg class="w-8 h-8 sk-text-primary-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <div>
              <h1 class="text-3xl font-black sk-text-primary tracking-tight">Pusat Laporan & Analytics</h1>
              <p class="text-sm font-medium sk-text-muted mt-1">Ringkasan kinerja operasional & keuangan toko Anda secara real-time</p>
            </div>
          </div>
        </div>
      </div>

      <div class="flex-1 p-6 sm:p-8 max-w-[1400px] mx-auto w-full space-y-8">
        
        <!-- SUMMARY STAT CARDS -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="sk-bg-card p-6 rounded-3xl border sk-border shadow-sm relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-32 h-32 sk-bg-success-soft rounded-full blur-3xl -mr-10 -mt-10 transition-all group-hover:scale-110"></div>
                <div class="relative z-10 flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider sk-text-muted mb-1">Revenue Bulan Ini</p>
                        <p class="text-3xl font-black sk-text-success mb-1">Rp {{ formatNumber(summary?.total_revenue || 0) }}</p>
                        <p class="text-sm font-medium sk-text-muted">Total omset penjualan & servis</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl sk-bg-success-soft flex items-center justify-center sk-text-success">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
            </div>

            <div class="sk-bg-card p-6 rounded-3xl border sk-border shadow-sm relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-32 h-32 sk-bg-primary-soft rounded-full blur-3xl -mr-10 -mt-10 transition-all group-hover:scale-110"></div>
                <div class="relative z-10 flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider sk-text-muted mb-1">Total Servis Bulan Ini</p>
                        <p class="text-3xl font-black sk-text-primary-brand mb-1">{{ summary?.total_services || 0 }} <span class="text-xl font-bold">Unit</span></p>
                        <p class="text-sm font-medium sk-text-muted">Unit servis masuk & diproses</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl sk-bg-primary-soft flex items-center justify-center sk-text-primary-brand">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                </div>
            </div>

            <div class="sk-bg-card p-6 rounded-3xl border sk-border shadow-sm relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-32 h-32 sk-bg-danger-soft rounded-full blur-3xl -mr-10 -mt-10 transition-all group-hover:scale-110"></div>
                <div class="relative z-10 flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider sk-text-muted mb-1">Pengeluaran Bulan Ini</p>
                        <p class="text-3xl font-black sk-text-danger mb-1">Rp {{ formatNumber(summary?.total_expenses || 0) }}</p>
                        <p class="text-sm font-medium sk-text-muted">Biaya operasional & belanja</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl sk-bg-danger-soft flex items-center justify-center sk-text-danger">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-2">
            <h2 class="text-lg font-bold sk-text-primary mb-4">Modul Laporan Tersedia</h2>
            
            <!-- MODULE LINKS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Penjualan -->
                <Link :href="route('reports.sales')" class="sk-bg-card p-6 rounded-3xl border sk-border shadow-sm hover:shadow-lg transition-all group flex flex-col items-start gap-4">
                    <div class="w-14 h-14 rounded-2xl sk-bg-success-soft sk-text-success flex items-center justify-center group-hover:scale-110 group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-inner">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold sk-text-primary group-hover:sk-text-success transition-colors">Penjualan</h3>
                        <p class="text-sm font-medium sk-text-muted mt-1 leading-snug">Laporan omset & rincian nota penjualan produk.</p>
                    </div>
                </Link>

                <!-- Servis -->
                <Link :href="route('reports.services')" class="sk-bg-card p-6 rounded-3xl border sk-border shadow-sm hover:shadow-lg transition-all group flex flex-col items-start gap-4">
                    <div class="w-14 h-14 rounded-2xl sk-bg-primary-soft sk-text-primary-brand flex items-center justify-center group-hover:scale-110 group-hover:sk-bg-primary group-hover:text-white transition-all shadow-inner">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold sk-text-primary group-hover:sk-text-primary-brand transition-colors">Servis & Reparasi</h3>
                        <p class="text-sm font-medium sk-text-muted mt-1 leading-snug">Laporan status servis, estimasi, dan garansi.</p>
                    </div>
                </Link>

                <!-- Inventory -->
                <Link :href="route('reports.inventory')" class="sk-bg-card p-6 rounded-3xl border sk-border shadow-sm hover:shadow-lg transition-all group flex flex-col items-start gap-4">
                    <div class="w-14 h-14 rounded-2xl sk-bg-info-soft sk-text-info flex items-center justify-center group-hover:scale-110 group-hover:bg-blue-600 group-hover:text-white transition-all shadow-inner">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold sk-text-primary group-hover:sk-text-info transition-colors">Inventory (Stok)</h3>
                        <p class="text-sm font-medium sk-text-muted mt-1 leading-snug">Valuasi stok produk, barang kritis & mutasi.</p>
                    </div>
                </Link>

                <!-- Keuangan -->
                <Link :href="route('reports.finance')" class="sk-bg-card p-6 rounded-3xl border sk-border shadow-sm hover:shadow-lg transition-all group flex flex-col items-start gap-4">
                    <div class="w-14 h-14 rounded-2xl sk-bg-warning-soft sk-text-warning flex items-center justify-center group-hover:scale-110 group-hover:bg-amber-500 group-hover:text-white transition-all shadow-inner">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold sk-text-primary group-hover:sk-text-warning transition-colors">Keuangan & Kas</h3>
                        <p class="text-sm font-medium sk-text-muted mt-1 leading-snug">Arus kas, laba rugi, dan rekonsiliasi bank.</p>
                    </div>
                </Link>

                <!-- Komisi Teknisi -->
                <Link :href="route('reports.commissions')" class="sk-bg-card p-6 rounded-3xl border sk-border shadow-sm hover:shadow-lg transition-all group flex flex-col items-start gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center group-hover:scale-110 group-hover:bg-purple-600 group-hover:text-white transition-all shadow-inner">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold sk-text-primary group-hover:text-purple-600 transition-colors">Komisi Teknisi</h3>
                        <p class="text-sm font-medium sk-text-muted mt-1 leading-snug">Rincian komisi per teknisi dan status bayar.</p>
                    </div>
                </Link>

                <!-- Produktivitas -->
                <Link :href="route('reports.productivity')" class="sk-bg-card p-6 rounded-3xl border sk-border shadow-sm hover:shadow-lg transition-all group flex flex-col items-start gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-pink-50 text-pink-600 flex items-center justify-center group-hover:scale-110 group-hover:bg-pink-600 group-hover:text-white transition-all shadow-inner">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold sk-text-primary group-hover:text-pink-600 transition-colors">Kinerja & Waktu</h3>
                        <p class="text-sm font-medium sk-text-muted mt-1 leading-snug">Kecepatan teknisi dan beban kerja.</p>
                    </div>
                </Link>

                <!-- Analisis Pelanggan -->
                <Link :href="route('reports.customer-analytics')" class="sk-bg-card p-6 rounded-3xl border sk-border shadow-sm hover:shadow-lg transition-all group flex flex-col items-start gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center group-hover:scale-110 group-hover:bg-sky-500 group-hover:text-white transition-all shadow-inner">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold sk-text-primary group-hover:text-sky-600 transition-colors">Analisis Pelanggan</h3>
                        <p class="text-sm font-medium sk-text-muted mt-1 leading-snug">Tingkat kedatangan kembali (retention) pelanggan.</p>
                    </div>
                </Link>

                <!-- Perbandingan Revenue -->
                <Link :href="route('reports.revenue-comparison')" class="sk-bg-card p-6 rounded-3xl border sk-border shadow-sm hover:shadow-lg transition-all group flex flex-col items-start gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center group-hover:scale-110 group-hover:bg-teal-500 group-hover:text-white transition-all shadow-inner">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold sk-text-primary group-hover:text-teal-600 transition-colors">Tren Pendapatan</h3>
                        <p class="text-sm font-medium sk-text-muted mt-1 leading-snug">Perbandingan revenue antar periode bulan/tahun.</p>
                    </div>
                </Link>
            </div>
        </div>
      </div>
    </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    chartData: { type: Array, default: () => [] },
    summary: { type: Object, default: () => ({}) },
});

function formatNumber(val) {
    if (!val) return '0';
    return new Intl.NumberFormat('id-ID').format(val);
}
</script>
