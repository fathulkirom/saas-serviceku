<script setup>
import { router } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  setupSummary: { type: Object, default: null },
})

// ── Welcome state (first login, not yet dismissed) ──
const isWelcome = computed(() =>
  props.setupSummary && !props.setupSummary.firstLoginDismissed
)

// ── Config progress bar ──
const configColor = computed(() =>
  props.setupSummary.configProgress >= 100 ? 'bg-green-500' :
  props.setupSummary.configProgress >= 70 ? 'bg-indigo-500' :
  props.setupSummary.configProgress >= 40 ? 'bg-amber-500' : 'bg-red-500'
)

// ── Overall status ──
const statusBadge = computed(() => {
  const s = props.setupSummary.overallStatus
  return {
    READY:               { label: 'READY',                color: 'bg-green-100 text-green-800 border-green-200' },
    READY_WITH_WARNING:  { label: 'READY WITH WARNING',   color: 'bg-amber-100 text-amber-800 border-amber-200' },
    NOT_READY:           { label: 'NOT READY',             color: 'bg-red-100 text-red-800 border-red-200' },
  }[s] || { label: s, color: 'bg-gray-100 text-gray-800 border-gray-200' }
})

// ── Severity badge colors ──
const severityBadge = (s) => ({
  blocking: 'bg-red-100 text-red-700 border-red-200',
  warning:  'bg-amber-100 text-amber-700 border-amber-200',
  info:     'bg-blue-100 text-blue-700 border-blue-200',
  done:     'bg-green-100 text-green-700 border-green-200',
}[s] || 'bg-gray-100 text-gray-700 border-gray-200')

const severityIcon = (s) => ({
  blocking: '🔴',
  warning:  '⚠️',
  info:     'ℹ️',
  done:     '✅',
}[s] || '⬜')

// ── Health level ──
const healthLevel = computed(() => {
  const s = props.setupSummary.healthStatus
  return {
    ready:    { icon: '✅', label: 'Sehat',              color: 'text-green-600', bg: 'bg-green-50 border-green-200' },
    warning:  { icon: '⚠️', label: 'Perlu Perhatian',    color: 'text-amber-600', bg: 'bg-amber-50 border-amber-200' },
    blocking: { icon: '🔴', label: 'Ada Bloker',         color: 'text-red-600',   bg: 'bg-red-50 border-red-200' },
  }[s] || { icon: '⬜', label: s, color: 'text-gray-500', bg: 'bg-gray-50 border-gray-200' }
})

function lanjutkan() {
  router.visit(route('setup'))
}

function nanti() {
  router.post(route('setup.dismiss-first-login'), {}, {
    preserveScroll: true,
    onSuccess: () => window.location.reload(),
  })
}

function sembunyikan() {
  router.post(route('setup.dismiss'), {}, {
    preserveScroll: true,
    onSuccess: () => window.location.reload(),
  })
}
</script>

