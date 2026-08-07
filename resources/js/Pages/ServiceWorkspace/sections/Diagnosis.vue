<template>
  <SkCard title="Diagnosa Teknisi" size="md">
    <!-- Loading State -->
    <div v-if="isSaving && !showForm" class="py-8 text-center">
      <div class="animate-spin text-2xl mb-2">⏳</div>
      <p class="text-sm" :style="{ color: 'var(--text-muted)' }">Menyimpan diagnosis...</p>
    </div>

    <!-- Empty/New State -->
    <div v-else-if="!diagnosis && !showForm" class="py-6 space-y-3 text-center">
      <SkEmptyState variant="empty" title="Belum ada diagnosa"
        description="Teknisi dapat memulai diagnosa untuk mendokumentasikan hasil pemeriksaan." />
      <button v-if="canDiagnose" @click="openForm"
        class="px-4 py-2 rounded-xl text-sm font-bold text-white transition"
        style="background: var(--primary)">
        🔍 Mulai Diagnosa
      </button>
    </div>

    <!-- Edit Form -->
    <div v-else-if="showForm" class="space-y-4">
      <div class="flex items-center justify-between mb-1">
        <h4 class="text-sm font-bold" :style="{ color: 'var(--text-primary)' }">{{ diagnosis ? 'Edit Diagnosa' : 'Diagnosa Baru' }}</h4>
        <button v-if="diagnosis" @click="cancelEdit" class="text-xs px-2 py-1 rounded" :style="{ color: 'var(--text-muted)', background: 'var(--bg-hover)' }">Batal</button>
      </div>

      <div>
        <label class="block text-[10px] font-bold uppercase tracking-wider mb-1" :style="{ color: 'var(--text-muted)' }">Keluhan Customer</label>
        <textarea v-model="form.customer_complaint" rows="2" class="w-full rounded-lg border text-sm p-2.5 resize-none" :style="{ borderColor: 'var(--border-light)', background: 'var(--bg-surface)', color: 'var(--text-primary)' }" placeholder="Tulis keluhan customer..."></textarea>
      </div>

      <div>
        <label class="block text-[10px] font-bold uppercase tracking-wider mb-1" :style="{ color: 'var(--text-muted)' }">Hasil Pemeriksaan <span style="color: var(--danger)">*</span></label>
        <textarea v-model="form.findings" rows="3" class="w-full rounded-lg border text-sm p-2.5 resize-none" :style="{ borderColor: errors.findings ? 'var(--danger)' : 'var(--border-light)', background: 'var(--bg-surface)', color: 'var(--text-primary)' }" placeholder="Jelaskan hasil pemeriksaan teknisi..."></textarea>
        <p v-if="errors.findings" class="text-[10px] mt-1" style="color: var(--danger)">{{ errors.findings }}</p>
      </div>

      <div>
        <label class="block text-[10px] font-bold uppercase tracking-wider mb-1" :style="{ color: 'var(--text-muted)' }">Akar Masalah</label>
        <textarea v-model="form.cause" rows="2" class="w-full rounded-lg border text-sm p-2.5 resize-none" :style="{ borderColor: 'var(--border-light)', background: 'var(--bg-surface)', color: 'var(--text-primary)' }" placeholder="Penyebab utama kerusakan..."></textarea>
      </div>

      <div>
        <label class="block text-[10px] font-bold uppercase tracking-wider mb-1" :style="{ color: 'var(--text-muted)' }">Solusi / Tindakan <span style="color: var(--danger)">*</span></label>
        <textarea v-model="form.solution" rows="2" class="w-full rounded-lg border text-sm p-2.5 resize-none" :style="{ borderColor: errors.solution ? 'var(--danger)' : 'var(--border-light)', background: 'var(--bg-surface)', color: 'var(--text-primary)' }" placeholder="Tindakan perbaikan yang direkomendasikan..."></textarea>
        <p v-if="errors.solution" class="text-[10px] mt-1" style="color: var(--danger)">{{ errors.solution }}</p>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-wider mb-1" :style="{ color: 'var(--text-muted)' }">Estimasi Waktu (menit)</label>
          <input type="number" v-model.number="form.estimated_minutes" min="1" class="w-full rounded-lg border text-sm p-2.5" :style="{ borderColor: 'var(--border-light)', background: 'var(--bg-surface)', color: 'var(--text-primary)' }" placeholder="60" />
        </div>
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-wider mb-1" :style="{ color: 'var(--text-muted)' }">Estimasi Biaya (Rp)</label>
          <input type="number" v-model.number="form.estimated_cost" min="0" class="w-full rounded-lg border text-sm p-2.5" :style="{ borderColor: 'var(--border-light)', background: 'var(--bg-surface)', color: 'var(--text-primary)' }" placeholder="0" />
        </div>
      </div>

      <div v-if="saveError" class="p-2 rounded-lg text-xs" style="background: var(--danger-soft); color: var(--danger-text)">{{ saveError }}</div>
      <div class="flex gap-2 pt-2">
        <button @click="cancelEdit" class="flex-1 px-4 py-2 rounded-xl text-sm font-bold border" :style="{ borderColor: 'var(--border-light)', color: 'var(--text-primary)' }">Batal</button>
        <button @click="saveDiagnosis" :disabled="isSaving" class="flex-1 px-4 py-2 rounded-xl text-sm font-bold text-white transition disabled:opacity-50" style="background: var(--primary)">
          {{ isSaving ? 'Menyimpan...' : 'Simpan Diagnosis' }}
        </button>
      </div>
    </div>

    <!-- Display Mode -->
    <div v-else class="space-y-4">
      <div class="flex items-center justify-between">
        <h4 class="text-sm font-bold" :style="{ color: 'var(--text-primary)' }">Detail Diagnosa</h4>
        <button v-if="canDiagnose" @click="openForm" class="text-xs px-3 py-1 rounded-lg font-bold transition" :style="{ background: 'var(--primary-soft)', color: 'var(--primary)' }">✏️ Edit</button>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
        <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
          <p class="text-[10px] uppercase tracking-wider mb-1" :style="{ color: 'var(--text-muted)' }">Estimasi Waktu</p>
          <p class="text-sm font-semibold" :style="{ color: 'var(--text-primary)' }">{{ diagnosis.estimated_minutes ? formatMinutes(diagnosis.estimated_minutes) : '-' }}</p>
        </div>
        <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
          <p class="text-[10px] uppercase tracking-wider mb-1" :style="{ color: 'var(--text-muted)' }">Estimasi Biaya</p>
          <p class="text-sm font-semibold" :style="{ color: 'var(--text-primary)' }">Rp {{ formatNumber(diagnosis.estimated_cost || 0) }}</p>
        </div>
        <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
          <p class="text-[10px] uppercase tracking-wider mb-1" :style="{ color: 'var(--text-muted)' }">Didiagnosa Oleh</p>
          <p class="text-sm font-semibold" :style="{ color: 'var(--text-primary)' }">{{ diagnosis.diagnostician?.name || '-' }}</p>
        </div>
      </div>

      <div>
        <p class="sk-section-title mb-2">📝 Keluhan Customer</p>
        <p class="sk-body-sm">{{ diagnosis.customer_complaint || 'Tidak ada catatan.' }}</p>
      </div>
      <div>
        <p class="sk-section-title mb-2">🔍 Hasil Pemeriksaan</p>
        <p class="sk-body-sm">{{ diagnosis.findings || '-' }}</p>
      </div>
      <div>
        <p class="sk-section-title mb-2">🎯 Akar Masalah</p>
        <p class="sk-body-sm">{{ diagnosis.cause || 'Belum ditentukan.' }}</p>
      </div>
      <div>
        <p class="sk-section-title mb-2">🛠️ Solusi / Tindakan</p>
        <p class="sk-body-sm">{{ diagnosis.solution || '-' }}</p>
      </div>
    </div>
  </SkCard>
