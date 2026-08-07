<template>
  <Teleport to="body">
    <Transition name="sk-modal">
      <div
        v-if="open"
        class="fixed inset-0 z-50 flex items-center justify-center"
        :class="scrollable ? 'overflow-y-auto py-8' : ''"
      >
        <!-- Backdrop -->
        <div
          class="fixed inset-0 bg-black/40 backdrop-blur-sm"
          @click="closeOnBackdrop && $emit('close')"
        />

        <!-- Panel -->
        <div
          ref="panelRef"
          class="sk-modal-panel relative rounded-2xl shadow-2xl w-full border flex flex-col max-h-[90vh]"
          :class="sizeClass"
          :style="panelStyle"
        >
          <!-- Header -->
          <div
            v-if="!hideHeader"
            class="flex items-center justify-between px-5 py-4 border-b flex-shrink-0"
            :style="{ borderColor: 'var(--border-light)' }"
          >
            <div class="flex items-center gap-3 min-w-0">
              <!-- Danger icon -->
              <div v-if="variant === 'danger'" class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
              </div>
              <div class="min-w-0">
                <h2 class="text-base font-bold" :style="{ color: 'var(--text-primary)' }">{{ title }}</h2>
                <p v-if="subtitle" class="sk-caption">{{ subtitle }}</p>
              </div>
            </div>
            <button
              v-if="!hideClose"
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
            class="px-5 py-4 border-t flex items-center gap-3 flex-shrink-0"
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
import { ref, computed, watch, onUnmounted } from 'vue';

/**
 * Enterprise Modal — confirmation, danger, wizard, large, fullscreen.
 *
 * @example
 * <SkModal v-model:open="show" title="Konfirmasi" variant="danger">
 *   <p>Apakah Anda yakin ingin menghapus data ini?</p>
 *   <template #footer>
 *     <SkButton variant="secondary" @click="show=false">Batal</SkButton>
 *     <SkButton variant="danger" @click="confirm">Hapus</SkButton>
 *   </template>
 * </SkModal>
 */
const props = defineProps({
  open: { type: Boolean, default: false },
  title: { type: String, default: '' },
  subtitle: { type: String, default: '' },
  variant: { type: String, default: 'default' }, // default | danger | wizard
  size: { type: String, default: 'md' },         // sm | md | lg | xl | fullscreen
  hideHeader: { type: Boolean, default: false },
  hideClose: { type: Boolean, default: false },
  scrollable: { type: Boolean, default: false },
  closeOnBackdrop: { type: Boolean, default: true },
  closeOnEscape: { type: Boolean, default: true },
});

defineEmits(['close']);

const panelRef = ref(null);

const sizeClasses = {
  sm: 'max-w-sm mx-3',
  md: 'max-w-md mx-3',
  lg: 'max-w-lg mx-3',
  xl: 'max-w-xl mx-3',
  fullscreen: 'max-w-[95vw] mx-3',
};

const sizeClass = computed(() => sizeClasses[props.size] || sizeClasses.md);

const panelStyle = computed(() => ({
  background: 'var(--bg-card)',
  borderColor: props.variant === 'danger' ? 'var(--danger-soft-border)' : 'var(--border-color)',
}));

// Lock body scroll
watch(() => props.open, (val) => {
  if (val) {
    document.body.style.overflow = 'hidden';
  } else {
    document.body.style.overflow = '';
  }
});

onUnmounted(() => {
  document.body.style.overflow = '';
});

// Close on Escape
watch(() => props.open, (val) => {
  if (val && props.closeOnEscape) {
    const handler = (e) => {
      if (e.key === 'Escape') {
        // Trigger close via the component using v-model:open
      }
    };
    document.addEventListener('keydown', handler);
    return () => document.removeEventListener('keydown', handler);
  }
});
</script>
