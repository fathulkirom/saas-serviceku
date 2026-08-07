import { workspaceRegistry } from '@/Enterprise/Workspace/WorkspaceRegistry.js';
import WorkflowOverview from '@/Pages/Workflow/sections/Overview.vue';

workspaceRegistry.register('workflow', {
  tabs: { overview: WorkflowOverview },
  sidebarWidgets: {},
  inspectorSections: {},
  actionHandlers: {
    create_workflow(data) { window.dispatchEvent(new CustomEvent('workflow:create')); },
    approve(data) { window.dispatchEvent(new CustomEvent('workflow:approve', { detail: data })); },
    reject(data) { window.dispatchEvent(new CustomEvent('workflow:reject', { detail: data })); },
    escalate(data) { window.dispatchEvent(new CustomEvent('workflow:escalate', { detail: data })); },
    delegate(data) { window.dispatchEvent(new CustomEvent('workflow:delegate', { detail: data })); },
    define_sla(data) { window.dispatchEvent(new CustomEvent('workflow:define-sla')); },
    create_business_rule(data) { window.dispatchEvent(new CustomEvent('workflow:create-rule')); },
    export(data) { window.open('/reports/export?type=workflow', '_blank'); },
  },
  shortcutHandlers: { refresh() { window.location.reload(); } },
});

export default workspaceRegistry;
