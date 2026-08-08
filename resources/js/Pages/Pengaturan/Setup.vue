<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import KCard from '@/Components/KCard.vue'
import KButton from '@/Components/KButton.vue'
import KBadge from '@/Components/KBadge.vue'

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
  checklist: Array,
  health: Array,
  dataIssues: Array,
  progress: Number,
  isReady: Boolean,
})

// ── Progress ──
const progressColor = computed(() => props.progress >= 100 ? 'bg-green-500' : props.progress >= 70 ? 'bg-indigo-500' : props.progress >= 40 ? 'bg-amber-500' : 'bg-red-500')
const remainingCount = computed(() => props.checklist.filter(c => c.status !== 'done').length)

// ── Grouping ──
const checklistGroups = computed(() => {
  const groups = []
  const seen = new Set()
  for (const item of props.checklist) {
    const g = item.group || 'Lainnya'
    if (!seen.has(g)) {
      seen.add(g)
      groups.push({ name: g, items: props.checklist.filter(i => (i.group || 'Lainnya') === g) })
    }
  }
  return groups
})

// ── Status helpers (three-level severity) ──
const statusIcon = (s) => ({ done: '✅', info: 'ℹ️', warning: '⚠️', blocking: '🔴' }[s] || '⬜')
const statusColor = (s) => ({
  done: 'sk-border-primary sk-bg-success-soft',
  info: 'border-blue-200 sk-bg-info-soft',
  warning: 'sk-border-primary sk-bg-warning-soft',
  blocking: 'sk-border-primary sk-bg-danger-soft',
}[s] || '')
const severityColor = (s) => ({ blocking: 'sk-text-danger', warning: 'sk-text-warning', info: 'sk-text-info' }[s] || '')
const severityLabel = (s) => ({ blocking: 'Bloker', warning: 'Peringatan', info: 'Info', done: 'Selesai' }[s] || '')

// ── Quick stats ──
const doneCount = computed(() => props.checklist.filter(c => c.status === 'done').length)
const blockingCount = computed(() => props.checklist.filter(c => c.status === 'blocking').length)
const warningCount = computed(() => props.checklist.filter(c => c.status === 'warning').length)
</script>