<template>
  <div
    v-if="setupSummary && (!setupSummary.configComplete || setupSummary.healthStatus !== 'ready')"
    class="bg-white rounded-2xl border shadow-[0_2px_10px_-3px_rgba(0,0,0,0.08)] overflow-hidden"
    :class="isWelcome ? 'border-indigo-300 ring-2 ring-indigo-100' : 'border-amber-200'"
  >
    <!-- Header: Welcome or Standard -->
    <div
      :class="isWelcome ? 'bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-indigo-100' : 'bg-gradient-to-r from-amber-50 to-orange-50 border-b border-amber-100'"
      class="px-6 py-4"
    >
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <span class="text-xl">{{ isWelcome ? '👋' : '⚙️' }}</span>
          <div>
            <h3 class="font-bold text-zinc-900">
              {{ isWelcome ? 'Selamat Datang di ServiceKU!' : 'Setup Toko' }}
            </h3>
            <p class="text-sm text-zinc-500">
              {{ isWelcome
                ? 'Lengkapi pengaturan toko Anda untuk memulai.'
                : `${setupSummary.configProgress}% Konfigurasi — ${setupSummary.blockingCount} bloker, ${setupSummary.warningCount} peringatan` }}
            </p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <span class="px-2.5 py-1 text-xs font-bold rounded-full border" :class="statusBadge.color">{{ statusBadge.label }}</span>
          <button
            v-if="isWelcome"
            @click="lanjutkan"
            class="px-4 py-2 text-sm font-semibold bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors shadow-sm"
          >
            Lanjutkan Setup
          </button>
          <button
            v-if="isWelcome"
            @click="nanti"
            class="px-3 py-2 text-sm text-zinc-500 hover:text-zinc-700 hover:bg-zinc-100 rounded-lg transition-colors"
          >
            Nanti
          </button>
          <button
            v-if="!isWelcome"
            @click="lanjutkan"
            class="px-4 py-2 text-sm font-semibold bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors shadow-sm"
          >
            Lanjutkan Setup
          </button>
          <button
            v-if="!isWelcome"
            @click="sembunyikan"
            class="px-3 py-2 text-sm text-zinc-500 hover:text-zinc-700 hover:bg-zinc-100 rounded-lg transition-colors"
            title="Sembunyikan"
          >
            ✕
          </button>
        </div>
      </div>
    </div>

    <!-- Body -->
    <div class="px-6 py-4 space-y-4">
      <!-- Config progress -->
      <div>
        <div class="flex items-center justify-between mb-1.5">
          <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wide">Konfigurasi</span>
          <span class="text-xs text-zinc-400">{{ setupSummary.configDone }}/{{ setupSummary.configTotal }} item</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
          <div class="h-full rounded-full transition-all duration-700" :class="configColor" :style="{ width: setupSummary.configProgress + '%' }"></div>
        </div>
      </div>

      <!-- Health status -->
      <div class="flex items-center gap-2 text-sm p-3 rounded-lg border" :class="healthLevel.bg">
        <span>{{ healthLevel.icon }}</span>
        <span :class="healthLevel.color" class="font-semibold">{{ healthLevel.label }}</span>
        <span class="text-zinc-500">
          —
          <template v-if="setupSummary.healthBlockingCount > 0">{{ setupSummary.healthBlockingCount }} bloker, </template>
          {{ setupSummary.healthWarningCount }} peringatan
        </span>
      </div>

      <!-- Blocking items (show all, these are critical) -->
      <div v-if="setupSummary.blockingCount > 0" class="space-y-1.5">
        <p class="text-xs font-bold text-red-600 uppercase tracking-wide flex items-center gap-1">
          <span>{{ severityIcon('blocking') }}</span> Bloker — Harus Segera Dilengkapi
        </p>
        <div v-for="item in setupSummary.blockingItems" :key="item.key"
          class="flex items-center gap-2 text-sm p-2 rounded-lg bg-red-50 border border-red-200">
          <span>{{ severityIcon('blocking') }}</span>
          <span class="text-red-700">{{ item.label }}</span>
        </div>
      </div>

      <!-- Warning items (collapsed, not spammy) -->
      <details v-if="setupSummary.warningCount > 0" class="text-sm">
        <summary class="cursor-pointer text-amber-700 font-medium flex items-center gap-1">
          <span>{{ severityIcon('warning') }}</span> {{ setupSummary.warningCount }} Peringatan
        </summary>
        <div class="mt-2 space-y-1 ml-6">
          <div v-for="item in setupSummary.warningItems" :key="item.key"
            class="flex items-center gap-2 text-sm p-1.5">
            <span class="text-xs">{{ severityIcon('warning') }}</span>
            <span class="text-amber-700">{{ item.label }}</span>
          </div>
        </div>
      </details>

      <!-- Info items (subtle) -->
      <div v-if="setupSummary.infoCount > 0 && setupSummary.blockingCount === 0" class="text-xs text-zinc-400 flex items-center gap-1">
        <span>{{ severityIcon('info') }}</span> {{ setupSummary.infoCount }} item informasional
      </div>

      <!-- Empty state: all good but health warnings exist -->
      <p v-if="setupSummary.configComplete && setupSummary.blockingCount === 0 && setupSummary.healthWarningCount > 0"
        class="text-sm text-amber-600 bg-amber-50 rounded-lg p-3 border border-amber-200">
        ⚠️ Konfigurasi 100% — tetapi masih ada peringatan kesehatan operasional.
      </p>
    </div>
  </div>
</template>
