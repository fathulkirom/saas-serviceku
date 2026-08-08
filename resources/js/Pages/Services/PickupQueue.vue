<template>
    <AuthenticatedLayout>
        <div class="flex flex-col gap-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight sk-text-primary">Antrian Pengambilan</h2>
                    <p class="text-sm sk-text-muted mt-1">Servis yang sudah siap diambil tapi belum diambil pelanggan.</p>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('services.index')" class="inline-flex items-center gap-2 px-4 py-2 sk-bg-card hover:sk-bg-hover border sk-border sk-text-primary text-sm font-bold rounded-xl transition-colors shadow-sm">
                        ← Daftar Servis
                    </Link>
                </div>
            </div>

            <div class="sk-bg-card rounded-2xl border sk-border shadow-sm">
                <div class="px-6 py-4 border-b sk-border-light flex items-center justify-between">
                    <h3 class="font-bold sk-text-primary">Siap Diambil</h3>
                    <span class="text-xs font-semibold sk-text-muted">{{ pickups?.length ?? 0 }} unit</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b sk-border-light sk-bg-hover">
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider sk-text-muted">Servis</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider sk-text-muted">Pelanggan</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider sk-text-muted">Unit</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider sk-text-muted">Menunggu</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider sk-text-muted">Pembayaran</th>
                                <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider sk-text-muted">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y sk-border-light">
                            <tr v-for="p in pickups ?? []" :key="p.service_id" class="hover:sk-bg-hover">
                                <td class="px-6 py-3 text-sm font-bold sk-text-primary-brand">#{{ p.service_id }}</td>
                                <td class="px-6 py-3 text-sm font-semibold sk-text-primary">{{ p.customer || '—' }}</td>
                                <td class="px-6 py-3 text-sm sk-text-secondary">{{ p.device || '—' }}</td>
                                <td class="px-6 py-3">
                                    <span class="px-2 py-1 rounded-lg text-xs font-bold" :class="waitStyle(p.days_waiting)">
                                        {{ p.days_waiting }} hari
                                    </span>
                                </td>
                                <td class="px-6 py-3">
                                    <span class="px-2 py-1 rounded-lg text-xs font-bold" :class="p.payment_verified ? 'sk-bg-success-soft sk-text-success' : 'sk-bg-warning-soft sk-text-warning'">
                                        {{ p.payment_verified ? '✓ Lunas' : 'Belum Bayar' }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <Link :href="route('services.show', p.service_id)" class="text-xs font-bold px-3 py-1.5 rounded-lg sk-text-primary-brand sk-bg-primary-soft hover:sk-bg-primary-soft">Detail</Link>
                                </td>
                            </tr>
                            <tr v-if="!pickups?.length">
                                <td colspan="6" class="px-6 py-12 text-center text-sm sk-text-muted">Tidak ada servis menunggu diambil.</td>
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
    if (days >= 7) return 'sk-bg-danger-soft sk-text-danger';
    if (days >= 3) return 'sk-bg-warning-soft sk-text-warning';
    return 'sk-bg-hover sk-text-secondary';
};
</script>
