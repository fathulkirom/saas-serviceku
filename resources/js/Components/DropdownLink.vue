<template>
  <component
    :is="tag"
    :href="computedHref"
    :method="method"
    :as="as"
    class="block w-full px-4 py-2 text-left text-sm leading-5 transition-colors"
    :style="{ color: 'var(--text-secondary)' }"
    @mouseenter="hover = true"
    @mouseleave="hover = false"
    :class="{ 'bg-dark-50': hover }"
    @click="$emit('click', $event)"
  >
    <slot />
  </component>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  href: { type: String, default: '' },
  method: { type: String, default: 'get' },
  as: { type: String, default: 'a' },
});

defineEmits(['click']);

const hover = ref(false);

const isLink = computed(() => !!props.href);
const tag = computed(() => isLink.value ? Link : 'button');

const computedHref = computed(() => isLink.value ? props.href : undefined);
</script>
