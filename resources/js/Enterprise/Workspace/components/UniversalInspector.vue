<template>
  <!-- UniversalWorkspaceInspector — Auto-generated audit/metadata panel from registry -->
  <aside class="workspace-inspector flex flex-col h-full overflow-y-auto text-xs" :style="{ background: 'var(--bg-surface)', borderLeft: '1px solid var(--border-light)', width: '260px', minWidth: '260px' }">
    <div class="p-4 border-b flex items-center justify-between" :style="{ borderColor: 'var(--border-light)' }">
      <h3 class="text-sm font-bold" :style="{ color: 'var(--text-primary)' }">Inspector</h3>
      <button @click="$emit('close')" class="text-xs px-2 py-1 rounded hover:opacity-80" :style="{ color: 'var(--text-muted)' }">✕</button>
    </div>

    <div class="p-3 space-y-4">
      <!-- Metadata Section -->
      <section>
        <h4 class="text-[10px] uppercase tracking-wider mb-1.5" :style="{ color: 'var(--text-muted)' }">Metadata</h4>
        <div class="space-y-1">
          <div v-if="meta.uuid" class="flex justify-between"><span :style="{ color: 'var(--text-muted)' }">UUID</span><span class="font-mono text-[10px] truncate ml-2 max-w-[140px]" :style="{ color: 'var(--text-primary)' }">{{ meta.uuid }}</span></div>
          <div v-if="meta.module" class="flex justify-between"><span :style="{ color: 'var(--text-muted)' }">Module</span><span class="font-bold" :style="{ color: 'var(--primary)' }">{{ meta.module }}</span></div>
          <div v-if="meta.branch" class="flex justify-between"><span :style="{ color: 'var(--text-muted)' }">Branch</span><span :style="{ color: 'var(--text-primary)' }">{{ meta.branch }}</span></div>
          <div v-if="meta.tenant" class="flex justify-between"><span :style="{ color: 'var(--text-muted)' }">Tenant</span><span class="font-mono text-[10px] truncate ml-2 max-w-[120px]" :style="{ color: 'var(--text-muted)' }">{{ meta.tenant }}</span></div>
          <div v-if="meta.version" class="flex justify-between"><span :style="{ color: 'var(--text-muted)' }">Version</span><span class="font-bold" :style="{ color: 'var(--text-primary)' }">v{{ meta.version }}</span></div>
        </div>
      </section>

      <!-- Audit Section -->
      <section>
        <h4 class="text-[10px] uppercase tracking-wider mb-1.5" :style="{ color: 'var(--text-muted)' }">Audit</h4>
        <div class="space-y-1">
          <div class="flex justify-between"><span :style="{ color: 'var(--text-muted)' }">Created</span><span :style="{ color: 'var(--text-primary)' }">{{ formatDate(meta.created_at) }}</span></div>
          <div class="flex justify-between"><span :style="{ color: 'var(--text-muted)' }">Created By</span><span :style="{ color: 'var(--text-primary)' }">{{ meta.created_by || '-' }}</span></div>
          <div class="flex justify-between"><span :style="{ color: 'var(--text-muted)' }">Updated</span><span :style="{ color: 'var(--text-primary)' }">{{ formatDate(meta.updated_at) }}</span></div>
          <div class="flex justify-between"><span :style="{ color: 'var(--text-muted)' }">Updated By</span><span :style="{ color: 'var(--text-primary)' }">{{ meta.updated_by || '-' }}</span></div>
        </div>
      </section>

      <!-- Workflow Section -->
      <section v-if="meta.workflow">
        <h4 class="text-[10px] uppercase tracking-wider mb-1.5" :style="{ color: 'var(--text-muted)' }">Workflow</h4>
        <div class="space-y-1">
          <div class="flex justify-between"><span :style="{ color: 'var(--text-muted)' }">Status</span><span class="font-bold px-1.5 py-0.5 rounded text-[10px]" :style="{ background: 'var(--success-soft)', color: 'var(--success-text)' }">{{ meta.workflow.status || '-' }}</span></div>
          <div class="flex justify-between"><span :style="{ color: 'var(--text-muted)' }">Current Owner</span><span :style="{ color: 'var(--text-primary)' }">{{ meta.workflow.owner || '-' }}</span></div>
          <div class="flex justify-between"><span :style="{ color: 'var(--text-muted)' }">Next Step</span><span :style="{ color: 'var(--text-primary)' }">{{ meta.workflow.next_step || '-' }}</span></div>
        </div>
      </section>

      <!-- Access Section -->
      <section>
        <h4 class="text-[10px] uppercase tracking-wider mb-1.5" :style="{ color: 'var(--text-muted)' }">Access Control</h4>
        <div class="space-y-1">
          <div class="flex justify-between"><span :style="{ color: 'var(--text-muted)' }">Permission</span><span class="text-[10px] font-bold" :style="{ color: meta.permission ? 'var(--success)' : 'var(--danger)' }">{{ meta.permission ? '✅ Granted' : '❌ Denied' }}</span></div>
          <div class="flex justify-between"><span :style="{ color: 'var(--text-muted)' }">Feature</span><span class="text-[10px] font-bold" :style="{ color: meta.feature_active ? 'var(--success)' : 'var(--danger)' }">{{ meta.feature_active ? '✅ Active' : '❌ Inactive' }}</span></div>
          <div v-if="meta.features && meta.features.length" class="flex flex-wrap gap-1 mt-1">
            <span v-for="f in meta.features" :key="f" class="text-[9px] px-1.5 py-0.5 rounded" :style="{ background: 'var(--bg-hover)', color: 'var(--text-muted)' }">{{ f }}</span>
          </div>
        </div>
      </section>
    </div>
  </aside>
</template>

<script setup>
defineProps({
  meta: { type: Object, default: () => ({}) },
});
defineEmits(['close']);

function formatDate(v) { if (!v) return '-'; return new Date(v).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }); }
</script>
