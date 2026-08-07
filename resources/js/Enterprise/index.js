/**
 * ═══════════════════════════════════════════════════════════
 * SERVICEKU ENTERPRISE DESIGN SYSTEM — Barrel Export
 * ═══════════════════════════════════════════════════════════
 *
 * Import semua komponen enterprise dari satu entry point.
 *
 * Usage:
 *   import { SkCard, SkDataTable, SkModal, useShortcut } from '@/Enterprise'
 */

// ── Typography ──
export { default as SkHeading } from './Components/Typography/Heading.vue';
export { default as SkText } from './Components/Typography/Text.vue';

// ── Cards ──
export { default as SkCard } from './Components/Cards/Card.vue';
export { default as SkMetricCard } from './Components/Cards/MetricCard.vue';
export { default as SkWidgetCard } from './Components/Cards/WidgetCard.vue';

// ── Table ──
export { default as SkDataTable } from './Components/Table/DataTable.vue';

// ── Form ──
export { default as SkFloatingInput } from './Components/Form/FloatingInput.vue';
export { default as SkAutocomplete } from './Components/Form/Autocomplete.vue';
export { default as SkSwitch } from './Components/Form/Switch.vue';
export { default as SkCurrencyInput } from './Components/Form/CurrencyInput.vue';
export { default as SkFileUpload } from './Components/Form/FileUpload.vue';

// ── Overlay ──
export { default as SkDrawer } from './Components/Overlay/Drawer.vue';
export { default as SkModal } from './Components/Overlay/Modal.vue';

// ── Feedback ──
export { default as SkLoading } from './Components/Feedback/Loading.vue';

// ── Empty ──
export { default as SkEmptyState } from './Components/Empty/EmptyState.vue';

// ── Navigation ──
export { default as SkBreadcrumb } from './Components/Navigation/Breadcrumb.vue';
export { default as SkFavorites } from './Components/Navigation/Favorites.vue';

// ── Dashboard ──
export { default as SkWidgetGrid } from './Components/Dashboard/WidgetGrid.vue';
export { default as DashboardTopBar } from './Dashboard/TopBar.vue';
export { default as DashboardQuickActions } from './Dashboard/QuickActions.vue';
export { registry as dashboardRegistry } from './Dashboard/DashboardWidgetRegistry.js';

// ── Workspace Engine (Sprint 10.0) ──
export { workspaceRegistry } from './Workspace/WorkspaceRegistry.js';
export { useWorkspace } from './Workspace/composables/useWorkspace.js';

// ── Universal Workspace Components (Sprint v1.0 Production) ──
export { default as WorkspaceShell } from './Workspace/components/WorkspaceShell.vue';
export { default as UniversalSidebar } from './Workspace/components/UniversalSidebar.vue';
export { default as UniversalInspector } from './Workspace/components/UniversalInspector.vue';
export { default as UniversalTimeline } from './Workspace/components/UniversalTimeline.vue';
export { default as UniversalFooter } from './Workspace/components/UniversalFooter.vue';
export { default as UniversalRelations } from './Workspace/components/UniversalRelations.vue';

// ── Form Engine (Sprint 11.0) ──
export { fieldRegistry, formRegistry } from './Form/FormRegistry.js';
export { useForm } from './Form/composables/useForm.js';
export { default as FormRenderer } from './Form/FormRenderer.vue';
export { default as FormField } from './Form/FormField.vue';
export { default as FormSection } from './Form/FormSection.vue';
export { default as FormToolbar } from './Form/FormToolbar.vue';

// ── Data Engine (Sprint 12.0) ──
export { columnRendererRegistry, dataRegistry } from './Data/DataRegistry.js';
export { useDataTable } from './Data/composables/useDataTable.js';
export { default as EnterpriseDataTable } from './Data/DataTable.vue';

// ── Automation Engine (Sprint 13.0) ──
export { automationRegistry } from './Automation/AutomationRegistry.js';
export { default as AutomationBuilder } from './Automation/AutomationBuilder.vue';

// ── Reporting Engine (Sprint 14.0) ──
export { reportRegistry } from './Reporting/ReportRegistry.js';
export { default as ReportViewer } from './Reporting/ReportViewer.vue';
export { default as KPIGrid } from './Reporting/KPIGrid.vue';
export { default as ChartViewer } from './Reporting/ChartViewer.vue';

// ── Composables ──
export { useShortcut, SHORTCUTS } from './Composables/useShortcut.js';
export { useBreakpoint } from './Composables/useBreakpoint.js';
export { useTheme } from './Composables/useTheme.js';
