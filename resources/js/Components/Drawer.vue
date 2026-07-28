<template>
  <Teleport to="body">
    <Transition name="drawer">
      <div v-if="open" class="fixed inset-0 z-[100] flex" :class="position === 'right' ? 'justify-end' : 'justify-start'">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="$emit('close')" />
        <div
          ref="drawerRef"
          class="relative h-full flex flex-col shadow-2xl border-l"
          :style="{ width, background: 'var(--bg-card)', borderColor: 'var(--border-color)' }"
          @keydown.escape="$emit('close')">
          <div class="flex items-center justify-between px-5 py-4 border-b flex-shrink-0" :style="{ borderColor: 'var(--border-light)' }">
            <h2 class="text-base font-bold" :style="{ color: 'var(--text-primary)' }">{{ title }}</h2>
            <button @click="$emit('close')" class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors"
              :style="{ color: 'var(--text-muted)' }">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
          <div class="flex-1 overflow-y-auto px-5 py-4">
            <slot />
          </div>
          <div v-if="$slots.footer" class="px-5 py-4 border-t flex-shrink-0" :style="{ borderColor: 'var(--border-light)' }">
            <slot name="footer" />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  open: { type: Boolean, default: false },
  position: { type: String, default: 'right' },
  width: { type: String, default: '448px' },
  title: { type: String, default: '' },
});

defineEmits(['close']);

const drawerRef = ref(null);

function handleKeydown(e) {
  if (e.key === 'Escape' && props.open) {
    e.preventDefault();
  }
}

watch(() => props.open, (val) => {
  if (val) {
    const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
    document.body.style.overflow = 'hidden';
    document.body.style.paddingRight = scrollbarWidth + 'px';
  } else {
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
  }
});

onMounted(() => {
  document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown);
  document.body.style.overflow = '';
});
</script>

<style scoped>
.drawer-enter-active, .drawer-leave-active {
  transition: opacity 0.2s ease;
}
.drawer-enter-from, .drawer-leave-to {
  opacity: 0;
}
.drawer-enter-active > div:last-child {
  transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.drawer-leave-active > div:last-child {
  transition: transform 0.2s ease;
}
.drawer-enter-from > div:last-child {
  transform: translateX(100%);
}
.drawer-leave-to > div:last-child {
  transform: translateX(100%);
}
</style>
