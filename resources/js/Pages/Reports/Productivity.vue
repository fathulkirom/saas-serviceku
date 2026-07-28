<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold">Produktivitas Teknisi</h2>
                <select v-model="period" @change="changePeriod" class="rounded-lg border text-xs px-3 py-2"><option value="month">Bulan Ini</option><option value="week">Minggu Ini</option><option value="year">Tahun Ini</option></select>
            </div>
        </template>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="t in technicians" :key="t.name" class="bg-white rounded-xl border shadow-sm p-5" style="border-color: var(--border-color);">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-sm font-bold">{{ t.name }}</p>
                        <p class="text-xs text-dark-400">{{ t.role }} • {{ t.level }}</p>
                    </div>
                    <span class="px-2 py-0.5 text-xs rounded-full font-bold" :class="t.level === 'senior' ? 'bg-green-100 text-green-700' : t.level === 'junior' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700'">{{ t.score }} pts</span>
                </div>
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between"><span class="text-dark-400">Selesai</span><span class="font-semibold">{{ t.completed }} / {{ t.total }}</span></div>
                    <div class="flex justify-between"><span class="text-dark-400">Rate</span><span class="font-semibold">{{ t.completion_rate }}%</span></div>
                    <div class="flex justify-between"><span class="text-dark-400">Rata-rata</span><span class="font-semibold">{{ t.avg_days }} hari</span></div>
                    <div class="flex justify-between"><span class="text-dark-400">Komisi</span><span class="font-semibold text-green-600">Rp {{ formatNumber(t.commission) }}</span></div>
                </div>
            </div>
            <div v-if="!technicians.length" class="col-span-full text-center py-12 text-sm text-dark-400">Belum ada data</div>
        </div>
    </AuthenticatedLayout>
</template>
<script setup>
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
const props = defineProps({ technicians: { type: Array, default: () => [] }, period: { type: String, default: 'month' } });
const period = ref(props.period);
const formatNumber = (n) => new Intl.NumberFormat('id-ID').format(n || 0);
const changePeriod = () => router.get(route('reports.index'), { period: period.value }, { preserveState: true });
</script>
