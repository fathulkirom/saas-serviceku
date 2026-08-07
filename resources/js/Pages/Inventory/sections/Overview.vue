<template>
  <div class="space-y-5">
    <!-- Stock Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <SkMetricCard label="Total Item" :value="data?.total_items || 0" format="number" color="primary" icon="📦" />
      <SkMetricCard label="Nilai Stok" :value="data?.stock_value || 0" format="currency" color="success" icon="💰" />
      <SkMetricCard label="Stok Menipis" :value="data?.low_stock_count || 0" format="number" color="danger" icon="⚠️">
        <template #value><span :class="{ 'animate-pulse': (data?.low_stock_count || 0) > 0 }">{{ data?.low_stock_count || 0 }}</span></template>
      </SkMetricCard>
      <SkMetricCard label="Dead Stock" :value="data?.dead_stock || 0" format="number" color="warning" icon="📉" />
    </div>

    <!-- Product Info -->
    <SkCard title="Informasi Produk" size="md" v-if="data">
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div><p class="sk-caption">SKU</p><p class="sk-code text-xs">{{ data.sku || '-' }}</p></div>
        <div><p class="sk-caption">Kategori</p><p class="sk-label-sm">{{ data.category || '-' }}</p></div>
        <div><p class="sk-caption">Merek</p><p class="sk-label-sm">{{ data.brand || '-' }}</p></div>
        <div><p class="sk-caption">Satuan</p><p class="sk-label-sm">{{ data.unit || 'Pcs' }}</p></div>
        <div><p class="sk-caption">Stok Saat Ini</p><p class="text-lg font-bold" :style="{ color: stockColor }">{{ data.stock_quantity || 0 }}</p></div>
        <div><p class="sk-caption">Stok Minimum</p><p class="sk-label-sm">{{ data.min_stock || 0 }}</p></div>
        <div><p class="sk-caption">Gudang</p><p class="sk-label-sm">{{ data.warehouse || '-' }}</p></div>
        <div><p class="sk-caption">Rak/Bin</p><p class="sk-label-sm">{{ data.rack_location || '-' }}</p></div>
      </div>
    </SkCard>

    <!-- Pricing -->
    <SkCard title="Harga" size="md" v-if="data">
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
          <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Harga Beli</p>
          <p class="text-sm font-bold mt-1" :style="{ color: 'var(--text-primary)' }">Rp {{ formatNumber(data.cost_price) }}</p>
        </div>
        <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
          <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Harga Jual</p>
          <p class="text-sm font-bold mt-1" :style="{ color: 'var(--success-text)' }">Rp {{ formatNumber(data.selling_price) }}</p>
        </div>
        <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
          <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Harga Grosir</p>
          <p class="text-sm font-bold mt-1" :style="{ color: 'var(--text-primary)' }">Rp {{ formatNumber(data.wholesale_price) }}</p>
        </div>
        <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
          <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Margin</p>
          <p class="text-sm font-bold mt-1" :style="{ color: 'var(--primary)' }">{{ marginPercent }}%</p>
        </div>
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
const { formatNumber } = useFormatter();

const stockColor = computed(() => {
  const qty = props.data?.stock_quantity || 0;
  const min = props.data?.min_stock || 0;
  if (qty <= 0) return 'var(--danger)';
  if (qty <= min) return 'var(--warning)';
  return 'var(--success)';
});

const marginPercent = computed(() => {
  const cost = Number(props.data?.cost_price || 0);
  const sell = Number(props.data?.selling_price || 0);
  if (!cost || !sell) return 0;
  return Math.round(((sell - cost) / cost) * 100);
});
</script>
