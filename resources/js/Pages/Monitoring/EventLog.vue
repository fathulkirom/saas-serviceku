<script setup>
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import KCard from '@/Components/KCard.vue'
import KButton from '@/Components/KButton.vue'
import KBadge from '@/Components/KBadge.vue'
import KInput from '@/Components/KInput.vue'
import KSelect from '@/Components/KSelect.vue'

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
  events: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
  stats: { type: Object, default: () => ({ total: 0, today: 0 }) },
})

const search = ref(props.filters?.search || '')
const selectedEvent = ref(props.filters?.event_key || '')
const selectedEntity = ref(props.filters?.entity_type || '')
const selectedSeverity = ref(props.filters?.severity || '')

const debounceTimer = ref(null)
watch([search, selectedEvent, selectedEntity, selectedSeverity], () => {
  clearTimeout(debounceTimer.value)
  debounceTimer.value = setTimeout(() => {
    router.get(route('tenant.event-log.index'), {
      search: search.value,
      event_key: selectedEvent.value,
      entity_type: selectedEntity.value,
      severity: selectedSeverity.value,
    }, { preserveState: true, preserveScroll: true, replace: true })
  }, 300)
})

const eventKeys = computed(() => [...new Set(props.events.map(e => e.event_key))].sort())
const entityTypes = computed(() => [...new Set(props.events.map(e => e.entity_type).filter(Boolean))].sort())

function viewMetadata(e) {
  const meta = typeof e.metadata === 'string' ? JSON.parse(e.metadata) : e.metadata
  alert(JSON.stringify(meta, null, 2))
}

const severityColors = {
  info: 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200',
  warning: 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-200',
  error: 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200',
  critical: 'bg-red-200 text-red-900 dark:bg-red-800 dark:text-red-100',
}
</script>

<template>
  <div class="space-y-6">
    <PageHeader title="Event Log" description="Canonical event store — single source of truth. Append-only, immutable. Semua timeline, history, audit, dan dashboard berasal dari sini.">
      <template #stats>
        <div class="flex gap-4 text-sm">
          <span class="text-gray-500">Total: <strong class="text-gray-900 dark:text-white">{{ stats.total }}</strong></span>
          <span class="text-gray-500">Today: <strong class="text-gray-900 dark:text-white">{{ stats.today }}</strong></span>
        </div>
      </template>
    </PageHeader>

    <!-- Filters -->
    <div class="flex flex-wrap gap-3">
      <KInput v-model="search" placeholder="Search events..." class="w-64" />
      <KSelect v-model="selectedEvent" :options="[{value:'',label:'All Events'}, ...eventKeys.map(k=>({value:k,label:k}))]" />
      <KSelect v-model="selectedEntity" :options="[{value:'',label:'All Entities'}, ...entityTypes.map(t=>({value:t,label:t.split('\\').pop()}))]" />
      <KSelect v-model="selectedSeverity" :options="[{value:'',label:'All Severity'},{value:'info',label:'Info'},{value:'warning',label:'Warning'},{value:'error',label:'Error'},{value:'critical',label:'Critical'}]" />
    </div>

    <!-- Event Table -->
    <KCard class="overflow-hidden !p-0">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-left text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800/50">
              <th class="px-4 py-3 font-medium">Event</th>
              <th class="px-4 py-3 font-medium">Entity</th>
              <th class="px-4 py-3 font-medium">Severity</th>
              <th class="px-4 py-3 font-medium">Actor</th>
              <th class="px-4 py-3 font-medium">Correlation</th>
              <th class="px-4 py-3 font-medium">Time</th>
              <th class="px-4 py-3 font-medium"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="e in events" :key="e.id" class="border-b border-gray-50 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50">
              <td class="px-4 py-3">
                <KBadge size="xs" variant="outline">{{ e.event_key }}</KBadge>
              </td>
              <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                {{ e.entity_type?.split('\\').pop() || '-' }}<span v-if="e.entity_id" class="text-gray-400"> #{{ e.entity_id }}</span>
              </td>
              <td class="px-4 py-3">
                <KBadge size="xs" :class="severityColors[e.severity] || severityColors.info">{{ e.severity || 'info' }}</KBadge>
              </td>
              <td class="px-4 py-3 text-gray-500">{{ e.actor?.name || 'System' }}</td>
              <td class="px-4 py-3 text-xs text-gray-400 font-mono">{{ e.correlation_id?.substring(0, 8) || '-' }}</td>
              <td class="px-4 py-3 text-xs text-gray-400 whitespace-nowrap">{{ new Date(e.occurred_at).toLocaleString('id-ID') }}</td>
              <td class="px-4 py-3 text-right">
                <KButton size="xs" variant="ghost" @click="viewMetadata(e)">Meta</KButton>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="events.length === 0" class="text-center py-12 text-gray-400">Tidak ada event yang cocok dengan filter.</div>
    </KCard>
  </div>
</template>
