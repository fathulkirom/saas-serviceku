<template>
    <AdminLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-black text-slate-100 tracking-tight">Manajemen Tenant</h2>
                    <p class="text-sm text-slate-400 mt-1 font-medium">Kelola semua tenant dan status langganan</p>
                </div>
                <Link :href="route('admin.tenant.create')" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition-all shadow-sm hover:shadow-md hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Tambah Tenant
                </Link>
            </div>
        </template>

        <!-- Flash Messages -->
        <div v-if="$page.props.flash?.success" class="mb-5 p-4 rounded-xl border flex items-center gap-3 animate-slide-down bg-emerald-500/10 border-emerald-500/20">
            <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <p class="text-sm text-emerald-300 font-medium">{{ $page.props.flash.success }}</p>
        </div>
        <div v-if="$page.props.flash?.error" class="mb-5 p-4 rounded-xl border flex items-center gap-3 animate-slide-down bg-rose-500/10 border-rose-500/20">
            <svg class="w-5 h-5 text-rose-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-sm text-rose-300 font-medium">{{ $page.props.flash.error }}</p>
        </div>

        <!-- Filters -->
        <div class="rounded-2xl p-6 mb-6 border bg-slate-800/50 border-slate-700/50 backdrop-blur-xl animate-fade-in shadow-sm">
            <form @submit.prevent="search" class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Cari Tenant</label>
                    <KInput  type="text" v-model="filters.search" placeholder="Nama, email, ID tenant..."
                        class="w-full rounded-xl text-sm py-2.5 px-4 border transition-all duration-200 focus:ring-2 focus:ring-indigo-500/50 focus:outline-none bg-slate-900/50 border-slate-700 text-slate-200 placeholder-slate-500" />
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Status</label>
                    <KSelect  v-model="filters.status"
                        class="rounded-xl text-sm py-2.5 px-3 border transition-all duration-200 focus:ring-2 focus:ring-indigo-500/50 focus:outline-none bg-slate-900/50 border-slate-700 text-slate-200">
                        <option value="">Semua</option>
                        <option value="trial">Trial</option>
                        <option value="active">Active</option>
                        <option value="expired">Expired</option>
                        <option value="suspended">Suspended</option>
                    </KSelect>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Paket</label>
                    <KSelect  v-model="filters.plan_id"
                        class="rounded-xl text-sm py-2.5 px-3 border transition-all duration-200 focus:ring-2 focus:ring-indigo-500/50 focus:outline-none bg-slate-900/50 border-slate-700 text-slate-200">
                        <option value="">Semua</option>
                        <option v-for="p in plans" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </KSelect>
                </div>
                <div class="flex gap-2">
                    <KButton  type="submit" class="px-6 py-2.5 rounded-xl text-sm font-bold text-white transition-all duration-200 hover:-translate-y-0.5 bg-indigo-600 hover:bg-indigo-700 shadow-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Cari
                    </KButton>
                    <KButton  type="button" @click="resetFilters" class="px-5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 hover:-translate-y-0.5 border bg-slate-800 text-slate-300 border-slate-700 hover:bg-slate-700">
                        Reset
                    </KButton>
                </div>
            </form>
        </div>

        <!-- Tenant Table -->
        <div class="rounded-2xl overflow-hidden border bg-slate-800/50 border-slate-700/50 backdrop-blur-xl animate-slide-up shadow-sm">
            <div class="px-6 py-4 border-b border-slate-700/50 flex justify-between items-center">
                <h3 class="font-bold text-base text-slate-100">Daftar Tenant</h3>
                <span class="px-3 py-1 rounded-lg text-xs font-semibold bg-slate-700/50 text-slate-300">
                    Total: {{ tenants.total }} tenant
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-slate-700/50 bg-slate-900/50">
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Tenant</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Plan</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Tipe Bisnis</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Domain</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        <tr v-for="t in tenants.data" :key="t.id" class="transition-colors hover:bg-slate-700/30">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <Link :href="route('admin.tenant.show', t.id)" class="text-sm font-bold text-indigo-400 hover:text-indigo-300 transition-colors">{{ t.tenant_name }}</Link>
                                <p class="text-xs mt-0.5 text-slate-400 font-medium">{{ t.email }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-300">{{ t.plan?.name || '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-700/50 text-slate-300 border border-slate-600/50">
                                    {{ businessTypeLabel(t.data?.business_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <Badge :status="t.subscription_status" :label="t.subscription_status" />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-mono max-w-[160px] truncate text-slate-400 bg-slate-900/30 px-2 py-1 rounded-md">
                                {{ t.domains?.[0]?.domain || '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex gap-2 flex-wrap">
                                    <Link :href="route('admin.tenant.show', t.id)" class="text-xs font-bold px-3 py-1.5 rounded-lg transition-all hover:-translate-y-0.5 hover:shadow-sm text-indigo-300 bg-indigo-500/10 hover:bg-indigo-500/20">Detail</Link>
                                    <Link :href="route('admin.tenant.edit', t.id)" class="text-xs font-bold px-3 py-1.5 rounded-lg transition-all hover:-translate-y-0.5 hover:shadow-sm text-blue-300 bg-blue-500/10 hover:bg-blue-500/20">Edit</Link>
                                    <a v-if="t.domains?.[0]?.domain" :href="'http://' + t.domains[0].domain + '/login'" target="_blank" class="text-xs font-bold px-3 py-1.5 rounded-lg transition-all hover:-translate-y-0.5 hover:shadow-sm text-emerald-300 bg-emerald-500/10 hover:bg-emerald-500/20">Buka</a>
                                    <KButton  v-if="t.is_active" @click="suspend(t.id)" class="text-xs font-bold px-3 py-1.5 rounded-lg transition-all hover:-translate-y-0.5 hover:shadow-sm text-amber-300 bg-amber-500/10 hover:bg-amber-500/20 cursor-pointer">Suspend</KButton>
                                    <KButton  v-else @click="activate(t.id)" class="text-xs font-bold px-3 py-1.5 rounded-lg transition-all hover:-translate-y-0.5 hover:shadow-sm text-emerald-300 bg-emerald-500/10 hover:bg-emerald-500/20 cursor-pointer">Aktifkan</KButton>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="tenants.data.length === 0">
                            <td colspan="6" class="px-6 py-16 text-center bg-slate-900/20">
                                <div class="flex flex-col items-center gap-3">
                                    <span class="text-5xl opacity-50">🏢</span>
                                    <p class="text-sm font-medium text-slate-400">Tidak ada tenant ditemukan.</p>
                                    <Link :href="route('admin.tenant.create')" class="text-sm font-bold text-indigo-400 hover:text-indigo-300 transition-colors">
                                        Buat tenant baru &rarr;
                                    </Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-slate-700/50 flex justify-between items-center bg-slate-900/20" v-if="tenants.last_page > 1">
                <p class="text-xs font-medium text-slate-400">Halaman {{ tenants.current_page }} dari {{ tenants.last_page }}</p>
                <div class="flex gap-1.5">
                    <Link v-if="tenants.prev_page_url" :href="tenants.prev_page_url" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors border border-slate-700 text-slate-300 hover:bg-slate-700/50">
                        &larr; Prev
                    </Link>
                    <Link v-if="tenants.next_page_url" :href="tenants.next_page_url" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors border border-slate-700 text-slate-300 hover:bg-slate-700/50">
                        Next &rarr;
                    </Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import KButton from '@/Components/KButton.vue';
import KInput from '@/Components/KInput.vue';
import KSelect from '@/Components/KSelect.vue';

import { Link, router } from '@inertiajs/vue3';
import { reactive } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Badge from '@/Components/Badge.vue';

const props = defineProps({
    tenants: { type: Object, default: () => ({ data: [], total: 0 }) },
    plans: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const filters = reactive({
    search: props.filters.search || '',
    status: props.filters.status || '',
    plan_id: props.filters.plan_id || '',
    business_type: props.filters.business_type || '',
});

const businessTypeLabel = (type) => ({
    full_service: 'Servis & Sparepart',
    aksesoris_service: 'Aksesoris & Servis',
    aksespare_service: 'Pusat Servis & Sparepart',
    gadget_full: 'Gadget & Servis',
    retail_only: 'Retail Saja',
}[type] || type || 'Full Service');

const search = () => {
    router.get(route('admin.tenant.index'), filters, { preserveState: true, replace: true });
};

const resetFilters = () => {
    filters.search = '';
    filters.status = '';
    filters.plan_id = '';
    filters.business_type = '';
    router.get(route('admin.tenant.index'), {}, { preserveState: true, replace: true });
};

const suspend = (id) => {
    if (confirm('Yakin ingin suspend tenant ini?')) {
        router.post(route('admin.tenant.suspend', id));
    }
};

const activate = (id) => {
    router.post(route('admin.tenant.activate', id));
};
</script>
