import { workspaceRegistry } from '@/Enterprise/Workspace/WorkspaceRegistry.js';
import ManufacturingOverview from '@/Pages/Manufacturing/sections/Overview.vue';

workspaceRegistry.register('manufacturing', {
  tabs: { overview: ManufacturingOverview },
  sidebarWidgets: {},
  inspectorSections: {},
  actionHandlers: {
    new_production(data) { window.dispatchEvent(new CustomEvent('mfg:new-production')); },
    start_production(data) { window.dispatchEvent(new CustomEvent('mfg:start-production')); },
    record_output(data) { window.dispatchEvent(new CustomEvent('mfg:record-output')); },
    qc_inspection(data) { window.dispatchEvent(new CustomEvent('mfg:qc-inspection')); },
    material_request(data) { window.dispatchEvent(new CustomEvent('mfg:material-request')); },
    update_routing(data) { window.dispatchEvent(new CustomEvent('mfg:update-routing')); },
    close_order(data) { window.dispatchEvent(new CustomEvent('mfg:close-order')); },
    export(data) { window.open('/reports/export?type=manufacturing', '_blank'); },
  },
  shortcutHandlers: {
    refresh() { window.location.reload(); },
    new() { /* Ctrl+N -> new production order */ },
  },
});

export default workspaceRegistry;
