<template>
  <div class="flex flex-col items-center justify-center py-16 px-4">
    <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4" style="background: var(--bg-hover);">
      <span v-html="iconSvg" class="w-8 h-8" :style="{ color: 'var(--text-muted)' }"></span>
    </div>
    <p class="text-sm font-semibold mb-1" style="color: var(--text-primary);">{{ title }}</p>
    <p class="text-xs mb-6 text-center max-w-xs" style="color: var(--text-muted);">{{ description }}</p>
    <Link
      v-if="actionUrl && actionLabel"
      :href="actionUrl"
      class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 shadow-lg"
      :style="{ background: 'var(--accent-primary)' }"
    >
      {{ actionLabel }}
    </Link>
    <slot />
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { getIcon } from '@/Components/Icons.js';

const props = defineProps({
  icon: { type: String, default: 'default' },
  title: { type: String, default: 'Belum ada data' },
  description: { type: String, default: '' },
  actionUrl: { type: String, default: '' },
  actionLabel: { type: String, default: '' },
});

const iconSvg = computed(() => getIcon(props.icon));
</script>
