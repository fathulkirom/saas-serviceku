<template>
  <AuthenticatedLayout>
    <div class="min-h-screen" :style="{ background: 'var(--bg-app)' }">

      <!-- ═══════════ BREADCRUMB + HEADER ═══════════ -->
      <div class="border-b px-4 sm:px-6 lg:px-8 py-3" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-light)' }">
        <div class="max-w-[1600px] mx-auto w-full">
          <div class="flex items-center justify-between gap-4 flex-wrap">
            <!-- Breadcrumb -->
            <SkBreadcrumb :items="breadcrumbItems" />

            <!-- Top-right: Refresh + Tracking Code -->
            <div class="flex items-center gap-3">
              <span class="text-xs font-mono font-bold px-3 py-1.5 rounded-lg border" :style="{ color: 'var(--text-secondary)', borderColor: 'var(--border-color)', background: 'var(--bg-hover)' }">
                #{{ service.tracking_code }}
              </span>
              <button
                @click="refresh"
                :disabled="isRefreshing"
                class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors"
                :class="{ 'animate-spin': isRefreshing }"
                :style="{ color: 'var(--text-muted)' }"
                title="Refresh"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ═══════════ ACTION BAR ═══════════ -->
      <WorkspaceActionBar
        :service="service"
        :availableTransitions="availableTransitions"
        :isTransitioning="isTransitioning"
        :transitionError="transitionError"
        :canAssign="canAssign"
        :canWork="canWork"
        :canInvoice="canInvoice"
        :canQC="canQC"
        @transition="executeTransition"
        @repair-action="handleRepairAction"
        @navigate-qc="switchTab('qc')"
      />

      <div class="max-w-[1600px] mx-auto w-full px-4 sm:px-6 lg:px-8 py-5">
        <div class="flex flex-col lg:flex-row gap-5">

          <!-- ═══════════ MAIN CONTENT (70%) ═══════════ -->
          <div class="flex-1 min-w-0 space-y-5">

            <!-- TABS -->
            <div class="flex items-center gap-0.5 border-b overflow-x-auto" :style="{ borderColor: 'var(--border-light)' }">
              <button
                v-for="tab in availableTabs"
                :key="tab.id"
                @click="switchTab(tab.id)"
                class="flex items-center gap-1.5 px-4 py-2.5 text-sm font-semibold whitespace-nowrap border-b-2 transition-colors -mb-[1px]"
                :style="{
                  color: activeTab === tab.id ? 'var(--primary)' : 'var(--text-muted)',
                  borderColor: activeTab === tab.id ? 'var(--primary)' : 'transparent',
                }"
              >
                <span>{{ tab.icon }}</span>
                <span>{{ tab.label }}</span>
              </button>
            </div>

            <!-- TAB CONTENT -->
            <div class="sk-animate-slide-up" :key="activeTab">
              <!-- Overview -->
              <WorkspaceOverview
                v-if="activeTab === 'overview'"
                :service="service"
                :customerSummary="customerSummary"
                :previousServices="previousServices"
                :relatedServices="relatedServices"
              />

              <!-- Timeline -->
              <WorkspaceTimeline
                v-else-if="activeTab === 'timeline'"
                :workflowHistory="workflowHistory"
                :service="service"
              />

              <!-- Repair -->
              <WorkspaceRepair
                v-else-if="activeTab === 'repair'"
                :service="service"
                :canStartRepair="canStartRepair"
                :canCompleteRepair="canCompleteRepair"
                :canAddNote="canStartRepair"
                :technicianName="service.technician?.name"
                :worklogs="service.worklogs || []"
                :repairNotes="service.repair_notes || []"
                @refresh="refresh"
              />

              <!-- Spareparts -->
              <WorkspaceSpareparts
                v-else-if="activeTab === 'spareparts'"
                :service="service"
                :spareparts="service.spareparts || []"
                :requiredParts="service.required_parts || []"
                :availableProducts="service.available_products || []"
                :canManageParts="canManageParts"
                :canConsumeParts="canConsumeParts"
                :canRequestPart="canRequestPart"
                @refresh="refresh"
              />

              <!-- Photos -->
              <WorkspacePhotos
                v-else-if="activeTab === 'photos'"
                :photos="service.photos || []"
                :canUpload="canWork"
                :canDelete="canWork"
                :serviceId="service.id"
                @refresh="refresh"
              />

              <!-- QC -->
              <WorkspaceQC
                v-else-if="activeTab === 'qc'"
                :service="service"
                :canQC="canQC"
                :qcChecks="service.qc_checks || []"
                @refresh="refresh"
              />

              <!-- Invoice -->
              <WorkspaceInvoice
                v-else-if="activeTab === 'invoice'"
                :sale="service.sale"
                :serviceCharge="service.service_charge"
                :totalCost="service.total_cost"
                :paymentStatus="service.payment_status"
              />
            </div>
          </div>

          <!-- ═══════════ SIDEBAR (30%) ═══════════ -->
          <div class="w-full lg:w-[360px] flex-shrink-0 space-y-4">
            <!-- Toggle sidebar on mobile -->
            <button
              @click="showSidebar = !showSidebar"
              class="lg:hidden w-full px-3 py-2 text-sm font-semibold rounded-xl border text-center"
              :style="{ borderColor: 'var(--border-color)', color: 'var(--text-secondary)' }"
            >
              {{ showSidebar ? 'Sembunyikan' : 'Tampilkan' }} Sidebar
            </button>

            <div v-show="showSidebar" class="space-y-4">
              <WorkspaceSidebar
                :service="service"
                :customerSummary="customerSummary"
                :featureAccess="featureAccess"
                :canAssign="canAssign"
                :canWork="canWork"
              />
            </div>
          </div>

        </div>
      </div>

      <!-- ═══════════ TRANSITION ERROR TOAST ═══════════ -->
      <div
        v-if="transitionError"
        class="fixed bottom-5 right-5 z-[9999] px-4 py-3 rounded-xl shadow-lg border text-sm font-semibold flex items-center gap-2"
        :style="{ background: 'var(--danger-soft)', borderColor: 'var(--danger-soft-border)', color: 'var(--danger-text)' }"
      >
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
        </svg>
        {{ transitionError }}
        <button @click="transitionError = ''" class="ml-2 w-5 h-5 rounded-full flex items-center justify-center" :style="{ color: 'var(--danger)' }">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SkBreadcrumb from '@/Enterprise/Components/Navigation/Breadcrumb.vue';
