<template>
  <AuthenticatedLayout>
    <div class="flex flex-col min-h-screen" :style="{ background: 'var(--bg-app)' }">
      <div class="px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto w-full py-6 space-y-6">
        <div>
          <h1 class="text-xl font-bold" :style="{ color: 'var(--text-primary)' }">🔧 Partner Eksternal</h1>
          <p class="text-sm mt-0.5" :style="{ color: 'var(--text-muted)' }">Kirim service ke teknisi/vendor luar. Tracking unit keluar/masuk, biaya partner, margin toko.</p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="p-4 rounded-xl border" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
            <p class="text-xs" :style="{ color: 'var(--text-muted)' }">Aktif di Partner</p>
            <p class="text-xl font-bold" :style="{ color: 'var(--info-text)' }">{{ activeCount }}</p>
          </div>
          <div class="p-4 rounded-xl border" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
            <p class="text-xs" :style="{ color: 'var(--text-muted)' }">Terlambat</p>
            <p class="text-xl font-bold" :class="overdueCount > 0 ? 'text-red-500' : ''" :style="{ color: overdueCount > 0 ? 'var(--danger-text)' : 'var(--text-muted)' }">{{ overdueCount }}</p>
          </div>
        </div>

        <!-- Add Partner -->
        <div class="rounded-xl border p-5 space-y-3" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
          <h3 class="font-semibold text-sm" :style="{ color: 'var(--text-primary)' }">➕ Tambah Partner</h3>
          <form @submit.prevent="submitPartner" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="sm:col-span-2">
              <KInput v-model="partnerForm.name" placeholder="Nama partner/vendor" required class="w-full rounded-lg text-sm" />
            </div>
            <KInput v-model="partnerForm.phone" placeholder="Telepon" class="w-full rounded-lg text-sm" />
            <KInput v-model="partnerForm.specialty" placeholder="Spesialisasi" class="w-full rounded-lg text-sm" />
            <KButton type="submit" :disabled="partnerForm.processing" class="px-4 py-2 rounded-lg text-sm font-bold text-white" style="background: var(--info)">
              {{ partnerForm.processing ? '...' : 'Simpan Partner' }}
            </KButton>
          </form>
          <!-- Partner list -->
          <div v-if="partners?.length" class="flex flex-wrap gap-2 pt-2 border-t" :style="{ borderColor: 'var(--border-light)' }">
            <span v-for="p in partners" :key="p.id" class="px-3 py-1 rounded-full text-xs font-medium" :style="{ background: 'var(--bg-hover)', color: 'var(--text-secondary)' }">
              {{ p.name }} <span v-if="p.specialty" :style="{ color: 'var(--text-muted)' }">· {{ p.specialty }}</span>
            </span>
          </div>
        </div>

        <!-- Repair List -->
        <div class="rounded-xl border overflow-hidden" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
          <div class="p-4 border-b" :style="{ borderColor: 'var(--border-color)' }">
            <h3 class="font-semibold text-sm" :style="{ color: 'var(--text-primary)' }">📋 Service di Partner</h3>
          </div>
          <div v-if="repairs?.data?.length" class="divide-y" :style="{ borderColor: 'var(--border-light)' }">
            <div v-for="r in repairs.data" :key="r.id" class="p-4 space-y-2">
              <div class="flex items-start justify-between gap-4">
                <div class="min-w-0 flex-1">
                  <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-sm font-bold" :style="{ color: 'var(--text-primary)' }">Service #{{ r.service_id }}</span>
                    <span class="px-2 py-0.5 text-[10px] rounded-full font-semibold" :style="statusStyle(r.status)">{{ statusLabel(r.status) }}</span>
                    <span v-if="r.is_overdue" class="px-2 py-0.5 text-[10px] rounded-full font-semibold" :style="{ background: 'var(--danger-soft)', color: 'var(--danger-text)' }">⚠️ Terlambat</span>
                  </div>
                  <p class="text-sm mt-1" :style="{ color: 'var(--text-secondary)' }">{{ r.service?.customer?.name || 'Customer' }} → {{ r.partner?.name }}</p>
                  <div class="flex items-center gap-3 mt-1 text-xs" :style="{ color: 'var(--text-muted)' }">
                    <span>Biaya Partner: Rp {{ formatNumber(r.partner_cost) }}</span>
                    <span>Charge Customer: Rp {{ formatNumber(r.customer_charge) }}</span>
                    <span class="font-semibold" :class="r.store_margin >= 0 ? 'text-emerald-600' : 'text-red-600'">Margin: {{ r.store_margin >= 0 ? '+' : '' }}Rp {{ formatNumber(r.store_margin) }}</span>
                  </div>
                  <div v-if="r.estimated_return" class="text-xs mt-1" :style="{ color: 'var(--text-muted)' }">
                    Estimasi kembali: {{ formatDate(r.estimated_return) }}
                    <span v-if="r.returned_at"> · Kembali: {{ formatDate(r.returned_at) }}</span>
                  </div>
                </div>
                <div class="shrink-0 space-y-1 text-right">
                  <p class="text-xs" :style="{ color: 'var(--text-muted)' }">{{ formatDate(r.created_at) }}</p>
                  <button v-if="r.status === 'sent'" @click="updateStatus(r, 'in_progress')" class="text-xs px-3 py-1 rounded font-bold" :style="{ background: 'var(--info-soft)', color: 'var(--info-text)' }">Dikerjakan</button>
                  <button v-if="r.status === 'in_progress'" @click="updateStatus(r, 'done')" class="text-xs px-3 py-1 rounded font-bold" :style="{ background: 'var(--success-soft)', color: 'var(--success-text)' }">Selesai</button>
                  <button v-if="r.status === 'done'" @click="updateStatus(r, 'returned')" class="text-xs px-3 py-1 rounded font-bold" :style="{ background: 'var(--warning-soft)', color: 'var(--warning-text)' }">Unit Kembali</button>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="p-8 text-center"><p class="text-sm" :style="{ color: 'var(--text-muted)' }">Belum ada service di partner eksternal.</p></div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import KButton from '@/Components/KButton.vue';
import KInput from '@/Components/KInput.vue';
import { useFormatter } from '@/Composables/useFormatter.js';

const props = defineProps({
  partners: Array, repairs: Object, activeCount: Number, overdueCount: Number,
});

const { formatNumber, formatDate } = useFormatter();

const statusLabel = (s) => ({ sent: 'Dikirim', in_progress: 'Dikerjakan', done: 'Selesai (Partner)', returned: 'Unit Kembali', completed: 'Complete' }[s] || s);
const statusStyle = (s) => {
  const m = { sent: 'var(--warning-soft)', in_progress: 'var(--info-soft)', done: 'var(--success-soft)', returned: 'var(--bg-hover)', completed: 'var(--bg-hover)' };
  const c = { sent: 'var(--warning-text)', in_progress: 'var(--info-text)', done: 'var(--success-text)', returned: 'var(--text-muted)', completed: 'var(--text-muted)' };
  return { background: m[s] || '', color: c[s] || '' };
};

const partnerForm = useForm({ name: '', phone: '', specialty: '', address: '', notes: '' });
const submitPartner = () => partnerForm.post(route('external-partners.store'), { preserveScroll: true, onSuccess: () => partnerForm.reset() });

const updateStatus = (repair, status) => router.put(route('external-repairs.update', repair.id), { status }, { preserveScroll: true });
</script>
