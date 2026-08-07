<template>
    <AuthenticatedLayout>
        <div class="flex flex-col gap-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-zinc-900">Antrian Pengambilan</h2>
                    <p class="text-sm text-zinc-500 mt-1">Servis yang sudah siap diambil tapi belum diambil pelanggan.</p>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('services.index')" class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-zinc-50 border border-zinc-200 text-zinc-700 text-sm font-bold rounded-xl transition-colors shadow-sm">
                        ← Daftar Servis
                    </Link>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm">
                <div class="px-6 py-4 border-b border-zinc-100 flex items-center justify-between">
                    <h3 class="font-bold text-zinc-900">Siap Diambil</h3>
                    <span class="text-xs font-semibold text-zinc-500">{{ pickups?.length ?? 0 }} unit</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-zinc-100 bg-zinc-50">
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500">Servis</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500">Pelanggan</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500">Unit</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500">Menunggu</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500">Pembayaran</th>
                                <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-zinc-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            <tr v-for="p in pickups ?? []" :key="p.service_id" class="hover:bg-zinc-50">
                                <td class="px-6 py-3 text-sm font-bold text-indigo-600">#{{ p.service_id }}</td>
                                <td class="px-6 py-3 text-sm font-semibold text-zinc-900">{{ p.customer || '—' }}</td>
                                <td class="px-6 py-3 text-sm text-zinc-600">{{ p.device || '—' }}</td>
                                <td class="px-6 py-3">
                                    <span class="px-2 py-1 rounded-lg text-xs font-bold" :class="waitStyle(p.days_waiting)">
                                        {{ p.days_waiting }} hari
                                    </span>
                                </td>
                                <td class="px-6 py-3">
                                    <span class="px-2 py-1 rounded-lg text-xs font-bold" :class="p.payment_verified ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'">
                                        {{ p.payment_verified ? '✓ Lunas' : 'Belum Bayar' }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <Link :href="route('services.show', p.service_id)" class="text-xs font-bold px-3 py-1.5 rounded-lg text-indigo-600 bg-indigo-50 hover:bg-indigo-100">Detail</Link>
                                </td>
                            </tr>
                            <tr v-if="!pickups?.length">
                                <td colspan="6" class="px-6 py-12 text-center text-sm text-zinc-400">Tidak ada servis menunggu diambil.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    pickups: { type: Array, default: () => [] },
});

const waitStyle = (days) => {
    if (days >= 7) return 'bg-red-50 text-red-700';
    if (days >= 3) return 'bg-amber-50 text-amber-700';
    return 'bg-zinc-100 text-zinc-600';
};
</script>
