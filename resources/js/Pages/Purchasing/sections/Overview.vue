<template>
  <div class="space-y-5">
    <!-- Purchase Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <SkMetricCard label="Total PO" :value="data?.total_po || 0" format="number" color="primary" icon="📋" />
      <SkMetricCard label="Nilai Pembelian" :value="data?.total_value || 0" format="currency" color="success" icon="💰" />
      <SkMetricCard label="Waiting Approval" :value="data?.pending_approval || 0" format="number" color="warning" icon="⏳" />
      <SkMetricCard label="Outstanding PO" :value="data?.outstanding || 0" format="number" color="danger" icon="📋" />
    </div>

    <!-- PO Info -->
    <SkCard title="Informasi Purchase Order" size="md" v-if="data">
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div><p class="sk-caption">PO Number</p><p class="sk-code text-xs">{{ data.po_number || '-' }}</p></div>
        <div><p class="sk-caption">Status</p><span class="text-xs font-bold px-2 py-0.5 rounded-full" :style="statusBadgeStyle">{{ statusLabel }}</span></div>
        <div><p class="sk-caption">Supplier</p><p class="sk-label-sm">{{ data.supplier_name || '-' }}</p></div>
        <div><p class="sk-caption">Warehouse</p><p class="sk-label-sm">{{ data.warehouse || '-' }}</p></div>
        <div><p class="sk-caption">Expected Date</p><p class="sk-label-sm">{{ formatDate(data.expected_date) }}</p></div>
        <div><p class="sk-caption">Currency</p><p class="sk-label-sm">{{ data.currency || 'IDR' }}</p></div>
        <div><p class="sk-caption">Payment Terms</p><p class="sk-label-sm">{{ data.payment_terms || '-' }}</p></div>
        <div><p class="sk-caption">Created By</p><p class="sk-label-sm">{{ data.created_by || '-' }}</p></div>
      </div>
    </SkCard>

    <!-- Approval Progress -->
    <SkCard title="Approval Progress" size="md" v-if="approvalSteps?.length">
      <div class="relative">
        <div class="absolute left-[11px] top-2 bottom-2 w-px" :style="{ background: 'var(--border-light)' }"></div>
        <div class="space-y-3">
          <div v-for="(step, i) in approvalSteps" :key="i" class="flex items-center gap-3">
            <div class="relative z-10 w-[23px] h-[23px] rounded-full flex items-center justify-center text-xs font-bold"
              :style="stepStyle(step)">
              {{ step.completed ? '✓' : step.rejected ? '✗' : step.active ? '●' : i + 1 }}
            </div>
            <div class="flex-1">
              <p class="text-xs font-semibold" :style="{ color: 'var(--text-primary)' }">{{ step.label }}</p>
              <p class="sk-caption">{{ step.approver || 'Pending' }} · {{ step.date || '-' }}</p>
            </div>
            <span v-if="step.completed" class="text-xs font-bold" :style="{ color: 'var(--success-text)' }">✓</span>
            <span v-else-if="step.rejected" class="text-xs font-bold" :style="{ color: 'var(--danger-text)' }">✗</span>
            <span v-else class="text-xs" :style="{ color: 'var(--text-muted)' }">—</span>
          </div>
        </div>
      </div>
    </SkCard>

    <!-- Receipt Status -->
    <SkCard title="Receipt Status" size="md" v-if="data">
      <div class="flex items-center gap-4">
        <div class="flex-1">
          <div class="flex justify-between text-xs mb-1">
            <span :style="{ color: 'var(--text-muted)' }">Received</span>
            <span class="font-semibold" :style="{ color: 'var(--text-primary)' }">{{ data.received_qty || 0 }} / {{ data.total_qty || 0 }}</span>
          </div>
          <div class="h-2 rounded-full overflow-hidden" :style="{ background: 'var(--bg-hover)' }">
            <div class="h-full rounded-full transition-all duration-500" :style="{ width: receiptPercent + '%', background: receiptColor }"></div>
          </div>
        </div>
        <span class="text-sm font-bold" :style="{ color: 'var(--text-primary)' }">{{ receiptPercent }}%</span>
      </div>
    </SkCard>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import SkCard from '@/Enterprise/Components/Cards/Card.vue';
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
import { useFormatter } from '@/Composables/useFormatter.js';

const props = defineProps({ data: { type: Object, default: () => ({}) }, workspace: { type: Object, default: null } });
const { formatDate } = useFormatter();

const statusLabels = { draft:'Draft', pending_approval:'Waiting Approval', approved:'Approved', sent:'Sent', partial_received:'Partial', completed:'Completed', cancelled:'Cancelled' };
const statusColors = { draft:{bg:'var(--bg-hover)',text:'var(--text-secondary)'}, pending_approval:{bg:'var(--warning-soft)',text:'var(--warning-text)'}, approved:{bg:'var(--info-soft)',text:'var(--info-text)'}, completed:{bg:'var(--success-soft)',text:'var(--success-text)'}, cancelled:{bg:'var(--danger-soft)',text:'var(--danger-text)'} };

const statusLabel = computed(() => statusLabels[props.data?.status] || props.data?.status || '-');
const statusBadgeStyle = computed(() => statusColors[props.data?.status] || { background:'var(--bg-hover)', color:'var(--text-secondary)' });

const receiptPercent = computed(() => {
  const r = Number(props.data?.received_qty || 0), t = Number(props.data?.total_qty || 1);
  return Math.round((r / t) * 100);
});
const receiptColor = computed(() => receiptPercent.value >= 100 ? 'var(--success)' : receiptPercent.value > 0 ? 'var(--warning)' : 'var(--bg-hover)');

const approvalSteps = computed(() => props.data?.approval_steps || [
  { label:'Manager Approval', completed:true, date:'Today', approver:'Manager A' },
  { label:'Admin Approval', active:true },
  { label:'Owner Approval' },
]);

function stepStyle(step) {
  if (step.completed) return { background:'var(--success)', color:'#fff' };
  if (step.rejected) return { background:'var(--danger)', color:'#fff' };
  if (step.active) return { background:'var(--primary-soft)', color:'var(--primary)', border:'2px solid var(--primary)' };
  return { background:'var(--bg-hover)', color:'var(--text-muted)' };
}
</script>
