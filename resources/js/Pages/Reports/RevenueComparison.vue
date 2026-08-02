<template>
    <AuthenticatedLayout>
    <div class="flex flex-col min-h-[calc(100vh-64px)] bg-zinc-50/50">
        <div class="px-6 sm:px-8 py-6 bg-white border-b border-zinc-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4 sticky top-0 z-20">
            <div class="flex items-center gap-4">
                <Link :href="route('reports.index')" class="w-10 h-10 bg-zinc-100 rounded-xl flex items-center justify-center text-zinc-600 hover:bg-zinc-200 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-zinc-900 tracking-tight">Perbandingan Revenue</h1>
                    <p class="text-sm text-zinc-500 font-medium mt-0.5">Pertumbuhan pendapatan Year-over-Year (YoY)</p>
                </div>
            </div>
            <div>
                <KSelect  v-model="year" @change="changeYear" class="w-full sm:w-32 rounded-xl border border-zinc-300 px-4 py-2 text-sm font-semibold bg-white text-zinc-900 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all cursor-pointer shadow-sm">
                    <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                </KSelect>
            </div>
        </div>

        <div class="flex-1 p-6 sm:p-8 max-w-[1400px] mx-auto w-full space-y-6">
            <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-zinc-200 bg-zinc-50 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-zinc-900">Tabel Pertumbuhan</h3>
                </div>
                <KTable :columns="columns" :rows="rows">
                    <template #cell-month="{row}">
                        <span class="font-bold text-zinc-900">{{ row.month }}</span>
                    </template>
                    <template #cell-current_display="{row}">
                        <span class="font-medium text-zinc-900">{{ row.current_display }}</span>
                    </template>
                    <template #cell-previous_display="{row}">
                        <span class="font-medium text-zinc-500">{{ row.previous_display }}</span>
                    </template>
                    <template #cell-growth_display="{row}">
                        <div class="flex justify-end">
                            <span class="inline-flex items-center gap-1 font-black px-2 py-1 rounded-md" 
                                :class="row.growth > 0 ? 'text-emerald-700 bg-emerald-50' : row.growth < 0 ? 'text-red-700 bg-red-50' : 'text-zinc-600 bg-zinc-100'">
                                <svg v-if="row.growth > 0" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7"/></svg>
                                <svg v-else-if="row.growth < 0" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/></svg>
                                <svg v-else class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 12h14"/></svg>
                                {{ row.growth >= 0 && row.growth > 0 ? '+' : '' }}{{ row.growth }}%
                            </span>
                        </div>
                    </template>
                    <template #empty>
                        <div class="py-12 text-center">
                            <div class="w-16 h-16 bg-zinc-100 text-zinc-400 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                            </div>
                            <p class="text-sm font-bold text-zinc-900">Tidak ada data</p>
                            <p class="text-xs text-zinc-500 mt-1">Belum ada data revenue untuk perbandingan tahun ini.</p>
                        </div>
                    </template>
                </KTable>
            </div>
        </div>
    </div>
    </AuthenticatedLayout>
</template>

<script setup>
import KSelect from '@/Components/KSelect.vue';

import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import KTable from '@/Components/KTable.vue';
import EmptyState from '@/Components/EmptyState.vue';

const props = defineProps({
    months: { type: Array, default: () => [] },
    year: { type: Number, default: new Date().getFullYear() },
    previousYear: { type: Number, default: new Date().getFullYear() - 1 },
});

const year = ref(props.year);
const years = computed(() => [props.year - 2, props.year - 1, props.year, props.year + 1]);
const formatNumber = (n) => new Intl.NumberFormat('id-ID').format(n || 0);

const columns = computed(() => [
    { key: 'month', label: 'Bulan' },
    { key: 'current_display', label: String(props.year), align: 'right' },
    { key: 'previous_display', label: String(props.previousYear), align: 'right' },
    { key: 'growth_display', label: 'Growth', align: 'right' },
]);

const rows = computed(() => props.months.map(m => ({
    ...m,
    current_display: `Rp ${formatNumber(m.current)}`,
    previous_display: `Rp ${formatNumber(m.previous)}`,
    growth_display: `${m.growth >= 0 ? '+' : ''}${m.growth}%`,
})));

const changeYear = () => router.get(route('reports.index'), { year: year.value }, { preserveState: true });
</script>
