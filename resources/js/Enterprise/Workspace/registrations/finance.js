import { workspaceRegistry } from '@/Enterprise/Workspace/WorkspaceRegistry.js';
import FinanceOverview from '@/Pages/Finance/sections/Overview.vue';

workspaceRegistry.register('finance', {
  tabs: { overview: FinanceOverview },
  sidebarWidgets: {},
  inspectorSections: {},
  actionHandlers: {
    new_journal(data) { window.dispatchEvent(new CustomEvent('finance:new-journal')); },
    new_coa(data) { window.dispatchEvent(new CustomEvent('finance:new-coa')); },
    new_invoice(data) { window.dispatchEvent(new CustomEvent('finance:new-invoice')); },
    new_payment(data, payload) { fetch('/payment', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': data?.csrf_token || '' }, body: JSON.stringify(payload || {}) }).then(r => r.ok && window.location.reload()); },
    new_expense(data, payload) { fetch('/expenses', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': data?.csrf_token || '' }, body: JSON.stringify(payload || {}) }).then(r => r.ok && window.location.reload()); },
    bank_reconcile(data) { window.dispatchEvent(new CustomEvent('finance:bank-reconcile')); },
    close_period(data) { window.dispatchEvent(new CustomEvent('finance:close-period')); },
    export(data) { window.open('/reports/export', '_blank'); },
  },
  shortcutHandlers: {
    refresh() { window.location.reload(); },
    new() { /* Ctrl+N -> new journal */ },
  },
});

export default workspaceRegistry;
