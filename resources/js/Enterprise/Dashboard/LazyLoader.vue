<template>
  <div ref="rootRef" class="min-h-[60px]">
    <slot v-if="isVisible" />
    <div v-else class="flex items-center justify-center py-8">
      <div class="sk-animate-spin w-5 h-5 border-2 border-indigo-400 border-t-transparent rounded-full"></div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

/**
 * LazyLoader — hanya render slot saat element masuk viewport.
 * Mengurangi initial render cost untuk widget di bawah fold.
 *
 * Usage:
 * <LazyLoader>
 *   <HeavyWidget />
 * </LazyLoader>
 */
const rootRef = ref(null);
const isVisible = ref(false);
let observer = null;

onMounted(() => {
  if (!rootRef.value) return;
  observer = new IntersectionObserver(
    ([entry]) => {
      if (entry.isIntersecting) {
        isVisible.value = true;
        observer?.disconnect();
      }
    },
    { rootMargin: '200px' } // pre-load 200px before visible
  );
  observer.observe(rootRef.value);
});

onUnmounted(() => {
  observer?.disconnect();
});
</script>
