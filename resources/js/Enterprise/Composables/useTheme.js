import { ref, watch } from 'vue';

/**
 * Enterprise Theme Manager.
 *
 * Usage:
 *   const { theme, toggle, setTheme, isDark } = useTheme()
 *
 * Supports: 'light' | 'dark' | 'system'
 */
export function useTheme() {
  const stored = typeof localStorage !== 'undefined' ? localStorage.getItem('sk-theme') : null;
  const theme = ref(stored || 'system');

  const isDark = computed(() => {
    if (theme.value === 'dark') return true;
    if (theme.value === 'light') return false;
    // system
    return typeof window !== 'undefined' && window.matchMedia('(prefers-color-scheme: dark)').matches;
  });

  function applyTheme() {
    const root = document.documentElement;
    if (isDark.value) {
      root.classList.add('dark');
    } else {
      root.classList.remove('dark');
    }
  }

  function setTheme(t) {
    theme.value = t;
    localStorage.setItem('sk-theme', t);
    applyTheme();
  }

  function toggle() {
    setTheme(isDark.value ? 'light' : 'dark');
  }

  // Watch system preference
  if (typeof window !== 'undefined') {
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
      if (theme.value === 'system') applyTheme();
    });
  }

  watch(isDark, applyTheme);
  applyTheme();

  return { theme, isDark, setTheme, toggle };
}

import { computed } from 'vue';
