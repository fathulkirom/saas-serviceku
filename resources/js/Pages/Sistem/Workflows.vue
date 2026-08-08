<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import KCard from '@/Components/KCard.vue'
import KButton from '@/Components/KButton.vue'
import KBadge from '@/Components/KBadge.vue'
import KDialog from '@/Components/KDialog.vue'

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
  workflows: { type: Array, default: () => [] },
  graph: { type: Object, default: () => null },
})

const selectedWorkflow = ref(props.workflows[0]?.key || null)
const selectedTransition = ref(null)
const showTransitionDialog = ref(false)

const currentGraph = computed(() => {
  if (!selectedWorkflow.value) return null
  // Would be fetched via router.get — for now using prop
  return props.graph
})

const states = computed(() => currentGraph.value?.states || [])
const transitions = computed(() => currentGraph.value?.transitions || [])

function selectWorkflow(key) {
  selectedWorkflow.value = key
  router.get(route('tenant.workflows.graph', { workflow: key }), {}, { preserveState: true, preserveScroll: true })
}

function openTransition(t) {
  selectedTransition.value = t
  showTransitionDialog.value = true
}

const categoryColors = {
  active: 'sk-bg-info-soft border-blue-400 text-blue-800 dark:bg-blue-900 dark:border-blue-600 dark:text-blue-200',
  waiting: 'sk-bg-warning-soft border-amber-400 sk-text-warning dark:bg-amber-900 dark:border-amber-600 dark:text-amber-200',
  done: 'sk-bg-success-soft border-green-400 sk-text-success dark:bg-green-900 dark:border-green-600 dark:text-green-200',
  cancelled: 'sk-bg-danger-soft border-red-400 sk-text-danger dark:bg-red-900 dark:border-red-600 dark:text-red-200',
}
</script>

<template>
  <div class="space-y-6">
    <PageHeader title="Workflow Builder" description="Visual state machine editor. Semua workflow berasal dari database — tidak ada hardcode." />

    <!-- Workflow Selector -->
    <div class="flex flex-wrap gap-2">
      <button v-for="wf in workflows" :key="wf.key" @click="selectWorkflow(wf.key)"
        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
        :class="selectedWorkflow === wf.key ? 'bg-primary-600 text-white shadow' : 'sk-bg-card dark:sk-bg-inverse border sk-border dark:sk-border hover:border-primary-300 dark:hover:border-primary-600 sk-text-primary dark:sk-text-muted'">
        {{ wf.label }}
        <KBadge size="xs" class="ml-2" :class="wf.is_active ? 'sk-bg-success-soft sk-text-success' : 'sk-bg-hover sk-text-muted'">
          {{ wf.is_active ? 'Active' : 'Inactive' }}
        </KBadge>
      </button>
    </div>

    <!-- State Machine Canvas -->
    <KCard v-if="currentGraph">
      <div class="mb-4 flex items-center justify-between">
        <div>
          <h3 class="text-lg font-semibold sk-text-primary dark:text-white">{{ currentGraph.label }}</h3>
          <p class="text-sm sk-text-muted">Initial: <KBadge variant="outline">{{ currentGraph.initial }}</KBadge> &middot; {{ states.length }} states &middot; {{ transitions.length }} transitions</p>
        </div>
        <div class="flex gap-2">
          <KButton size="sm" variant="outline">Export JSON</KButton>
          <KButton size="sm" variant="outline">Import</KButton>
        </div>
      </div>

      <!-- States Grid -->
      <div class="mb-6">
        <h4 class="text-sm font-medium sk-text-muted dark:sk-text-muted mb-3 uppercase tracking-wide">States</h4>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2">
          <div v-for="state in states" :key="state.key"
            class="px-3 py-2 rounded-lg border-2 text-center text-sm font-medium transition-shadow hover:shadow-md cursor-default"
            :class="[categoryColors[state.category] || 'sk-bg-hover sk-border dark:sk-bg-inverse dark:border-gray-600', state.is_terminal ? 'border-dashed' : '']"
            :title="state.label + (state.is_terminal ? ' (Terminal)' : '')">
            {{ state.label }}
            <span v-if="state.is_terminal" class="block text-xs opacity-60">terminal</span>
          </div>
        </div>
      </div>

      <!-- Transitions Table -->
      <div>
        <h4 class="text-sm font-medium sk-text-muted dark:sk-text-muted mb-3 uppercase tracking-wide">Transitions</h4>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="text-left sk-text-muted dark:sk-text-muted border-b dark:sk-border">
                <th class="pb-2 font-medium">From</th>
                <th class="pb-2 font-medium">→</th>
                <th class="pb-2 font-medium">To</th>
                <th class="pb-2 font-medium">Label</th>
                <th class="pb-2 font-medium">Permission</th>
                <th class="pb-2 font-medium">Auto</th>
                <th class="pb-2 font-medium"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="t in transitions" :key="`${t.from}-${t.to}`"
                class="border-b border-gray-50 dark:border-gray-800 hover:sk-bg-hover dark:hover:sk-bg-inverse/50 cursor-pointer"
                @click="openTransition(t)">
                <td class="py-2"><KBadge size="xs" variant="outline">{{ t.from }}</KBadge></td>
                <td class="py-2 sk-text-muted">→</td>
                <td class="py-2"><KBadge size="xs" variant="outline">{{ t.to }}</KBadge></td>
                <td class="py-2">{{ t.label || '-' }}</td>
                <td class="py-2 text-xs sk-text-muted">{{ t.permission || '-' }}</td>
                <td class="py-2">{{ t.is_auto ? '⚡' : '-' }}</td>
                <td class="py-2 text-right">
                  <KButton size="xs" variant="ghost">Detail</KButton>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <p v-if="transitions.length === 0" class="text-sm sk-text-muted py-4 text-center">Tidak ada transisi untuk workflow ini.</p>
      </div>
    </KCard>

    <!-- Empty -->
    <KCard v-else class="text-center py-12">
      <p class="sk-text-muted dark:sk-text-muted">Pilih workflow untuk melihat state machine visual.</p>
      <p class="text-sm sk-text-muted mt-1">Data workflow berasal dari tabel `workflows`, `workflow_states`, dan `workflow_transitions`.</p>
    </KCard>

    <!-- Transition Detail Dialog -->
    <KDialog v-if="showTransitionDialog && selectedTransition" :open="showTransitionDialog" @close="showTransitionDialog = false" title="Transition Detail">
      <div class="space-y-3 text-sm">
        <div class="flex gap-4">
          <div><span class="sk-text-muted">From:</span> <KBadge>{{ selectedTransition.from }}</KBadge></div>
          <div><span class="sk-text-muted">To:</span> <KBadge>{{ selectedTransition.to }}</KBadge></div>
        </div>
        <div><span class="sk-text-muted">Label:</span> {{ selectedTransition.label || '-' }}</div>
        <div><span class="sk-text-muted">Permission:</span> {{ selectedTransition.permission || 'Tidak ada' }}</div>
        <div><span class="sk-text-muted">Auto:</span> {{ selectedTransition.is_auto ? 'Ya (system trigger)' : 'Tidak (manual)' }}</div>
      </div>
    </KDialog>
  </div>
</template>
