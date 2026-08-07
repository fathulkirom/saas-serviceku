import { workspaceRegistry } from '@/Enterprise/Workspace/WorkspaceRegistry.js';
import EPOCOverview from '@/Pages/EPOC/sections/Overview.vue';

workspaceRegistry.register('epoc', {
  tabs: { overview: EPOCOverview },
  sidebarWidgets: {},
  inspectorSections: {},
  actionHandlers: {
    run_health_check(data) { fetch('/up', { method: 'GET' }).then(r => r.json()).then(d => window.dispatchEvent(new CustomEvent('epoc:health-result', { detail: d }))); },
    trigger_backup(data) { fetch('/backup/trigger', { method: 'POST', headers: { 'X-CSRF-TOKEN': data?.csrf_token || '' } }).then(r => r.ok && window.location.reload()); },
    deploy(data) { window.dispatchEvent(new CustomEvent('epoc:deploy')); },
    rollback(data) { window.dispatchEvent(new CustomEvent('epoc:rollback')); },
    flush_cache(data) { fetch('/admin/cache/clear', { method: 'POST', headers: { 'X-CSRF-TOKEN': data?.csrf_token || '' } }).then(r => r.ok && window.location.reload()); },
    restart_queue(data) { fetch('/admin/queue/restart', { method: 'POST', headers: { 'X-CSRF-TOKEN': data?.csrf_token || '' } }).then(r => r.ok && window.location.reload()); },
    toggle_maintenance(data) { fetch('/admin/maintenance/toggle', { method: 'POST', headers: { 'X-CSRF-TOKEN': data?.csrf_token || '' } }).then(r => r.ok && window.location.reload()); },
    retry_failed_jobs(data) { fetch('/admin/queue/retry', { method: 'POST', headers: { 'X-CSRF-TOKEN': data?.csrf_token || '' } }).then(r => r.ok && window.location.reload()); },
    generate_report(data) { window.open('/reports/export?type=epoc', '_blank'); },
    export(data) { window.open('/reports/export?type=epoc', '_blank'); },
  },
  shortcutHandlers: { refresh() { window.location.reload(); } },
});

export default workspaceRegistry;
