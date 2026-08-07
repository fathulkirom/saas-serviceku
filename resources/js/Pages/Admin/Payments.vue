<template>
    <AdminLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-bold text-slate-100">Transaksi Pembayaran</h2>
                <p class="text-sm text-slate-400 mt-0.5">Pantau semua pembayaran tenant</p>
            </div>
        </template>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 animate-fade-in">
            <div class="rounded-2xl p-5 border bg-slate-900/50 border-slate-800 backdrop-blur-xl">
                <p class="text-2xl font-bold text-slate-200">Rp {{ formatNumber(stats.total_revenue) }}</p>
                <p class="text-xs mt-1 text-slate-400">Total Pendapatan</p>
            </div>
            <div class="rounded-2xl p-5 border bg-slate-900/50 border-slate-800 backdrop-blur-xl">
                <p class="text-2xl font-bold text-emerald-400">Rp {{ formatNumber(stats.monthly_revenue) }}</p>
                <p class="text-xs mt-1 text-slate-400">Bulan Ini</p>
            </div>
            <div class="rounded-2xl p-5 border bg-slate-900/50 border-slate-800 backdrop-blur-xl">
                <p class="text-2xl font-bold text-amber-400">{{ stats.pending_count }}</p>
                <p class="text-xs mt-1 text-slate-400">Pending</p>
            </div>
            <div class="rounded-2xl p-5 border bg-slate-900/50 border-slate-800 backdrop-blur-xl">
                <p class="text-2xl font-bold text-emerald-400">{{ stats.success_count }}</p>
                <p class="text-xs mt-1 text-slate-400">Sukses</p>
            </div>
        </div>

        <!-- Flash Message -->
        <div v-if="$page.props.flash?.success" class="mb-4 p-4 rounded-xl border flex items-center gap-3 bg-emerald-500/10 border-emerald-500/20">
            <p class="text-sm text-emerald-300">{{ $page.props.flash.success }}</p>
        </div>

        <!-- Tabel -->
        <div class="rounded-2xl overflow-hidden border bg-slate-900/50 border-slate-800 backdrop-blur-xl">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-slate-800/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Invoice</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Tenant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Plan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-slate-900">
                    <tr v-for="p in payments.data" :key="p.id" class="hover:bg-slate-800/50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-indigo-600">{{ p.invoice_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-100">{{ p.tenant?.tenant_name || '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-400">{{ p.plan_slug }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-100">Rp {{ formatNumber(p.amount) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full" :class="statusClass(p.status)">
                                {{ statusLabel(p.status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-400">{{ p.created_at }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="flex gap-2">
                                <KButton  v-if="p.status === 'pending'" @click="confirmPayment(p.id)"
                                    class="text-emerald-400 hover:text-green-800 text-xs font-medium">
                                    Konfirmasi
                                </KButton>
                                <KButton  v-if="p.status === 'pending'" @click="cancelPayment(p.id)"
                                    class="text-red-400 hover:text-red-800 text-xs font-medium">
                                    Batal
                                </KButton>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="payments.data?.length === 0">
                        <td colspan="7" class="px-6 py-4 text-center text-sm text-slate-400">Belum ada transaksi</td>
                    </tr>
                </tbody>
            </table>
            <div class="px-6 py-4 border-t" v-if="payments.links">
                <component :is="'div'" v-html="payments.links" class="flex justify-center gap-1" />
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import KButton from '@/Components/KButton.vue';

import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    payments: { type: Object, default: () => ({ data: [] }) },
    stats: { type: Object, default: () => ({}) },
    gatewayConfig: { type: Object, default: () => ({}) },
});

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num || 0);

const statusLabel = (status) => ({
    pending: 'Menunggu',
    success: 'Dibayar',
    failed: 'Gagal',
    expired: 'Kedaluwarsa',
    refunded: 'Dikembalikan',
}[status] || status);

const statusClass = (status) => ({
    pending: 'bg-yellow-100 text-yellow-800',
    success: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800',
    expired: 'bg-slate-800 text-slate-200',
    refunded: 'bg-orange-100 text-orange-800',
}[status] || 'bg-slate-800 text-slate-200');

const confirmPayment = (id) => {
    if (confirm('Konfirmasi pembayaran ini?')) {
        router.post(route('admin.payments.confirm', id));
    }
};

const cancelPayment = (id) => {
    if (confirm('Batalkan pembayaran ini?')) {
        router.post(route('admin.payments.cancel', id));
    }
};
</script>
