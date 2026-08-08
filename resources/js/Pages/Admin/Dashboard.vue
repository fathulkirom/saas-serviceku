<template>
    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-slate-100">SuperAdmin Dashboard</h2>
                    <p class="text-sm text-slate-400 mt-0.5">Monitoring seluruh tenant & platform</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        🟢 System Online
                    </span>
                </div>
            </div>
        </template>

        <!-- ===== MAIN STATS ===== -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6 animate-fade-in">
            <div class="relative rounded-2xl p-5 border transition-all duration-300 hover:-translate-y-0.5 bg-slate-900/50 border-slate-800 backdrop-blur-xl">
                <div class="absolute left-0 top-0 bottom-0 w-1 rounded-l-2xl bg-purple-500"></div>
                <div class="flex items-start gap-4">
                    <div class="w-11 h-11 rounded-xl bg-purple-500/10 flex items-center justify-center text-purple-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Tenant</p>
                        <p class="text-2xl font-bold text-slate-100">{{ stats.total_tenants }}</p>
                        <p class="text-xs text-slate-500 mt-1">+{{ stats.recent_registrations }} minggu ini</p>
                    </div>
                </div>
            </div>

            <div class="relative rounded-2xl p-5 border transition-all duration-300 hover:-translate-y-0.5 bg-slate-900/50 border-slate-800 backdrop-blur-xl">
                <div class="absolute left-0 top-0 bottom-0 w-1 rounded-l-2xl bg-emerald-500"></div>
                <div class="flex items-start gap-4">
                    <div class="w-11 h-11 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Aktif</p>
                        <p class="text-2xl font-bold text-emerald-400">{{ stats.active_tenants }}</p>
                    </div>
                </div>
            </div>

            <div class="relative rounded-2xl p-5 border transition-all duration-300 hover:-translate-y-0.5 bg-slate-900/50 border-slate-800 backdrop-blur-xl">
                <div class="absolute left-0 top-0 bottom-0 w-1 rounded-l-2xl bg-cyan-500"></div>
                <div class="flex items-start gap-4">
                    <div class="w-11 h-11 rounded-xl bg-cyan-500/10 flex items-center justify-center text-cyan-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Trial</p>
                        <p class="text-2xl font-bold text-cyan-400">{{ stats.trial_tenants }}</p>
                        <p v-if="stats.expiring_trials > 0" class="text-xs text-amber-400 mt-1">⚠ {{ stats.expiring_trials }} akan expired</p>
                    </div>
                </div>
            </div>

            <div class="relative rounded-2xl p-5 border transition-all duration-300 hover:-translate-y-0.5 bg-slate-900/50 border-slate-800 backdrop-blur-xl">
                <div class="absolute left-0 top-0 bottom-0 w-1 rounded-l-2xl bg-red-500"></div>
                <div class="flex items-start gap-4">
                    <div class="w-11 h-11 rounded-xl bg-red-500/10 flex items-center justify-center text-red-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Suspended</p>
                        <p class="text-2xl font-bold text-red-400">{{ stats.suspended_tenants }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== GLOBAL STATS ===== -->
        <div v-if="totalStats" class="rounded-2xl p-6 mb-6 border animate-slide-up bg-slate-900/50 border-slate-800 backdrop-blur-xl">
            <h3 class="text-base font-bold text-slate-100 mb-5">Statistik Global Platform</h3>
            <div class="grid grid-cols-3 md:grid-cols-6 gap-6">
                <div class="text-center">
                    <p class="text-2xl font-bold text-indigo-400">{{ totalStats.total_users }}</p>
                    <p class="text-xs text-slate-400 mt-1">Total User</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-indigo-400">{{ totalStats.total_services }}</p>
                    <p class="text-xs text-slate-400 mt-1">Total Servis</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-indigo-400">{{ totalStats.total_sales }}</p>
                    <p class="text-xs text-slate-400 mt-1">Total Penjualan</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-emerald-400">Rp {{ formatNumber(totalStats.total_revenue) }}</p>
                    <p class="text-xs text-slate-400 mt-1">Total Revenue</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-indigo-400">{{ totalStats.total_products }}</p>
                    <p class="text-xs text-slate-400 mt-1">Total Produk</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-indigo-400">{{ totalStats.total_storage }} MB</p>
                    <p class="text-xs text-slate-400 mt-1">Storage</p>
                </div>
            </div>
        </div>

        <!-- ===== SYSTEM HEALTH + RECENT LOGS ===== -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6 animate-slide-up">
            <!-- System Health -->
            <div class="rounded-2xl p-6 border bg-slate-900/50 border-slate-800 backdrop-blur-xl">
                <h3 class="text-base font-bold text-slate-100 mb-4">System Health</h3>
                <div class="space-y-3" v-if="systemHealth">
                    <div class="flex justify-between items-center py-2 border-b border-slate-800">
                        <span class="text-sm text-slate-400">PHP Version</span>
                        <span class="text-sm font-mono font-semibold text-slate-200">{{ systemHealth.php_version }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-slate-800">
                        <span class="text-sm text-slate-400">Laravel</span>
                        <span class="text-sm font-mono font-semibold text-slate-200">{{ systemHealth.laravel_version }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-slate-800">
                        <span class="text-sm text-slate-400">Database Size</span>
                        <span class="text-sm font-semibold text-slate-200">{{ systemHealth.database_size }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-sm text-slate-400">Server Time</span>
                        <span class="text-sm font-mono font-semibold text-slate-200">{{ systemHealth.server_time }}</span>
                    </div>
                </div>
            </div>

            <!-- Recent Logs -->
            <div class="lg:col-span-2 rounded-2xl p-6 border bg-slate-900/50 border-slate-800 backdrop-blur-xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-slate-100">Aktivitas Terkini</h3>
                    <Link :href="route('admin.logs')" class="text-xs font-medium hover:underline text-indigo-400">Lihat Semua →</Link>
                </div>
                <div class="space-y-2 max-h-64 overflow-y-auto">
                    <div v-for="log in recentLogs" :key="log.id" class="flex items-center gap-3 py-2.5 px-3 rounded-xl transition-colors border-b border-slate-800">
                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-md uppercase" :class="levelClass(log.level)">{{ log.level }}</span>
                        <span class="text-sm text-slate-300 flex-1 truncate">{{ log.message }}</span>
                        <span class="text-xs text-slate-500 flex-shrink-0">{{ log.created_at }}</span>
                    </div>
                    <p v-if="recentLogs.length === 0" class="text-slate-500 text-center py-6 text-sm">Tidak ada log</p>
                </div>
            </div>
        </div>

        <!-- ===== QUICK ACTIONS ===== -->
        <div class="rounded-2xl p-6 border animate-slide-up bg-slate-900/50 border-slate-800 backdrop-blur-xl">
            <h3 class="text-base font-bold text-slate-100 mb-4">Aksi Cepat</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                <Link :href="route('admin.tenant.create')" class="flex flex-col items-center gap-2 px-4 py-4 rounded-xl transition-all duration-200 hover:-translate-y-0.5 group bg-slate-800/50 border-slate-700 hover:bg-slate-700/50">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-violet-600 flex items-center justify-center shadow-sm group-hover:shadow-md transition-all">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    </div>
                    <span class="text-xs font-semibold text-slate-300">Tambah Tenant</span>
                </Link>
                <Link :href="route('admin.plans')" class="flex flex-col items-center gap-2 px-4 py-4 rounded-xl transition-all duration-200 hover:-translate-y-0.5 group bg-slate-800/50 border-slate-700 hover:bg-slate-700/50">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center shadow-sm group-hover:shadow-md transition-all">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <span class="text-xs font-semibold text-slate-300">Kelola Paket</span>
                </Link>
                <Link :href="route('admin.vouchers.create')" class="flex flex-col items-center gap-2 px-4 py-4 rounded-xl transition-all duration-200 hover:-translate-y-0.5 group bg-slate-800/50 border-slate-700 hover:bg-slate-700/50">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center shadow-sm group-hover:shadow-md transition-all">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="text-xs font-semibold text-slate-300">Buat Voucher</span>
                </Link>
                <Link :href="route('admin.backup')" class="flex flex-col items-center gap-2 px-4 py-4 rounded-xl transition-all duration-200 hover:-translate-y-0.5 group bg-slate-800/50 border-slate-700 hover:bg-slate-700/50">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-sm group-hover:shadow-md transition-all">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    </div>
                    <span class="text-xs font-semibold text-slate-300">Backup</span>
                </Link>
                <Link :href="route('admin.monitoring')" class="flex flex-col items-center gap-2 px-4 py-4 rounded-xl transition-all duration-200 hover:-translate-y-0.5 group bg-slate-800/50 border-slate-700 hover:bg-slate-700/50">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-rose-500 to-pink-600 flex items-center justify-center shadow-sm group-hover:shadow-md transition-all">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <span class="text-xs font-semibold text-slate-300">Monitoring</span>
                </Link>
                <Link :href="route('admin.settings')" class="flex flex-col items-center gap-2 px-4 py-4 rounded-xl transition-all duration-200 hover:-translate-y-0.5 group bg-slate-800/50 border-slate-700 hover:bg-slate-700/50">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-slate-500 to-slate-600 flex items-center justify-center shadow-sm group-hover:shadow-md transition-all">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924-1.756-3.35a1.724 1.724 0 001.066-2.573c-1.543.94-3.31-.826-2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <span class="text-xs font-semibold text-slate-300">Pengaturan</span>
                </Link>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
    stats: { type: Object, default: () => ({ total_tenants: 0, active_tenants: 0, trial_tenants: 0, suspended_tenants: 0, recent_registrations: 0, expiring_trials: 0 }) },
    totalStats: { type: Object, default: null },
    systemHealth: { type: Object, default: null },
    recentLogs: { type: Array, default: () => [] },
});

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num || 0);

const levelClass = (level) => {
    const map = {
        emergency: 'sk-bg-danger/20 text-red-400',
        alert: 'bg-red-500/20 text-red-400',
        critical: 'bg-red-500/20 text-red-400',
        error: 'bg-red-500/10 text-red-400',
        warning: 'bg-amber-500/10 text-amber-400',
        notice: 'bg-blue-500/10 text-blue-400',
        info: 'bg-cyan-500/10 text-cyan-400',
        debug: 'bg-slate-500/10 text-slate-400',
    };
    return map[level] || map.info;
};
</script>
