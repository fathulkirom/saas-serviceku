<template>
    <AuthenticatedLayout>
    <div class="flex flex-col min-h-[calc(100vh-64px)] sk-bg-hover">
        <div class="px-6 sm:px-8 py-6 sk-bg-card border-b sk-border flex flex-col sm:flex-row sm:items-center justify-between gap-4 sticky top-0 z-20">
            <div class="flex items-center gap-4">
                <Link :href="route('reports.index')" class="w-10 h-10 sk-bg-hover rounded-xl flex items-center justify-center sk-text-secondary hover:bg-zinc-200 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold sk-text-primary tracking-tight">Analisis Pelanggan</h1>
                    <p class="text-sm sk-text-muted font-medium mt-0.5">Statistik pengguna dan tren kunjungan bengkel</p>
                </div>
            </div>
        </div>

        <div class="flex-1 p-6 sm:p-8 max-w-[1400px] mx-auto w-full space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="sk-bg-card rounded-2xl border sk-border shadow-sm p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center sk-bg-primary-soft sk-text-primary-brand shadow-inner">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold sk-text-muted uppercase tracking-wider">Total Pelanggan</p>
                        <p class="text-2xl font-black sk-text-primary">{{ totalCustomers }} <span class="text-sm font-bold sk-text-muted">Orang</span></p>
                    </div>
                </div>
                <div class="sk-bg-card rounded-2xl border sk-border shadow-sm p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center sk-bg-info-soft sk-text-info shadow-inner">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold sk-text-muted uppercase tracking-wider">Pelanggan Aktif (3 Bln)</p>
                        <p class="text-2xl font-black sk-text-info">{{ activeCustomers }} <span class="text-sm font-bold text-blue-400">Orang</span></p>
                    </div>
                </div>
                <div class="sk-bg-card rounded-2xl border sk-border shadow-sm p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center shadow-inner" 
                        :class="retentionRate > 50 ? 'sk-bg-success-soft sk-text-success' : 'sk-bg-warning-soft sk-text-warning'">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold sk-text-muted uppercase tracking-wider">Tingkat Retensi</p>
                        <p class="text-2xl font-black" :class="retentionRate > 50 ? 'sk-text-success' : 'sk-text-warning'">{{ retentionRate }}%</p>
                    </div>
                </div>
            </div>

            <div class="sk-bg-card rounded-2xl border sk-border shadow-sm overflow-hidden flex flex-col">
                <div class="px-6 py-5 border-b sk-border sk-bg-hover">
                    <h3 class="text-lg font-bold sk-text-primary">Jam Sibuk Bengkel (Peak Hours)</h3>
                    <p class="text-xs sk-text-muted mt-1 font-medium">Berdasarkan waktu pembuatan nota servis</p>
                </div>
                <div class="p-6 sm:p-8">
                    <div v-if="peakHours.length" class="space-y-4 max-w-2xl">
                        <div v-for="ph in peakHours" :key="ph.hour" class="flex items-center gap-4 group">
                            <span class="text-sm font-bold sk-text-primary w-16">{{ ph.hour }}:00</span>
                            <div class="flex-1 sk-bg-hover rounded-full h-4 overflow-hidden shadow-inner">
                                <div class="h-full rounded-full bg-indigo-500 group-hover:bg-indigo-400 transition-all duration-500 relative overflow-hidden" 
                                    :style="{ width: Math.min(ph.total * 10, 100) + '%' }">
                                    <div class="absolute inset-0 sk-bg-card/20"></div>
                                </div>
                            </div>
                            <span class="text-sm font-black sk-text-primary-brand w-24 text-right">{{ ph.total }} <span class="text-xs font-bold sk-text-muted uppercase">Servis</span></span>
                        </div>
                    </div>
                    <div v-else class="text-center py-12">
                        <div class="w-16 h-16 sk-bg-hover sk-text-muted rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <p class="text-sm font-bold sk-text-primary">Belum ada data jam sibuk</p>
                        <p class="text-xs sk-text-muted mt-1">Data akan muncul setelah ada transaksi servis.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </AuthenticatedLayout>
</template>
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
defineProps({ totalCustomers: { type: Number, default: 0 }, activeCustomers: { type: Number, default: 0 }, retentionRate: { type: Number, default: 0 }, peakHours: { type: Array, default: () => [] } });
</script>
