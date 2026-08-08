<template>
    <AuthenticatedLayout>
        <div class="flex flex-col gap-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight sk-text-primary">Operasional Gudang</h2>
                    <p class="text-sm sk-text-muted mt-1">Permintaan part, stok reservasi, dan reorder.</p>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('inventaris.index')" class="inline-flex items-center gap-2 px-4 py-2 sk-bg-card hover:sk-bg-hover border sk-border sk-text-primary text-sm font-bold rounded-xl transition-colors shadow-sm">
                        ← Ke Inventaris
                    </Link>
                </div>
            </div>

            <!-- Stat Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
                <div class="sk-bg-card rounded-2xl border sk-border shadow-sm p-4 text-center">
                    <p class="text-2xl font-bold sk-text-primary-brand">{{ stats?.waiting_requests ?? 0 }}</p>
                    <p class="text-xs font-semibold sk-text-muted mt-1">Permintaan Part</p>
                </div>
                <div class="sk-bg-card rounded-2xl border sk-border shadow-sm p-4 text-center">
                    <p class="text-2xl font-bold sk-text-info">{{ stats?.reserved_stock ?? 0 }}</p>
                    <p class="text-xs font-semibold sk-text-muted mt-1">Stok Reservasi</p>
                </div>
                <div class="sk-bg-card rounded-2xl border sk-border shadow-sm p-4 text-center">
                    <p class="text-2xl font-bold sk-text-warning">{{ stats?.low_stock ?? 0 }}</p>
                    <p class="text-xs font-semibold sk-text-muted mt-1">Stok Menipis</p>
                </div>
                <div class="sk-bg-card rounded-2xl border sk-border shadow-sm p-4 text-center">
                    <p class="text-2xl font-bold text-purple-600">{{ stats?.waiting_purchase ?? 0 }}</p>
                    <p class="text-xs font-semibold sk-text-muted mt-1">Menunggu Beli</p>
                </div>
                <div class="sk-bg-card rounded-2xl border sk-border shadow-sm p-4 text-center">
                    <p class="text-2xl font-bold sk-text-danger">{{ stats?.pending_return ?? 0 }}</p>
                    <p class="text-xs font-semibold sk-text-muted mt-1">Retur Part</p>
                </div>
                <div class="sk-bg-card rounded-2xl border sk-border shadow-sm p-4 text-center">
                    <p class="text-2xl font-bold sk-text-success">{{ formatNumber(stats?.today_incoming ?? 0) }}</p>
                    <p class="text-xs font-semibold sk-text-muted mt-1">Masuk Hari Ini</p>
                </div>
            </div>

            <!-- Waiting Requests -->
            <div class="sk-bg-card rounded-2xl border sk-border shadow-sm">
                <div class="px-6 py-4 border-b sk-border-light flex items-center justify-between">
                    <h3 class="font-bold sk-text-primary">Permintaan Part Menunggu</h3>
                    <span class="text-xs font-semibold sk-text-muted">{{ waitingRequests?.length ?? 0 }} item</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b sk-border-light sk-bg-hover">
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider sk-text-muted">Part</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider sk-text-muted">Servis</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider sk-text-muted">Pelanggan</th>
                                <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider sk-text-muted">Qty</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider sk-text-muted">Prioritas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y sk-border-light">
                            <tr v-for="r in waitingRequests ?? []" :key="r.id" class="hover:sk-bg-hover">
                                <td class="px-6 py-3 text-sm font-semibold sk-text-primary">{{ r.part_name || '—' }}</td>
                                <td class="px-6 py-3 text-sm sk-text-secondary">#{{ r.service_id }}</td>
                                <td class="px-6 py-3 text-sm sk-text-secondary">{{ r.service?.customer?.name || '—' }}</td>
                                <td class="px-6 py-3 text-sm font-bold text-right sk-text-primary">{{ r.qty }}</td>
                                <td class="px-6 py-3">
                                    <span class="px-2 py-1 rounded-lg text-xs font-bold" :class="priorityStyle(r.priority)">
                                        {{ r.priority || 'normal' }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="!waitingRequests?.length">
                                <td colspan="5" class="px-6 py-12 text-center text-sm sk-text-muted">Tidak ada permintaan part menunggu.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Top Parts -->
            <div v-if="stats?.top_parts?.length" class="sk-bg-card rounded-2xl border sk-border shadow-sm">
                <div class="px-6 py-4 border-b sk-border-light">
                    <h3 class="font-bold sk-text-primary">Part Paling Banyak Dipakai</h3>
                </div>
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div v-for="(p, i) in stats.top_parts" :key="i" class="flex items-center justify-between p-3 sk-bg-hover rounded-xl">
                        <span class="text-sm font-semibold sk-text-primary">{{ p.part_name }}</span>
                        <span class="text-sm font-bold sk-text-primary-brand">{{ p.cnt }}×</span>
                    </div>
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
    waitingRequests: { type: Array, default: () => [] },
});

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num || 0);

const priorityStyle = (p) => ({
    urgent: 'sk-bg-danger-soft sk-text-danger',
    high: 'sk-bg-warning-soft sk-text-warning',
    normal: 'sk-bg-hover sk-text-secondary',
    low: 'sk-bg-success-soft sk-text-success',
}[p] || 'sk-bg-hover sk-text-secondary');
</script>
