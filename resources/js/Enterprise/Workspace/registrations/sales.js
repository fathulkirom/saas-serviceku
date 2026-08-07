import { workspaceRegistry } from '@/Enterprise/Workspace/WorkspaceRegistry.js';
import SalesOverview from '@/Pages/Sales/sections/Overview.vue';

workspaceRegistry.register('sales', {
  tabs: { overview: SalesOverview },
  sidebarWidgets: {},
  inspectorSections: {},
  actionHandlers: {
    new_sale(data) { window.dispatchEvent(new CustomEvent('pos:new-sale')); },
    new_quote(data) { window.dispatchEvent(new CustomEvent('pos:new-quote')); },
    add_payment(data, payload) { fetch(`/sales/${payload?.sale_id}/pay`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': data?.csrf_token || '' }, body: JSON.stringify(payload || {}) }).then(r => r.ok && window.location.reload()); },
    add_delivery(data, payload) { fetch(`/pickup-deliveries`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': data?.csrf_token || '' }, body: JSON.stringify(payload || {}) }).then(r => r.ok && window.location.reload()); },
    process_return(data, payload) { fetch(`/purchase-returns`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': data?.csrf_token || '' }, body: JSON.stringify(payload || {}) }).then(r => r.ok && window.location.reload()); },
    apply_promo(data, payload) { fetch(`/sales/${payload?.sale_id}/apply-promo`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': data?.csrf_token || '' }, body: JSON.stringify(payload || {}) }).then(r => r.ok && window.location.reload()); },
    export(data) { window.open('/sales/export', '_blank'); },
  },
  shortcutHandlers: {
    refresh() { window.location.reload(); },
    new() { /* Ctrl+N -> new sale */ },
  },
});

export default workspaceRegistry;
