<template>
    <div class="min-h-screen bg-gray-50" :style="{ '--theme-color': primaryColor }">
        <div class="max-w-2xl mx-auto px-4 py-8">
            <!-- Header Toko -->
            <div class="text-center mb-8">
                <div v-if="storeLogo" class="mb-3">
                    <img :src="storeLogo" class="h-16 mx-auto object-contain" />
                </div>
                <h1 class="text-2xl font-bold text-gray-900">{{ storeName }}</h1>
                <p class="text-sm text-gray-500">Cek Status Servis</p>
            </div>

            <!-- Tracking Form -->
            <div class="bg-white rounded-2xl shadow-sm border p-4 mb-6">
                <form @submit.prevent="searchTracking" class="flex gap-2">
                    <input type="text" v-model="searchCode" placeholder="Masukkan kode tracking..."
                        class="flex-1 rounded-xl border text-sm px-4 py-2.5 focus:ring-2 focus:outline-none border-gray-200 focus:border-indigo-500 focus:ring-indigo-200" />
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white" :style="{ background: 'var(--theme-color)' }">
                        Cari
                    </button>
                </form>
            </div>

            <!-- Error -->
            <div v-if="error" class="bg-red-50 border border-red-200 rounded-xl p-6 text-center">
                <svg class="w-12 h-12 mx-auto text-red-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-red-700 font-medium">{{ error }}</p>
                <p class="text-red-500 text-sm mt-1">Kode: {{ trackingCode }}</p>
            </div>

            <!-- Service Info -->
            <div v-if="service" class="space-y-6">
                <!-- Status Card -->
                <div class="bg-white rounded-2xl shadow-sm border p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Status Servis</h2>
                        <span :class="statusClass" class="px-3 py-1 text-xs font-semibold rounded-full">
                            {{ statusLabel }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500">No. Servis</p>
                            <p class="font-medium text-gray-900">#{{ service.id }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Kode Tracking</p>
                            <p class="font-mono font-medium text-gray-900">{{ service.tracking_code }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Tanggal Masuk</p>
                            <p class="font-medium text-gray-900">{{ formatDate(service.created_at) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Estimasi Biaya</p>
                            <p class="font-medium text-gray-900">Rp {{ formatNumber(service.total_cost || service.service_charge) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Customer & Device -->
                <div class="bg-white rounded-2xl shadow-sm border p-6">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Data Servis</h3>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-gray-500">Nama Pelanggan</p>
                            <p class="font-medium text-gray-900">{{ maskName(service.customer?.name) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Teknisi</p>
                            <p class="font-medium text-gray-900">{{ service.technician?.name || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Perangkat</p>
                            <p class="font-medium text-gray-900">{{ service.kategori_perangkat?.name || '-' }} {{ service.merek?.name || '' }} {{ service.tipe_unit || '' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">IMEI/SN</p>
                            <p class="font-medium text-gray-900">{{ service.imei_sn || '-' }}</p>
                        </div>
                    </div>
                    <div v-if="service.problem_description" class="mt-3 pt-3 border-t">
                        <p class="text-xs text-gray-500 mb-1">Deskripsi Masalah:</p>
                        <p class="text-sm text-gray-700">{{ service.problem_description }}</p>
                    </div>
                </div>

                <!-- Photos -->
                <div v-if="service.photos?.length" class="bg-white rounded-2xl shadow-sm border p-6">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Foto Perangkat</h3>
                    <div class="grid grid-cols-3 gap-2">
                        <img v-for="photo in service.photos" :key="photo.id" :src="photo.photo_url"
                            class="w-full h-24 object-cover rounded-lg cursor-pointer border"
                            @click="previewPhoto = photo.photo_url" />
                    </div>
                    <Teleport to="body">
                        <div v-if="previewPhoto" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click="previewPhoto = null">
                            <img :src="previewPhoto" class="max-h-screen max-w-full object-contain rounded-lg" />
                        </div>
                    </Teleport>
                </div>

                <!-- Timeline -->
                <div class="bg-white rounded-2xl shadow-sm border p-6">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Timeline Servis</h3>
                    <div class="relative">
                        <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>
                        <div class="space-y-6 relative">
                            <div class="flex items-start gap-4 relative">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-100 flex items-center justify-center relative z-10 ring-4 ring-white">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Servis Diterima</p>
                                    <p class="text-xs text-gray-500">{{ formatDate(service.created_at) }}</p>
                                </div>
                            </div>

                            <div v-if="service.status !== 'menunggu_alokasi' && service.status !== 'cancel'" class="flex items-start gap-4 relative">
                                <div :class="progressLevel >= 2 ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-400'" class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center relative z-10 ring-4 ring-white">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Dikerjakan</p>
                                    <p class="text-xs text-gray-500">{{ service.technician?.name ? 'Oleh: ' + service.technician.name : 'Belum ditugaskan' }}</p>
                                </div>
                            </div>

                            <div v-if="service.status === 'indent'" class="flex items-start gap-4 relative">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-yellow-100 flex items-center justify-center relative z-10 ring-4 ring-white">
                                    <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Menunggu Sparepart</p>
                                    <p class="text-xs text-gray-500">Sedang menunggu ketersediaan sparepart</p>
                                </div>
                            </div>

                            <div v-if="progressLevel >= 4" class="flex items-start gap-4 relative">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-100 flex items-center justify-center relative z-10 ring-4 ring-white">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Selesai</p>
                                    <p class="text-xs text-gray-500">Servis selesai dikerjakan</p>
                                </div>
                            </div>

                            <div v-if="service.status === 'cancel' || service.status === 'void'" class="flex items-start gap-4 relative">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-red-100 flex items-center justify-center relative z-10 ring-4 ring-white">
                                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Dibatalkan</p>
                                    <p class="text-xs text-gray-500">Servis dibatalkan</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading -->
            <div v-if="!service && !error && trackingCode" class="text-center py-12">
                <div class="animate-spin w-8 h-8 border-2 border-gray-400 border-t-transparent rounded-full mx-auto mb-3"></div>
                <p class="text-gray-500">Mencari data servis...</p>
            </div>

            <div class="text-center text-xs text-gray-400 mt-8">
                <p>ServiceKU — Solusi Manajemen Servis Center</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    service: { type: Object, default: null },
    storeName: { type: String, default: 'ServiceKU' },
    storeLogo: { type: String, default: '' },
    primaryColor: { type: String, default: '#4F46E5' },
    trackingCode: { type: String, default: '' },
    error: { type: String, default: '' },
});

const searchCode = ref(props.trackingCode || '');
const previewPhoto = ref(null);

const statusPriority = {
    menunggu_alokasi: 0, diterima: 1, dikerjakan: 2,
    menunggu_konfirmasi_pelanggan: 2, menunggu_konfirmasi_internal: 2,
    indent: 3, onpartner: 2, siap_diambil: 4, selesai: 5,
    cancel: 6, void: 6, close: 6,
};

const progressLevel = computed(() => props.service ? (statusPriority[props.service.status] ?? 0) : 0);

const statusLabel = computed(() => {
    const labels = {
        menunggu_alokasi: 'Menunggu', diterima: 'Diterima Teknisi',
        dikerjakan: 'Dikerjakan', menunggu_konfirmasi_pelanggan: 'Menunggu Konfirmasi',
        menunggu_konfirmasi_internal: 'Menunggu Persetujuan', siap_diambil: 'Siap Diambil',
        indent: 'Menunggu Sparepart', onpartner: 'Dikerjakan Partner',
        selesai: 'Selesai', cancel: 'Dibatalkan', void: 'Void', close: 'Ditutup',
    };
    return props.service ? (labels[props.service.status] || props.service.status) : '';
});

const statusClass = computed(() => {
    const classes = {
        menunggu_alokasi: 'bg-yellow-100 text-yellow-800', diterima: 'bg-orange-100 text-orange-800',
        dikerjakan: 'bg-blue-100 text-blue-800',
        menunggu_konfirmasi_pelanggan: 'bg-pink-100 text-pink-800',
        menunggu_konfirmasi_internal: 'bg-pink-100 text-pink-800',
        siap_diambil: 'bg-green-100 text-green-800', indent: 'bg-purple-100 text-purple-800',
        onpartner: 'bg-purple-100 text-purple-800', selesai: 'bg-green-100 text-green-800',
        cancel: 'bg-red-100 text-red-800', void: 'bg-red-100 text-red-800', close: 'bg-gray-100 text-gray-800',
    };
    return props.service ? (classes[props.service.status] || 'bg-gray-100 text-gray-800') : '';
});

const searchTracking = () => {
    if (!searchCode.value.trim()) return;
    window.location.href = '/track/' + searchCode.value.trim();
};

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num || 0);
const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};
const maskName = (name) => {
    if (!name) return '-';
    if (name.length <= 2) return name;
    return name[0] + '** ' + name[name.length - 1];
};
</script>
