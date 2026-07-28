<template>
    <AdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail Tenant: {{ tenant.tenant_name }}</h2>
                <Link :href="route('admin.dashboard')" class="text-sm text-indigo-600 hover:text-indigo-500">← Kembali</Link>
            </div>
        </template>

        <!-- Flash Message -->
        <div v-if="$page.props.flash?.success" class="mb-4 p-4 bg-green-50 border border-green-200 rounded-md">
            <p class="text-sm text-green-700">{{ $page.props.flash.success }}</p>
        </div>

        <!-- Row 1: Info & Stats -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Info Tenant -->
            <div class="lg:col-span-2 rounded-2xl  p-6">
                <h3 class="font-semibold text-gray-900 mb-4">Informasi Tenant</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Nama Toko</p>
                        <p class="font-medium">{{ tenant.tenant_name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Email</p>
                        <p class="font-medium">{{ tenant.email }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Telepon</p>
                        <p class="font-medium">{{ tenant.phone || '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Paket</p>
                        <p class="font-medium">{{ tenant.plan?.name || '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Tipe Bisnis</p>
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-premium-50 text-premium-700">{{ businessTypeLabel }}</span>
                    </div>
                    <div>
                        <p class="text-gray-500">Status</p>
                        <span :class="statusBadge(tenant.subscription_status)" class="px-2 py-1 text-xs font-medium rounded-full">{{ tenant.subscription_status }}</span>
                    </div>
                    <div>
                        <p class="text-gray-500">Trial Ends</p>
                        <p class="font-medium">{{ tenant.trial_ends_at || '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Dibuat</p>
                        <p class="font-medium">{{ tenant.created_at }}</p>
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
                    <a v-if="domainUrl" :href="domainUrl" target="_blank" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700 inline-block">
                        🔗 Buka Toko
                    </a>
                    <Link :href="route('admin.tenant.login-as', tenant.id)" method="post" as="button" class="px-4 py-2 bg-emerald-600 text-white rounded-md text-sm hover:bg-emerald-700">
                        🔑 Login as Tenant
                    </Link>
                    <button v-if="tenant.is_active" @click="suspend" class="px-4 py-2 bg-red-600 text-white rounded-md text-sm hover:bg-red-700">Suspend</button>
                    <button v-else @click="activate" class="px-4 py-2 bg-green-600 text-white rounded-md text-sm hover:bg-green-700">Aktifkan</button>
                    <button @click="resetPassword" class="px-4 py-2 bg-yellow-600 text-white rounded-md text-sm hover:bg-yellow-700">
                        Reset Password
                    </button>
                    <Link :href="route('admin.sync-tenant-stats', tenant.id)" method="post" as="button" class="px-4 py-2 bg-indigo-100 text-indigo-700 rounded-md text-sm hover:bg-indigo-200">
                        Sync Stats
                    </Link>
                </div>

                <!-- Domain Management -->
                <div class="mt-6 pt-4 border-t">
                    <h4 class="font-semibold text-gray-900 mb-3 text-sm">Domain / Subdomain</h4>
                    <div v-if="tenant.domains?.length > 0" class="mb-2">
                        <p class="text-xs text-gray-500">Domain saat ini:</p>
                        <p v-for="d in tenant.domains" :key="d.id" class="text-sm font-mono text-indigo-600">{{ d.domain }}</p>
                    </div>
                    <p v-else class="text-xs text-gray-400 mb-2">Belum ada domain</p>
                    <form @submit.prevent="updateDomain" class="flex gap-2">
                        <input type="text" v-model="domainForm.domain" class="flex-1 rounded-md border-gray-300 shadow-sm text-sm" placeholder="contoh: tokoku.serviceku.app" required />
                        <button type="submit" :disabled="domainForm.processing" class="px-3 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700 disabled:opacity-50">Simpan</button>
                    </form>
                    <p class="text-xs text-gray-400 mt-2">Gunakan Cloudflare Tunnel untuk mengarahkan domain ke server lokal.</p>
                </div>
            </div>

            <!-- Stats Card -->
            <div class="rounded-2xl  p-6">
                <h3 class="font-semibold text-gray-900 mb-4">📊 Penggunaan</h3>
                <div v-if="stats" class="space-y-4">
                    <div>
                        <div class="flex justify-between text-sm"><span class="text-gray-500">Users</span><span class="font-medium">{{ stats.users_count }}</span></div>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-1"><div class="bg-indigo-500 h-2 rounded-full" :style="{ width: Math.min(100, (stats.users_count / 10) * 100) + '%' }"></div></div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm"><span class="text-gray-500">Servis</span><span class="font-medium">{{ stats.services_count }}</span></div>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-1"><div class="bg-blue-500 h-2 rounded-full" :style="{ width: Math.min(100, (stats.services_count / 50) * 100) + '%' }"></div></div>
                    </div>
                    <div class="flex justify-between text-sm"><span class="text-gray-500">Penjualan</span><span class="font-medium">{{ stats.sales_count }}</span></div>
                    <div class="flex justify-between text-sm"><span class="text-gray-500">Total Revenue</span><span class="font-medium text-green-600">Rp {{ formatNumber(stats.total_revenue) }}</span></div>
                    <div class="flex justify-between text-sm"><span class="text-gray-500">Produk</span><span class="font-medium">{{ stats.products_count }}</span></div>
                    <div class="flex justify-between text-sm"><span class="text-gray-500">Storage</span><span class="font-medium">{{ stats.storage_used_mb }} MB</span></div>
                    <div class="flex justify-between text-sm"><span class="text-gray-500">Terakhir Aktif</span><span class="font-medium">{{ stats.last_active_at || '-' }}</span></div>
                </div>
                <p v-else class="text-sm text-gray-500">Belum ada data. Klik "Sync Stats" untuk mengambil data.</p>
            </div>
        </div>

        <!-- Row 2: Laporan & Aktivitas -->
        <div v-if="tenantData" class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Recent Services -->
            <div class="rounded-2xl  p-6">
                <h3 class="font-semibold text-gray-900 mb-3">🔧 Servis Terbaru</h3>
                <div v-if="tenantData.recent_services?.length" class="space-y-2">
                    <div v-for="svc in tenantData.recent_services" :key="svc.id" class="flex justify-between items-center p-2 hover:bg-gray-50 rounded">
                        <div>
                            <p class="text-sm font-medium">#{{ svc.id }} - {{ svc.customer_name }}</p>
                            <p class="text-xs text-gray-500">{{ svc.created_at }}</p>
                        </div>
                        <span :class="serviceStatusBadge(svc.status)" class="px-2 py-0.5 text-xs rounded-full">{{ svc.status }}</span>
                    </div>
                </div>
                <p v-else class="text-sm text-gray-400">Belum ada servis</p>
            </div>

            <!-- Recent Sales -->
            <div class="rounded-2xl  p-6">
                <h3 class="font-semibold text-gray-900 mb-3">💰 Penjualan Terbaru</h3>
                <div v-if="tenantData.recent_sales?.length" class="space-y-2">
                    <div v-for="s in tenantData.recent_sales" :key="s.id" class="flex justify-between items-center p-2 hover:bg-gray-50 rounded">
                        <div>
                            <p class="text-sm font-medium">#{{ s.id }} - {{ s.customer_name }}</p>
                            <p class="text-xs text-gray-500">{{ s.created_at }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium">Rp {{ formatNumber(s.total) }}</p>
                            <span :class="s.payment_status === 'paid' ? 'text-green-600' : 'text-yellow-600'" class="text-xs">{{ s.payment_status }}</span>
                        </div>
                    </div>
                </div>
                <p v-else class="text-sm text-gray-400">Belum ada penjualan</p>
            </div>
        </div>

        <!-- Row 3: Ringkasan -->
        <div v-if="tenantData" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="rounded-2xl  p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center text-lg">🔧</div>
                <div>
                    <p class="text-lg font-bold text-gray-900">{{ tenantData.service_stats?.total || 0 }}</p>
                    <p class="text-xs text-gray-500">Total Servis ({{ tenantData.service_stats?.completed || 0 }} selesai)</p>
                </div>
            </div>
            <div class="rounded-2xl  p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center text-lg">💰</div>
                <div>
                    <p class="text-lg font-bold text-green-600">Rp {{ formatNumber(tenantData.monthly_revenue) }}</p>
                    <p class="text-xs text-gray-500">Revenue Bulan Ini</p>
                </div>
            </div>
            <div class="rounded-2xl  p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center text-lg">📦</div>
                <div>
                    <p class="text-lg font-bold text-gray-900">{{ tenantData.recent_services?.length || 0 }}</p>
                    <p class="text-xs text-gray-500">Servis Aktif</p>
                </div>
            </div>
        </div>

        <!-- Row 4: Aktivitas -->
        <div class="rounded-2xl  p-6">
            <h3 class="font-semibold text-gray-900 mb-3">📋 Aktivitas Terakhir</h3>
            <div v-if="activityLogs?.length" class="space-y-2">
                <div v-for="log in activityLogs" :key="log.id" class="flex items-start gap-3 p-2 hover:bg-gray-50 rounded">
                    <span :class="log.level === 'error' ? 'bg-red-100' : log.level === 'warning' ? 'bg-yellow-100' : 'bg-gray-100'" class="w-6 h-6 rounded-full flex items-center justify-center text-xs mt-0.5">
                        {{ log.level === 'error' ? '❌' : log.level === 'warning' ? '⚠️' : 'ℹ️' }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-700 truncate">{{ log.message }}</p>
                        <p class="text-xs text-gray-400">{{ log.created_at }}</p>
                    </div>
                </div>
            </div>
            <p v-else class="text-sm text-gray-400">Belum ada aktivitas tercatat.</p>
        </div>
    </AdminLayout>
</template>

<script setup>
import { useForm, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    tenant: { type: Object, required: true },
    stats: { type: Object, default: null },
    activityLogs: { type: Array, default: () => [] },
    tenantData: { type: Object, default: null },
});

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num || 0);

const domainUrl = computed(() => {
    if (props.tenant.domains?.length > 0) {
        return 'http://' + props.tenant.domains[0].domain + '/login';
    }
    return null;
});

const businessTypeLabel = computed(() => ({
    full_service: 'Servis & Sparepart',
    aksesoris_service: 'Aksesoris & Servis',
    aksespare_service: 'Pusat Servis & Sparepart',
    gadget_full: 'Gadget & Servis',
    retail_only: 'Retail Saja',
}[props.tenant.data?.business_type] || props.tenant.data?.business_type || 'Full Service'));

const statusBadge = (s) => ({
    trial: 'bg-yellow-100 text-yellow-800',
    active: 'bg-green-100 text-green-800',
    expired: 'bg-red-100 text-red-800',
    suspended: 'bg-gray-100 text-gray-800',
}[s] || 'bg-gray-100 text-gray-800');

const serviceStatusBadge = (s) => ({
    menunggu_alokasi: 'bg-yellow-100 text-yellow-800',
    dikerjakan: 'bg-blue-100 text-blue-800',
    selesai: 'bg-green-100 text-green-800',
    dilempar: 'bg-purple-100 text-purple-800',
    cancel: 'bg-red-100 text-red-800',
}[s] || 'bg-gray-100 text-gray-800');

const domainForm = useForm({ domain: '' });
const updateDomain = () => domainForm.post(route('admin.tenant.domain', props.tenant.id));
const suspend = () => router.post(route('admin.tenant.suspend', props.tenant.id));
const activate = () => router.post(route('admin.tenant.activate', props.tenant.id));
const resetPassword = () => {
    if (confirm('Apakah Anda yakin ingin me-reset password owner untuk tenant ini? Password baru akan digenerate secara acak dan ditampilkan di pesan sukses.')) {
        router.post(route('admin.tenant.reset-password', props.tenant.id));
    }
};
</script>
