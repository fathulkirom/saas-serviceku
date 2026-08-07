<template>
  <AuthenticatedLayout>
    <div class="min-h-screen flex flex-col" :style="{ background: 'var(--bg-app)' }">

      <!-- ═══════════ HEADER ═══════════ -->
      <WorkspaceHeader
        :workspace="workspace"
        :data="data"
        :isRefreshing="isRefreshing"
        :isFullscreen="isFullscreen"
        @refresh="refresh"
        @toggle-fullscreen="toggleFullscreen"
        @toggle-inspector="toggleInspector"
      />

      <!-- ═══════════ TOOLBAR ═══════════ -->
      <WorkspaceToolbar
        :actions="actions"
        :actionLoading="actionLoading"
        :lastError="lastError"
        @execute="executeAction"
        @toggle-sidebar="toggleSidebar"
        @toggle-inspector="toggleInspector"
      />

      <!-- ═══════════ TABS ═══════════ -->
      <WorkspaceTabs
        :tabs="tabs"
        :activeTab="activeTab"
        @switch="switchTab"
      />

      <!-- ═══════════ MAIN CONTENT AREA ═══════════ -->
      <div class="flex-1 flex overflow-hidden">
        <!-- Main Content -->
        <div class="flex-1 min-w-0 overflow-y-auto">
          <div class="max-w-[1600px] mx-auto w-full px-4 sm:px-6 lg:px-8 py-5">
            <!-- Dynamic Tab Content -->
            <transition name="sk-page" mode="out-in">
              <component
                :is="getTabComponent(activeTab)"
                :key="activeTab"
                v-if="getTabComponent(activeTab)"
                :data="data"
                :workspace="workspace"
                v-bind="data"
                @refresh="refresh"
              />
              <div v-else class="py-12 text-center">
                <p class="sk-caption">Tab "{{ activeTab }}" belum diimplementasikan.</p>
              </div>
            </transition>
          </div>
        </div>

        <!-- Sidebar (right) -->
        <transition name="sk-drawer">
          <WorkspaceSidebar
            v-if="showSidebar"
            :widgets="sidebarWidgets"
            :data="data"
            :workspace="workspace"
            class="w-[340px] flex-shrink-0 border-l overflow-y-auto hidden lg:block"
            :style="{ borderColor: 'var(--border-light)', background: 'var(--bg-card)' }"
          />
        </transition>

        <!-- Inspector Panel (rightmost) -->
        <transition name="sk-drawer">
          <WorkspaceInspector
            v-if="showInspector"
            :sections="inspectorSections"
            :data="data"
            @close="showInspector = false"
            class="w-[320px] flex-shrink-0 border-l overflow-y-auto"
            :style="{ borderColor: 'var(--border-light)', background: 'var(--bg-card)' }"
          />
        </transition>
      </div>

      <!-- ═══════════ FOOTER ═══════════ -->
      <WorkspaceFooter
        :lastRefreshed="lastRefreshed"
        :isRefreshing="isRefreshing"
        @refresh="refresh"
      />

      <!-- ═══════════ MOBILE SIDEBAR DRAWER ═══════════ -->
      <SkDrawer
        :open="mobileSidebarOpen"
        position="right"
        title="Sidebar"
        @close="mobileSidebarOpen = false"
      >
        <WorkspaceSidebar
          :widgets="sidebarWidgets"
          :data="data"
          :workspace="workspace"
        />
      </SkDrawer>

    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SkDrawer from '@/Enterprise/Components/Overlay/Drawer.vue';
import { useWorkspace } from '@/Enterprise/Workspace/composables/useWorkspace.js';
import { workspaceRegistry } from '@/Enterprise/Workspace/WorkspaceRegistry.js';

// ── Layout Components ──
import WorkspaceHeader from './WorkspaceHeader.vue';
import WorkspaceToolbar from './WorkspaceToolbar.vue';
import WorkspaceTabs from './WorkspaceTabs.vue';
import WorkspaceSidebar from './WorkspaceSidebar.vue';
import WorkspaceInspector from './WorkspaceInspector.vue';
import WorkspaceFooter from './WorkspaceFooter.vue';

const props = defineProps({
  workspaceConfig: { type: Object, default: () => ({}) },
});

// Detect module from config
const moduleId = computed(() => props.workspaceConfig?.workspace?.id || 'unknown');

// ── Workspace Engine ──
const {
  workspace, data, tabs, actions, sidebarWidgets, inspectorSections,
  activeTab, isRefreshing, showSidebar, showInspector, lastError,
  actionLoading,
  switchTab, executeAction, refresh: _refresh, toggleSidebar, toggleInspector, toggleFullscreen,
} = useWorkspace(moduleId.value);

const mobileSidebarOpen = ref(false);
const lastRefreshed = ref(Date.now());

// Watch refresh to update timestamp
const refresh = () => {
  _refresh();
  lastRefreshed.value = Date.now();
};

// ── Dynamic Tab Component Resolution ──
function getTabComponent(tabId) {
  return workspaceRegistry.getTabComponent(moduleId.value, tabId);
}
</script>
