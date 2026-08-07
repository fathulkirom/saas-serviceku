import { workspaceRegistry } from '@/Enterprise/Workspace/WorkspaceRegistry.js';
import EmployeeOverview from '@/Pages/Employee/sections/Overview.vue';

workspaceRegistry.register('employee', {
  tabs: { overview: EmployeeOverview },
  sidebarWidgets: {},
  inspectorSections: {},
  actionHandlers: {
    edit(data) { window.dispatchEvent(new CustomEvent('employee:edit', { detail: { id: data?.employee?.id } })); },
    record_attendance(data) { fetch('/attendances', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': data?.csrf_token || '' }, body: JSON.stringify({ user_id: data?.employee?.id }) }).then(r => r.ok && window.location.reload()); },
    apply_leave(data) { window.dispatchEvent(new CustomEvent('employee:apply-leave', { detail: { user_id: data?.employee?.id } })); },
    add_training(data) { window.dispatchEvent(new CustomEvent('employee:add-training')); },
    start_review(data) { window.dispatchEvent(new CustomEvent('employee:start-review')); },
    assign_asset(data) { window.dispatchEvent(new CustomEvent('employee:assign-asset')); },
    export(data) { window.open('/reports/export?type=employee', '_blank'); },
  },
  shortcutHandlers: {
    refresh() { window.location.reload(); },
    new() { /* Ctrl+N -> new employee */ },
  },
});

export default workspaceRegistry;
