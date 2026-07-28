<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-lg font-bold text-dark-900">Monitoring</h2>
                <div class="flex gap-2">
                    <Link v-if="activeAlerts.length > 0" :href="route('monitoring.dismiss-all-alerts')" method="post" as="button" class="rounded-full px-3 py-1.5 bg-red-50 text-red-700 border border-red-200 text-sm font-semibold hover:bg-red-100 transition-colors">
                        Dismiss Semua Alert
                    </Link>
                    <Link :href="route('monitoring.check-low-stock')" method="post" as="button" class="rounded-full px-3 py-1.5 bg-warning-50 text-yellow-700 border border-warning-200 text-sm font-semibold hover:bg-warning-100 transition-colors">
                        Cek Stok
                    </Link>
                    <Link :href="route('monitoring.activities')" class="rounded-full px-3 py-1.5 text-sm font-semibold border" :style="{ background: 'rgba(var(--accent-primary-rgb, 99,102,241), 0.12)', color: 'var(--accent-primary)', borderColor: 'rgba(var(--accent-primary-rgb, 99,102,241), 0.25)' }">
                        Semua Aktivitas
                    </Link>
                </div>
            </div>
        </template>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="stat-card">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(var(--accent-primary-rgb, 99,102,241), 0.12); color: var(--accent-primary);">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-dark-400 uppercase tracking-wider">Servis Hari Ini</p>
                    <p class="text-xl font-bold text-dark-900">{{ stats.services_today }}</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-icon bg-success-50 text-success-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="stat-card-label">Revenue Hari Ini</p>
                    <p class="stat-card-value text-success-600">Rp {{ formatNumber(stats.revenue_today) }}</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(var(--accent-primary-rgb, 99,102,241), 0.12); color: var(--accent-primary);">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-dark-400 uppercase tracking-wider">Login Hari Ini</p>
                    <p class="text-xl font-bold text-dark-900">{{ loginsToday }}</p>
                    <p v-if="failedLogins > 0" class="text-xs text-red-500">{{ failedLogins }} failed</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-icon bg-warning-50 text-yellow-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <p class="stat-card-label">Stok Kritis</p>
                    <p class="stat-card-value">
                        <span class="text-yellow-600">{{ stats.low_stock_count }}</span>
                        <span v-if="stats.out_of_stock > 0" class="text-accent-500 ml-1">/ {{ stats.out_of_stock }} habis</span>
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Active Alerts -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl border shadow-sm" style="border-color: var(--border-color);">
                    <div class="px-4 py-3 border-b border-dark-100 flex justify-between items-center">
                        <h3 class="text-sm font-semibold text-dark-900">Alert Aktif</h3>
                        <span class="text-xs text-dark-400">{{ activeAlerts.length }} alert</span>
                    </div>
                    <div class="divide-y divide-dark-100 max-h-80 overflow-y-auto">
                        <div v-for="alert in activeAlerts" :key="alert.id" class="p-3 hover:bg-dark-50 transition-colors">
                            <div class="flex items-start gap-2">
                                <span class="mt-0.5 flex-shrink-0 h-2 w-2 rounded-full mt-1.5"
                                    :class="{'bg-red-500': alert.severity === 'danger', 'bg-yellow-500': alert.severity === 'warning', 'bg-blue-500': alert.severity === 'info'}">
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-dark-900 truncate">{{ alert.title }}</p>
                                    <p class="text-xs text-dark-400 mt-0.5">{{ alert.message }}</p>
                                    <p class="text-xs text-dark-300 mt-1">{{ alert.created_at }}</p>
                                </div>
                                <Link :href="route('monitoring.dismiss-alert', alert.id)" method="post" as="button"
                                    class="text-xs text-dark-300 hover:text-dark-500 flex-shrink-0">
                                    Dismiss
                                </Link>
                            </div>
                        </div>
                        <div v-if="activeAlerts.length === 0" class="p-6 text-center text-sm text-dark-400">
                            Tidak ada alert aktif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl border shadow-sm" style="border-color: var(--border-color);">
                    <div class="px-4 py-3 border-b border-dark-100 flex justify-between items-center">
                        <h3 class="text-sm font-semibold text-dark-900">Aktivitas Terkini</h3>
                        <Link :href="route('monitoring.activities')" class="text-xs font-medium" style="color: var(--accent-primary);">Lihat Semua</Link>
                    </div>
                    <div class="divide-y divide-dark-100 max-h-80 overflow-y-auto">
                        <div v-for="activity in recentActivities" :key="activity.id" class="px-4 py-2.5 hover:bg-dark-50 transition-colors flex items-center gap-3">
                            <div class="flex-shrink-0 h-8 w-8 rounded-full flex items-center justify-center" style="background: rgba(var(--accent-primary-rgb, 99,102,241), 0.12);">
                                <span class="text-xs font-medium" style="color: var(--accent-primary);">{{ activity.user.charAt(0) }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-dark-900">
                                    <span class="font-medium">{{ activity.user }}</span>
                                    <span class="text-dark-400"> {{ activity.description }}</span>
                                </p>
                                <p class="text-xs text-dark-300">{{ activity.time }}</p>
                            </div>
                            <span class="text-xs text-dark-300">{{ activity.created_at }}</span>
                        </div>
                        <div v-if="recentActivities.length === 0" class="p-6 text-center text-sm text-dark-400">
                            Belum ada aktivitas
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
