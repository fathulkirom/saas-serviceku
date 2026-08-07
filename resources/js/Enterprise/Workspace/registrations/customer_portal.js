import { workspaceRegistry } from '@/Enterprise/Workspace/WorkspaceRegistry.js';
import CustomerPortalOverview from '@/Pages/CustomerPortal/sections/Overview.vue';

workspaceRegistry.register('customer_portal', {
  tabs: { overview: CustomerPortalOverview },
  sidebarWidgets: {},
  inspectorSections: {},
  actionHandlers: {
    book_appointment(data, payload) {
      // Open appointment booking drawer/modal
      const event = new CustomEvent('customer:book-appointment', {
        detail: {
          branch_id: payload?.branch_id || null,
          service_type: payload?.service_type || null,
          preferred_date: payload?.preferred_date || null,
        },
      });
      window.dispatchEvent(event);
    },
    create_ticket(data, payload) {
      const customerId = data?.customer?.id;
      if (!customerId) return;
      fetch('/tickets', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': data?.csrf_token || '' },
        body: JSON.stringify({
          customer_id: customerId,
          subject: payload?.subject || '',
          description: payload?.description || '',
          priority: payload?.priority || 'normal',
          category: payload?.category || 'general',
        }),
      }).then(r => r.ok && window.location.reload());
    },
    send_message(data, payload) {
      // Trigger chat/message UI
      const event = new CustomEvent('customer:send-message', {
        detail: { service_id: payload?.service_id || null, topic: payload?.topic || 'general' },
      });
      window.dispatchEvent(event);
    },
    claim_warranty(data, payload) {
      const serviceId = data?.service?.id || payload?.service_id;
      if (!serviceId) return;
      fetch(`/services/${serviceId}/warranty/claim`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': data?.csrf_token || '' },
        body: JSON.stringify({
          claim_reason: payload?.reason || '',
          claim_type: payload?.type || 'service',
        }),
      }).then(r => r.ok && window.location.reload());
    },
    make_payment(data, payload) {
      const serviceId = data?.service?.id || payload?.service_id;
      const invoiceId = data?.invoice?.id || payload?.invoice_id;
      if (!serviceId && !invoiceId) return;
      const endpoint = invoiceId
        ? `/invoices/${invoiceId}/pay`
        : `/services/${serviceId}/payment`;
      fetch(endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': data?.csrf_token || '' },
        body: JSON.stringify({
          amount: payload?.amount || 0,
          method: payload?.method || 'cash',
        }),
      }).then(r => r.ok && window.location.reload());
    },
    download_invoice(data, payload) {
      const invoiceId = data?.invoice?.id || payload?.invoice_id;
      const serviceId = data?.service?.id || payload?.service_id;
      const id = invoiceId || serviceId;
      if (!id) return;
      const endpoint = invoiceId
        ? `/invoices/${invoiceId}/download`
        : `/services/${serviceId}/invoice/download`;
      window.open(endpoint, '_blank');
    },
    edit_profile(data, payload) {
      const customerId = data?.customer?.id;
      if (!customerId) return;
      const event = new CustomEvent('customer:edit-profile', {
        detail: { customer_id: customerId },
      });
      window.dispatchEvent(event);
    },
  },
  shortcutHandlers: { refresh() { window.location.reload(); } },
});

export default workspaceRegistry;
