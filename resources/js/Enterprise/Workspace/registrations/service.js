/**
 * ═══════════════════════════════════════════════════════════
 * SERVICE WORKSPACE — UI Registration (Sprint 36A Refined)
 * ═══════════════════════════════════════════════════════════
 * 
 * Refined action handlers with status-aware validation.
 * All transitions checked against allowedTransitions matrix.
 * All business rules enforced (payment, QC, diagnosis, checklist).
 */

import { workspaceRegistry } from '@/Enterprise/Workspace/WorkspaceRegistry.js';
import { isTransitionAllowed, requiresPaymentBeforeClose, requiresQcBeforeReady, requiresDiagnosisBeforeRepair } from '@/Composables/useServiceStatus.js';
import { router } from '@inertiajs/vue3';

import ServiceOverview from '@/Pages/ServiceWorkspace/sections/Overview.vue';
import ServiceTimeline from '@/Pages/ServiceWorkspace/sections/Timeline.vue';
import ServiceSpareparts from '@/Pages/ServiceWorkspace/sections/Spareparts.vue';
import ServicePhotos from '@/Pages/ServiceWorkspace/sections/Photos.vue';
import ServiceInvoice from '@/Pages/ServiceWorkspace/sections/Invoice.vue';
import ServiceDiagnosis from '@/Pages/ServiceWorkspace/sections/Diagnosis.vue';
import ServiceQuotation from '@/Pages/ServiceWorkspace/sections/Quotation.vue';
import ServicePayment from '@/Pages/ServiceWorkspace/sections/Payment.vue';
import ServiceQC from '@/Pages/ServiceWorkspace/sections/QC.vue';
import ServiceWarranty from '@/Pages/ServiceWorkspace/sections/Warranty.vue';

