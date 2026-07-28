<template>
    <AuthenticatedLayout>
        <PageHeader title="Laporan Inventori" />

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl border shadow-sm p-4 flex items-center gap-4" style="border-color: var(--border-color);">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(var(--accent-primary-rgb, 99,102,241), 0.12); color: var(--accent-primary);">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div>
                    <p class="text-xs text-dark-400 uppercase tracking-wider">Total Produk</p>
                    <p class="text-xl font-bold text-dark-900">{{ summary.total_products }}</p>
                </div>
            </div>
            <div class="bg-white rounded-xl border shadow-sm p-4 flex items-center gap-4" style="border-color: var(--border-color);">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(255,193,7,0.12); color: #d97706;">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"/></svg>
                </div>
                <div>
                    <p class="text-xs text-dark-400 uppercase tracking-wider">Stok Menipis</p>
                    <p class="text-xl font-bold text-yellow-600">{{ summary.low_stock }}</p>
                </div>
            </div>
            <div class="bg-white rounded-xl border shadow-sm p-4 flex items-center gap-4" style="border-color: var(--border-color);">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(var(--accent-primary-rgb, 99,102,241), 0.12); color: var(--accent-primary);">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <div>
                    <p class="text-xs text-dark-400 uppercase tracking-wider">Stok Habis</p>
                    <p class="text-xl font-bold" style="color: var(--accent-primary);">{{ summary.out_of_stock }}</p>
                </div>
            </div>
            <div class="bg-white rounded-xl border shadow-sm p-4 flex items-center gap-4" style="border-color: var(--border-color);">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(34,197,94,0.12); color: #16a34a;">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-dark-400 uppercase tracking-wider">Total Nilai Stok</p>
                    <p class="text-xl font-bold text-dark-900">Rp {{ formatNumber(summary.total_value) }}</p>
                </div>
            </div>
        </div>

        <KTable title="Daftar Stok Produk" :columns="columns" :rows="products">
            <template #cell-stock_quantity="{row}">
                <span :class="row.stock_quantity <= row.min_stock ? 'font-bold' : ''" :style="row.stock_quantity <= row.min_stock ? 'color: var(--accent-primary);' : ''">
                    {{ row.stock_quantity }}
                </span>
            </template>
            <template #cell-selling_price="{row}">
                Rp {{ formatNumber(row.selling_price) }}
            </template>
            <template #cell-status="{row}">
                <Badge v-if="row.stock_quantity <= 0" variant="red">Habis</Badge>
                <Badge v-else-if="row.stock_quantity <= row.min_stock" variant="yellow">Menipis</Badge>
                <Badge v-else variant="green">Aman</Badge>
            </template>
            <template #empty>
                <EmptyState icon="package" title="Tidak ada produk" description="Belum ada produk dalam inventori" />
            </template>
        </KTable>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import KTable from '@/Components/KTable.vue';
import Badge from '@/Components/Badge.vue';
import EmptyState from '@/Components/EmptyState.vue';

defineProps({
    products: { type: Array, default: () => [] },
    mutations: { type: Array, default: () => [] },
    summary: { type: Object, default: () => ({}) },
});

const columns = [
    { key: 'code', label: 'Kode' },
    { key: 'name', label: 'Nama' },
    { key: 'stock_quantity', label: 'Stok', align: 'right' },
    { key: 'min_stock', label: 'Min', align: 'right' },
    { key: 'selling_price', label: 'Harga Jual', align: 'right' },
    { key: 'status', label: 'Status' },
];

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num || 0);
</script>
