<template>
  <div class="enterprise-data-table">
    <!-- ═══════════ TOOLBAR ═══════════ -->
    <div v-if="schema.showToolbar !== false" class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-4">
      <!-- Search + Quick Filters -->
      <div class="flex items-center gap-2 flex-wrap flex-1">
        <div v-if="schema.searchable !== false" class="relative">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4" :style="{ color: 'var(--text-muted)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          <input
            :value="searchQuery"
            @input="search($event.target.value)"
            :placeholder="`Cari ${schema.title || ''}...`"
            class="pl-9 pr-3 py-2 text-sm rounded-xl border outline-none w-48 focus:w-64 transition-all"
            :style="{ background: 'var(--bg-input)', color: 'var(--text-primary)', borderColor: 'var(--border-color)' }"
          />
        </div>

        <!-- Quick Filters -->
        <select
          v-for="filter in quickFilters"
          :key="filter.key"
          @change="setFilter(filter.key, $event.target.value)"
          class="px-3 py-2 text-sm rounded-xl border"
          :style="{ background: 'var(--bg-input)', color: 'var(--text-primary)', borderColor: 'var(--border-color)' }"
        >
          <option value="">{{ filter.placeholder || filter.label }}</option>
          <option v-for="opt in (filter.options || [])" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
        </select>

        <!-- Active Filters Badge -->
        <button v-if="hasActiveFilters" @click="clearFilters()"
          class="px-2.5 py-1.5 text-xs font-semibold rounded-lg flex items-center gap-1"
          :style="{ background: 'var(--primary-soft)', color: 'var(--primary)' }">
          ✕ Clear Filters
        </button>
      </div>

      <!-- Right Actions -->
      <div class="flex items-center gap-2 flex-shrink-0">
        <!-- Column Chooser -->
        <div class="relative" ref="columnMenuRef">
          <button @click="showColumnMenu = !showColumnMenu"
            class="px-3 py-2 text-sm rounded-xl border flex items-center gap-1.5"
            :style="{ background: 'var(--bg-card)', color: 'var(--text-secondary)', borderColor: 'var(--border-color)' }">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
            Kolom
          </button>
          <div v-if="showColumnMenu" class="absolute right-0 mt-1 w-56 rounded-xl border shadow-lg z-50 py-1"
            :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
            <div v-for="col in allColumns" :key="col.key"
              class="flex items-center gap-2 px-3 py-2 cursor-pointer hover:bg-zinc-50 text-sm"
              @click="toggleColumn(col.key)">
              <input type="checkbox" :checked="!hiddenColumnKeys.includes(col.key)" class="rounded" />
              <span :style="{ color: 'var(--text-primary)' }">{{ col.label }}</span>
            </div>
            <div class="border-t px-3 py-2" :style="{ borderColor: 'var(--border-light)' }">
              <button @click="resetColumns()" class="text-xs font-semibold" :style="{ color: 'var(--primary)' }">Reset Kolom</button>
            </div>
          </div>
        </div>

        <!-- Export -->
        <button v-if="schema.exportable" @click="exportData()"
          class="px-3 py-2 text-sm rounded-xl border flex items-center gap-1.5"
          :style="{ background: 'var(--bg-card)', color: 'var(--text-secondary)', borderColor: 'var(--border-color)' }">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
          Export
        </button>
      </div>
    </div>

    <!-- ═══════════ BULK TOOLBAR ═══════════ -->
    <div v-if="selected.length > 0" class="flex items-center gap-3 px-4 py-2.5 rounded-xl mb-3"
      :style="{ background: 'var(--primary-soft)', border: '1px solid var(--primary-soft-border)' }">
      <span class="text-sm font-semibold" :style="{ color: 'var(--primary)' }">{{ selected.length }} dipilih</span>
      <button v-for="action in schema.bulkActions" :key="action.id"
        @click="executeBulk(action.id)"
        class="px-2.5 py-1 text-xs font-semibold rounded-lg transition-colors"
        :style="action.variant === 'danger' ? { background: 'var(--danger)', color: '#fff' } : { background: 'var(--primary)', color: '#fff' }">
        {{ action.icon }} {{ action.label }}
      </button>
      <button @click="selected = []" class="ml-auto text-xs font-semibold" :style="{ color: 'var(--text-secondary)' }">Batal</button>
    </div>

    <!-- ═══════════ TABLE ═══════════ -->
    <div class="relative rounded-2xl border overflow-hidden" :style="{ borderColor: 'var(--border-color)' }">
      <!-- Loading Overlay -->
      <div v-if="isLoading" class="absolute inset-0 z-20 bg-white/60 flex items-center justify-center">
        <div class="sk-animate-spin w-8 h-8 border-2 border-indigo-500 border-t-transparent rounded-full"></div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <!-- HEADER -->
          <thead>
            <tr :style="{ background: 'var(--bg-hover)', borderBottom: '1px solid var(--border-light)' }">
              <!-- Checkbox -->
              <th v-if="schema.selectable !== false" class="sticky top-0 z-10 px-3 py-3 w-10" :style="{ background: 'var(--bg-hover)' }">
                <input type="checkbox" :checked="allSelected" :indeterminate.prop="someSelected" @change="toggleAll()" class="rounded" />
              </th>

              <!-- Data Columns -->
              <th v-for="col in orderedColumns" :key="col.key"
                class="sticky top-0 z-10 px-4 py-3 text-xs font-bold uppercase tracking-wider select-none"
                :class="[
                  col.sortable ? 'cursor-pointer hover:text-zinc-700' : '',
                  col.align === 'right' ? 'text-right' : col.align === 'center' ? 'text-center' : 'text-left',
                  col.pinned ? 'sticky z-20' : '',
                ]"
                :style="headerStyle(col)"
                @click="col.sortable && toggleSort(col.key)">
                <span class="inline-flex items-center gap-1">
                  {{ col.label }}
                  <span v-if="col.sortable" class="text-[10px]">{{ getSortIcon(col.key) }}</span>
                </span>
              </th>
            </tr>
          </thead>

          <!-- BODY -->
          <tbody>
            <tr v-if="!data.length" class="h-32">
              <td :colspan="visibleColumns.length + (schema.selectable !== false ? 1 : 0)" class="text-center">
                <div class="py-12 flex flex-col items-center gap-3">
                  <div class="w-12 h-12 rounded-2xl flex items-center justify-center" :style="{ background: 'var(--bg-hover)' }">
                    <svg class="w-6 h-6" :style="{ color: 'var(--text-muted)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                  </div>
                  <p class="sk-label">Belum ada data</p>
                  <p class="sk-caption">Data akan muncul di sini setelah tersedia.</p>
                </div>
              </td>
            </tr>

            <tr v-for="(row, i) in data" :key="row[schema.rowKey || 'id'] || i"
              class="group transition-colors"
              :class="[
                'hover:bg-zinc-50/80',
                density === 'compact' ? 'h-10' : '',
                selected.includes(row[schema.rowKey || 'id']) ? 'bg-indigo-50/50' : '',
              ]"
              :style="{ borderBottom: '1px solid var(--border-light)' }"
              @click="$emit('row-click', row)">
              
              <!-- Checkbox -->
              <td v-if="schema.selectable !== false" class="px-3 py-3" @click.stop>
                <input type="checkbox" :checked="selected.includes(row[schema.rowKey || 'id'])" @change="toggleRow(row[schema.rowKey || 'id'])" class="rounded" />
              </td>

              <!-- Cells -->
              <td v-for="col in orderedColumns" :key="col.key"
                class="px-4 text-sm"
                :class="[
                  col.align === 'right' ? 'text-right' : col.align === 'center' ? 'text-center' : 'text-left',
                  density === 'compact' ? 'py-2' : 'py-3',
                  col.bold ? 'font-semibold' : '',
                  col.truncate ? 'truncate max-w-[250px]' : '',
                  col.pinned ? 'sticky z-10' : '',
                ]"
                :style="cellStyle(col, row)">
                <!-- Dynamic Cell Renderer -->
                <slot :name="'cell-' + col.key" :row="row" :value="row[col.key]" :column="col">
                  <span v-if="col.type === 'currency'" class="font-medium">Rp {{ formatNumber(row[col.key]) }}</span>
                  <span v-else-if="col.type === 'badge' || col.type === 'status'">
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full"
                      :style="statusBadgeStyle(row[col.key], col)">{{ statusBadgeLabel(row[col.key], col) }}</span>
                  </span>
                  <span v-else-if="col.type === 'boolean'">{{ row[col.key] ? '✅' : '—' }}</span>
                  <span v-else-if="col.type === 'date'">{{ formatDate(row[col.key]) }}</span>
                  <span v-else-if="col.type === 'datetime'">{{ formatDateTime(row[col.key]) }}</span>
                  <span v-else-if="col.type === 'actions'" class="flex items-center gap-1 justify-center">
                    <slot name="actions" :row="row" />
                  </span>
                  <span v-else :style="{ color: col.bold ? 'var(--text-primary)' : 'var(--text-secondary)' }">
                    {{ row[col.key] ?? col.emptyText ?? '-' }}
                  </span>
                </slot>
              </td>
            </tr>
          </tbody>

          <!-- FOOTER (Aggregates) -->
          <tfoot v-if="hasAggregates">
            <tr :style="{ background: 'var(--bg-hover)', borderTop: '2px solid var(--border-light)' }">
              <td v-if="schema.selectable !== false"></td>
              <td v-for="col in orderedColumns" :key="col.key"
                class="px-4 py-2 text-xs font-bold"
                :class="col.align === 'right' ? 'text-right' : 'text-left'"
                :style="{ color: 'var(--text-primary)' }">
                <template v-if="col.aggregate && aggregates[col.key] !== undefined">
                  {{ col.type === 'currency' ? 'Rp ' + formatNumber(aggregates[col.key]) : formatNumber(aggregates[col.key]) }}
                </template>
              </td>
            </tr>
          </tfoot>
        </table>
      </div>

      <!-- ═══════════ PAGINATION ═══════════ -->
      <div v-if="pagination.last_page > 1" class="flex items-center justify-between px-5 py-3 border-t"
        :style="{ borderColor: 'var(--border-light)' }">
        <span class="sk-caption">
          {{ pagination.from }}–{{ pagination.to }} dari {{ pagination.total }}
        </span>
        <div class="flex items-center gap-1">
          <button :disabled="currentPage <= 1" @click="goToPage(currentPage - 1)"
            class="w-8 h-8 rounded-lg flex items-center justify-center text-sm disabled:opacity-30"
            :style="{ color: 'var(--text-secondary)' }">‹</button>
          <button v-for="p in visiblePages" :key="p" @click="goToPage(p)"
            class="w-8 h-8 rounded-lg flex items-center justify-center text-sm font-semibold"
            :style="p === currentPage ? { background: 'var(--primary)', color: '#fff' } : { color: 'var(--text-secondary)' }">{{ p }}</button>
          <button :disabled="currentPage >= pagination.last_page" @click="goToPage(currentPage + 1)"
            class="w-8 h-8 rounded-lg flex items-center justify-center text-sm disabled:opacity-30"
            :style="{ color: 'var(--text-secondary)' }">›</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useDataTable } from '@/Enterprise/Data/composables/useDataTable.js';

