<template>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="rounded-xl border p-5" style="border-color: var(--border-color); background: var(--bg-card);">
            <h3 class="text-sm font-bold mb-4 text-zinc-900">👤 Data Pelanggan</h3>
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-bold text-white shadow-sm bg-indigo-600 text-white">
                        {{ getInitials(service.customer?.name) }}
                    </div>
                    <div>
                        <p class="text-sm font-bold text-zinc-900">{{ service.customer?.name || '-' }}</p>
                        <p class="text-xs text-zinc-500">{{ service.customer?.phone || '-' }}</p>
                        <p v-if="service.customer?.address" class="text-xs mt-0.5 text-zinc-500">{{ service.customer.address }}</p>
                    </div>
                </div>
                <div v-if="previousServices?.length" class="pt-2 border-t" style="border-color: var(--border-light);">
                    <p class="text-xs font-semibold mb-2 text-zinc-500">Riwayat Servis Sebelumnya:</p>
                    <Link v-for="ps in previousServices" :key="ps.id" :href="route('services.show', ps.id)"
                        class="block text-xs py-1 text-indigo-600">
                        #{{ ps.id }} — {{ formatDate(ps.created_at) }}
                    </Link>
                </div>
            </div>
        </div>
        <div class="rounded-xl border p-5" style="border-color: var(--border-color); background: var(--bg-card);">
            <h3 class="text-sm font-bold mb-4 text-zinc-900">📱 Data Perangkat</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-zinc-500">Teknisi</span><span class="font-semibold text-zinc-900">{{ service.technician?.name || '-' }}</span></div>
                <div class="flex justify-between"><span class="text-zinc-500">Tipe</span><span class="font-semibold text-zinc-900">{{ service.tipe_unit || '-' }}</span></div>
                <div class="flex justify-between"><span class="text-zinc-500">IMEI/SN</span><span class="font-semibold text-zinc-900">{{ service.imei_sn || '-' }}</span></div>
                <div class="flex justify-between"><span class="text-zinc-500">Sandi/PIN</span><span class="font-semibold text-zinc-900">{{ service.sandi_pola || '-' }}</span></div>
                <div class="flex justify-between"><span class="text-zinc-500">Kelengkapan</span><span class="font-semibold text-zinc-900">{{ Array.isArray(service.kelengkapan) ? service.kelengkapan.join(', ') : service.kelengkapan || '-' }}</span></div>
                <div class="flex justify-between"><span class="text-zinc-500">Cabang</span><span class="font-semibold text-zinc-900">{{ service.branch?.name || '-' }}</span></div>
                <div class="flex justify-between"><span class="text-zinc-500">Dibuat oleh</span><span class="font-semibold text-zinc-900">{{ service.creator?.name || '-' }}</span></div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { getInitials, formatDate } from '@/Composables/useServiceStatus.js';

const props = defineProps({
    service: { type: Object, default: () => ({}) },
    previousServices: { type: Array, default: () => [] },
});
</script>
