<template>
    <AuthenticatedLayout>
        <div class="flex flex-col gap-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-zinc-900">Dashboard Inventaris</h2>
                    <p class="text-sm text-zinc-500 mt-1">Ringkasan stok, nilai inventaris, dan mutasi terbaru.</p>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('inventaris.index')" class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-zinc-50 border border-zinc-200 text-zinc-700 text-sm font-bold rounded-xl transition-colors shadow-sm">
                        ← Ke Inventaris
                    </Link>
                </div>
            </div>

            <!-- Stat Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-5">
                    <div class="flex items-start gap-4">
                        <div class="w-11 h-11 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Total Item</p>
                            <p class="text-2xl font-bold text-zinc-900">{{ stats?.total_items ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-5">
                    <div class="flex items-start gap-4">
                        <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Nilai Stok</p>
                            <p class="text-2xl font-bold text-zinc-900">Rp {{ formatNumber(stats?.stock_value ?? 0) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-5">
                    <div class="flex items-start gap-4">
                        <div class="w-11 h-11 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Stok Menipis</p>
                            <p class="text-2xl font-bold text-amber-600">{{ stats?.low_stock ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-5">
                    <div class="flex items-start gap-4">
                        <div class="w-11 h-11 rounded-xl bg-red-100 text-red-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Stok Habis</p>
                            <p class="text-2xl font-bold text-red-600">{{ stats?.out_of_stock ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Movements -->
            <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm">
                <div class="px-6 py-4 border-b border-zinc-100">
                    <h3 class="font-bold text-zinc-900">Mutasi Terbaru</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-zinc-100 bg-zinc-50">
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500">Produk</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500">Tipe</th>
                                <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-zinc-500">Qty</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500">Oleh</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500">Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            <tr v-for="m in stats?.recent_movements ?? []" :key="m.id" class="hover:bg-zinc-50">
                                <td class="px-6 py-3 text-sm font-semibold text-zinc-900">{{ m.product?.name || '—' }}</td>
                                <td class="px-6 py-3">
                                    <span class="px-2 py-1 rounded-lg text-xs font-bold" :class="typeStyle(m.type)">
                                        {{ typeLabel(m.type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-sm font-bold text-right" :class="m.quantity >= 0 ? 'text-emerald-600' : 'text-red-600'">
                                    {{ m.quantity >= 0 ? '+' : '' }}{{ m.quantity }}
                                </td>
                                <td class="px-6 py-3 text-sm text-zinc-600">{{ m.creator?.name || '—' }}</td>
                                <td class="px-6 py-3 text-sm text-zinc-500">{{ formatDate(m.created_at) }}</td>
                            </tr>
                            <tr v-if="!stats?.recent_movements?.length">
                                <td colspan="5" class="px-6 py-12 text-center text-sm text-zinc-400">Belum ada mutasi.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    stats: { type: Object, default: () => ({}) },
});

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num || 0);
const formatDate = (d) => d ? new Date(d).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) : '—';

const typeLabel = (t) => ({
    purchase: 'Pembelian',
    sale: 'Penjualan',
    service: 'Pakai Servis',
    adjustment: 'Penyesuaian',
    transfer: 'Transfer',
    return: 'Retur',
}[t] || t || '—');

const typeStyle = (t) => ({
    purchase: 'bg-emerald-50 text-emerald-700',
    sale: 'bg-blue-50 text-blue-700',
    service: 'bg-purple-50 text-purple-700',
    adjustment: 'bg-amber-50 text-amber-700',
    transfer: 'bg-indigo-50 text-indigo-700',
    return: 'bg-red-50 text-red-700',
}[t] || 'bg-zinc-100 text-zinc-600');
</script>
