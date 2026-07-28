<template>
    <Link
        :href="href"
        :class="classes"
    >
        <slot />
    </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    href: { type: String, required: true },
    active: { type: Boolean, default: false },
});

const classes = computed(() => {
    return props.active
        ? 'nav-link-premium-active font-semibold'
        : 'nav-link-premium-inactive';
});
</script>
