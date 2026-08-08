<script setup>
import { ref, computed } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import KCard from '@/Components/KCard.vue'
import KButton from '@/Components/KButton.vue'
import KBadge from '@/Components/KBadge.vue'
import KSelect from '@/Components/KSelect.vue'
import KInput from '@/Components/KInput.vue'
import KDialog from '@/Components/KDialog.vue'

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
  rules: { type: Array, default: () => [] },
  workflows: { type: Array, default: () => [] },
  eventOptions: { type: Array, default: () => ['workflow.state_changed', 'service.status_changed', 'request.created', 'request.completed', 'work_order.completed', 'payment.received'] },
  actionOptions: { type: Array, default: () => ['send_whatsapp', 'send_email', 'upload_gdrive', 'create_timeline', 'generate_pdf', 'assign_user', 'create_work_order', 'generate_review', 'browser_notify', 'generate_reminder'] },
})

const showCreateDialog = ref(false)
const editingRule = ref(null)

const form = useForm({
  name: '',
  event: '',
  workflow_key: null,
  conditions: [],
  action_type: '',
  action_config: {},
  delay_minutes: 0,
  is_active: true,
})

function openCreate() {
  form.reset()
  editingRule.value = null
  showCreateDialog.value = true
}

function openEdit(rule) {
  editingRule.value = rule.id
  form.name = rule.name
  form.event = rule.event
  form.workflow_key = rule.workflow_key
  form.action_type = rule.action_type
  form.delay_minutes = rule.delay_minutes || 0
  form.is_active = rule.is_active
  showCreateDialog.value = true
}

function save() {
  if (editingRule.value) {
    form.put(route('tenant.automation.update', editingRule.value), { preserveScroll: true, onSuccess: () => { showCreateDialog.value = false } })
  } else {
    form.post(route('tenant.automation.store'), { preserveScroll: true, onSuccess: () => { showCreateDialog.value = false } })
  }
}

function toggleRule(rule) {
  router.post(route('tenant.automation.toggle', rule.id), {}, { preserveScroll: true })
}

const statusBadge = (s) => s ? 'sk-bg-success-soft sk-text-success dark:bg-green-900 dark:text-green-200' : 'sk-bg-hover sk-text-muted dark:sk-bg-inverse dark:sk-text-muted'
const actionLabel = (a) => ({
  send_whatsapp: '📱 WhatsApp', send_email: '📧 Email', upload_gdrive: '📁 Google Drive',
  create_timeline: '📝 Timeline', generate_pdf: '📄 PDF', assign_user: '👤 Assign User',
  create_work_order: '🔧 Work Order', generate_review: '⭐ Review Link', browser_notify: '🔔 Browser',
  generate_reminder: '⏰ Reminder',
}[a] || a)
</script>

