<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold" style="color: var(--text-primary);">📊 Pusat Laporan & Analytics</h2>
                    <p class="text-xs mt-0.5" style="color: var(--text-muted);">Ringkasan kinerja operasional & keuangan toko Anda</p>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- SUMMARY STAT CARDS -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="p-4 rounded-xl border" style="background: var(--bg-card); borderColor: var(--border-color);">
                    <p class="text-xs font-semibold uppercase tracking-wider" style="color: var(--text-muted);">Revenue Bulan Ini</p>
                    <p class="text-2xl font-bold mt-1" style="color: #10b981;">Rp {{ formatNumber(summary?.total_revenue || 0) }}</p>
                    <p class="text-[11px] mt-1" style="color: var(--text-secondary);">Total omset penjualan & servis</p>
                </div>
                <div class="p-4 rounded-xl border" style="background: var(--bg-card); borderColor: var(--border-color);">
                    <p class="text-xs font-semibold uppercase tracking-wider" style="color: var(--text-muted);">Total Servis Bulan Ini</p>
                    <p class="text-2xl font-bold mt-1" style="color: var(--accent-primary);">{{ summary?.total_services || 0 }} Unit</p>
                    <p class="text-[11px] mt-1" style="color: var(--text-secondary);">Unit servis masuk & diproses</p>
                </div>
                <div class="p-4 rounded-xl border" style="background: var(--bg-card); borderColor: var(--border-color);">
                    <p class="text-xs font-semibold uppercase tracking-wider" style="color: var(--text-muted);">Pengeluaran Bulan Ini</p>
                    <p class="text-2xl font-bold mt-1" style="color: #ef4444;">Rp {{ formatNumber(summary?.total_expenses || 0) }}</p>
                    <p class="text-[11px] mt-1" style="color: var(--text-secondary);">Biaya operasional & belanja</p>
                </div>
            </div>

            <!-- MODULE LINKS -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <Link :href="route('reports.sales')" class="p-5 rounded-xl border transition-all hover:shadow-md group" style="background: var(--bg-card); borderColor: var(--border-color);">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg mb-3" style="background: rgba(16,185,129,0.1); color: #10b981;">💰</div>
                    <p class="text-sm font-bold group-hover:text-emerald-600 transition-colors" style="color: var(--text-primary);">Penjualan</p>
                    <p class="text-xs mt-1" style="color: var(--text-muted);">Laporan omset & rincian nota penjualan</p>
                </Link>
                <Link :href="route('reports.services')" class="p-5 rounded-xl border transition-all hover:shadow-md group" style="background: var(--bg-card); borderColor: var(--border-color);">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg mb-3" style="background: var(--accent-light); color: var(--accent-primary);">🔧</div>
                    <p class="text-sm font-bold group-hover:text-purple-600 transition-colors" style="color: var(--text-primary);">Servis</p>
                    <p class="text-xs mt-1" style="color: var(--text-muted);">Laporan servis per status & teknisi</p>
                </Link>
                <Link :href="route('reports.inventory')" class="p-5 rounded-xl border transition-all hover:shadow-md group" style="background: var(--bg-card); borderColor: var(--border-color);">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg mb-3" style="background: rgba(59,130,246,0.1); color: #3b82f6;">📦</div>
                    <p class="text-sm font-bold group-hover:text-blue-600 transition-colors" style="color: var(--text-primary);">Inventory</p>
                    <p class="text-xs mt-1" style="color: var(--text-muted);">Stok produk, nilai & stok kritis</p>
                </Link>
                <Link :href="route('reports.finance')" class="p-5 rounded-xl border transition-all hover:shadow-md group" style="background: var(--bg-card); borderColor: var(--border-color);">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg mb-3" style="background: rgba(245,158,11,0.1); color: #f59e0b;">📊</div>
                    <p class="text-sm font-bold group-hover:text-amber-600 transition-colors" style="color: var(--text-primary);">Keuangan</p>
                    <p class="text-xs mt-1" style="color: var(--text-muted);">Laba rugi, revenue vs pengeluaran</p>
                </Link>
                <Link :href="route('reports.commissions')" class="p-5 rounded-xl border transition-all hover:shadow-md group" style="background: var(--bg-card); borderColor: var(--border-color);">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg mb-3" style="background: rgba(168,85,247,0.1); color: #a855f7;">🏆</div>
                    <p class="text-sm font-bold group-hover:text-purple-600 transition-colors" style="color: var(--text-primary);">Komisi Teknisi</p>
                    <p class="text-xs mt-1" style="color: var(--text-muted);">Rincian & status pembayaran komisi</p>
                </Link>
                <Link :href="route('reports.productivity')" class="p-5 rounded-xl border transition-all hover:shadow-md group" style="background: var(--bg-card); borderColor: var(--border-color);">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg mb-3" style="background: rgba(236,72,153,0.1); color: #ec4899;">📈</div>
                    <p class="text-sm font-bold group-hover:text-pink-600 transition-colors" style="color: var(--text-primary);">Produktivitas</p>
                    <p class="text-xs mt-1" style="color: var(--text-muted);">Kecepatan & beban kerja teknisi</p>
                </Link>
                <Link :href="route('reports.customer-analytics')" class="p-5 rounded-xl border transition-all hover:shadow-md group" style="background: var(--bg-card); borderColor: var(--border-color);">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg mb-3" style="background: rgba(14,165,233,0.1); color: #0ea5e9;">👥</div>
                    <p class="text-sm font-bold group-hover:text-sky-600 transition-colors" style="color: var(--text-primary);">Analisis Pelanggan</p>
                    <p class="text-xs mt-1" style="color: var(--text-muted);">Retention, repeat order & histori</p>
                </Link>
                <Link :href="route('reports.revenue-comparison')" class="p-5 rounded-xl border transition-all hover:shadow-md group" style="background: var(--bg-card); borderColor: var(--border-color);">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg mb-3" style="background: rgba(99,102,241,0.1); color: #6366f1;">📅</div>
                    <p class="text-sm font-bold group-hover:text-indigo-600 transition-colors" style="color: var(--text-primary);">Perbandingan Revenue</p>
                    <p class="text-xs mt-1" style="color: var(--text-muted);">Perbandingan bulan ke bulan</p>
                </Link>
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
