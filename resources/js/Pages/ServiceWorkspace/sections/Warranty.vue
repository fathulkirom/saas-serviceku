<template>
  <SkCard title="Garansi" size="md">
    <div class="space-y-4">
      <!-- Warranty Status -->
      <div class="flex items-center justify-between">
        <span class="sk-label-sm">Status Garansi</span>
        <span class="text-xs font-bold px-3 py-1 rounded-full" :style="warrantyStatusStyle">
          {{ warrantyStatusLabel }}
        </span>
      </div>

      <!-- Warranty Info Grid -->
      <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
        <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
          <p class="text-[10px] uppercase tracking-wider mb-1" :style="{ color: 'var(--text-muted)' }">Durasi</p>
          <p class="text-sm font-semibold" :style="{ color: 'var(--text-primary)' }">
            {{ service.warranty_days || 0 }} hari
          </p>
        </div>
        <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
          <p class="text-[10px] uppercase tracking-wider mb-1" :style="{ color: 'var(--text-muted)' }">Berakhir</p>
          <p class="text-sm font-semibold" :style="{ color: remainingDays > 7 ? 'var(--success-text)' : 'var(--danger-text)' }">
            {{ remainingDaysText }}
          </p>
        </div>
        <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
          <p class="text-[10px] uppercase tracking-wider mb-1" :style="{ color: 'var(--text-muted)' }">Tipe</p>
          <p class="text-sm font-semibold" :style="{ color: 'var(--text-primary)' }">
            {{ service.is_warranty_claim ? 'Klaim' : 'Garansi Servis' }}
          </p>
        </div>
      </div>

      <!-- Remaining Days Progress -->
      <div v-if="service.warranty_expired_at && remainingDays > 0">
        <div class="flex items-center justify-between mb-1.5">
          <span class="text-[10px]" :style="{ color: 'var(--text-muted)' }">Sisa Garansi</span>
          <span class="text-xs font-semibold" :style="{ color: 'var(--text-secondary)' }">{{ remainingDays }} hari</span>
        </div>
        <div class="h-2 rounded-full overflow-hidden" :style="{ background: 'var(--bg-hover)' }">
          <div class="h-full rounded-full transition-all duration-500"
            :style="{ width: warrantyProgress + '%', background: warrantyBarColor }"></div>
        </div>
      </div>

      <!-- Warranty Claims History (BR-FIX-04: real claim rows + linked rework) -->
      <div v-if="claims?.length" class="pt-3 border-t" :style="{ borderColor: 'var(--border-light)' }">
        <p class="sk-section-title mb-3">Klaim Garansi</p>
        <div class="space-y-2">
          <div v-for="claim in claims" :key="claim.id"
            class="px-3 py-2 rounded-lg" :style="{ background: 'var(--bg-hover)' }">
            <div class="flex items-center justify-between">
              <p class="text-xs font-semibold" :style="{ color: 'var(--text-primary)' }">
                {{ claim.claim_number || ('#' + (claim.id || claim.tracking_code)) }}
              </p>
              <span class="text-[10px] px-2 py-0.5 rounded-full" :style="claimStatusStyle(claim.status)">
                {{ claim.status_label || claim.status }}
              </span>
            </div>
            <p class="sk-caption mt-1">{{ claim.problem_description }}</p>
            <div class="flex items-center justify-between mt-1">
              <span class="text-[10px]" :style="{ color: 'var(--text-muted)' }">
                {{ claim.branch_name ? claim.branch_name + ' · ' : '' }}{{ formatDate(claim.created_at) }}
              </span>
              <span v-if="claim.rework" class="text-[10px]" :style="{ color: 'var(--info-text)' }">
                Rework: {{ claim.rework.tracking_code || ('#' + claim.rework.id) }} ({{ claim.rework.status }})
              </span>
            </div>
            <p v-if="claim.resolution_note" class="sk-caption mt-1" :style="{ color: 'var(--text-muted)' }">
              Resolusi: {{ claim.resolution_note }}
            </p>
            <div v-if="decideError" class="mt-1 text-[10px] font-semibold" :style="{ color: 'var(--danger-text)' }">
              {{ decideError }}
            </div>
            <div v-if="canDecide && isDecidable(claim)" class="mt-2 flex items-center justify-end gap-2">
              <button type="button" :disabled="!!decideBusy" @click="decideClaim(claim, 'reject')"
                class="text-[10px] font-bold px-3 py-1 rounded-lg transition-colors disabled:opacity-50"
                :style="{ background: 'var(--danger-soft)', color: 'var(--danger-text)' }">
                {{ decideBusy === claim.id + ':reject' ? 'Memproses...' : 'Tolak' }}
              </button>
              <button type="button" :disabled="!!decideBusy" @click="decideClaim(claim, 'approve')"
                class="text-[10px] font-bold px-3 py-1 rounded-lg transition-colors disabled:opacity-50"
                :style="{ background: 'var(--success-soft)', color: 'var(--success-text)' }">
                {{ decideBusy === claim.id + ':approve' ? 'Memproses...' : 'Setujui' }}
              </button>
            </div>
            <div v-if="canRefundClaim(claim)" class="mt-2 flex justify-end">
              <button type="button" @click="openRefund(claim)"
                class="text-[10px] font-bold px-3 py-1 rounded-lg transition-colors"
                :style="{ background: 'var(--danger-soft)', color: 'var(--danger-text)' }">
                Refund
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Upstream Supplier/Distributor Warranty (BR-FIX-04 / BR-013) -->
      <div v-if="upstreamWarranty?.length" class="pt-3 border-t" :style="{ borderColor: 'var(--border-light)' }">
        <p class="sk-section-title mb-3">Garansi Upstream (Supplier/Distributor)</p>
        <div class="space-y-2">
          <div v-for="(up, i) in upstreamWarranty" :key="i"
            class="flex items-center justify-between px-3 py-2 rounded-lg"
            :style="{ background: 'var(--bg-hover)' }">
            <div>
              <p class="text-xs font-semibold" :style="{ color: 'var(--text-primary)' }">{{ up.part_name }}</p>
              <p class="sk-caption">{{ up.supplier_name || 'Supplier' }} · {{ up.warranty_type }}</p>
            </div>
            <span class="text-[10px] font-bold" :style="up.status === 'active' ? { color: 'var(--success-text)' } : { color: 'var(--text-muted)' }">
              {{ up.status === 'active' ? 'Aktif' : up.status }}
            </span>
          </div>
        </div>
      </div>

      <!-- Expired Notice -->
      <div v-if="remainingDays <= 0 && service.warranty_expired_at"
        class="p-3 rounded-xl text-center"
        :style="{ background: 'var(--danger-soft)', border: '1px solid var(--danger-soft-border)' }">
        <p class="text-xs font-bold" :style="{ color: 'var(--danger-text)' }">⚠ Garansi telah berakhir</p>
        <p class="text-[10px] mt-1" :style="{ color: 'var(--danger-text)' }">
          Berakhir pada {{ formatDate(service.warranty_expired_at) }}
        </p>
      </div>
    </div>
  </SkCard>

  <!-- BR-FIX-04.1: Refund modal (authorized users only) -->
  <Modal :open="refundModalOpen" title="Refund Garansi" subtitle="Pengembalian dana tercatat sebagai event finansial terpisah (nota asli tetap utuh)."
    size="md" @close="refundModalOpen = false">
    <div class="p-5 space-y-4">
      <div v-if="refundTarget" class="rounded-xl px-3 py-2" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-xs font-semibold" :style="{ color: 'var(--text-primary)' }">
          Klaim {{ refundTarget.claim_number }}
        </p>
        <p class="sk-caption">Sisa yang dapat direfund: <b>Rp {{ formatCurrency(refundTarget.refundable) }}</b></p>
      </div>
      <div class="space-y-1">
        <label class="text-xs font-semibold" :style="{ color: 'var(--text-muted)' }">Jumlah Refund *</label>
        <input type="number" min="0.01" :max="refundTarget?.refundable ?? 0" step="0.01" v-model="refundForm.amount"
          class="w-full px-3 py-2 rounded-lg border text-sm" :style="{ borderColor: 'var(--border-light)', color: 'var(--text-primary)' }" />
      </div>
      <div class="space-y-1">
        <label class="text-xs font-semibold" :style="{ color: 'var(--text-muted)' }">Metode</label>
        <select v-model="refundForm.method" class="w-full px-3 py-2 rounded-lg border text-sm"
          :style="{ borderColor: 'var(--border-light)', color: 'var(--text-primary)' }">
          <option value="cash">Cash</option>
          <option value="transfer">Transfer</option>
          <option value="qris">QRIS</option>
        </select>
      </div>
      <div class="space-y-1">
        <label class="text-xs font-semibold" :style="{ color: 'var(--text-muted)' }">Alasan *</label>
        <textarea v-model="refundForm.reason" rows="2" placeholder="Alasan refund"
          class="w-full px-3 py-2 rounded-lg border text-sm" :style="{ borderColor: 'var(--border-light)', color: 'var(--text-primary)' }"></textarea>
      </div>
      <div class="flex items-center justify-end gap-2 pt-1">
        <button type="button" @click="refundModalOpen = false" class="px-4 py-2 rounded-lg text-xs font-semibold"
          :style="{ background: 'var(--bg-hover)', color: 'var(--text-secondary)' }">Batal</button>
        <button type="button" @click="submitRefund" :disabled="refundForm.processing"
          class="px-4 py-2 rounded-lg text-xs font-bold text-white transition-colors"
          :style="{ background: 'var(--danger)', opacity: refundForm.processing ? 0.6 : 1 }">
          {{ refundForm.processing ? 'Memproses...' : 'Konfirmasi Refund' }}
        </button>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { computed, ref } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import SkCard from '@/Enterprise/Components/Cards/Card.vue';
