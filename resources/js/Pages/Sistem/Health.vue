<script setup>
import { computed } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import KCard from '@/Components/KCard.vue'
import KBadge from '@/Components/KBadge.vue'

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
  health: { type: Object, default: () => ({}) },
})

const engines = computed(() => [
  { key: 'permission', label: 'Permission Engine', status: props.health?.permission || 'healthy', desc: 'Role, permission, user assignment' },
  { key: 'feature', label: 'Feature Engine', status: props.health?.feature || 'healthy', desc: 'Module registry, plan, business type' },
  { key: 'provider', label: 'Provider Engine', status: props.health?.provider || 'healthy', desc: 'WhatsApp, Email, GDrive, Payment' },
  { key: 'workflow', label: 'Workflow Engine', status: props.health?.workflow || 'healthy', desc: 'State machine, transitions, guards' },
  { key: 'automation', label: 'Automation Engine', status: props.health?.automation || 'healthy', desc: 'Rules, conditions, actions' },
  { key: 'settings', label: 'Settings Engine', status: props.health?.settings || 'healthy', desc: 'Tenant configuration, registry' },
  { key: 'request', label: 'Request Engine', status: props.health?.request || 'healthy', desc: 'Core entry point, forking' },
  { key: 'sla', label: 'SLA Engine', status: props.health?.sla || 'healthy', desc: 'Service level targets, escalation' },
])

const infrastructure = computed(() => [
  { key: 'queue', label: 'Queue', status: props.health?.queue || 'healthy', jobs: props.health?.queue_jobs || 0 },
  { key: 'schedule', label: 'Scheduler', status: props.health?.schedule || 'healthy', last: props.health?.schedule_last || '-' },
  { key: 'broadcast', label: 'Broadcast', status: props.health?.broadcast || 'healthy', conn: props.health?.broadcast_connections || 0 },
  { key: 'cache', label: 'Cache', status: props.health?.cache || 'healthy', driver: props.health?.cache_driver || '-' },
  { key: 'storage', label: 'Storage', status: props.health?.storage || 'healthy', disk: props.health?.storage_disk || '-' },
  { key: 'database', label: 'Database', status: props.health?.database || 'healthy', conn: props.health?.db_connection || '-' },
])

const statusIcon = (s) => ({ healthy: '✅', degraded: '⚠️', down: '🔴', unknown: '❓' }[s] || '❓')
const statusColor = (s) => ({ healthy: 'border-green-400 sk-bg-success-soft dark:bg-green-900/20', degraded: 'border-amber-400 sk-bg-warning-soft dark:bg-amber-900/20', down: 'border-red-400 sk-bg-danger-soft dark:bg-red-900/20' }[s] || 'sk-border')
</script>

<template>
  <div class="space-y-6">
    <PageHeader title="System Health" description="Status seluruh engine dan infrastruktur ServiceKU." />

    <!-- Engines -->
    <h3 class="text-sm font-medium sk-text-muted dark:sk-text-muted uppercase tracking-wide">Business Engines</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <KCard v-for="engine in engines" :key="engine.key" :class="statusColor(engine.status)">
        <div class="flex items-center justify-between mb-1">
          <h4 class="font-semibold sk-text-primary dark:text-white text-sm">{{ engine.label }}</h4>
          <span class="text-lg">{{ statusIcon(engine.status) }}</span>
        </div>
        <p class="text-xs sk-text-muted dark:sk-text-muted">{{ engine.desc }}</p>
        <KBadge size="xs" :class="engine.status === 'healthy' ? 'sk-bg-success-soft sk-text-success' : 'sk-bg-danger-soft sk-text-danger'" class="mt-2">{{ engine.status }}</KBadge>
      </KCard>
    </div>

    <!-- Infrastructure -->
    <h3 class="text-sm font-medium sk-text-muted dark:sk-text-muted uppercase tracking-wide">Infrastructure</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <KCard v-for="infra in infrastructure" :key="infra.key" :class="statusColor(infra.status)">
        <div class="flex items-center justify-between mb-1">
          <h4 class="font-semibold sk-text-primary dark:text-white text-sm">{{ infra.label }}</h4>
          <span class="text-lg">{{ statusIcon(infra.status) }}</span>
        </div>
        <div class="text-xs sk-text-muted dark:sk-text-muted space-y-0.5">
          <span v-if="infra.jobs !== undefined">Jobs: {{ infra.jobs }}</span>
          <span v-if="infra.last">Last: {{ infra.last }}</span>
          <span v-if="infra.conn !== undefined">Connections: {{ infra.conn }}</span>
          <span v-if="infra.driver">Driver: {{ infra.driver }}</span>
          <span v-if="infra.disk">Disk: {{ infra.disk }}</span>
          <span v-if="infra.conn">DB: {{ infra.conn }}</span>
        </div>
        <KBadge size="xs" :class="infra.status === 'healthy' ? 'sk-bg-success-soft sk-text-success' : 'sk-bg-danger-soft sk-text-danger'" class="mt-2">{{ infra.status }}</KBadge>
      </KCard>
    </div>
  </div>
</template>
