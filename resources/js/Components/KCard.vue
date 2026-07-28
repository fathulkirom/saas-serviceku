<template>
    <div
        :class="[
            'rounded-xl border shadow-sm transition-all',
            paddingClass,
            hoverClass,
        ]"
        :style="cardStyle"
    >
        <div v-if="title || $slots.title" class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold" style="color: var(--text-primary);">
                <slot name="title">{{ title }}</slot>
            </h3>
            <slot name="action" />
        </div>
        <slot />
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    title: { type: String, default: '' },
    padding: { type: String, default: 'md' },
    hover: { type: Boolean, default: false },
    borderColor: { type: String, default: '' },
});

const paddingMap = { none: 'p-0', sm: 'p-3', md: 'p-5', lg: 'p-6' };
const paddingClass = computed(() => paddingMap[props.padding] || 'p-5');

const hoverClass = computed(() => props.hover ? 'hover:shadow-md hover:-translate-y-0.5 cursor-pointer' : '');
const cardStyle = computed(() => ({
    background: 'var(--bg-card)',
    ...(props.borderColor ? { borderColor: props.borderColor } : {}),
}));
</script>