const props = defineProps({
  tableProps: { type: Object, default: () => ({}) },
});

defineEmits(['row-click']);

const {
  schema, data, pagination, allColumns, visibleColumns, hiddenColumnKeys,
  searchQuery, activeSort, activeFilters, isLoading, hasActiveFilters,
  selected, allSelected, someSelected, currentPage,
  aggregates, density,
  toggleRow, toggleAll, toggleColumn, resetColumns,
  toggleSort, getSortIcon,
  search, setFilter, clearFilters,
  goToPage, executeBulk, exportData,
  handleKeydown,
} = useDataTable({ tableProps: props.tableProps });

const showColumnMenu = ref(false);
const columnMenuRef = ref(null);

// Click outside to close column menu
function onClickOutside(e) {
  if (columnMenuRef.value && !columnMenuRef.value.contains(e.target)) {
    showColumnMenu.value = false;
  }
}
onMounted(() => document.addEventListener('click', onClickOutside));
onUnmounted(() => document.removeEventListener('click', onClickOutside));
onMounted(() => document.addEventListener('keydown', handleKeydown));
onUnmounted(() => document.removeEventListener('keydown', handleKeydown));

const quickFilters = computed(() => (schema.value?.filters || []).filter(f => f.quick));

const hasAggregates = computed(() => allColumns.value.some(c => c.aggregate));

