<template>
  <div class="space-y-5">
    <!-- ═══════════ REQUEST PART (Technician) ═══════════ -->
    <SkCard v-if="canRequestPart" title="➕ Request Part" size="md">
      <div class="mb-2 text-[11px] font-medium" :style="{ color: 'var(--text-muted)' }">
        Request part untuk servis ini. Admin akan menyetujui (stok di-reservasi) lalu CS mengonfirmasi saat invoice.
      </div>
      <form @submit.prevent="requestPart" class="space-y-3">
        <div>
          <select
            v-model="requestForm.product_id"
            class="w-full rounded-xl border text-sm p-2.5"
            :style="{ borderColor: 'var(--border-light)', background: 'var(--bg-surface)', color: 'var(--text-primary)' }"
          >
            <option value="">-- Pilih Produk --</option>
            <option v-for="p in availableProducts" :key="p.id" :value="p.id">
              {{ p.name }} (Stok: {{ p.stock_quantity }} · Tersedia: {{ p.available_quantity }})
            </option>
          </select>
        </div>
        <div class="flex gap-2">
          <input
            v-model.number="requestForm.qty"
            type="number"
            min="1"
            max="999"
            class="w-24 rounded-xl border text-sm p-2.5 text-center"
            :style="{ borderColor: 'var(--border-light)', background: 'var(--bg-surface)', color: 'var(--text-primary)' }"
            placeholder="Qty"
          />
          <input
            v-model="requestForm.notes"
            type="text"
            class="flex-1 rounded-xl border text-sm p-2.5"
            :style="{ borderColor: 'var(--border-light)', background: 'var(--bg-surface)', color: 'var(--text-primary)' }"
            placeholder="Catatan (opsional)"
          />
        </div>
        <button
          type="submit"
          :disabled="requestLoading || !requestForm.product_id || requestForm.qty < 1"
          class="px-4 py-2 rounded-xl text-sm font-bold text-white transition hover:opacity-90 disabled:opacity-50"
          style="background: var(--primary)"
        >
          <span v-if="requestLoading" class="animate-spin inline-block mr-1">⏳</span>
          Kirim Request
        </button>
      </form>
    </SkCard>

    <!-- ═══════════ PART LIFECYCLE (Requested / Approved-Reserved / Consumed / Returned / Rejected) ═══════════ -->
    <SkCard v-if="requiredParts.length" title="🔩 Part Request & Usage" size="md">
      <div class="mb-3 p-3 rounded-xl text-[11px] font-medium"
        :style="{ background: 'var(--info-soft)', color: 'var(--info-text)' }">
        Alur: Teknisi minta part → Admin setujui (stok di-reservasi) → CS konfirmasi saat invoice (stok fisik berkurang).
        Stok fisik tidak berubah saat request/disetujui.
      </div>
      <div class="space-y-2">
        <div
          v-for="part in requiredParts"
          :key="part.id"
          class="flex items-center justify-between p-3 rounded-xl"
          :style="{ background: 'var(--bg-hover)' }"
        >
          <div class="flex-1 min-w-0 pr-2">
            <p class="text-sm font-bold truncate" :style="{ color: 'var(--text-primary)' }">{{ part.product_name }}</p>
            <p class="text-[10px]" :style="{ color: 'var(--text-muted)' }">
              Qty: {{ part.qty }} · {{ part.priority || 'normal' }}
              <template v-if="part.stock_info">
                · Stok: {{ part.stock_info.physical }} (reserved {{ part.stock_info.reserved }}, tersedia {{ part.stock_info.available }})
              </template>
            </p>
          </div>
          <div class="flex items-center gap-1.5 flex-wrap justify-end">
            <span class="text-[10px] font-bold px-2 py-1 rounded-full" :style="partStatusStyle(part.status)">
              {{ partStatusLabel(part.status) }}
            </span>

            <!-- Admin/Warehouse: approve request (→ reserved) -->
            <button
              v-if="part.status === 'requested' && canManageParts"
              @click="approvePart(part.id)"
              class="text-[10px] font-bold px-2 py-1 rounded-lg text-white"
              style="background: var(--success)"
              title="Setujui (reservasi stok)"
            >✅ Setuju</button>

            <!-- Admin/Warehouse: reject request -->
            <button
              v-if="part.status === 'requested' && canManageParts"
              @click="rejectPart(part.id)"
              class="text-[10px] font-bold px-2 py-1 rounded-lg text-white"
              style="background: var(--danger)"
              title="Tolak request"
            >⛔ Tolak</button>

            <!-- Admin/Tech: cancel (releases reservation, no stock change) -->
            <button
              v-if="['requested', 'approved', 'reserved'].includes(part.status) && canManageParts"
              @click="cancelPart(part.id)"
              class="text-[10px] font-bold px-2 py-1 rounded-lg text-white"
              style="background: var(--danger)"
              title="Batalkan (lepaskan reservasi)"
            >❌ Batal</button>

            <!-- CS / Billing: confirm billable consumption (deducts stock ONCE) -->
            <button
              v-if="['approved', 'reserved'].includes(part.status) && canConsumeParts"
              @click="confirmPart(part)"
              class="text-[10px] font-bold px-2 py-1 rounded-lg text-white"
              style="background: var(--primary)"
              title="Konfirmasi dipakai & masuk invoice (stok fisik berkurang)"
            >💰 Konfirmasi</button>
          </div>
        </div>
      </div>
    </SkCard>

    <!-- ═══════════ INSTALLED SPAREPARTS (Consumed → billed) ═══════════ -->
    <SkCard title="🔧 Sparepart Terpasang (Sudah Dikonfirmasi)" size="md">
    <div v-if="!spareparts.length" class="py-8">
      <SkEmptyState variant="empty" title="Belum ada sparepart" description="Part yang dikonfirmasi CS akan muncul di sini dan masuk invoice." />
    </div>

    <SkDataTable
      v-else
      :columns="columns"
      :rows="spareparts"
      rowKey="id"
      :showToolbar="false"
      :showPagination="false"
      compact
    >
      <template #cell-product_name="{ value }">
        <span class="font-medium" :style="{ color: 'var(--text-primary)' }">{{ value || '-' }}</span>
      </template>
      <template #cell-price="{ value }">
        <span class="text-xs">Rp {{ formatNumber(value) }}</span>
      </template>
      <template #cell-total="{ value }">
        <span class="text-xs font-bold" :style="{ color: 'var(--text-primary)' }">Rp {{ formatNumber(value) }}</span>
      </template>
      <template #cell-status="{ value }">
        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full"
          :style="value === 'indent' ? { background: 'var(--warning-soft)', color: 'var(--warning-text)' } : { background: 'var(--success-soft)', color: 'var(--success-text)' }"
        >{{ value === 'indent' ? 'Indent' : 'Terpasang' }}</span>
      </template>
    </SkDataTable>

    <!-- Total -->
    <div v-if="spareparts.length" class="mt-4 pt-3 border-t flex justify-between items-center" :style="{ borderColor: 'var(--border-light)' }">
      <span class="sk-label-sm">Total Sparepart</span>
      <span class="text-sm font-bold" :style="{ color: 'var(--text-primary)' }">
        Rp {{ formatNumber(totalParts) }}
      </span>
    </div>
  </SkCard>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import SkCard from '@/Enterprise/Components/Cards/Card.vue';
