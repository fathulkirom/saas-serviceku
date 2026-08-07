<template>
  <SkWidgetCard title="Servis Terbaru" :loading="loading" collapsible>
    <template #action>
      <Link :href="route('services.index')" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">
        Semua
      </Link>
    </template>

    <div v-if="!services.length" class="py-6">
      <SkEmptyState variant="empty" title="Belum ada servis" description="Servis baru akan muncul di sini." />
    </div>

    <SkDataTable
      v-else
      :columns="columns"
      :rows="services"
      rowKey="id"
      :showToolbar="false"
      :showPagination="false"
      compact
      @row-click="(row) => $emit('navigate', row)"
    >
      <template #cell-customer="{ row }">
        <span class="font-medium">{{ row.customer?.name || '-' }}</span>
      </template>
      <template #cell-status="{ row }">
        <span
          class="text-[10px] font-semibold px-2 py-0.5 rounded-full"
          :style="{ background: statusBg(row.status), color: statusColor(row.status) }"
        >
          {{ statusLabel(row.status) }}
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
import { useServiceStatus } from '@/Composables/useServiceStatus.js';

const { statusLabel, statusStyle } = useServiceStatus();

defineProps({
  services: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
});

defineEmits(['navigate']);

const statusBg = (s) => statusStyle(s)?.bg || 'var(--bg-hover)';
const statusColor = (s) => statusStyle(s)?.color || 'var(--text-muted)';

const columns = [
  { key: 'customer', label: 'Pelanggan', bold: true },
  { key: 'device_type', label: 'Tipe' },
  { key: 'status', label: 'Status', align: 'center' },
];
</script>
