<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-lg font-bold text-dark-900">Laporan Keuangan</h2>
                <select v-model="period" @change="changePeriod" class="w-auto rounded-lg border text-sm px-3 py-2.5 focus:ring-2 focus:outline-none border-dark-200 text-dark-700 focus:border-blue-500 focus:ring-blue-200 transition-all">
                    <option value="today">Hari Ini</option>
                    <option value="week">Minggu Ini</option>
                    <option value="month">Bulan Ini</option>
                    <option value="year">Tahun Ini</option>
                </select>
            </div>
        </template>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl border shadow-sm p-4 flex items-center gap-4" style="border-color: var(--border-color);">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(34,197,94,0.12); color: #16a34a;">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-dark-400 uppercase tracking-wider">Pendapatan</p>
                    <p class="text-xl font-bold text-success-600">Rp {{ formatNumber(summary.revenue) }}</p>
                    <p class="text-xs text-dark-300 mt-0.5">{{ summary.sales_count }} transaksi</p>
                </div>
            </div>
            <div class="bg-white rounded-xl border shadow-sm p-4 flex items-center gap-4" style="border-color: var(--border-color);">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(239,68,68,0.12); color: #dc2626;">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                </div>
                <div>
                    <p class="text-xs text-dark-400 uppercase tracking-wider">Pengeluaran</p>
                    <p class="text-xl font-bold text-red-600">Rp {{ formatNumber(summary.expenses) }}</p>
                    <p class="text-xs text-dark-300 mt-0.5">{{ summary.expenses_count }} pengeluaran</p>
                </div>
            </div>
            <div class="bg-white rounded-xl border shadow-sm p-4 flex items-center gap-4" style="border-color: var(--border-color);">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(34,197,94,0.12); color: #16a34a;">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
                <div>
                    <p class="text-xs text-dark-400 uppercase tracking-wider">Laba Bersih</p>
                    <p class="text-xl font-bold" :class="summary.profit >= 0 ? 'text-success-600' : 'text-red-600'">
                        Rp {{ formatNumber(summary.profit) }}
                    </p>
                </div>
            </div>
            <div class="stat-card">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(var(--accent-primary-rgb, 99,102,241), 0.12); color: var(--accent-primary);">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-dark-400 uppercase tracking-wider">Total Setoran</p>
                    <p class="text-xl font-bold" style="color: var(--accent-primary);">Rp {{ formatNumber(summary.total_deposits) }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl border shadow-sm p-6" style="border-color: var(--border-color);">
                <h3 class="text-sm font-bold text-dark-900 mb-4">Pengeluaran</h3>
                <div v-for="e in expenses" :key="e.id" class="flex justify-between py-2.5 border-b border-dark-100 last:border-0">
                    <div>
                        <p class="text-sm font-medium text-dark-900">{{ e.description }}</p>
                        <p class="text-xs text-dark-400">{{ e.expense_date }}</p>
                    </div>
                    <p class="text-sm font-medium text-accent-600">- Rp {{ formatNumber(e.amount) }}</p>
                </div>
                <p v-if="expenses.length === 0" class="text-sm text-dark-400 text-center py-4">Tidak ada pengeluaran</p>
            </div>

            <div class="bg-white rounded-xl border shadow-sm p-6" style="border-color: var(--border-color);">
                <h3 class="text-sm font-bold text-dark-900 mb-4">Setoran Harian</h3>
                <div v-for="d in deposits" :key="d.id" class="flex justify-between py-2.5 border-b border-dark-100 last:border-0">
                    <div>
                        <p class="text-sm font-medium text-dark-900">{{ d.deposit_date }}</p>
                        <p v-if="d.note" class="text-xs text-dark-400">{{ d.note }}</p>
                    </div>
                    <p class="text-sm font-medium" style="color: var(--accent-primary);">Rp {{ formatNumber(d.amount) }}</p>
                </div>
                <p v-if="deposits.length === 0" class="text-sm text-dark-400 text-center py-4">Tidak ada setoran</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    summary: { type: Object, default: () => ({}) },
    sales: { type: Array, default: () => [] },
    expenses: { type: Array, default: () => [] },
    deposits: { type: Array, default: () => [] },
    period: { type: String, default: 'month' },
});

const period = ref(props.period);
const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num || 0);
const changePeriod = () => router.get(route('reports.finance'), { period: period.value }, { preserveState: true });
</script>
