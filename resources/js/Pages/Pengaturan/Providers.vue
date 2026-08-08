<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import KCard from '@/Components/KCard.vue'
import KButton from '@/Components/KButton.vue'
import KBadge from '@/Components/KBadge.vue'
import KTable from '@/Components/KTable.vue'
import EmptyState from '@/Components/EmptyState.vue'

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
  providers: { type: Object, default: () => ({}) },
  health: { type: Object, default: () => ({}) },
})

const selectedCategory = ref(null)
const testingProvider = ref(null)
const testResult = ref(null)

const categories = computed(() => {
  return Object.entries(props.providers).map(([key, cat]) => ({ key, ...cat }))
})

const selectedProviders = computed(() => {
  if (!selectedCategory.value) return []
  const cat = props.providers[selectedCategory.value]
  return cat?.providers || []
})

function testConnection(catKey, provKey) {
  testingProvider.value = provKey
  router.post(route('tenant.providers.test', { category: catKey, provider: provKey }), {}, {
    preserveScroll: true,
    onSuccess: (page) => { testResult.value = page.props.flash?.success || 'Connection OK'; testingProvider.value = null },
    onError: (e) => { testResult.value = 'Test failed: ' + (e.message || 'Unknown error'); testingProvider.value = null },
  })
}

function toggleProvider(catKey, provKey) {
  router.post(route('tenant.providers.toggle', { category: catKey, provider: provKey }), {}, { preserveScroll: true })
}

const statusColors = { connected: 'sk-bg-success-soft sk-text-success dark:bg-green-900 dark:text-green-200', disconnected: 'sk-bg-hover sk-text-secondary dark:sk-bg-inverse dark:sk-text-muted', error: 'sk-bg-danger-soft sk-text-danger dark:bg-red-900 dark:text-red-200' }
const healthColors = { healthy: '🟢', degraded: '🟡', down: '🔴', unknown: '⚪' }
</script>

<template>
  <div class="space-y-6">
    <PageHeader title="Provider Center" description="Kelola semua koneksi provider — WhatsApp, Email, Storage, Payment, Printing, dan lainnya." />

    <!-- Category Pills -->
    <div class="flex flex-wrap gap-2">
      <button v-for="cat in categories" :key="cat.key" @click="selectedCategory = selectedCategory === cat.key ? null : cat.key"
        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
        :class="selectedCategory === cat.key ? 'bg-primary-600 text-white shadow' : 'sk-bg-card dark:sk-bg-inverse border sk-border dark:sk-border hover:border-primary-300 dark:hover:border-primary-600 sk-text-primary dark:sk-text-muted'">
        {{ cat.label }}
      </button>
    </div>

    <!-- Provider Grid -->
    <div v-if="selectedCategory" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <KCard v-for="prov in selectedProviders" :key="prov.key" class="relative">
        <div class="flex items-start justify-between mb-3">
          <div>
            <h3 class="font-semibold sk-text-primary dark:text-white">{{ prov.label }}</h3>
            <p class="text-xs sk-text-muted dark:sk-text-muted mt-0.5">{{ prov.description || 'Tidak ada deskripsi' }}</p>
          </div>
          <KBadge :class="statusColors[prov.connection_status] || statusColors.disconnected">
            {{ prov.connection_status }}
          </KBadge>
        </div>

        <!-- Health + Latency -->
        <div class="flex items-center gap-4 text-sm sk-text-muted dark:sk-text-muted mb-3">
          <span>{{ healthColors[prov.health] || '⚪' }} {{ prov.health || 'unknown' }}</span>
          <span v-if="prov.quota">Quota: {{ prov.quota }}</span>
          <span v-if="prov.usage">Usage: {{ prov.usage }}</span>
        </div>

        <!-- Last Check / Error -->
        <div v-if="prov.last_check" class="text-xs sk-text-muted mb-3">Last check: {{ prov.last_check }}</div>
        <div v-if="prov.last_error" class="text-xs sk-text-danger mb-3 truncate">{{ prov.last_error }}</div>

        <!-- Actions -->
        <div class="flex gap-2 pt-2 border-t sk-border-light dark:sk-border">
          <KButton size="sm" variant="outline" @click="testConnection(selectedCategory, prov.key)" :disabled="testingProvider === prov.key">
            {{ testingProvider === prov.key ? 'Testing...' : 'Test' }}
          </KButton>
          <KButton size="sm" variant="ghost" @click="toggleProvider(selectedCategory, prov.key)">
            {{ prov.connection_status === 'connected' ? 'Disconnect' : 'Connect' }}
          </KButton>
        </div>
      </KCard>
    </div>

    <!-- Empty State -->
    <EmptyState v-if="!selectedCategory"
      title="Provider Center"
      description="Pilih kategori provider di atas untuk melihat dan mengelola koneksi. Provider Engine mengelola WhatsApp, Email, Google Drive, Payment Gateway, Printer, dan lainnya.">
      <template #icon>
        <svg class="w-12 h-12 sk-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
      </template>
    </EmptyState>
  </div>
</template>
