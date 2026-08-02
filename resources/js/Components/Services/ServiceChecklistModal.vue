<template>
    <KDialog :model-value="show" @update:model-value="show = $event" max-width="lg" scrollable>
        <h3 class="text-base font-bold mb-4 text-zinc-900">{{ isMasuk ? (existing ? 'Edit Checklist Masuk' : 'Isi Checklist Masuk') : (existing ? 'Edit Checklist Keluar' : 'Isi Checklist Keluar') }}</h3>
        <div class="space-y-4">
            <div>
                <label class="block text-xs font-semibold mb-1 text-zinc-500">{{ isMasuk ? 'Template Checklist' : 'Template Checklist Keluar' }}</label>
                <KSelect v-model="form.template_id">
                    <option value="">-- Pilih Template --</option>
                    <option v-for="tpl in templates" :key="tpl.id" :value="tpl.id">{{ tpl.name }}</option>
                </KSelect>
            </div>
            <div v-if="checkItems.length" :class="isMasuk ? 'space-y-1' : 'space-y-1 max-h-60 overflow-y-auto pr-1'">
                <label v-for="item in checkItems" :key="item.id"
                    :class="isMasuk ? 'flex items-center gap-2 text-sm cursor-pointer py-0.5 text-zinc-600' : 'flex items-center gap-2 text-sm cursor-pointer py-1 border-b border-dark-100/30 text-zinc-600'">
                    <KCheckbox :value="item.id" v-model="form.checked_items"
                        class="rounded" :style="{ 'accent-color': isMasuk ? 'var(--primary)' : '#2563eb' }" />
                    {{ item.item_name }}
                </label>
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1 text-zinc-500">{{ isMasuk ? 'Catatan' : 'Catatan Keluar' }}</label>
                <KTextarea v-model="form.notes" rows="2" />
            </div>
        </div>
        <div class="flex gap-2 mt-5">
            <KButton variant="modal-secondary" @click="show = false">Batal</KButton>
            <KButton v-if="isMasuk" variant="modal-primary-indigo" shadow :disabled="processing === 'save_checklist_masuk'" @click="executeSave">{{ processing === 'save_checklist_masuk' ? 'Menyimpan...' : 'Simpan' }}</KButton>
            <KButton v-else variant="modal-primary" shadow button-style="background: #2563eb;" :disabled="processing === 'save_checklist_keluar'" @click="executeSave">{{ processing === 'save_checklist_keluar' ? 'Menyimpan...' : 'Simpan Checklist Keluar' }}</KButton>
        </div>
    </KDialog>
</template>

<script setup>
import KCheckbox from '@/Components/KCheckbox.vue';

import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import KDialog from '@/Components/KDialog.vue';
import KButton from '@/Components/KButton.vue';
import KSelect from '@/Components/KSelect.vue';
import KTextarea from '@/Components/KTextarea.vue';

const props = defineProps({
    service: { type: Object, default: () => ({}) },
    templates: { type: Array, default: () => [] },
    mode: { type: String, default: 'masuk' }, // masuk | keluar
});

const isMasuk = computed(() => props.mode === 'masuk');

const show = ref(false);
const processing = ref(null);
const form = ref({
    template_id: '',
    checked_items: [],
    notes: '',
});

const existing = computed(() => {
    return (props.service.checklists || []).find(c => c.type === props.mode);
});

const checkItems = computed(() => {
    if (!form.value.template_id) return [];
    const tpl = props.templates.find(t => t.id == form.value.template_id);
    return tpl?.items || [];
});

function open() {
    const prev = existing.value;
    form.value = {
        template_id: prev?.checklist_template_id?.toString() || (isMasuk.value ? '' : (props.templates[0]?.id?.toString() || '')),
        checked_items: prev?.checked_items ? [...prev.checked_items] : [],
        notes: prev?.notes || '',
    };
    show.value = true;
}

function executeSave() {
    if (!form.value.template_id) return;
    const actionKey = isMasuk.value ? 'save_checklist_masuk' : 'save_checklist_keluar';
    processing.value = actionKey;
    router.post(route('services.checklists.store', props.service.id), {
        checklist_template_id: form.value.template_id,
        type: props.mode,
        checked_items: form.value.checked_items,
        notes: form.value.notes,
    }, {
        onSuccess: () => { show.value = false; },
        onFinish: () => { processing.value = null; },
    });
}

defineExpose({ open });
</script>
