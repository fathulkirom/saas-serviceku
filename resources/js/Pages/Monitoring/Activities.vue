<template>
    <AuthenticatedLayout>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold tracking-tight sk-text-primary">Aktivitas Sistem</h2>
                <p class="text-sm sk-text-muted mt-1">Daftar lengkap log aktivitas yang terjadi di dalam sistem</p>
            </div>
            <div>
                <Link :href="route('monitoring.index')" class="px-4 py-2 sk-bg-card hover:sk-bg-hover border sk-border sk-text-primary text-sm font-bold rounded-xl transition-colors shadow-sm inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali ke Monitoring
                </Link>
            </div>
        </div>

        <!-- Filters -->
        <div class="sk-bg-card rounded-2xl border sk-border shadow-sm p-5 mb-6">
            <form class="flex flex-wrap gap-4 items-end" @submit.prevent="filter">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-bold sk-text-muted uppercase tracking-wider mb-2">User</label>
                    <KSelect  v-model="filters.user_id" @change="filter" class="w-full rounded-xl border sk-border text-sm px-4 py-2.5 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all sk-text-primary sk-bg-card">
                        <option value="">Semua User</option>
                        <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                    </KSelect>
                </div>
                <div class="flex-1 min-w-[150px]">
                    <label class="block text-xs font-bold sk-text-muted uppercase tracking-wider mb-2">Aksi</label>
                    <KSelect  v-model="filters.action" @change="filter" class="w-full rounded-xl border sk-border text-sm px-4 py-2.5 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all sk-text-primary sk-bg-card">
                        <option value="">Semua Aksi</option>
                        <option value="created">Created</option>
                        <option value="updated">Updated</option>
                        <option value="completed">Completed</option>
                        <option value="login">Login</option>
                        <option value="logout">Logout</option>
                    </KSelect>
                </div>
                <div class="flex-1 min-w-[150px]">
                    <label class="block text-xs font-bold sk-text-muted uppercase tracking-wider mb-2">Dari Tanggal</label>
                    <KInput  type="date" v-model="filters.date_from" @change="filter" class="w-full rounded-xl border sk-border text-sm px-4 py-2.5 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all sk-text-primary sk-bg-card" />
                </div>
                <div class="flex-1 min-w-[150px]">
                    <label class="block text-xs font-bold sk-text-muted uppercase tracking-wider mb-2">Sampai Tanggal</label>
                    <KInput  type="date" v-model="filters.date_to" @change="filter" class="w-full rounded-xl border sk-border text-sm px-4 py-2.5 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all sk-text-primary sk-bg-card" />
                </div>
                <div>
                    <KButton  type="button" @click="resetFilter" class="px-5 py-2.5 rounded-xl border sk-border text-sm font-bold sk-text-primary sk-bg-card hover:sk-bg-hover transition-colors shadow-sm">
                        Reset Filter
                    </KButton>
                </div>
            </form>
        </div>

        <div class="sk-bg-card rounded-2xl border sk-border shadow-sm overflow-hidden">
            <KTable :columns="activityColumns" :rows="activities.data">
                <template #cell-user="{ row }">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full sk-bg-hover border sk-border flex items-center justify-center shrink-0">
                            <span class="text-xs font-bold sk-text-secondary">{{ (row.user?.name || 'S').charAt(0).toUpperCase() }}</span>
                        </div>
                        <span class="font-bold sk-text-primary">{{ row.user?.name || 'System' }}</span>
                    </div>
                </template>
                <template #cell-action="{ row }">
                    <span :class="['inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold border', actionClass(row.action)]">
                        {{ row.action }}
                    </span>
                </template>
                <template #cell-ip_address="{ row }">
                    <span class="font-mono text-xs sk-text-muted sk-bg-hover px-2 py-1 rounded-md border sk-border">
                        {{ row.ip_address || '-' }}
                    </span>
                </template>
                <template #cell-description="{ row }">
                    <span class="text-sm sk-text-secondary">{{ row.description }}</span>
                </template>
                <template #cell-created_at="{ row }">
                    <span class="text-sm font-medium sk-text-muted">{{ row.created_at }}</span>
                </template>
                <template #empty>
                    <EmptyState icon="monitoring" title="Tidak ada aktivitas" description="Tidak ada log aktivitas yang cocok dengan filter Anda." />
                </template>
            </KTable>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import KButton from '@/Components/KButton.vue';
import KInput from '@/Components/KInput.vue';
import KSelect from '@/Components/KSelect.vue';

import { Link, router } from '@inertiajs/vue3';
import { reactive } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import KTable from '@/Components/KTable.vue';
import EmptyState from '@/Components/EmptyState.vue';

const props = defineProps({
    activities: { type: Object, default: () => ({ data: [] }) },
    users: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const filters = reactive({
    user_id: props.filters.user_id || '',
    action: props.filters.action || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
});

const actionClass = (action) => ({
    created: 'sk-bg-success-soft sk-text-success sk-border-primary',
    updated: 'sk-bg-primary-soft sk-text-primary-brand sk-border-primary',
    completed: 'sk-bg-info-soft sk-text-info border-blue-200',
    login: 'sk-bg-primary-soft sk-text-primary-brand sk-border-primary',
    logout: 'sk-bg-hover sk-text-secondary sk-border',
    deleted: 'sk-bg-danger-soft sk-text-danger sk-border-primary',
}[action] || 'sk-bg-hover sk-text-secondary sk-border');

const filter = () => {
    router.get(route('monitoring.activities'), filters, { preserveState: true });
};

const resetFilter = () => {
    filters.user_id = '';
    filters.action = '';
    filters.date_from = '';
    filters.date_to = '';
    router.get(route('monitoring.activities'), {}, { preserveState: true });
};

const activityColumns = [
    { key: 'user', label: 'User' },
    { key: 'action', label: 'Aksi' },
    { key: 'description', label: 'Deskripsi' },
    { key: 'ip_address', label: 'Alamat IP' },
    { key: 'created_at', label: 'Waktu' },
];
</script>
