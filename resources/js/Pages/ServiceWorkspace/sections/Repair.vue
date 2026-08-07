<template>
  <div class="space-y-5">
    <!-- ═══════════ REPAIR STATUS CARD ═══════════ -->
    <SkCard title="🔧 Status Perbaikan" size="md">
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
        <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
          <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Status</p>
          <p class="text-sm font-bold mt-1" :style="{ color: statusColor }">{{ statusLabel }}</p>
        </div>
        <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
          <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Mulai</p>
          <p class="text-sm font-bold mt-1" :style="{ color: 'var(--text-primary)' }">{{ dikerjakanAt || '-' }}</p>
        </div>
        <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
          <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Selesai</p>
          <p class="text-sm font-bold mt-1" :style="{ color: 'var(--text-primary)' }">{{ selesaiAt || '-' }}</p>
        </div>
        <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
          <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Teknisi</p>
          <p class="text-sm font-bold mt-1" :style="{ color: 'var(--text-primary)' }">{{ technicianName || '-' }}</p>
        </div>
      </div>

      <!-- ═══════════ REPAIR ACTIONS ═══════════ -->
      <div class="flex flex-wrap gap-2" v-if="canStartRepair || canCompleteRepair">
        <!-- Start Repair -->
        <button
          v-if="canStartRepair && !service.dikerjakan_at"
          @click="startRepair"
          :disabled="actionLoading"
          class="px-4 py-2 rounded-xl text-sm font-bold text-white transition hover:opacity-90 disabled:opacity-50 flex items-center gap-2"
          style="background: var(--primary)"
        >
          <span v-if="actionLoading === 'start'" class="animate-spin">⏳</span>
          🔧 Mulai Perbaikan
        </button>

        <!-- Complete Repair -->
        <button
          v-if="canCompleteRepair"
          @click="showCompleteForm = !showCompleteForm"
          class="px-4 py-2 rounded-xl text-sm font-bold text-white transition hover:opacity-90"
          style="background: var(--success)"
        >
          ✅ Selesaikan Perbaikan
        </button>
      </div>

      <!-- Already started -->
      <div
        v-if="service.dikerjakan_at && service.status === 'dikerjakan'"
        class="mt-3 p-3 rounded-xl text-sm font-medium flex items-center gap-2"
        :style="{ background: 'var(--info-soft)', color: 'var(--info-text)' }"
      >
        ⏱️ Perbaikan sedang berlangsung sejak {{ dikerjakanAt }}
      </div>

      <!-- Repair completed, awaiting QC -->
      <div
        v-if="service.status === 'selesai'"
        class="mt-3 p-3 rounded-xl text-sm font-medium flex items-center gap-2"
        :style="{ background: 'var(--warning-soft)', color: 'var(--warning-text)' }"
      >
        ⏳ Menunggu Quality Control...
      </div>
    </SkCard>

    <!-- ═══════════ REPAIR NOTES / WORKLOG ═══════════ -->
    <SkCard title="📝 Catatan Perbaikan" size="md" v-if="service.status === 'dikerjakan' || service.status === 'selesai'">
      <!-- Quick Note Input (only during active repair) -->
      <form v-if="canAddNote" @submit.prevent="addNote" class="flex gap-2 mb-3">
        <input
          v-model="newNote"
          type="text"
          class="flex-1 rounded-xl border text-sm p-2.5"
          :style="{ borderColor: 'var(--border-light)', background: 'var(--bg-surface)', color: 'var(--text-primary)' }"
          placeholder="Tambah catatan progress..."
          :disabled="noteLoading"
        />
        <button
          type="submit"
          :disabled="noteLoading || !newNote.trim()"
          class="px-4 py-2 rounded-xl text-xs font-bold text-white transition hover:opacity-90 disabled:opacity-50"
          style="background: var(--primary)"
        >
          <span v-if="noteLoading" class="animate-spin inline-block mr-1">⏳</span>
          Simpan
        </button>
      </form>

      <!-- Worklog Timeline (from ActivityLog repair notes) -->
      <div v-if="repairNotes.length" class="space-y-2">
        <div
          v-for="note in repairNotes"
          :key="note.id"
          class="p-2.5 rounded-xl"
          :style="{ background: 'var(--bg-hover)' }"
        >
          <div class="flex items-center justify-between mb-1">
            <span class="text-xs font-bold" :style="{ color: 'var(--primary)' }">{{ note.created_by || 'Teknisi' }}</span>
            <span class="text-[10px]" :style="{ color: 'var(--text-muted)' }">{{ formatTime(note.created_at) }}</span>
          </div>
          <p class="text-sm" :style="{ color: 'var(--text-primary)' }">{{ note.description }}</p>
        </div>
      </div>
      <div v-else class="text-center py-4">
        <p class="text-xs" :style="{ color: 'var(--text-muted)' }">Belum ada catatan perbaikan.</p>
      </div>
    </SkCard>

    <!-- ═══════════ STOCK WARNINGS ═══════════ -->
    <div v-if="stockWarnings.length" class="space-y-2">
      <div
        v-for="(w, i) in stockWarnings"
        :key="i"
        class="p-3 rounded-xl text-sm font-medium flex items-start gap-2"
        :style="{ background: 'var(--warning-soft)', color: 'var(--warning-text)', border: '1px solid var(--warning-soft-border)' }"
      >
        ⚠️ {{ w }}
      </div>
    </div>

    <!-- ═══════════ COMPLETE REPAIR FORM ═══════════ -->
    <SkCard v-if="showCompleteForm" title="📝 Selesaikan Perbaikan" size="md">
      <form @submit.prevent="completeRepair" class="space-y-4">
        <!-- Repair Notes -->
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-wider mb-1" :style="{ color: 'var(--text-muted)' }">Catatan Perbaikan</label>
          <textarea
            v-model="repairNotes"
            rows="3"
            class="w-full rounded-xl border text-sm p-3 resize-none"
            :style="{ borderColor: 'var(--border-light)', background: 'var(--bg-surface)', color: 'var(--text-primary)' }"
            placeholder="Tulis catatan perbaikan, langkah troubleshooting, hasil..."
          ></textarea>
        </div>

        <!-- Parts note (BR-FIX-01: finishing a repair does NOT consume stock) -->
        <div
          class="p-3 rounded-xl text-xs font-medium"
          :style="{ background: 'var(--info-soft)', color: 'var(--info-text)' }"
        >
          ℹ️ Menyelesaikan perbaikan tidak menghapus stok. Part dipakai dikonfirmasi oleh CS pada tab
          <strong>Sparepart</strong> (request → disetujui → dikonfirmasi saat invoice).
        </div>

        <!-- Error display -->
        <div
          v-if="actionError"
          class="p-3 rounded-xl text-sm font-medium"
          :style="{ background: 'var(--danger-soft)', color: 'var(--danger-text)', border: '1px solid var(--danger-soft-border)' }"
        >
          {{ actionError }}
        </div>

        <!-- Submit -->
        <button
          type="submit"
          :disabled="actionLoading === 'complete'"
          class="w-full px-4 py-2.5 rounded-xl text-sm font-bold text-white transition hover:opacity-90 disabled:opacity-50 flex items-center justify-center gap-2"
          style="background: var(--success)"
        >
          <span v-if="actionLoading === 'complete'" class="animate-spin">⏳</span>
          ✅ Konfirmasi Selesai Perbaikan
        </button>
      </form>
    </SkCard>

    <!-- ═══════════ ERROR TOAST ═══════════ -->
    <div
      v-if="actionError && !showCompleteForm"
      class="p-3 rounded-xl text-sm font-medium flex items-start gap-2"
      :style="{ background: 'var(--danger-soft)', color: 'var(--danger-text)', border: '1px solid var(--danger-soft-border)' }"
    >
      ❌ {{ actionError }}
      <button @click="actionError = ''" class="ml-auto text-xs font-bold">×</button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import SkCard from '@/Enterprise/Components/Cards/Card.vue';