<template>
  <div class="space-y-6">
    <PageHeader title="Automation Builder" description="IF condition THEN action — semua rule berbasis data. Tidak ada if/else hardcode.">
      <template #actions>
        <KButton @click="openCreate">+ New Rule</KButton>
      </template>
    </PageHeader>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <KCard class="text-center"><div class="text-2xl font-bold text-primary-600">{{ rules.length }}</div><div class="text-xs sk-text-muted mt-1">Total Rules</div></KCard>
      <KCard class="text-center"><div class="text-2xl font-bold sk-text-success">{{ rules.filter(r => r.is_active).length }}</div><div class="text-xs sk-text-muted mt-1">Active</div></KCard>
      <KCard class="text-center"><div class="text-2xl font-bold sk-text-warning">{{ rules.filter(r => r.delay_minutes > 0).length }}</div><div class="text-xs sk-text-muted mt-1">Delayed</div></KCard>
      <KCard class="text-center"><div class="text-2xl font-bold sk-text-secondary">{{ rules.filter(r => r.is_template).length }}</div><div class="text-xs sk-text-muted mt-1">Templates</div></KCard>
    </div>

    <!-- Rules List -->
    <div class="space-y-3">
      <KCard v-for="rule in rules" :key="rule.id" class="hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between gap-4">
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
              <h3 class="font-semibold sk-text-primary dark:text-white truncate">{{ rule.name }}</h3>
              <KBadge :class="statusBadge(rule.is_active)">{{ rule.is_active ? 'Active' : 'Inactive' }}</KBadge>
              <KBadge v-if="rule.is_template" class="bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-200">Template</KBadge>
            </div>
            <p class="text-xs sk-text-muted dark:sk-text-muted mb-2">
              Event: <code class="sk-bg-hover dark:sk-bg-inverse px-1 rounded text-xs">{{ rule.event }}</code>
              <span v-if="rule.workflow_key" class="ml-2">Workflow: <code class="sk-bg-hover dark:sk-bg-inverse px-1 rounded text-xs">{{ rule.workflow_key }}</code></span>
            </p>
            <div class="flex items-center gap-2 text-xs">
              <KBadge variant="outline" size="xs">IF</KBadge>
              <span class="sk-text-muted">{{ rule.conditions ? 'conditions defined' : 'always' }}</span>
              <KBadge variant="outline" size="xs" class="ml-2">THEN</KBadge>
              <KBadge class="sk-bg-info-soft sk-text-info dark:bg-blue-900 dark:text-blue-200" size="xs">{{ actionLabel(rule.action_type) }}</KBadge>
              <span v-if="rule.delay_minutes > 0" class="sk-text-warning text-xs">⏱️ +{{ rule.delay_minutes }} min</span>
            </div>
          </div>
          <div class="flex gap-1 shrink-0">
            <KButton size="xs" variant="ghost" @click="toggleRule(rule)">{{ rule.is_active ? 'Disable' : 'Enable' }}</KButton>
            <KButton size="xs" variant="ghost" @click="openEdit(rule)">Edit</KButton>
          </div>
        </div>
      </KCard>
    </div>

    <!-- Create/Edit Dialog -->
    <KDialog :open="showCreateDialog" @close="showCreateDialog = false" :title="editingRule ? 'Edit Rule' : 'New Automation Rule'" size="lg">
      <form @submit.prevent="save" class="space-y-4">
        <div>
          <label class="block text-sm font-medium sk-text-primary dark:sk-text-muted mb-1">Rule Name</label>
          <KInput v-model="form.name" placeholder="e.g., Kirim WhatsApp saat Service Selesai" required class="w-full" />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium sk-text-primary dark:sk-text-muted mb-1">Event</label>
            <KSelect v-model="form.event" :options="eventOptions.map(e => ({ value: e, label: e }))" required />
          </div>
          <div>
            <label class="block text-sm font-medium sk-text-primary dark:sk-text-muted mb-1">Workflow</label>
            <KSelect v-model="form.workflow_key" :options="[{value: null, label: 'All'}, ...workflows.map(w => ({value: w.key, label: w.label}))]" />
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium sk-text-primary dark:sk-text-muted mb-1">Action</label>
            <KSelect v-model="form.action_type" :options="actionOptions.map(a => ({value: a, label: actionLabel(a)}))" required />
          </div>
          <div>
            <label class="block text-sm font-medium sk-text-primary dark:sk-text-muted mb-1">Delay (minutes)</label>
            <KInput v-model="form.delay_minutes" type="number" min="0" placeholder="0 = immediate" />
          </div>
        </div>
        <!-- Recipient for WhatsApp/Email -->
        <div v-if="['send_whatsapp', 'send_email'].includes(form.action_type)">
          <label class="block text-sm font-medium sk-text-primary dark:sk-text-muted mb-1">Recipient</label>
          <KSelect v-model="form.action_config.recipient" :options="[{value: 'customer', label: 'Customer'}, {value: 'technician', label: 'Technician'}, {value: 'owner', label: 'Owner'}, {value: 'branch', label: 'Branch'}]" />
        </div>
        <div v-if="['send_whatsapp', 'send_email'].includes(form.action_type)">
          <label class="block text-sm font-medium sk-text-primary dark:sk-text-muted mb-1">Message Template</label>
          <KInput v-model="form.action_config.message" placeholder="Use {customer_name}, {id}, {status}, {tracking_code}, {date}" class="w-full" />
        </div>
        <div class="flex items-center gap-2">
          <input type="checkbox" v-model="form.is_active" id="active" class="rounded" />
          <label for="active" class="text-sm sk-text-primary dark:sk-text-muted">Active</label>
        </div>
        <div class="flex justify-end gap-2 pt-4 border-t dark:sk-border">
          <KButton variant="outline" type="button" @click="showCreateDialog = false">Cancel</KButton>
          <KButton type="submit" :disabled="form.processing">{{ editingRule ? 'Update' : 'Create' }}</KButton>
        </div>
      </form>
    </KDialog>
  </div>
</template>
