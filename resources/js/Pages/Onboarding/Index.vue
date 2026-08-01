<template>
    <AuthenticatedLayout>
        <div class="max-w-3xl mx-auto py-10 px-4 sm:px-6 lg:px-8 space-y-8">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-2xl border border-indigo-100">🚀</div>
                    <div>
                        <h2 class="text-2xl font-black text-zinc-900 tracking-tight">Panduan Memulai (Onboarding)</h2>
                        <p class="text-sm font-medium text-zinc-500 mt-0.5">Selesaikan 4 langkah awal untuk menyiapkan toko Anda</p>
                    </div>
                </div>
                <Link :href="route('dashboard')" class="px-5 py-2.5 bg-white border border-zinc-200 rounded-xl text-zinc-700 text-sm font-semibold hover:bg-zinc-50 transition-colors shadow-sm">
                    Lewati ke Dashboard &rarr;
                </Link>
            </div>

            <!-- PROGRESS BAR -->
            <div class="p-6 rounded-3xl border border-zinc-200 shadow-sm bg-white overflow-hidden relative group">
                <div class="absolute right-0 top-0 w-32 h-32 bg-indigo-50 rounded-full blur-3xl -mr-10 -mt-10"></div>
                <div class="relative z-10 flex flex-col gap-4">
                    <div class="flex items-end justify-between">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 mb-1 block">Kemajuan Setup</span>
                            <span class="text-3xl font-black text-zinc-900">{{ progressPercent }}%</span>
                        </div>
                        <span class="text-sm font-semibold text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full">{{ completedCount }} dari {{ totalSteps }} Selesai</span>
                    </div>
                    <div class="w-full h-4 rounded-full bg-zinc-100 overflow-hidden ring-1 ring-inset ring-zinc-200">
                        <div class="h-full rounded-full bg-indigo-500 transition-all duration-700 ease-out"
                            :style="{ width: progressPercent + '%' }"></div>
                    </div>
                </div>
            </div>

            <!-- STEP CARDS -->
            <div class="space-y-4">
                <div v-for="(step, idx) in steps" :key="step.id"
                    class="p-6 rounded-3xl border transition-all duration-300 flex items-center justify-between gap-6"
                    :class="step.done ? 'bg-emerald-50/30 border-emerald-200 hover:border-emerald-300' : 'bg-white border-zinc-200 hover:border-indigo-300 hover:shadow-md'">
                    <div class="flex items-center gap-5">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-bold text-lg flex-shrink-0 transition-colors"
                            :class="step.done 
                                ? 'bg-emerald-100 text-emerald-600' 
                                : 'bg-indigo-50 text-indigo-600'">
                            <span v-if="step.done">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <span v-else>{{ idx + 1 }}</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-zinc-900">{{ step.title }}</h3>
                            <p class="text-sm text-zinc-500 mt-1 font-medium">{{ step.description }}</p>
                        </div>
                    </div>
                    <div>
                        <Link :href="step.url"
                            class="px-5 py-2.5 rounded-xl text-sm font-bold transition-all inline-flex items-center gap-2 shadow-sm"
                            :class="step.done ? 'bg-white border border-zinc-200 text-zinc-700 hover:bg-zinc-50' : 'bg-indigo-600 text-white hover:bg-indigo-700 hover:-translate-y-0.5 hover:shadow-md'">
                            {{ step.done ? 'Ubah' : 'Mulai' }}
                            <svg v-if="!step.done" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    steps: { type: Array, default: () => [] },
    completedCount: { type: Number, default: 0 },
    totalSteps: { type: Number, default: 4 },
    progressPercent: { type: Number, default: 0 },
    isNew: { type: Boolean, default: true },
});
</script>