const props = defineProps({
  service: { type: Object, required: true },
  canStartRepair: { type: Boolean, default: false },
  canCompleteRepair: { type: Boolean, default: false },
  canAddNote: { type: Boolean, default: false },
  technicianName: { type: String, default: null },
  worklogs: { type: Array, default: () => [] },
  repairNotes: { type: Array, default: () => [] },
});

const emit = defineEmits(['refresh']);

const page = usePage();
const showCompleteForm = ref(false);
const actionLoading = ref(null);
const actionError = ref('');
const repairNotes = ref('');
const newNote = ref('');
const noteLoading = ref(false);
const stockWarnings = ref([]);

const statusLabel = computed(() => props.service?.status_label || props.service?.status || '-');
const statusColor = computed(() => {
  const map = {
    dikerjakan: 'var(--warning)', selesai: 'var(--info)', siap_diambil: 'var(--success)',
    diagnosa: 'var(--primary)', menunggu_konfirmasi_pelanggan: 'var(--warning)',
  };
  return map[props.service?.status] || 'var(--text-muted)';
});

const dikerjakanAt = computed(() => {
  const d = props.service?.dikerjakan_at;
  return d ? new Date(d).toLocaleString('id-ID', { dateStyle: 'short', timeStyle: 'short' }) : null;
});

