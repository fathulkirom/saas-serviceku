<template>
  <div class="sk-data-table-root">
    <!-- TOOLBAR -->
    <div v-if="showToolbar" class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-4">
      <div class="flex items-center gap-2 flex-wrap">
        <!-- Search -->
        <div v-if="searchable" class="relative">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4" :style="{ color: 'var(--text-muted)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          <input
            v-model="searchQuery"
            type="text"
            :placeholder="searchPlaceholder"
            class="pl-9 pr-3 py-2 text-sm rounded-xl border transition-all w-56 focus:w-72"
            :style="{ background: 'var(--bg-input)', color: 'var(--text-primary)', borderColor: 'var(--border-color)' }"
            @input="onSearch"
          />
        </div>

        <!-- Bulk Actions -->
        <div v-if="selectedRows.length > 0" class="flex items-center gap-2">
          <span class="sk-label-sm">{{ selectedRows.length }} dipilih</span>
          <slot name="bulk-actions" :selected="selectedRows" />
        </div>
      </div>

      <div class="flex items-center gap-2">
        <!-- Column Toggle -->
        <div v-if="showColumnToggle" class="relative" ref="columnToggleRef">
          <button
            @click="showColumnMenu = !showColumnMenu"
            class="px-3 py-2 text-sm rounded-xl border transition-all flex items-center gap-1.5"
            :style="{ background: 'var(--bg-card)', color: 'var(--text-secondary)', borderColor: 'var(--border-color)' }"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
            </svg>
            Kolom
          </button>
          <!-- Column Menu -->
          <div v-if="showColumnMenu" class="absolute right-0 mt-1 w-56 rounded-xl border shadow-lg z-50 py-1" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
            <div v-for="col in columns" :key="col.key" class="flex items-center gap-2 px-3 py-2 hover:bg-zinc-50 cursor-pointer" @click="toggleColumn(col.key)">
              <input type="checkbox" :checked="!hiddenColumns.has(col.key)" class="rounded" />
              <span class="text-sm">{{ col.label }}</span>
            </div>
          </div>
        </div>

        <!-- Export -->
        <button v-if="exportable" @click="$emit('export')" class="px-3 py-2 text-sm rounded-xl border transition-all flex items-center gap-1.5" :style="{ background: 'var(--bg-card)', color: 'var(--text-secondary)', borderColor: 'var(--border-color)' }">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
          </svg>
          Export
        </button>

        <!-- Per Page -->
        <select v-if="pageSizeOptions" v-model="currentPageSize" class="px-2 py-2 text-sm rounded-xl border" :style="{ background: 'var(--bg-input)', color: 'var(--text-primary)', borderColor: 'var(--border-color)' }" @change="$emit('update:pageSize', currentPageSize)">
          <option v-for="n in pageSizeOptions" :key="n" :value="n">{{ n }}</option>
        </select>
      </div>
    </div>

    <!-- TABLE -->
    <div class="relative rounded-2xl border overflow-hidden" :style="{ borderColor: 'var(--border-color)' }">
      <!-- Loading Overlay -->
      <div v-if="loading" class="absolute inset-0 z-20 bg-white/60 flex items-center justify-center">
        <div class="sk-animate-spin w-8 h-8 border-2 border-indigo-500 border-t-transparent rounded-full"></div>
      </div>

      <div class="overflow-x-auto" ref="tableContainerRef">
        <table class="w-full text-left border-collapse">
          <!-- HEADER -->
          <thead>
            <tr :style="{ background: 'var(--bg-hover)', borderBottom: '1px solid var(--border-light)' }">
              <!-- Checkbox column -->
              <th v-if="selectable" class="sticky top-0 z-10 px-4 py-3 w-10" :style="{ background: 'var(--bg-hover)' }">
                <input
                  type="checkbox"
                  :checked="allSelected"
                  :indeterminate="someSelected && !allSelected"
                  @change="toggleAll"
                  class="rounded"
                />
              </th>
              <!-- Data columns -->
              <th
                v-for="col in visibleColumns"
                :key="col.key"
                class="sticky top-0 z-10 px-4 py-3 text-xs font-bold uppercase tracking-wider select-none"
                :class="[
                  col.sortable ? 'cursor-pointer hover:text-zinc-700' : '',
                  col.align === 'right' ? 'text-right' : col.align === 'center' ? 'text-center' : 'text-left',
                ]"
                :style="{ color: 'var(--text-muted)', background: 'var(--bg-hover)', minWidth: col.width || 'auto', maxWidth: col.maxWidth || 'none' }"
                @click="col.sortable && toggleSort(col.key)"
              >
                <div class="flex items-center gap-1" :class="col.align === 'right' ? 'justify-end' : col.align === 'center' ? 'justify-center' : 'justify-start'">
                  {{ col.label }}
                  <span v-if="col.sortable && sortKey === col.key" class="text-xs">
                    {{ sortDir === 'asc' ? '↑' : '↓' }}
                  </span>
                </div>
                <!-- Resize handle -->
                <div
                  v-if="resizable"
                  class="absolute right-0 top-0 bottom-0 w-1 cursor-col-resize hover:bg-indigo-300"
                  @mousedown.stop="startResize($event, col.key)"
                ></div>
              </th>
            </tr>
          </thead>

          <!-- BODY -->
          <tbody>
            <!-- Skeleton rows -->
            <template v-if="loading && rows.length === 0">
              <tr v-for="i in skeletonCount" :key="'skeleton-'+i">
                <td v-if="selectable" class="px-4 py-3"><div class="skeleton h-4 w-4 rounded" /></td>
                <td v-for="col in visibleColumns" :key="col.key" class="px-4 py-3">
                  <div class="skeleton h-4 rounded" :style="{ width: (60 + Math.random() * 30) + '%' }"></div>
                </td>
              </tr>
            </template>

            <!-- Empty State -->
            <tr v-else-if="!rows.length">
              <td :colspan="totalCols" class="px-4 py-16">
                <div class="flex flex-col items-center justify-center text-center">
                  <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-3" :style="{ background: 'var(--bg-hover)' }">
                    <svg class="w-7 h-7" :style="{ color: 'var(--text-muted)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                  </div>
                  <p class="sk-label mb-1">{{ emptyTitle }}</p>
                  <p class="sk-body-xs max-w-xs">{{ emptyDescription }}</p>
                </div>
              </td>
            </tr>

            <!-- Data rows -->
            <tr
              v-for="(row, i) in sortedRows"
              :key="rowKey ? row[rowKey] : i"
              class="group transition-colors"
              :class="[
                hoverable ? 'hover:bg-zinc-50/80 cursor-pointer' : '',
                striped && i % 2 === 1 ? 'bg-zinc-50/30' : '',
                selectedRows.includes(row[rowKey]) ? 'bg-indigo-50/50' : '',
              ]"
              :style="{ borderBottom: '1px solid var(--border-light)' }"
              @click="onRowClick(row)"
            >
              <!-- Checkbox -->
              <td v-if="selectable" class="px-4 py-3" @click.stop>
                <input
                  type="checkbox"
                  :checked="selectedRows.includes(row[rowKey])"
                  @change="toggleRow(row)"
                  class="rounded"
                />
              </td>
              <!-- Data cells -->
              <td
                v-for="col in visibleColumns"
                :key="col.key"
                class="px-4 text-sm"
                :class="[
                  col.align === 'right' ? 'text-right' : col.align === 'center' ? 'text-center' : 'text-left',
                  compact ? 'py-2.5' : 'py-3',
                  col.bold ? 'font-semibold' : '',
                ]"
                :style="{ color: col.bold ? 'var(--text-primary)' : 'var(--text-secondary)' }"
              >
                <slot :name="'cell-' + col.key" :row="row" :value="row[col.key]">
                  {{ formatCell(row, col) }}
                </slot>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- PAGINATION -->
      <div v-if="showPagination && totalPages > 1" class="flex flex-col sm:flex-row items-center justify-between gap-3 px-5 py-3 border-t" :style="{ borderColor: 'var(--border-light)' }">
        <span class="sk-caption">
          Menampilkan {{ ((currentPage - 1) * currentPageSize) + 1 }} - {{ Math.min(currentPage * currentPageSize, totalRows) }} dari {{ totalRows }}
        </span>
        <div class="flex items-center gap-1">
          <button
            :disabled="currentPage <= 1"
            @click="goToPage(currentPage - 1)"
            class="w-8 h-8 rounded-lg flex items-center justify-center text-sm transition-colors disabled:opacity-30"
            :style="{ color: 'var(--text-secondary)' }"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
          </button>
          <button
            v-for="p in visiblePages"
            :key="p"
            @click="goToPage(p)"
            class="w-8 h-8 rounded-lg flex items-center justify-center text-sm font-semibold transition-all"
            :class="p === currentPage ? 'text-white shadow-sm' : ''"
            :style="p === currentPage ? { background: 'var(--primary)' } : { color: 'var(--text-secondary)' }"
          >
            {{ p }}
          </button>
          <button
            :disabled="currentPage >= totalPages"
            @click="goToPage(currentPage + 1)"
            class="w-8 h-8 rounded-lg flex items-center justify-center text-sm transition-colors disabled:opacity-30"
            :style="{ color: 'var(--text-secondary)' }"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

