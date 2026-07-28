<template>
  <Teleport to="body">
    <div class="fixed top-4 right-4 z-[9999] flex flex-col gap-2 pointer-events-none" style="max-width: var(--toast-max-width);">
      <div v-for="toast in toasts" :key="toast.id"
        class="pointer-events-auto flex items-start gap-3 px-4 py-3 rounded-xl border shadow-lg transition-all duration-300"
        :class="toast.entering ? 'translate-x-0 opacity-100' : 'translate-x-full opacity-0'"
        :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
        <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"
          :style="{ background: toast.iconBg }">
          <span class="text-white text-xs" v-html="iconSvg(toast.type)" />
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-semibold" :style="{ color: 'var(--text-primary)' }">{{ toast.title }}</p>
          <p v-if="toast.message" class="text-xs mt-0.5" :style="{ color: 'var(--text-secondary)' }">{{ toast.message }}</p>
          <div class="mt-2 h-0.5 rounded-full overflow-hidden" :style="{ background: 'var(--bg-hover)' }">
            <div class="h-full rounded-full transition-all duration-[5000ms] linear" :style="{ width: toast.progress + '%', background: toast.color }" />
          </div>
        </div>
        <button @click="dismiss(toast.id)" class="flex-shrink-0 w-5 h-5 flex items-center justify-center rounded-full transition-colors" :style="{ color: 'var(--text-muted)' }">
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, onUnmounted } from 'vue';

const toasts = ref([]);
let counter = 0;

const icons = {
  success: '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>',
  error: '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>',
  warning: '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>',
  info: '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
};

const vars = {
  success: { bg: 'var(--success-bg)', bar: 'var(--success)' },
  error: { bg: 'var(--danger-bg)', bar: 'var(--danger)' },
  warning: { bg: 'var(--warning-bg)', bar: 'var(--warning)' },
  info: { bg: 'var(--info-bg)', bar: 'var(--info)' },
};

const titles = { success: 'Berhasil', error: 'Gagal', warning: 'Peringatan', info: 'Informasi' };

function iconSvg(type) { return icons[type] || icons.info; }

function add(type, message, title) {
  const id = ++counter;
  const config = vars[type] || vars.info;
  const toast = {
    id, type, message, title: title || titles[type] || 'Info',
    color: config.bar, iconBg: config.bg,
    progress: 100, entering: false,
  };
  toasts.value.push(toast);
  setTimeout(() => { toast.entering = true; }, 10);
  if (toasts.value.length > 5) toasts.value.shift();
  const duration = 5000;
  const start = Date.now();
  const interval = setInterval(() => {
    const elapsed = Date.now() - start;
    toast.progress = Math.max(0, 100 - (elapsed / duration) * 100);
    if (elapsed >= duration) {
      clearInterval(interval);
      dismiss(id);
    }
  }, 50);
}

function dismiss(id) {
  const idx = toasts.value.findIndex(t => t.id === id);
  if (idx > -1) {
    toasts.value[idx].entering = false;
    setTimeout(() => { toasts.value = toasts.value.filter(t => t.id !== id); }, 300);
  }
}

defineExpose({ add, dismiss });
</script>
