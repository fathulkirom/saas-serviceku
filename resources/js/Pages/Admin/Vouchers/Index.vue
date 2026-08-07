<template>
    <AdminLayout>
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 tracking-tight">Voucher & Promo</h1>
                <p class="text-sm text-zinc-500 mt-1">Kelola kode diskon untuk pendaftaran baru & perpanjangan tenant</p>
            </div>
            <Link :href="route('admin.vouchers.create')" class="inline-flex items-center gap-2 px-5 py-2.5 bg-zinc-900 hover:bg-zinc-800 text-white text-sm font-bold rounded-xl shadow-sm hover:shadow-md transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Buat Voucher Baru
            </Link>
        </div>

        <div class="bg-white border border-zinc-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-zinc-50 text-zinc-500 font-semibold border-b border-zinc-200 uppercase tracking-wider text-xs">
                        <tr>
                            <th class="px-6 py-4">Kode Voucher</th>
                            <th class="px-6 py-4">Tipe & Nilai</th>
                            <th class="px-6 py-4">Berlaku Untuk</th>
                            <th class="px-6 py-4 text-center">Pemakaian</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4">Masa Berlaku</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        <tr v-if="vouchers.data.length === 0">
                            <td colspan="7" class="px-6 py-12 text-center text-zinc-500">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-zinc-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                                    <p class="text-base font-medium text-zinc-900">Belum ada voucher</p>
                                    <p class="text-sm mt-1">Buat voucher pertama Anda untuk mulai memberikan promo.</p>
                                </div>
                            </td>
                        </tr>
                        <tr v-for="v in vouchers.data" :key="v.id" class="hover:bg-zinc-50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-indigo-50 border border-indigo-100">
                                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                    <code class="text-indigo-700 text-sm font-bold tracking-wider">{{ v.code }}</code>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-zinc-900">
                                    {{ v.type === 'percent' ? v.value + '%' : 'Rp ' + formatNumber(v.value) }}
                                </p>
                                <p class="text-xs text-zinc-500 font-medium mt-0.5">
                                    {{ v.type === 'percent' ? 'Diskon Persentase' : 'Potongan Harga Tetap' }}
                                </p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1 text-sm text-zinc-700 font-medium">
                                    <span>{{ v.applicable_for === 'new' ? 'Pendaftaran Baru' : v.applicable_for === 'existing' ? 'Perpanjangan' : 'Semua Transaksi' }}</span>
                                    <span v-if="v.tenant" class="inline-flex items-center gap-1 text-xs text-amber-600 bg-amber-50 px-2 py-0.5 rounded-md w-fit">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        Khusus: {{ v.tenant.tenant_name }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="inline-flex items-center justify-center gap-1.5 px-3 py-1 rounded-full bg-zinc-100 text-zinc-700 text-xs font-bold border border-zinc-200">
                                    {{ v.used_count }} <span class="text-zinc-400 font-medium">/</span> {{ v.max_uses || '∞' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span v-if="v.is_active" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                </span>
                                <span v-else class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Nonaktif
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-zinc-500 font-medium">
                                {{ v.expires_at ? formatDate(v.expires_at) : 'Selamanya' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Link :href="route('admin.vouchers.edit', v.id)" class="px-3 py-1.5 text-xs font-bold text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 rounded-lg transition-colors">
                                        Edit
                                    </Link>
                                    <KButton  @click="confirmDelete(v)" class="px-3 py-1.5 text-xs font-bold text-red-400 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors">
                                        Hapus
                                    </KButton>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="vouchers.total > vouchers.per_page" class="mt-4">
            <component :is="'div'" v-html="vouchers.links" />
        </div>
    </AdminLayout>
</template>

<script setup>
import KButton from '@/Components/KButton.vue';

import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    vouchers: { type: Object, required: true },
});

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num || 0);

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
};

const confirmDelete = (v) => {
    if (confirm('Hapus voucher ' + v.code + '?')) {
        router.post(route('admin.vouchers.destroy', v.id));
    }
};
</script>