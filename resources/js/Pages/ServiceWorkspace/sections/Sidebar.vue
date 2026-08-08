<template>
  <div class="space-y-4">
    <!-- CUSTOMER CARD -->
    <SkCard v-if="customerSummary" title="Pelanggan" size="sm">
      <div class="space-y-3">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold" :style="{ background: 'var(--primary-soft)', color: 'var(--primary)' }">
            {{ customerSummary.name?.charAt(0) || '?' }}
          </div>
          <div class="min-w-0">
            <p class="sk-label-sm truncate">{{ customerSummary.name }}</p>
            <p class="sk-caption">{{ customerSummary.phone }}</p>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-2 text-center">
          <div class="p-2 rounded-lg" :style="{ background: 'var(--bg-hover)' }">
            <p class="text-lg font-bold" :style="{ color: 'var(--text-primary)' }">{{ customerSummary.service_count }}</p>
            <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Servis</p>
          </div>
          <div class="p-2 rounded-lg" :style="{ background: 'var(--bg-hover)' }">
            <p class="text-lg font-bold" :style="{ color: 'var(--text-primary)' }">{{ customerSummary.device_count }}</p>
            <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Device</p>
          </div>
        </div>
        <div v-if="customerSummary.tags?.length" class="flex flex-wrap gap-1">
          <span v-for="tag in customerSummary.tags" :key="tag" class="text-[10px] font-medium px-2 py-0.5 rounded-full" :style="{ background: 'var(--bg-hover)', color: 'var(--text-secondary)' }">{{ tag }}</span>
        </div>
      </div>
    </SkCard>

    <!-- QUICK METRICS -->
    <SkCard size="sm">
      <h4 class="sk-section-title mb-3">Ringkasan</h4>
      <div class="space-y-2.5">
        <div class="flex justify-between items-center">
          <span class="sk-caption">Status</span>
          <span class="text-xs font-bold" :style="{ color: statusColor }">{{ statusLabel }}</span>
        </div>
        <div class="flex justify-between items-center">
          <span class="sk-caption">Teknisi</span>
          <span class="text-xs font-semibold" :style="{ color: 'var(--text-primary)' }">{{ service.technician?.name || 'Belum ditugaskan' }}</span>
        </div>
        <div class="flex justify-between items-center">
          <span class="sk-caption">Garansi</span>
          <span class="text-xs font-semibold" :style="{ color: service.is_warranty_claim ? 'var(--success-text)' : 'var(--text-secondary)' }">
            {{ service.is_warranty_claim ? 'Klaim Garansi' : 'Non-Garansi' }}
          </span>
        </div>
        <div class="flex justify-between items-center">
          <span class="sk-caption">Estimasi Biaya</span>
          <span class="text-xs font-bold" :style="{ color: 'var(--text-primary)' }">
            Rp {{ formatNumber(service.total_cost || service.service_charge || 0) }}
          </span>
        </div>
      </div>
    </SkCard>

    <!-- FEATURE ACCESS INDICATORS -->
    <SkCard size="sm" v-if="featureAccess">
      <h4 class="sk-section-title mb-3">Akses</h4>
      <div class="space-y-2">
        <div class="flex items-center gap-2 text-xs" v-for="(val, key) in featureAccess" :key="key">
          <span :class="val ? 'sk-text-success' : 'sk-text-muted'" class="text-xs">●</span>
          <span :style="{ color: 'var(--text-secondary)' }">{{ featureLabels[key] || key }}</span>
        </div>
      </div>
    </SkCard>

    <!-- PREVIOUS SERVICES -->
    <SkCard size="sm" v-if="previousServices.length">
      <h4 class="sk-section-title mb-3">Servis Sebelumnya</h4>
      <div class="space-y-2 max-h-[240px] overflow-y-auto">
        <div v-for="ps in previousServices.slice(0, 5)" :key="ps.id"
          class="flex items-center gap-2 px-2 py-1.5 rounded-lg cursor-pointer transition-colors"
          :style="{ background: 'transparent' }"
          @click="navigateTo(ps.id)"
        >
          <span class="w-1.5 h-1.5 rounded-full" :style="{ background: statusDot(ps.status) }"></span>
          <span class="text-xs flex-1 truncate" :style="{ color: 'var(--text-secondary)' }">#{{ ps.tracking_code }}</span>
          <span class="text-[10px]" :style="{ color: 'var(--text-muted)' }">{{ formatDate(ps.created_at) }}</span>
        </div>
      </div>
    </SkCard>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import SkCard from '@/Enterprise/Components/Cards/Card.vue';
import { useFormatter } from '@/Composables/useFormatter.js';

const props = defineProps({
  service: { type: Object, required: true },
  customerSummary: { type: Object, default: null },
  featureAccess: { type: Object, default: () => ({}) },
  canAssign: { type: Boolean, default: false },
  canWork: { type: Boolean, default: false },
});

const { formatNumber, formatDate } = useFormatter();

const statusLabels = {
  menunggu_alokasi: 'Menunggu Alokasi', diterima: 'Diterima', diagnosa: 'Diagnosa',
  dikerjakan: 'Dikerjakan', menunggu_konfirmasi_pelanggan: 'Konfirmasi Pelanggan',
  indent: 'Indent', onpartner: 'Di Partner', selesai: 'Selesai',
  siap_diambil: 'Siap Diambil', cancel: 'Dibatalkan', close: 'Ditutup',
};
const statusColors = {
  menunggu_alokasi: '#F59E0B', diterima: '#3B82F6', dikerjakan: '#EC4899',
  selesai: '#22C55E', siap_diambil: '#10B981', cancel: '#EF4444', close: '#374151',
};

const statusLabel = computed(() => statusLabels[props.service?.status] || props.service?.status);
const statusColor = computed(() => statusColors[props.service?.status] || '#6B7280');

const statusDot = (s) => statusColors[s] || '#6B7280';

const featureLabels = {
  can_assign: 'Assign Teknisi',
  can_manage_parts: 'Kelola Sparepart',
  can_invoice: 'Buat Invoice',
  can_indent: 'Indent Part',
};

function navigateTo(id) {
  router.get(route('services.show', { id }));
}
</script>
