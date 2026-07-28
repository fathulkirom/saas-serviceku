<template>
    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="Riwayat Login">
                <Link :href="route('monitoring.index')" class="text-sm font-medium" style="color: var(--accent-primary);">← Kembali</Link>
            </PageHeader>
        </template>

        <div class="bg-white rounded-xl border shadow-sm p-4 mb-6" style="border-color: var(--border-color);">
            <form class="flex gap-4 items-end">
                <div>
                    <label class="block text-xs text-dark-400 mb-1">User</label>
                    <select v-model="filters.user_id" @change="filter" class="w-full rounded-lg border text-sm px-3 py-2.5 focus:ring-2 focus:outline-none border-dark-200 text-dark-700 focus:border-blue-500 focus:ring-blue-200 transition-all">
                        <option value="">Semua User</option>
                        <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-dark-400 mb-1">Status</label>
                    <select v-model="filters.status" @change="filter" class="w-full rounded-lg border text-sm px-3 py-2.5 focus:ring-2 focus:outline-none border-dark-200 text-dark-700 focus:border-blue-500 focus:ring-blue-200 transition-all">
                        <option value="">Semua</option>
                        <option value="success">Success</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
            </form>
        </div>

        <KTable :columns="loginColumns" :rows="logins.data">
            <template #cell-user="{ row }">
                {{ row.user?.name }}
            </template>
            <template #cell-status="{ row }">
                <div>
                    <Badge :variant="row.status === 'success' ? 'green' : 'red'" :dot="true">{{ row.status === 'success' ? 'Berhasil' : 'Gagal' }}</Badge>
                    <p v-if="row.failure_reason" class="text-xs mt-0.5" style="color: var(--accent-primary);">{{ row.failure_reason }}</p>
                </div>
            </template>
            <template #cell-ip_address="{ row }">
                {{ row.ip_address || '-' }}
            </template>
            <template #cell-user_agent="{ row }">
                <span class="max-w-xs truncate block">{{ row.user_agent || '-' }}</span>
            </template>
            <template #empty>
                <EmptyState icon="monitoring" title="Tidak ada login" description="Tidak ada data login ditemukan." />
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
