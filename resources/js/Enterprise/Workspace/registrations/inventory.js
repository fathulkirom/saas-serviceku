/**
 * INVENTORY WORKSPACE — UI Registration
 */
import { workspaceRegistry } from '@/Enterprise/Workspace/WorkspaceRegistry.js';
import InventoryOverview from '@/Pages/Inventory/sections/Overview.vue';

workspaceRegistry.register('inventory', {
  tabs: {
    overview: InventoryOverview,
    // Additional sections can be added as they are built
    // movement, purchase, sales, service, transfer, supplier, pricing, serial, documents
  },
  sidebarWidgets: {},
  inspectorSections: {},
  actionHandlers: {
    add_stock(data, payload) {
      fetch('/products', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': data?.csrf_token || '' }, body: JSON.stringify(payload || {}) }).then(r => r.ok && window.location.reload());
    },
    adjust(data, payload) {
      fetch('/inventory/adjustments', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': data?.csrf_token || '' }, body: JSON.stringify(payload || {}) }).then(r => r.ok && window.location.reload());
    },
    transfer(data, payload) {
      fetch('/technician-stock/transfer', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': data?.csrf_token || '' }, body: JSON.stringify(payload || {}) }).then(r => r.ok && window.location.reload());
    },
    opname(data, payload) {
      fetch('/stock-opnames', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': data?.csrf_token || '' }, body: JSON.stringify(payload || {}) }).then(r => r.ok && window.location.reload());
    },
  },
  shortcutHandlers: {
    refresh() { window.location.reload(); },
  },
});

export default workspaceRegistry;
