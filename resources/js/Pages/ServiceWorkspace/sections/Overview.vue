<template>
  <div class="space-y-5">
    <!-- SERVICE INFO + CUSTOMER -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
      <!-- Service Info Card -->
      <SkCard title="Informasi Servis" size="md">
        <div class="space-y-3">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <p class="sk-caption">Tipe Unit</p>
              <p class="sk-label-sm">{{ service.device_type || '-' }}</p>
            </div>
            <div>
              <p class="sk-caption">IMEI/SN</p>
              <p class="sk-code text-xs">{{ service.imei_sn || '-' }}</p>
            </div>
          </div>
          <div>
            <p class="sk-caption">Keluhan</p>
            <p class="sk-body-sm">{{ service.problem_description || 'Tidak ada keluhan tercatat.' }}</p>
          </div>
          <div>
            <p class="sk-caption">Kondisi</p>
            <p class="sk-body-sm">{{ service.condition_note || 'Tidak ada catatan kondisi.' }}</p>
          </div>
          <div v-if="service.kelengkapan">
            <p class="sk-caption">Kelengkapan</p>
            <div class="flex flex-wrap gap-1 mt-1">
              <span v-for="(item, i) in parseKelengkapan" :key="i"
                class="text-[10px] font-medium px-2 py-0.5 rounded-full"
                :style="{ background: 'var(--bg-hover)', color: 'var(--text-secondary)' }"
              >{{ item }}</span>
            </div>
          </div>
        </div>
      </SkCard>

      <!-- Customer Card -->
      <SkCard title="Pelanggan" size="md" v-if="customerSummary">
        <div class="space-y-3">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-full flex items-center justify-center text-lg font-bold" :style="{ background: 'var(--primary-soft)', color: 'var(--primary)' }">
              {{ customerSummary.name?.charAt(0) || '?' }}
            </div>
            <div>
              <p class="font-bold" :style="{ color: 'var(--text-primary)' }">{{ customerSummary.name }}</p>
              <p class="sk-caption">{{ customerSummary.phone }}</p>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-2">
            <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
              <p class="text-xl font-bold" :style="{ color: 'var(--text-primary)' }">{{ customerSummary.service_count }}</p>
              <p class="text-[10px] uppercase tracking-wider mt-0.5" :style="{ color: 'var(--text-muted)' }">Total Servis</p>
            </div>
            <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
              <p class="text-xl font-bold" :style="{ color: 'var(--text-primary)' }">{{ customerSummary.device_count }}</p>
              <p class="text-[10px] uppercase tracking-wider mt-0.5" :style="{ color: 'var(--text-muted)' }">Device</p>
            </div>
          </div>
          <div v-if="customerSummary.risk !== 'new'">
            <p class="sk-caption">Risk Level</p>
            <span class="inline-block mt-1 text-xs font-bold px-2 py-0.5 rounded-full"
              :style="riskStyle">{{ riskLabel }}</span>
          </div>
        </div>
      </SkCard>
    </div>

    <!-- DIAGNOSIS (if exists) -->
    <SkCard v-if="service.diagnosis" title="Diagnosa Teknisi" size="md">
      <div class="space-y-3">
        <div class="grid grid-cols-2 gap-3">
          <div>
            <p class="sk-caption">Kategori</p>
            <p class="sk-label-sm">{{ service.diagnosis.issue_category || '-' }}</p>
          </div>
          <div>
            <p class="sk-caption">Severity</p>
            <span class="text-xs font-bold px-2 py-0.5 rounded-full" :style="severityStyle">{{ service.diagnosis.severity || '-' }}</span>
          </div>
        </div>
        <div>
          <p class="sk-caption">Analisa</p>
          <p class="sk-body-sm">{{ service.diagnosis.analysis || '-' }}</p>
        </div>
        <div>
          <p class="sk-caption">Root Cause</p>
          <p class="sk-body-sm">{{ service.diagnosis.root_cause || '-' }}</p>
        </div>
        <div>
          <p class="sk-caption">Solusi</p>
          <p class="sk-body-sm">{{ service.diagnosis.solution || '-' }}</p>
        </div>
      </div>
    </SkCard>

    <!-- CHECKLISTS -->
    <SkCard v-if="service.checklists?.length" title="Checklist" size="md">
      <div class="space-y-4">
        <div v-for="cl in service.checklists" :key="cl.id">
          <p class="sk-label-sm mb-2">{{ cl.name || 'Checklist' }}</p>
          <div class="space-y-1.5">
            <div v-for="item in (cl.items || [])" :key="item.id"
              class="flex items-center gap-2 px-3 py-1.5 rounded-lg"
              :style="{ background: 'var(--bg-hover)' }"
            >
              <span :class="item.checked ? 'sk-text-success' : 'sk-text-muted'" class="text-sm">●</span>
              <span class="text-xs" :style="{ color: 'var(--text-secondary)' }">{{ item.label }}</span>
            </div>
          </div>
        </div>
      </div>
    </SkCard>

    <!-- RELATED SERVICES -->
    <SkCard v-if="relatedServices.length" title="Servis Terkait" size="md">
      <SkDataTable
        :columns="relatedColumns"
        :rows="relatedServices"
        rowKey="id"
        :showToolbar="false"
        :showPagination="false"
        compact
        @row-click="(row) => navigateTo(row.id)"
      />
    </SkCard>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import SkCard from '@/Enterprise/Components/Cards/Card.vue';
import SkDataTable from '@/Enterprise/Components/Table/DataTable.vue';

const props = defineProps({
  service: { type: Object, required: true },
  customerSummary: { type: Object, default: null },
  previousServices: { type: Array, default: () => [] },
  relatedServices: { type: Array, default: () => [] },
});

const parseKelengkapan = computed(() => {
  const k = props.service?.kelengkapan;
  if (!k) return [];
  if (Array.isArray(k)) return k;
  if (typeof k === 'object') return Object.values(k);
  return [];
});

const riskStyle = computed(() => {
  const r = props.customerSummary?.risk;
  if (r === 'high') return { background: 'var(--danger-soft)', color: 'var(--danger-text)' };
  if (r === 'medium') return { background: 'var(--warning-soft)', color: 'var(--warning-text)' };
  return { background: 'var(--success-soft)', color: 'var(--success-text)' };
});

const riskLabel = computed(() => {
  const r = props.customerSummary?.risk;
  if (r === 'high') return 'High Risk';
  if (r === 'medium') return 'Medium Risk';
  return 'Low Risk';
});

const severityStyle = computed(() => {
  const s = props.service?.diagnosis?.severity;
  if (s === 'critical') return { background: 'var(--danger-soft)', color: 'var(--danger-text)' };
  if (s === 'high') return { background: 'var(--warning-soft)', color: 'var(--warning-text)' };
  return { background: 'var(--info-soft)', color: 'var(--info-text)' };
});

const relatedColumns = [
  { key: 'tracking_code', label: 'Kode', bold: true },
  { key: 'device_type', label: 'Tipe' },
  { key: 'status', label: 'Status' },
];

function navigateTo(id) {
  router.get(route('services.show', { id }));
}
</script>
