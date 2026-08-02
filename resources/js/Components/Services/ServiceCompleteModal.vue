<template>
    <KDialog :model-value="show" @update:model-value="show = $event" max-width="lg" scrollable>
        <h3 class="text-base font-bold mb-4 text-zinc-900">✅ Complete Servis</h3>
        <div class="space-y-4">
            <div>
                <label class="block text-xs font-semibold mb-1 text-zinc-500">Checklist Keluar (opsional)</label>
                <KSelect v-model="form.checklist_template_id">
                    <option value="">-- Tanpa Checklist --</option>
                    <option v-for="tpl in templatesKeluar" :key="tpl.id" :value="tpl.id">{{ tpl.name }}</option>
                </KSelect>
                <div v-if="selectedChecklistItems.length" class="mt-2 space-y-1">
                    <label v-for="item in selectedChecklistItems" :key="item.id"
                        class="flex items-center gap-2 text-sm cursor-pointer py-0.5 text-zinc-600">
                        <KCheckbox :value="item.id" v-model="form.checked_items"
                            class="rounded" style="accent-color: var(--primary);" />
                        {{ item.item_name }}
                    </label>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1 text-zinc-500">Biaya Jasa</label>
                <KInput v-model.number="form.service_charge" type="number" min="0" />
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1 text-zinc-500">Tambah Sparepart</label>
                <div class="space-y-2">
                    <div v-for="(sp, idx) in form.spareparts" :key="idx" class="flex gap-2 items-center">
                        <KSelect v-model="sp.product_id" size="sm" width-class="flex-1">
                            <option value="">-- Pilih --</option>
                            <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }} (stok: {{ p.stock_quantity }})</option>
                        </KSelect>
                        <KInput v-model.number="sp.quantity" type="number" min="1" placeholder="Qty" size="sm" width-class="w-16" extra-class="text-center" />
                        <KButton variant="text-danger" @click="form.spareparts.splice(idx, 1)">✕</KButton>
                    </div>
                    <KButton variant="text-link" @click="form.spareparts.push({ product_id: '', quantity: 1 })">+ Tambah Sparepart</KButton>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1 text-zinc-500">Catatan (opsional)</label>
                <KTextarea v-model="form.condition_note" rows="2" />
            </div>
        </div>
        <div class="flex gap-2 mt-5">
            <KButton variant="modal-secondary" @click="show = false">Batal</KButton>
            <KButton variant="modal-primary-success" shadow :disabled="processing === 'complete'" @click="executeComplete">{{ processing === 'complete' ? 'Memproses...' : 'Simpan & Selesaikan' }}</KButton>
        </div>
    </KDialog>
</template>

<script setup>
import KCheckbox from '@/Components/KCheckbox.vue';

import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import KDialog from '@/Components/KDialog.vue';
import KButton from '@/Components/KButton.vue';
import KInput from '@/Components/KInput.vue';
import KSelect from '@/Components/KSelect.vue';
import KTextarea from '@/Components/KTextarea.vue';

const props = defineProps({
    service: { type: Object, default: () => ({}) },
    products: { type: Array, default: () => [] },
    templatesKeluar: { type: Array, default: () => [] },
});

const show = ref(false);
const processing = ref(null);
const form = ref({
    checklist_template_id: '',
    checked_items: [],
    service_charge: 0,
    spareparts: [],
    condition_note: '',
});

const selectedChecklistItems = computed(() => {
    if (!form.value.checklist_template_id) return [];
    const tpl = props.templatesKeluar.find(t => t.id == form.value.checklist_template_id);
    return tpl?.items || [];
});

function open() {
    form.value = {
        checklist_template_id: '',
        checked_items: [],
        service_charge: Number(props.service.service_charge) || 0,
        spareparts: [],
        condition_note: '',
    };
    show.value = true;
}

function executeComplete() {
    processing.value = 'complete';
    router.post(route('services.complete', props.service.id), form.value, {
        onSuccess: () => { show.value = false; },
        onFinish: () => { processing.value = null; },
    });
}

defineExpose({ open });
</script>
