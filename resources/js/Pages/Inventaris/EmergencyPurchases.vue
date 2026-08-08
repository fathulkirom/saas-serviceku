<template>
  <AuthenticatedLayout>
    <div class="flex flex-col min-h-screen" :style="{ background: 'var(--bg-app)' }">
      <div class="px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto w-full py-6 space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-xl font-bold" :style="{ color: 'var(--text-primary)' }">🛒 Pembelian Darurat</h1>
            <p class="text-sm mt-0.5" :style="{ color: 'var(--text-muted)' }">Beli sparepart lokal langsung dari kas toko saat stok kosong.</p>
          </div>
          <Link :href="route('inventaris.index')" class="text-sm font-semibold" :style="{ color: 'var(--text-muted)' }">← Kembali</Link>
        </div>

        <!-- Today's Total -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="p-4 rounded-xl border" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
            <p class="text-xs" :style="{ color: 'var(--text-muted)' }">Total Hari Ini</p>
            <p class="text-xl font-bold" :style="{ color: 'var(--danger-text)' }">Rp {{ formatNumber(todayTotal) }}</p>
          </div>
        </div>

        <!-- Purchase Form -->
        <form @submit.prevent="submitPurchase" class="rounded-xl border p-5 space-y-4" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
          <h3 class="font-semibold text-sm" :style="{ color: 'var(--text-primary)' }">📝 Catat Pembelian Baru</h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="sm:col-span-2">
              <label class="text-xs font-medium" :style="{ color: 'var(--text-secondary)' }">Nama Sparepart *</label>
              <KInput v-model="form.product_name" placeholder="Contoh: LCD Samsung A54" required class="w-full rounded-lg text-sm mt-1" />
            </div>
            <div>
              <label class="text-xs font-medium" :style="{ color: 'var(--text-secondary)' }">Jumlah *</label>
              <KInput v-model="form.quantity" type="number" min="1" required class="w-full rounded-lg text-sm mt-1" />
            </div>
            <div>
              <label class="text-xs font-medium" :style="{ color: 'var(--text-secondary)' }">Harga Satuan (Rp) *</label>
              <KInput v-model="form.cost_price" type="number" min="0" step="100" required class="w-full rounded-lg text-sm mt-1" />
            </div>
            <div>
              <label class="text-xs font-medium" :style="{ color: 'var(--text-secondary)' }">Supplier / Toko</label>
              <KInput v-model="form.supplier_name" placeholder="Opsional" class="w-full rounded-lg text-sm mt-1" />
            </div>
            <div>
              <label class="text-xs font-medium" :style="{ color: 'var(--text-secondary)' }">Link ke Produk</label>
              <select v-model="form.product_id" class="w-full rounded-lg text-sm mt-1 border" :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-input)' }">
                <option value="">-- Tidak ada --</option>
                <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }} (Stok: {{ p.stock_quantity }})</option>
              </select>
            </div>
          </div>
          <div>
            <label class="text-xs font-medium" :style="{ color: 'var(--text-secondary)' }">Alasan</label>
            <KInput v-model="form.reason" placeholder="Kenapa beli darurat?" class="w-full rounded-lg text-sm mt-1" />
          </div>
          <div class="flex items-center gap-2">
            <input type="checkbox" v-model="form.paid_from_cash" class="rounded" />
            <label class="text-xs" :style="{ color: 'var(--text-secondary)' }">Bayar dari kas toko (tercatat pengeluaran)</label>
          </div>
          <div class="flex justify-between items-center pt-2">
            <p class="text-sm font-semibold" :style="{ color: 'var(--text-primary)' }">
              Total: <span :style="{ color: 'var(--danger-text)' }">Rp {{ formatNumber(form.quantity * form.cost_price) }}</span>
            </p>
            <KButton type="submit" :disabled="form.processing" class="px-5 py-2 rounded-lg text-sm font-bold text-white" style="background: var(--danger)">
              {{ form.processing ? 'Menyimpan...' : 'Catat Pembelian' }}
            </KButton>
          </div>
        </form>

        <!-- Purchase History -->
        <div class="rounded-xl border overflow-hidden" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
          <div class="p-4 border-b" :style="{ borderColor: 'var(--border-color)' }">
            <h3 class="font-semibold text-sm" :style="{ color: 'var(--text-primary)' }">📋 Riwayat Pembelian Darurat</h3>
          </div>
          <div v-if="purchases?.data?.length" class="divide-y" :style="{ borderColor: 'var(--border-light)' }">
            <div v-for="p in purchases.data" :key="p.id" class="px-4 py-3 flex items-center justify-between gap-4">
              <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold truncate" :style="{ color: 'var(--text-primary)' }">{{ p.product_name }} <span class="text-xs font-normal" :style="{ color: 'var(--text-muted)' }">x{{ p.quantity }}</span></p>
                <p class="text-xs" :style="{ color: 'var(--text-muted)' }">
                  {{ p.supplier_name || 'Tanpa supplier' }} · {{ p.user?.name }} · {{ formatDate(p.created_at) }}
                </p>
              </div>
              <div class="text-right shrink-0">
                <p class="text-sm font-bold" :style="{ color: 'var(--danger-text)' }">Rp {{ formatNumber(p.total) }}</p>
                <span v-if="p.paid_from_cash" class="text-[10px] px-1.5 py-0.5 rounded" :style="{ background: 'var(--warning-soft)', color: 'var(--warning-text)' }">kas</span>
              </div>
            </div>
          </div>
          <div v-else class="p-8 text-center">
            <p class="text-sm" :style="{ color: 'var(--text-muted)' }">Belum ada pembelian darurat.</p>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import KButton from '@/Components/KButton.vue';
import KInput from '@/Components/KInput.vue';
import { useFormatter } from '@/Composables/useFormatter.js';

const props = defineProps({
  purchases: { type: Object, default: () => ({ data: [] }) },
  todayTotal: { type: Number, default: 0 },
  products: { type: Array, default: () => [] },
});

const { formatNumber, formatDate } = useFormatter();

const form = useForm({
  product_name: '',
  quantity: 1,
  cost_price: 0,
  supplier_name: '',
  reason: '',
  paid_from_cash: true,
  product_id: '',
  notes: '',
});

const submitPurchase = () => form.post(route('inventaris.emergency-purchases.store'), {
  preserveScroll: true,
  onSuccess: () => form.reset(),
});
</script>
