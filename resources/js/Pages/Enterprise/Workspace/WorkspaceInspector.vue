<template>
  <div class="p-4 space-y-4">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <h3 class="sk-label">Inspector</h3>
      <button @click="$emit('close')" class="w-7 h-7 rounded-lg flex items-center justify-center" :style="{ color: 'var(--text-muted)' }">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>

    <!-- Sections from Registry -->
    <div v-for="section in sections" :key="section.id" class="border-b pb-3" :style="{ borderColor: 'var(--border-light)' }">
      <h4 class="sk-section-title mb-2">{{ section.icon }} {{ section.label }}</h4>
      <component
        :is="getInspectorComponent(section.id)"
        v-if="getInspectorComponent(section.id)"
        :data="data"
      />
      <p v-else class="sk-caption">Section available</p>
    </div>

    <!-- Default Properties (always shown) -->
    <div v-if="data" class="space-y-2">
      <h4 class="sk-section-title">📋 Properti</h4>
      <div class="space-y-1.5">
        <div v-for="(val, key) in inspectorProps" :key="key" class="flex justify-between text-xs">
          <span :style="{ color: 'var(--text-muted)' }">{{ key }}</span>
          <span class="font-medium truncate max-w-[150px]" :style="{ color: 'var(--text-primary)' }">{{ val || '-' }}</span>
        </div>
      </div>
    </div>

    <!-- Metadata -->
    <div class="space-y-2">
      <h4 class="sk-section-title">ℹ️ Metadata</h4>
      <div class="space-y-1.5">
        <div class="flex justify-between text-xs">
          <span :style="{ color: 'var(--text-muted)' }">Created</span>
          <span :style="{ color: 'var(--text-primary)' }">{{ formatDate(data?.created_at) }}</span>
        </div>
        <div class="flex justify-between text-xs">
          <span :style="{ color: 'var(--text-muted)' }">Updated</span>
          <span :style="{ color: 'var(--text-primary)' }">{{ formatDate(data?.updated_at) }}</span>
        </div>
        <div class="flex justify-between text-xs">
          <span :style="{ color: 'var(--text-muted)' }">ID</span>
          <span class="font-mono text-[10px]" :style="{ color: 'var(--text-muted)' }">#{{ data?.id }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { workspaceRegistry } from '@/Enterprise/Workspace/WorkspaceRegistry.js';

const props = defineProps({
  sections: { type: Array, default: () => [] },
  data: { type: Object, default: () => ({}) },
  workspace: { type: Object, default: null },
});

defineEmits(['close']);

function getInspectorComponent(sectionId) {
  return workspaceRegistry.getInspectorSection(props.workspace?.id, sectionId) || null;
}

const inspectorProps = computed(() => {
  const d = props.data || {};
  // Auto-extract flat props for inspector
  const exclude = ['id', 'created_at', 'updated_at', 'customer', 'technician', 'spareparts', 'photos', 'sale', 'worklogs'];
  const entries = Object.entries(d).filter(([k]) => !exclude.includes(k) && typeof d[k] !== 'object');
  return Object.fromEntries(entries.slice(0, 10));
});

function formatDate(d) {
  if (!d) return '-';
  return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}
</script>
