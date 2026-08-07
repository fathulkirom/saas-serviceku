import { workspaceRegistry } from '@/Enterprise/Workspace/WorkspaceRegistry.js';
import AssetOverview from '@/Pages/Asset/sections/Overview.vue';

workspaceRegistry.register('asset', {
  tabs: { overview: AssetOverview },
  sidebarWidgets: {},
  inspectorSections: {},
  actionHandlers: {
    edit(data) { window.dispatchEvent(new CustomEvent('asset:edit', { detail: data })); },
    schedule_maintenance(data) { window.dispatchEvent(new CustomEvent('asset:schedule-maintenance')); },
    record_maintenance(data) { window.dispatchEvent(new CustomEvent('asset:record-maintenance')); },
    transfer(data) { window.dispatchEvent(new CustomEvent('asset:transfer')); },
    assign(data) { window.dispatchEvent(new CustomEvent('asset:assign')); },
    post_depreciation(data) { window.dispatchEvent(new CustomEvent('asset:post-depreciation')); },
    dispose(data) { window.dispatchEvent(new CustomEvent('asset:dispose')); },
    export(data) { window.open('/reports/export?type=asset', '_blank'); },
  },
  shortcutHandlers: {
    refresh() { window.location.reload(); },
    new() { /* Ctrl+N -> new asset */ },
  },
});

export default workspaceRegistry;
