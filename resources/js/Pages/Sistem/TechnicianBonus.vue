<template>
  <AuthenticatedLayout>
    <div class="flex flex-col min-h-screen" :style="{ background: 'var(--bg-app)' }">
      <div class="px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto w-full py-6 space-y-6">
        <div>
          <h1 class="text-xl font-bold" :style="{ color: 'var(--text-primary)' }">💰 Bonus Teknisi</h1>
          <p class="text-sm mt-0.5" :style="{ color: 'var(--text-muted)' }">Konfigurasi bonus, riwayat, approval, dan rekap per teknisi.</p>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="p-4 rounded-xl border" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
            <p class="text-xs" :style="{ color: 'var(--text-muted)' }">Pending Approval</p>
            <p class="text-xl font-bold" :style="{ color: 'var(--warning-text)' }">Rp {{ formatNumber(pendingTotal) }}</p>
          </div>
          <div class="p-4 rounded-xl border" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
            <p class="text-xs" :style="{ color: 'var(--text-muted)' }">Disetujui</p>
            <p class="text-xl font-bold" :style="{ color: 'var(--success-text)' }">Rp {{ formatNumber(approvedTotal) }}</p>
          </div>
        </div>

        <!-- Bonus Config -->
        <div class="rounded-xl border p-5 space-y-4" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
          <h3 class="font-semibold text-sm" :style="{ color: 'var(--text-primary)' }">⚙️ Konfigurasi Bonus Teknisi</h3>
          <form @submit.prevent="submitConfig" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
              <label class="text-xs font-medium" :style="{ color: 'var(--text-secondary)' }">Teknisi</label>
              <select v-model="configForm.user_id" required class="w-full rounded-lg text-sm mt-1 border" :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-input)' }">
                <option value="">Pilih...</option>
                <option v-for="t in technicians" :key="t.id" :value="t.id">{{ t.name }}</option>
              </select>
            </div>
            <div>
              <label class="text-xs font-medium" :style="{ color: 'var(--text-secondary)' }">Tipe Bonus</label>
              <select v-model="configForm.bonus_type" class="w-full rounded-lg text-sm mt-1 border" :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-input)' }">
                <option value="percentage">Persentase</option>
                <option value="fixed">Fixed</option>
                <option value="per_category">Per Kategori</option>
                <option value="combined">Kombinasi</option>
              </select>
            </div>
            <div v-if="configForm.bonus_type === 'percentage' || configForm.bonus_type === 'combined'">
              <label class="text-xs font-medium" :style="{ color: 'var(--text-secondary)' }">Persentase (%)</label>
              <KInput v-model="configForm.percentage" type="number" min="0" max="100" step="0.5" class="w-full rounded-lg text-sm mt-1" />
            </div>
            <div v-if="configForm.bonus_type === 'fixed' || configForm.bonus_type === 'combined'">
              <label class="text-xs font-medium" :style="{ color: 'var(--text-secondary)' }">Fixed / Service (Rp)</label>
              <KInput v-model="configForm.fixed_amount" type="number" min="0" class="w-full rounded-lg text-sm mt-1" />
            </div>
            <div>
              <label class="flex items-center gap-2 mt-5">
                <input type="checkbox" v-model="configForm.exclude_warranty_rework" class="rounded" />
                <span class="text-xs" :style="{ color: 'var(--text-secondary)' }">Skip warranty rework</span>
              </label>
            </div>
            <div>
              <label class="text-xs font-medium" :style="{ color: 'var(--text-secondary)' }">Gaji Pokok (Rp)</label>
              <KInput v-model="configForm.base_salary" type="number" min="0" class="w-full rounded-lg text-sm mt-1" />
            </div>
            <div class="flex items-end">
              <KButton type="submit" :disabled="configForm.processing" class="px-5 py-2 rounded-lg text-sm font-bold text-white" style="background: var(--info)">
                {{ configForm.processing ? '...' : 'Simpan Konfig' }}
              </KButton>
            </div>
          </form>

          <!-- Existing Configs -->
          <div v-if="configs.length" class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-3 border-t" :style="{ borderColor: 'var(--border-light)' }">
            <div v-for="c in configs" :key="c.id" class="px-3 py-2 rounded-lg text-xs flex items-center justify-between" :style="{ background: 'var(--bg-hover)' }">
              <span :style="{ color: 'var(--text-primary)' }">{{ c.user?.name }}</span>
              <span :style="{ color: 'var(--text-muted)' }">{{ c.bonus_type }} {{ c.percentage > 0 ? c.percentage + '%' : '' }} {{ c.fixed_amount > 0 ? 'Rp' + formatNumber(c.fixed_amount) : '' }}</span>
            </div>
          </div>
        </div>

        <!-- Bonus Records -->
        <div class="rounded-xl border overflow-hidden" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
          <div class="p-4 border-b flex items-center justify-between" :style="{ borderColor: 'var(--border-color)' }">
            <h3 class="font-semibold text-sm" :style="{ color: 'var(--text-primary)' }">📋 Riwayat Bonus</h3>
            <button v-if="pendingRecords.length" @click="approveAll" class="text-xs px-3 py-1 rounded font-bold text-white" style="background: var(--success)">Approve Semua ({{ pendingRecords.length }})</button>
          </div>
          <div v-if="records?.data?.length" class="divide-y" :style="{ borderColor: 'var(--border-light)' }">
            <div v-for="r in records.data" :key="r.id" class="px-4 py-2.5 flex items-center justify-between gap-4">
              <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold" :style="{ color: 'var(--text-primary)' }">{{ r.user?.name }} — Rp {{ formatNumber(r.amount) }}</p>
                <p class="text-xs" :style="{ color: 'var(--text-muted)' }">Service #{{ r.service_id }} · {{ r.bonus_type }} · {{ formatDate(r.created_at) }}</p>
              </div>
              <div class="shrink-0 flex items-center gap-2">
                <span class="text-xs px-2 py-0.5 rounded-full font-semibold" :style="r.status === 'approved' ? {background:'var(--success-soft)',color:'var(--success-text)'} : {background:'var(--warning-soft)',color:'var(--warning-text)'}">{{ r.status }}</span>
                <button v-if="r.status === 'pending'" @click="approveOne(r)" class="text-xs px-2 py-0.5 rounded" :style="{ background: 'var(--info-soft)', color: 'var(--info-text)' }">Approve</button>
              </div>
            </div>
          </div>
          <div v-else class="p-8 text-center"><p class="text-sm" :style="{ color: 'var(--text-muted)' }">Belum ada catatan bonus.</p></div>
        </div>

        <!-- Recap -->
        <div v-if="recap?.length" class="rounded-xl border p-5" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
          <h3 class="font-semibold text-sm mb-3" :style="{ color: 'var(--text-primary)' }">📊 Rekap Bonus per Teknisi (Disetujui)</h3>
          <div class="space-y-2">
            <div v-for="r in recap" :key="r.user_id" class="flex items-center justify-between px-3 py-2 rounded" :style="{ background: 'var(--bg-hover)' }">
              <span class="text-sm font-semibold" :style="{ color: 'var(--text-primary)' }">{{ r.user?.name }}</span>
              <div class="text-right">
                <p class="text-sm font-bold" :style="{ color: 'var(--success-text)' }">Rp {{ formatNumber(r.total_bonus) }}</p>
                <p class="text-xs" :style="{ color: 'var(--text-muted)' }">{{ r.service_count }} service</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { useForm, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import KButton from '@/Components/KButton.vue';
import KInput from '@/Components/KInput.vue';
import { useFormatter } from '@/Composables/useFormatter.js';

const props = defineProps({
  configs: Array, technicians: Array, records: Object,
  pendingTotal: Number, approvedTotal: Number, recap: Array,
});

const { formatNumber, formatDate } = useFormatter();

const pendingRecords = computed(() => props.records?.data?.filter(r => r.status === 'pending') || []);

const configForm = useForm({
  user_id: '', bonus_type: 'percentage', percentage: 10, fixed_amount: 0,
  category_rates: null, base_salary: 0, exclude_warranty_rework: true, is_active: true,
});
const submitConfig = () => configForm.post(route('technician-bonus.config'), { preserveScroll: true, onSuccess: () => configForm.reset() });

const approveOne = (r) => router.post(route('technician-bonus.approve', r.id), {}, { preserveScroll: true });
const approveAll = () => {
  const ids = pendingRecords.value.map(r => r.id);
  router.post(route('technician-bonus.approve-batch'), { ids }, { preserveScroll: true });
};
</script>
