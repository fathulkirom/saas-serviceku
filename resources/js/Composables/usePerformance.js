/**
 * ═══════════════════════════════════════════════════════════
 * usePerformance — Frontend Performance Optimization Composable
 * ═══════════════════════════════════════════════════════════
 * 
 * SPRINT 36D: Reusable performance utilities for Vue 3 components.
 * Debounce, throttle, lazy loading, memoization, virtual scroll helpers.
 * 
 * Usage:
 *   import { useDebounce, useThrottle, useLazyLoad } from '@/Composables/usePerformance';
 */

import { ref, computed, onMounted, onUnmounted, watch } from 'vue';

// ═══════════════════════════════════════════════════════════
// DEBOUNCE — Delays execution until pauses (search input, resize)
// ═══════════════════════════════════════════════════════════

export function useDebounce(fn, delay = 300) {
  let timer = null;
  return function (...args) {
    clearTimeout(timer);
    timer = setTimeout(() => fn.apply(this, args), delay);
  };
}

export function useDebounceRef(initialValue, delay = 300) {
  const immediate = ref(initialValue);
  const debounced = ref(initialValue);
  let timer = null;

  watch(immediate, (val) => {
    clearTimeout(timer);
    timer = setTimeout(() => { debounced.value = val; }, delay);
  });

  return { immediate, debounced };
}

// ═══════════════════════════════════════════════════════════
// THROTTLE — Limits execution rate (scroll, mousemove, resize)
// ═══════════════════════════════════════════════════════════

export function useThrottle(fn, limit = 100) {
  let inThrottle = false;
  return function (...args) {
    if (!inThrottle) {
      fn.apply(this, args);
      inThrottle = true;
      setTimeout(() => { inThrottle = false; }, limit);
    }
  };
}

// ═══════════════════════════════════════════════════════════
// LAZY LOAD — IntersectionObserver for below-fold content
// ═══════════════════════════════════════════════════════════

export function useLazyLoad(callback, options = {}) {
  const target = ref(null);
  const isVisible = ref(false);
  let observer = null;

  onMounted(() => {
    if (!target.value) return;
    observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting) {
          isVisible.value = true;
          callback?.();
          observer.disconnect();
        }
      },
      { rootMargin: options.rootMargin || '200px', threshold: options.threshold || 0 }
    );
    observer.observe(target.value);
  });

  onUnmounted(() => { observer?.disconnect(); });

  return { target, isVisible };
}

// ═══════════════════════════════════════════════════════════
// MEMO — Simple in-component memoization
// ═══════════════════════════════════════════════════════════

export function useMemo(fn, dependencies) {
  const cache = new Map();
  return (...args) => {
    const key = JSON.stringify(args);
    if (cache.has(key)) return cache.get(key);
    const result = fn(...args);
    cache.set(key, result);
    return result;
  };
}

// ═══════════════════════════════════════════════════════════
// RAF THROTTLE — requestAnimationFrame throttle for animations
// ═══════════════════════════════════════════════════════════

export function useRafThrottle(fn) {
  let rafId = null;
  return function (...args) {
    if (rafId) return;
    rafId = requestAnimationFrame(() => {
      fn.apply(this, args);
      rafId = null;
    });
  };
}

// ═══════════════════════════════════════════════════════════
// IDLE CALLBACK — Defer non-critical work
// ═══════════════════════════════════════════════════════════

export function useIdleCallback(fn, timeout = 2000) {
  if (typeof requestIdleCallback !== 'undefined') {
    requestIdleCallback(fn, { timeout });
  } else {
    setTimeout(fn, 1);
  }
}

export default {
  useDebounce,
  useDebounceRef,
  useThrottle,
  useLazyLoad,
  useMemo,
  useRafThrottle,
  useIdleCallback,
};
