<template>
  <div class="border-b px-4 sm:px-6 lg:px-8 py-3" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-light)' }">
    <div class="max-w-[1600px] mx-auto w-full">
      <div class="flex items-center gap-3 flex-wrap">

        <!-- Breadcrumb -->
        <SkBreadcrumb :items="breadcrumbItems" class="flex-shrink-0" />

        <div class="flex-1"></div>

        <!-- Workspace Title + Status -->
        <div class="flex items-center gap-2" v-if="workspace">
          <span class="text-lg">{{ workspace.icon }}</span>
          <span class="text-sm font-bold" :style="{ color: 'var(--text-primary)' }">{{ workspace.title }}</span>
        </div>

        <!-- Status Badge (if data has status) -->
        <span v-if="data?.status"
          class="text-[11px] font-bold px-2.5 py-0.5 rounded-full"
          :style="{ background: statusBg, color: statusColor }"
        >
          {{ data.status_label || data.status }}
        </span>

        <!-- Priority (if data has priority) -->
        <span v-if="data?.priority"
          class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full"
          :style="priorityStyle"
        >
          {{ data.priority }}
        </span>

        <div class="w-px h-5 mx-1" :style="{ background: 'var(--border-light)' }"></div>

        <!-- Quick Search -->
        <div class="relative hidden md:block">
          <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5" :style="{ color: 'var(--text-muted)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          <input
            type="text"
            placeholder="Quick search... (Ctrl+K)"
            class="w-48 pl-8 pr-3 py-1.5 text-xs rounded-lg border transition-all focus:w-64 focus:border-indigo-400 outline-none"
            :style="{ background: 'var(--bg-input)', color: 'var(--text-primary)', borderColor: 'var(--border-color)' }"
          />
        </div>

        <!-- Command Button -->
        <button
          class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors"
          :style="{ color: 'var(--text-muted)' }"
          title="Commands (Ctrl+K)"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
          </svg>
        </button>

        <!-- Favorite -->
        <button
          class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors"
          :class="isFavorite ? 'text-yellow-500' : ''"
          :style="{ color: isFavorite ? '#F59E0B' : 'var(--text-muted)' }"
          @click="isFavorite = !isFavorite"
          title="Favorite"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :fill="isFavorite ? 'currentColor' : 'none'" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
          </svg>
        </button>

        <!-- Inspector Toggle -->
        <button
          @click="$emit('toggle-inspector')"
          class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors"
          :style="{ color: 'var(--text-muted)' }"
          title="Inspector (Ctrl+I)"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </button>

        <!-- Refresh -->
        <button
          @click="$emit('refresh')"
          :disabled="isRefreshing"
          class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors"
          :class="{ 'animate-spin': isRefreshing }"
          :style="{ color: 'var(--text-muted)' }"
          title="Refresh"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
        </button>

        <!-- Fullscreen -->
        <button
          @click="$emit('toggle-fullscreen')"
          class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors"
          :style="{ color: 'var(--text-muted)' }"
          title="Fullscreen"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import SkBreadcrumb from '@/Enterprise/Components/Navigation/Breadcrumb.vue';

const props = defineProps({
  workspace: { type: Object, default: null },
  data: { type: Object, default: () => ({}) },
  isRefreshing: { type: Boolean, default: false },
  isFullscreen: { type: Boolean, default: false },
});

defineEmits(['refresh', 'toggle-fullscreen', 'toggle-inspector']);

const isFavorite = ref(false);

const breadcrumbItems = computed(() => [
  { label: 'Dashboard', url: '/dashboard' },
  { label: props.workspace?.title || 'Workspace' },
]);

const statusColors = {
  menunggu_alokasi: { bg: '#FEF3C7', text: '#92400E' },
  diterima: { bg: '#DBEAFE', text: '#1E40AF' },
  dikerjakan: { bg: '#FCE7F3', text: '#9D174D' },
  selesai: { bg: '#DCFCE7', text: '#166534' },
  siap_diambil: { bg: '#D1FAE5', text: '#065F46' },
  cancel: { bg: '#FEE2E2', text: '#991B1B' },
};

const statusStyle = computed(() => statusColors[props.data?.status] || { bg: 'var(--bg-hover)', color: 'var(--text-secondary)' });
const statusBg = computed(() => statusStyle.value.bg);
const statusColor = computed(() => statusStyle.value.text);

const priorityStyle = computed(() => {
  const p = props.data?.priority;
  if (p === 'high' || p === 'urgent') return { background: 'var(--danger-soft)', color: 'var(--danger-text)' };
  return { background: 'var(--bg-hover)', color: 'var(--text-secondary)' };
});
</script>
