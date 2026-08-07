import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { workspaceRegistry } from '../WorkspaceRegistry.js';

/**
 * useWorkspace — Universal Workspace Engine Composable.
 * 
 * Digunakan oleh SEMUA modul workspace (Service, Inventory, POS, Finance, etc.).
 * Zero module-specific code. Semua konfigurasi dari registry.
 * 
 * Usage:
 *   const ws = useWorkspace('service', serviceId)
 *   ws.activeTab       // 'overview'
 *   ws.switchTab('timeline')
 *   ws.executeAction('start')
 *   ws.toggleInspector()
 */
export function useWorkspace(moduleId) {
  const page = usePage();

  // ── Workspace Config (from backend Inertia props) ──
  const config = computed(() => page.props.workspaceConfig || {});
  const workspace = computed(() => config.value?.workspace || {});
  const data = computed(() => config.value?.data || {});
  const meta = computed(() => config.value?.meta || {});
  const user = computed(() => config.value?.user || {});

  // ── UI State ──
  const activeTab = ref('overview');
  const isRefreshing = ref(false);
  const showSidebar = ref(true);
  const showInspector = ref(false);
  const isFullscreen = ref(false);
  const actionLoading = ref({});  // { actionId: true/false }
  const lastError = ref('');

  // ── Derived ──
  const tabs = computed(() => workspace.value?.tabs || []);
  const actions = computed(() => workspace.value?.actions || []);
  const sidebarWidgets = computed(() => workspace.value?.sidebarWidgets || []);
  const inspectorSections = computed(() => workspace.value?.inspectorSections || []);
  const shortcuts = computed(() => workspace.value?.shortcuts || []);
  const wsConfig = computed(() => workspace.value?.config || {});
  // ── Universal Sidebar Data (auto-derived from workspace meta) ──
  const sidebarData = computed(() => ({
    record: data.value || {},
    stats: meta.value?.stats || {},
    relations: meta.value?.relations || [],
    features: meta.value?.features || [],
    permissions: meta.value?.permissions || [],
    tags: meta.value?.tags || data.value?.tags || [],
    quickActions: actions.value?.filter(a => a.showInSidebar) || [],
  }));

  // ── Universal Inspector Data (auto-derived from workspace meta) ──
  const inspectorData = computed(() => ({
    uuid: meta.value?.uuid || data.value?.id || '',
    module: meta.value?.module || workspace.value?.id || moduleId,
    branch: meta.value?.branch || data.value?.branch_name || '',
    tenant: meta.value?.tenant || '',
    version: meta.value?.version || data.value?.lock_version || 1,
    created_at: meta.value?.created_at || data.value?.created_at || '',
    created_by: meta.value?.created_by || data.value?.created_by_name || '',
    updated_at: meta.value?.updated_at || data.value?.updated_at || '',
    updated_by: meta.value?.updated_by || '',
    workflow: meta.value?.workflow || null,
    permission: meta.value?.permission_granted !== false,
    feature_active: meta.value?.feature_active !== false,
    features: meta.value?.features || [],
  }));

  // ── Universal Timeline Data (auto-derived from workspace meta) ──
  const timelineEvents = computed(() => meta.value?.timeline || []);

  // ── Universal Footer Data (auto-derived) ──
  const footerData = computed(() => ({
    created_at: meta.value?.created_at || data.value?.created_at || '',
    updated_at: meta.value?.updated_at || data.value?.updated_at || '',
    version: meta.value?.version || data.value?.lock_version || 1,
    created_by: meta.value?.created_by || data.value?.created_by_name || '',
    record_count: meta.value?.record_count,
  }));

  // ── Universal Relations Data ──
  const relationsData = computed(() => meta.value?.relations || []);
  const isAccessible = computed(() => workspace.value?.accessible !== false);
  const userRole = computed(() => user.value?.role || 'admin');
  const userPermissions = computed(() => user.value?.permissions || []);

  // ── Tab Management ──
  function switchTab(tabId) {
    const tab = tabs.value.find(t => t.id === tabId);
    if (tab) activeTab.value = tabId;
  }

  function nextTab() {
    const idx = tabs.value.findIndex(t => t.id === activeTab.value);
    if (idx < tabs.value.length - 1) activeTab.value = tabs.value[idx + 1].id;
  }

  function prevTab() {
    const idx = tabs.value.findIndex(t => t.id === activeTab.value);
    if (idx > 0) activeTab.value = tabs.value[idx - 1].id;
  }

  // ── Actions ──
  async function executeAction(actionId, payload = {}) {
    const action = actions.value.find(a => a.id === actionId);
    if (!action) return;

    // Check if there's a registered handler
    const handler = workspaceRegistry.getActionHandler(moduleId, actionId);
    if (handler) {
      actionLoading.value[actionId] = true;
      try {
        await handler(data.value, payload);
      } catch (e) {
        lastError.value = e.message || `Gagal menjalankan aksi: ${action.label}`;
      } finally {
        actionLoading.value[actionId] = false;
      }
      return;
    }

    // Default: POST to backend
    actionLoading.value[actionId] = true;
    lastError.value = '';

    try {
      const response = await fetch(`/workspace/${moduleId}/${data.value?.id}/action`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': page.props.csrf_token || '',
          'Accept': 'application/json',
        },
        body: JSON.stringify({ action: actionId, payload }),
      });

      if (!response.ok) throw new Error('Action failed');

      const result = await response.json();
      if (!result.success) throw new Error(result.error || 'Action failed');

      // Refresh workspace data
      refresh();
    } catch (e) {
      lastError.value = e.message || 'Gagal menjalankan aksi';
    } finally {
      actionLoading.value[actionId] = false;
    }
  }

  function isLoading(actionId) {
    return !!actionLoading.value[actionId];
  }

  // ── Refresh ──
  async function refresh() {
    isRefreshing.value = true;
    router.reload({
      only: ['workspaceConfig'],
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => { isRefreshing.value = false; },
      onError: () => { isRefreshing.value = false; },
    });
  }

  // ── Sidebar / Inspector Toggle ──
  function toggleSidebar() { showSidebar.value = !showSidebar.value; }
  function toggleInspector() { showInspector.value = !showInspector.value; }
  function toggleFullscreen() { isFullscreen.value = !isFullscreen.value; }

  // ── Shortcut Registration ──
  function handleKeydown(e) {
    // Don't capture if typing in input
    const tag = e.target?.tagName?.toLowerCase();
    if (['input', 'textarea', 'select'].includes(tag)) return;

    const wsShorts = shortcuts.value || [];
    for (const s of wsShorts) {
      const keyMatch = e.key.toLowerCase() === s.key.toLowerCase();
      const ctrlMatch = s.ctrl ? e.ctrlKey || e.metaKey : !e.ctrlKey && !e.metaKey;
      if (keyMatch && ctrlMatch) {
        e.preventDefault();
        // Try registered handler first
        const handler = workspaceRegistry.getShortcutHandler(moduleId, s.action);
        if (handler) {
          handler(data.value);
          return;
        }
        // Default actions
        if (s.action === 'refresh') refresh();
        else if (s.action === 'search') { /* focus search */ }
        else if (s.action === 'edit') { /* navigate to edit */ }
      }
    }
  }

  onMounted(() => {
    document.addEventListener('keydown', handleKeydown);
  });

  onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown);
  });

  // ── Return full API ──
  return {
    // Data
    workspace, config, data, meta, user,

    // UI State
    activeTab, isRefreshing, showSidebar, showInspector, isFullscreen,
    actionLoading, lastError,

    // Derived
    tabs, actions, sidebarWidgets, inspectorSections, shortcuts, wsConfig,
    isAccessible, userRole, userPermissions,

    // Universal Components Data (auto-derived, zero hardcode)
    sidebarData, inspectorData, timelineEvents, footerData, relationsData,

    // Tab
    switchTab, nextTab, prevTab,

    // Actions
    executeAction, isLoading,

    // Toggle
    refresh, toggleSidebar, toggleInspector, toggleFullscreen,
  };
}
