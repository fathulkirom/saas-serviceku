<template>
    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="Aktivitas Sistem">
                <Link :href="route('monitoring.index')" class="text-sm font-medium" style="color: var(--accent-primary);">← Kembali</Link>
            </PageHeader>
        </template>

        <!-- Filters -->
        <div class="bg-white rounded-xl border shadow-sm p-4 mb-6" style="border-color: var(--border-color);">
            <form class="flex flex-wrap gap-4 items-end">
                <div>
                    <label class="block text-xs text-dark-400 mb-1">User</label>
                    <select v-model="filters.user_id" @change="filter" class="w-full rounded-lg border text-sm px-3 py-2.5 focus:ring-2 focus:outline-none border-dark-200 text-dark-700 focus:border-blue-500 focus:ring-blue-200 transition-all">
                        <option value="">Semua User</option>
                        <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-dark-400 mb-1">Aksi</label>
                    <select v-model="filters.action" @change="filter" class="w-full rounded-lg border text-sm px-3 py-2.5 focus:ring-2 focus:outline-none border-dark-200 text-dark-700 focus:border-blue-500 focus:ring-blue-200 transition-all">
                        <option value="">Semua Aksi</option>
                        <option value="created">Created</option>
                        <option value="updated">Updated</option>
                        <option value="completed">Completed</option>
                        <option value="login">Login</option>
                        <option value="logout">Logout</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-dark-400 mb-1">Dari Tanggal</label>
                    <input type="date" v-model="filters.date_from" @change="filter" class="w-full rounded-lg border text-sm px-3 py-2.5 focus:ring-2 focus:outline-none border-dark-200 text-dark-700 focus:border-blue-500 focus:ring-blue-200 transition-all" />
                </div>
                <div>
                    <label class="block text-xs text-dark-400 mb-1">Sampai</label>
                    <input type="date" v-model="filters.date_to" @change="filter" class="w-full rounded-lg border text-sm px-3 py-2.5 focus:ring-2 focus:outline-none border-dark-200 text-dark-700 focus:border-blue-500 focus:ring-blue-200 transition-all" />
                </div>
                <button type="button" @click="resetFilter" class="px-3 py-2.5 rounded-lg border text-sm font-semibold text-dark-600 border-dark-200 hover:bg-dark-50 transition-all">Reset</button>
            </form>
        </div>

        <KTable :columns="activityColumns" :rows="activities.data">
            <template #cell-user="{ row }">
                {{ row.user?.name || 'System' }}
            </template>
            <template #cell-action="{ row }">
                <Badge :variant="actionVariant(row.action)">{{ row.action }}</Badge>
            </template>
            <template #cell-ip_address="{ row }">
                {{ row.ip_address || '-' }}
            </template>
            <template #empty>
                <EmptyState icon="monitoring" title="Tidak ada aktivitas" description="Tidak ada data aktivitas ditemukan." />
            </template>
        </KTable>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import { reactive } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import KTable from '@/Components/KTable.vue';
import Badge from '@/Components/Badge.vue';
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
    created: 'bg-success-50 text-success-700 border border-success-200',
    updated: 'bg-blue-50 text-blue-700 border border-blue-200',
    completed: 'bg-blue-50 text-blue-700 border border-blue-200',
    login: 'bg-blue-50 text-blue-700 border border-blue-200',
    logout: 'bg-dark-100 text-dark-600 border border-dark-200',
    deleted: 'bg-accent-50 text-accent-700 border border-accent-200',
}[action] || 'bg-dark-100 text-dark-600 border border-dark-200');

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
    { key: 'ip_address', label: 'IP' },
    { key: 'created_at', label: 'Waktu' },
];

const actionVariant = (action) => ({
    created: 'green',
    updated: 'blue',
    completed: 'blue',
    login: 'blue',
    logout: 'default',
    deleted: 'red',
}[action] || 'default');
</script>
