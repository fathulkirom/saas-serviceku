<template>
    <div
        :class="[
            'rounded-xl border border-slate-200 bg-white shadow-sm transition-all',
            paddingClass,
            hoverClass,
        ]"
        :style="cardStyle"
    >
        <div v-if="title || $slots.title" class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-black text-slate-950">
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

const paddingMap = { none: 'p-0', sm: 'p-4', md: 'p-6', lg: 'p-8' };
const paddingClass = computed(() => paddingMap[props.padding] || 'p-6');

const hoverClass = computed(() => props.hover ? 'hover:shadow-md hover:-translate-y-0.5 hover:border-teal-200 cursor-pointer' : '');
const cardStyle = computed(() => ({
    ...(props.borderColor ? { borderColor: props.borderColor } : {}),
}));
</script>
