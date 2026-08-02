<template>
    <KDialog :model-value="show" @update:model-value="show = $event">
        <h3 class="text-base font-bold mb-2 text-zinc-900">Kirim ke Partner</h3>
        <p class="text-xs mb-3 text-zinc-500">Servis #{{ service.id }} akan dikerjakan oleh partner eksternal.</p>
        <KTextarea v-model="partnerNote" rows="3" placeholder="Catatan untuk partner (opsional)..." extra-class="mb-4" />
        <div class="flex gap-2">
            <KButton variant="modal-secondary" @click="show = false">Batal</KButton>
            <KButton variant="modal-primary-indigo" shadow :disabled="processing === 'partner'" @click="executePartner">{{ processing === 'partner' ? 'Mengirim...' : 'Kirim' }}</KButton>
        </div>
    </KDialog>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import KDialog from '@/Components/KDialog.vue';
import KButton from '@/Components/KButton.vue';
import KTextarea from '@/Components/KTextarea.vue';

const props = defineProps({
    service: { type: Object, default: () => ({}) },
});

const show = ref(false);
const partnerNote = ref('');
const processing = ref(null);

function open() {
    partnerNote.value = '';
    show.value = true;
}

function executePartner() {
    processing.value = 'partner';
    router.post(route('services.partner', props.service.id), {
        partner_note: partnerNote.value,
    }, {
        onSuccess: () => { show.value = false; },
        onFinish: () => { processing.value = null; },
    });
}

defineExpose({ open });
</script>
