<template>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <Link :href="route('services.index')" class="w-8 h-8 rounded-lg flex items-center justify-center border transition-all"
                style="border-color: var(--border-color); color: var(--text-muted); background: var(--bg-hover);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </Link>
            <div>
                <h2 class="text-xl font-bold sk-text-primary">Servis #{{ service.id }}</h2>
                <p class="text-xs mt-0.5 sk-text-muted">{{ service.customer?.name || '-' }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold" :style="statusStyle(service.status)">
                <span class="w-1.5 h-1.5 rounded-full" :style="{ background: statusDot(service.status) }"></span>
                {{ statusLabel(service.status) }}
            </span>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { statusStyle, statusDot, statusLabel } from '@/Composables/useServiceStatus.js';

const props = defineProps({
    service: { type: Object, default: () => ({}) },
});
</script>
