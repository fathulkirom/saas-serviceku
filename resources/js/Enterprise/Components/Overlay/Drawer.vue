<template>
  <Teleport to="body">
    <Transition name="sk-drawer">
      <div v-if="open" class="fixed inset-0 z-[100]" :class="positionClass">
        <!-- Backdrop -->
        <div
          class="fixed inset-0 bg-black/40 backdrop-blur-sm"
          @click="closeOnBackdrop && $emit('close')"
        />

        <!-- Panel -->
        <div
          ref="panelRef"
          class="sk-drawer-panel relative h-full flex flex-col shadow-2xl"
          :class="[
            position === 'right' ? 'sk-drawer-panel--right border-l' : '',
            position === 'left' ? 'sk-drawer-panel--left border-r' : '',
            position === 'bottom' ? 'sk-drawer-panel--bottom border-t' : '',
            fullscreen ? 'w-full' : '',
          ]"
          :style="panelStyle"
          @keydown.escape="closeOnEscape && $emit('close')"
        >
          <!-- Header -->
          <div
            v-if="!hideHeader"
            class="flex items-center justify-between px-5 py-4 border-b flex-shrink-0"
            :style="{ borderColor: 'var(--border-light)' }"
          >
            <div class="flex items-center gap-3 min-w-0">
              <div v-if="icon" class="w-8 h-8 rounded-lg flex items-center justify-center" :style="{ background: 'var(--bg-hover)' }">
                <span class="text-base" v-html="icon"></span>
              </div>
              <div class="min-w-0">
                <h2 class="text-base font-bold truncate" :style="{ color: 'var(--text-primary)' }">{{ title }}</h2>
                <p v-if="subtitle" class="sk-caption truncate">{{ subtitle }}</p>
              </div>
            </div>
            <button
              @click="$emit('close')"
              class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors hover:bg-zinc-100 flex-shrink-0"
              :style="{ color: 'var(--text-muted)' }"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Content -->
          <div class="flex-1 overflow-y-auto px-5 py-4">
            <slot />
          </div>

          <!-- Footer -->
          <div
            v-if="$slots.footer"
            class="px-5 py-4 border-t flex-shrink-0"
            :style="{ borderColor: 'var(--border-light)' }"
          >
            <slot name="footer" />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

/**
 * Enterprise Drawer — left, right, bottom, fullscreen.
 *
 * @example
 * <SkDrawer v-model:open="show" title="Detail Servis" position="right" width="600px">
 *   <p>Content here</p>
 *   <template #footer>
 *     <SkButton @click="show = false">Tutup</SkButton>
 *   </template>
 * </SkDrawer>
 */
const props = defineProps({
  open: { type: Boolean, default: false },
  position: { type: String, default: 'right' }, // left | right | bottom
  title: { type: String, default: '' },
  subtitle: { type: String, default: '' },
  icon: { type: String, default: '' },
  width: { type: String, default: '448px' },
  fullscreen: { type: Boolean, default: false },
  hideHeader: { type: Boolean, default: false },
  closeOnBackdrop: { type: Boolean, default: true },
  closeOnEscape: { type: Boolean, default: true },
});

defineEmits(['close']);

const panelRef = ref(null);

const positionClass = computed(() => {
  if (props.position === 'left') return 'justify-start';
  if (props.position === 'bottom') return 'items-end justify-center';
  return 'justify-end';
});

const panelStyle = computed(() => {
  const base = { background: 'var(--bg-card)', borderColor: 'var(--border-color)' };
  if (props.fullscreen || props.position === 'bottom') {
    return { ...base, width: '100%', maxHeight: props.position === 'bottom' ? '90vh' : '100vh' };
  }
  return { ...base, width: props.width };
});

// Lock body scroll
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
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && props.open && props.closeOnEscape) {
      // handled by @keydown.escape above
    }
  });
});

onUnmounted(() => {
  document.body.style.overflow = '';
  document.body.style.paddingRight = '';
});
</script>
