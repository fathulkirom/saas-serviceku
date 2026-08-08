<template>
  <AuthenticatedLayout>
    <div class="flex flex-col min-h-screen" :style="{ background: 'var(--bg-app)' }">
      <div class="px-4 sm:px-6 lg:px-8 max-w-3xl mx-auto w-full py-6 space-y-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-xl font-bold" :style="{ color: 'var(--text-primary)' }">🔑 API Tokens</h1>
            <p class="text-sm mt-0.5" :style="{ color: 'var(--text-muted)' }">Generate token untuk akses API ServiceKU tenant Anda.</p>
          </div>
          <Link :href="route('pengaturan.index')" class="text-sm font-semibold" :style="{ color: 'var(--text-muted)' }">← Pengaturan</Link>
        </div>

        <!-- Generate -->
        <form @submit.prevent="submitToken" class="rounded-xl border p-5 space-y-3" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
          <h3 class="font-semibold text-sm" :style="{ color: 'var(--text-primary)' }">Buat Token Baru</h3>
          <div class="flex gap-3">
            <KInput v-model="tokenForm.name" placeholder="Nama token (contoh: Integrasi Webhook)" required class="flex-1 rounded-lg text-sm" />
            <KButton type="submit" :disabled="tokenForm.processing" class="px-5 py-2 rounded-lg text-sm font-bold text-white" style="background: var(--info)">
              {{ tokenForm.processing ? '...' : 'Generate' }}
            </KButton>
          </div>
        </form>

        <!-- Token List -->
        <div class="rounded-xl border overflow-hidden" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
          <div class="p-4 border-b" :style="{ borderColor: 'var(--border-color)' }">
            <h3 class="font-semibold text-sm" :style="{ color: 'var(--text-primary)' }">Token Aktif</h3>
          </div>
          <div v-if="tokens?.length" class="divide-y" :style="{ borderColor: 'var(--border-light)' }">
            <div v-for="t in tokens" :key="t.id" class="px-4 py-3 flex items-center justify-between">
              <div>
                <p class="text-sm font-semibold" :style="{ color: 'var(--text-primary)' }">{{ t.name }}</p>
                <p class="text-xs" :style="{ color: 'var(--text-muted)' }">
                  Scope: {{ (t.scopes || []).join(', ') }} · Dibuat {{ formatDate(t.created_at) }}
                  <span v-if="t.last_used_at"> · Terakhir: {{ formatDate(t.last_used_at) }}</span>
                </p>
              </div>
              <button @click="revokeToken(t)" class="text-xs px-3 py-1 rounded font-bold text-white" style="background: var(--danger)">Revoke</button>
            </div>
          </div>
          <div v-else class="p-8 text-center"><p class="text-sm" :style="{ color: 'var(--text-muted)' }">Belum ada token API.</p></div>
        </div>

        <!-- API Usage Info -->
        <div class="rounded-xl border p-5 text-xs space-y-1" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)', color: 'var(--text-muted)' }">
          <p class="font-semibold">📡 Endpoint API:</p>
          <code>GET /api/v1/services</code> — Daftar servis<br>
          <code>GET /api/v1/services/{id}</code> — Detail servis<br>
          <code>GET /api/v1/sales</code> — Daftar penjualan<br>
          <code>GET /api/v1/products</code> — Daftar produk<br>
          <code>GET /api/v1/customers</code> — Daftar pelanggan<br>
          <p class="mt-2">Header: <code>Authorization: Bearer {token}</code></p>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { useForm, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import KButton from '@/Components/KButton.vue';
import KInput from '@/Components/KInput.vue';
import { useFormatter } from '@/Composables/useFormatter.js';

const props = defineProps({ tokens: Array });
const { formatDate } = useFormatter();

const tokenForm = useForm({ name: '', scopes: ['services.read', 'sales.read'] });
const submitToken = () => tokenForm.post(route('api-tokens.store'), { preserveScroll: true, onSuccess: () => tokenForm.reset() });
const revokeToken = (t) => { if (confirm('Nonaktifkan token ini?')) router.delete(route('api-tokens.destroy', t.id), { preserveScroll: true }); };
</script>
