import { workspaceRegistry } from '@/Enterprise/Workspace/WorkspaceRegistry.js';
import SupplierOverview from '@/Pages/Supplier/sections/Overview.vue';

workspaceRegistry.register('supplier', {
  tabs: { overview: SupplierOverview },
  sidebarWidgets: {},
  inspectorSections: {},
  actionHandlers: {
    new_po(data, payload) { window.dispatchEvent(new CustomEvent('purchasing:new-po', { detail: { supplier_id: payload?.supplier_id || data?.supplier?.id } })); },
    contact(data, payload) { window.dispatchEvent(new CustomEvent('supplier:contact', { detail: { supplier_id: payload?.supplier_id || data?.supplier?.id } })); },
  },
  shortcutHandlers: { refresh() { window.location.reload(); } },
});

export default workspaceRegistry;
