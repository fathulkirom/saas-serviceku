import { workspaceRegistry } from '@/Enterprise/Workspace/WorkspaceRegistry.js';
import WarehouseOverview from '@/Pages/Warehouse/sections/Overview.vue';

workspaceRegistry.register('warehouse', {
  tabs: { overview: WarehouseOverview },
  sidebarWidgets: {},
  inspectorSections: {},
  actionHandlers: {
    create_warehouse(data) { window.dispatchEvent(new CustomEvent('warehouse:create')); },
    receive_goods(data) { window.dispatchEvent(new CustomEvent('warehouse:receive')); },
    start_picking(data) { window.dispatchEvent(new CustomEvent('warehouse:picking')); },
    start_packing(data) { window.dispatchEvent(new CustomEvent('warehouse:packing')); },
    create_shipment(data) { window.dispatchEvent(new CustomEvent('warehouse:shipment')); },
    request_transfer(data) { fetch('/technician-stock/transfer', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': data?.csrf_token || '' }, body: JSON.stringify(data || {}) }).then(r => r.ok && window.location.reload()); },
    schedule_cycle_count(data) { fetch('/stock-opnames', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': data?.csrf_token || '' }, body: JSON.stringify(data || {}) }).then(r => r.ok && window.location.reload()); },
    export(data) { window.open('/reports/export?type=warehouse', '_blank'); },
  },
  shortcutHandlers: {
    refresh() { window.location.reload(); },
    new() { /* Ctrl+N -> new warehouse */ },
  },
});

export default workspaceRegistry;
