<template>
    <AuthenticatedLayout>
    <div class="flex flex-col min-h-[calc(100vh-64px)] sk-bg-hover">
        <div class="px-6 sm:px-8 py-6 sk-bg-card border-b sk-border flex flex-col sm:flex-row sm:items-center justify-between gap-4 sticky top-0 z-20">
            <div class="flex items-center gap-4">
                <Link :href="route('reports.index')" class="w-10 h-10 sk-bg-hover rounded-xl flex items-center justify-center sk-text-secondary hover:bg-zinc-200 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold sk-text-primary tracking-tight">Laporan Inventori</h1>
                    <p class="text-sm sk-text-muted font-medium mt-0.5">Analisis ketersediaan dan nilai stok produk</p>
                </div>
            </div>
        </div>

        <div class="flex-1 p-6 sm:p-8 max-w-[1400px] mx-auto w-full space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="sk-bg-card rounded-2xl border sk-border shadow-sm p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center sk-bg-primary-soft sk-text-primary-brand shadow-inner">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold sk-text-muted uppercase tracking-wider">Total Produk</p>
                        <p class="text-2xl font-black sk-text-primary">{{ summary.total_products }}</p>
                    </div>
                </div>
                <div class="sk-bg-card rounded-2xl border sk-border shadow-sm p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center sk-bg-warning-soft sk-text-warning shadow-inner">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold sk-text-muted uppercase tracking-wider">Stok Menipis</p>
                        <p class="text-2xl font-black sk-text-warning">{{ summary.low_stock }}</p>
                    </div>
                </div>
                <div class="sk-bg-card rounded-2xl border sk-border shadow-sm p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center sk-bg-danger-soft sk-text-danger shadow-inner">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold sk-text-muted uppercase tracking-wider">Stok Habis</p>
                        <p class="text-2xl font-black sk-text-danger">{{ summary.out_of_stock }}</p>
                    </div>
                </div>
                <div class="sk-bg-card rounded-2xl border sk-border shadow-sm p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center sk-bg-success-soft sk-text-success shadow-inner">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold sk-text-muted uppercase tracking-wider">Nilai Stok</p>
                        <p class="text-2xl font-black sk-text-success">Rp {{ formatNumber(summary.total_value) }}</p>
                    </div>
                </div>
            </div>

            <div class="sk-bg-card rounded-2xl border sk-border shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b sk-border flex items-center justify-between">
                    <h3 class="text-lg font-bold sk-text-primary">Daftar Stok Produk</h3>
                </div>
                <KTable :columns="columns" :rows="products">
                    <template #cell-code="{row}">
                        <span class="font-mono text-xs font-bold sk-text-primary-brand sk-bg-primary-soft px-2 py-1 rounded-md">{{ row.code }}</span>
                    </template>
                    <template #cell-name="{row}">
                        <span class="font-bold sk-text-primary">{{ row.name }}</span>
                    </template>
                    <template #cell-stock_quantity="{row}">
                        <span class="font-bold" :class="row.stock_quantity <= row.min_stock ? (row.stock_quantity <= 0 ? 'sk-text-danger' : 'sk-text-warning') : 'sk-text-primary'">
                            {{ row.stock_quantity }}
                        </span>
                    </template>
                    <template #cell-min_stock="{row}">
                        <span class="sk-text-muted">{{ row.min_stock }}</span>
                    </template>
                    <template #cell-selling_price="{row}">
                        <span class="font-medium sk-text-primary">Rp {{ formatNumber(row.selling_price) }}</span>
                    </template>
                    <template #cell-status="{row}">
                        <Badge v-if="row.stock_quantity <= 0" variant="red">Habis</Badge>
                        <Badge v-else-if="row.stock_quantity <= row.min_stock" variant="yellow">Menipis</Badge>
                        <Badge v-else variant="green">Aman</Badge>
                    </template>
                    <template #empty>
                        <div class="py-12 text-center">
                            <div class="w-16 h-16 sk-bg-hover sk-text-muted rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                            <p class="text-sm font-bold sk-text-primary">Tidak ada produk</p>
                            <p class="text-xs sk-text-muted mt-1">Belum ada produk dalam inventori</p>
                        </div>
                    </template>
                </KTable>
            </div>
        </div>
    </div>
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