/**
 * ═══════════════════════════════════════════════════════════
 * ENTERPRISE DATA TABLE
 * ═══════════════════════════════════════════════════════════
 *
 * Fitur:
 * - Sticky Header
 * - Column Resize
 * - Column Hide/Show
 * - Sorting (client-side)
 * - Search (client-side)
 * - Pagination (client-side)
 * - Bulk Select
 * - Row Click
 * - Loading Skeleton
 * - Export trigger
 * - Responsive scroll
 * - Empty State
 *
 * @example
 * <SkDataTable
 *   :columns="columns"
 *   :rows="data"
 *   searchable
 *   selectable
 *   exportable
 *   @row-click="handleClick"
 * >
 *   <template #cell-status="{ value }">
 *     <SkBadge :variant="value">{{ value }}</SkBadge>
 *   </template>
 * </SkDataTable>
 */
const props = defineProps({
  columns: { type: Array, required: true },
  rows: { type: Array, default: () => [] },
  rowKey: { type: String, default: 'id' },
  // Features
  searchable: { type: Boolean, default: false },
  searchPlaceholder: { type: String, default: 'Cari...' },
  selectable: { type: Boolean, default: false },
  exportable: { type: Boolean, default: false },
  resizable: { type: Boolean, default: false },
  sortable: { type: Boolean, default: true },
  // Display
  striped: { type: Boolean, default: false },
  hoverable: { type: Boolean, default: true },
  compact: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
  skeletonCount: { type: Number, default: 5 },
  // Toolbar
  showToolbar: { type: Boolean, default: true },
  showColumnToggle: { type: Boolean, default: false },
  // Pagination
  showPagination: { type: Boolean, default: true },
  pageSize: { type: Number, default: 10 },
  pageSizeOptions: { type: Array, default: () => [10, 25, 50, 100] },
  // Empty state
  emptyTitle: { type: String, default: 'Belum ada data' },
  emptyDescription: { type: String, default: 'Data akan muncul di sini setelah tersedia.' },
});

