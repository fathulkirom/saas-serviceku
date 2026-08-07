<script setup>
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import KCard from '@/Components/KCard.vue'
import KButton from '@/Components/KButton.vue'
import KDialog from '@/Components/KDialog.vue'
import KInput from '@/Components/KInput.vue'
import KSelect from '@/Components/KSelect.vue'

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({ templates: { type: Array, default: () => [] } })

const showDialog = ref(false)
const editing = ref(null)

const form = useForm({
    name: '', key: '', channel: 'whatsapp', subject: '', body: '', is_active: true,
})

function openCreate() { editing.value = null; form.reset(); showDialog.value = true }
function openEdit(t) {
    editing.value = t.id
    form.name = t.name; form.key = t.key; form.channel = t.channel
    form.subject = t.subject; form.body = t.body; form.is_active = t.is_active
    showDialog.value = true
}
function save() {
    if (editing.value) form.put(route('message-templates.update', editing.value), { preserveScroll: true, onSuccess: () => showDialog.value = false })
    else form.post(route('message-templates.store'), { preserveScroll: true, onSuccess: () => showDialog.value = false })
}

const variables = ['customer_name', 'device', 'service_number', 'amount', 'warranty_date']
function insertVar(v) { form.body += '{{' + v + '}}' }
const varLabel = (v) => '{' + '{' + v + '}' + '}'
</script>

<template>
  <div class="space-y-6">
    <PageHeader title="Template Pesan" description="Template WhatsApp & Email untuk komunikasi customer.">
      <template #actions><KButton @click="openCreate">+ Template Baru</KButton></template>
    </PageHeader>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <KCard v-for="t in templates" :key="t.id" class="!p-4">
        <div class="flex items-center justify-between mb-2">
          <h3 class="font-semibold text-gray-900 dark:text-white">{{ t.name }}</h3>
          <div class="flex items-center gap-2">
            <span class="text-xs px-2 py-0.5 rounded-full" :class="t.channel === 'whatsapp' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'">{{ t.channel }}</span>
            <span v-if="t.is_system" class="text-xs text-zinc-400">sistem</span>
          </div>
        </div>
        <pre class="text-xs text-gray-500 dark:text-gray-400 whitespace-pre-wrap line-clamp-4 mb-3">{{ t.body }}</pre>
        <div class="flex gap-2">
          <KButton size="xs" variant="outline" @click="openEdit(t)">Edit</KButton>
        </div>
      </KCard>
    </div>

    <KDialog :open="showDialog" @close="showDialog = false" :title="editing ? 'Edit Template' : 'Template Baru'" size="lg">
      <form @submit.prevent="save" class="space-y-4">
        <div class="grid grid-cols-2 gap-3">
          <KInput v-model="form.name" label="Nama" required />
          <KInput v-model="form.key" label="Key" required :disabled="!!editing" />
        </div>
        <KSelect v-model="form.channel" label="Channel" :options="[{value:'whatsapp',label:'WhatsApp'},{value:'email',label:'Email'}]" />
        <KInput v-if="form.channel === 'email'" v-model="form.subject" label="Subject" />
        <div>
          <label class="block text-sm font-medium mb-1">Body</label>
          <textarea v-model="form.body" rows="6" class="w-full px-3 py-2 border border-zinc-200 rounded-lg text-sm dark:bg-gray-800 dark:border-gray-700" required></textarea>
          <div class="flex flex-wrap gap-1 mt-1">
            <button v-for="v in variables" :key="v" type="button" @click="insertVar(v)" class="text-xs px-2 py-0.5 bg-zinc-100 hover:bg-zinc-200 rounded-full dark:bg-gray-700 dark:hover:bg-gray-600">{{ varLabel(v) }}</button>
          </div>
        </div>
        <div class="flex justify-end gap-2 pt-4 border-t dark:border-gray-700">
          <KButton variant="outline" type="button" @click="showDialog = false">Batal</KButton>
          <KButton type="submit" :disabled="form.processing">Simpan</KButton>
        </div>
      </form>
    </KDialog>
  </div>
</template>
