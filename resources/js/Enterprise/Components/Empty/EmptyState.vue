<template>
  <div :class="classes">
    <!-- Icon -->
    <div
      class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4"
      :style="{ background: iconBg }"
    >
      <span class="w-8 h-8" :style="{ color: iconColor }" v-html="currentIcon"></span>
    </div>

    <!-- Title -->
    <p class="sk-label mb-1">{{ title }}</p>

    <!-- Description -->
    <p v-if="description" class="sk-body-xs mb-6 text-center max-w-xs">{{ description }}</p>

    <!-- Action Button -->
    <slot name="action">
      <Link
        v-if="actionUrl"
        :href="actionUrl"
        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 shadow-sm hover:shadow-md"
        :style="{ background: 'var(--primary)' }"
      >
        {{ actionLabel }}
      </Link>
      <button
        v-else-if="actionLabel"
        @click="$emit('action')"
        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 shadow-sm hover:shadow-md"
        :style="{ background: 'var(--primary)' }"
      >
        {{ actionLabel }}
      </button>
    </slot>

    <!-- Additional content slot -->
    <slot />
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

/**
 * Enterprise Empty State — various empty/error states.
 *
 * Variants:
 *   empty     — No data (default)
 *   search    — No search results
 *   error     — Error state
 *   offline   — Connection lost
 *   lock      — No permission
 *
 * @example
 * <SkEmptyState variant="search" title="Tidak ditemukan" description="Coba kata kunci lain" />
 * <SkEmptyState variant="error" title="Gagal memuat" actionLabel="Coba Lagi" @action="retry" />
 */
const props = defineProps({
  variant: { type: String, default: 'empty' }, // empty | search | error | offline | lock
  title: { type: String, default: 'Belum ada data' },
  description: { type: String, default: '' },
  actionLabel: { type: String, default: '' },
  actionUrl: { type: String, default: '' },
  extraClass: { type: String, default: '' },
});

defineEmits(['action']);

const icons = {
  empty: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>`,
  search: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>`,
  error: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>`,
  offline: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 5.636a9 9 0 010 12.728m-2.829-9.9a5 5 0 010 7.072M9.172 16.242a4 4 0 01-.707-3.536m-2.83-7.07a9 9 0 00.707 13.435M12 12h.01"/></svg>`,
  lock: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>`,
};

const iconColors = {
  empty: 'var(--text-muted)',
  search: 'var(--text-muted)',
  error: 'var(--danger)',
  offline: 'var(--warning)',
  lock: 'var(--text-muted)',
};

const iconBgs = {
  empty: 'var(--bg-hover)',
  search: 'var(--bg-hover)',
  error: 'var(--danger-soft)',
  offline: 'var(--warning-soft)',
  lock: 'var(--bg-hover)',
};

const defaultTitles = {
  empty: 'Belum ada data',
  search: 'Tidak ditemukan',
  error: 'Terjadi kesalahan',
  offline: 'Tidak terhubung',
  lock: 'Akses terbatas',
};

const defaultDescriptions = {
  empty: 'Data akan muncul di sini setelah tersedia.',
  search: 'Coba gunakan kata kunci yang berbeda.',
  error: 'Silakan coba lagi beberapa saat.',
  offline: 'Periksa koneksi internet Anda.',
  lock: 'Anda tidak memiliki izin untuk mengakses halaman ini.',
};

const currentIcon = computed(() => icons[props.variant] || icons.empty);
const iconColor = computed(() => iconColors[props.variant] || iconColors.empty);
const iconBg = computed(() => iconBgs[props.variant] || iconBgs.empty);

const title = computed(() => props.title || defaultTitles[props.variant] || 'Belum ada data');
const description = computed(() => props.description || defaultDescriptions[props.variant] || '');

const classes = computed(() => [
  'flex flex-col items-center justify-center py-16 px-4',
  props.extraClass,
].filter(Boolean).join(' '));
</script>
