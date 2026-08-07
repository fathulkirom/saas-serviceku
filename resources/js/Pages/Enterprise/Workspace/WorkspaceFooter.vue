<template>
  <div class="border-t px-4 sm:px-6 lg:px-8 py-2 flex items-center justify-between text-xs"
    :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-light)', color: 'var(--text-muted)' }">
    <div class="flex items-center gap-3">
      <span>ServiceKU Enterprise Workspace</span>
      <span v-if="lastRefreshed">· Diperbarui {{ timeAgo }}</span>
    </div>
    <div class="flex items-center gap-2">
      <button @click="$emit('refresh')" :disabled="isRefreshing" class="hover:text-indigo-600 transition-colors">
        Refresh
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  lastRefreshed: { type: Number, default: 0 },
  isRefreshing: { type: Boolean, default: false },
});

defineEmits(['refresh']);

const timeAgo = computed(() => {
  if (!props.lastRefreshed) return '';
  const diff = Math.floor((Date.now() - props.lastRefreshed) / 1000);
  if (diff < 60) return `${diff}s lalu`;
  return `${Math.floor(diff / 60)}m lalu`;
});
</script>
