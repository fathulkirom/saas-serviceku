<template>
  <div class="border-b px-4 sm:px-6 lg:px-8 py-2" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-light)' }">
    <div class="max-w-[1600px] mx-auto w-full flex items-center gap-2 overflow-x-auto">
      
      <!-- Action Buttons from Registry -->
      <button
        v-for="action in visibleActions"
        :key="action.id"
        @click="$emit('execute', action.id)"
        :disabled="actionLoading[action.id]"
        class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg transition-all whitespace-nowrap disabled:opacity-50"
        :class="action.danger ? 'hover:sk-bg-danger-soft' : 'hover:sk-bg-primary-soft'"
        :style="actionStyle(action)"
      >
        <div v-if="actionLoading[action.id]" class="sk-animate-spin w-3 h-3 border-2 border-current border-t-transparent rounded-full"></div>
        <span v-else class="text-sm">{{ actionIcon(action.id) }}</span>
        <span>{{ action.label }}</span>
        <kbd v-if="action.shortcut" class="text-[9px] px-1 py-0.5 rounded ml-1 hidden sm:inline" :style="{ background: 'var(--bg-hover)', color: 'var(--text-muted)' }">{{ action.shortcut }}</kbd>
      </button>

      <div class="flex-1"></div>

      <!-- Toggle Sidebar (mobile) -->
      <button
        @click="$emit('toggle-sidebar')"
        class="lg:hidden w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0"
        :style="{ color: 'var(--text-muted)' }"
        title="Sidebar"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>

      <!-- Toggle Inspector -->
      <button
        @click="$emit('toggle-inspector')"
        class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0"
        :style="{ color: 'var(--text-muted)' }"
        title="Inspector"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </button>
    </div>

    <!-- Error Toast -->
    <div v-if="lastError" class="mt-2 text-xs font-semibold px-3 py-1.5 rounded-lg flex items-center gap-2"
      :style="{ background: 'var(--danger-soft)', color: 'var(--danger-text)' }">
      <span>{{ lastError }}</span>
      <button @click="$emit('clear-error')" class="ml-auto font-bold">×</button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  actions: { type: Array, default: () => [] },
  actionLoading: { type: Object, default: () => ({}) },
  lastError: { type: String, default: '' },
});

defineEmits(['execute', 'toggle-sidebar', 'toggle-inspector', 'clear-error']);

const visibleActions = computed(() => props.actions || []);

function actionStyle(action) {
  if (action.danger) return { color: 'var(--danger-text)', background: 'transparent' };
  return { color: 'var(--text-secondary)', background: 'transparent' };
}

function actionIcon(id) {
  const icons = {
    assign: '👤', diagnose: '🔍', start: '▶️', complete: '✅',
    indent: '📦', ready: '📋', cancel: '❌', print: '🖨️', share: '📤',
  };
  return icons[id] || '•';
}
</script>
