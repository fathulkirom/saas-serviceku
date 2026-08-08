<template>
    <AuthenticatedLayout>
    <div class="flex flex-col min-h-[calc(100vh-64px)] sk-bg-hover">
        <div class="px-6 sm:px-8 py-6 sk-bg-card border-b sk-border flex flex-col sm:flex-row sm:items-center justify-between gap-4 sticky top-0 z-20">
            <div class="flex items-center gap-4">
                <Link :href="route('reports.index')" class="w-10 h-10 sk-bg-hover rounded-xl flex items-center justify-center sk-text-secondary hover:bg-zinc-200 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold sk-text-primary tracking-tight">Laporan Komisi Teknisi</h1>
                    <p class="text-sm sk-text-muted font-medium mt-0.5">Analisis insentif dan komisi jasa servis teknisi</p>
                </div>
            </div>
            <div class="flex sk-bg-hover p-1 rounded-xl">
                <KButton  v-for="p in periods" :key="p.key" @click="changePeriod(p.key)" class="px-4 py-2 text-sm font-bold rounded-lg transition-all"
                    :class="period === p.key ? 'sk-bg-card sk-text-primary-brand shadow-sm' : 'sk-text-muted hover:sk-text-primary hover:bg-zinc-200'">
                    {{ p.label }}
                </KButton>
            </div>
        </div>

        <div class="flex-1 p-6 sm:p-8 max-w-[1400px] mx-auto w-full space-y-6">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="sk-bg-card rounded-2xl border sk-border shadow-sm p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center sk-bg-primary-soft sk-text-primary-brand shadow-inner">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold sk-text-muted uppercase tracking-wider">Total Servis</p>
                        <p class="text-2xl font-black sk-text-primary">{{ summary.total_services }} <span class="text-sm font-bold sk-text-muted">Unit</span></p>
                    </div>
                </div>
                <div class="sk-bg-card rounded-2xl border sk-border shadow-sm p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center sk-bg-info-soft sk-text-info shadow-inner">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold sk-text-muted uppercase tracking-wider">Total Biaya Jasa</p>
                        <p class="text-2xl font-black sk-text-info">Rp {{ formatNumber(summary.total_service_charge) }}</p>
                    </div>
                </div>
                <div class="sk-bg-card rounded-2xl border sk-border shadow-sm p-5 flex items-center gap-4 relative overflow-hidden">
                    <div class="absolute right-0 top-0 w-24 h-24 sk-bg-success-soft rounded-full blur-2xl -mr-4 -mt-4 opacity-50"></div>
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center sk-bg-success-soft sk-text-success shadow-inner relative z-10">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="relative z-10">
                        <p class="text-xs font-bold sk-text-muted uppercase tracking-wider">Estimasi Komisi</p>
                        <p class="text-2xl font-black sk-text-success">Rp {{ formatNumber(summary.total_commissions) }}</p>
                    </div>
                </div>
                <div class="sk-bg-card rounded-2xl border sk-border shadow-sm p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-purple-50 text-purple-600 shadow-inner">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold sk-text-muted uppercase tracking-wider">Jml Teknisi Aktif</p>
                        <p class="text-2xl font-black sk-text-primary">{{ summary.technician_count }} <span class="text-sm font-bold sk-text-muted">Orang</span></p>
                    </div>
                </div>
            </div>

            <!-- Per Teknisi -->
            <div class="space-y-6">
                <div v-for="(item, techId) in commissions" :key="techId" class="sk-bg-card rounded-2xl border sk-border shadow-sm overflow-hidden">
                    <div class="flex justify-between items-center p-5 border-b sk-border sk-bg-hover">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full sk-bg-primary-soft sk-text-primary-brand flex items-center justify-center font-bold text-lg">
                                {{ (item.technician?.name || 'U')[0].toUpperCase() }}
                            </div>
                            <div>
                                <h3 class="text-lg font-bold sk-text-primary">{{ item.technician?.name || 'Tanpa Teknisi' }}</h3>
                                <p class="text-xs font-medium sk-text-muted">{{ item.total_services }} servis diselesaikan</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-bold sk-text-muted uppercase tracking-wider">Total Komisi Teknisi Ini</p>
                            <p class="text-xl font-black sk-text-success">Rp {{ formatNumber(item.estimated_commission) }}</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="sk-bg-card border-b sk-border">
                                    <th class="px-6 py-4 text-xs font-bold sk-text-muted uppercase tracking-wider whitespace-nowrap">Nota Servis</th>
                                    <th class="px-6 py-4 text-xs font-bold sk-text-muted uppercase tracking-wider whitespace-nowrap">Pelanggan</th>
                                    <th class="px-6 py-4 text-xs font-bold sk-text-muted uppercase tracking-wider whitespace-nowrap">Tgl Selesai</th>
                                    <th class="px-6 py-4 text-xs font-bold sk-text-muted uppercase tracking-wider text-right whitespace-nowrap">Total Jasa</th>
                                    <th class="px-6 py-4 text-xs font-bold sk-text-muted uppercase tracking-wider text-right whitespace-nowrap">Komisi (50%)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y sk-border-light">
                                <tr v-for="s in item.services" :key="s.id" class="hover:sk-bg-hover transition-colors group">
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <span class="font-mono text-xs font-bold sk-text-primary-brand sk-bg-primary-soft px-2 py-1 rounded-md border sk-border-primary group-hover:border-indigo-300 transition-colors">#{{ s.id }}</span>
                                    </td>
                                    <td class="px-6 py-3 text-sm font-bold sk-text-primary whitespace-nowrap">{{ s.customer_name }}</td>
                                    <td class="px-6 py-3 text-sm font-medium sk-text-muted whitespace-nowrap">{{ s.completed_at }}</td>
                                    <td class="px-6 py-3 text-sm font-bold sk-text-primary text-right whitespace-nowrap">Rp {{ formatNumber(s.service_charge) }}</td>
                                    <td class="px-6 py-3 text-sm font-black sk-text-success text-right whitespace-nowrap">Rp {{ formatNumber(s.commission) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div v-if="Object.keys(commissions).length === 0" class="sk-bg-card rounded-2xl border sk-border p-12 text-center shadow-sm">
                <div class="w-16 h-16 sk-bg-hover sk-text-muted rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-sm font-bold sk-text-primary">Tidak ada data komisi</p>
                <p class="text-xs sk-text-muted mt-1">Belum ada servis yang diselesaikan teknisi pada periode ini.</p>
            </div>
        </div>
    </div>
    </AuthenticatedLayout>
</template>

<script setup>
import KButton from '@/Components/KButton.vue';

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
