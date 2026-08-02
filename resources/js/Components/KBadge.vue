<template>
    <span :class="classes" :style="style"><slot /></span>
</template>

<script setup>
import { computed } from 'vue';

/**
 * Badge reusable (standar).
 * variant: '' (passthrough) | success | warning | danger | info | primary
 * Class/style dari parent ikut diteruskan.
 */
const props = defineProps({
    variant: { type: String, default: '' },
    extraClass: { type: String, default: '' },
    style: { type: [Object, String], default: null },
});

const classes = computed(() => {
    const list = [];
    if (props.variant) list.push('badge', `badge-${props.variant}`);
    if (props.extraClass) list.push(props.extraClass);
    return list;
});
</script>
