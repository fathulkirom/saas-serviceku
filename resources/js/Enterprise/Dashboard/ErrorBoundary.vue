<template>
  <div>
    <slot v-if="!hasError" />
    <div v-else class="rounded-2xl border p-6 text-center" :style="{ borderColor: 'var(--danger-soft-border)', background: 'var(--danger-soft)' }">
      <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-3">
        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
        </svg>
      </div>
      <p class="text-sm font-semibold text-red-700 mb-1">Widget Error</p>
      <p class="text-xs text-red-600 mb-3">{{ errorMessage }}</p>
      <button
        @click="retry"
        class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-red-600 text-white hover:bg-red-700 transition-colors"
      >
        Coba Lagi
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onErrorCaptured } from 'vue';

/**
 * Error Boundary — menangkap error dari child components.
 * Jika widget gagal, dashboard tidak ikut crash.
 *
 * Usage:
 * <ErrorBoundary>
 *   <SomeWidget />
 * </ErrorBoundary>
 */
const hasError = ref(false);
const errorMessage = ref('');

onErrorCaptured((err, instance, info) => {
  hasError.value = true;
  errorMessage.value = err?.message || 'Terjadi kesalahan pada widget ini.';
  console.warn('[ErrorBoundary] Caught error:', err?.message, info);
  return false; // prevent propagation
});

function retry() {
  hasError.value = false;
  errorMessage.value = '';
}
</script>
