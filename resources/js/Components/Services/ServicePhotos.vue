<template>
    <div class="space-y-5">
        <!-- PHOTOS -->
        <div v-if="service.photos?.length" class="rounded-xl border p-5" style="border-color: var(--border-color); background: var(--bg-card);">
            <h3 class="text-sm font-bold mb-4 text-zinc-900">📸 Foto Perangkat</h3>
            <div class="grid grid-cols-4 gap-3">
                <div v-for="photo in service.photos" :key="photo.id" class="relative group cursor-pointer" @click="previewPhoto = photo.photo_url">
                    <img :src="photo.photo_url" class="w-full h-24 object-cover rounded-lg border border-zinc-200" />
                    <div class="absolute inset-0 rounded-lg bg-black/0 group-hover:bg-black/10 transition-all"></div>
                </div>
            </div>
        </div>

        <!-- UPLOAD PHOTO -->
        <div v-if="driveConnected && isActive" class="rounded-xl border p-5" style="border-color: var(--border-color); background: var(--bg-card);">
            <h3 class="text-sm font-bold mb-4 text-zinc-900">📤 Upload Foto Tambahan</h3>
            <form @submit.prevent="uploadPhotos">
                <KInput  type="file" @change="onAdditionalPhotos" accept="image/*" multiple
                    class="block w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                <div v-if="additionalPreviews.length" class="mt-3 flex flex-wrap gap-2">
                    <div v-for="(preview, idx) in additionalPreviews" :key="idx" class="relative">
                        <img :src="preview" class="h-16 w-16 object-cover rounded-lg border border-zinc-200" />
                    </div>
                </div>
                <KButton v-if="additionalFiles.length" type="submit" variant="action-indigo" size="md" extra-class="mt-3" :disabled="processing === 'upload_photos'">
                    {{ processing === 'upload_photos' ? 'Mengupload...' : 'Upload' }}
                </KButton>
            </form>
        </div>

        <!-- LIGHTBOX -->
        <Teleport to="body">
            <div v-if="previewPhoto" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click="previewPhoto = null">
                <img :src="previewPhoto" class="max-h-screen max-w-full object-contain rounded-lg" />
            </div>
        </Teleport>
    </div>
</template>

<script setup>
import KInput from '@/Components/KInput.vue';

import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import KButton from '@/Components/KButton.vue';

const props = defineProps({
    service: { type: Object, default: () => ({}) },
    driveConnected: { type: Boolean, default: false },
});

const isActive = computed(() => !['selesai', 'cancel', 'void', 'close'].includes(props.service.status));

const previewPhoto = ref(null);
const additionalFiles = ref([]);
const additionalPreviews = ref([]);
const processing = ref(null);

const onAdditionalPhotos = (e) => {
    additionalFiles.value = Array.from(e.target.files);
    additionalPreviews.value = additionalFiles.value.map(f => URL.createObjectURL(f));
};

const uploadPhotos = () => {
    if (!additionalFiles.value.length) return;
    processing.value = 'upload_photos';
    const data = new FormData();
    additionalFiles.value.forEach((file, i) => data.append(`photos[${i}]`, file));
    router.post(route('services.photos.store', props.service.id), data, {
        onSuccess: () => {
            additionalFiles.value = [];
            additionalPreviews.value = [];
        },
        onFinish: () => { processing.value = null; },
    });
};
</script>
