<template>
    <AuthenticatedLayout>
        <div class="flex flex-col gap-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight sk-text-primary">Riwayat Mutasi</h2>
                    <p class="text-sm sk-text-muted mt-1">{{ product?.name }} <span class="sk-text-muted">· SKU {{ product?.sku || '—' }}</span></p>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('inventaris.index')" class="inline-flex items-center gap-2 px-4 py-2 sk-bg-card hover:sk-bg-hover border sk-border sk-text-primary text-sm font-bold rounded-xl transition-colors shadow-sm">
                        ← Ke Inventaris
                    </Link>
                </div>
            </div>

            <!-- Product Summary -->
            <div class="sk-bg-card rounded-2xl border sk-border shadow-sm p-6">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider sk-text-muted">Stok Saat Ini</p>
                        <p class="text-2xl font-bold sk-text-primary">{{ product?.stock_quantity ?? 0 }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider sk-text-muted">Min Stok</p>
                        <p class="text-2xl font-bold sk-text-primary">{{ product?.min_stock ?? 0 }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider sk-text-muted">Harga Beli</p>
                        <p class="text-2xl font-bold sk-text-primary">Rp {{ formatNumber(product?.cost_price ?? 0) }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider sk-text-muted">Harga Jual</p>
                        <p class="text-2xl font-bold sk-text-primary">Rp {{ formatNumber(product?.price ?? 0) }}</p>
                    </div>
                </div>
            </div>

            <!-- Movements Table -->
            <div class="sk-bg-card rounded-2xl border sk-border shadow-sm">
                <div class="px-6 py-4 border-b sk-border-light flex items-center justify-between">
                    <h3 class="font-bold sk-text-primary">Riwayat Mutasi Stok</h3>
                    <span class="text-xs font-semibold sk-text-muted">Total: {{ movements?.total ?? 0 }}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b sk-border-light sk-bg-hover">
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider sk-text-muted">Tipe</th>
                                <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider sk-text-muted">Qty</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider sk-text-muted">Catatan</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider sk-text-muted">Oleh</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider sk-text-muted">Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y sk-border-light">
                            <tr v-for="m in movements?.data ?? []" :key="m.id" class="hover:sk-bg-hover">
                                <td class="px-6 py-3">
                                    <span class="px-2 py-1 rounded-lg text-xs font-bold" :class="typeStyle(m.type)">
                                        {{ typeLabel(m.type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-sm font-bold text-right" :class="m.quantity >= 0 ? 'sk-text-success' : 'sk-text-danger'">
                                    {{ m.quantity >= 0 ? '+' : '' }}{{ m.quantity }}
                                </td>
                                <td class="px-6 py-3 text-sm sk-text-secondary">{{ m.notes || '—' }}</td>
                                <td class="px-6 py-3 text-sm sk-text-secondary">{{ m.creator?.name || '—' }}</td>
                                <td class="px-6 py-3 text-sm sk-text-muted">{{ formatDate(m.created_at) }}</td>
                            </tr>
                            <tr v-if="!movements?.data?.length">
                                <td colspan="5" class="px-6 py-12 text-center text-sm sk-text-muted">Belum ada mutasi untuk produk ini.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="movements?.last_page > 1" class="px-6 py-4 border-t sk-border-light flex items-center justify-between">
                    <Link :href="movements.prev_page_url" class="px-4 py-2 text-sm font-bold sk-bg-card border sk-border rounded-xl hover:sk-bg-hover disabled:opacity-50" :class="!movements.prev_page_url && 'pointer-events-none opacity-40'">← Sebelumnya</Link>
                    <span class="text-sm font-semibold sk-text-secondary">Hal {{ movements.current_page }} / {{ movements.last_page }}</span>
                    <Link :href="movements.next_page_url" class="px-4 py-2 text-sm font-bold sk-bg-card border sk-border rounded-xl hover:sk-bg-hover disabled:opacity-50" :class="!movements.next_page_url && 'pointer-events-none opacity-40'">Berikutnya →</Link>
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
    purchase: 'sk-bg-success-soft sk-text-success',
    sale: 'sk-bg-info-soft sk-text-info',
    service: 'bg-purple-50 text-purple-700',
    adjustment: 'sk-bg-warning-soft sk-text-warning',
    transfer: 'sk-bg-primary-soft sk-text-primary-brand',
    return: 'sk-bg-danger-soft sk-text-danger',
}[t] || 'sk-bg-hover sk-text-secondary');
</script>
