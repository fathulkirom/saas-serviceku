<template>
    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-zinc-900">QR Scanner</h2>
                <p class="text-sm text-zinc-500 mt-1">Pindai QR code pada perangkat atau nota pelanggan</p>
            </div>
        </template>
        <div class="max-w-xl mx-auto mt-6">
            <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-8 text-center">
                <div class="mb-6">
                    <video ref="video" class="w-full max-w-sm mx-auto rounded-2xl border border-zinc-200 bg-zinc-900 shadow-inner" style="min-height: 300px;"></video>
                </div>
                <p class="text-sm font-medium text-zinc-600 mb-6">Arahkan kamera ke QR code pada sticker device</p>
                <div class="flex gap-3 justify-center">
                    <button @click="startScan" class="px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition-colors shadow-sm focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                        Mulai Scan
                    </button>
                    <button @click="stopScan" class="px-5 py-2.5 rounded-xl text-sm font-bold text-zinc-700 bg-white border border-zinc-300 hover:bg-zinc-50 transition-colors shadow-sm">
                        Berhenti
                    </button>
                </div>
                <div v-if="result" class="mt-6 p-5 bg-emerald-50 border border-emerald-200 rounded-2xl flex flex-col items-center">
                    <p class="text-sm font-bold text-emerald-800 mb-2">Hasil Scan: <span class="font-mono bg-white px-2 py-1 rounded border border-emerald-100 ml-1">{{ result }}</span></p>
                    <a v-if="isServiceCode" :href="route('services.show', result)" class="mt-2 inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl transition-colors shadow-sm">
                        Buka Detail Servis
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
                <div v-if="error" class="mt-6 p-4 bg-red-50 border border-red-200 rounded-xl flex items-center gap-3 text-left">
                    <div class="shrink-0 w-8 h-8 rounded-full bg-red-100 flex items-center justify-center text-red-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <p class="text-sm font-medium text-red-800 flex-1">{{ error }}</p>
                </div>
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
        error.value = 'Tidak dapat mengakses kamera. Pastikan Anda memberikan izin kamera untuk aplikasi ini: ' + e.message;
    }
};

const stopScan = () => {
    if (scanInterval) { clearInterval(scanInterval); scanInterval = null; }
    if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }
};
</script>
