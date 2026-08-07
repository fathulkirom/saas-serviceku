<template>
  <div class="relative">
    <!-- Refresh indicator -->
    <div class="flex items-center justify-between mb-3">
      <h3 class="sk-label">{{ title }}</h3>
      <div class="flex items-center gap-2">
        <span v-if="lastUpdated" class="text-[10px]" :style="{ color: 'var(--text-muted)' }">
          {{ lastUpdatedText }}
        </span>
        <button
          @click="refresh"
          :disabled="isRefreshing"
          class="w-7 h-7 rounded-lg flex items-center justify-center transition-all"
          :class="{ 'animate-spin': isRefreshing }"
          :style="{ color: 'var(--text-muted)', background: isRefreshing ? 'var(--bg-hover)' : 'transparent' }"
          title="Refresh widget"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Content -->
    <div :class="{ 'opacity-60 pointer-events-none': isRefreshing }">
      <slot :refreshing="isRefreshing" />
    </div>

    <!-- Refresh overlay -->
    <div v-if="isRefreshing" class="absolute inset-0 flex items-center justify-center z-10">
      <div class="sk-animate-spin w-5 h-5 border-2 border-indigo-400 border-t-transparent rounded-full"></div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onUnmounted } from 'vue';

/**
 * Widget Refresh — manual refresh + auto-refresh timer.
 *
 * Usage:
 * <WidgetRefresh title="Servis" :autoRefresh="30" @refresh="fetchData">
 *   <SomeWidget />
 * </WidgetRefresh>
 */
const props = defineProps({
  title: { type: String, default: '' },
  autoRefresh: { type: Number, default: 0 },  // seconds, 0 = disabled
});

const emit = defineEmits(['refresh']);

const isRefreshing = ref(false);
const lastUpdated = ref(Date.now());
let timer = null;

const lastUpdatedText = computed(() => {
  const diff = Math.floor((Date.now() - lastUpdated.value) / 1000);
  if (diff < 60) return `${diff}s lalu`;
  return `${Math.floor(diff / 60)}m lalu`;
});

function refresh() {
  isRefreshing.value = true;
  emit('refresh');
  // Simulate refresh delay
  setTimeout(() => {
    isRefreshing.value = false;
    lastUpdated.value = Date.now();
  }, 600);
}

// Auto-refresh timer
if (props.autoRefresh > 0) {
  timer = setInterval(() => {
    refresh();
  }, props.autoRefresh * 1000);
}

onUnmounted(() => {
  if (timer) clearInterval(timer);
});

// Update lastUpdated text every 30s
const textTimer = setInterval(() => {
  // trigger reactivity
  lastUpdated.value = lastUpdated.value;
}, 30000);

onUnmounted(() => {
  clearInterval(textTimer);
});
</script>