const selesaiAt = computed(() => {
  const d = props.service?.selesai_at;
  return d ? new Date(d).toLocaleString('id-ID', { dateStyle: 'short', timeStyle: 'short' }) : null;
});

function formatTime(iso) {
  if (!iso) return '-';
  return new Date(iso).toLocaleString('id-ID', { dateStyle: 'short', timeStyle: 'short' });
}

async function addNote() {
  if (!newNote.value.trim()) return;
  noteLoading.value = true;
  try {
    const r = await fetch(`/services/${props.service.id}/repair/note`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': page.props.csrf_token || '',
        'Accept': 'application/json',
      },
      body: JSON.stringify({ description: newNote.value }),
    });
    if (r.ok) {
      newNote.value = '';
      emit('refresh');
    } else {
      const data = await r.json().catch(() => ({}));
      actionError.value = data.message || 'Gagal menyimpan catatan.';
    }
  } catch {
    actionError.value = 'Gagal terhubung ke server.';
  } finally {
    noteLoading.value = false;
  }
}

async function startRepair() {
  actionLoading.value = 'start';
  actionError.value = '';
  try {
    const r = await fetch(`/services/${props.service.id}/repair/start`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': page.props.csrf_token || '',
        'Accept': 'application/json',
      },
    });

    const data = await r.json().catch(() => ({}));

    if (!r.ok) {
      actionError.value = data.message || `Gagal memulai perbaikan (${r.status})`;
      return;
    }

    // Check for stock warnings
    if (data.warnings?.length) {
      stockWarnings.value = data.warnings;
    }

    emit('refresh');
  } catch (e) {
    actionError.value = 'Gagal terhubung ke server.';
  } finally {
    actionLoading.value = null;
  }
}

async function completeRepair() {
  actionLoading.value = 'complete';
  actionError.value = '';
  try {
    // BR-FIX-01: completing the repair only marks work done. Part consumption is
    // a separate CS confirmation step — no parts_used payload.
    const r = await fetch(`/services/${props.service.id}/repair/complete`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': page.props.csrf_token || '',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        repair_notes: repairNotes.value,
      }),
    });

    const data = await r.json().catch(() => ({}));

    if (!r.ok) {
      actionError.value = data.message || `Gagal menyelesaikan perbaikan (${r.status})`;
      return;
    }

    showCompleteForm.value = false;
    repairNotes.value = '';
    emit('refresh');
  } catch (e) {
    actionError.value = 'Gagal terhubung ke server.';
  } finally {
    actionLoading.value = null;
  }
}
</script>
