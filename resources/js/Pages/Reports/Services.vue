<template>
    <AuthenticatedLayout>
        <PageHeader title="Laporan Servis">
            <select v-model="period" @change="changePeriod" class="w-auto rounded-lg border text-sm px-3 py-2.5 focus:ring-2 focus:outline-none border-dark-200 text-dark-700 focus:border-blue-500 focus:ring-blue-200 transition-all">
                <option value="today">Hari Ini</option>
                <option value="yesterday">Kemarin</option>
                <option value="week">Minggu Ini</option>
                <option value="month">Bulan Ini</option>
                <option value="year">Tahun Ini</option>
                <option value="custom">Kustom</option>
            </select>
        </PageHeader>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl border shadow-sm p-4 flex items-center gap-4" style="border-color: var(--border-color);">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(var(--accent-primary-rgb, 99,102,241), 0.12); color: var(--accent-primary);">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div>
                    <p class="text-xs text-dark-400 uppercase tracking-wider">Total Servis</p>
                    <p class="text-xl font-bold text-dark-900">{{ summary.total }}</p>
                </div>
            </div>
            <div class="bg-white rounded-xl border shadow-sm p-4 flex items-center gap-4" style="border-color: var(--border-color);">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(34,197,94,0.12); color: #16a34a;">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-dark-400 uppercase tracking-wider">Biaya Jasa</p>
                    <p class="text-xl font-bold text-success-600">Rp {{ formatNumber(summary.total_charge) }}</p>
                </div>
            </div>
            <div class="bg-white rounded-xl border shadow-sm p-4 flex items-center gap-4" style="border-color: var(--border-color);">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(255,193,7,0.12); color: #d97706;">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-dark-400 uppercase tracking-wider">Total Biaya</p>
                    <p class="text-xl font-bold text-dark-900">Rp {{ formatNumber(summary.total_cost) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border shadow-sm p-6 mb-6" style="border-color: var(--border-color);">
            <h3 class="text-sm font-bold text-dark-900 mb-4">Status Servis</h3>
            <div class="grid grid-cols-4 gap-4">
                <div v-for="(count, status) in summary.by_status" :key="status" class="text-center p-3 bg-dark-50 rounded-xl">
                    <p class="text-sm text-dark-400">{{ statusLabel(status) }}</p>
                    <p class="text-lg font-bold text-dark-900">{{ count }}</p>
                </div>
            </div>
        </div>

        <KTable :columns="columns" :rows="rows">
            <template #cell-id="{row}">
                <span style="color: var(--accent-primary);">#{{ row.id }}</span>
            </template>
            <template #cell-status="{row}">
                <Badge :variant="statusVariant(row.status)">
                    {{ statusLabel(row.status) }}
                </Badge>
            </template>
            <template #cell-total_cost="{row}">
                Rp {{ formatNumber(row.total_cost) }}
            </template>
            <template #empty>
                <EmptyState icon="tool" title="Tidak ada servis" description="Belum ada data servis pada periode ini" />
            </template>
        </KTable>
    </AuthenticatedLayout>
</template>

<script setup>
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
