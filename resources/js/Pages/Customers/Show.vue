<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail Pelanggan: {{ customer.name }}</h2>
                <Link :href="route('customers.index')" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 text-sm">Kembali</Link>
            </div>
        </template>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="rounded-2xl p-6 border" style="background: var(--bg-card); border-color: var(--border-color);">
                <h3 class="font-semibold text-gray-900 mb-4">Informasi Pelanggan</h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500">Nama</p>
                        <p class="font-medium">{{ customer.name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Telepon</p>
                        <p class="font-medium">{{ customer.phone || '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Email</p>
                        <p class="font-medium">{{ customer.email || '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Alamat</p>
                        <p class="font-medium">{{ customer.address || '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Member</p>
                        <span v-if="customer.is_member" class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Member</span>
                        <span v-else class="text-sm text-gray-500">Tidak</span>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl p-6 border" style="background: var(--bg-card); border-color: var(--border-color);">
                <h3 class="font-semibold text-gray-900 mb-4">Riwayat Servis</h3>
                <div v-if="customer.services?.length > 0">
                    <div v-for="s in customer.services" :key="s.id" class="flex justify-between py-2 border-b">
                        <div>
                            <Link :href="route('services.show', s.id)" class="text-sm text-indigo-600 hover:text-indigo-500">#{{ s.id }}</Link>
                            <span :class="statusClass(s.status)" class="ml-2 px-2 py-0.5 text-xs font-medium rounded-full">{{ s.status }}</span>
                        </div>
                        <p class="text-sm text-gray-500">{{ s.created_at }}</p>
                    </div>
                </div>
                <p v-else class="text-sm text-gray-500">Belum ada servis</p>
            </div>
        </div>

        <div class="rounded-2xl p-6 border" style="background: var(--bg-card); border-color: var(--border-color);">
            <h3 class="font-semibold text-gray-900 mb-4">Riwayat Penjualan</h3>
            <div v-if="customer.sales?.length > 0">
                <div v-for="s in customer.sales" :key="s.id" class="flex justify-between py-2 border-b">
                    <div>
                        <Link :href="route('sales.show', s.id)" class="text-sm text-indigo-600 hover:text-indigo-500">#{{ s.id }}</Link>
                        <span class="ml-2 text-sm text-gray-500">{{ s.sale_type }}</span>
                    </div>
                    <p class="text-sm font-medium">Rp {{ formatNumber(s.total) }}</p>
                </div>
            </div>
            <p v-else class="text-sm text-gray-500">Belum ada penjualan</p>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
    customer: { type: Object, required: true },
});

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num || 0);
const statusClass = (s) => ({
    menunggu_alokasi: 'bg-yellow-100 text-yellow-800',
    dikerjakan: 'bg-blue-100 text-blue-800',
    selesai: 'bg-green-100 text-green-800',
}[s] || 'bg-gray-100 text-gray-800');
</script>
