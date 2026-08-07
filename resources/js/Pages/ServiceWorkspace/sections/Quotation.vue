<template>
  <SkCard title="Estimasi Biaya" size="md">
    <!-- Loading -->
    <div v-if="isSaving" class="py-8 text-center">
      <div class="animate-spin text-2xl mb-2">⏳</div>
      <p class="text-sm" :style="{ color: 'var(--text-muted)' }">Memproses estimasi...</p>
    </div>

    <!-- No quotation yet -->
    <div v-else-if="!quotations?.length && !showForm" class="py-6 space-y-3 text-center">
      <SkEmptyState variant="empty" title="Belum ada estimasi"
        description="Buat estimasi biaya berdasarkan diagnosis dan sparepart yang dibutuhkan." />
      <button v-if="canCreateQuotation" @click="openForm"
        class="px-4 py-2 rounded-xl text-sm font-bold text-white transition" style="background: var(--primary)">
        💰 Buat Estimasi
      </button>
    </div>

    <!-- Create Form -->
    <div v-else-if="showForm" class="space-y-4">
      <h4 class="text-sm font-bold" :style="{ color: 'var(--text-primary)' }">Estimasi Baru</h4>

      <!-- Labor / Jasa -->
      <div>
        <label class="block text-[10px] font-bold uppercase tracking-wider mb-1" :style="{ color: 'var(--text-muted)' }">Biaya Jasa (Rp)</label>
        <input type="number" v-model.number="form.labor_cost" min="0" class="w-full rounded-lg border text-sm p-2.5" :style="{ borderColor: 'var(--border-light)', background: 'var(--bg-surface)', color: 'var(--text-primary)' }" placeholder="Biaya jasa teknisi" />
      </div>

      <!-- Parts -->
      <div>
        <label class="block text-[10px] font-bold uppercase tracking-wider mb-1" :style="{ color: 'var(--text-muted)' }">Sparepart</label>
        <div v-for="(item, i) in form.items" :key="i" class="flex items-center gap-2 mb-2">
          <input v-model="item.name" placeholder="Nama part" class="flex-1 rounded border text-xs p-2" :style="{ borderColor: 'var(--border-light)', background: 'var(--bg-surface)' }" />
          <input v-model.number="item.qty" type="number" min="1" placeholder="Qty" class="w-16 rounded border text-xs p-2" :style="{ borderColor: 'var(--border-light)', background: 'var(--bg-surface)' }" />
          <input v-model.number="item.price" type="number" min="0" placeholder="Harga" class="w-24 rounded border text-xs p-2" :style="{ borderColor: 'var(--border-light)', background: 'var(--bg-surface)' }" />
          <button @click="removeItem(i)" class="text-xs px-2 py-1 rounded" style="color: var(--danger)">✕</button>
        </div>
        <button @click="addItem" class="text-xs px-3 py-1.5 rounded-lg font-bold" :style="{ background: 'var(--bg-hover)', color: 'var(--primary)' }">+ Tambah Sparepart</button>
      </div>

      <!-- Totals (preview only) -->
      <div class="p-3 rounded-lg space-y-1" :style="{ background: 'var(--bg-hover)' }">
        <div class="flex justify-between text-xs"><span :style="{ color: 'var(--text-muted)' }">Subtotal Parts</span><span class="font-bold" :style="{ color: 'var(--text-primary)' }">Rp {{ formatNumber(partsTotal) }}</span></div>
        <div class="flex justify-between text-xs"><span :style="{ color: 'var(--text-muted)' }">Jasa</span><span class="font-bold" :style="{ color: 'var(--text-primary)' }">Rp {{ formatNumber(form.labor_cost || 0) }}</span></div>
        <div class="flex justify-between text-xs pt-1 border-t" :style="{ borderColor: 'var(--border-light)' }"><span class="font-bold" :style="{ color: 'var(--text-primary)' }">Total</span><span class="font-bold text-sm" style="color: var(--success)">Rp {{ formatNumber(grandTotal) }}</span></div>
      </div>

      <div>
        <label class="block text-[10px] font-bold uppercase tracking-wider mb-1" :style="{ color: 'var(--text-muted)' }">Catatan</label>
        <textarea v-model="form.notes" rows="2" class="w-full rounded-lg border text-sm p-2.5 resize-none" :style="{ borderColor: 'var(--border-light)', background: 'var(--bg-surface)', color: 'var(--text-primary)' }" placeholder="Catatan untuk customer / internal..."></textarea>
      </div>

      <div v-if="saveError" class="p-2 rounded-lg text-xs" style="background: var(--danger-soft); color: var(--danger-text)">{{ saveError }}</div>
      <div class="flex gap-2 pt-2">
        <button @click="cancelForm" class="flex-1 px-4 py-2 rounded-xl text-sm font-bold border" :style="{ borderColor: 'var(--border-light)', color: 'var(--text-primary)' }">Batal</button>
        <button @click="saveQuotation" :disabled="isSaving" class="flex-1 px-4 py-2 rounded-xl text-sm font-bold text-white transition disabled:opacity-50" style="background: var(--primary)">
          {{ isSaving ? 'Menyimpan...' : 'Simpan & Kirim Estimasi' }}
        </button>
      </div>
    </div>

    <!-- Display quotations -->
    <div v-else class="space-y-3">
      <div v-for="q in quotations" :key="q.id" class="p-4 rounded-xl border" :style="{ borderColor: q.status === 'approved' ? 'var(--success)' : q.status === 'rejected' ? 'var(--danger)' : 'var(--border-light)', background: 'var(--bg-surface)' }">
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs font-bold px-2 py-0.5 rounded-full" :style="statusStyle(q.status)">{{ statusLabel(q.status) }}</span>
          <span class="text-[10px]" :style="{ color: 'var(--text-muted)' }">{{ formatDate(q.created_at) }}</span>
        </div>
        <div class="grid grid-cols-2 gap-2 text-xs mb-2">
          <p :style="{ color: 'var(--text-muted)' }">Total</p>
          <p class="text-right font-bold" :style="{ color: 'var(--text-primary)' }">Rp {{ formatNumber(q.total_cost) }}</p>
        </div>
        <p v-if="q.notes" class="text-[10px]" :style="{ color: 'var(--text-muted)' }">{{ q.notes }}</p>
        <!-- Items -->
        <div v-if="q.items?.length" class="mt-2 pt-2 border-t space-y-1" :style="{ borderColor: 'var(--border-light)' }">
          <div v-for="(item, i) in q.items" :key="i" class="flex justify-between text-[10px]">
            <span :style="{ color: 'var(--text-primary)' }">{{ item.name || item.part_name }} ×{{ item.qty || 1 }}</span>
            <span :style="{ color: 'var(--text-muted)' }">Rp {{ formatNumber((item.price || 0) * (item.qty || 1)) }}</span>
          </div>
        </div>
        <!-- Approval actions -->
        <div v-if="q.status === 'sent' || q.status === 'pending'" class="flex gap-2 mt-3 pt-2 border-t" :style="{ borderColor: 'var(--border-light)' }">
          <button v-if="canApprove" @click="approve(q.id)" class="flex-1 px-3 py-1.5 rounded-lg text-xs font-bold text-white" style="background: var(--success)">✅ Setujui</button>
          <button v-if="canApprove" @click="reject(q.id)" class="flex-1 px-3 py-1.5 rounded-lg text-xs font-bold text-white" style="background: var(--danger)">❌ Tolak</button>
        </div>
        <div v-if="q.status === 'approved'" class="mt-2 pt-2 border-t text-xs" :style="{ borderColor: 'var(--border-light)', color: 'var(--success)' }">
          ✅ Disetujui — {{ q.approval_method || 'CS' }} · {{ formatDate(q.approved_at) }}
        </div>
      </div>
      <!-- New quotation button -->
      <button v-if="canCreateQuotation" @click="openForm"
        class="w-full px-4 py-2 rounded-xl text-sm font-bold transition" :style="{ background: 'var(--primary-soft)', color: 'var(--primary)' }">
        💰 Buat Estimasi Baru
      </button>
    </div>
  </SkCard>
