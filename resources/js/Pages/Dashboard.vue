<template>
  <AuthenticatedLayout>
    <div class="flex flex-col gap-6 min-h-screen pb-12" :style="{ background: 'var(--bg-app)' }">

      <!-- ═══════════ ENTERPRISE TOP BAR ═══════════ -->
      <DashboardTopBar
        :userName="$page.props.auth.user.name"
        :userRole="userRole"
        :branchName="currentBranch?.name || ''"
      />

      <div class="px-4 sm:px-6 lg:px-8 max-w-[1600px] mx-auto w-full space-y-6">

        <!-- ═══════════ QUICK ACTIONS ═══════════ -->
        <DashboardQuickActions
          :userRole="userRole"
          :planAccess="planAccess"
        />

        <!-- ═══════════ SETUP PROGRESS (Owner/Manager only) ═══════════ -->
        <ErrorBoundary>
          <SetupProgressCard v-if="setupSummary" :setupSummary="setupSummary" />
        </ErrorBoundary>

        <!-- ═══════════ SKELETON LOADING ═══════════ -->
        <template v-if="!stats">
          <SkLoading variant="skeleton" type="stat" :count="4" />
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mt-5">
            <SkLoading variant="skeleton" type="card" />
            <SkLoading variant="skeleton" type="card" />
          </div>
        </template>

        <!-- ═══════════ DASHBOARD WIDGETS ═══════════ -->
        <template v-else>
          <!-- Metric Widgets Row -->
          <div v-if="visibleMetricWidgets.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
            <ErrorBoundary v-for="(widget, i) in visibleMetricWidgets" :key="widget.id">
              <LazyLoader>
                <component
                  :is="widget.component"
                  :stats="stats"
                  :style="{ animationDelay: (i * 50) + 'ms' }"
                  class="sk-animate-slide-up"
                />
              </LazyLoader>
            </ErrorBoundary>
          </div>

          <!-- Content Widgets Row (charts + data tables) -->
          <div v-if="visibleContentWidgets.length" class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <ErrorBoundary v-for="(widget, i) in visibleContentWidgets" :key="widget.id">
              <LazyLoader>
                <WidgetRefresh
                  :title="widget.title"
                  :autoRefresh="widget.autoRefresh || 0"
                  @refresh="handleRefresh(widget.id)"
                >
                  <component
                    :is="widget.component"
                    v-bind="getWidgetProps(widget)"
                    :loading="false"
                    :style="{ animationDelay: (i * 75) + 'ms' }"
                    class="sk-animate-slide-up"
                    @navigate="handleWidgetNavigate"
                  />
                </WidgetRefresh>
              </LazyLoader>
            </ErrorBoundary>
          </div>

          <!-- ═══════════ PREFERENCES PANEL ═══════════ -->
          <div v-if="showPrefs" class="rounded-2xl border p-5" :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-card)' }">
            <div class="flex items-center justify-between mb-4">
              <h3 class="sk-label">Atur Widget</h3>
              <button @click="showPrefs = false" class="text-xs font-semibold" :style="{ color: 'var(--text-muted)' }">Tutup</button>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
              <label
                v-for="widget in allWidgets"
                :key="widget.id"
                class="flex items-center gap-2 px-3 py-2 rounded-xl border cursor-pointer transition-colors"
                :style="{ borderColor: hiddenIds.includes(widget.id) ? 'var(--border-light)' : 'var(--primary-soft-border)', background: hiddenIds.includes(widget.id) ? 'var(--bg-hover)' : 'var(--primary-soft)' }"
              >
                <input
                  type="checkbox"
                  :checked="!hiddenIds.includes(widget.id)"
                  @change="toggleWidgetPref(widget.id)"
                  class="rounded"
                />
                <span class="text-xs font-medium" :style="{ color: 'var(--text-primary)' }">{{ widget.title }}</span>
              </label>
            </div>
            <button
              @click="resetPrefs"
              class="mt-4 text-xs font-semibold"
              :style="{ color: 'var(--danger)' }"
            >
              Reset ke Default
            </button>
          </div>

          <!-- ═══════════ PREFERENCES TOGGLE BUTTON ═══════════ -->
          <div class="flex justify-end">
            <button
              @click="showPrefs = !showPrefs"
              class="px-3 py-1.5 text-xs font-semibold rounded-lg border transition-colors flex items-center gap-1.5"
              :style="{ background: 'var(--bg-card)', color: 'var(--text-secondary)', borderColor: 'var(--border-color)' }"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
              </svg>
              {{ showPrefs ? 'Sembunyikan' : 'Atur Widget' }}
            </button>
          </div>
        </template>

        <!-- ═══════════ EMPTY STATE ═══════════ -->
        <div v-if="stats && allWidgets.length === 0" class="py-16">
          <SkEmptyState
            variant="lock"
            title="Dashboard Tidak Tersedia"
            description="Role Anda tidak memiliki akses ke widget apapun. Silakan hubungi administrator."
          />
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// ── Enterprise Dashboard Components ──
import DashboardTopBar from '@/Enterprise/Dashboard/TopBar.vue';
import DashboardQuickActions from '@/Enterprise/Dashboard/QuickActions.vue';
import ErrorBoundary from '@/Enterprise/Dashboard/ErrorBoundary.vue';
import LazyLoader from '@/Enterprise/Dashboard/LazyLoader.vue';
import WidgetRefresh from '@/Enterprise/Dashboard/WidgetRefresh.vue';
import SkLoading from '@/Enterprise/Components/Feedback/Loading.vue';
import SkEmptyState from '@/Enterprise/Components/Empty/EmptyState.vue';

