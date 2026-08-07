<template>
    <AuthenticatedLayout>
        <div class="flex flex-col gap-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-zinc-900">Operasional Gudang</h2>
                    <p class="text-sm text-zinc-500 mt-1">Permintaan part, stok reservasi, dan reorder.</p>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('inventaris.index')" class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-zinc-50 border border-zinc-200 text-zinc-700 text-sm font-bold rounded-xl transition-colors shadow-sm">
                        ← Ke Inventaris
                    </Link>
                </div>
            </div>

            <!-- Stat Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
                <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-4 text-center">
                    <p class="text-2xl font-bold text-indigo-600">{{ stats?.waiting_requests ?? 0 }}</p>
                    <p class="text-xs font-semibold text-zinc-500 mt-1">Permintaan Part</p>
                </div>
                <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-4 text-center">
                    <p class="text-2xl font-bold text-blue-600">{{ stats?.reserved_stock ?? 0 }}</p>
                    <p class="text-xs font-semibold text-zinc-500 mt-1">Stok Reservasi</p>
                </div>
                <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-4 text-center">
                    <p class="text-2xl font-bold text-amber-600">{{ stats?.low_stock ?? 0 }}</p>
                    <p class="text-xs font-semibold text-zinc-500 mt-1">Stok Menipis</p>
                </div>
                <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-4 text-center">
                    <p class="text-2xl font-bold text-purple-600">{{ stats?.waiting_purchase ?? 0 }}</p>
                    <p class="text-xs font-semibold text-zinc-500 mt-1">Menunggu Beli</p>
                </div>
                <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-4 text-center">
                    <p class="text-2xl font-bold text-red-600">{{ stats?.pending_return ?? 0 }}</p>
                    <p class="text-xs font-semibold text-zinc-500 mt-1">Retur Part</p>
                </div>
                <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-4 text-center">
                    <p class="text-2xl font-bold text-emerald-600">{{ formatNumber(stats?.today_incoming ?? 0) }}</p>
                    <p class="text-xs font-semibold text-zinc-500 mt-1">Masuk Hari Ini</p>
                </div>
            </div>

            <!-- Waiting Requests -->
            <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm">
                <div class="px-6 py-4 border-b border-zinc-100 flex items-center justify-between">
                    <h3 class="font-bold text-zinc-900">Permintaan Part Menunggu</h3>
                    <span class="text-xs font-semibold text-zinc-500">{{ waitingRequests?.length ?? 0 }} item</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-zinc-100 bg-zinc-50">
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500">Part</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500">Servis</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500">Pelanggan</th>
                                <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-zinc-500">Qty</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500">Prioritas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            <tr v-for="r in waitingRequests ?? []" :key="r.id" class="hover:bg-zinc-50">
                                <td class="px-6 py-3 text-sm font-semibold text-zinc-900">{{ r.part_name || '—' }}</td>
                                <td class="px-6 py-3 text-sm text-zinc-600">#{{ r.service_id }}</td>
                                <td class="px-6 py-3 text-sm text-zinc-600">{{ r.service?.customer?.name || '—' }}</td>
                                <td class="px-6 py-3 text-sm font-bold text-right text-zinc-900">{{ r.qty }}</td>
                                <td class="px-6 py-3">
                                    <span class="px-2 py-1 rounded-lg text-xs font-bold" :class="priorityStyle(r.priority)">
                                        {{ r.priority || 'normal' }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="!waitingRequests?.length">
                                <td colspan="5" class="px-6 py-12 text-center text-sm text-zinc-400">Tidak ada permintaan part menunggu.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Top Parts -->
            <div v-if="stats?.top_parts?.length" class="bg-white rounded-2xl border border-zinc-200 shadow-sm">
                <div class="px-6 py-4 border-b border-zinc-100">
                    <h3 class="font-bold text-zinc-900">Part Paling Banyak Dipakai</h3>
                </div>
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div v-for="(p, i) in stats.top_parts" :key="i" class="flex items-center justify-between p-3 bg-zinc-50 rounded-xl">
                        <span class="text-sm font-semibold text-zinc-700">{{ p.part_name }}</span>
                        <span class="text-sm font-bold text-indigo-600">{{ p.cnt }}×</span>
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
    urgent: 'bg-red-50 text-red-700',
    high: 'bg-amber-50 text-amber-700',
    normal: 'bg-zinc-100 text-zinc-600',
    low: 'bg-emerald-50 text-emerald-700',
}[p] || 'bg-zinc-100 text-zinc-600');
</script>
