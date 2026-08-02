<template>
    <div v-if="loading" :class="classes" :style="style" role="status">
        <svg class="animate-spin" :class="spinnerClass" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
        <span v-if="$slots.default" class="ml-2"><slot /></span>
    </div>
</template>

<script setup>
import { computed } from 'vue';

/**
 * Loading reusable (standar) — spinner sederhana.
 * size: sm | md | lg
 */
const props = defineProps({
    loading: { type: Boolean, default: true },
    size: { type: String, default: 'md' },
    style: { type: [Object, String], default: null },
    extraClass: { type: String, default: '' },
});

const spinnerClass = computed(() => ({
    sm: 'w-4 h-4',
    md: 'w-6 h-6',
    lg: 'w-8 h-8',
}[props.size] || 'w-6 h-6'));

const classes = computed(() => ['inline-flex items-center', 'text-indigo-600', props.extraClass].filter(Boolean));
</script>
