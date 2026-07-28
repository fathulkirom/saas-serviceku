<template>
    <AuthenticatedLayout>
        <template #header><h2 class="text-xl font-bold">Analisis Pelanggan</h2></template>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl border shadow-sm p-5" style="border-color: var(--border-color);">
                <p class="text-xs font-semibold text-dark-400 uppercase">Total Pelanggan</p>
                <p class="text-2xl font-bold mt-1">{{ totalCustomers }}</p>
            </div>
            <div class="bg-white rounded-xl border shadow-sm p-5" style="border-color: var(--border-color);">
                <p class="text-xs font-semibold text-dark-400 uppercase">Pelanggan Aktif (3 bln)</p>
                <p class="text-2xl font-bold mt-1">{{ activeCustomers }}</p>
            </div>
            <div class="bg-white rounded-xl border shadow-sm p-5" style="border-color: var(--border-color);">
                <p class="text-xs font-semibold text-dark-400 uppercase">Retention Rate</p>
                <p class="text-2xl font-bold mt-1" :class="retentionRate > 50 ? 'text-green-600' : 'text-yellow-600'">{{ retentionRate }}%</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border shadow-sm p-5" style="border-color: var(--border-color);">
            <h3 class="text-sm font-bold mb-4">Jam Sibuk (Peak Hours)</h3>
            <div v-if="peakHours.length" class="space-y-2">
                <div v-for="ph in peakHours" :key="ph.hour" class="flex items-center gap-3">
                    <span class="text-sm font-semibold w-16">{{ ph.hour }}:00</span>
                    <div class="flex-1 bg-gray-100 rounded-full h-4"><div class="h-4 rounded-full" :style="{ width: ph.total * 10 + '%', background: 'var(--accent-primary)' }"></div></div>
                    <span class="text-xs text-dark-400 w-12 text-right">{{ ph.total }} servis</span>
                </div>
            </div>
            <p v-else class="text-sm text-dark-400">Belum ada data</p>
        </div>
    </AuthenticatedLayout>
</template>
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
defineProps({ totalCustomers: { type: Number, default: 0 }, activeCustomers: { type: Number, default: 0 }, retentionRate: { type: Number, default: 0 }, peakHours: { type: Array, default: () => [] } });
</script>
