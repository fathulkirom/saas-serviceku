<template>
    <KDialog :model-value="show" @update:model-value="show = $event">
        <h3 class="text-base font-bold mb-2 text-zinc-900">Batalkan Servis?</h3>
        <p class="text-sm mb-4 text-zinc-500">Servis #{{ service.id }} akan dibatalkan. Tindakan ini tidak dapat dibatalkan.</p>
        <div class="flex gap-2">
            <KButton variant="modal-secondary" @click="show = false">Tidak</KButton>
            <KButton variant="modal-primary-danger" shadow :disabled="processing === 'cancel'" @click="executeCancel">{{ processing === 'cancel' ? 'Memproses...' : 'Ya, Batalkan' }}</KButton>
        </div>
    </KDialog>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import KDialog from '@/Components/KDialog.vue';
import KButton from '@/Components/KButton.vue';

const props = defineProps({
    service: { type: Object, default: () => ({}) },
});

const show = ref(false);
const processing = ref(null);

function open() {
    show.value = true;
}

function executeCancel() {
    processing.value = 'cancel';
    router.post(route('services.cancel', props.service.id), {}, {
        onSuccess: () => { show.value = false; },
        onFinish: () => { processing.value = null; },
    });
}

defineExpose({ open });
</script>
