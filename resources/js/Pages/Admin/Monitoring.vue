<template>
    <AdminLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-bold text-slate-100">Monitoring Platform</h2>
                <p class="text-sm text-slate-400 mt-0.5">System health, storage, dan performa</p>
            </div>
        </template>

        <!-- Health Status -->
        <div class="rounded-2xl p-6 mb-6 border bg-slate-900/50 border-slate-800 backdrop-blur-xl">
            <h3 class="text-base font-bold text-slate-100 mb-5">System Health</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="p-4 rounded-xl" style="background: var(--bg-hover);">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">PHP Version</p>
                    <p class="font-semibold text-sm mt-1 text-slate-200">{{ health.php_version }}</p>
                </div>
                <div class="p-4 rounded-xl" style="background: var(--bg-hover);">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Laravel</p>
                    <p class="font-semibold text-sm mt-1 text-slate-200">{{ health.laravel_version }}</p>
                </div>
                <div class="p-4 rounded-xl" style="background: var(--bg-hover);">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Environment</p>
                    <p class="font-semibold text-sm mt-1 text-slate-200">{{ health.environment }}</p>
                </div>
                <div class="p-3 bg-slate-800/50 rounded">
                    <p class="text-xs text-slate-400">Debug Mode</p>
                    <span class="px-2 py-1 text-xs font-medium rounded" :class="health.debug_mode ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'">
                        {{ health.debug_mode ? 'ON' : 'OFF' }}
                    </span>
                </div>
                <div class="p-3 bg-slate-800/50 rounded">
                    <p class="text-xs text-slate-400">Database</p>
                    <p class="font-medium text-sm">{{ health.db_connection }}</p>
                    <span class="px-2 py-0.5 text-xs rounded" :class="health.db_status === 'Connected' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                        {{ health.db_status }}
                    </span>
                </div>
                <div class="p-3 bg-slate-800/50 rounded">
                    <p class="text-xs text-slate-400">Cache Driver</p>
                    <p class="font-medium text-sm">{{ health.cache_driver }}</p>
                </div>
                <div class="p-3 bg-slate-800/50 rounded">
                    <p class="text-xs text-slate-400">Queue Driver</p>
                    <p class="font-medium text-sm">{{ health.queue_driver }}</p>
                </div>
                <div class="p-3 bg-slate-800/50 rounded">
                    <p class="text-xs text-slate-400">Server Time</p>
                    <p class="font-medium text-sm">{{ health.server_time }}</p>
                </div>
            </div>
        </div>

        <!-- Queue Status -->
        <div v-if="queueStats" class="rounded-2xl p-6 mb-6 border bg-slate-900/50 border-slate-800 backdrop-blur-xl">
            <h3 class="font-semibold text-slate-100 mb-4">📬 Queue Status</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div class="p-3 bg-slate-800/50 rounded">
                    <p class="text-xs text-slate-400">Pending Jobs</p>
                    <p class="text-lg font-bold" :class="queueStats.pending > 10 ? 'text-amber-400' : 'text-emerald-400'">{{ queueStats.pending }}</p>
                </div>
                <div class="p-3 bg-slate-800/50 rounded">
                    <p class="text-xs text-slate-400">Failed Jobs</p>
                    <p class="text-lg font-bold" :class="queueStats.failed > 0 ? 'text-red-400' : 'text-emerald-400'">{{ queueStats.failed }}</p>
                </div>
                <div class="p-3 bg-slate-800/50 rounded">
                    <p class="text-xs text-slate-400">Backup Files</p>
                    <p class="text-lg font-bold text-indigo-400">{{ backupHealth?.file_count ?? 0 }}</p>
                </div>
                <div class="p-3 bg-slate-800/50 rounded">
                    <p class="text-xs text-slate-400">DB Status</p>
                    <p class="text-lg font-bold" :class="health.db_status === 'Connected' ? 'text-emerald-400' : 'text-red-400'">{{ health.db_status }}</p>
                </div>
            </div>
        </div>

        <!-- Storage Health -->
        <div class="rounded-2xl p-6 mb-6 border bg-slate-900/50 border-slate-800 backdrop-blur-xl">
            <h3 class="font-semibold text-slate-100 mb-4">💾 Storage Health</h3>

            <div v-for="alert in health.system_alerts" :key="alert.message" class="p-3 rounded-lg text-sm flex items-start gap-3 mb-3" :class="alert.type === 'danger' ? 'bg-red-50 border border-red-200 text-red-700' : 'bg-yellow-50 border border-yellow-200 text-yellow-700'">
                {{ alert.message }}
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- SSD -->
                <div class="p-4 bg-slate-800/50 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-medium text-slate-300">⚡ SSD (System)</p>
                        <span class="px-2 py-0.5 text-xs rounded-full" :class="ssdBadgeClass">{{ storageHealth?.ssd?.status || '?' }}</span>
                    </div>
                    <div class="w-full bg-slate-700 rounded-full h-2.5 mb-2">
                        <div class="h-2.5 rounded-full" :class="ssdBarClass" :style="{ width: (storageHealth?.ssd?.percent || 0) + '%' }"></div>
                    </div>
                    <div class="flex justify-between text-xs text-slate-400">
                        <span>{{ storageHealth?.ssd?.used }} / {{ storageHealth?.ssd?.total }}</span>
                        <span>{{ storageHealth?.ssd?.percent }}%</span>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">{{ storageHealth?.ssd?.message }}</p>
                </div>

                <!-- HDD -->
                <div class="p-4 bg-slate-800/50 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-medium text-slate-300">💽 HDD (Backup)</p>
                        <span class="px-2 py-0.5 text-xs rounded-full" :class="hddBadgeClass">{{ storageHealth?.hdd?.status || '?' }}</span>
                    </div>
                    <div v-if="storageHealth?.hdd?.info" class="w-full bg-slate-700 rounded-full h-2.5 mb-2">
                        <div class="bg-indigo-500 h-2.5 rounded-full" :style="{ width: (storageHealth?.hdd?.info?.percent || 0) + '%' }"></div>
                    </div>
                    <div v-if="storageHealth?.hdd?.info" class="flex justify-between text-xs text-slate-400">
                        <span>{{ storageHealth?.hdd?.info?.used }} / {{ storageHealth?.hdd?.info?.total }}</span>
                        <span>{{ storageHealth?.hdd?.info?.percent }}%</span>
                    </div>
                    <p class="text-xs mt-1" :class="storageHealth?.hdd?.status === 'unavailable' ? 'text-red-500' : 'text-slate-400'">
                        {{ storageHealth?.hdd?.message || 'HDD tidak terdeteksi' }}
                    </p>
                </div>

                <!-- MySQL Data (only when backend can measure it — no fake metrics) -->
                <div v-if="storageHealth?.mysql_data_size !== null && storageHealth?.mysql_data_size !== undefined" class="p-4 bg-slate-800/50 rounded-lg">
                    <p class="text-sm font-medium text-slate-300 mb-1">🗄️ MySQL Data</p>
                    <p class="text-lg font-bold text-slate-100">{{ storageHealth?.mysql_data_size }}</p>
                    <p class="text-xs text-slate-400 mt-2">Data tersimpan di database</p>
                </div>
            </div>
        </div>

        <!-- Backup Health -->
        <div v-if="backupHealth" class="rounded-2xl p-6 mb-6 border bg-slate-900/50 border-slate-800 backdrop-blur-xl">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-slate-100">📦 Backup Status</h3>
                    <p class="text-sm mt-1" :class="backupHealth.status === 'healthy' ? 'text-emerald-400' : backupHealth.status === 'critical' ? 'text-red-400' : 'text-amber-400'">
                        {{ backupHealth.message }}
                    </p>
                </div>
                <div class="text-right text-sm text-slate-400">
                    <p>File backup: <strong>{{ backupHealth.file_count }}</strong></p>
                    <p>Terakhir: <strong>{{ backupHealth.last_run }}</strong></p>
                </div>
            </div>
            <div v-for="alert in storageHealth.alerts" :key="alert.message" class="p-3 rounded-lg text-sm mb-2" :class="alert.type === 'danger' ? 'bg-red-50 text-red-700' : 'bg-yellow-50 text-yellow-700'">
                {{ alert.message }}
            </div>
        </div>

        <!-- Stats Today -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="rounded-2xl p-5 border bg-slate-900/50 border-slate-800 backdrop-blur-xl">
                <p class="text-sm text-slate-400">Registrasi Hari Ini</p>
                <p class="text-2xl font-bold mt-1">{{ registrationsToday }}</p>
            </div>
            <div class="rounded-2xl p-5 border bg-slate-900/50 border-slate-800 backdrop-blur-xl">
                <p class="text-sm text-slate-400">Error Hari Ini</p>
                <p class="text-2xl font-bold mt-1" :class="errorsToday > 0 ? 'text-red-400' : 'text-emerald-400'">{{ errorsToday }}</p>
            </div>
            <div class="rounded-2xl p-5 border bg-slate-900/50 border-slate-800 backdrop-blur-xl">
                <p class="text-sm text-slate-400">Total Revenue Platform</p>
                <p class="text-2xl font-bold mt-1 text-emerald-400">Rp {{ formatNumber(aggregate?.total_revenue || 0) }}</p>
            </div>
        </div>

        <!-- Tenant Stats Table -->
        <div class="rounded-2xl overflow-hidden mb-6 border bg-slate-900/50 border-slate-800 backdrop-blur-xl">
            <div class="px-6 py-4 border-b flex justify-between">
                <h3 class="font-semibold text-slate-100">Tenant Usage</h3>
                <Link :href="route('admin.sync-all-stats')" method="post" as="button" class="text-xs text-indigo-600 border border-indigo-200 px-2 py-1 rounded hover:bg-indigo-50">
                    Sync All
                </Link>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-slate-800/50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-slate-400">Tenant</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-slate-400">Users</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-slate-400">Servis</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-slate-400">Penjualan</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-slate-400">Revenue</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-slate-400">Produk</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-slate-400">Storage</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-slate-400">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="t in tenants" :key="t.id" class="hover:bg-slate-800/50 text-sm">
                        <td class="px-4 py-2">
                            <Link :href="route('admin.tenant.show', t.id)" class="text-indigo-600 hover:text-indigo-500 font-medium">{{ t.tenant_name }}</Link>
                            <p class="text-xs text-slate-400">{{ t.email }}</p>
                        </td>
                        <td class="px-4 py-2 text-right">{{ t.stats?.users_count || 0 }}</td>
                        <td class="px-4 py-2 text-right">{{ t.stats?.services_count || 0 }}</td>
                        <td class="px-4 py-2 text-right">{{ t.stats?.sales_count || 0 }}</td>
                        <td class="px-4 py-2 text-right">Rp {{ formatNumber(t.stats?.total_revenue || 0) }}</td>
                        <td class="px-4 py-2 text-right">{{ t.stats?.products_count || 0 }}</td>
                        <td class="px-4 py-2 text-right">{{ t.stats?.storage_used_mb || 0 }} MB</td>
                        <td class="px-4 py-2">
                            <span class="px-1.5 py-0.5 text-xs rounded-full" :class="statusClass(t.subscription_status)">{{ t.subscription_status }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    tenants: { type: Array, default: () => [] },
    aggregate: { type: Object, default: null },
    recentLogs: { type: Array, default: () => [] },
    errorsToday: { type: Number, default: 0 },
    registrationsToday: { type: Number, default: 0 },
    health: { type: Object, default: () => ({}) },
    storageHealth: { type: Object, default: null },
    backupHealth: { type: Object, default: null },
    queueStats: { type: Object, default: null },
});

const ssdBadgeClass = computed(() => ({
    healthy: 'bg-green-100 text-green-800',
    warning: 'bg-yellow-100 text-yellow-800',
    critical: 'bg-red-100 text-red-800',
}[props.storageHealth?.ssd?.status] || 'bg-slate-800 text-slate-200'));

const ssdBarClass = computed(() => ({
    healthy: 'bg-green-500',
    warning: 'bg-yellow-500',
    critical: 'bg-red-500',
}[props.storageHealth?.ssd?.status] || 'bg-green-500'));

const hddBadgeClass = computed(() => ({
    healthy: 'bg-green-100 text-green-800',
    warning: 'bg-yellow-100 text-yellow-800',
    critical: 'bg-red-100 text-red-800',
    unavailable: 'bg-slate-800 text-slate-400',
}[props.storageHealth?.hdd?.status] || 'bg-slate-800 text-slate-200'));

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num || 0);
const statusClass = (s) => ({
    trial: 'bg-yellow-100 text-yellow-800',
    active: 'bg-green-100 text-green-800',
    expired: 'bg-red-100 text-red-800',
    suspended: 'bg-slate-800 text-slate-200',
}[s] || 'bg-slate-800 text-slate-200');
</script>
