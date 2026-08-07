<template>
  <div class="space-y-5">
    <!-- ═══════════ QC STATUS ═══════════ -->
    <SkCard title="🔬 Quality Control" size="md">
      <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-4">
        <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
          <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Status Servis</p>
          <p class="text-sm font-bold mt-1" :style="{ color: statusColor }">{{ statusLabel }}</p>
        </div>
        <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
          <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">QC Hari Ini</p>
          <p class="text-sm font-bold mt-1" :style="{ color: qcDoneToday ? 'var(--success)' : 'var(--text-muted)' }">
            {{ qcDoneToday ? '✅ Selesai' : '⏳ Belum' }}
          </p>
        </div>
        <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
          <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Hasil Terakhir</p>
          <p class="text-sm font-bold mt-1" :style="{ color: lastQcResult === 'pass' ? 'var(--success)' : lastQcResult === 'fail' ? 'var(--danger)' : 'var(--text-muted)' }">
            {{ lastQcResult === 'pass' ? '✅ LULUS' : lastQcResult === 'fail' ? '❌ GAGAL' : '-' }}
          </p>
        </div>
      </div>

      <!-- QC already done today → show results -->
      <div v-if="qcDoneToday" class="space-y-3">
        <div class="p-3 rounded-xl" :style="{ background: lastQcResult === 'pass' ? 'var(--success-soft)' : 'var(--danger-soft)' }">
          <p class="text-sm font-bold" :style="{ color: lastQcResult === 'pass' ? 'var(--success-text)' : 'var(--danger-text)' }">
            {{ lastQcResult === 'pass' ? '✅ QC LULUS — Servis siap diambil.' : '❌ QC GAGAL — Servis dikembalikan ke perbaikan.' }}
          </p>
        </div>
        <div class="space-y-1">
          <div v-for="check in existingChecks" :key="check.item" class="flex items-center justify-between py-1.5 px-3 rounded-lg" :style="{ background: check.result === 'pass' ? 'var(--success-soft)' : check.result === 'fail' ? 'var(--danger-soft)' : 'var(--bg-hover)' }">
            <span class="text-sm font-medium" :style="{ color: 'var(--text-primary)' }">{{ check.item }}</span>
            <span class="text-xs font-bold px-2 py-0.5 rounded-full" :style="checkBadgeStyle(check.result)">{{ check.result === 'pass' ? '✅' : check.result === 'fail' ? '❌' : '⏳' }}</span>
          </div>
        </div>
      </div>

      <!-- QC NOT yet done → show QC form (only for authorized roles) -->
      <div v-else-if="canQC">
        <div class="p-3 rounded-xl mb-4 text-sm font-medium" :style="{ background: 'var(--info-soft)', color: 'var(--info-text)' }">
          📋 Lakukan pengecekan kualitas untuk setiap item. Pilih Pass/Fail dan beri catatan bila perlu.
        </div>

        <form @submit.prevent="submitQC" class="space-y-4">
          <!-- QC Checklist Items -->
          <div class="space-y-2">
            <div v-for="(item, idx) in qcItems" :key="idx" class="flex items-center gap-3 p-3 rounded-xl" :style="{ background: 'var(--bg-hover)' }">
              <div class="flex-1">
                <p class="text-sm font-bold" :style="{ color: 'var(--text-primary)' }">{{ item.item }}</p>
                <input
                  v-if="item.result === 'fail'"
                  v-model="item.notes"
                  type="text"
                  class="w-full mt-1 rounded-lg border text-xs p-1.5"
                  :style="{ borderColor: 'var(--border-light)', background: 'var(--bg-surface)', color: 'var(--text-primary)' }"
                  placeholder="Catatan kenapa fail..."
                />
              </div>
              <div class="flex gap-1">
                <button
                  type="button"
                  @click="item.result = 'pass'; item.notes = ''"
                  class="px-2.5 py-1 rounded-lg text-xs font-bold transition"
                  :style="item.result === 'pass' ? { background: 'var(--success)', color: '#fff' } : { background: 'var(--bg-surface)', color: 'var(--text-muted)' }"
                >✅</button>
                <button
                  type="button"
                  @click="item.result = 'fail'"
                  class="px-2.5 py-1 rounded-lg text-xs font-bold transition"
                  :style="item.result === 'fail' ? { background: 'var(--danger)', color: '#fff' } : { background: 'var(--bg-surface)', color: 'var(--text-muted)' }"
                >❌</button>
              </div>
            </div>
          </div>

          <!-- QC Decision -->
          <div>
            <label class="block text-[10px] font-bold uppercase tracking-wider mb-1" :style="{ color: 'var(--text-muted)' }">Keputusan QC</label>
            <div class="flex gap-2">
              <button
                type="button"
                @click="qcDecision = 'pass'"
                class="flex-1 px-4 py-2.5 rounded-xl text-sm font-bold transition"
                :style="qcDecision === 'pass' ? { background: 'var(--success)', color: '#fff' } : { background: 'var(--bg-hover)', color: 'var(--text-muted)' }"
              >✅ LULUS</button>
              <button
                type="button"
                @click="qcDecision = 'fail'"
                class="flex-1 px-4 py-2.5 rounded-xl text-sm font-bold transition"
                :style="qcDecision === 'fail' ? { background: 'var(--danger)', color: '#fff' } : { background: 'var(--bg-hover)', color: 'var(--text-muted)' }"
              >❌ GAGAL</button>
            </div>
          </div>

          <!-- QC Notes -->
          <div>
            <label class="block text-[10px] font-bold uppercase tracking-wider mb-1" :style="{ color: 'var(--text-muted)' }">Catatan QC</label>
            <textarea
              v-model="qcNotes"
              rows="2"
              class="w-full rounded-xl border text-sm p-3 resize-none"
              :style="{ borderColor: 'var(--border-light)', background: 'var(--bg-surface)', color: 'var(--text-primary)' }"
              placeholder="Catatan tambahan untuk teknisi / customer..."
            ></textarea>
          </div>

          <!-- Error -->
          <div
            v-if="actionError"
            class="p-3 rounded-xl text-sm font-medium"
            :style="{ background: 'var(--danger-soft)', color: 'var(--danger-text)', border: '1px solid var(--danger-soft-border)' }"
          >{{ actionError }}</div>

          <!-- Submit -->
          <button
            type="submit"
            :disabled="actionLoading || !qcDecision"
            class="w-full px-4 py-2.5 rounded-xl text-sm font-bold text-white transition hover:opacity-90 disabled:opacity-50 flex items-center justify-center gap-2"
            :style="{ background: qcDecision === 'pass' ? 'var(--success)' : 'var(--danger)' }"
          >
            <span v-if="actionLoading" class="animate-spin">⏳</span>
            {{ qcDecision === 'pass' ? '✅ Konfirmasi QC LULUS' : '❌ Konfirmasi QC GAGAL' }}
          </button>
        </form>
      </div>

      <!-- QC not authorized -->
      <div
        v-else-if="!qcDoneToday"
        class="p-3 rounded-xl text-sm font-medium"
        :style="{ background: 'var(--warning-soft)', color: 'var(--warning-text)' }"
      >
        ⚠️ QC hanya dapat dilakukan oleh Manager atau Owner.
      </div>
    </SkCard>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import SkCard from '@/Enterprise/Components/Cards/Card.vue';