<template>
  <div class="max-w-4xl mx-auto space-y-6 py-6 px-4">
    <!-- Navigation -->
    <div class="flex items-center justify-between">
      <a :href="route('dashboard')" class="text-sm sk-text-muted hover:sk-text-primary flex items-center gap-1 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke Dashboard
      </a>
      <span class="text-xs sk-text-muted">Setup Assistant</span>
    </div>

    <!-- Celebration -->
    <div v-if="isReady" class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-2xl p-8 text-white text-center shadow-lg">
      <div class="text-5xl mb-4">🎉</div>
      <h1 class="text-3xl font-bold mb-2">Selamat!</h1>
      <p class="text-lg opacity-90">Toko Anda siap menggunakan ServiceKU.</p>
    </div>

    <!-- Progress: Configuration Completion -->
    <KCard class="!p-6">
      <h2 class="text-lg font-bold sk-text-primary dark:text-white mb-1">Konfigurasi Toko</h2>
      <p class="text-sm sk-text-muted mb-4">{{ isReady ? 'Semua siap!' : `${doneCount} dari ${checklist.length} item selesai` }}</p>
      <div class="w-full sk-bg-hover dark:bg-gray-700 rounded-full h-3 overflow-hidden">
        <div class="h-full rounded-full transition-all duration-700" :class="progressColor" :style="{ width: progress + '%' }"></div>
      </div>
      <div class="text-center mt-2 text-sm font-semibold" :class="progress >= 100 ? 'sk-text-success' : 'sk-text-muted'">{{ progress }}%</div>
    </KCard>

    <!-- Checklist: grouped by section -->
    <KCard class="!p-6">
      <h2 class="text-lg font-bold sk-text-primary dark:text-white mb-4">Setup Checklist</h2>
      <div class="space-y-6">
        <div v-for="group in checklistGroups" :key="group.name">
          <h3 class="text-xs font-bold sk-text-muted uppercase tracking-wider mb-2 px-1">{{ group.name }}</h3>
          <div class="space-y-1">
            <div v-for="item in group.items" :key="item.key"
              class="flex items-center justify-between p-3 rounded-lg border transition-colors hover:sk-bg-hover dark:hover:sk-bg-inverse/50 cursor-pointer"
              :class="statusColor(item.status)"
              @click="item.url && item.url !== '#' ? router.visit(item.url) : null">
              <div class="flex items-center gap-3">
                <span class="text-lg">{{ statusIcon(item.status) }}</span>
                <div>
                  <div class="text-sm font-medium sk-text-primary dark:text-white">{{ item.label }}</div>
                  <div v-if="item.count !== undefined" class="text-xs sk-text-muted">{{ item.count }} data</div>
                </div>
              </div>
              <div class="flex items-center gap-2">
                <KBadge v-if="item.status === 'done'" size="xs" class="sk-bg-success-soft sk-text-success border sk-border-primary">✓ {{ severityLabel(item.status) }}</KBadge>
                <KBadge v-else-if="item.status === 'blocking'" size="xs" class="sk-bg-danger-soft sk-text-danger border sk-border-primary">🔴 {{ severityLabel(item.status) }}</KBadge>
                <KBadge v-else-if="item.status === 'warning'" size="xs" class="sk-bg-warning-soft sk-text-warning border sk-border-primary">⚠️ {{ severityLabel(item.status) }}</KBadge>
                <KBadge v-else-if="item.status === 'info'" size="xs" class="sk-bg-info-soft sk-text-info border border-blue-200">ℹ️ {{ severityLabel(item.status) }}</KBadge>
                <KButton v-if="item.status !== 'done' && item.url && item.url !== '#'" size="xs" variant="outline" @click.stop="router.visit(item.url)">Perbaiki →</KButton>
              </div>
            </div>
          </div>
        </div>
      </div>
    </KCard>

    <!-- Health Check: Operational Health (separate from config) -->
    <KCard v-if="health.length > 0" class="!p-6">
      <h2 class="text-lg font-bold sk-text-primary dark:text-white mb-1">Kesehatan Operasional</h2>
      <p class="text-sm sk-text-muted mb-4">Dipantau terpisah dari konfigurasi. Tetap muncul meskipun konfigurasi 100%.</p>
      <div class="space-y-2">
        <div v-for="(item, i) in health" :key="i" class="flex items-center gap-2 text-sm p-2 rounded-lg"
          :class="item.status === 'blocking' ? 'sk-bg-danger-soft border sk-border-primary' : item.status === 'warning' ? 'sk-bg-warning-soft border sk-border-primary' : 'sk-bg-info-soft border border-blue-200'">
          <span>{{ statusIcon(item.status) }}</span>
          <span :class="item.status === 'blocking' ? 'sk-text-danger' : item.status === 'warning' ? 'sk-text-warning' : 'sk-text-info'">{{ item.message }}</span>
          <KBadge size="xs" :class="item.status === 'blocking' ? 'sk-bg-danger-soft sk-text-danger' : item.status === 'warning' ? 'sk-bg-warning-soft sk-text-warning' : 'sk-bg-info-soft sk-text-info'">{{ severityLabel(item.status) }}</KBadge>
        </div>
      </div>
    </KCard>

    <!-- Data Consistency -->
    <KCard v-if="dataIssues.some(d => d.count > 0)" class="!p-6">
      <h2 class="text-lg font-bold sk-text-primary dark:text-white mb-4">Data Consistency</h2>
      <div class="space-y-2">
        <div v-for="(item, i) in dataIssues.filter(d => d.count > 0)" :key="i" class="flex justify-between text-sm p-2 rounded-lg sk-bg-hover dark:sk-bg-inverse/50">
          <span :class="severityColor(item.severity)">{{ item.label }}</span>
          <span class="font-semibold">{{ item.count }}</span>
        </div>
      </div>
    </KCard>

    <!-- Status footer -->
    <div v-if="!isReady && progress >= 80" class="text-center text-sm sk-text-warning sk-bg-warning-soft rounded-xl p-4 border sk-border-primary">
      ⚠️ Hampir selesai! {{ remainingCount }} item tersisa — {{ blockingCount }} di antaranya bloker.
    </div>
    <div v-if="isReady && health.length > 0" class="text-center text-sm sk-text-warning sk-bg-warning-soft rounded-xl p-4 border sk-border-primary">
      ⚠️ Konfigurasi 100% — tetapi masih ada {{ health.length }} peringatan kesehatan operasional. Lihat bagian "Kesehatan Operasional" di atas.
    </div>
    <div v-if="isReady && health.length === 0" class="text-center text-sm sk-text-success sk-bg-success-soft rounded-xl p-4 border sk-border-primary">
      ✅ Semua konfigurasi selesai dan operasional sehat!
    </div>
  </div>
</template>
