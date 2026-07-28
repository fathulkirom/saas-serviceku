<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-lg font-bold text-dark-900">Laporan Komisi Teknisi</h2>
                <div class="flex gap-2">
                    <button v-for="p in periods" :key="p.key" @click="changePeriod(p.key)" class="px-3 py-1.5 text-xs font-medium rounded-xl transition-all"
                        :class="period === p.key ? 'text-white' : 'bg-white text-dark-500 border border-dark-200'"
                        :style="period === p.key ? { background: 'var(--accent-primary)' } : {}">
                        {{ p.label }}
                    </button>
                </div>
            </div>
        </template>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl border shadow-sm p-4" style="border-color: var(--border-color);">
                <p class="text-xs text-dark-400 uppercase tracking-wider">Total Servis</p>
                <p class="text-2xl font-bold text-dark-900 mt-1">{{ summary.total_services }}</p>
            </div>
            <div class="bg-white rounded-xl border shadow-sm p-4" style="border-color: var(--border-color);">
                <p class="text-xs text-dark-400 uppercase tracking-wider">Total Biaya Jasa</p>
                <p class="text-2xl font-bold text-dark-900 mt-1">Rp {{ formatNumber(summary.total_service_charge) }}</p>
            </div>
            <div class="bg-white rounded-xl border shadow-sm p-4" style="border-color: var(--border-color);">
                <p class="text-xs text-dark-400 uppercase tracking-wider">Estimasi Komisi</p>
                <p class="text-2xl font-bold text-green-600 mt-1">Rp {{ formatNumber(summary.total_commissions) }}</p>
            </div>
            <div class="bg-white rounded-xl border shadow-sm p-4" style="border-color: var(--border-color);">
                <p class="text-xs text-dark-400 uppercase tracking-wider">Jml Teknisi</p>
                <p class="text-2xl font-bold text-dark-900 mt-1">{{ summary.technician_count }}</p>
            </div>
        </div>

        <!-- Per Teknisi -->
        <div v-for="(item, techId) in commissions" :key="techId" class="bg-white rounded-xl border shadow-sm p-5 mb-4" style="border-color: var(--border-color);">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-base font-semibold text-dark-900">
                    {{ item.technician?.name || 'Tanpa Teknisi' }}
                    <span class="text-xs text-dark-400 font-normal ml-2">({{ item.total_services }} servis)</span>
                </h3>
                <div class="text-right">
                    <p class="text-sm text-dark-400">Estimasi Komisi</p>
                    <p class="text-lg font-bold text-green-600">Rp {{ formatNumber(item.estimated_commission) }}</p>
                </div>
            </div>

            <table class="w-full">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-dark-400 uppercase">ID Servis</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-dark-400 uppercase">Pelanggan</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-dark-400 uppercase">Tgl Selesai</th>
                        <th class="px-4 py-2 text-right text-xs font-semibold text-dark-400 uppercase">Jasa</th>
                        <th class="px-4 py-2 text-right text-xs font-semibold text-dark-400 uppercase">Komisi (50%)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="s in item.services" :key="s.id">
                        <td class="px-4 py-2 text-sm font-medium" style="color: var(--accent-primary);">#{{ s.id }}</td>
                        <td class="px-4 py-2 text-sm text-dark-900">{{ s.customer_name }}</td>
                        <td class="px-4 py-2 text-sm text-dark-400">{{ s.completed_at }}</td>
                        <td class="px-4 py-2 text-sm text-right">Rp {{ formatNumber(s.service_charge) }}</td>
                        <td class="px-4 py-2 text-sm text-right font-medium text-green-600">Rp {{ formatNumber(s.commission) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="Object.keys(commissions).length === 0" class="text-center py-12 text-dark-400">
            Tidak ada data komisi untuk periode ini.
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    commissions: { type: Object, default: () => ({}) },
    summary: { type: Object, default: () => ({}) },
    period: { type: String, default: 'month' },
    dateFrom: { type: String, default: '' },
    dateTo: { type: String, default: '' },
});

const periods = [
    { key: 'today', label: 'Hari Ini' },
    { key: 'week', label: 'Minggu Ini' },
    { key: 'month', label: 'Bulan Ini' },
    { key: 'year', label: 'Tahun Ini' },
];

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num || 0);

const changePeriod = (p) => {
    router.get(route('reports.commissions'), { period: p }, { preserveState: true });
};
</script>
