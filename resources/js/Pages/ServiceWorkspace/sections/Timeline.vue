<template>
  <SkCard title="Timeline Aktivitas" size="md">
    <div v-if="!timelineEntries.length" class="py-8">
      <SkEmptyState variant="empty" title="Belum ada aktivitas" description="Aktivitas servis akan muncul di sini." />
    </div>

    <div v-else class="relative">
      <!-- Timeline line -->
      <div class="absolute left-[15px] top-2 bottom-2 w-px" :style="{ background: 'var(--border-light)' }"></div>

      <div class="space-y-4">
        <!-- Backend worklogs -->
        <div v-for="(entry, i) in timelineEntries" :key="'wl-'+i"
          class="flex gap-3 relative"
        >
          <!-- Dot -->
          <div class="relative z-10 flex-shrink-0 mt-0.5">
            <div class="w-[11px] h-[11px] rounded-full border-2" :style="{ background: entry.color, borderColor: entry.borderColor }"></div>
          </div>

          <!-- Content -->
          <div class="flex-1 min-w-0 pb-3">
            <div class="flex items-center gap-2 mb-0.5">
              <span class="text-xs font-bold" :style="{ color: 'var(--text-primary)' }">{{ entry.title }}</span>
              <span v-if="entry.user" class="text-[10px]" :style="{ color: 'var(--text-muted)' }">oleh {{ entry.user }}</span>
            </div>
            <p v-if="entry.description" class="sk-caption">{{ entry.description }}</p>
            <p class="text-[10px] mt-1" :style="{ color: 'var(--text-muted)' }">
              {{ entry.time }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </SkCard>
</template>

<script setup>
import { computed } from 'vue';
import SkCard from '@/Enterprise/Components/Cards/Card.vue';
import SkEmptyState from '@/Enterprise/Components/Empty/EmptyState.vue';

const props = defineProps({
  data: { type: Object, required: true },
});

/**
 * Builds a unified timeline from:
 * 1. Service created timestamp
 * 2. Worklog entries from backend
 */
const timelineEntries = computed(() => {
  const entries = [];

  // Service created
  if (props.data?.created_at || props.data?.id) {
    entries.push({
      title: 'Servis Dibuat',
      description: `Tracking #${props.data.tracking_code}`,
      time: formatTime(props.data.created_at || new Date().toISOString()),
      color: '#3B82F6',
      borderColor: '#BFDBFE',
      user: props.data.creator?.name || 'Sistem',
    });
  }

  // Worklog entries
  (props.data?.worklogs || []).forEach(log => {
    const isStatusChange = log.action === 'status_change' || log.metadata?.from;
    entries.push({
      title: isStatusChange
        ? `Status: ${statusLabel(log.metadata?.from || '?')} → ${statusLabel(log.metadata?.to || '?')}`
        : (log.action || 'Update'),
      description: log.description || '',
      time: formatTime(log.created_at),
      color: isStatusChange ? '#22C55E' : '#6B7280',
      borderColor: isStatusChange ? '#BBF7D0' : '#E5E7EB',
      user: log.user?.name,
    });
  });

  // Service completed
  if (props.data?.selesai_at || props.data?.status === 'selesai' || props.data?.status === 'siap_diambil' || props.data?.status === 'close') {
    entries.push({
      title: 'Servis Selesai',
      description: 'Pengerjaan selesai',
      time: formatTime(props.data.selesai_at || new Date().toISOString()),
      color: '#10B981',
      borderColor: '#A7F3D0',
    });
  }

  return entries;
});

function formatTime(dateStr) {
  if (!dateStr) return '-';
  return new Date(dateStr).toLocaleDateString('id-ID', {
    day: 'numeric', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
  });
}

function statusLabel(s) {
  const labels = {
    menunggu_alokasi: 'Menunggu',
    diterima: 'Diterima',
    diagnosa: 'Diagnosa',
    dikerjakan: 'Dikerjakan',
    menunggu_konfirmasi_pelanggan: 'Konfirmasi',
    indent: 'Indent',
    onpartner: 'Di Partner',
    selesai: 'Selesai',
    siap_diambil: 'Siap Ambil',
    cancel: 'Dibatalkan',
    close: 'Ditutup',
  };
  return labels[s] || s || '?';
}
</script>
