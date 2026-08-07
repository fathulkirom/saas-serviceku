<template>
  <div class="border-t px-4 sm:px-6 lg:px-8 py-2.5 flex items-center justify-between"
    :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-light)' }">
    <span class="text-[11px]" :style="{ color: 'var(--text-muted)' }">
      <span v-if="isDirty">⚠ Ada perubahan belum tersimpan</span>
      <span v-else>✅ Semua perubahan tersimpan</span>
    </span>

    <div class="flex items-center gap-2">
      <button
        v-for="action in actions"
        :key="action.id"
        @click="$emit('action', action.id)"
        :disabled="isSubmitting"
        class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all disabled:opacity-50"
        :style="{ background: 'var(--primary)', color: '#fff' }"
      >
        {{ action.label }}
      </button>
    </div>
  </div>
</template>

<script setup>
defineProps({
  actions: { type: Array, default: () => [] },
  isDirty: { type: Boolean, default: false },
  isSubmitting: { type: Boolean, default: false },
});

defineEmits(['action']);
</script>
