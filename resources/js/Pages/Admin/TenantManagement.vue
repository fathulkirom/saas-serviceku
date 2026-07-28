<template>
    <AdminLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-100">Manajemen Tenant</h2>
                    <p class="text-sm text-slate-400 mt-0.5">Kelola semua tenant dan status langganan</p>
                </div>
                <Link :href="route('admin.tenant.create')" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white transition-all duration-200 hover:-translate-y-0.5 shadow-lg" style="background: var(--accent-primary);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Tambah Tenant
                </Link>
            </div>
        </template>

        <!-- Flash Messages -->
        <div v-if="$page.props.flash?.success" class="mb-4 p-4 rounded-xl border flex items-center gap-3 animate-slide-down" style="background: rgba(34,197,94,0.1); border-color: rgba(34,197,94,0.2);">
            <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <p class="text-sm text-emerald-300">{{ $page.props.flash.success }}</p>
        </div>
        <div v-if="$page.props.flash?.error" class="mb-4 p-4 rounded-xl border flex items-center gap-3 animate-slide-down" style="background: rgba(239,68,68,0.1); border-color: rgba(239,68,68,0.2);">
            <svg class="w-5 h-5 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-sm text-red-300">{{ $page.props.flash.error }}</p>
        </div>

        <!-- Filters -->
        <div class="rounded-2xl p-5 mb-5 border animate-fade-in" style="background: var(--bg-card); border-color: var(--border-color);">
            <form @submit.prevent="search" class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-semibold mb-1.5" style="color: var(--text-muted);">Cari Tenant</label>
                    <input type="text" v-model="filters.search" placeholder="Nama, email, ID tenant..."
                        class="w-full rounded-xl text-sm py-2.5 px-4 border transition-all duration-200 focus:ring-2 focus:outline-none"
                        :style="{ background: 'var(--bg-input)', borderColor: 'var(--border-color)', color: 'var(--text-primary)', placeholder: 'var(--text-muted)' }"
                    />
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5" style="color: var(--text-muted);">Status</label>
                    <select v-model="filters.status"
                        class="rounded-xl text-sm py-2.5 px-3 border transition-all duration-200 focus:ring-2 focus:outline-none"
                        :style="{ background: 'var(--bg-input)', borderColor: 'var(--border-color)', color: 'var(--text-primary)' }">
                        <option value="">Semua</option>
                        <option value="trial">Trial</option>
                        <option value="active">Active</option>
                        <option value="expired">Expired</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5" style="color: var(--text-muted);">Paket</label>
                    <select v-model="filters.plan_id"
                        class="rounded-xl text-sm py-2.5 px-3 border transition-all duration-200 focus:ring-2 focus:outline-none"
                        :style="{ background: 'var(--bg-input)', borderColor: 'var(--border-color)', color: 'var(--text-primary)' }">
                        <option value="">Semua</option>
                        <option v-for="p in plans" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white transition-all duration-200 hover:-translate-y-0.5" style="background: var(--accent-primary);">
                        🔍 Cari
                    </button>
                    <button type="button" @click="resetFilters" class="px-5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 hover:-translate-y-0.5 border" style="background: var(--bg-hover); color: var(--text-secondary); border-color: var(--border-color);">
                        Reset
                    </button>
                </div>
            </form>
        </div>

        <!-- Tenant Table -->
        <div class="rounded-2xl overflow-hidden border animate-slide-up" style="background: var(--bg-card); border-color: var(--border-color);">
            <div class="px-6 py-4 border-b flex justify-between items-center" style="border-color: var(--border-light);">
                <h3 class="font-bold text-base" style="color: var(--text-primary);">Daftar Tenant</h3>
                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold" style="background: var(--bg-hover); color: var(--text-muted);">
                    Total: {{ tenants.total }} tenant
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--border-light); background: var(--bg-hover);">
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider" style="color: var(--text-muted);">Tenant</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider" style="color: var(--text-muted);">Plan</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider" style="color: var(--text-muted);">Tipe Bisnis</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider" style="color: var(--text-muted);">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider" style="color: var(--text-muted);">Domain</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider" style="color: var(--text-muted);">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="t in tenants.data" :key="t.id" class="transition-colors" :style="{ borderBottom: '1px solid ' + 'var(--border-light)' }" style="--hover-bg: var(--bg-hover);">
                            <td class="px-6 py-4 whitespace-nowrap" @mouseenter="$event.target.closest('tr').style.background='var(--bg-hover)'" @mouseleave="$event.target.closest('tr').style.background=''">
                                <Link :href="route('admin.tenant.show', t.id)" class="text-sm font-semibold hover:underline" :style="{ color: 'var(--accent-primary)' }">{{ t.tenant_name }}</Link>
                                <p class="text-xs mt-0.5" style="color: var(--text-muted);">{{ t.email }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" style="color: var(--text-primary);">{{ t.plan?.name || '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold" style="background: var(--bg-hover); color: var(--text-secondary);">
                                    {{ businessTypeLabel(t.data?.business_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <Badge :status="t.subscription_status" :label="t.subscription_status" />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-mono max-w-[160px] truncate" style="color: var(--text-muted);">
                                {{ t.domains?.[0]?.domain || '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex gap-1 flex-wrap">
                                    <Link :href="route('admin.tenant.show', t.id)" class="text-xs font-semibold px-2 py-1 rounded-lg transition-colors hover:opacity-80" style="color: var(--accent-primary); background: var(--accent-light);">Detail</Link>
                                    <Link :href="route('admin.tenant.edit', t.id)" class="text-xs font-semibold px-2 py-1 rounded-lg transition-colors hover:opacity-80" style="color: #60a5fa; background: rgba(96,165,250,0.1);">Edit</Link>
                                    <a v-if="t.domains?.[0]?.domain" :href="'http://' + t.domains[0].domain + '/login'" target="_blank" class="text-xs font-semibold px-2 py-1 rounded-lg transition-colors hover:opacity-80" style="color: #4ade80; background: rgba(74,222,128,0.1);">Buka</a>
                                    <button v-if="t.is_active" @click="suspend(t.id)" class="text-xs font-semibold px-2 py-1 rounded-lg transition-colors hover:opacity-80" style="color: #fbbf24; background: rgba(251,191,36,0.1);">Suspend</button>
                                    <button v-else @click="activate(t.id)" class="text-xs font-semibold px-2 py-1 rounded-lg transition-colors hover:opacity-80" style="color: #4ade80; background: rgba(74,222,128,0.1);">Aktifkan</button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="tenants.data.length === 0">
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <span class="text-4xl">🏢</span>
                                    <p class="text-sm" style="color: var(--text-muted);">Tidak ada tenant ditemukan.</p>
                                    <Link :href="route('admin.tenant.create')" class="text-sm font-semibold hover:underline" :style="{ color: 'var(--accent-primary)' }">
                                        Buat tenant baru →
                                    </Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div class="px-6 py-4 border-t flex justify-between items-center" v-if="tenants.last_page > 1" style="border-color: var(--border-light);">
                <p class="text-xs" style="color: var(--text-muted);">Halaman {{ tenants.current_page }} dari {{ tenants.last_page }}</p>
                <div class="flex gap-2">
                    <Link v-if="tenants.prev_page_url" :href="tenants.prev_page_url" class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition-colors hover:opacity-80" style="background: var(--bg-hover); color: var(--text-secondary); border-color: var(--border-color);">← Prev</Link>
                    <Link v-if="tenants.next_page_url" :href="tenants.next_page_url" class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition-colors hover:opacity-80" style="background: var(--bg-hover); color: var(--text-secondary); border-color: var(--border-color);">Next →</Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
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
