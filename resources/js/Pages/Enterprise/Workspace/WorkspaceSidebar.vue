<template>
  <div class="p-4 space-y-4">
    <div
      v-for="widget in widgets"
      :key="widget.id"
    >
      <!-- Dynamic sidebar widget -->
      <component
        :is="getWidgetComponent(widget.component)"
        v-if="getWidgetComponent(widget.component)"
        :data="data"
        :workspace="workspace"
      />
      <!-- Fallback for unregistered widgets -->
      <div v-else class="rounded-xl border p-4" :style="{ borderColor: 'var(--border-light)' }">
        <p class="sk-caption text-center">{{ widget.component || widget.id }}</p>
      </div>
    </div>

    <div v-if="!widgets.length" class="py-8 text-center">
      <p class="sk-caption">Tidak ada widget sidebar.</p>
    </div>
  </div>
</template>

<script setup>
import { workspaceRegistry } from '@/Enterprise/Workspace/WorkspaceRegistry.js';

const props = defineProps({
  widgets: { type: Array, default: () => [] },
  data: { type: Object, default: () => ({}) },
  workspace: { type: Object, default: null },
});

function getWidgetComponent(componentName) {
  if (!componentName) return null;
  // Resolve from registry: workspaceId + widgetId
  return workspaceRegistry.getSidebarWidget(props.workspace?.id, componentName) || null;
}
</script>
