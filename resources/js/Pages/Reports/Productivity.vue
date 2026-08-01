<template>
    <AuthenticatedLayout>
    <div class="flex flex-col min-h-[calc(100vh-64px)] bg-zinc-50/50">
        <div class="px-6 sm:px-8 py-6 bg-white border-b border-zinc-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4 sticky top-0 z-20">
            <div class="flex items-center gap-4">
                <Link :href="route('reports.index')" class="w-10 h-10 bg-zinc-100 rounded-xl flex items-center justify-center text-zinc-600 hover:bg-zinc-200 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-zinc-900 tracking-tight">Produktivitas Teknisi</h1>
                    <p class="text-sm text-zinc-500 font-medium mt-0.5">Analisis performa, kecepatan, dan penyelesaian tugas</p>
                </div>
            </div>
            <div>
                <select v-model="period" @change="changePeriod" class="w-full sm:w-48 rounded-xl border border-zinc-300 px-4 py-2 text-sm font-semibold bg-white text-zinc-900 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all cursor-pointer shadow-sm">
                    <option value="week">Minggu Ini</option>
                    <option value="month">Bulan Ini</option>
                    <option value="year">Tahun Ini</option>
                </select>
            </div>
        </div>

        <div class="flex-1 p-6 sm:p-8 max-w-[1400px] mx-auto w-full space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="t in technicians" :key="t.name" class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden flex flex-col group hover:shadow-md transition-shadow">
                    <div class="p-5 border-b border-zinc-100 bg-zinc-50/50 flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-black text-xl shadow-inner">
                                {{ t.name[0].toUpperCase() }}
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-zinc-900 leading-tight">{{ t.name }}</h3>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-xs font-medium text-zinc-500">{{ t.role }}</span>
                                    <span class="w-1 h-1 rounded-full bg-zinc-300"></span>
                                    <span class="text-xs font-bold capitalize" :class="t.level === 'senior' ? 'text-emerald-600' : t.level === 'junior' ? 'text-blue-600' : 'text-zinc-600'">{{ t.level }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="px-3 py-1.5 rounded-lg font-black text-sm border flex flex-col items-center justify-center" 
                            :class="t.score >= 80 ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : t.score >= 60 ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-amber-50 text-amber-700 border-amber-200'">
                            <span>{{ t.score }}</span>
                            <span class="text-[10px] uppercase font-bold tracking-wider opacity-70 leading-none">PTS</span>
                        </div>
                    </div>
                    
                    <div class="p-5 space-y-4 flex-1">
                        <!-- Progress Bar for Completion Rate -->
                        <div>
                            <div class="flex justify-between items-end mb-2">
                                <span class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Tingkat Penyelesaian</span>
                                <span class="text-sm font-black text-zinc-900">{{ t.completion_rate }}%</span>
                            </div>
                            <div class="w-full h-2.5 bg-zinc-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-1000" 
                                    :class="t.completion_rate >= 80 ? 'bg-emerald-500' : t.completion_rate >= 50 ? 'bg-blue-500' : 'bg-amber-500'" 
                                    :style="`width: ${t.completion_rate}%`"></div>
                            </div>
                            <p class="text-xs font-medium text-zinc-500 mt-2 text-right">{{ t.completed }} dari {{ t.total }} servis selesai</p>
                        </div>

                        <div class="grid grid-cols-2 gap-3 pt-4 border-t border-zinc-100">
                            <div class="bg-zinc-50 p-3 rounded-xl border border-zinc-100">
                                <p class="text-xs font-bold text-zinc-500 mb-1">Rata-rata Waktu</p>
                                <p class="text-base font-black text-zinc-900">{{ t.avg_days }} <span class="text-xs font-medium text-zinc-500">hari</span></p>
                            </div>
                            <div class="bg-emerald-50 p-3 rounded-xl border border-emerald-100">
                                <p class="text-xs font-bold text-emerald-600/70 mb-1">Total Komisi</p>
                                <p class="text-base font-black text-emerald-700">Rp {{ formatNumber(t.commission) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div v-if="!technicians.length" class="bg-white rounded-2xl border border-zinc-200 p-12 text-center shadow-sm">
                <div class="w-16 h-16 bg-zinc-100 text-zinc-400 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <p class="text-sm font-bold text-zinc-900">Belum ada data teknisi</p>
                <p class="text-xs text-zinc-500 mt-1">Data produktivitas belum tersedia untuk periode ini.</p>
            </div>
        </div>
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