</template>

<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import SkCard from '@/Enterprise/Components/Cards/Card.vue';
import SkEmptyState from '@/Enterprise/Components/Empty/EmptyState.vue';
import { useFormatter } from '@/Composables/useFormatter.js';

const props = defineProps({
  quotations: { type: Array, default: () => [] },
  serviceId: { type: [Number, String], default: null },
});

const page = usePage();
const { formatNumber } = useFormatter();

const showForm = ref(false);
const isSaving = ref(false);
const saveError = ref('');

const form = ref({ labor_cost: null, items: [], notes: '' });

const userRole = computed(() => page.props.auth?.user?.role || '');
const canCreateQuotation = computed(() => ['owner', 'admin', 'technician', 'manager'].includes(userRole.value));
const canApprove = computed(() => ['owner', 'admin', 'manager', 'cs'].includes(userRole.value));

const partsTotal = computed(() => form.value.items.reduce((sum, i) => sum + ((i.price || 0) * (i.qty || 1)), 0));
const grandTotal = computed(() => partsTotal.value + (form.value.labor_cost || 0));

function addItem() { form.value.items.push({ name: '', qty: 1, price: 0 }); }
function removeItem(i) { form.value.items.splice(i, 1); }

function openForm() { form.value = { labor_cost: null, items: [], notes: '' }; saveError.value = ''; showForm.value = true; }
function cancelForm() { showForm.value = false; }

