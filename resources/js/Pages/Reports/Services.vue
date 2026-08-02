<template>
    <AuthenticatedLayout>
    <div class="flex flex-col min-h-[calc(100vh-64px)] bg-zinc-50/50">
        <div class="px-6 sm:px-8 py-6 bg-white border-b border-zinc-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4 sticky top-0 z-20">
            <div class="flex items-center gap-4">
                <Link :href="route('reports.index')" class="w-10 h-10 bg-zinc-100 rounded-xl flex items-center justify-center text-zinc-600 hover:bg-zinc-200 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-zinc-900 tracking-tight">Laporan Servis</h1>
                    <p class="text-sm text-zinc-500 font-medium mt-0.5">Ringkasan unit servis dan performa bengkel</p>
                </div>
            </div>
            <div>
                <KSelect  v-model="period" @change="changePeriod" class="w-full sm:w-48 rounded-xl border border-zinc-300 px-4 py-2 text-sm font-semibold bg-white text-zinc-900 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all cursor-pointer shadow-sm">
                    <option value="today">Hari Ini</option>
                    <option value="yesterday">Kemarin</option>
                    <option value="week">Minggu Ini</option>
                    <option value="month">Bulan Ini</option>
                    <option value="year">Tahun Ini</option>
                    <option value="custom">Kustom</option>
                </KSelect>
            </div>
        </div>

        <div class="flex-1 p-6 sm:p-8 max-w-[1400px] mx-auto w-full space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-indigo-50 text-indigo-600 shadow-inner">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Total Servis</p>
                        <p class="text-2xl font-black text-zinc-900">{{ summary.total }} <span class="text-base font-bold text-zinc-500">Unit</span></p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-emerald-50 text-emerald-600 shadow-inner">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Pendapatan Jasa</p>
                        <p class="text-2xl font-black text-emerald-600">Rp {{ formatNumber(summary.total_charge) }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-amber-50 text-amber-600 shadow-inner">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Total Biaya & Part</p>
                        <p class="text-2xl font-black text-amber-600">Rp {{ formatNumber(summary.total_cost) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-6">
                <h3 class="text-lg font-bold text-zinc-900 mb-4">Distribusi Status Servis</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3">
                    <div v-for="(count, status) in summary.by_status" :key="status" class="text-center p-3 bg-zinc-50 rounded-xl border border-zinc-100 flex flex-col justify-between">
                        <p class="text-xs font-bold text-zinc-500 mb-1 leading-snug">{{ statusLabel(status) }}</p>
                        <p class="text-xl font-black text-zinc-900">{{ count }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-zinc-200 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-zinc-900">Riwayat Servis</h3>
                </div>
                <KTable :columns="columns" :rows="rows">
                    <template #cell-id="{row}">
                        <span class="font-mono text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded-md">#{{ row.id }}</span>
                    </template>
                    <template #cell-customer_name="{row}">
                        <span class="font-bold text-zinc-900">{{ row.customer_name || 'Umum' }}</span>
                    </template>
                    <template #cell-status="{row}">
                        <Badge :variant="statusVariant(row.status)">
                            {{ statusLabel(row.status) }}
                        </Badge>
                    </template>
                    <template #cell-technician_name="{row}">
                        <span class="font-medium text-zinc-700">{{ row.technician_name }}</span>
                    </template>
                    <template #cell-total_cost="{row}">
                        <span class="font-bold text-zinc-900">Rp {{ formatNumber(row.total_cost) }}</span>
                    </template>
                    <template #empty>
                        <div class="py-12 text-center">
                            <div class="w-16 h-16 bg-zinc-100 text-zinc-400 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <p class="text-sm font-bold text-zinc-900">Tidak ada data</p>
                            <p class="text-xs text-zinc-500 mt-1">Belum ada data servis pada periode ini</p>
                        </div>
                    </template>
                </KTable>
            </div>
        </div>
    </div>
    </AuthenticatedLayout>
</template>

<script setup>
import KSelect from '@/Components/KSelect.vue';

import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import KTable from '@/Components/KTable.vue';
import Badge from '@/Components/Badge.vue';
import EmptyState from '@/Components/EmptyState.vue';

const props = defineProps({
    services: { type: Array, default: () => [] },
    summary: { type: Object, default: () => ({}) },
    period: { type: String, default: 'today' },
});

const period = ref(props.period);
const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num || 0);
const statusLabel = (s) => ({
    menunggu_alokasi: 'Menunggu Alokasi', diterima: 'Diterima', diagnosa: 'Diagnosa',
    dikerjakan: 'Dikerjakan', menunggu_konfirmasi_pelanggan: 'Konfirmasi Pelanggan',
    menunggu_konfirmasi_internal: 'Konfirmasi Internal', siap_diambil: 'Siap Diambil',
    indent: 'Indent', onpartner: 'Partner', selesai: 'Selesai',
    cancel: 'Cancel', void: 'Void', close: 'Close',
}[s] || s);
const statusVariant = (s) => ({
    menunggu_alokasi: 'yellow', diterima: 'blue', diagnosa: 'cyan',
    dikerjakan: 'blue', menunggu_konfirmasi_pelanggan: 'pink',
    menunggu_konfirmasi_internal: 'pink', siap_diambil: 'green',
    indent: 'purple', onpartner: 'purple', selesai: 'green',
    cancel: 'red', void: 'red', close: 'gray',
}[s] || 'gray');

const columns = [
    { key: 'id', label: 'ID' },
    { key: 'customer_name', label: 'Pelanggan' },
    { key: 'status', label: 'Status' },
    { key: 'technician_name', label: 'Teknisi' },
    { key: 'total_cost', label: 'Biaya', align: 'right' },
];

const rows = computed(() => props.services.map(s => ({
    id: s.id,
    customer_name: s.customer?.name,
    status: s.status,
    technician_name: s.technician?.name || '-',
    total_cost: s.total_cost,
})));

const changePeriod = () => router.get(route('reports.services'), { period: period.value }, { preserveState: true });
</script>