import SkDataTable from '@/Enterprise/Components/Table/DataTable.vue';
import SkEmptyState from '@/Enterprise/Components/Empty/EmptyState.vue';
import { useFormatter } from '@/Composables/useFormatter.js';

const props = defineProps({
  service: { type: Object, default: () => ({}) },
  spareparts: { type: Array, default: () => [] },
  requiredParts: { type: Array, default: () => [] },
  availableProducts: { type: Array, default: () => [] },
  canManageParts: { type: Boolean, default: false },
  canConsumeParts: { type: Boolean, default: false },
  canRequestPart: { type: Boolean, default: false },
});

const emit = defineEmits(['refresh']);

const page = usePage();
const { formatNumber } = useFormatter();

// Technician request-part form state (real backend call on submit).
const requestForm = ref({ product_id: '', qty: 1, notes: '' });
const requestLoading = ref(false);

async function requestPart() {
  if (!requestForm.value.product_id || requestForm.value.qty < 1) return;
  requestLoading.value = true;
  try {
    await postForm(`/services/${props.service.id}/parts/request`, {
      product_id: Number(requestForm.value.product_id),
      part_name: props.availableProducts.find(p => p.id === Number(requestForm.value.product_id))?.name || '',
      qty: Number(requestForm.value.qty),
      notes: requestForm.value.notes || null,
    });
    requestForm.value = { product_id: '', qty: 1, notes: '' };
    emit('refresh');
  } catch (e) {
    window.alert(e.message);
  } finally {
    requestLoading.value = false;
  }
}

