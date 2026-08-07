import { ref, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

/**
 * useServiceWorkspace — Central state management for Service Workspace.
 * 
 * Handles:
 * - Active tab switching
 * - Status transitions (optimistic)
 * - Refresh/reload
 * - Role-based access checks
 * - Feature access checks
 */
export function useServiceWorkspace() {
  const page = usePage();

  // ── Workspace Data ──
  const workspace = computed(() => page.props.workspace || {});
  const service = computed(() => workspace.value?.service || {});
  const customerSummary = computed(() => workspace.value?.customerSummary || null);
  const previousServices = computed(() => workspace.value?.previousServices || []);
  const relatedServices = computed(() => workspace.value?.relatedServices || []);
  const workflowHistory = computed(() => workspace.value?.workflowHistory || []);
  const availableTransitions = computed(() => workspace.value?.availableTransitions || []);
  const featureAccess = computed(() => workspace.value?.featureAccess || {});
  const workspaceConfig = computed(() => workspace.value?.workspaceConfig || {});

  // ── UI State ──
  const activeTab = ref('overview');
  const isRefreshing = ref(false);
  const isTransitioning = ref(false);
  const transitionError = ref('');
  const showSidebar = ref(true);

  // ── Available Tabs (filtered by data availability + status) ──
  const availableTabs = computed(() => {
    const tabs = [
      { id: 'overview', label: 'Overview', icon: '📋' },
      { id: 'timeline', label: 'Timeline', icon: '🕐' },
      { id: 'repair', label: 'Repair', icon: '🔧', show: canViewRepairTab.value },
      { id: 'spareparts', label: 'Sparepart', icon: '🔩', show: service.value?.spareparts?.length > 0 || canManageParts.value },
      { id: 'photos', label: 'Foto', icon: '📸', show: service.value?.photos?.length > 0 },
      { id: 'qc', label: 'QC', icon: '🔬', show: canViewQcTab.value },
      { id: 'invoice', label: 'Invoice', icon: '💰', show: !!service.value?.sale },
    ];
    return tabs.filter(t => t.show !== false);
  });

  // ── User Context ──
  const userRole = computed(() => page.props.auth?.user?.role || 'admin');
  const rolePermissions = computed(() => page.props.role_permissions?.[userRole.value] || []);

  const isOwner = computed(() => userRole.value === 'owner');
  const isAdmin = computed(() => userRole.value === 'admin');
  const isManager = computed(() => userRole.value === 'manager');
  const isCs = computed(() => userRole.value === 'cs');
  const isTechnician = computed(() => userRole.value === 'technician');

  // ── Permission Checks ──
  const canAssign = computed(() =>
    (isOwner.value || isAdmin.value || isCs.value) &&
    rolePermissions.value.includes('assign_technician')
  );

  const canWork = computed(() =>
    (isTechnician.value || isOwner.value || isAdmin.value) &&
    rolePermissions.value.includes('work_on_services')
  );

  const canManageParts = computed(() =>
    (isOwner.value || isAdmin.value || isManager.value) &&
    rolePermissions.value.includes('manage_products')
  );

  // BR-FIX-01: CS / Kasir (billing actor) confirms billable part consumption.
  // Mirrors ServiceRequiredPartPolicy@consume; backend remains authoritative.
  const canConsumeParts = computed(() =>
    isOwner.value || isAdmin.value || isManager.value || isCs.value || userRole.value === 'cashier'
  );

  const canInvoice = computed(() =>
    (isOwner.value || isAdmin.value || isManager.value) &&
    rolePermissions.value.includes('manage_sales')
  );

  // ── Sprint v3.0B: Repair & QC Permissions ──
  const canStartRepair = computed(() =>
    canWork.value &&
    (isTechnician.value || isOwner.value || isAdmin.value || isManager.value) &&
    ['dikerjakan', 'menunggu_konfirmasi_pelanggan'].includes(service.value?.status)
  );

  const canCompleteRepair = computed(() =>
    canWork.value &&
    (isTechnician.value || isOwner.value || isAdmin.value || isManager.value) &&
    service.value?.status === 'dikerjakan' &&
    !!service.value?.dikerjakan_at
  );

  const canQC = computed(() =>
    (isOwner.value || isAdmin.value || isManager.value) &&
    service.value?.status === 'selesai'
  );

  const canRequestPart = computed(() =>
    canWork.value &&
    ['dikerjakan', 'diagnosa'].includes(service.value?.status)
  );

  const canViewRepairTab = computed(() =>
    ['dikerjakan', 'selesai', 'siap_diambil'].includes(service.value?.status)
  );

  const canViewQcTab = computed(() =>
    ['selesai', 'siap_diambil'].includes(service.value?.status) ||
    (isOwner.value || isAdmin.value || isManager.value)
  );

  // ── Status Helpers ──
  const statusLabel = computed(() => service.value?.status_label || service.value?.status || '-');
  const statusColor = computed(() => service.value?.status_color || 'default');

  const isActive = computed(() => {
    const s = service.value?.status;
    return s && !['cancel', 'void', 'close'].includes(s);
  });

  // ── Actions ──
  function switchTab(tabId) {
    activeTab.value = tabId;
  }

  async function refresh() {
    isRefreshing.value = true;
    try {
      router.reload({
        only: ['workspace'],
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => { isRefreshing.value = false; },
        onError: () => { isRefreshing.value = false; },
      });
    } catch {
      isRefreshing.value = false;
    }
  }

  async function executeTransition(newStatus, options = {}) {
    isTransitioning.value = true;
    transitionError.value = '';

    // Optimistic update
    const oldStatus = service.value?.status;
    if (service.value) service.value.status = newStatus;

    try {
      const response = await fetch(
        `/services/${service.value.id}/workspace/transition`,
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': page.props.csrf_token || '',
          },
          body: JSON.stringify({
            status: newStatus,
            note: options.note || '',
            technician_id: options.technician_id || null,
          }),
        }
      );

      if (!response.ok) throw new Error('Transition failed');

      const data = await response.json();
      // Replace workspace data
      page.props.workspace = data.workspace;
      isTransitioning.value = false;
    } catch (err) {
      // Rollback optimistic update
      if (service.value) service.value.status = oldStatus;
      transitionError.value = err.message || 'Gagal mengubah status';
      isTransitioning.value = false;
    }
  }

  return {
    // Data
    workspace, service, customerSummary, previousServices, relatedServices,
    workflowHistory, availableTransitions, featureAccess, workspaceConfig,

    // UI State
    activeTab, availableTabs, isRefreshing, isTransitioning, transitionError,
    showSidebar,

    // User
    userRole, rolePermissions, isOwner, isAdmin, isManager, isCs, isTechnician,

    // Permissions
    canAssign, canWork, canManageParts, canConsumeParts, canInvoice,
    canStartRepair, canCompleteRepair, canQC, canRequestPart,
    canViewRepairTab, canViewQcTab,

    // Status
    statusLabel, statusColor, isActive,

    // Actions
    switchTab, refresh, executeTransition,
  };
}
