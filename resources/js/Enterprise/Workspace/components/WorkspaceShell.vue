<template>
  <!-- WorkspaceShell — Unified workspace layout for ALL 22 modules -->
  <div class="workspace-shell flex flex-col h-full" :style="{ background: 'var(--bg-base)' }">
    <!-- Header + Toolbar -->
    <header class="flex-shrink-0 border-b" :style="{ borderColor: 'var(--border-light)', background: 'var(--bg-surface)' }">
      <div class="flex items-center justify-between px-4 py-2">
        <div class="flex items-center gap-3">
          <h2 class="text-lg font-bold" :style="{ color: 'var(--text-primary)' }">{{ ws.workspace?.title || moduleId }}</h2>
          <span v-if="ws.sidebarData.record.status" class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase"
            :style="{ background: 'var(--primary-soft)', color: 'var(--primary)' }">
            {{ ws.sidebarData.record.status_label || ws.sidebarData.record.status }}
          </span>
        </div>
        <div class="flex items-center gap-2">
          <!-- Actions -->
          <button v-for="action in ws.actions" :key="action.id"
            @click="ws.executeAction(action.id)"
            :disabled="ws.isLoading(action.id)"
            class="text-xs font-bold px-3 py-1.5 rounded-lg transition hover:opacity-80 disabled:opacity-50"
            :style="{ background: action.danger ? 'var(--danger-soft)' : 'var(--primary-soft)', color: action.danger ? 'var(--danger-text)' : 'var(--primary)' }">
            <span v-if="ws.isLoading(action.id)" class="animate-spin mr-1">⏳</span>
            {{ action.label }}
          </button>
          <!-- Toggle buttons -->
          <button @click="ws.toggleSidebar()" class="text-xs px-2 py-1 rounded"
            :style="{ background: ws.showSidebar ? 'var(--primary-soft)' : 'var(--bg-hover)', color: 'var(--text-muted)' }" title="Sidebar">
            📋
          </button>
          <button @click="ws.toggleInspector()" class="text-xs px-2 py-1 rounded"
            :style="{ background: ws.showInspector ? 'var(--primary-soft)' : 'var(--bg-hover)', color: 'var(--text-muted)' }" title="Inspector">
            ℹ️
          </button>
          <button @click="ws.refresh()" class="text-xs px-2 py-1 rounded" :style="{ background: 'var(--bg-hover)', color: 'var(--text-muted)' }" title="Refresh">
            🔄
          </button>
        </div>
      </div>

      <!-- Tabs -->
      <nav class="flex px-4 gap-0 overflow-x-auto" :style="{ borderTop: '1px solid ' + 'var(--border-light)' }">
        <button v-for="tab in ws.tabs" :key="tab.id"
          @click="ws.switchTab(tab.id)"
          class="text-xs font-medium px-3 py-2 border-b-2 transition whitespace-nowrap"
          :style="ws.activeTab === tab.id
            ? { borderColor: 'var(--primary)', color: 'var(--primary)' }
            : { borderColor: 'transparent', color: 'var(--text-muted)' }">
          <span class="mr-1">{{ tab.icon || '📋' }}</span>{{ tab.label }}
        </button>
      </nav>
    </header>

    <!-- Body: Content + Sidebar + Inspector -->
    <div class="flex-1 flex overflow-hidden">
      <!-- Main Content -->
      <main class="flex-1 overflow-y-auto p-4">
        <!-- Relations Bar -->
        <UniversalRelations v-if="ws.relationsData.length" :links="ws.relationsData" class="mb-3" />

        <!-- Active Tab Content -->
        <slot name="tab" :activeTab="ws.activeTab" :data="ws.data" :meta="ws.meta" :workspace="ws" />

        <!-- Timeline (below tab content) -->
        <div class="mt-5" v-if="ws.timelineEvents.length">
          <h4 class="text-xs font-bold uppercase tracking-wider mb-2" :style="{ color: 'var(--text-muted)' }">Timeline</h4>
          <div class="p-3 rounded-xl" :style="{ background: 'var(--bg-surface)', border: '1px solid var(--border-light)' }">
            <UniversalTimeline :events="ws.timelineEvents" />
          </div>
        </div>
      </main>

      <!-- Sidebar -->
      <transition name="slide">
        <UniversalSidebar
          v-if="ws.showSidebar"
          :title="ws.workspace?.title || 'Details'"
          :record="ws.sidebarData.record"
          :stats="ws.sidebarData.stats"
          :relations="ws.sidebarData.relations"
          :quickActions="ws.sidebarData.quickActions"
          :features="ws.sidebarData.features"
          :permissions="ws.sidebarData.permissions"
          :tags="ws.sidebarData.tags"
          @close="ws.toggleSidebar()"
          @navigate="(rel) => $emit('navigate', rel)"
          @action="(id) => ws.executeAction(id)"
        />
      </transition>

      <!-- Inspector -->
      <transition name="slide">
        <UniversalInspector
          v-if="ws.showInspector"
          :meta="ws.inspectorData"
          @close="ws.toggleInspector()"
        />
      </transition>
    </div>

    <!-- Footer -->
    <UniversalFooter
      :meta="ws.footerData"
      :autoRefresh="ws.wsConfig?.autoRefreshSeconds || 0"
      :lastSync="ws.lastSync"
    />
  </div>
</template>

<script setup>
import { useWorkspace } from '@/Enterprise/Workspace/composables/useWorkspace.js';
import UniversalSidebar from '@/Enterprise/Workspace/components/UniversalSidebar.vue';
import UniversalInspector from '@/Enterprise/Workspace/components/UniversalInspector.vue';
import UniversalTimeline from '@/Enterprise/Workspace/components/UniversalTimeline.vue';
import UniversalFooter from '@/Enterprise/Workspace/components/UniversalFooter.vue';
import UniversalRelations from '@/Enterprise/Workspace/components/UniversalRelations.vue';

const props = defineProps({
  moduleId: { type: String, required: true },
});

defineEmits(['navigate']);

const ws = useWorkspace(props.moduleId);
</script>

<style scoped>
.slide-enter-active, .slide-leave-active { transition: transform 0.2s ease, opacity 0.2s ease; }
.slide-enter-from, .slide-leave-to { transform: translateX(20px); opacity: 0; }
</style>