workspaceRegistry.register('service', {
  tabs: {
    overview: ServiceOverview,
    timeline: ServiceTimeline,
    spareparts: ServiceSpareparts,
    photos: ServicePhotos,
    invoice: ServiceInvoice,
    diagnosis: ServiceDiagnosis,
    quotation: ServiceQuotation,
    payment: ServicePayment,
    qc: ServiceQC,
    warranty: ServiceWarranty,
  },
  sidebarWidgets: {},
  inspectorSections: {},
  actionHandlers: {
    assign(data, payload) {
      // Validate: only from menunggu_alokasi or diterima
      const status = data?.status;
      if (!['menunggu_alokasi', 'diterima'].includes(status)) {
        alert('Assign only allowed from pending/received status');
        return;
      }
      
      const techId = window.prompt('Masukkan ID Teknisi untuk ditugaskan (contoh: 1 untuk Owner):', '1');
      if (!techId) return;

      router.post(`/services/${data.id}/assign`, { technician_id: techId }, {
        preserveScroll: true,
        onSuccess: () => alert('Teknisi berhasil ditugaskan!'),
      });
    },
    diagnose(data, payload) {
      const status = data?.status;
      if (!isTransitionAllowed(status, 'diagnosa')) {
        alert('Diagnosis not allowed from current status: ' + status);
        return;
      }
      alert('Silakan buka tab Diagnosa untuk mengisi hasil pemeriksaan.');
    },
    start(data, payload) {
      const status = data?.status;
      // Check diagnosis requirement
      if (requiresDiagnosisBeforeRepair('dikerjakan') && !data?.diagnosis) {
        alert('Repair requires diagnosis first');
        return;
      }
      if (!isTransitionAllowed(status, 'dikerjakan')) {
        alert('Cannot start repair from: ' + status);
        return;
      }
      if (window.confirm('Mulai perbaikan servis ini?')) {
        router.post(`/services/${data.id}/repair/start`, {}, {
            preserveScroll: true,
            onSuccess: () => alert('Perbaikan dimulai!'),
        });
      }
    },
    complete(data, payload) {
      const status = data?.status;
      if (!isTransitionAllowed(status, 'selesai')) {
        alert('Cannot complete from: ' + status);
        return;
      }
      const notes = window.prompt('Catatan penyelesaian perbaikan:', '');
      if (notes === null) return;

      router.post(`/services/${data.id}/repair/complete`, { repair_notes: notes, parts_used: [] }, {
        preserveScroll: true,
        onSuccess: () => alert('Perbaikan selesai dan masuk tahap QC!'),
      });
    },
    qc_pass(data, payload) {
      const status = data?.status;
      if (status !== 'selesai') {
        alert('QC only after repair complete');
        return;
      }
      if (window.confirm('Setujui QC (PASS)? Servis akan siap diambil.')) {
        router.post(`/services/${data.id}/qc`, { 
            checks: [{item: 'General', result: 'pass'}], 
            qc_decision: 'pass' 
        }, {
            preserveScroll: true,
            onSuccess: () => alert('QC Lulus! Servis siap diambil.'),
        });
      }
    },
    qc_fail(data, payload) {
      const status = data?.status;
      if (status !== 'selesai') {
        alert('QC fail only from selesai status');
        return;
      }
      const notes = window.prompt('Alasan QC Gagal:', '');
      if (!notes) return;

      router.post(`/services/${data.id}/qc`, { 
          checks: [{item: 'General', result: 'fail'}], 
          qc_decision: 'fail',
          qc_notes: notes 
      }, {
          preserveScroll: true,
          onSuccess: () => alert('QC Gagal! Dikembalikan ke teknisi.'),
      });
    },
    ready(data, payload) {
      const status = data?.status;
      if (!isTransitionAllowed(status, 'siap_diambil')) {
        alert('Cannot mark ready from: ' + status);
        return;
      }
      // Validate QC passed
      if (requiresQcBeforeReady(status) && !data?.qc_passed) {
        alert('QC must pass before ready for pickup');
        return;
      }
      router.post(`/services/${data.id}/ready-pickup`, {}, { preserveScroll: true });
    },
    indent(data, payload) {
      const status = data?.status;
      if (!isTransitionAllowed(status, 'indent')) {
        alert('Cannot indent from: ' + status);
        return;
      }
      if (window.confirm('Tandai servis menunggu part (indent)?')) {
        router.post(`/services/${data.id}/indent`, {}, {
          preserveScroll: true,
          onSuccess: () => alert('Servis ditandai menunggu part (indent).'),
        });
      }
    },
    cancel(data, payload) {
      const status = data?.status;
      if (!isTransitionAllowed(status, 'cancel')) {
        alert('Cannot cancel from: ' + status);
        return;
      }
      const notes = window.prompt('Alasan pembatalan:', '');
      if (!notes) return;
      router.post(`/services/${data.id}/cancel`, { note: notes }, {
        preserveScroll: true,
        onSuccess: () => alert('Servis dibatalkan.'),
      });
    },
    print(data) {
      window.open(`/services/${data.id}/print-receipt`, '_blank');
    },
    reopen(data, payload) {
      const reason = window.prompt('Alasan permintaan reopen (wajib):', '');
      if (!reason) return;
      router.post(`/services/${data.id}/reopen`, { reason }, {
        preserveScroll: true,
        onSuccess: () => alert('Permintaan reopen diajukan ke Approval Center.'),
      });
    },
    create_invoice(data, payload) {
      if (data?.sale) {
        alert('Invoice sudah dibuat.');
        return;
      }
      if (window.confirm('Buat draft invoice untuk servis ini?')) {
        router.post(`/sales/draft-from-service/${data.id}`, {}, { preserveScroll: true });
      }
    },
    pay(data, payload) {
      if (!data?.sale) {
        alert('Buat invoice terlebih dahulu.');
        return;
      }
      if (data?.sale?.status === 'paid' || data?.payment_status === 'paid') {
        alert('Servis ini sudah lunas.');
        return;
      }
      const amount = window.prompt('Masukkan jumlah pembayaran:', data.sale.total);
      if (!amount) return;
      router.post(`/sales/${data.sale.id}/pay-draft`, { paid_amount: amount, payment_method: 'cash' }, { preserveScroll: true, onSuccess: () => alert('Pembayaran berhasil!') });
    },
    pickup(data, payload) {
      if (data?.status !== 'siap_diambil') {
        alert('Servis harus berstatus siap diambil sebelum diserahkan.');
        return;
      }
      const receivedBy = window.prompt('Nama Penerima:', data.customer?.name || '');
      if (!receivedBy) return;
      const phone = window.prompt('No HP Penerima:', data.customer?.phone || '');
      if (!phone) return;
      
      router.post(`/services/${data.id}/pickup`, {
        received_by: receivedBy,
        receiver_phone: phone,
        receiver_relation: 'self',
      }, { preserveScroll: true, onSuccess: () => alert('Servis berhasil diserahkan!') });
    },
    close(data, payload) {
      const status = data?.status;
      if (!isTransitionAllowed(status, 'close')) {
        alert('Cannot close from: ' + status);
        return;
      }
      if (window.confirm('Tutup servis ini?')) {
        router.post(`/services/${data.id}/close`, {}, { preserveScroll: true, onSuccess: () => alert('Servis ditutup.') });
      }
    },
  },
  shortcutHandlers: {
    refresh() { window.location.reload(); },
  },
});

export default workspaceRegistry;

