<template>
  <div class="mb-6 sm:mb-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div class="flex-1 min-w-0">
        <nav v-if="breadcrumbs?.length" class="flex items-center gap-1.5 mb-2 text-xs" :style="{ color: 'var(--text-muted)' }">
          <template v-for="(crumb, i) in breadcrumbs" :key="i">
            <Link
              v-if="crumb.url"
              :href="crumb.url"
              class="hover:underline transition-colors"
              :style="{ color: 'var(--accent-primary)' }"
            >
              {{ crumb.label }}
            </Link>
            <span v-else>{{ crumb.label }}</span>
            <svg v-if="i < breadcrumbs.length - 1" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </template>
        </nav>
        <h1 class="text-xl sm:text-2xl font-bold tracking-tight" :style="{ color: 'var(--text-primary)' }">
          {{ title }}
        </h1>
        <p v-if="subtitle" class="text-sm mt-1" :style="{ color: 'var(--text-secondary)' }">
          {{ subtitle }}
        </p>
      </div>
      <div v-if="$slots.default" class="flex items-center gap-2 flex-shrink-0">
        <slot />
      </div>
    </div>
    <div v-if="$slots.tabs" class="mt-6 flex gap-1 p-1 rounded-xl border" :style="{ background: 'var(--bg-secondary)', borderColor: 'var(--border-color)' }">
      <slot name="tabs" />
    </div>
    <div v-if="$slots.filters" class="mt-4 flex flex-wrap items-center gap-3">
      <slot name="filters" />
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
  breadcrumbs: { type: Array, default: () => [] },
});
</script>
