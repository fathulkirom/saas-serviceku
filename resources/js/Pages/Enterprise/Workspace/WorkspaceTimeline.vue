<template>
  <div class="relative">
    <!-- Timeline line -->
    <div class="absolute left-[15px] top-2 bottom-2 w-px" :style="{ background: 'var(--border-light)' }"></div>

    <div class="space-y-4">
      <div v-for="(entry, i) in entries" :key="i" class="flex gap-3 relative">
        <!-- Dot -->
        <div class="relative z-10 flex-shrink-0 mt-0.5">
          <div class="w-[11px] h-[11px] rounded-full border-2" :style="{ background: entry.color || '#6B7280', borderColor: entry.borderColor || '#E5E7EB' }"></div>
        </div>

        <!-- Content -->
        <div class="flex-1 min-w-0 pb-3">
          <div class="flex items-center gap-2 mb-0.5">
            <span class="text-xs font-bold" :style="{ color: 'var(--text-primary)' }">{{ entry.title }}</span>
            <span v-if="entry.user" class="text-[10px]" :style="{ color: 'var(--text-muted)' }">oleh {{ entry.user }}</span>
          </div>
          <p v-if="entry.description" class="sk-caption">{{ entry.description }}</p>
          <p class="text-[10px] mt-1" :style="{ color: 'var(--text-muted)' }">{{ entry.time }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
/**
 * Universal Timeline component — digunakan oleh semua workspace.
 * Props: entries[] = [{ title, description, time, color, borderColor, user }]
 */
defineProps({
  entries: { type: Array, default: () => [] },
});
</script>
