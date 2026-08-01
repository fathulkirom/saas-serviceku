<template>
    <AuthenticatedLayout>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-zinc-900">Riwayat Login</h2>
                <p class="text-sm text-zinc-500 mt-1">Daftar percobaan login dari semua pengguna sistem</p>
            </div>
            <div>
                <Link :href="route('monitoring.index')" class="px-4 py-2 bg-white hover:bg-zinc-50 border border-zinc-200 text-zinc-700 text-sm font-bold rounded-xl transition-colors shadow-sm inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali ke Monitoring
                </Link>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-5 mb-6">
            <form class="flex flex-wrap gap-4 items-end" @submit.prevent="filter">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-2">User</label>
                    <select v-model="filters.user_id" @change="filter" class="w-full rounded-xl border border-zinc-300 text-sm px-4 py-2.5 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all text-zinc-900 bg-white">
                        <option value="">Semua User</option>
                        <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-2">Status</label>
                    <select v-model="filters.status" @change="filter" class="w-full rounded-xl border border-zinc-300 text-sm px-4 py-2.5 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all text-zinc-900 bg-white">
                        <option value="">Semua Status</option>
                        <option value="success">Success</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden">
            <KTable :columns="loginColumns" :rows="logins.data">
                <template #cell-user="{ row }">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-zinc-100 border border-zinc-200 flex items-center justify-center shrink-0">
                            <span class="text-xs font-bold text-zinc-600">{{ (row.user?.name || '?').charAt(0).toUpperCase() }}</span>
                        </div>
                        <span class="font-bold text-zinc-900">{{ row.user?.name || 'Unknown User' }}</span>
                    </div>
                </template>
                <template #cell-status="{ row }">
                    <div>
                        <span :class="['inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold border', row.status === 'success' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-700 border-red-200']">
                            <span :class="['w-1.5 h-1.5 rounded-full mr-1.5', row.status === 'success' ? 'bg-emerald-500' : 'bg-red-500']"></span>
                            {{ row.status === 'success' ? 'Berhasil' : 'Gagal' }}
                        </span>
                        <p v-if="row.failure_reason" class="text-xs font-medium text-red-600 mt-1.5 ml-0.5">{{ row.failure_reason }}</p>
                    </div>
                </template>
                <template #cell-ip_address="{ row }">
                    <span class="font-mono text-xs text-zinc-500 bg-zinc-50 px-2 py-1 rounded-md border border-zinc-200">
                        {{ row.ip_address || '-' }}
                    </span>
                </template>
                <template #cell-user_agent="{ row }">
                    <span class="max-w-xs truncate block text-sm text-zinc-600" :title="row.user_agent">{{ row.user_agent || '-' }}</span>
                </template>
                <template #cell-created_at="{ row }">
                    <span class="text-sm font-medium text-zinc-500">{{ row.created_at }}</span>
                </template>
                <template #empty>
                    <EmptyState icon="monitoring" title="Tidak ada login" description="Tidak ada data riwayat login yang ditemukan." />
                </template>
            </KTable>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import { reactive } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import KTable from '@/Components/KTable.vue';
import EmptyState from '@/Components/EmptyState.vue';

const props = defineProps({
    logins: { type: Object, default: () => ({ data: [] }) },
    users: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const filters = reactive({
    user_id: props.filters.user_id || '',
    status: props.filters.status || '',
});

const filter = () => {
    router.get(route('monitoring.logins'), filters, { preserveState: true });
};

const loginColumns = [
    { key: 'user', label: 'User' },
    { key: 'status', label: 'Status' },
    { key: 'ip_address', label: 'IP Address' },
    { key: 'user_agent', label: 'User Agent' },
    { key: 'created_at', label: 'Waktu' },
];
</script>
