<template>
    <AuthenticatedLayout>
        <PageHeader title="Laporan Penjualan">
            <select v-model="period" @change="changePeriod" class="w-auto rounded-lg border text-sm px-3 py-2.5 focus:ring-2 focus:outline-none border-dark-200 text-dark-700 focus:border-blue-500 focus:ring-blue-200 transition-all">
                <option value="today">Hari Ini</option>
                <option value="yesterday">Kemarin</option>
                <option value="week">Minggu Ini</option>
                <option value="month">Bulan Ini</option>
                <option value="year">Tahun Ini</option>
            </select>
        </PageHeader>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl border shadow-sm p-4 flex items-center gap-4" style="border-color: var(--border-color);">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(var(--accent-primary-rgb, 99,102,241), 0.12); color: var(--accent-primary);">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div>
                    <p class="text-xs text-dark-400 uppercase tracking-wider">Total Transaksi</p>
                    <p class="text-xl font-bold text-dark-900">{{ summary.total_sales }}</p>
                </div>
            </div>
            <div class="bg-white rounded-xl border shadow-sm p-4 flex items-center gap-4" style="border-color: var(--border-color);">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(34,197,94,0.12); color: #16a34a;">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-dark-400 uppercase tracking-wider">Total Pendapatan</p>
                    <p class="text-xl font-bold text-success-600">Rp {{ formatNumber(summary.total_revenue) }}</p>
                </div>
            </div>
            <div class="bg-white rounded-xl border shadow-sm p-4 flex items-center gap-4" style="border-color: var(--border-color);">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(var(--accent-primary-rgb, 99,102,241), 0.12); color: var(--accent-primary);">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
                <div>
                    <p class="text-xs text-dark-400 uppercase tracking-wider">Rata-rata</p>
                    <p class="text-xl font-bold text-dark-900">Rp {{ formatNumber(summary.average_sale) }}</p>
                </div>
            </div>
            <div class="bg-white rounded-xl border shadow-sm p-4 flex items-center gap-4" style="border-color: var(--border-color);">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(239,68,68,0.12); color: #dc2626;">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                </div>
                <div>
                    <p class="text-xs text-dark-400 uppercase tracking-wider">Diskon</p>
                    <p class="text-xl font-bold text-red-600">Rp {{ formatNumber(summary.total_discount) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border shadow-sm p-6 mb-6" style="border-color: var(--border-color);">
            <h3 class="text-sm font-bold text-dark-900 mb-4">Per Tipe Penjualan</h3>
            <div class="grid grid-cols-3 gap-4" v-if="summary.by_type">
                <div v-for="(data, type) in summary.by_type" :key="type" class="text-center p-3 bg-dark-50 rounded-xl">
                    <p class="text-sm text-dark-400">{{ tipeLabel(type) }}</p>
                    <p class="text-lg font-bold text-dark-900">{{ data.count }}x</p>
                    <p class="text-sm text-success-600 font-medium">Rp {{ formatNumber(data.total) }}</p>
                </div>
            </div>
        </div>

        <KTable :columns="columns" :rows="rows">
            <template #cell-id="{row}">
                <span style="color: var(--accent-primary);">#{{ row.id }}</span>
            </template>
            <template #cell-total="{row}">
                <span class="font-medium">Rp {{ formatNumber(row.total) }}</span>
            </template>
            <template #empty>
                <EmptyState icon="receipt" title="Tidak ada data" description="Tidak ada penjualan pada periode ini" />
            </template>
        </KTable>
    </AuthenticatedLayout>
</template>

<script setup>
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
