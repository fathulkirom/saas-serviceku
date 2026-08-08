<template>
    <AuthenticatedLayout>
    <div class="flex flex-col min-h-[calc(100vh-64px)] sk-bg-hover">
        <div class="px-6 sm:px-8 py-6 sk-bg-card border-b sk-border flex flex-col sm:flex-row sm:items-center justify-between gap-4 sticky top-0 z-20">
            <div class="flex items-center gap-4">
                <Link :href="route('reports.index')" class="w-10 h-10 sk-bg-hover rounded-xl flex items-center justify-center sk-text-secondary hover:bg-zinc-200 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold sk-text-primary tracking-tight">Produktivitas Teknisi</h1>
                    <p class="text-sm sk-text-muted font-medium mt-0.5">Analisis performa, kecepatan, dan penyelesaian tugas</p>
                </div>
            </div>
            <div>
                <KSelect  v-model="period" @change="changePeriod" class="w-full sm:w-48 rounded-xl border sk-border px-4 py-2 text-sm font-semibold sk-bg-card sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all cursor-pointer shadow-sm">
                    <option value="week">Minggu Ini</option>
                    <option value="month">Bulan Ini</option>
                    <option value="year">Tahun Ini</option>
                </KSelect>
            </div>
        </div>

        <div class="flex-1 p-6 sm:p-8 max-w-[1400px] mx-auto w-full space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="t in technicians" :key="t.name" class="sk-bg-card rounded-2xl border sk-border shadow-sm overflow-hidden flex flex-col group hover:shadow-md transition-shadow">
                    <div class="p-5 border-b sk-border-light sk-bg-hover flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-full sk-bg-primary-soft sk-text-primary-brand flex items-center justify-center font-black text-xl shadow-inner">
                                {{ t.name[0].toUpperCase() }}
                            </div>
                            <div>
                                <h3 class="text-base font-bold sk-text-primary leading-tight">{{ t.name }}</h3>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-xs font-medium sk-text-muted">{{ t.role }}</span>
                                    <span class="w-1 h-1 rounded-full bg-zinc-300"></span>
                                    <span class="text-xs font-bold capitalize" :class="t.level === 'senior' ? 'sk-text-success' : t.level === 'junior' ? 'sk-text-info' : 'sk-text-secondary'">{{ t.level }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="px-3 py-1.5 rounded-lg font-black text-sm border flex flex-col items-center justify-center" 
                            :class="t.score >= 80 ? 'sk-bg-success-soft sk-text-success sk-border-primary' : t.score >= 60 ? 'sk-bg-info-soft sk-text-info border-blue-200' : 'sk-bg-warning-soft sk-text-warning sk-border-primary'">
                            <span>{{ t.score }}</span>
                            <span class="text-[10px] uppercase font-bold tracking-wider opacity-70 leading-none">PTS</span>
                        </div>
                    </div>
                    
                    <div class="p-5 space-y-4 flex-1">
                        <!-- Progress Bar for Completion Rate -->
                        <div>
                            <div class="flex justify-between items-end mb-2">
                                <span class="text-xs font-bold sk-text-muted uppercase tracking-wider">Tingkat Penyelesaian</span>
                                <span class="text-sm font-black sk-text-primary">{{ t.completion_rate }}%</span>
                            </div>
                            <div class="w-full h-2.5 sk-bg-hover rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-1000" 
                                    :class="t.completion_rate >= 80 ? 'bg-emerald-500' : t.completion_rate >= 50 ? 'bg-blue-500' : 'bg-amber-500'" 
                                    :style="`width: ${t.completion_rate}%`"></div>
                            </div>
                            <p class="text-xs font-medium sk-text-muted mt-2 text-right">{{ t.completed }} dari {{ t.total }} servis selesai</p>
                        </div>

                        <div class="grid grid-cols-2 gap-3 pt-4 border-t sk-border-light">
                            <div class="sk-bg-hover p-3 rounded-xl border sk-border-light">
                                <p class="text-xs font-bold sk-text-muted mb-1">Rata-rata Waktu</p>
                                <p class="text-base font-black sk-text-primary">{{ t.avg_days }} <span class="text-xs font-medium sk-text-muted">hari</span></p>
                            </div>
                            <div class="sk-bg-success-soft p-3 rounded-xl border border-emerald-100">
                                <p class="text-xs font-bold sk-text-success/70 mb-1">Total Komisi</p>
                                <p class="text-base font-black sk-text-success">Rp {{ formatNumber(t.commission) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div v-if="!technicians.length" class="sk-bg-card rounded-2xl border sk-border p-12 text-center shadow-sm">
                <div class="w-16 h-16 sk-bg-hover sk-text-muted rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <p class="text-sm font-bold sk-text-primary">Belum ada data teknisi</p>
                <p class="text-xs sk-text-muted mt-1">Data produktivitas belum tersedia untuk periode ini.</p>
            </div>
        </div>
    </div>
    </AuthenticatedLayout>
</template>
<script setup>
import KSelect from '@/Components/KSelect.vue';

import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
const props = defineProps({ technicians: { type: Array, default: () => [] }, period: { type: String, default: 'month' } });
const period = ref(props.period);
const formatNumber = (n) => new Intl.NumberFormat('id-ID').format(n || 0);
const changePeriod = () => router.get(route('reports.index'), { period: period.value }, { preserveState: true });
</script>
