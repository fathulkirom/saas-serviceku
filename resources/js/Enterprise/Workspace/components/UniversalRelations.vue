<template>
  <!-- UniversalWorkspaceRelations — Cross-module relationship graph display -->
  <div class="workspace-relations">
    <div v-if="!links || !links.length" class="py-4 text-center">
      <p class="text-xs" :style="{ color: 'var(--text-muted)' }">No related records</p>
    </div>
    <div v-else class="flex flex-wrap items-center gap-1.5 text-xs">
      <template v-for="(link, i) in links" :key="link.key || i">
        <!-- Connector arrow -->
        <span v-if="i > 0" class="text-[10px]" :style="{ color: 'var(--text-muted)' }">→</span>
        <!-- Link chip -->
        <div class="flex items-center gap-1 px-2 py-1 rounded-lg cursor-pointer hover:opacity-80 transition"
          :style="{ background: link.active ? 'var(--primary-soft)' : 'var(--bg-hover)' }"
          @click="$emit('navigate', link)">
          <span>{{ link.icon || '📋' }}</span>
          <span class="font-medium" :style="{ color: link.active ? 'var(--primary)' : 'var(--text-primary)' }">{{ link.label }}</span>
          <span v-if="link.status" class="text-[9px] font-bold px-1 py-0.5 rounded-full"
            :style="statusStyle(link.status)">{{ link.status }}</span>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
defineProps({
  links: { type: Array, default: () => [] },
});
defineEmits(['navigate']);

function statusStyle(s) {
  const colors = {
    active: { background: 'var(--info-soft)', color: 'var(--info-text)' },
    completed: { background: 'var(--success-soft)', color: 'var(--success-text)' },
    pending: { background: 'var(--warning-soft)', color: 'var(--warning-text)' },
    cancelled: { background: 'var(--danger-soft)', color: 'var(--danger-text)' },
    void: { background: 'var(--danger-soft)', color: 'var(--danger-text)' },
  };
  return colors[s] || { background: 'var(--bg-hover)', color: 'var(--text-muted)' };
}
</script>
