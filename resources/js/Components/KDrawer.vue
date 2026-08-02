<template>
    <Drawer v-bind="$attrs" @close="$emit('close')">
        <slot />
    </Drawer>
</template>

<script setup>
/**
 * Drawer reusable (standar) — alias dari Drawer.vue.
 * Menyediakan nama KDrawer sesuai standar komponen K-*.
 */
import Drawer from '@/Components/Drawer.vue';

defineOptions({ inheritAttrs: false });
defineEmits(['close']);
</script>
