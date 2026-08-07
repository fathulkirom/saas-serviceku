import { workspaceRegistry } from '@/Enterprise/Workspace/WorkspaceRegistry.js';
import TechnicianPortalOverview from '@/Pages/TechnicianPortal/sections/Overview.vue';

workspaceRegistry.register('technician_portal', {
  tabs: {
    overview: TechnicianPortalOverview,
  },
  sidebarWidgets: {},
  inspectorSections: {},
  actionHandlers: {
    start_job(data, payload) {
      const serviceId = data?.service?.id || payload?.service_id;
      if (!serviceId) return;
      fetch(`/services/${serviceId}/repair/start`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': data?.csrf_token || '' },
      }).then(r => r.ok && window.location.reload());
    },
    pause_job(data, payload) {
      const woId = data?.work_order?.id || payload?.work_order_id;
      if (!woId) return;
      fetch(`/work-orders/${woId}/pause`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': data?.csrf_token || '' },
      }).then(r => r.ok && window.location.reload());
    },
    resume_job(data, payload) {
      const woId = data?.work_order?.id || payload?.work_order_id;
      if (!woId) return;
      fetch(`/work-orders/${woId}/resume`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': data?.csrf_token || '' },
      }).then(r => r.ok && window.location.reload());
    },
    finish_job(data, payload) {
      const serviceId = data?.service?.id || payload?.service_id;
      const woId = data?.work_order?.id || payload?.work_order_id;
      if (!serviceId && !woId) return;
      const endpoint = serviceId
        ? `/services/${serviceId}/repair/complete`
        : `/work-orders/${woId}/finish`;
      fetch(endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': data?.csrf_token || '' },
        body: JSON.stringify({ note: payload?.note || '', actual_minutes: payload?.actual_minutes || 0 }),
      }).then(r => r.ok && window.location.reload());
    },
    upload_photo(data, payload) {
      const event = new CustomEvent('technician:upload-photo', { detail: { category: payload?.category || 'repair' } });
      window.dispatchEvent(event);
    },
    add_diagnosis(data, payload) {
      const serviceId = data?.service?.id || payload?.service_id;
      if (!serviceId) return;
      fetch(`/services/${serviceId}/diagnosis`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': data?.csrf_token || '' },
        body: JSON.stringify({
          findings: payload?.findings || '',
          cause: payload?.cause || '',
          solution: payload?.solution || '',
          estimated_cost: payload?.estimated_cost || 0,
          estimated_minutes: payload?.estimated_minutes || 0,
        }),
      }).then(r => r.ok && window.location.reload());
    },
    request_parts(data, payload) {
      const serviceId = data?.service?.id || payload?.service_id;
      if (!serviceId) return;
      fetch(`/services/${serviceId}/parts`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': data?.csrf_token || '' },
        body: JSON.stringify({
          product_id: payload?.product_id,
          part_name: payload?.part_name || '',
          qty: payload?.qty || 1,
          priority: payload?.priority || 'normal',
        }),
      }).then(r => r.ok && window.location.reload());
    },
    request_approval(data, payload) {
      const serviceId = data?.service?.id || payload?.service_id;
      if (!serviceId) return;
      fetch(`/services/${serviceId}/quotation`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': data?.csrf_token || '' },
        body: JSON.stringify({
          total_cost: payload?.total_cost || 0,
          items: payload?.items || [],
          note: payload?.note || '',
        }),
      }).then(r => r.ok && window.location.reload());
    },
    get_signature(data, payload) {
      const event = new CustomEvent('technician:capture-signature', { detail: {} });
      window.dispatchEvent(event);
    },
    escalate(data, payload) {
      const serviceId = data?.service?.id || payload?.service_id;
      if (!serviceId) return;
      fetch(`/services/${serviceId}/workspace/transition`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': data?.csrf_token || '' },
        body: JSON.stringify({
          status: 'menunggu_konfirmasi_internal',
          note: payload?.reason || 'Escalated by technician',
        }),
      }).then(r => r.ok && window.location.reload());
    },
  },
  shortcutHandlers: { refresh() { window.location.reload(); } },
});

export default workspaceRegistry;
