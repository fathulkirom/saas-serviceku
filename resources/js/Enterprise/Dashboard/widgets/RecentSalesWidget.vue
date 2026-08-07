<template>
  <SkWidgetCard title="Penjualan Terbaru" :loading="loading" collapsible>
    <template #action>
      <Link :href="route('keuangan.index')" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">
        Semua
      </Link>
    </template>

    <div v-if="!sales.length" class="py-6">
      <SkEmptyState variant="empty" title="Belum ada penjualan" description="Transaksi penjualan akan muncul di sini." />
    </div>

    <SkDataTable
      v-else
      :columns="columns"
      :rows="sales"
      rowKey="id"
      :showToolbar="false"
      :showPagination="false"
      compact
    >
      <template #cell-total="{ value }">
        <span class="font-semibold">Rp {{ formatNumber(value) }}</span>
      </template>
      <template #cell-status="{ row }">
        <span
          class="text-[10px] font-semibold px-2 py-0.5 rounded-full"
          :style="row.status === 'paid' ? { background: 'var(--success-soft)', color: 'var(--success-text)' } : { background: 'var(--warning-soft)', color: 'var(--warning-text)' }"
        >
          {{ row.status === 'paid' ? 'Lunas' : 'Draft' }}
        </span>
      </template>
    </SkDataTable>
  </SkWidgetCard>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import SkWidgetCard from '@/Enterprise/Components/Cards/WidgetCard.vue';
import SkDataTable from '@/Enterprise/Components/Table/DataTable.vue';
import SkEmptyState from '@/Enterprise/Components/Empty/EmptyState.vue';
import { useFormatter } from '@/Composables/useFormatter.js';

const { formatNumber } = useFormatter();

defineProps({
  sales: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
});

const columns = [
  { key: 'customer_name', label: 'Pelanggan', bold: true },
  { key: 'total', label: 'Total', format: 'currency', align: 'right' },
  { key: 'status', label: 'Status', align: 'center' },
];
</script>
