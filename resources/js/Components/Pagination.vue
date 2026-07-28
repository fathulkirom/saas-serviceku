<template>
  <div v-if="meta?.total > perPage" class="flex items-center justify-between">
    <span class="text-xs" style="color: var(--text-muted);">
      Menampilkan {{ meta?.from ?? 0 }} - {{ meta?.to ?? 0 }} dari {{ meta?.total ?? 0 }}
    </span>
    <div class="flex gap-1">
      <component
        :is="link.url ? 'button' : 'span'"
        v-for="link in meta?.links"
        :key="link.label"
        v-bind="link.url ? { onClick: () => navigate(link.url) } : {}"
        class="inline-flex items-center justify-center min-w-[30px] h-7 px-1.5 rounded-lg text-xs font-bold transition-all"
        :class="getLinkClass(link)"
        :style="link.active ? { background: 'var(--accent-primary)', color: '#fff' } : {}"
      >
        <span v-html="link.label" />
      </component>
    </div>
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3';

const props = defineProps({
  meta: { type: Object, default: () => ({ data: [], links: [], from: 0, to: 0, total: 0 }) },
  perPage: { type: Number, default: 1 },
});

function navigate(url) {
  router.get(url, {}, { preserveState: true });
}

function getLinkClass(link) {
  if (link.active) return 'text-white shadow-sm border-transparent';
  if (link.url) return 'border';
  return 'cursor-not-allowed opacity-40';
}
</script>
