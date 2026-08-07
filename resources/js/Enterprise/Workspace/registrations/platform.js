import { workspaceRegistry } from '@/Enterprise/Workspace/WorkspaceRegistry.js';
import PlatformOverview from '@/Pages/Platform/sections/Overview.vue';

workspaceRegistry.register('platform', {
  tabs: { overview: PlatformOverview },
  sidebarWidgets: {},
  inspectorSections: {},
  actionHandlers: {
    create_tenant(data) { window.dispatchEvent(new CustomEvent('platform:create-tenant')); },
    create_plan(data) { window.dispatchEvent(new CustomEvent('platform:create-plan')); },
    manage_license(data) { window.dispatchEvent(new CustomEvent('platform:manage-license')); },
    toggle_feature(data) { window.dispatchEvent(new CustomEvent('platform:toggle-feature')); },
    run_backup(data) { fetch('/backup/trigger', { method: 'POST', headers: { 'X-CSRF-TOKEN': data?.csrf_token || '' } }).then(r => r.ok && window.location.reload()); },
    view_audit(data) { window.dispatchEvent(new CustomEvent('platform:view-audit')); },
    maintenance_mode(data) { fetch('/admin/maintenance/toggle', { method: 'POST', headers: { 'X-CSRF-TOKEN': data?.csrf_token || '' } }).then(r => r.ok && window.location.reload()); },
    export(data) { window.open('/reports/export?type=platform', '_blank'); },
  },
  shortcutHandlers: {
    refresh() { window.location.reload(); },
  },
});

export default workspaceRegistry;
