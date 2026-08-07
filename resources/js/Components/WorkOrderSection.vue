<template>
    <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-zinc-100 flex items-center justify-between">
            <h3 class="font-bold text-zinc-900">{{ title }}</h3>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-zinc-100 text-zinc-600">{{ orders?.length ?? 0 }} item</span>
        </div>
        <div v-if="orders?.length" class="divide-y divide-zinc-100">
            <div v-for="o in orders" :key="o.id" class="flex items-center justify-between gap-4 px-6 py-3 hover:bg-zinc-50">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-zinc-900 truncate">{{ o.service?.customer?.name || '—' }}</p>
                    <p class="text-xs text-zinc-500 truncate">{{ deviceLabel(o.service) }}</p>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <span class="px-2 py-1 rounded-lg text-xs font-bold" :class="statusStyle(o.status)">
                        {{ o.status || statusKey }}
                    </span>
                    <Link v-if="o.service_id" :href="route('services.show', o.service_id)" class="text-xs font-bold px-3 py-1.5 rounded-lg text-indigo-600 bg-indigo-50 hover:bg-indigo-100">Detail</Link>
                </div>
            </div>
        </div>
        <p v-else class="px-6 py-10 text-center text-sm text-zinc-400">Tidak ada work order.</p>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    title: { type: String, default: '' },
    orders: { type: Array, default: () => [] },
    statusKey: { type: String, default: '' },
});

const deviceLabel = (svc) => {
    if (!svc) return '—';
    const brand = svc.device?.brand || '';
    const model = svc.device?.model || '';
    return [brand, model].filter(Boolean).join(' ') || `#${svc.id}`;
};

const statusStyle = (s) => ({
    assigned: 'bg-indigo-50 text-indigo-700',
    in_progress: 'bg-blue-50 text-blue-700',
    waiting_part: 'bg-purple-50 text-purple-700',
    qc: 'bg-emerald-50 text-emerald-700',
}[s] || 'bg-zinc-100 text-zinc-600');
</script>
