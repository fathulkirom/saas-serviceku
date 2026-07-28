<template>
    <AuthenticatedLayout>
        <PageHeader title="Perbandingan Revenue">
            <select v-model="year" @change="changeYear" class="rounded-lg border text-xs px-3 py-2 focus:ring-2 focus:outline-none border-dark-200 text-dark-700 focus:border-blue-500 focus:ring-blue-200 transition-all">
                <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
            </select>
        </PageHeader>

        <KTable :columns="columns" :rows="rows">
            <template #cell-growth_display="{row}">
                <span :class="row.growth >= 0 ? 'text-green-600' : 'text-red-600'" class="font-bold">
                    {{ row.growth >= 0 ? '+' : '' }}{{ row.growth }}%
                </span>
            </template>
            <template #empty>
                <EmptyState icon="chart-bar" title="Tidak ada data" description="Belum ada data revenue untuk tahun ini" />
            </template>
        </KTable>
    </AuthenticatedLayout>
</template>

<script setup>
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
