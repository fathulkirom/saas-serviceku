<template>
  <!-- UniversalWorkspaceSidebar — Reads workspace metadata, auto-generated from registry -->
  <aside class="workspace-sidebar flex flex-col h-full overflow-y-auto" :style="{ background: 'var(--bg-surface)', borderLeft: '1px solid var(--border-light)', width: '280px', minWidth: '280px' }">
    <!-- Header -->
    <div class="p-4 border-b" :style="{ borderColor: 'var(--border-light)' }">
      <div class="flex items-center justify-between mb-2">
        <h3 class="text-sm font-bold" :style="{ color: 'var(--text-primary)' }">{{ title || 'Details' }}</h3>
        <button @click="$emit('close')" class="text-xs px-2 py-1 rounded hover:opacity-80" :style="{ color: 'var(--text-muted)' }">✕</button>
      </div>

      <!-- Status Badge -->
      <div v-if="record.status" class="mb-3">
        <span class="text-[10px] font-bold px-2 py-1 rounded-full uppercase"
          :style="statusStyle">{{ record.status_label || record.status }}</span>
        <span v-if="record.priority" class="ml-1 text-[10px] font-bold px-2 py-1 rounded-full uppercase"
          :style="priorityStyle">{{ record.priority_label || record.priority }}</span>
      </div>

      <!-- Key Metadata -->
      <div class="space-y-2">
        <div v-if="record.tracking_code || record.code || record.number" class="flex justify-between text-xs">
          <span :style="{ color: 'var(--text-muted)' }">#</span>
          <span class="font-mono font-bold" :style="{ color: 'var(--text-primary)' }">{{ record.tracking_code || record.code || record.number }}</span>
        </div>
        <div v-if="record.customer_name || record.name" class="flex justify-between text-xs">
          <span :style="{ color: 'var(--text-muted)' }">Customer</span>
          <span class="font-medium" :style="{ color: 'var(--text-primary)' }">{{ record.customer_name || record.name }}</span>
        </div>
        <div v-if="record.technician_name || record.assigned_to" class="flex justify-between text-xs">
          <span :style="{ color: 'var(--text-muted)' }">Assigned</span>
          <span class="font-medium" :style="{ color: 'var(--text-primary)' }">{{ record.technician_name || record.assigned_to }}</span>
        </div>
        <div v-if="record.created_at" class="flex justify-between text-xs">
          <span :style="{ color: 'var(--text-muted)' }">Created</span>
          <span :style="{ color: 'var(--text-primary)' }">{{ formatDate(record.created_at) }}</span>
        </div>
        <div v-if="record.updated_at" class="flex justify-between text-xs">
          <span :style="{ color: 'var(--text-muted)' }">Updated</span>
          <span :style="{ color: 'var(--text-primary)' }">{{ formatDate(record.updated_at) }}</span>
        </div>
      </div>
    </div>

    <!-- Stats Section -->
    <div v-if="stats && Object.keys(stats).length" class="p-3 border-b" :style="{ borderColor: 'var(--border-light)' }">
      <h4 class="text-[10px] uppercase tracking-wider mb-2" :style="{ color: 'var(--text-muted)' }">Statistics</h4>
      <div class="grid grid-cols-2 gap-2">
        <div v-for="(val, key) in stats" :key="key" class="p-2 rounded-lg text-center" :style="{ background: 'var(--bg-hover)' }">
          <p class="text-sm font-bold" :style="{ color: 'var(--text-primary)' }">{{ formatValue(val) }}</p>
          <p class="text-[9px] uppercase" :style="{ color: 'var(--text-muted)' }">{{ formatLabel(key) }}</p>
        </div>
      </div>
    </div>

    <!-- Related Records -->
    <div v-if="relations && relations.length" class="p-3 border-b" :style="{ borderColor: 'var(--border-light)' }">
      <h4 class="text-[10px] uppercase tracking-wider mb-2" :style="{ color: 'var(--text-muted)' }">Related</h4>
      <div class="space-y-1">
        <div v-for="rel in relations" :key="rel.id || rel.key" class="flex items-center justify-between py-1 px-2 rounded text-xs cursor-pointer hover:opacity-80" :style="{ background: rel.active ? 'var(--primary-soft)' : 'transparent' }" @click="$emit('navigate', rel)">
          <span class="flex items-center gap-1.5">
            <span>{{ rel.icon || '📋' }}</span>
            <span :style="{ color: 'var(--text-primary)' }">{{ rel.label }}</span>
          </span>
          <span class="font-bold" :style="{ color: 'var(--text-muted)' }">{{ rel.count || '' }}</span>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div v-if="quickActions && quickActions.length" class="p-3 border-b" :style="{ borderColor: 'var(--border-light)' }">
      <h4 class="text-[10px] uppercase tracking-wider mb-2" :style="{ color: 'var(--text-muted)' }">Quick Actions</h4>
      <div class="space-y-1">
        <button v-for="action in quickActions" :key="action.id" @click="$emit('action', action.id)"
          class="w-full text-left text-xs px-2 py-1.5 rounded font-medium hover:opacity-80 transition"
          :style="{ background: 'var(--bg-hover)', color: 'var(--text-primary)' }">
          <span class="mr-1.5">{{ action.icon || '▶' }}</span>{{ action.label }}
        </button>
      </div>
    </div>

    <!-- Feature & Permission Indicators -->
    <div class="p-3 border-b" :style="{ borderColor: 'var(--border-light)' }">
      <h4 class="text-[10px] uppercase tracking-wider mb-2" :style="{ color: 'var(--text-muted)' }">Access</h4>
      <div class="space-y-1">
        <div v-for="f in (features || [])" :key="f" class="flex justify-between text-[10px]">
          <span :style="{ color: 'var(--text-muted)' }">Feature: {{ f }}</span>
          <span :style="{ color: 'var(--success)' }">✅</span>
        </div>
        <div v-for="p in (permissions || [])" :key="p" class="flex justify-between text-[10px]">
          <span :style="{ color: 'var(--text-muted)' }">Permission: {{ p }}</span>
          <span :style="{ color: 'var(--success)' }">✅</span>
        </div>
      </div>
    </div>

    <!-- Branch & Tags -->
    <div v-if="record.branch_name || (tags && tags.length)" class="p-3" :style="{ borderColor: 'var(--border-light)' }">
      <div v-if="record.branch_name" class="flex justify-between text-xs mb-2">
        <span :style="{ color: 'var(--text-muted)' }">Branch</span>
        <span class="font-medium" :style="{ color: 'var(--text-primary)' }">{{ record.branch_name }}</span>
      </div>
      <div v-if="tags && tags.length" class="flex flex-wrap gap-1">
        <span v-for="tag in tags" :key="tag" class="text-[9px] font-medium px-1.5 py-0.5 rounded"
          :style="{ background: 'var(--bg-hover)', color: 'var(--text-secondary)' }">{{ tag }}</span>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  title: { type: String, default: 'Details' },
  record: { type: Object, default: () => ({}) },
  stats: { type: Object, default: null },
  relations: { type: Array, default: null },
  quickActions: { type: Array, default: null },
  features: { type: Array, default: null },
  permissions: { type: Array, default: null },
  tags: { type: Array, default: null },
});

