import { workspaceRegistry } from '@/Enterprise/Workspace/WorkspaceRegistry.js';
import GRCOverview from '@/Pages/GRC/sections/Overview.vue';

workspaceRegistry.register('grc', {
  tabs: { overview: GRCOverview },
  sidebarWidgets: {},
  inspectorSections: {},
  actionHandlers: {
    create_risk(data) { window.dispatchEvent(new CustomEvent('grc:create-risk')); },
    create_audit(data) { window.dispatchEvent(new CustomEvent('grc:create-audit')); },
    create_finding(data) { window.dispatchEvent(new CustomEvent('grc:create-finding')); },
    create_capa(data) { window.dispatchEvent(new CustomEvent('grc:create-capa')); },
    report_incident(data) { window.dispatchEvent(new CustomEvent('grc:report-incident')); },
    assess_risk(data) { window.dispatchEvent(new CustomEvent('grc:assess-risk')); },
    generate_report(data) { window.open('/reports/export?type=grc', '_blank'); },
    export(data) { window.open('/reports/export?type=grc', '_blank'); },
  },
  shortcutHandlers: { refresh() { window.location.reload(); } },
});

export default workspaceRegistry;
