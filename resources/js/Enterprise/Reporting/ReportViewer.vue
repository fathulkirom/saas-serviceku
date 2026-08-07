<template>
  <div class="space-y-6">
    <!-- ═══════════ HEADER ═══════════ -->
    <div class="flex items-center justify-between flex-wrap gap-3">
      <div>
        <h1 class="sk-heading-4">{{ schema?.title || 'Report' }}</h1>
        <p v-if="schema?.description" class="sk-caption mt-1">{{ schema.description }}</p>
      </div>
      <div class="flex items-center gap-2">
        <select v-model="selectedReport" @change="loadReport" class="px-3 py-2 text-sm rounded-xl border"
          :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-input)', color: 'var(--text-primary)' }">
          <option v-for="r in availableReports" :key="r.id" :value="r.id">{{ r.title }}</option>
        </select>
        <button @click="loadReport" class="px-3 py-2 text-sm rounded-xl border" :style="{ color: 'var(--text-secondary)', borderColor: 'var(--border-color)' }">
          🔄 Refresh
        </button>
        <button v-if="schema?.exportable" @click="$emit('export', 'pdf')" class="px-3 py-2 text-sm rounded-xl border"
          :style="{ color: 'var(--text-secondary)', borderColor: 'var(--border-color)' }">📥 Export</button>
      </div>
    </div>

    <!-- ═══════════ FILTERS ═══════════ -->
    <div v-if="schema?.filters?.length" class="flex items-center gap-3 flex-wrap p-4 rounded-2xl border"
      :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-card)' }">
      <div v-for="filter in schema.filters" :key="filter.id" class="flex items-center gap-2">
        <label class="text-xs font-semibold" :style="{ color: 'var(--text-secondary)' }">{{ filter.label }}</label>
        <select v-if="filter.type === 'select'" v-model="filterValues[filter.id]"
          class="px-2.5 py-1.5 text-xs rounded-lg border" :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-input)' }">
          <option value="">Semua</option>
          <option v-for="opt in (filter.options || [])" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
        </select>
        <input v-else-if="filter.type === 'date_range'" type="date" v-model="filterValues[filter.id]"
          class="px-2.5 py-1.5 text-xs rounded-lg border" :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-input)' }" />
        <input v-else v-model="filterValues[filter.id]"
          class="px-2.5 py-1.5 text-xs rounded-lg border w-32" :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-input)' }" />
      </div>
      <button @click="loadReport" class="px-3 py-1.5 text-xs font-semibold rounded-lg text-white"
        :style="{ background: 'var(--primary)' }">Terapkan</button>
    </div>

    <!-- ═══════════ KPI GRID ═══════════ -->
    <KPIGrid v-if="schema?.chartType === 'kpi'" :metrics="kpiMetrics" />

    <!-- ═══════════ CHART VIEWER ═══════════ -->
    <ChartViewer v-if="chartData && schema?.chartType !== 'kpi' && schema?.chartType !== 'table'"
      :chartType="schema?.chartType || 'bar'" :chartData="chartData" />

    <!-- ═══════════ DATA TABLE ═══════════ -->
    <div v-if="reportData?.length" class="rounded-2xl border overflow-hidden" :style="{ borderColor: 'var(--border-color)' }">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr :style="{ background: 'var(--bg-hover)', borderBottom: '1px solid var(--border-light)' }">
              <th v-for="dim in schema.dimensions" :key="dim.id"
                class="px-4 py-3 text-xs font-bold uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">
                {{ dim.label }}
              </th>
              <th v-for="metric in schema.metrics" :key="metric.id"
                class="px-4 py-3 text-xs font-bold uppercase tracking-wider text-right" :style="{ color: 'var(--text-muted)' }">
                {{ metric.label }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, i) in reportData" :key="i" :style="{ borderBottom: '1px solid var(--border-light)' }"
              class="hover:bg-zinc-50/80 transition-colors">
              <td v-for="dim in schema.dimensions" :key="dim.id" class="px-4 py-3 text-sm"
                :style="{ color: 'var(--text-primary)' }">{{ row[dim.field] ?? '-' }}</td>
              <td v-for="metric in schema.metrics" :key="metric.id" class="px-4 py-3 text-sm text-right font-semibold"
                :style="{ color: 'var(--text-primary)' }">
                <span v-if="metric.format === 'currency'">Rp {{ formatNumber(row[metric.id]) }}</span>
                <span v-else>{{ formatNumber(row[metric.id]) }}</span>
              </td>
            </tr>
          </tbody>
          <!-- Footer totals -->
          <tfoot>
            <tr :style="{ background: 'var(--bg-hover)', borderTop: '2px solid var(--border-light)' }">
              <td v-for="dim in schema.dimensions" :key="dim.id"></td>
              <td v-for="metric in schema.metrics" :key="metric.id"
                class="px-4 py-3 text-sm text-right font-bold" :style="{ color: 'var(--text-primary)' }">
                <span v-if="metric.format === 'currency'">Rp {{ formatNumber(computedMetrics?.[metric.id]) }}</span>
                <span v-else>{{ formatNumber(computedMetrics?.[metric.id]) }}</span>
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- Empty state -->
    <div v-if="!reportData?.length && !chartData" class="py-16 text-center rounded-2xl border"
      :style="{ borderColor: 'var(--border-color)' }">
      <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-3" :style="{ background: 'var(--bg-hover)' }">
        <svg class="w-7 h-7" :style="{ color: 'var(--text-muted)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
      </div>
      <p class="sk-label">Belum ada data</p>
      <p class="sk-caption mt-1">Pilih filter dan klik Terapkan.</p>
    </div>

    <!-- Generated timestamp -->
    <p v-if="generatedAt" class="text-[11px] text-right" :style="{ color: 'var(--text-muted)' }">
      Dibuat: {{ formatDateTime(generatedAt) }}
    </p>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import KPIGrid from './KPIGrid.vue';
import ChartViewer from './ChartViewer.vue';

const page = usePage();
const reportProps = computed(() => page.props.reportProps || {});

const schema = computed(() => reportProps.value?.schema || {});
const chartData = computed(() => reportProps.value?.chartData || null);
const reportData = computed(() => reportProps.value?.data || []);
const computedMetrics = computed(() => reportProps.value?.metrics || {});
const generatedAt = computed(() => reportProps.value?.generatedAt || null);
const availableReports = computed(() => reportProps.value?.availableReports || []);

const selectedReport = ref(schema.value?.id || '');
const filterValues = reactive({});

const kpiMetrics = computed(() => {
  if (!computedMetrics.value || !schema.value?.metrics) return [];
  return schema.value.metrics.map(m => ({
    ...m,
    value: computedMetrics.value[m.id] ?? 0,
  }));
});

function loadReport() {
  const params = new URLSearchParams({ report: selectedReport.value, ...filterValues });
  window.location.search = params.toString();
}

function formatNumber(n) {
  if (n === null || n === undefined) return '-';
  return new Intl.NumberFormat('id-ID').format(Number(n));
}

function formatDateTime(d) {
  if (!d) return '-';
  return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}
</script>
