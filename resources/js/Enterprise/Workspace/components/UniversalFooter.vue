<template>
  <!-- UniversalWorkspaceFooter — Audit info, refresh status, record metadata -->
  <footer class="workspace-footer flex items-center justify-between px-4 py-2 text-[10px] border-t"
    :style="{ background: 'var(--bg-surface)', borderColor: 'var(--border-light)', color: 'var(--text-muted)' }">
    <div class="flex items-center gap-4">
      <span v-if="meta.created_at">Created {{ formatDate(meta.created_at) }}</span>
      <span v-if="meta.updated_at" class="flex items-center gap-1">
        <span class="w-1.5 h-1.5 rounded-full" :style="{ background: 'var(--success)' }"></span>
        Updated {{ formatDate(meta.updated_at) }}
      </span>
      <span v-if="meta.version" class="font-mono">v{{ meta.version }}</span>
    </div>
    <div class="flex items-center gap-3">
      <span v-if="meta.record_count != null" class="font-bold" :style="{ color: 'var(--text-primary)' }">{{ meta.record_count }} records</span>
      <span v-if="autoRefresh" class="flex items-center gap-1">
        <span class="w-1.5 h-1.5 rounded-full animate-pulse" :style="{ background: 'var(--info)' }"></span>
        Auto-refresh {{ autoRefresh }}s
      </span>
      <span v-if="lastSync" :style="{ color: 'var(--text-muted)' }">Synced {{ formatTime(lastSync) }}</span>
      <span v-if="meta.created_by">by {{ meta.created_by }}</span>
    </div>
  </footer>
</template>

<script setup>
defineProps({
  meta: { type: Object, default: () => ({}) },
  autoRefresh: { type: Number, default: 0 },
  lastSync: { type: String, default: '' },
});

function formatDate(v) { if (!v) return ''; return new Date(v).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }); }
function formatTime(v) { if (!v) return ''; return new Date(v).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }); }
</script>
