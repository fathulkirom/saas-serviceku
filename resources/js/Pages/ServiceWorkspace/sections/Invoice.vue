<template>
  <SkCard title="Invoice & Pembayaran" size="md">
    <div v-if="!sale" class="py-8">
      <SkEmptyState variant="empty" title="Belum ada invoice" description="Invoice akan dibuat setelah servis selesai." />
    </div>

    <div v-else class="space-y-4">
      <!-- Invoice Header -->
      <div class="flex items-center justify-between">
        <div>
          <p class="sk-label-sm">Invoice #{{ sale.invoice_number || '-' }}</p>
          <p class="sk-caption" :style="sale.status === 'paid' ? { color: 'var(--success-text)' } : { color: 'var(--warning-text)' }">
            {{ sale.status === 'paid' ? 'Lunas' : sale.status === 'partial' ? 'DP' : 'Belum Lunas' }}
          </p>
        </div>
      </div>

      <!-- Items -->
      <div class="space-y-2">
        <div v-for="(item, i) in (sale.items || [])" :key="i"
          class="flex items-center justify-between py-2 border-b"
          :style="{ borderColor: 'var(--border-light)' }"
        >
          <div class="flex-1 min-w-0">
            <p class="text-xs font-medium" :style="{ color: 'var(--text-primary)' }">{{ item.name }}</p>
            <p class="sk-caption">{{ item.quantity }}x</p>
          </div>
          <span class="text-xs font-semibold flex-shrink-0 ml-3" :style="{ color: 'var(--text-primary)' }">
            Rp {{ formatNumber(item.total) }}
          </span>
        </div>
      </div>

      <!-- Service Charge -->
      <div v-if="serviceCharge" class="flex items-center justify-between py-2 border-b" :style="{ borderColor: 'var(--border-light)' }">
        <span class="text-xs" :style="{ color: 'var(--text-secondary)' }">Biaya Servis</span>
        <span class="text-xs font-semibold" :style="{ color: 'var(--text-primary)' }">Rp {{ formatNumber(serviceCharge) }}</span>
      </div>

      <!-- Grand Total -->
      <div class="flex items-center justify-between pt-2">
        <span class="text-sm font-bold" :style="{ color: 'var(--text-primary)' }">Grand Total</span>
        <span class="text-lg font-extrabold" :style="{ color: 'var(--text-primary)' }">
          Rp {{ formatNumber(totalCost || sale.total || 0) }}
        </span>
      </div>

      <!-- Payments -->
      <div v-if="sale.payments?.length" class="pt-3 border-t" :style="{ borderColor: 'var(--border-light)' }">
        <p class="sk-section-title mb-3">Riwayat Pembayaran</p>
        <div class="space-y-2">
          <div v-for="p in sale.payments" :key="p.id"
            class="flex items-center justify-between px-3 py-2 rounded-lg"
            :style="{ background: 'var(--bg-hover)' }"
          >
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
import SkCard from '@/Enterprise/Components/Cards/Card.vue';
import SkEmptyState from '@/Enterprise/Components/Empty/EmptyState.vue';
import { useFormatter } from '@/Composables/useFormatter.js';

const { formatNumber, formatDate } = useFormatter();

defineProps({
  sale: { type: Object, default: null },
  serviceCharge: { type: [Number, String], default: 0 },
  totalCost: { type: [Number, String], default: 0 },
  paymentStatus: { type: String, default: '' },
});
</script>
