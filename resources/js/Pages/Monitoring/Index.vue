<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight sk-text-primary">Monitoring</h2>
                    <p class="text-sm sk-text-muted mt-1">Pantau aktivitas sistem dan metrik performa aplikasi</p>
                </div>
                <div class="flex items-center gap-2">
                    <Link v-if="activeAlerts.length > 0" :href="route('monitoring.dismiss-all-alerts')" method="post" as="button" class="px-4 py-2 sk-bg-danger-soft hover:sk-bg-danger-soft sk-text-danger border sk-border-primary text-sm font-bold rounded-xl transition-colors shadow-sm">
                        Dismiss Semua Alert
                    </Link>
                    <Link :href="route('monitoring.check-low-stock')" method="post" as="button" class="px-4 py-2 sk-bg-warning-soft hover:sk-bg-warning-soft sk-text-warning border sk-border-primary text-sm font-bold rounded-xl transition-colors shadow-sm">
                        Cek Stok
                    </Link>
                    <Link :href="route('monitoring.activities')" class="px-4 py-2 sk-bg-primary-soft hover:sk-bg-primary-soft sk-text-primary-brand border sk-border-primary text-sm font-bold rounded-xl transition-colors shadow-sm">
                        Semua Aktivitas
                    </Link>
                </div>
            </div>
        </template>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Servis Hari Ini -->
            <div class="sk-bg-card p-5 rounded-2xl border sk-border shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl sk-bg-primary-soft flex items-center justify-center sk-text-primary-brand border sk-border-primary shrink-0">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold sk-text-muted uppercase tracking-wider mb-0.5">Servis Hari Ini</p>
                    <p class="text-2xl font-bold sk-text-primary leading-none">{{ stats.services_today }}</p>
                </div>
            </div>

            <!-- Revenue Hari Ini -->
            <div class="sk-bg-card p-5 rounded-2xl border sk-border shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl sk-bg-success-soft flex items-center justify-center sk-text-success border border-emerald-100 shrink-0">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold sk-text-muted uppercase tracking-wider mb-0.5">Revenue Hari Ini</p>
                    <p class="text-xl font-bold sk-text-success leading-none truncate" :title="'Rp ' + formatNumber(stats.revenue_today)">Rp {{ formatNumber(stats.revenue_today) }}</p>
                </div>
            </div>

            <!-- Login Hari Ini -->
            <div class="sk-bg-card p-5 rounded-2xl border sk-border shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl sk-bg-info-soft flex items-center justify-center sk-text-info border border-blue-100 shrink-0">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold sk-text-muted uppercase tracking-wider mb-0.5">Login Hari Ini</p>
                    <div class="flex items-end gap-2">
                        <p class="text-2xl font-bold sk-text-primary leading-none">{{ loginsToday }}</p>
                        <p v-if="failedLogins > 0" class="text-xs font-medium sk-text-danger mb-0.5">{{ failedLogins }} failed</p>
                    </div>
                </div>
            </div>

            <!-- Stok Kritis -->
            <div class="sk-bg-card p-5 rounded-2xl border sk-border shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl sk-bg-warning-soft flex items-center justify-center sk-text-warning border border-amber-100 shrink-0">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold sk-text-muted uppercase tracking-wider mb-0.5">Stok Kritis</p>
                    <p class="text-2xl font-bold leading-none flex items-baseline gap-1.5">
                        <span class="sk-text-warning">{{ stats.low_stock_count }}</span>
                        <span v-if="stats.out_of_stock > 0" class="text-sm font-semibold sk-text-danger sk-bg-danger-soft px-1.5 py-0.5 rounded-md">/ {{ stats.out_of_stock }} habis</span>
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Active Alerts -->
            <div class="lg:col-span-1">
                <div class="sk-bg-card rounded-2xl border sk-border shadow-sm overflow-hidden flex flex-col h-full max-h-[500px]">
                    <div class="px-5 py-4 border-b sk-border sk-bg-hover flex justify-between items-center shrink-0">
                        <h3 class="text-sm font-bold sk-text-primary flex items-center gap-2">
                            <svg class="w-4 h-4 sk-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            Alert Aktif
                        </h3>
                        <span class="inline-flex items-center justify-center sk-bg-hover sk-text-secondary text-xs font-bold px-2.5 py-1 rounded-full border sk-border">
                            {{ activeAlerts.length }} alert
                        </span>
                    </div>
                    <div class="flex-1 overflow-y-auto p-0">
                        <div v-if="activeAlerts.length === 0" class="p-8 text-center flex flex-col items-center justify-center h-full">
                            <div class="w-12 h-12 sk-bg-hover rounded-full flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 sk-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <p class="text-sm font-medium sk-text-muted">Tidak ada alert aktif. Semua sistem normal.</p>
                        </div>
                        <div v-else class="divide-y sk-border-light">
                            <div v-for="alert in activeAlerts" :key="alert.id" class="p-4 hover:sk-bg-hover transition-colors group">
                                <div class="flex items-start gap-3">
                                    <div class="mt-1 shrink-0 w-2.5 h-2.5 rounded-full ring-4"
                                        :class="{
                                            'bg-red-500 ring-red-100': alert.severity === 'danger', 
                                            'bg-amber-500 ring-amber-100': alert.severity === 'warning', 
                                            'bg-blue-500 ring-blue-100': alert.severity === 'info'
                                        }">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-2 mb-1">
                                            <p class="text-sm font-bold sk-text-primary truncate">{{ alert.title }}</p>
                                            <Link :href="route('monitoring.dismiss-alert', alert.id)" method="post" as="button" class="opacity-0 group-hover:opacity-100 text-xs font-bold sk-text-muted hover:sk-text-primary transition-all shrink-0">
                                                Dismiss
                                            </Link>
                                        </div>
                                        <p class="text-xs sk-text-secondary leading-relaxed mb-2">{{ alert.message }}</p>
                                        <p class="text-[11px] font-medium sk-text-muted">{{ alert.created_at }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="lg:col-span-2">
                <div class="sk-bg-card rounded-2xl border sk-border shadow-sm overflow-hidden flex flex-col h-full max-h-[500px]">
                    <div class="px-5 py-4 border-b sk-border sk-bg-hover flex justify-between items-center shrink-0">
                        <h3 class="text-sm font-bold sk-text-primary flex items-center gap-2">
                            <svg class="w-4 h-4 sk-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Aktivitas Terkini
                        </h3>
                        <Link :href="route('monitoring.activities')" class="text-xs font-bold sk-text-primary-brand hover:sk-text-primary-brand transition-colors">Lihat Semua →</Link>
                    </div>
                    <div class="flex-1 overflow-y-auto p-0">
                        <div v-if="recentActivities.length === 0" class="p-8 text-center flex flex-col items-center justify-center h-full">
                            <div class="w-12 h-12 sk-bg-hover rounded-full flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 sk-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                            </div>
                            <p class="text-sm font-medium sk-text-muted">Belum ada aktivitas terekam.</p>
                        </div>
                        <div v-else class="divide-y sk-border-light">
                            <div v-for="activity in recentActivities" :key="activity.id" class="p-4 hover:sk-bg-hover transition-colors flex items-center gap-4">
                                <div class="shrink-0 w-10 h-10 rounded-full sk-bg-primary-soft border sk-border-primary flex items-center justify-center">
                                    <span class="text-sm font-bold sk-text-primary-brand">{{ activity.user ? activity.user.charAt(0).toUpperCase() : '?' }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm sk-text-primary leading-tight">
                                        <span class="font-bold">{{ activity.user }}</span>
                                        <span class="sk-text-muted ml-1">{{ activity.description }}</span>
                                    </p>
                                    <p class="text-xs font-medium sk-text-muted mt-1">{{ activity.time }}</p>
                                </div>
                                <div class="shrink-0">
                                    <span class="text-xs font-medium sk-text-muted whitespace-nowrap">{{ activity.created_at }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
    recentActivities: { type: Array, default: () => [] },
    activeAlerts: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({}) },
    loginsToday: { type: Number, default: 0 },
    failedLogins: { type: Number, default: 0 },
    hourlyActivity: { type: Object, default: () => ({}) },
});

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num || 0);
</script>