const emit = defineEmits([
  'row-click', 'export', 'update:pageSize',
  'update:selected', 'update:sort',
]);

// Search
const searchQuery = ref('');
const onSearch = () => { currentPage.value = 1; };

// Column visibility
const hiddenColumns = ref(new Set());
const showColumnMenu = ref(false);
const columnToggleRef = ref(null);

const toggleColumn = (key) => {
  const newSet = new Set(hiddenColumns.value);
  if (newSet.has(key)) newSet.delete(key); else newSet.add(key);
  hiddenColumns.value = newSet;
};

// Click outside column menu
const handleClickOutside = (e) => {
  if (columnToggleRef.value && !columnToggleRef.value.contains(e.target)) {
    showColumnMenu.value = false;
  }
};
onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => document.removeEventListener('click', handleClickOutside));

// Sorting
const sortKey = ref('');
const sortDir = ref('asc');

const toggleSort = (key) => {
  if (sortKey.value === key) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortKey.value = key;
    sortDir.value = 'asc';
  }
  currentPage.value = 1;
};

// Filtering & Sorting
const visibleColumns = computed(() => props.columns.filter(c => !hiddenColumns.value.has(c.key)));

const filteredRows = computed(() => {
  let data = [...props.rows];
  // Search filter
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase();
    data = data.filter(row =>
      visibleColumns.value.some(col => {
        const val = row[col.key];
        return val !== null && val !== undefined && String(val).toLowerCase().includes(q);
      })
    );
  }
  // Sort
  if (sortKey.value) {
    const key = sortKey.value;
    const dir = sortDir.value === 'asc' ? 1 : -1;
    data.sort((a, b) => {
      const va = a[key], vb = b[key];
      if (va == null && vb == null) return 0;
      if (va == null) return 1;
      if (vb == null) return -1;
      if (typeof va === 'number' && typeof vb === 'number') return (va - vb) * dir;
      return String(va).localeCompare(String(vb), 'id') * dir;
    });
  }
  return data;
});

