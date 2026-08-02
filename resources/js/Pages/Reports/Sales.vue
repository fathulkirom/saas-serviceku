<template>
    <AuthenticatedLayout>
    <div class="flex flex-col min-h-[calc(100vh-64px)] bg-zinc-50/50">
        <div class="px-6 sm:px-8 py-6 bg-white border-b border-zinc-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4 sticky top-0 z-20">
            <div class="flex items-center gap-4">
                <Link :href="route('reports.index')" class="w-10 h-10 bg-zinc-100 rounded-xl flex items-center justify-center text-zinc-600 hover:bg-zinc-200 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-zinc-900 tracking-tight">Laporan Penjualan</h1>
                    <p class="text-sm text-zinc-500 font-medium mt-0.5">Analisis omset dan volume transaksi</p>
                </div>
            </div>
            <div>
                <KSelect  v-model="period" @change="changePeriod" class="w-full sm:w-48 rounded-xl border border-zinc-300 px-4 py-2 text-sm font-semibold bg-white text-zinc-900 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all cursor-pointer shadow-sm">
                    <option value="today">Hari Ini</option>
                    <option value="yesterday">Kemarin</option>
                    <option value="week">Minggu Ini</option>
                    <option value="month">Bulan Ini</option>
                    <option value="year">Tahun Ini</option>
                </KSelect>
            </div>
        </div>

        <div class="flex-1 p-6 sm:p-8 max-w-[1400px] mx-auto w-full space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-indigo-50 text-indigo-600 shadow-inner">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Total Transaksi</p>
                        <p class="text-2xl font-black text-zinc-900">{{ summary.total_sales }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-emerald-50 text-emerald-600 shadow-inner">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Total Pendapatan</p>
                        <p class="text-2xl font-black text-emerald-600">Rp {{ formatNumber(summary.total_revenue) }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-blue-50 text-blue-600 shadow-inner">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Rata-rata Nota</p>
                        <p class="text-2xl font-black text-zinc-900">Rp {{ formatNumber(summary.average_sale) }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-red-50 text-red-600 shadow-inner">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Total Diskon</p>
                        <p class="text-2xl font-black text-red-600">Rp {{ formatNumber(summary.total_discount) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-6">
                <h3 class="text-lg font-bold text-zinc-900 mb-4">Perincian Tipe Penjualan</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4" v-if="summary.by_type">
                    <div v-for="(data, type) in summary.by_type" :key="type" class="text-center p-4 bg-zinc-50 rounded-xl border border-zinc-100">
                        <p class="text-sm font-bold text-zinc-600 uppercase tracking-wider mb-2">{{ tipeLabel(type) }}</p>
                        <p class="text-2xl font-black text-zinc-900 mb-1">{{ data.count }}x Transaksi</p>
                        <p class="text-sm text-emerald-600 font-bold">Rp {{ formatNumber(data.total) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-zinc-200 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-zinc-900">Riwayat Transaksi</h3>
                </div>
                <KTable :columns="columns" :rows="rows">
                    <template #cell-id="{row}">
                        <span class="font-mono text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded-md">#{{ row.id }}</span>
                    </template>
                    <template #cell-customer_name="{row}">
                        <span class="font-bold text-zinc-900">{{ row.customer_name }}</span>
                    </template>
                    <template #cell-sale_type_label="{row}">
                        <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-zinc-100 text-zinc-700 border border-zinc-200">{{ row.sale_type_label }}</span>
                    </template>
                    <template #cell-total="{row}">
                        <span class="font-bold text-zinc-900">Rp {{ formatNumber(row.total) }}</span>
                    </template>
                    <template #cell-created_at="{row}">
                        <span class="text-zinc-500">{{ row.created_at }}</span>
                    </template>
                    <template #empty>
                        <div class="py-12 text-center">
                            <div class="w-16 h-16 bg-zinc-100 text-zinc-400 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <p class="text-sm font-bold text-zinc-900">Tidak ada data</p>
                            <p class="text-xs text-zinc-500 mt-1">Tidak ada penjualan pada periode ini</p>
                        </div>
                    </template>
                </KTable>
            </div>
        </div>
    </div>
    </AuthenticatedLayout>
</template>

<script setup>
import KSelect from '@/Components/KSelect.vue';

import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import KTable from '@/Components/KTable.vue';
import EmptyState from '@/Components/EmptyState.vue';

const props = defineProps({
    sales: { type: Array, default: () => [] },
    summary: { type: Object, default: () => ({}) },
    period: { type: String, default: 'today' },
});

const period = ref(props.period);
const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num || 0);
const tipeLabel = (t) => ({ servis: 'Servis', langsung: 'Langsung', inden: 'Inden' }[t] || t);

const columns = [
    { key: 'id', label: 'Nota' },
    { key: 'customer_name', label: 'Pelanggan' },
    { key: 'sale_type_label', label: 'Tipe' },
    { key: 'total', label: 'Total', align: 'right' },
    { key: 'created_at', label: 'Tanggal' },
];

const rows = computed(() => props.sales.map(s => ({
    id: s.id,
    customer_name: s.customer?.name || 'Umum',
    sale_type_label: tipeLabel(s.sale_type),
    total: s.total,
    created_at: s.created_at,
})));

const changePeriod = () => router.get(route('reports.sales'), { period: period.value }, { preserveState: true });
</script>