const columns = [
  { key: 'product_name', label: 'Nama', bold: true },
  { key: 'product_sku', label: 'SKU' },
  { key: 'quantity', label: 'Qty', align: 'center' },
  { key: 'price', label: 'Harga', align: 'right', format: 'currency' },
  { key: 'total', label: 'Total', align: 'right', format: 'currency' },
  { key: 'status', label: 'Status', align: 'center' },
];

const totalParts = computed(() =>
  props.spareparts.reduce((sum, p) => sum + (Number(p.total) || 0), 0)
);

// BR-FIX-01: clear 5-state distinction.
function partStatusLabel(status) {
  const map = {
    requested: 'Diminta',
    approved: 'Disetujui / Reserved',
    reserved: 'Direserve',
    used: 'Dipakai (Masuk Invoice)',
    returned: 'Dikembalikan',
    cancelled: 'Dibatalkan',
    rejected: 'Ditolak',
    waiting_purchase: 'Menunggu PO',
    indent: 'Indent',
  };
  return map[status] || status;
}

function partStatusStyle(status) {
  const map = {
    requested: { background: 'var(--info-soft)', color: 'var(--info-text)' },
    approved: { background: 'var(--primary-soft)', color: 'var(--primary)' },
    reserved: { background: 'var(--primary-soft)', color: 'var(--primary)' },
    used: { background: 'var(--success-soft)', color: 'var(--success-text)' },
    returned: { background: 'var(--warning-soft)', color: 'var(--warning-text)' },
    cancelled: { background: 'var(--danger-soft)', color: 'var(--danger-text)' },
    rejected: { background: 'var(--danger-soft)', color: 'var(--danger-text)' },
  };
  return map[status] || { background: 'var(--bg-hover)', color: 'var(--text-muted)' };
}

async function postForm(url, body = {}) {
  const r = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': page.props.csrf_token || '', 'Accept': 'application/json' },
    body: JSON.stringify(body),
  });
  if (!r.ok) {
    const data = await r.json().catch(() => ({}));
    throw new Error(data.message || 'Permintaan gagal');
  }
}

async function approvePart(partId) {
  try {
    await postForm(`/service-parts/${partId}/approve`);
    emit('refresh');
  } catch (e) { window.alert(e.message); }
}

async function rejectPart(partId) {
  const reason = prompt('Alasan penolakan:');
  if (!reason) return;
  try {
    await postForm(`/service-parts/${partId}/reject`, { reason });
    emit('refresh');
  } catch (e) { window.alert(e.message); }
}

async function cancelPart(partId) {
  const reason = prompt('Alasan pembatalan (reservasi akan dilepaskan):');
  if (!reason) return;
  try {
    await postForm(`/service-parts/${partId}/cancel`, { reason });
    emit('refresh');
  } catch (e) { window.alert(e.message); }
}

// CS confirms the approved part → consume reservation, deduct stock once,
// add to invoice. Real backend call — never browser-only state.
async function confirmPart(part) {
  const defaultPrice = part.selling_price ?? part.stock_info?.selling_price ?? 0;
  const price = prompt(`Harga jual part "${part.product_name}" (x${part.qty})?`, defaultPrice);
  if (price === null) return;
  const discount = prompt('Diskon (Rp)?', '0') || '0';
  try {
    await postForm(`/service-parts/${part.id}/use`, {
      selling_price: Number(price) || 0,
      discount: Number(discount) || 0,
    });
    emit('refresh');
  } catch (e) { window.alert(e.message); }
}
</script>
