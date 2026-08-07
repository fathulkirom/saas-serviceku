<template>
  <!-- UniversalWorkspaceTimeline — Reads real audit/event data from backend -->
  <div class="workspace-timeline space-y-0">
    <div v-if="!events || !events.length" class="py-8 text-center">
      <p class="text-sm" :style="{ color: 'var(--text-muted)' }">No timeline events yet</p>
    </div>
    <div v-else class="relative pl-6">
      <!-- Vertical line -->
      <div class="absolute left-[11px] top-2 bottom-2 w-px" :style="{ background: 'var(--border-light)' }"></div>

      <div v-for="(event, i) in events" :key="event.id || i" class="relative pb-4 last:pb-0">
        <!-- Dot -->
        <div class="absolute left-[-15px] top-1 w-[23px] h-[23px] rounded-full flex items-center justify-center text-xs font-bold z-10"
          :style="dotStyle(event)">
          {{ eventIcon(event) }}
        </div>

        <!-- Content -->
        <div class="ml-3">
          <div class="flex items-center gap-2">
            <p class="text-xs font-semibold" :style="{ color: 'var(--text-primary)' }">{{ event.label || event.event || event.action }}</p>
            <span v-if="event.badge" class="text-[9px] font-bold px-1.5 py-0.5 rounded-full"
              :style="badgeStyle(event)">{{ event.badge }}</span>
          </div>
          <p v-if="event.description || event.detail" class="text-[10px] mt-0.5" :style="{ color: 'var(--text-muted)' }">{{ event.description || event.detail }}</p>
          <div class="flex items-center gap-2 mt-1">
            <span class="text-[9px]" :style="{ color: 'var(--text-muted)' }">{{ formatTime(event.timestamp || event.created_at) }}</span>
            <span v-if="event.actor || event.user_name" class="text-[9px]" :style="{ color: 'var(--text-muted)' }">by {{ event.actor || event.user_name }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  events: { type: Array, default: () => [] },
});

const eventColors = {
  created:     { bg: 'var(--info-soft)', color: 'var(--info-text)', icon: '📝' },
  updated:     { bg: 'var(--primary-soft)', color: 'var(--primary)', icon: '✏️' },
  assigned:    { bg: 'var(--info-soft)', color: 'var(--info-text)', icon: '👤' },
  status_changed: { bg: 'var(--warning-soft)', color: 'var(--warning-text)', icon: '🔄' },
  completed:   { bg: 'var(--success-soft)', color: 'var(--success-text)', icon: '✅' },
  payment:     { bg: 'var(--success-soft)', color: 'var(--success-text)', icon: '💰' },
  qc:          { bg: 'var(--primary-soft)', color: 'var(--primary)', icon: '🔍' },
  warranty:    { bg: 'var(--warning-soft)', color: 'var(--warning-text)', icon: '🛡️' },
  attachment:  { bg: 'var(--bg-hover)', color: 'var(--text-secondary)', icon: '📎' },
  comment:     { bg: 'var(--bg-hover)', color: 'var(--text-secondary)', icon: '💬' },
  notification:{ bg: 'var(--info-soft)', color: 'var(--info-text)', icon: '🔔' },
  automation:  { bg: 'var(--primary-soft)', color: 'var(--primary)', icon: '⚡' },
  approval:    { bg: 'var(--warning-soft)', color: 'var(--warning-text)', icon: '📋' },
  cancelled:   { bg: 'var(--danger-soft)', color: 'var(--danger-text)', icon: '❌' },
};

function dotStyle(event) {
  const c = eventColors[event.type] || eventColors[event.event] || eventColors.updated;
  return { background: c.bg, color: c.color };
}

function eventIcon(event) {
  const c = eventColors[event.type] || eventColors[event.event] || eventColors.updated;
  return c.icon;
}

function badgeStyle(event) {
  const c = eventColors[event.type] || eventColors[event.event] || eventColors.updated;
  return { background: c.bg, color: c.color };
}

function formatTime(v) { if (!v) return ''; const d = new Date(v); return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }); }
</script>
