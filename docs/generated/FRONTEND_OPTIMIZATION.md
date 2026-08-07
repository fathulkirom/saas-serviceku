# Frontend Optimization — Sprint 36D

> Vue 3 + Inertia frontend performance patterns.

---

## ⚡ Patterns Implemented

### Code Splitting (Vite)
```js
// vite.config.js — manualChunks
rollupOptions: {
  output: {
    manualChunks: {
      vendor: ['vue', '@inertiajs/vue3', 'axios'],
      enterprise: ['@/Enterprise/Components/Cards/Card.vue', '@/Enterprise/Components/Table/DataTable.vue'],
      charts: ['chart.js'],
    },
  },
}
```

### Lazy Loading (Components)
```js
// Lazy-load tab content
const DiagnosisTab = defineAsyncComponent(() => import('./sections/Diagnosis.vue'));

// IntersectionObserver-based lazy loading
const { target, isVisible } = useLazyLoad(() => { loadHeavyContent(); });
```

### Debounce & Throttle
```js
// Search input — debounce 300ms
const debouncedSearch = useDebounce(searchHandler, 300);

// Scroll handler — throttle 100ms
const throttledScroll = useThrottle(scrollHandler, 100);
```

### Memoization
```js
// Expensive computed that depends on stable data
const expensiveResult = computed(() => {
  // This only recomputes when dependencies change
  return heavyCalculation(props.data);
});
```

### Image Optimization
```html
<!-- Lazy loading -->
<img loading="lazy" src="..." alt="..." />

<!-- Responsive images -->
<img srcset="thumb.webp 200w, medium.webp 600w, full.webp 1200w" sizes="(max-width: 600px) 200px, 600px" />
```

---

## 📊 Inertia Optimization

| Technique | Usage |
|-----------|-------|
| Partial Reload | `router.reload({ only: ['workspace'] })` |
| Preserve Scroll | `preserveScroll: true` |
| Preserve State | `preserveState: true` |
| Lazy Props | `Inertia::lazy(fn() => ...)` |
| Deferred Props | `Inertia::defer(fn() => ...)` |

---

*Frontend Optimization — Sprint 36D*
