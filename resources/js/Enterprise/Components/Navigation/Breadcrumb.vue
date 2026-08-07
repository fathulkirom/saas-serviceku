<template>
  <nav aria-label="Breadcrumb" :class="extraClass">
    <ol class="flex items-center gap-1.5 flex-wrap">
      <li v-for="(item, i) in items" :key="i" class="flex items-center gap-1.5">
        <!-- Separator -->
        <svg
          v-if="i > 0"
          class="w-4 h-4 flex-shrink-0"
          :style="{ color: 'var(--text-muted)' }"
          fill="none" stroke="currentColor" viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>

        <!-- Link or Text -->
        <component
          :is="item.url ? 'Link' : 'span'"
          :href="item.url"
          :class="[
            'text-sm transition-colors truncate max-w-[200px]',
            item.url ? 'hover:text-indigo-600 cursor-pointer' : '',
            i === items.length - 1 ? 'font-semibold' : 'font-normal',
          ]"
          :style="{ color: i === items.length - 1 ? 'var(--text-primary)' : 'var(--text-muted)' }"
        >
          <!-- Home icon for first item -->
          <span v-if="i === 0 && homeIcon" class="flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1"/>
            </svg>
            {{ item.label }}
          </span>
          <span v-else>{{ item.label }}</span>
        </component>
      </li>
    </ol>
  </nav>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

/**
 * Enterprise Breadcrumb navigation.
 *
 * @example
 * <SkBreadcrumb :items="[
 *   { label: 'Dashboard', url: route('dashboard') },
 *   { label: 'Servis', url: route('services.index') },
 *   { label: 'Detail' },
 * ]" />
 */
defineProps({
  items: { type: Array, required: true },  // [{ label, url? }]
  homeIcon: { type: Boolean, default: true },
  extraClass: { type: String, default: '' },
});
</script>
