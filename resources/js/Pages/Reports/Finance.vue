<template>
    <AuthenticatedLayout>
    <div class="flex flex-col min-h-[calc(100vh-64px)] bg-zinc-50/50">
        <div class="px-6 sm:px-8 py-6 bg-white border-b border-zinc-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4 sticky top-0 z-20">
            <div class="flex items-center gap-4">
                <Link :href="route('reports.index')" class="w-10 h-10 bg-zinc-100 rounded-xl flex items-center justify-center text-zinc-600 hover:bg-zinc-200 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-zinc-900 tracking-tight">Laporan Keuangan</h1>
                    <p class="text-sm text-zinc-500 font-medium mt-0.5">Analisis arus kas, laba rugi, dan setoran</p>
                </div>
            </div>
            <div>
                <select v-model="period" @change="changePeriod" class="w-full sm:w-48 rounded-xl border border-zinc-300 px-4 py-2 text-sm font-semibold bg-white text-zinc-900 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all cursor-pointer shadow-sm">
                    <option value="today">Hari Ini</option>
                    <option value="week">Minggu Ini</option>
                    <option value="month">Bulan Ini</option>
                    <option value="year">Tahun Ini</option>
                </select>
            </div>
        </div>

        <div class="flex-1 p-6 sm:p-8 max-w-[1400px] mx-auto w-full space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-emerald-50 text-emerald-600 shadow-inner">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Pendapatan</p>
                        <p class="text-2xl font-black text-emerald-600">Rp {{ formatNumber(summary.revenue) }}</p>
                        <p class="text-xs font-medium text-zinc-500 mt-0.5">{{ summary.sales_count }} transaksi</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-red-50 text-red-600 shadow-inner">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Pengeluaran</p>
                        <p class="text-2xl font-black text-red-600">Rp {{ formatNumber(summary.expenses) }}</p>
                        <p class="text-xs font-medium text-zinc-500 mt-0.5">{{ summary.expenses_count }} pengeluaran</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-indigo-50 text-indigo-600 shadow-inner">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Laba Bersih</p>
                        <p class="text-2xl font-black" :class="summary.profit >= 0 ? 'text-indigo-600' : 'text-red-600'">
                            Rp {{ formatNumber(summary.profit) }}
                        </p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-blue-50 text-blue-600 shadow-inner">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Total Setoran</p>
                        <p class="text-2xl font-black text-zinc-900">Rp {{ formatNumber(summary.total_deposits) }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Rincian Pengeluaran -->
                <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden flex flex-col h-[500px]">
                    <div class="px-6 py-5 border-b border-zinc-200 bg-zinc-50 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-zinc-900">Rincian Pengeluaran</h3>
                        <span class="text-sm font-bold text-zinc-500 bg-zinc-200 px-3 py-1 rounded-full">{{ expenses.length }} Data</span>
                    </div>
                    <div class="flex-1 overflow-y-auto p-2">
                        <div v-for="e in expenses" :key="e.id" class="flex justify-between items-center p-4 hover:bg-zinc-50 rounded-xl transition-all border-b border-zinc-100 last:border-0 group">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-red-50 text-red-500 flex items-center justify-center group-hover:bg-red-100 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-zinc-900">{{ e.description }}</p>
                                    <p class="text-xs font-medium text-zinc-500 mt-0.5">{{ e.expense_date }}</p>
                                </div>
                            </div>
                            <p class="text-base font-black text-red-600">- Rp {{ formatNumber(e.amount) }}</p>
                        </div>
                        <div v-if="expenses.length === 0" class="h-full flex flex-col items-center justify-center p-8 text-center">
                            <div class="w-16 h-16 bg-zinc-100 text-zinc-400 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <p class="text-sm font-bold text-zinc-900">Tidak ada pengeluaran</p>
                            <p class="text-xs text-zinc-500 mt-1">Belum ada data pengeluaran pada periode ini</p>
                        </div>
                    </div>
                </div>

                <!-- Rincian Setoran -->
                <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden flex flex-col h-[500px]">
                    <div class="px-6 py-5 border-b border-zinc-200 bg-zinc-50 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-zinc-900">Riwayat Setoran Kasir</h3>
                        <span class="text-sm font-bold text-zinc-500 bg-zinc-200 px-3 py-1 rounded-full">{{ deposits.length }} Data</span>
                    </div>
                    <div class="flex-1 overflow-y-auto p-2">
                        <div v-for="d in deposits" :key="d.id" class="flex justify-between items-center p-4 hover:bg-zinc-50 rounded-xl transition-all border-b border-zinc-100 last:border-0 group">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center group-hover:bg-blue-100 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-zinc-900">{{ d.deposit_date }}</p>
                                    <p v-if="d.note" class="text-xs font-medium text-zinc-500 mt-0.5 max-w-[200px] truncate">{{ d.note }}</p>
                                </div>
                            </div>
                            <p class="text-base font-black text-blue-600">Rp {{ formatNumber(d.amount) }}</p>
                        </div>
                        <div v-if="deposits.length === 0" class="h-full flex flex-col items-center justify-center p-8 text-center">
                            <div class="w-16 h-16 bg-zinc-100 text-zinc-400 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <p class="text-sm font-bold text-zinc-900">Tidak ada setoran</p>
                            <p class="text-xs text-zinc-500 mt-1">Belum ada data setoran kasir pada periode ini</p>
                        </div>
                    </div>
                </div>
            </div>
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
