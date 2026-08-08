<template>
    <AuthenticatedLayout>
        <div class="max-w-3xl mx-auto py-10 px-4 sm:px-6 lg:px-8 space-y-8">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 sk-bg-primary-soft rounded-2xl flex items-center justify-center text-2xl border sk-border-primary">🚀</div>
                    <div>
                        <h2 class="text-2xl font-black sk-text-primary tracking-tight">Panduan Memulai (Onboarding)</h2>
                        <p class="text-sm font-medium sk-text-muted mt-0.5">Selesaikan 4 langkah awal untuk menyiapkan toko Anda</p>
                    </div>
                </div>
                <Link :href="route('dashboard')" class="px-5 py-2.5 sk-bg-card border sk-border rounded-xl sk-text-primary text-sm font-semibold hover:sk-bg-hover transition-colors shadow-sm">
                    Lewati ke Dashboard &rarr;
                </Link>
            </div>

            <!-- PROGRESS BAR -->
            <div class="p-6 rounded-3xl border sk-border shadow-sm sk-bg-card overflow-hidden relative group">
                <div class="absolute right-0 top-0 w-32 h-32 sk-bg-primary-soft rounded-full blur-3xl -mr-10 -mt-10"></div>
                <div class="relative z-10 flex flex-col gap-4">
                    <div class="flex items-end justify-between">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider sk-text-muted mb-1 block">Kemajuan Setup</span>
                            <span class="text-3xl font-black sk-text-primary">{{ progressPercent }}%</span>
                        </div>
                        <span class="text-sm font-semibold sk-text-primary-brand sk-bg-primary-soft px-3 py-1 rounded-full">{{ completedCount }} dari {{ totalSteps }} Selesai</span>
                    </div>
                    <div class="w-full h-4 rounded-full sk-bg-hover overflow-hidden ring-1 ring-inset ring-zinc-200">
                        <div class="h-full rounded-full bg-indigo-500 transition-all duration-700 ease-out"
                            :style="{ width: progressPercent + '%' }"></div>
                    </div>
                </div>
            </div>

            <!-- STEP CARDS -->
            <div class="space-y-4">
                <div v-for="(step, idx) in steps" :key="step.id"
                    class="p-6 rounded-3xl border transition-all duration-300 flex items-center justify-between gap-6"
                    :class="step.done ? 'sk-bg-success-soft/30 sk-border-primary hover:border-emerald-300' : 'sk-bg-card sk-border hover:border-indigo-300 hover:shadow-md'">
                    <div class="flex items-center gap-5">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-bold text-lg flex-shrink-0 transition-colors"
                            :class="step.done 
                                ? 'sk-bg-success-soft sk-text-success' 
                                : 'sk-bg-primary-soft sk-text-primary-brand'">
                            <span v-if="step.done">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <span v-else>{{ idx + 1 }}</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold sk-text-primary">{{ step.title }}</h3>
                            <p class="text-sm sk-text-muted mt-1 font-medium">{{ step.description }}</p>
                        </div>
                    </div>
                    <div>
                        <Link :href="step.url"
                            class="px-5 py-2.5 rounded-xl text-sm font-bold transition-all inline-flex items-center gap-2 shadow-sm"
                            :class="step.done ? 'sk-bg-card border sk-border sk-text-primary hover:sk-bg-hover' : 'sk-bg-primary text-white hover:sk-bg-primary hover:-translate-y-0.5 hover:shadow-md'">
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