</template>

<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import SkCard from '@/Enterprise/Components/Cards/Card.vue';
import SkEmptyState from '@/Enterprise/Components/Empty/EmptyState.vue';
import { useFormatter } from '@/Composables/useFormatter.js';

const props = defineProps({
  diagnosis: { type: Object, default: null },
  serviceId: { type: [Number, String], default: null },
});

const page = usePage();
const { formatNumber } = useFormatter();

const showForm = ref(false);
const isSaving = ref(false);
const saveError = ref('');
const errors = ref({});

const form = ref({
  customer_complaint: '',
  findings: '',
  cause: '',
  solution: '',
  estimated_minutes: null,
  estimated_cost: null,
});

const userRole = computed(() => page.props.auth?.user?.role || '');
const canDiagnose = computed(() => ['owner', 'admin', 'technician'].includes(userRole.value));

function openForm() {
  if (props.diagnosis) {
    form.value = {
      customer_complaint: props.diagnosis.customer_complaint || '',
      findings: props.diagnosis.findings || '',
      cause: props.diagnosis.cause || '',
      solution: props.diagnosis.solution || '',
      estimated_minutes: props.diagnosis.estimated_minutes || null,
      estimated_cost: props.diagnosis.estimated_cost || null,
    };
  }
  errors.value = {};
  saveError.value = '';
  showForm.value = true;
}

function cancelEdit() { showForm.value = false; errors.value = {}; saveError.value = ''; }

async function saveDiagnosis() {
  errors.value = {};
  if (!form.value.findings?.trim()) { errors.value.findings = 'Hasil pemeriksaan wajib diisi.'; return; }
  if (!form.value.solution?.trim()) { errors.value.solution = 'Solusi / tindakan wajib diisi.'; return; }

  isSaving.value = true; saveError.value = '';
  try {
    const id = props.serviceId || page.props.workspace?.service?.id;
    const response = await fetch(`/services/${id}/diagnosis`, {
      method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': page.props.csrf_token || '', 'Accept': 'application/json' },
      body: JSON.stringify(form.value),
    });
    if (!response.ok) { const data = await response.json().catch(() => ({})); throw new Error(data.message || 'Gagal menyimpan diagnosis.'); }
    showForm.value = false;
    window.location.reload();
  } catch (e) { saveError.value = e.message; }
  finally { isSaving.value = false; }
}

function formatMinutes(minutes) {
  if (!minutes) return '-';
  if (minutes < 60) return minutes + ' menit';
  const h = Math.floor(minutes / 60); const m = minutes % 60;
  return m > 0 ? h + ' jam ' + m + ' menit' : h + ' jam';
}
</script>
