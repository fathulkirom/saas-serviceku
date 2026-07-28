<template>
  <div class="rounded-xl border overflow-hidden" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
    <div v-if="title" class="px-5 py-4 border-b" :style="{ borderColor: 'var(--border-light)' }">
      <h3 class="text-sm font-bold" style="color: var(--text-primary);">{{ title }}</h3>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead>
          <tr class="border-b" :style="{ borderColor: 'var(--border-light)', background: 'var(--bg-hover)' }">
            <th v-for="col in columns" :key="col.key"
              class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider sticky top-0 z-10"
              :class="col.align === 'right' ? 'text-right' : col.align === 'center' ? 'text-center' : 'text-left'"
              :style="{ color: 'var(--text-muted)', background: 'var(--bg-hover)' }">
              {{ col.label }}
            </th>
            <th v-if="$slots.action" class="px-4 py-3 text-right sticky top-0 z-10" :style="{ background: 'var(--bg-hover)' }"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!rows.length">
            <td :colspan="columns.length + ($slots.action ? 1 : 0)" class="px-4 py-8">
              <EmptyState :icon="emptyIcon" :title="emptyTitle" :description="emptyDescription"
                :actionUrl="emptyActionUrl" :actionLabel="emptyActionLabel" />
            </td>
          </tr>
          <template v-else>
            <tr v-for="(row, i) in rows" :key="i"
              class="border-t transition-all"
              :class="[hoverable ? 'hover:bg-dark-50 cursor-pointer' : '', striped && i % 2 === 1 ? 'bg-dark-50/50' : '']"
              :style="{ borderColor: 'var(--border-light)' }"
              @click="$emit('row-click', row)">
              <td v-for="col in columns" :key="col.key"
                class="px-4 py-3.5 text-sm"
                :class="[col.align === 'right' ? 'text-right font-medium' : col.align === 'center' ? 'text-center' : 'text-left', compact ? 'py-2.5' : 'py-3.5']"
                :style="{ color: col.bold ? 'var(--text-primary)' : 'var(--text-secondary)' }">
                <slot :name="'cell-' + col.key" :row="row" :value="row[col.key]">
                  {{ formatCell(row, col) }}
                </slot>
              </td>
              <td v-if="$slots.action" class="px-4 py-3 text-right" :class="compact ? 'py-2.5' : 'py-3.5'">
                <slot name="action" :row="row" />
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
    <div v-if="$slots.footer" class="px-4 py-3 border-t" :style="{ borderColor: 'var(--border-light)' }">
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup>
import EmptyState from '@/Components/EmptyState.vue';

defineProps({
  columns: { type: Array, required: true },
  rows: { type: Array, default: () => [] },
  title: { type: String, default: '' },
  emptyText: { type: String, default: 'Belum ada data' },
  emptyIcon: { type: String, default: 'default' },
  emptyTitle: { type: String, default: 'Belum ada data' },
  emptyDescription: { type: String, default: '' },
  emptyActionUrl: { type: String, default: '' },
  emptyActionLabel: { type: String, default: '' },
  striped: { type: Boolean, default: false },
  hoverable: { type: Boolean, default: true },
  compact: { type: Boolean, default: false },
});

defineEmits(['row-click']);

const formatCell = (row, col) => {
  const val = row[col.key];
  if (val === null || val === undefined) return '-';
  if (col.format === 'number') return new Intl.NumberFormat('id-ID').format(val);
  if (col.format === 'currency') return 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
  if (col.format === 'date') return val ? new Date(val).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '-';
  return val;
};
</script>
