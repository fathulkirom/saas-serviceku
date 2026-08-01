<template>
    <AdminLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-100">System Logs</h2>
                    <p class="text-sm text-slate-400 mt-0.5">Pantau aktivitas sistem dan error</p>
                </div>
                <button @click="clearLogs" class="px-4 py-2.5 rounded-xl text-xs font-bold text-red-400 border border-red-500/20 hover:bg-red-500/10 transition-all duration-200 bg-slate-900">🗑 Clear Logs</button>
            </div>
        </template>

        <div class="rounded-2xl p-5 mb-6 border bg-slate-900/50 border-slate-800 backdrop-blur-xl">
            <form class="flex flex-wrap gap-4 items-end">
                <div>
                    <label class="block text-xs font-semibold mb-1.5 text-slate-400">Level</label>
                    <select v-model="filters.level" @change="filter" class="rounded-xl text-sm py-2.5 px-3 border transition-all duration-200 bg-slate-950/50 border-slate-800 text-slate-200 focus:border-indigo-500 focus:ring-indigo-500/20">
                        <option value="">Semua Level</option>
                        <option value="info">Info</option>
                        <option value="warning">Warning</option>
                        <option value="error">Error</option>
                        <option value="critical">Critical</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5 text-slate-400">Tipe</label>
                    <select v-model="filters.type" @change="filter" class="rounded-xl text-sm py-2.5 px-3 border transition-all duration-200 bg-slate-950/50 border-slate-800 text-slate-200 focus:border-indigo-500 focus:ring-indigo-500/20">
                        <option value="">Semua Tipe</option>
                        <option value="system">System</option>
                        <option value="tenant">Tenant</option>
                        <option value="security">Security</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5 text-slate-400">Dari</label>
                    <input type="date" v-model="filters.date_from" @change="filter" class="rounded-xl text-sm py-2.5 px-3 border transition-all duration-200 bg-slate-950/50 border-slate-800 text-slate-200 focus:border-indigo-500 focus:ring-indigo-500/20" />
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5 text-slate-400">Sampai</label>
                    <input type="date" v-model="filters.date_to" @change="filter" class="rounded-xl text-sm py-2.5 px-3 border transition-all duration-200 bg-slate-950/50 border-slate-800 text-slate-200 focus:border-indigo-500 focus:ring-indigo-500/20" />
                </div>
                <button type="button" @click="reset" class="px-4 py-2.5 rounded-xl text-xs font-semibold border transition-all duration-200 bg-slate-800 text-slate-300 border-slate-700 hover:bg-slate-700">Reset</button>
            </form>
        </div>

        <div class="rounded-2xl overflow-hidden border bg-slate-900/50 border-slate-800 backdrop-blur-xl">
            <div class="divide-y max-h-[600px] overflow-y-auto border-slate-800">
                <div v-for="log in logs.data" :key="log.id" class="px-6 py-3 hover:bg-opacity-50 transition-colors text-sm flex items-start gap-3 hover:bg-slate-800/50">
                    <span class="px-2 py-0.5 text-xs font-bold rounded-lg flex-shrink-0 w-20 text-center" :class="levelClass(log.level)">{{ log.level }}</span>
                    <span class="bg-slate-800/50 text-slate-400 px-2 py-0.5 text-xs font-semibold rounded-lg flex-shrink-0" :class="typeClass(log.type)">{{ log.type }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-slate-200">{{ log.message }}</p>
                        <p v-if="log.tenant_id" class="text-xs mt-0.5 text-slate-400">Tenant: {{ log.tenant_id }}</p>
                    </div>
                    <span class="text-xs flex-shrink-0 text-slate-400">{{ log.created_at }}</span>
                </div>
                <p v-if="logs.data?.length === 0" class="p-10 text-center text-sm text-slate-400">Tidak ada log</p>
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
}[l] || 'bg-slate-800 text-slate-200');

const typeClass = (t) => ({
    system: 'bg-slate-800 text-slate-200',
    tenant: 'bg-indigo-100 text-indigo-800',
    security: 'bg-purple-100 text-purple-800',
}[t] || 'bg-slate-800 text-slate-200');

const filter = () => router.get(route('admin.logs'), filters, { preserveState: true });
const reset = () => {
    filters.level = ''; filters.type = ''; filters.date_from = ''; filters.date_to = '';
    router.get(route('admin.logs'), {}, { preserveState: true });
};
const clearLogs = () => router.post(route('admin.logs.clear'));
</script>
