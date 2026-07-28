<template>
    <AuthenticatedLayout>
        <template #header><h2 class="text-xl font-bold">QR Scanner</h2></template>
        <div class="max-w-xl mx-auto">
            <div class="bg-white rounded-xl border shadow-sm p-6 text-center" style="border-color: var(--border-color);">
                <div class="mb-4">
                    <video ref="video" class="w-full max-w-sm mx-auto rounded-xl border bg-black" style="min-height: 300px;"></video>
                </div>
                <p class="text-sm text-dark-400 mb-4">Arahkan kamera ke QR code pada sticker device</p>
                <div class="flex gap-2 justify-center">
                    <button @click="startScan" class="px-4 py-2 rounded-lg text-xs font-bold text-white" style="background: var(--accent-primary);">Mulai Scan</button>
                    <button @click="stopScan" class="px-4 py-2 rounded-lg text-xs font-bold border" style="border-color: var(--border-color);">Berhenti</button>
                </div>
                <div v-if="result" class="mt-4 p-4 bg-green-50 border border-green-200 rounded-xl">
                    <p class="text-sm font-semibold text-green-800">Hasil: {{ result }}</p>
                    <a v-if="isServiceCode" :href="route('services.show', result)" class="mt-2 inline-block text-xs font-semibold" style="color: var(--accent-primary);">Lihat Service →</a>
                </div>
                <p v-if="error" class="mt-4 text-sm text-red-500">{{ error }}</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<script setup>
import { ref, computed } from 'vue';
import { route } from 'ziggy-js';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const video = ref(null);
const result = ref('');
const error = ref('');
let stream = null;
let scanInterval = null;

const isServiceCode = computed(() => /^\d+$/.test(result.value));

const startScan = async () => {
    error.value = '';
    result.value = '';
    try {
        stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
        if (video.value) video.value.srcObject = stream;
        const jsQR = (await import('jsqr')).default;
        scanInterval = setInterval(() => {
            if (!video.value) return;
            const canvas = document.createElement('canvas');
            canvas.width = video.value.videoWidth;
            canvas.height = video.value.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video.value, 0, 0);
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const code = jsQR(imageData.data, imageData.width, imageData.height);
            if (code) {
                result.value = code.data;
                stopScan();
            }
        }, 500);
    } catch (e) {
        error.value = 'Tidak bisa mengakses kamera: ' + e.message;
    }
};

const stopScan = () => {
    if (scanInterval) { clearInterval(scanInterval); scanInterval = null; }
    if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }
};
</script>
