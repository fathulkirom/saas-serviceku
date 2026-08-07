<template>
  <div class="border-b px-4 sm:px-6 lg:px-8 py-3" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-light)' }">
    <div class="max-w-[1600px] mx-auto w-full">
      <div class="flex items-center gap-2 flex-wrap">

        <!-- STATUS BADGE -->
        <div class="flex items-center gap-3 mr-3">
          <span class="w-2.5 h-2.5 rounded-full" :style="{ background: statusDotColor }"></span>
          <span class="text-sm font-bold" :style="{ color: 'var(--text-primary)' }">{{ statusLabel }}</span>
        </div>

        <div class="flex-1"></div>

        <!-- TRANSITION BUTTONS (from backend workflow) -->
        <template v-if="availableTransitions.length > 0 && !isTransitioning">
          <button
            v-for="trans in availableTransitions"
            :key="trans"
            @click="$emit('transition', trans)"
            class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all hover:-translate-y-0.5 shadow-sm"
            :style="transitionStyle(trans)"
          >
            {{ transitionLabel(trans) }}
          </button>
        </template>

        <!-- Transition loading -->
        <div v-if="isTransitioning" class="flex items-center gap-2 px-3 py-1.5 text-xs font-semibold rounded-lg" :style="{ background: 'var(--bg-hover)', color: 'var(--text-muted)' }">
          <div class="sk-animate-spin w-3.5 h-3.5 border-2 border-current border-t-transparent rounded-full"></div>
          Memproses...
        </div>

        <!-- DIVIDER -->
        <div class="w-px h-6 mx-1" :style="{ background: 'var(--border-light)' }"></div>

        <!-- ROLE-SPECIFIC ACTION BUTTONS -->
        <!-- CS: Assign Technician -->
        <button
          v-if="canAssign && service.status === 'menunggu_alokasi'"
          @click="$emit('transition', 'diterima', { needs_assign: true })"
          class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all flex items-center gap-1"
          :style="{ background: 'var(--primary-soft)', color: 'var(--primary)' }"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
          Assign Teknisi
        </button>

        <!-- Technician: Start Work -->
        <button
          v-if="canWork && ['diterima', 'menunggu_alokasi'].includes(service.status)"
          @click="$emit('transition', 'dikerjakan')"
          class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all flex items-center gap-1"
          :style="{ background: 'var(--primary)', color: '#fff' }"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          Mulai Servis
        </button>

        <!-- Sprint v3.0B: Repair-specific actions -->
        <!-- Start Repair (post-approval) -->
        <button
          v-if="canWork && service.status === 'dikerjakan' && !service.dikerjakan_at"
          @click="handleRepairAction('start')"
          :disabled="repairLoading === 'start'"
          class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all flex items-center gap-1 disabled:opacity-50"
          :style="{ background: 'var(--primary)', color: '#fff' }"
        >
          <span v-if="repairLoading === 'start'" class="animate-spin inline-block w-3 h-3 border-2 border-white border-t-transparent rounded-full mr-1"></span>
          🔧 Mulai Perbaikan
        </button>

        <!-- Complete Repair -->
        <button
          v-if="canWork && service.status === 'dikerjakan' && service.dikerjakan_at"
          @click="handleRepairAction('complete')"
          :disabled="repairLoading === 'complete'"
          class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all flex items-center gap-1 disabled:opacity-50"
          :style="{ background: 'var(--success)', color: '#fff' }"
        >
          <span v-if="repairLoading === 'complete'" class="animate-spin inline-block w-3 h-3 border-2 border-white border-t-transparent rounded-full mr-1"></span>
          ✅ Selesai Perbaikan
        </button>

        <!-- QC Action -->
        <button
          v-if="canQC && service.status === 'selesai'"
          @click="navigateToQC"
          class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all flex items-center gap-1"
          :style="{ background: 'var(--warning)', color: '#fff' }"
        >
          🔬 Quality Control
        </button>

        <!-- Invoice -->
        <button
          v-if="canInvoice"
          @click="$emit('transition', 'siap_diambil')"
          class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all flex items-center gap-1"
          :style="{ background: 'var(--success-soft)', color: 'var(--success-text)' }"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          Siap Diambil
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';

/**
 * WorkspaceActionBar — role-aware action bar.
 * Tombol berubah sesuai role, status service, dan workflow.
 */
const props = defineProps({
  service: { type: Object, required: true },
  availableTransitions: { type: Array, default: () => [] },
  isTransitioning: { type: Boolean, default: false },
  transitionError: { type: String, default: '' },
  canAssign: { type: Boolean, default: false },
  canWork: { type: Boolean, default: false },
  canInvoice: { type: Boolean, default: false },
  canQC: { type: Boolean, default: false },
});

const emit = defineEmits(['transition', 'repair-action', 'navigate-qc']);

const repairLoading = ref(null);

function handleRepairAction(action) {
  emit('repair-action', action);
}

function navigateToQC() {
  emit('navigate-qc');
}

const statusColors = {
  menunggu_alokasi: '#F59E0B',
  diterima: '#3B82F6',
  diagnosa: '#8B5CF6',
  dikerjakan: '#EC4899',
  menunggu_konfirmasi_pelanggan: '#EF4444',
  menunggu_konfirmasi_internal: '#F97316',
  indent: '#6366F1',
  onpartner: '#14B8A6',
  selesai: '#22C55E',
  siap_diambil: '#10B981',
  cancel: '#6B7280',
  void: '#9CA3AF',
  close: '#374151',
};

const statusLabels = {
  menunggu_alokasi: 'Menunggu Alokasi',
  diterima: 'Diterima',
  diagnosa: 'Diagnosa',
  dikerjakan: 'Dikerjakan',
  menunggu_konfirmasi_pelanggan: 'Konfirmasi Pelanggan',
  menunggu_konfirmasi_internal: 'Konfirmasi Internal',
  indent: 'Indent Sparepart',
  onpartner: 'Di Partner',
  selesai: 'Selesai',
  siap_diambil: 'Siap Diambil',
  cancel: 'Dibatalkan',
  void: 'Void',
  close: 'Ditutup',
};

const transitionLabels = {
  diterima: 'Terima',
  diagnosa: 'Diagnosa',
  dikerjakan: 'Kerjakan',
  indent: 'Indent Part',
  onpartner: 'Ke Partner',
  selesai: 'Selesai',
  siap_diambil: 'Siap Ambil',
  menunggu_konfirmasi_pelanggan: 'Konfirmasi',
  menunggu_konfirmasi_internal: 'Konfirmasi Internal',
  cancel: 'Batalkan',
  close: 'Tutup',
};

const statusLabel = computed(() => statusLabels[props.service?.status] || props.service?.status || '-');
const statusDotColor = computed(() => statusColors[props.service?.status] || '#6B7280');

function transitionStyle(to) {
  const dangerTransitions = ['cancel', 'void'];
  const successTransitions = ['selesai', 'siap_diambil', 'close'];
  if (dangerTransitions.includes(to)) return `background: var(--danger-soft); color: var(--danger-text)`;
  if (successTransitions.includes(to)) return `background: var(--success-soft); color: var(--success-text)`;
  return `background: var(--primary-soft); color: var(--primary)`;
}

function transitionLabel(to) {
  return transitionLabels[to] || to;
}
</script>
