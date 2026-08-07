import { ref, reactive, computed, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

/**
 * useDataTable — Universal Data Table Engine Composable.
 * 
 * Handles: columns, filters, sort, search, selection, pagination,
 * saved views, virtual scroll, export, bulk actions.
 * 
 * Usage:
 *   const dt = useDataTable({ tableId: 'service.index' })
 *   dt.data         // rows
 *   dt.columns      // visible columns
 *   dt.selected     // selected row keys
 *   dt.search('iPhone')
 *   dt.sort('created_at', 'desc')
 *   dt.executeBulk('delete')
 */
export function useDataTable(options = {}) {
  const page = usePage();
  const tableProps = computed(() => page.props.tableProps || options.tableProps || {});
  const schema = computed(() => tableProps.value?.schema || {});
  const rawData = computed(() => tableProps.value?.data || []);
  const pagination = computed(() => tableProps.value?.pagination || {});
  const tableParams = computed(() => tableProps.value?.params || {});

  // ── UI State ──
  const searchQuery = ref(tableParams.value?.search || '');
  const activeSort = ref(tableParams.value?.sort || schema.value?.defaultSort || {});
  const activeFilters = reactive(tableParams.value?.filters || {});
  const isLoading = ref(false);
  const currentView = ref('table');
  const density = ref('comfortable');

  // ── Column Management ──
  const allColumns = computed(() => schema.value?.columns || []);
  const hiddenColumnKeys = ref(loadSavedView()?.hiddenColumns || []);

  const visibleColumns = computed(() =>
    allColumns.value.filter(c => !hiddenColumnKeys.value.includes(c.key) && !c.hidden)
  );

  function toggleColumn(key) {
    const idx = hiddenColumnKeys.value.indexOf(key);
    if (idx >= 0) hiddenColumnKeys.value.splice(idx, 1);
    else hiddenColumnKeys.value.push(key);
    saveView();
  }

  function resetColumns() {
    hiddenColumnKeys.value = [];
    saveView();
  }

  // ── Pin columns ──
  const pinnedLeft = ref([]);
  const pinnedRight = ref([]);

  function pinColumn(key, side) {
    if (side === 'left') {
      pinnedRight.value = pinnedRight.value.filter(k => k !== key);
      if (!pinnedLeft.value.includes(key)) pinnedLeft.value.push(key);
    } else if (side === 'right') {
      pinnedLeft.value = pinnedLeft.value.filter(k => k !== key);
      if (!pinnedRight.value.includes(key)) pinnedRight.value.push(key);
    } else {
      pinnedLeft.value = pinnedLeft.value.filter(k => k !== key);
      pinnedRight.value = pinnedRight.value.filter(k => k !== key);
    }
  }

  // ── Sort ──
  function toggleSort(key) {
    const current = activeSort.value;
    if (current[key] === 'asc') {
      activeSort.value = { [key]: 'desc' };
    } else if (current[key] === 'desc') {
      const { [key]: _, ...rest } = current;
      activeSort.value = rest;
    } else {
      activeSort.value = { [key]: 'asc' };
    }
    fetchData();
  }

  function getSortIcon(key) {
    const dir = activeSort.value[key];
    if (dir === 'asc') return '↑';
    if (dir === 'desc') return '↓';
    return '↕';
  }

  // ── Search ──
  let searchTimer = null;

  function search(query) {
    searchQuery.value = query;
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => fetchData(), 300);
  }

  // ── Filters ──
  function setFilter(key, value) {
    activeFilters[key] = value;
    fetchData();
  }

  function clearFilters() {
    Object.keys(activeFilters).forEach(k => delete activeFilters[k]);
    fetchData();
  }

  const hasActiveFilters = computed(() => Object.keys(activeFilters).length > 0);

  // ── Selection ──
  const selected = ref([]);
  const lastSelectedIndex = ref(-1);

  const allSelected = computed(() =>
    rawData.value.length > 0 && selected.value.length === rawData.value.length
  );

  const someSelected = computed(() => selected.value.length > 0 && !allSelected.value);

  function toggleRow(rowKey) {
    const idx = selected.value.indexOf(rowKey);
    if (idx >= 0) selected.value.splice(idx, 1);
    else selected.value.push(rowKey);
  }

  function toggleAll() {
    if (allSelected.value) {
      selected.value = [];
    } else {
      selected.value = rawData.value.map(r => r[schema.value?.rowKey || 'id']);
    }
  }

  function selectRange(fromIndex, toIndex) {
    const keys = rawData.value.slice(
      Math.min(fromIndex, toIndex),
      Math.max(fromIndex, toIndex) + 1
    ).map(r => r[schema.value?.rowKey || 'id']);
    selected.value = [...new Set([...selected.value, ...keys])];
  }

  // ── Pagination ──
  const currentPage = ref(pagination.value?.current_page || 1);

  function goToPage(page) {
    currentPage.value = page;
    fetchData();
  }

  // ── Bulk Actions ──
  async function executeBulk(actionId) {
    const action = (schema.value?.bulkActions || []).find(a => a.id === actionId);
    if (!action) return;

    if (action.confirm && !confirm(action.confirmMessage || `Yakin menjalankan ${action.label}?`)) return;

    try {
      const endpoint = action.endpoint || window.location.href + '/bulk';
      await fetch(endpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': page.props.csrf_token || '',
          'Accept': 'application/json',
        },
        body: JSON.stringify({ action: actionId, ids: selected.value }),
      });
      selected.value = [];
      fetchData();
    } catch (e) {
      console.error('Bulk action failed:', e);
    }
  }

  // ── Export ──
  function exportData(format = 'csv') {
    const params = new URLSearchParams({
      format,
      search: searchQuery.value,
      sort: JSON.stringify(activeSort.value),
      filters: JSON.stringify(activeFilters),
    });
    window.open(`${window.location.href}/export?${params}`, '_blank');
  }

  // ── Fetch Data ──
  function fetchData() {
    isLoading.value = true;
    router.reload({
      data: {
        search: searchQuery.value,
        sort: activeSort.value,
        filters: activeFilters,
        page: currentPage.value,
        perPage: schema.value?.perPage || 25,
      },
      only: ['tableProps'],
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => { isLoading.value = false; },
      onError: () => { isLoading.value = false; },
    });
  }

  // ── Saved Views (localStorage) ──
  const viewStorageKey = computed(() => `sk-table-view-${schema.value?.id || 'default'}`);

  function loadSavedView() {
    try {
      return JSON.parse(localStorage.getItem(viewStorageKey.value) || '{}');
    } catch { return {}; }
  }

  function saveView() {
    localStorage.setItem(viewStorageKey.value, JSON.stringify({
      hiddenColumns: hiddenColumnKeys.value,
      density: density.value,
      currentView: currentView.value,
      pinnedLeft: pinnedLeft.value,
      pinnedRight: pinnedRight.value,
    }));
  }

  function resetView() {
    localStorage.removeItem(viewStorageKey.value);
    hiddenColumnKeys.value = [];
    density.value = 'comfortable';
    currentView.value = 'table';
  }

  // ── Keyboard Shortcuts ──
  function handleKeydown(e) {
    const tag = e.target?.tagName?.toLowerCase();
    if (['input', 'textarea', 'select'].includes(tag)) return;

    if (e.ctrlKey && e.key === 'a') { e.preventDefault(); toggleAll(); }
    if (e.key === 'Escape') { selected.value = []; }
    if (e.ctrlKey && e.key === 'f') { e.preventDefault(); /* focus search */ }
  }

  // ── Aggregate (footer) ──
  const aggregates = computed(() => {
    const aggCols = allColumns.value.filter(c => c.aggregate);
    const result = {};
    aggCols.forEach(col => {
      const vals = rawData.value.map(r => Number(r[col.key])).filter(v => !isNaN(v));
      if (col.aggregateType === 'sum') result[col.key] = vals.reduce((a, b) => a + b, 0);
      else if (col.aggregateType === 'avg') result[col.key] = vals.length ? vals.reduce((a, b) => a + b, 0) / vals.length : 0;
      else if (col.aggregateType === 'count') result[col.key] = vals.length;
    });
    return result;
  });

  return {
    // Data
    schema, data: rawData, pagination, allColumns, visibleColumns,

    // State
    searchQuery, activeSort, activeFilters, isLoading,
    currentView, density, hasActiveFilters,

    // Selection
    selected, allSelected, someSelected,
    toggleRow, toggleAll, selectRange,

    // Column
    hiddenColumnKeys, toggleColumn, resetColumns,
    pinnedLeft, pinnedRight, pinColumn,

    // Sort
    toggleSort, getSortIcon,

    // Search/Filter
    search, setFilter, clearFilters,

    // Pagination
    currentPage, goToPage,

    // Bulk
    executeBulk, exportData,

    // Views
    saveView, resetView, loadSavedView,

    // Aggregate
    aggregates,

    // Actions
    fetchData, handleKeydown,
  };
}