import Modal from '@/Enterprise/Components/Overlay/Modal.vue';
import { useFormatter } from '@/Composables/useFormatter.js';

const props = defineProps({
  service: { type: Object, default: () => ({}) },
  warrantyClaims: { type: Array, default: () => [] },
});

const page = usePage();
const { formatDate, formatCurrency } = useFormatter();

// BR-FIX-04: prefer real backend claim rows (with rework link); fall back to
// the legacy child-service list when present.
const claims = computed(() =>
  (props.service?.warranty_claims?.length ? props.service.warranty_claims : props.warrantyClaims) ?? []
);

const upstreamWarranty = computed(() => props.service?.upstream_warranty ?? []);
const canRefund = computed(() => props.service?.can_refund === true);

// PILOT-READY-01: claim approve/reject (warranty-claims.decide). Authority
// mirrors the backend — finance-capable operational roles. Backend re-validates.
const canDecide = computed(() =>
  ['owner', 'admin', 'manager', 'head_store'].includes(page.props.auth?.user?.role)
);
const isDecidable = (claim) => ['submitted', 'checking'].includes(claim?.status);

const decideBusy = ref(null);
const decideError = ref('');

const decideClaim = (claim, decision) => {
  if (!claim?.id) return;
  let note = '';
  if (decision === 'reject') {
    note = window.prompt('Alasan penolakan klaim garansi:', '');
    if (!note) return;
  }
  decideBusy.value = claim.id + ':' + decision;
  decideError.value = '';
  router.post(route('warranty-claims.decide', claim.id), {
    decision,
    note,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      decideBusy.value = null;
      router.reload({ only: ['service'] });
    },
    onError: () => {
      decideBusy.value = null;
      decideError.value = 'Gagal memproses klaim.';
    },
  });
};