// Pagination
const currentPage = ref(1);
const currentPageSize = ref(props.pageSize);

const totalRows = computed(() => filteredRows.value.length);
const totalPages = computed(() => Math.max(1, Math.ceil(totalRows.value / currentPageSize.value)));

const sortedRows = computed(() => {
  const start = (currentPage.value - 1) * currentPageSize.value;
  return filteredRows.value.slice(start, start + currentPageSize.value);
});

const visiblePages = computed(() => {
  const pages = [];
  const total = totalPages.value;
  const current = currentPage.value;
  let start = Math.max(1, current - 2);
  let end = Math.min(total, current + 2);
  if (end - start < 4) {
    if (start === 1) end = Math.min(total, start + 4);
    else start = Math.max(1, end - 4);
  }
  for (let i = start; i <= end; i++) pages.push(i);
  return pages;
});

const goToPage = (p) => {
  if (p >= 1 && p <= totalPages.value) currentPage.value = p;
};

// Selection
const selectedRows = ref([]);

const allSelected = computed(() => {
  const ids = sortedRows.value.map(r => r[props.rowKey]);
  return ids.length > 0 && ids.every(id => selectedRows.value.includes(id));
});

const someSelected = computed(() => {
  const ids = sortedRows.value.map(r => r[props.rowKey]);
  return ids.some(id => selectedRows.value.includes(id));
});

const toggleAll = () => {
  const ids = sortedRows.value.map(r => r[props.rowKey]);
  if (allSelected.value) {
    selectedRows.value = selectedRows.value.filter(id => !ids.includes(id));
  } else {
    const newIds = ids.filter(id => !selectedRows.value.includes(id));
    selectedRows.value = [...selectedRows.value, ...newIds];
  }
  emit('update:selected', selectedRows.value);
};

const toggleRow = (row) => {
  const id = row[props.rowKey];
  const idx = selectedRows.value.indexOf(id);
  if (idx >= 0) {
    selectedRows.value.splice(idx, 1);
  } else {
    selectedRows.value.push(id);
  }
  emit('update:selected', selectedRows.value);
};

const onRowClick = (row) => {
  emit('row-click', row);
};

// Column resize
const tableContainerRef = ref(null);
const startResize = (e, colKey) => {
  e.preventDefault();
  const startX = e.clientX;
  const col = props.columns.find(c => c.key === colKey);
  const startWidth = parseInt(col?.width || '150');

  const onMouseMove = (ev) => {
    const diff = ev.clientX - startX;
    const newWidth = Math.max(60, startWidth + diff);
    if (col) col.width = newWidth + 'px';
  };
  const onMouseUp = () => {
    document.removeEventListener('mousemove', onMouseMove);
    document.removeEventListener('mouseup', onMouseUp);
  };
  document.addEventListener('mousemove', onMouseMove);
  document.addEventListener('mouseup', onMouseUp);
};

// Format cell
const formatCell = (row, col) => {
  const val = row[col.key];
  if (val === null || val === undefined) return '-';
  if (col.format === 'number') return new Intl.NumberFormat('id-ID').format(val);
  if (col.format === 'currency') return 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
  if (col.format === 'date') {
    return val ? new Date(val).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-';
  }
  if (col.format === 'datetime') {
    return val ? new Date(val).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '-';
  }
  return val;
};

const totalCols = computed(() => visibleColumns.value.length + (props.selectable ? 1 : 0));

watch(() => props.pageSize, (v) => { currentPageSize.value = v; });
</script>

<style scoped>
.skeleton {
  background: linear-gradient(90deg, var(--bg-hover) 25%, var(--border-light) 50%, var(--bg-hover) 75%);
  background-size: 200% 100%;
  animation: sk-skeleton 1.5s ease-in-out infinite;
  border-radius: 0.375rem;
}
</style>
