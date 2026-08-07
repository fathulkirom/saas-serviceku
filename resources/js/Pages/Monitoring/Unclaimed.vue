<template>
    <AuthenticatedLayout>
        <div class="flex flex-col gap-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-zinc-900">Unit Belum Diambil</h2>
                    <p class="text-sm text-zinc-500 mt-1">Perangkat servis yang selesai tapi belum diambil pelanggan.</p>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('monitoring.index')" class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-zinc-50 border border-zinc-200 text-zinc-700 text-sm font-bold rounded-xl transition-colors shadow-sm">
                        ← Monitoring
                    </Link>
                </div>
            </div>

            <!-- Stat Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-5 text-center">
                    <p class="text-3xl font-black text-zinc-900">{{ stats?.total ?? 0 }}</p>
                    <p class="text-xs font-semibold text-zinc-500 mt-1">Total Belum Diambil</p>
                </div>
                <div class="bg-white rounded-2xl border border-amber-200 shadow-sm p-5 text-center">
                    <p class="text-3xl font-black text-amber-600">{{ stats?.warning_7 ?? 0 }}</p>
                    <p class="text-xs font-semibold text-zinc-500 mt-1">≥ 7 Hari</p>
                </div>
                <div class="bg-white rounded-2xl border border-orange-200 shadow-sm p-5 text-center">
                    <p class="text-3xl font-black text-orange-600">{{ stats?.attention_30 ?? 0 }}</p>
                    <p class="text-xs font-semibold text-zinc-500 mt-1">≥ 30 Hari</p>
                </div>
                <div class="bg-white rounded-2xl border border-red-200 shadow-sm p-5 text-center">
                    <p class="text-3xl font-black text-red-600">{{ stats?.abandoned_90 ?? 0 }}</p>
                    <p class="text-xs font-semibold text-zinc-500 mt-1">≥ 90 Hari (Terlantar)</p>
                </div>
            </div>

            <!-- Unclaimed Table -->
            <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm">
                <div class="px-6 py-4 border-b border-zinc-100">
                    <h3 class="font-bold text-zinc-900">Daftar Unit</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-zinc-100 bg-zinc-50">
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500">Servis</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500">Pelanggan</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500">Unit</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500">Siap Sejak</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500">Level</th>
                                <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-zinc-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            <tr v-for="u in unclaimed ?? []" :key="u.id" class="hover:bg-zinc-50">
                                <td class="px-6 py-3 text-sm font-bold text-indigo-600">#{{ u.service_id }}</td>
                                <td class="px-6 py-3 text-sm font-semibold text-zinc-900">{{ u.customer || '—' }}</td>
                                <td class="px-6 py-3 text-sm text-zinc-600">{{ u.device || '—' }}</td>
                                <td class="px-6 py-3 text-sm text-zinc-600">{{ formatDate(u.ready_at) }}</td>
                                <td class="px-6 py-3">
                                    <span class="px-2 py-1 rounded-lg text-xs font-bold" :class="levelStyle(u.level)">
                                        {{ levelLabel(u.level) }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <Link :href="route('services.show', u.service_id)" class="text-xs font-bold px-3 py-1.5 rounded-lg text-indigo-600 bg-indigo-50 hover:bg-indigo-100">Detail</Link>
                                </td>
                            </tr>
                            <tr v-if="!unclaimed?.length">
                                <td colspan="6" class="px-6 py-12 text-center text-sm text-zinc-400">Semua unit sudah diambil. 🎉</td>
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
    unclaimed: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({}) },
});

const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID', { dateStyle: 'medium' }) : '—';

const levelLabel = (l) => ({
    warning: 'Warning',
    attention: 'Perhatian',
    abandoned: 'Terlantar',
}[l] || l || '—');

const levelStyle = (l) => ({
    warning: 'bg-amber-50 text-amber-700',
    attention: 'bg-orange-50 text-orange-700',
    abandoned: 'bg-red-50 text-red-700',
}[l] || 'bg-zinc-100 text-zinc-600');
</script>