defineEmits(['close', 'navigate', 'action']);

const statusStyle = computed(() => {
  const s = props.record?.status;
  const colors = {
    active: { background: 'var(--info-soft)', color: 'var(--info-text)' },
    completed: { background: 'var(--success-soft)', color: 'var(--success-text)' },
    pending: { background: 'var(--warning-soft)', color: 'var(--warning-text)' },
    cancelled: { background: 'var(--danger-soft)', color: 'var(--danger-text)' },
    closed: { background: 'var(--bg-hover)', color: 'var(--text-muted)' },
  };
  return colors[s] || { background: 'var(--bg-hover)', color: 'var(--text-secondary)' };
});

const priorityStyle = computed(() => {
  const p = props.record?.priority;
  const colors = {
    high: { background: 'var(--danger-soft)', color: 'var(--danger-text)' },
    medium: { background: 'var(--warning-soft)', color: 'var(--warning-text)' },
    low: { background: 'var(--success-soft)', color: 'var(--success-text)' },
    critical: { background: 'var(--danger-soft)', color: 'var(--danger-text)' },
  };
  return colors[p] || { background: 'var(--bg-hover)', color: 'var(--text-secondary)' };
});

function formatDate(v) { if (!v) return '-'; return new Date(v).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }); }
function formatValue(v) { if (typeof v === 'number') return new Intl.NumberFormat('id-ID').format(v); return v; }
function formatLabel(key) { return key.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()); }
</script>