const props = defineProps({
  service: { type: Object, required: true },
  canQC: { type: Boolean, default: false },
  qcChecks: { type: Array, default: () => [] },
});

const emit = defineEmits(['refresh']);

const page = usePage();
const actionLoading = ref(false);
const actionError = ref('');
const qcDecision = ref('');
const qcNotes = ref('');

// Default QC items
const defaultItems = [
  'Touchscreen', 'Camera Depan', 'Camera Belakang', 'Charging',
  'Speaker', 'Microphone', 'Network/WiFi', 'Bluetooth',
  'Fingerprint/Face ID', 'Buttons', 'Screen Brightness', 'Battery',
];

const qcItems = ref(defaultItems.map(item => ({ item, result: 'pending', notes: '' })));

const existingChecks = computed(() => props.qcChecks || []);
const qcDoneToday = computed(() => existingChecks.value.length > 0 && existingChecks.value.some(c => c.result !== 'pending'));

const lastQcResult = computed(() => {
  const fails = existingChecks.value.filter(c => c.result === 'fail');
  if (fails.length > 0) return 'fail';
  const passes = existingChecks.value.filter(c => c.result === 'pass');
  if (passes.length > 0) return 'pass';
  return null;
});

const statusLabel = computed(() => props.service?.status_label || props.service?.status || '-');
const statusColor = computed(() => {
  const map = { selesai: 'var(--info)', siap_diambil: 'var(--success)', dikerjakan: 'var(--warning)' };
  return map[props.service?.status] || 'var(--text-muted)';
});

function checkBadgeStyle(result) {
  if (result === 'pass') return { background: 'var(--success-soft)', color: 'var(--success-text)' };
  if (result === 'fail') return { background: 'var(--danger-soft)', color: 'var(--danger-text)' };
  return { background: 'var(--bg-hover)', color: 'var(--text-muted)' };
}

async function submitQC() {
  if (!qcDecision.value) return;
  actionLoading.value = true;
  actionError.value = '';

  try {
    const checks = qcItems.value.map(item => ({
      item: item.item,
      result: item.result,
      notes: item.notes || '',
    }));

    const r = await fetch(`/services/${props.service.id}/qc`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': page.props.csrf_token || '',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        checks,
        qc_decision: qcDecision.value,
        qc_notes: qcNotes.value,
      }),
    });

    const data = await r.json().catch(() => ({}));

    if (!r.ok) {
      actionError.value = data.message || `Gagal menyimpan QC (${r.status})`;
      return;
    }

    emit('refresh');
  } catch (e) {
    actionError.value = 'Gagal terhubung ke server.';
  } finally {
    actionLoading.value = false;
  }
}
</script>
