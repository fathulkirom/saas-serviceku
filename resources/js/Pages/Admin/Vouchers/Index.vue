<template>
    <AdminLayout>
        <div class="page-header">
            <div>
                <h1 class="text-2xl font-bold text-dark-900">Voucher / Kode Promo</h1>
                <p class="text-sm text-dark-400 mt-1">Kelola kode diskon untuk pendaftaran baru & perpanjangan</p>
            </div>
            <Link :href="route('admin.vouchers.create')" class="btn-premium-primary">
                + Buat Voucher Baru
            </Link>
        </div>

        <div class="card-premium !p-0 overflow-hidden">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-dark-400 uppercase tracking-wider">Kode</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-dark-400 uppercase tracking-wider">Tipe</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-dark-400 uppercase tracking-wider">Nilai</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-dark-400 uppercase tracking-wider">Untuk</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-dark-400 uppercase tracking-wider">Pemakaian</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-dark-400 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-dark-400 uppercase tracking-wider">Berlaku</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-dark-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-100">
                    <tr v-for="v in vouchers.data" :key="v.id" class="hover:bg-dark-50/50 transition-colors">
                        <td class="px-5 py-3.5">
                            <code class="px-2.5 py-1 rounded-lg bg-premium-50 text-premium-700 text-sm font-bold tracking-wider">{{ v.code }}</code>
                        </td>
                        <td class="px-5 py-3.5 text-sm">
                            <span class="badge-premium" :class="v.type === 'percent' ? 'bg-premium-50 text-premium-700' : 'bg-success-50 text-success-700'">
                                {{ v.type === 'percent' ? '% Diskon' : 'Harga Tetap' }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-sm font-semibold text-dark-900">
                            {{ v.type === 'percent' ? v.value + '%' : 'Rp ' + formatNumber(v.value) }}
                        </td>
                        <td class="px-5 py-3.5 text-sm text-dark-600">
                            <div>{{ v.applicable_for === 'new' ? 'Pendaftaran Baru' : v.applicable_for === 'existing' ? 'Perpanjangan' : 'Semua' }}</div>
                            <div v-if="v.tenant" class="text-xs text-premium-600 font-semibold mt-0.5">
                                🔒 Khusus: {{ v.tenant.tenant_name }}
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-sm text-dark-600">
                            {{ v.used_count }}<span v-if="v.max_uses"> / {{ v.max_uses }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="badge-premium" :class="v.is_active ? 'bg-success-50 text-success-700 border-success-200' : 'bg-accent-50 text-accent-700 border-accent-200'">
                                {{ v.is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-sm text-dark-500">
                            {{ v.expires_at ? formatDate(v.expires_at) : 'Tanpa batas' }}
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <Link :href="route('admin.vouchers.edit', v.id)" class="text-premium-600 hover:text-premium-500 text-sm font-semibold mr-3">Edit</Link>
                            <button @click="confirmDelete(v)" class="text-accent-600 hover:text-accent-500 text-sm font-semibold">Hapus</button>
                        </td>
                    </tr>
                    <tr v-if="vouchers.data.length === 0">
                        <td colspan="8" class="px-5 py-10 text-center text-dark-400 text-sm">Belum ada voucher. Buat voucher pertama!</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="vouchers.total > vouchers.per_page" class="mt-4">
            <component :is="'div'" v-html="vouchers.links" />
        </div>
    </AdminLayout>
</template>

<script setup>
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