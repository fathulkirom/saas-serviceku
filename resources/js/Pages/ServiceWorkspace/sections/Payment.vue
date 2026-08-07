<template>
  <SkCard title="Pembayaran" size="md">
    <div v-if="!paymentInfo" class="py-8">
      <SkEmptyState variant="empty" title="Belum ada pembayaran"
        description="Pembayaran akan tersedia setelah invoice dibuat." />
    </div>

    <div v-else class="space-y-4">
      <!-- Payment Status Badge -->
      <div class="flex items-center justify-between">
        <span class="sk-label-sm">Status Pembayaran</span>
        <span class="text-xs font-bold px-3 py-1 rounded-full"
          :style="paymentStatusStyle">{{ paymentStatusLabel }}</span>
      </div>

      <!-- Service Charge -->
      <div class="flex justify-between items-center py-2 border-b" :style="{ borderColor: 'var(--border-light)' }">
        <span class="text-sm" :style="{ color: 'var(--text-secondary)' }">Biaya Servis</span>
        <span class="text-sm font-bold" :style="{ color: 'var(--text-primary)' }">Rp {{ formatNumber(serviceCharge) }}</span>
      </div>

      <!-- Parts Total -->
      <div v-if="partsTotal" class="flex justify-between items-center py-2 border-b" :style="{ borderColor: 'var(--border-light)' }">
        <span class="text-sm" :style="{ color: 'var(--text-secondary)' }">Sparepart</span>
        <span class="text-sm font-bold" :style="{ color: 'var(--text-primary)' }">Rp {{ formatNumber(partsTotal) }}</span>
      </div>

      <!-- Discount -->
      <div v-if="discount" class="flex justify-between items-center py-2 border-b" :style="{ borderColor: 'var(--border-light)' }">
        <span class="text-sm" :style="{ color: 'var(--text-secondary)' }">Diskon</span>
        <span class="text-sm font-bold" :style="{ color: 'var(--success-text)' }">-Rp {{ formatNumber(discount) }}</span>
      </div>

      <!-- Grand Total -->
      <div class="flex justify-between items-center pt-2">
        <span class="text-base font-bold" :style="{ color: 'var(--text-primary)' }">Grand Total</span>
        <span class="text-xl font-extrabold" :style="{ color: 'var(--text-primary)' }">
          Rp {{ formatNumber(totalCost) }}
        </span>
      </div>

      <!-- Payment Methods -->
      <div class="pt-3 border-t" :style="{ borderColor: 'var(--border-light)' }">
        <p class="sk-section-title mb-3">Metode Pembayaran</p>
        <div class="grid grid-cols-4 gap-2">
          <button
            v-for="method in paymentMethods"
            :key="method.id"
            @click="$emit('select-payment', method.id)"
            class="p-3 rounded-xl border text-center transition-all text-xs"
            :class="selectedMethod === method.id ? 'ring-2' : ''"
            :style="selectedMethod === method.id
              ? { borderColor: 'var(--primary)', background: 'var(--primary-soft)', color: 'var(--primary)' }
              : { borderColor: 'var(--border-color)', color: 'var(--text-secondary)' }"
          >
            <div class="text-lg mb-1">{{ method.icon }}</div>
            <div class="font-semibold">{{ method.label }}</div>
          </button>
        </div>
      </div>

      <!-- Payment History -->
      <div v-if="payments?.length" class="pt-3 border-t" :style="{ borderColor: 'var(--border-light)' }">
        <p class="sk-section-title mb-3">Riwayat Pembayaran</p>
        <div class="space-y-2">
          <div v-for="p in payments" :key="p.id"
            class="flex items-center justify-between px-3 py-2 rounded-lg"
            :style="{ background: 'var(--bg-hover)' }">
            <div>
              <p class="text-xs font-semibold" :style="{ color: 'var(--text-primary)' }">Rp {{ formatNumber(p.amount) }}</p>
              <p class="sk-caption">{{ p.method }}</p>
            </div>
            <span class="text-[10px]" :style="{ color: 'var(--text-muted)' }">{{ formatDate(p.created_at) }}</span>
          </div>
        </div>
      </div>
    </div>
  </SkCard>
</template>

<script setup>
import { ref, computed } from 'vue';
import SkCard from '@/Enterprise/Components/Cards/Card.vue';
import SkEmptyState from '@/Enterprise/Components/Empty/EmptyState.vue';
import { useFormatter } from '@/Composables/useFormatter.js';

const props = defineProps({
  service: { type: Object, default: () => ({}) },
  sale: { type: Object, default: null },
});

defineEmits(['select-payment']);

const { formatNumber, formatDate } = useFormatter();

const selectedMethod = ref('');

const paymentMethods = [
  { id: 'cash', label: 'Cash', icon: '💵' },
  { id: 'transfer', label: 'Transfer', icon: '🏦' },
  { id: 'qris', label: 'QRIS', icon: '📱' },
  { id: 'deposit', label: 'Deposit', icon: '💰' },
];

const paymentInfo = computed(() => props.sale || props.service);
const serviceCharge = computed(() => Number(props.service?.service_charge || 0));
const partsTotal = computed(() => {
  return (props.service?.spareparts || []).reduce((s, p) => s + (Number(p.total) || 0), 0);
});
const discount = computed(() => 0);
const totalCost = computed(() => Number(props.service?.total_cost || serviceCharge.value + partsTotal.value));
const payments = computed(() => props.sale?.payments || []);
const paymentStatusLabel = computed(() => {
  const s = props.service?.payment_status || props.sale?.status;
  return s === 'paid' ? 'Lunas' : s === 'partial' ? 'DP' : 'Belum Bayar';
});
const paymentStatusStyle = computed(() => {
  const s = props.service?.payment_status || props.sale?.status;
  return s === 'paid'
    ? { background: 'var(--success-soft)', color: 'var(--success-text)' }
    : { background: 'var(--warning-soft)', color: 'var(--warning-text)' };
});
</script>
