<template>
    <AuthenticatedLayout>
        <div class="flex flex-col gap-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-zinc-900">Dashboard Teknisi</h2>
                    <p class="text-sm text-zinc-500 mt-1">Work order aktif Anda.</p>
                </div>
            </div>

            <!-- Counts -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-5 text-center">
                    <p class="text-3xl font-black text-indigo-600">{{ waiting?.length ?? 0 }}</p>
                    <p class="text-xs font-semibold text-zinc-500 mt-1">Menunggu</p>
                </div>
                <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-5 text-center">
                    <p class="text-3xl font-black text-blue-600">{{ inProgress?.length ?? 0 }}</p>
                    <p class="text-xs font-semibold text-zinc-500 mt-1">Dikerjakan</p>
                </div>
                <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-5 text-center">
                    <p class="text-3xl font-black text-purple-600">{{ waitingPart?.length ?? 0 }}</p>
                    <p class="text-xs font-semibold text-zinc-500 mt-1">Tunggu Part</p>
                </div>
                <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-5 text-center">
                    <p class="text-3xl font-black text-emerald-600">{{ qc?.length ?? 0 }}</p>
                    <p class="text-xs font-semibold text-zinc-500 mt-1">QC / Selesai</p>
                </div>
            </div>

            <!-- Work Orders -->
            <WorkOrderSection title="Menunggu Dikerjakan" :orders="waiting" status-key="assigned" />
            <WorkOrderSection title="Sedang Dikerjakan" :orders="inProgress" status-key="in_progress" />
            <WorkOrderSection title="Menunggu Part" :orders="waitingPart" status-key="waiting_part" />
            <WorkOrderSection title="QC / Siap Selesai" :orders="qc" status-key="qc" />
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    workOrders: { type: Object, default: () => ({}) },
    waiting: { type: Array, default: () => [] },
    inProgress: { type: Array, default: () => [] },
    waitingPart: { type: Array, default: () => [] },
    qc: { type: Array, default: () => [] },
});
</script>
