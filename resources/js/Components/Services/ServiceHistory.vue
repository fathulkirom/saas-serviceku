<template>
    <div class="space-y-5">
        <!-- WARRANTY CLAIMS -->
        <div v-if="service.warranty_claims?.length" class="rounded-xl border p-5" style="border-color: var(--border-color); background: var(--bg-card);">
            <h3 class="text-sm font-bold mb-4 text-zinc-900">🛡️ Klaim Garansi</h3>
            <div class="space-y-2">
                <Link v-for="claim in service.warranty_claims" :key="claim.id" :href="route('services.show', claim.id)"
                    class="flex items-center justify-between px-3 py-2 rounded-lg text-sm transition-all bg-zinc-50">
                    <span class="text-zinc-900">#{{ claim.id }}</span>
                    <span class="text-xs" :style="statusStyle(claim.status)">{{ statusLabel(claim.status) }}</span>
                </Link>
            </div>
        </div>

        <!-- TIMELINE -->
        <div class="rounded-xl border p-5" style="border-color: var(--border-color); background: var(--bg-card);">
            <h3 class="text-sm font-bold mb-4 text-zinc-900">📊 Timeline Servis</h3>
            <div class="space-y-4">
                <div v-for="(evt, idx) in timeline" :key="idx" class="flex gap-3">
                    <div class="flex flex-col items-center">
                        <div class="w-3 h-3 rounded-full flex-shrink-0" :style="{ background: evt.active ? 'var(--primary)' : '#d1d5db' }"></div>
                        <div v-if="idx < timeline.length - 1" class="w-0.5 flex-1" style="background: #d1d5db; min-height: 24px;"></div>
                    </div>
                    <div class="pb-1">
                        <p class="text-sm font-semibold text-zinc-900">{{ evt.label }}</p>
                        <p class="text-xs text-zinc-500">{{ evt.date }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { formatDate, statusLabel, statusStyle } from '@/Composables/useServiceStatus.js';

const props = defineProps({
    service: { type: Object, default: () => ({}) },
});

const checklistMasuk = computed(() => {
    return (props.service.checklists || []).find(c => c.type === 'masuk');
});

const timeline = computed(() => {
    const items = [];
    if (props.service.created_at) {
        items.push({ label: 'Servis Dibuat', date: formatDate(props.service.created_at), active: true });
    }
    if (checklistMasuk.value) {
        items.push({ label: 'Checklist Masuk', date: formatDate(checklistMasuk.value.created_at), active: true });
    }
    items.push({ label: `Status: ${statusLabel(props.service.status)}`, date: formatDate(props.service.updated_at), active: true });
    return items;
});
</script>