const orderedColumns = computed(() => {
  let cols = [...visibleColumns.value];
  cols.sort((a, b) => (a.order || 0) - (b.order || 0));
  return cols;
});

const visiblePages = computed(() => {
  const pages = [];
  const total = pagination.value?.last_page || 1;
  const current = currentPage.value;
  let start = Math.max(1, current - 2);
  let end = Math.min(total, current + 2);
  for (let i = start; i <= end; i++) pages.push(i);
  return pages;
});

function headerStyle(col) {
  return { color: 'var(--text-muted)', minWidth: col.width || col.minWidth || 'auto', maxWidth: col.maxWidth || 'none' };
}

function cellStyle(col, row) {
  const style = {};
  if (col.pinned) style.background = 'var(--bg-card)';
  return style;
}

// ── Formatters ──
function formatNumber(n) {
  if (n === null || n === undefined) return '-';
  return new Intl.NumberFormat('id-ID').format(Number(n));
}

function formatDate(d) {
  if (!d) return '-';
  return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

function formatDateTime(d) {
  if (!d) return '-';
  return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

const statusLabelMap = {
  menunggu_alokasi: 'Menunggu', diterima: 'Diterima', dikerjakan: 'Dikerjakan',
  selesai: 'Selesai', siap_diambil: 'Siap Ambil', cancel: 'Dibatalkan',
  paid: 'Lunas', unpaid: 'Belum Bayar', draft: 'Draft',
  active: 'Aktif', inactive: 'Nonaktif',
};

const statusColorMap = {
  menunggu_alokasi: { bg: '#FEF3C7', text: '#92400E' },
  diterima: { bg: '#DBEAFE', text: '#1E40AF' },
  dikerjakan: { bg: '#FCE7F3', text: '#9D174D' },
  selesai: { bg: '#DCFCE7', text: '#166534' },
  siap_diambil: { bg: '#D1FAE5', text: '#065F46' },
  cancel: { bg: '#FEE2E2', text: '#991B1B' },
  paid: { bg: '#DCFCE7', text: '#166534' },
  unpaid: { bg: '#FEE2E2', text: '#991B1B' },
  active: { bg: '#DCFCE7', text: '#166534' },
};

function statusBadgeStyle(val, col) {
  const c = statusColorMap[val] || { bg: 'var(--bg-hover)', text: 'var(--text-secondary)' };
  return { background: c.bg, color: c.text };
}

function statusBadgeLabel(val, col) {
  return statusLabelMap[val] || val || '-';
}
</script>