// BR-FIX-04.1: Refund action — shown only when authorized, has a refundable
// balance, and the claim is in a refundable state (backend re-validates).
const canRefundClaim = (claim) => {
  if (!canRefund.value || !claim?.sale_id) return false;
  if (!(claim.refundable > 0)) return false;
  return ['submitted', 'checking', 'approved', 'repairing', 'completed'].includes(claim.status);
};

const refundModalOpen = ref(false);
const refundTarget = ref(null);
const refundForm = useForm({ amount: '', method: 'cash', reason: '' });

const openRefund = (claim) => {
  refundTarget.value = claim;
  refundForm.reset();
  refundForm.amount = String(claim.refundable || '');
  refundForm.method = 'cash';
  refundModalOpen.value = true;
};

const submitRefund = () => {
  if (!refundTarget.value) return;
  refundForm.post(route('warranty-claims.refund', refundTarget.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      refundModalOpen.value = false;
      refundTarget.value = null;
      router.reload({ only: ['service'] }); // refresh workspace data
    },
  });
};

const claimStatusStyle = (status) => {
  const map = {
    completed: { background: 'var(--success-soft)', color: 'var(--success-text)' },
    approved: { background: 'var(--info-soft)', color: 'var(--info-text)' },
    repairing: { background: 'var(--warning-soft)', color: 'var(--warning-text)' },
    rejected: { background: 'var(--danger-soft)', color: 'var(--danger-text)' },
  };
  return map[status] || { background: 'var(--bg-hover)', color: 'var(--text-secondary)' };
};

const remainingDays = computed(() => {
  if (!props.service?.warranty_expired_at) return props.service?.warranty_days || 0;
  const diff = Math.ceil((new Date(props.service.warranty_expired_at) - new Date()) / (1000 * 60 * 60 * 24));
  return Math.max(0, diff);
});

const remainingDaysText = computed(() => {
  if (!props.service?.warranty_expired_at) return `${props.service?.warranty_days || 0} hari`;
  if (remainingDays.value <= 0) return 'Berakhir';
  return `${remainingDays.value} hari`;
});

const warrantyProgress = computed(() => {
  const total = props.service?.warranty_days || 30;
  return Math.min(100, Math.max(0, (remainingDays.value / total) * 100));
});

const warrantyBarColor = computed(() => {
  if (remainingDays.value <= 3) return 'var(--danger)';
  if (remainingDays.value <= 7) return 'var(--warning)';
  return 'var(--success)';
});

const warrantyStatusLabel = computed(() => {
  if (props.service?.is_warranty_claim) return 'Klaim Garansi';
  if (remainingDays.value <= 0 && props.service?.warranty_expired_at) return 'Berakhir';
  if (remainingDays.value > 0) return 'Aktif';
  return 'Non-Garansi';
});

const warrantyStatusStyle = computed(() => {
  if (props.service?.is_warranty_claim) return { background: 'var(--info-soft)', color: 'var(--info-text)' };
  if (remainingDays.value <= 0 && props.service?.warranty_expired_at) return { background: 'var(--danger-soft)', color: 'var(--danger-text)' };
  if (remainingDays.value > 0) return { background: 'var(--success-soft)', color: 'var(--success-text)' };
  return { background: 'var(--bg-hover)', color: 'var(--text-secondary)' };
});
</script>
