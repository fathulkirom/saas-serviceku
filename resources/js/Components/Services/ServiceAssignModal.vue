<template>
    <KDialog :model-value="show" @update:model-value="show = $event">
        <h3 class="text-base font-bold mb-4 text-zinc-900">Assign Teknisi</h3>
        <KSelect v-model="assignTechnicianId" size="lg" extra-class="mb-4">
            <option value="" disabled>-- Pilih Teknisi --</option>
            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }} ({{ u.role }})</option>
        </KSelect>
        <div class="flex gap-2">
            <KButton variant="modal-secondary" @click="show = false">Batal</KButton>
            <KButton variant="modal-primary-indigo" :shadow="!!assignTechnicianId" :disabled="!assignTechnicianId || processing === 'assign'" @click="executeAssign">{{ processing === 'assign' ? 'Menyimpan...' : 'Simpan' }}</KButton>
        </div>
    </KDialog>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import KDialog from '@/Components/KDialog.vue';
import KButton from '@/Components/KButton.vue';
import KSelect from '@/Components/KSelect.vue';

const props = defineProps({
    service: { type: Object, default: () => ({}) },
    users: { type: Array, default: () => [] },
});

const show = ref(false);
const assignTechnicianId = ref('');
const processing = ref(null);

function open() {
    assignTechnicianId.value = '';
    show.value = true;
}

function executeAssign() {
    if (!assignTechnicianId.value) return;
    processing.value = 'assign';
    router.post(route('services.assign-technician', props.service.id), {
        technician_id: assignTechnicianId.value,
    }, {
        onSuccess: () => { show.value = false; },
        onFinish: () => { processing.value = null; },
    });
}

defineExpose({ open });
</script>