async function saveQuotation() {
  isSaving.value = true; saveError.value = '';
  try {
    const id = props.serviceId || page.props.workspace?.service?.id;
    const total = grandTotal.value;
    const response = await fetch(`/services/${id}/quotation`, {
      method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': page.props.csrf_token || '', 'Accept': 'application/json' },
      body: JSON.stringify({ total_cost: total, items: form.value.items, notes: form.value.notes }),
    });
    if (!response.ok) { const data = await response.json().catch(() => ({})); throw new Error(data.message || 'Gagal membuat estimasi.'); }
    showForm.value = false; window.location.reload();
  } catch (e) { saveError.value = e.message; }
  finally { isSaving.value = false; }
}

async function approve(quotationId) {
  isSaving.value = true;
  try {
    const response = await fetch(`/quotations/${quotationId}/approve`, {
      method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': page.props.csrf_token || '' },
      body: JSON.stringify({ method: 'cs' }),
    });
    if (!response.ok) throw new Error('Gagal menyetujui.');
    window.location.reload();
  } catch (e) { saveError.value = e.message; }
  finally { isSaving.value = false; }
}

async function reject(quotationId) {
  const reason = prompt('Alasan penolakan:');
  if (!reason) return;
  isSaving.value = true;
  try {
    const response = await fetch(`/quotations/${quotationId}/reject`, {
      method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': page.props.csrf_token || '' },
      body: JSON.stringify({ reason }),
    });
    if (!response.ok) throw new Error('Gagal menolak.');
    window.location.reload();
  } catch (e) { saveError.value = e.message; }
  finally { isSaving.value = false; }
}

function statusLabel(s) { return ({ sent:'Menunggu', pending:'Menunggu', approved:'Disetujui', rejected:'Ditolak' }[s] || s); }
function statusStyle(s) {
  const c = { sent: { bg: 'var(--warning-soft)', color: 'var(--warning-text)' }, approved: { bg: 'var(--success-soft)', color: 'var(--success-text)' }, rejected: { bg: 'var(--danger-soft)', color: 'var(--danger-text)' } };
  return c[s] || { background: 'var(--bg-hover)', color: 'var(--text-secondary)' };
}
function formatDate(v) { if (!v) return ''; return new Date(v).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }); }
</script>
