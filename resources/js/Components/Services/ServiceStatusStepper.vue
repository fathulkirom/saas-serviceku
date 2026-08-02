<template>
    <div v-if="isActive" class="flex items-center gap-2 px-2 py-3 overflow-x-auto" style="border-bottom: 1px solid var(--border-color);">
        <div v-for="(step, i) in statusTimeline" :key="step.key" class="flex items-center gap-2 whitespace-nowrap">
            <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold shrink-0 transition-all"
                :style="{
                    background: isStepDone(service.status, step.key) ? 'var(--primary)' : 'var(--bg-hover)',
                    color: isStepDone(service.status, step.key) ? '#fff' : 'var(--text-muted)',
                    boxShadow: step.key === service.status ? '0 0 0 3px var(--accent-glow)' : 'none',
                }">{{ i + 1 }}</div>
            <span class="text-xs font-medium whitespace-nowrap" :style="{
                color: isStepDone(service.status, step.key) ? 'var(--text-primary)' : 'var(--text-muted)',
                fontWeight: step.key === service.status ? '700' : '500',
            }">{{ step.label }}</span>
            <svg v-if="i < statusTimeline.length - 1" class="w-4 h-4 shrink-0 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { statusTimeline, isStepDone } from '@/Composables/useServiceStatus.js';

const props = defineProps({
    service: { type: Object, default: () => ({}) },
});

const isActive = computed(() => !['selesai', 'cancel', 'void', 'close'].includes(props.service.status));
</script>
