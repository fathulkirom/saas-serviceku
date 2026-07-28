<template>
    <AdminLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-100">System Logs</h2>
                    <p class="text-sm text-slate-400 mt-0.5">Pantau aktivitas sistem dan error</p>
                </div>
                <button @click="clearLogs" class="px-4 py-2.5 rounded-xl text-xs font-bold text-red-400 border border-red-500/20 hover:bg-red-500/10 transition-all duration-200" style="background: var(--bg-card);">🗑 Clear Logs</button>
            </div>
        </template>

        <div class="rounded-2xl p-5 mb-6 border" style="background: var(--bg-card); border-color: var(--border-color);">
            <form class="flex flex-wrap gap-4 items-end">
                <div>
                    <label class="block text-xs font-semibold mb-1.5" style="color: var(--text-muted);">Level</label>
                    <select v-model="filters.level" @change="filter" class="rounded-xl text-sm py-2.5 px-3 border transition-all duration-200" style="background: var(--bg-input); border-color: var(--border-color); color: var(--text-primary);">
                        <option value="">Semua Level</option>
                        <option value="info">Info</option>
                        <option value="warning">Warning</option>
                        <option value="error">Error</option>
                        <option value="critical">Critical</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5" style="color: var(--text-muted);">Tipe</label>
                    <select v-model="filters.type" @change="filter" class="rounded-xl text-sm py-2.5 px-3 border transition-all duration-200" style="background: var(--bg-input); border-color: var(--border-color); color: var(--text-primary);">
                        <option value="">Semua Tipe</option>
                        <option value="system">System</option>
                        <option value="tenant">Tenant</option>
                        <option value="security">Security</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5" style="color: var(--text-muted);">Dari</label>
                    <input type="date" v-model="filters.date_from" @change="filter" class="rounded-xl text-sm py-2.5 px-3 border transition-all duration-200" style="background: var(--bg-input); border-color: var(--border-color); color: var(--text-primary);" />
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5" style="color: var(--text-muted);">Sampai</label>
                    <input type="date" v-model="filters.date_to" @change="filter" class="rounded-xl text-sm py-2.5 px-3 border transition-all duration-200" style="background: var(--bg-input); border-color: var(--border-color); color: var(--text-primary);" />
                </div>
                <button type="button" @click="reset" class="px-4 py-2.5 rounded-xl text-xs font-semibold border transition-all duration-200" style="background: var(--bg-hover); color: var(--text-secondary); border-color: var(--border-color);">Reset</button>
            </form>
        </div>

        <div class="rounded-2xl overflow-hidden border" style="background: var(--bg-card); border-color: var(--border-color);">
            <div class="divide-y max-h-[600px] overflow-y-auto" :style="{ borderColor: 'var(--border-light)' }">
                <div v-for="log in logs.data" :key="log.id" class="px-6 py-3 hover:bg-opacity-50 transition-colors text-sm flex items-start gap-3" style="--hover-bg: var(--bg-hover);">
                    <span :class="levelClass(log.level)" class="px-2 py-0.5 text-xs font-bold rounded-lg flex-shrink-0 w-20 text-center">{{ log.level }}</span>
                    <span :class="typeClass(log.type)" class="px-2 py-0.5 text-xs font-semibold rounded-lg flex-shrink-0" style="background: var(--bg-hover); color: var(--text-muted);">{{ log.type }}</span>
                    <div class="flex-1 min-w-0">
                        <p style="color: var(--text-primary);">{{ log.message }}</p>
                        <p v-if="log.tenant_id" class="text-xs mt-0.5" style="color: var(--text-muted);">Tenant: {{ log.tenant_id }}</p>
                    </div>
                    <span class="text-xs flex-shrink-0" style="color: var(--text-muted);">{{ log.created_at }}</span>
                </div>
                <p v-if="logs.data?.length === 0" class="p-10 text-center text-sm" style="color: var(--text-muted);">Tidak ada log</p>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import { reactive } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    logs: { type: Object, default: () => ({ data: [] }) },
    filters: { type: Object, default: () => ({}) },
});

const filters = reactive({
    level: props.filters.level || '',
    type: props.filters.type || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
});

const levelClass = (l) => ({
    info: 'bg-blue-100 text-blue-800',
    warning: 'bg-yellow-100 text-yellow-800',
    error: 'bg-red-100 text-red-800',
    critical: 'bg-red-100 text-red-800 font-bold',
}[l] || 'bg-gray-100 text-gray-800');

const typeClass = (t) => ({
    system: 'bg-gray-100 text-gray-800',
    tenant: 'bg-indigo-100 text-indigo-800',
    security: 'bg-purple-100 text-purple-800',
}[t] || 'bg-gray-100 text-gray-800');

const filter = () => router.get(route('admin.logs'), filters, { preserveState: true });
const reset = () => {
    filters.level = ''; filters.type = ''; filters.date_from = ''; filters.date_to = '';
    router.get(route('admin.logs'), {}, { preserveState: true });
};
const clearLogs = () => router.post(route('admin.logs.clear'));
</script>
