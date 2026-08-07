<template>
  <div class="border-b px-4 sm:px-6 lg:px-8 py-3 flex items-center gap-3 flex-wrap"
    :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-light)' }">
    
    <!-- Back button -->
    <button @click="$router?.back?.() || history.back()" class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
      :style="{ color: 'var(--text-muted)' }">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </button>

    <!-- Title -->
    <h1 class="text-base font-bold flex-shrink-0" :style="{ color: 'var(--text-primary)' }">{{ title }}</h1>

    <!-- Dirty indicator -->
    <span v-if="isDirty" class="text-[10px] font-medium px-2 py-0.5 rounded-full flex-shrink-0"
      :style="{ background: 'var(--warning-soft)', color: 'var(--warning-text)' }">
      {{ dirtyCount }} perubahan
    </span>

    <!-- Last saved -->
    <span v-if="lastSavedAt && !isDirty" class="text-[10px] flex-shrink-0 hidden sm:inline" :style="{ color: 'var(--text-muted)' }">
      Tersimpan {{ timeAgo }}
    </span>

    <div class="flex-1"></div>

    <!-- Undo / Redo -->
    <button @click="$emit('undo')" :disabled="!canUndo" class="w-7 h-7 rounded flex items-center justify-center disabled:opacity-30"
      :style="{ color: 'var(--text-muted)' }" title="Undo (Ctrl+Z)">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
    </button>
    <button @click="$emit('redo')" :disabled="!canRedo" class="w-7 h-7 rounded flex items-center justify-center disabled:opacity-30"
      :style="{ color: 'var(--text-muted)' }" title="Redo (Ctrl+Y)">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10H11a8 8 0 00-8 8v2m18-10l-6 6m6-6l-6-6"/></svg>
    </button>

    <div class="w-px h-5 mx-1" :style="{ background: 'var(--border-light)' }"></div>

    <!-- Action Buttons -->
    <button
      v-for="action in actions"
      :key="action.id"
      @click="$emit('action', action.id)"
      :disabled="isSubmitting"
      class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all disabled:opacity-50 flex items-center gap-1.5"
      :style="actionStyle(action)"
    >
      <div v-if="isSubmitting && action.id === 'save'" class="sk-animate-spin w-3 h-3 border-2 border-current border-t-transparent rounded-full"></div>
      <span v-else-if="action.icon">{{ action.icon }}</span>
      <span>{{ action.label }}</span>
      <kbd v-if="action.shortcut" class="text-[9px] px-1 py-0.5 rounded ml-0.5 hidden sm:inline" :style="{ background: 'var(--bg-hover)' }">{{ action.shortcut }}</kbd>
    </button>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  title: { type: String, default: 'Form' },
  actions: { type: Array, default: () => [] },
  isDirty: { type: Boolean, default: false },
  dirtyCount: { type: Number, default: 0 },
  isSubmitting: { type: Boolean, default: false },
  canUndo: { type: Boolean, default: false },
  canRedo: { type: Boolean, default: false },
  lastSavedAt: { type: Date, default: null },
});

defineEmits(['action', 'undo', 'redo']);

function actionStyle(action) {
  switch (action.variant) {
    case 'danger': return { background: 'var(--danger)', color: '#fff' };
    case 'success': return { background: 'var(--success)', color: '#fff' };
    case 'secondary': return { background: 'var(--bg-hover)', color: 'var(--text-secondary)', border: '1px solid var(--border-color)' };
    case 'outline': return { color: 'var(--text-secondary)', border: '1px solid var(--border-color)' };
    default: return { background: 'var(--primary)', color: '#fff' };
  }
}

const timeAgo = computed(() => {
  if (!props.lastSavedAt) return '';
  const diff = Math.floor((Date.now() - props.lastSavedAt.getTime()) / 1000);
  if (diff < 60) return `${diff}s lalu`;
  return `${Math.floor(diff / 60)}m lalu`;
});
</script>
