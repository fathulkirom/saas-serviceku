<template>
    <AuthenticatedLayout>
        <div class="flex flex-col gap-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-zinc-900">Riwayat Mutasi</h2>
                    <p class="text-sm text-zinc-500 mt-1">{{ product?.name }} <span class="text-zinc-400">· SKU {{ product?.sku || '—' }}</span></p>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('inventaris.index')" class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-zinc-50 border border-zinc-200 text-zinc-700 text-sm font-bold rounded-xl transition-colors shadow-sm">
                        ← Ke Inventaris
                    </Link>
                </div>
            </div>

            <!-- Product Summary -->
            <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-6">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Stok Saat Ini</p>
                        <p class="text-2xl font-bold text-zinc-900">{{ product?.stock_quantity ?? 0 }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Min Stok</p>
                        <p class="text-2xl font-bold text-zinc-900">{{ product?.min_stock ?? 0 }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Harga Beli</p>
                        <p class="text-2xl font-bold text-zinc-900">Rp {{ formatNumber(product?.cost_price ?? 0) }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Harga Jual</p>
                        <p class="text-2xl font-bold text-zinc-900">Rp {{ formatNumber(product?.price ?? 0) }}</p>
                    </div>
                </div>
            </div>

            <!-- Movements Table -->
            <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm">
                <div class="px-6 py-4 border-b border-zinc-100 flex items-center justify-between">
                    <h3 class="font-bold text-zinc-900">Riwayat Mutasi Stok</h3>
                    <span class="text-xs font-semibold text-zinc-500">Total: {{ movements?.total ?? 0 }}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-zinc-100 bg-zinc-50">
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500">Tipe</th>
                                <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-zinc-500">Qty</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500">Catatan</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500">Oleh</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500">Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            <tr v-for="m in movements?.data ?? []" :key="m.id" class="hover:bg-zinc-50">
                                <td class="px-6 py-3">
                                    <span class="px-2 py-1 rounded-lg text-xs font-bold" :class="typeStyle(m.type)">
                                        {{ typeLabel(m.type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-sm font-bold text-right" :class="m.quantity >= 0 ? 'text-emerald-600' : 'text-red-600'">
                                    {{ m.quantity >= 0 ? '+' : '' }}{{ m.quantity }}
                                </td>
                                <td class="px-6 py-3 text-sm text-zinc-600">{{ m.notes || '—' }}</td>
                                <td class="px-6 py-3 text-sm text-zinc-600">{{ m.creator?.name || '—' }}</td>
                                <td class="px-6 py-3 text-sm text-zinc-500">{{ formatDate(m.created_at) }}</td>
                            </tr>
                            <tr v-if="!movements?.data?.length">
                                <td colspan="5" class="px-6 py-12 text-center text-sm text-zinc-400">Belum ada mutasi untuk produk ini.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="movements?.last_page > 1" class="px-6 py-4 border-t border-zinc-100 flex items-center justify-between">
                    <Link :href="movements.prev_page_url" class="px-4 py-2 text-sm font-bold bg-white border border-zinc-200 rounded-xl hover:bg-zinc-50 disabled:opacity-50" :class="!movements.prev_page_url && 'pointer-events-none opacity-40'">← Sebelumnya</Link>
                    <span class="text-sm font-semibold text-zinc-600">Hal {{ movements.current_page }} / {{ movements.last_page }}</span>
                    <Link :href="movements.next_page_url" class="px-4 py-2 text-sm font-bold bg-white border border-zinc-200 rounded-xl hover:bg-zinc-50 disabled:opacity-50" :class="!movements.next_page_url && 'pointer-events-none opacity-40'">Berikutnya →</Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    product: { type: Object, default: () => ({}) },
    movements: { type: Object, default: () => ({ data: [], total: 0 }) },
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
