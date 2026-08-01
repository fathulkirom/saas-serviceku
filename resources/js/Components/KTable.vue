<template>
  <div class="rounded-2xl border border-zinc-200 bg-white overflow-hidden shadow-sm">
    <div v-if="title" class="px-5 py-4 border-b border-zinc-200 bg-zinc-50/50">
      <h3 class="text-sm font-bold text-zinc-900">{{ title }}</h3>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="border-b border-zinc-200 bg-zinc-50">
            <th v-for="col in columns" :key="col.key"
              class="px-5 py-3 text-xs font-semibold text-zinc-500 uppercase tracking-wider sticky top-0 z-10"
              :class="col.align === 'right' ? 'text-right' : col.align === 'center' ? 'text-center' : 'text-left'">
              {{ col.label }}
            </th>
            <th v-if="$slots.action" class="px-5 py-3 text-right sticky top-0 z-10 bg-zinc-50"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-zinc-200">
          <tr v-if="!rows.length">
            <td :colspan="columns.length + ($slots.action ? 1 : 0)" class="px-5 py-12">
              <EmptyState :icon="emptyIcon" :title="emptyTitle" :description="emptyDescription"
                :actionUrl="emptyActionUrl" :actionLabel="emptyActionLabel" @action="$emit('empty-action')" />
            </td>
          </tr>
          <template v-else>
            <tr v-for="(row, i) in rows" :key="i"
              class="group transition-colors"
              :class="[hoverable ? 'hover:bg-zinc-50/80 cursor-pointer' : '', striped && i % 2 === 1 ? 'bg-zinc-50/30' : 'bg-white']"
              @click="$emit('row-click', row)">
              <td v-for="col in columns" :key="col.key"
                class="px-5 text-sm"
                :class="[
                  col.align === 'right' ? 'text-right' : col.align === 'center' ? 'text-center' : 'text-left',
                  compact ? 'py-3' : 'py-4',
                  col.bold ? 'font-semibold text-zinc-900' : 'text-zinc-600'
                ]">
                <slot :name="'cell-' + col.key" :row="row" :value="row[col.key]">
                  {{ formatCell(row, col) }}
                </slot>
              </td>
              <td v-if="$slots.action" class="px-5 text-right" :class="compact ? 'py-3' : 'py-4'">
                <slot name="action" :row="row" />
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
    <div v-if="$slots.footer" class="px-5 py-4 border-t border-zinc-200 bg-zinc-50/50">
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
