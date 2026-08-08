<template>
    <AuthenticatedLayout>
        <div class="flex flex-col gap-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight sk-text-primary">Unit Belum Diambil</h2>
                    <p class="text-sm sk-text-muted mt-1">Perangkat servis yang selesai tapi belum diambil pelanggan.</p>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('monitoring.index')" class="inline-flex items-center gap-2 px-4 py-2 sk-bg-card hover:sk-bg-hover border sk-border sk-text-primary text-sm font-bold rounded-xl transition-colors shadow-sm">
                        ← Monitoring
                    </Link>
                </div>
            </div>

            <!-- Stat Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="sk-bg-card rounded-2xl border sk-border shadow-sm p-5 text-center">
                    <p class="text-3xl font-black sk-text-primary">{{ stats?.total ?? 0 }}</p>
                    <p class="text-xs font-semibold sk-text-muted mt-1">Total Belum Diambil</p>
                </div>
                <div class="sk-bg-card rounded-2xl border sk-border-primary shadow-sm p-5 text-center">
                    <p class="text-3xl font-black sk-text-warning">{{ stats?.warning_7 ?? 0 }}</p>
                    <p class="text-xs font-semibold sk-text-muted mt-1">≥ 7 Hari</p>
                </div>
                <div class="sk-bg-card rounded-2xl border border-orange-200 shadow-sm p-5 text-center">
                    <p class="text-3xl font-black text-orange-600">{{ stats?.attention_30 ?? 0 }}</p>
                    <p class="text-xs font-semibold sk-text-muted mt-1">≥ 30 Hari</p>
                </div>
                <div class="sk-bg-card rounded-2xl border sk-border-primary shadow-sm p-5 text-center">
                    <p class="text-3xl font-black sk-text-danger">{{ stats?.abandoned_90 ?? 0 }}</p>
                    <p class="text-xs font-semibold sk-text-muted mt-1">≥ 90 Hari (Terlantar)</p>
                </div>
            </div>

            <!-- Unclaimed Table -->
            <div class="sk-bg-card rounded-2xl border sk-border shadow-sm">
                <div class="px-6 py-4 border-b sk-border-light">
                    <h3 class="font-bold sk-text-primary">Daftar Unit</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b sk-border-light sk-bg-hover">
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider sk-text-muted">Servis</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider sk-text-muted">Pelanggan</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider sk-text-muted">Unit</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider sk-text-muted">Siap Sejak</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider sk-text-muted">Level</th>
                                <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider sk-text-muted">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y sk-border-light">
                            <tr v-for="u in unclaimed ?? []" :key="u.id" class="hover:sk-bg-hover">
                                <td class="px-6 py-3 text-sm font-bold sk-text-primary-brand">#{{ u.service_id }}</td>
                                <td class="px-6 py-3 text-sm font-semibold sk-text-primary">{{ u.customer || '—' }}</td>
                                <td class="px-6 py-3 text-sm sk-text-secondary">{{ u.device || '—' }}</td>
                                <td class="px-6 py-3 text-sm sk-text-secondary">{{ formatDate(u.ready_at) }}</td>
                                <td class="px-6 py-3">
                                    <span class="px-2 py-1 rounded-lg text-xs font-bold" :class="levelStyle(u.level)">
                                        {{ levelLabel(u.level) }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <Link :href="route('services.show', u.service_id)" class="text-xs font-bold px-3 py-1.5 rounded-lg sk-text-primary-brand sk-bg-primary-soft hover:sk-bg-primary-soft">Detail</Link>
                                </td>
                            </tr>
                            <tr v-if="!unclaimed?.length">
                                <td colspan="6" class="px-6 py-12 text-center text-sm sk-text-muted">Semua unit sudah diambil. 🎉</td>
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
    warning: 'sk-bg-warning-soft sk-text-warning',
    attention: 'bg-orange-50 text-orange-700',
    abandoned: 'sk-bg-danger-soft sk-text-danger',
}[l] || 'sk-bg-hover sk-text-secondary');
</script>