// ── Widget Registry ──
import { registry } from '@/Enterprise/Dashboard/DashboardWidgetRegistry.js';
import '@/Enterprise/Dashboard/widgets.js';

// ── Preferences ──
import {
  loadPreferences, toggleWidget, isWidgetHidden,
  resetPreferences,
} from '@/Enterprise/Dashboard/DashboardPreferences.js';

// ── Legacy ──
import SetupProgressCard from '@/Components/SetupProgressCard.vue';

// ═══════════════════════════════════════════════════════════
// PROPS
// ═══════════════════════════════════════════════════════════
defineProps({
  stats: { type: Object, default: null },
  recentServices: { type: Array, default: () => [] },
  isNotTechnician: { type: Boolean, default: true },
  setupSummary: { type: Object, default: null },
});

const page = usePage();

// ═══════════════════════════════════════════════════════════
// USER CONTEXT
// ═══════════════════════════════════════════════════════════
const userRole = computed(() => page.props.auth?.user?.role || 'admin');
const planAccess = computed(() => page.props.plan_access || {});
const rolePermissions = computed(() => page.props.role_permissions?.[userRole.value] || []);
const businessType = computed(() => page.props.tenant?.business_type || 'full_service');
const currentBranch = computed(() => page.props.currentBranch || null);
const userId = computed(() => page.props.auth?.user?.id || 'anonymous');

// ═══════════════════════════════════════════════════════════
// WIDGET RESOLUTION
// ═══════════════════════════════════════════════════════════
const allWidgets = computed(() =>
  registry.resolve(userRole.value, planAccess.value, rolePermissions.value, businessType.value)
);

// ═══════════════════════════════════════════════════════════
// PREFERENCES (localStorage)
// ═══════════════════════════════════════════════════════════
const showPrefs = ref(false);
const prefs = ref(loadPreferences(userId.value));

const hiddenIds = computed(() => prefs.value.hidden || []);

const visibleMetricWidgets = computed(() =>
  allWidgets.value.filter(w => (w.cols || 1) === 1 && !isWidgetHidden(userId.value, w.id))
);

const visibleContentWidgets = computed(() =>
  allWidgets.value.filter(w => (w.cols || 1) >= 2 && !isWidgetHidden(userId.value, w.id))
);

function toggleWidgetPref(widgetId) {
  prefs.value = toggleWidget(userId.value, widgetId);
}

function resetPrefs() {
  resetPreferences(userId.value);
  prefs.value = { hidden: [], order: [], sizes: {} };
}

// ═══════════════════════════════════════════════════════════
// WIDGET REFRESH
// ═══════════════════════════════════════════════════════════
function handleRefresh(widgetId) {
  // Reload halaman via Inertia (preserve scroll)
  router.reload({ only: ['stats', 'recentServices'], preserveState: true, preserveScroll: true });
}

// ═══════════════════════════════════════════════════════════
// WIDGET PROPS
// ═══════════════════════════════════════════════════════════
function getWidgetProps(widget) {
  const base = { stats: page.props.stats };

  const dataMap = {
    recent_services: { services: page.props.recentServices || [] },
    recent_sales: { sales: page.props.stats?.recent_sales || [] },
    activity: { activities: page.props.recentServices || [] },
    stock_alerts: { alerts: page.props.stats?.low_stock_items || [] },
    status_chart: { statusCounts: page.props.stats?.statusCounts || {} },
  };

  return { ...base, ...(dataMap[widget.id] || {}) };
}

// ═══════════════════════════════════════════════════════════
// NAVIGATION
// ═══════════════════════════════════════════════════════════
function handleWidgetNavigate(item) {
  if (!item?.id) return;
  if (item.device_type !== undefined) {
    router.get(route('services.show', { id: item.id }));
  } else if (item.total !== undefined) {
    router.get(route('keuangan.show', { id: item.id }));
  }
}
</script>
