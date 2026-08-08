<template>
  <AuthenticatedLayout>
    <div class="flex flex-col min-h-screen" :style="{ background: 'var(--bg-app)' }">
      <div class="px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto w-full py-6 space-y-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-xl font-bold" :style="{ color: 'var(--text-primary)' }">🏭 Supplier</h1>
            <p class="text-sm mt-0.5" :style="{ color: 'var(--text-muted)' }">Kelola data supplier/vendor untuk pembelian & purchase order.</p>
          </div>
          <Link :href="route('inventaris.index')" class="text-sm font-semibold" :style="{ color: 'var(--text-muted)' }">← Inventaris</Link>
        </div>

        <!-- Add Supplier -->
        <form @submit.prevent="submitSupplier" class="rounded-xl border p-5 space-y-3" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
          <h3 class="font-semibold text-sm" :style="{ color: 'var(--text-primary)' }">➕ Tambah Supplier</h3>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <KInput v-model="form.name" placeholder="Nama supplier *" required class="rounded-lg text-sm" />
            <KInput v-model="form.contact_person" placeholder="Kontak person" class="rounded-lg text-sm" />
            <KInput v-model="form.phone" placeholder="Telepon" class="rounded-lg text-sm" />
            <KInput v-model="form.email" type="email" placeholder="Email" class="rounded-lg text-sm" />
            <select v-model="form.category" class="rounded-lg text-sm border p-2" :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-input)' }">
              <option value="">Kategori...</option>
              <option value="sparepart">Sparepart</option>
              <option value="tools">Tools / Peralatan</option>
              <option value="aksesoris">Aksesoris</option>
              <option value="umum">Umum</option>
            </select>
            <KButton type="submit" :disabled="form.processing" class="px-4 py-2 rounded-lg text-sm font-bold text-white" style="background: var(--info)">
              {{ form.processing ? '...' : 'Tambah' }}
            </KButton>
          </div>
        </form>

        <!-- Supplier List -->
        <div class="rounded-xl border overflow-hidden" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
          <div class="p-4 border-b" :style="{ borderColor: 'var(--border-color)' }">
            <h3 class="font-semibold text-sm" :style="{ color: 'var(--text-primary)' }">📋 Daftar Supplier</h3>
          </div>
          <div v-if="suppliers?.data?.length" class="divide-y" :style="{ borderColor: 'var(--border-light)' }">
            <div v-for="s in suppliers.data" :key="s.id" class="px-4 py-3 flex items-center justify-between gap-4">
              <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold" :style="{ color: 'var(--text-primary)' }">{{ s.name }}</p>
                <p class="text-xs" :style="{ color: 'var(--text-muted)' }">
                  {{ s.category ? categoryLabel(s.category) + ' · ' : '' }}{{ s.contact_person || s.phone || 'Tanpa kontak' }}
                  <span v-if="s.purchase_count"> · {{ s.purchase_count }}x pembelian · Rp {{ formatNumber(s.total_purchased) }}</span>
                </p>
              </div>
              <span class="text-xs px-2 py-0.5 rounded" :class="s.is_active ? 'sk-bg-success-soft sk-text-success' : 'sk-bg-hover sk-text-muted'">
                {{ s.is_active ? 'Aktif' : 'Nonaktif' }}
              </span>
            </div>
          </div>
          <div v-else class="p-8 text-center"><p class="text-sm" :style="{ color: 'var(--text-muted)' }">Belum ada supplier.</p></div>
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

const props = defineProps({ suppliers: Object });
const { formatNumber } = useFormatter();
const categoryLabel = (c) => ({ sparepart:'Sparepart', tools:'Tools', aksesoris:'Aksesoris', umum:'Umum' }[c]||c);

const form = useForm({ name:'', contact_person:'', phone:'', email:'', category:'', notes:'' });
const submitSupplier = () => form.post(route('suppliers.store'), { preserveScroll:true, onSuccess:()=>form.reset() });
</script>
