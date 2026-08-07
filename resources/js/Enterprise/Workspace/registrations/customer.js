import { workspaceRegistry } from '@/Enterprise/Workspace/WorkspaceRegistry.js';
import CustomerOverview from '@/Pages/Customer/sections/Overview.vue';

workspaceRegistry.register('customer', {
  tabs: { overview: CustomerOverview },
  sidebarWidgets: {},
  inspectorSections: {},
  actionHandlers: {
    new_service(data) { window.location.href = route('services.create', { customer_id: data?.customer?.id }); },
    send_wa(data) { alert('Fungsi WA belum diimplementasikan untuk ' + data?.customer?.phone); },
    add_note(data) { alert('Fungsi add note belum diimplementasikan'); },
  },
  shortcutHandlers: { refresh() { window.location.reload(); } },
});

export default workspaceRegistry;
