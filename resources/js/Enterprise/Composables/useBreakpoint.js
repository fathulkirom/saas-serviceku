import { ref, onMounted, onUnmounted } from 'vue';

/**
 * Enterprise Breakpoint Detection.
 *
 * Usage:
 *   const { breakpoint, isMobile, isTablet, isDesktop } = useBreakpoint()
 *
 * Breakpoints (Tailwind):
 *   sm: 640, md: 768, lg: 1024, xl: 1280, 2xl: 1536
 */
export function useBreakpoint() {
  const width = ref(typeof window !== 'undefined' ? window.innerWidth : 1024);

  const breakpoint = computed(() => {
    if (width.value >= 1536) return '2xl';
    if (width.value >= 1280) return 'xl';
    if (width.value >= 1024) return 'lg';
    if (width.value >= 768) return 'md';
    if (width.value >= 640) return 'sm';
    return 'xs';
  });

  const isMobile = computed(() => width.value < 768);
  const isTablet = computed(() => width.value >= 768 && width.value < 1024);
  const isDesktop = computed(() => width.value >= 1024);
  const isTouch = computed(() => typeof window !== 'undefined' && ('ontouchstart' in window || navigator.maxTouchPoints > 0));

  function onResize() {
    width.value = window.innerWidth;
  }

  onMounted(() => window.addEventListener('resize', onResize));
  onUnmounted(() => window.removeEventListener('resize', onResize));

  return { width, breakpoint, isMobile, isTablet, isDesktop, isTouch };
}

import { computed } from 'vue';