import { useServiceWorkspace } from './composables/useServiceWorkspace.js';
import { usePage } from '@inertiajs/vue3';

// ── Tab Sections ──
import WorkspaceActionBar from './sections/ActionBar.vue';
import WorkspaceSidebar from './sections/Sidebar.vue';
import WorkspaceOverview from './sections/Overview.vue';
import WorkspaceTimeline from './sections/Timeline.vue';
import WorkspaceRepair from './sections/Repair.vue';
import WorkspaceSpareparts from './sections/Spareparts.vue';
import WorkspacePhotos from './sections/Photos.vue';
import WorkspaceQC from './sections/QC.vue';
import WorkspaceInvoice from './sections/Invoice.vue';

// ── Workspace State ──
const {
  service, customerSummary, previousServices, relatedServices,
  workflowHistory, availableTransitions, featureAccess,
  activeTab, availableTabs, isRefreshing, isTransitioning, transitionError,
  showSidebar, canAssign, canWork, canInvoice,
  canStartRepair, canCompleteRepair, canQC, canRequestPart,
  canManageParts, canConsumeParts,
  switchTab, refresh, executeTransition,
} = useServiceWorkspace();

// ── Breadcrumb ──
const breadcrumbItems = computed(() => [
  { label: 'Dashboard', url: route('dashboard') },
  { label: 'Servis', url: route('services.index') },
  { label: service.value?.tracking_code || 'Detail' },
]);

const page = usePage();

// Sprint v3.0B: Handle repair action from ActionBar → navigate to repair tab
function handleRepairAction(action) {
  switchTab('repair');
}
</script>
