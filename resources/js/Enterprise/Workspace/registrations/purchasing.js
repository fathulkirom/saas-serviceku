import { workspaceRegistry } from '@/Enterprise/Workspace/WorkspaceRegistry.js';
import PurchasingOverview from '@/Pages/Purchasing/sections/Overview.vue';

workspaceRegistry.register('purchasing', {
  tabs: { overview: PurchasingOverview },
  sidebarWidgets: {},
  inspectorSections: {},
  actionHandlers: {
    approve(data, payload) { fetch(`/purchases/${payload?.id}/approve`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': data?.csrf_token || '' } }).then(r => r.ok && window.location.reload()); },
    reject(data, payload) { fetch(`/purchases/${payload?.id}/reject`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': data?.csrf_token || '' }, body: JSON.stringify({ reason: payload?.reason || '' }) }).then(r => r.ok && window.location.reload()); },
    send_po(data, payload) { fetch(`/purchases/${payload?.id}/send`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': data?.csrf_token || '' } }).then(r => r.ok && window.location.reload()); },
    receive(data, payload) { fetch(`/purchases/${payload?.id}/receive`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': data?.csrf_token || '' }, body: JSON.stringify(payload || {}) }).then(r => r.ok && window.location.reload()); },
  },
  shortcutHandlers: { refresh() { window.location.reload(); } },
});

export default workspaceRegistry;
