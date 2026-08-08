<template>
  <AuthenticatedLayout>
    <div class="flex flex-col min-h-screen" :style="{ background: 'var(--bg-app)' }">
      <div class="px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto w-full py-6 space-y-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-xl font-bold" :style="{ color: 'var(--text-primary)' }">🔧 Komplain Lintas Cabang</h1>
            <p class="text-sm mt-0.5" :style="{ color: 'var(--text-muted)' }">Kelola komplain customer yang masuk di cabang Anda untuk service dari cabang lain.</p>
          </div>
        </div>

        <!-- Complaint List -->
        <div class="rounded-xl border overflow-hidden" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
          <div class="p-4 border-b flex items-center justify-between" :style="{ borderColor: 'var(--border-color)' }">
            <h3 class="font-semibold text-sm" :style="{ color: 'var(--text-primary)' }">📋 Daftar Komplain</h3>
            <span class="text-xs px-2 py-1 rounded-full" :style="{ background: 'var(--bg-hover)', color: 'var(--text-muted)' }">{{ complaints?.total || 0 }} komplain</span>
          </div>
          <div v-if="complaints?.data?.length" class="divide-y" :style="{ borderColor: 'var(--border-light)' }">
            <div v-for="c in complaints.data" :key="c.id" class="p-4 space-y-2">
              <div class="flex items-start justify-between gap-4">
                <div class="min-w-0 flex-1">
                  <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-sm font-bold" :style="{ color: 'var(--text-primary)' }">#{{ c.id }}</span>
                    <span class="px-2 py-0.5 text-[10px] rounded-full font-semibold" :style="statusStyle(c.status)">{{ statusLabel(c.status) }}</span>
                    <span v-if="c.is_cross_branch" class="px-2 py-0.5 text-[10px] rounded-full font-semibold" :style="{ background: 'var(--warning-soft)', color: 'var(--warning-text)' }">🔄 Lintas Cabang</span>
                  </div>
                  <p class="text-sm mt-1" :style="{ color: 'var(--text-secondary)' }">{{ c.problem_description }}</p>
                  <div class="flex items-center gap-3 mt-1 text-xs" :style="{ color: 'var(--text-muted)' }">
                    <span>Service: #{{ c.service?.id }}</span>
                    <span v-if="c.customer">· {{ c.customer?.name }}</span>
                    <span>· Cabang Asal: {{ c.original_branch?.name }}</span>
                    <span>· Teknisi Asal: {{ c.original_technician?.name || '-' }}</span>
                  </div>
                </div>
                <div class="text-right shrink-0 space-y-1">
                  <p class="text-xs" :style="{ color: 'var(--text-muted)' }">{{ formatDate(c.created_at) }}</p>
                  <p v-if="c.technician" class="text-xs font-medium" :style="{ color: 'var(--info-text)' }">Teknisi: {{ c.technician.name }}</p>
                </div>
              </div>
              <div v-if="c.resolution" class="text-xs p-2 rounded" :style="{ background: 'var(--bg-hover)', color: 'var(--text-secondary)' }">
                ✅ Resolusi: {{ c.resolution }}
              </div>
              <!-- Quick actions -->
              <div v-if="c.status !== 'closed' && c.status !== 'resolved'" class="flex gap-2 pt-1">
                <button @click="updateStatus(c, 'in_progress')" class="text-xs px-3 py-1 rounded font-medium" :style="{ background: 'var(--info-soft)', color: 'var(--info-text)' }">
                  {{ c.status === 'open' ? 'Kerjakan' : 'Lanjutkan' }}
                </button>
                <button @click="resolveComplaint(c)" class="text-xs px-3 py-1 rounded font-medium" :style="{ background: 'var(--success-soft)', color: 'var(--success-text)' }">
                  Selesai
                </button>
              </div>
            </div>
          </div>
          <div v-else class="p-8 text-center">
            <p class="text-sm" :style="{ color: 'var(--text-muted)' }">Belum ada komplain di cabang ini.</p>
            <p class="text-xs mt-1" :style="{ color: 'var(--text-muted)' }">Gunakan tombol "Komplain" di halaman detail service untuk memulai.</p>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useFormatter } from '@/Composables/useFormatter.js';

const props = defineProps({
  complaints: { type: Object, default: () => ({ data: [] }) },
});

const { formatDate } = useFormatter();

const statusLabel = (s) => ({ open: 'Terbuka', in_progress: 'Dikerjakan', resolved: 'Selesai', closed: 'Ditutup' }[s] || s);
const statusStyle = (s) => {
  const map = { open: 'var(--warning-soft)', in_progress: 'var(--info-soft)', resolved: 'var(--success-soft)', closed: 'var(--bg-hover)' };
  const color = { open: 'var(--warning-text)', in_progress: 'var(--info-text)', resolved: 'var(--success-text)', closed: 'var(--text-muted)' };
  return { background: map[s] || 'var(--bg-hover)', color: color[s] || 'var(--text-muted)' };
};

const updateStatus = (complaint, status) => {
  router.put(route('services.complaint.update', complaint.id), { status }, { preserveScroll: true });
};

const resolveComplaint = (complaint) => {
  const resolution = prompt('Resolusi / catatan penyelesaian:');
  if (resolution === null) return;
  router.put(route('services.complaint.update', complaint.id), {
    status: 'resolved',
    resolution,
  }, { preserveScroll: true });
};
</script>
