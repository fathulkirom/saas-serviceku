<template>
    <div :class="classes" :style="style"><slot>{{ initials }}</slot></div>
</template>

<script setup>
import { computed } from 'vue';

/**
 * Avatar reusable (standar) — menampilkan inisial dari nama.
 * Class/style dari parent diteruskan; bila parent tidak memberi class,
 * dipakai default (indigo, rounded-xl, sesuai ukuran).
 */
const props = defineProps({
    name: { type: String, default: '' },
    size: { type: String, default: 'md' }, // sm | md | lg
    style: { type: [Object, String], default: null },
    extraClass: { type: String, default: '' },
});

const initials = computed(() => {
    if (!props.name) return '?';
    return props.name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
});

const sizeMap = {
    sm: 'w-8 h-8 text-xs',
    md: 'w-10 h-10 text-sm',
    lg: 'w-12 h-12 text-base',
};

const classes = computed(() => {
    if (props.extraClass) return props.extraClass;
    return ['flex items-center justify-center font-bold text-white rounded-xl shadow-sm bg-indigo-600', sizeMap[props.size] || sizeMap.md];
});
</script>
