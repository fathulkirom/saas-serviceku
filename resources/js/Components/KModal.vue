<template>
    <KDialog v-bind="$attrs">
        <slot />
    </KDialog>
</template>

<script setup>
/**
 * Alias kompatibilitas: KModal kini = KDialog (standar).
 * Semua props/slot/event diteruskan ke KDialog.
 */
import KDialog from '@/Components/KDialog.vue';

defineOptions({ inheritAttrs: false });
</script>
