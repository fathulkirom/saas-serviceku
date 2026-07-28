<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold" style="color: var(--text-primary);">🚀 Panduan Memulai (Onboarding)</h2>
                    <p class="text-xs mt-0.5" style="color: var(--text-muted);">Selesaikan 4 langkah awal untuk menyiapkan toko Anda</p>
                </div>
                <Link :href="route('dashboard')" class="btn-secondary text-xs">
                    Lewati ke Dashboard →
                </Link>
            </div>
        </template>

        <div class="max-w-3xl mx-auto space-y-6">
            <!-- PROGRESS BAR -->
            <div class="p-5 rounded-2xl border shadow-sm" style="background: var(--bg-card); borderColor: var(--border-color);">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-bold" style="color: var(--text-primary);">Kemajuan Setup: {{ progressPercent }}%</span>
                    <span class="text-xs font-semibold" style="color: var(--text-muted);">{{ completedCount }} dari {{ totalSteps }} Langkah Selesai</span>
                </div>
                <div class="w-full h-3 rounded-full overflow-hidden" style="background: var(--bg-hover);">
                    <div class="h-full rounded-full transition-all duration-500"
                        :style="{ width: progressPercent + '%', background: 'var(--accent-primary)' }"></div>
                </div>
            </div>

            <!-- STEP CARDS -->
            <div class="space-y-4">
                <div v-for="(step, idx) in steps" :key="step.id"
                    class="p-5 rounded-2xl border transition-all flex items-center justify-between gap-4"
                    :style="{ background: 'var(--bg-card)', borderColor: step.done ? 'rgba(16,185,129,0.3)' : 'var(--border-color)' }">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm flex-shrink-0"
                            :style="step.done 
                                ? { background: 'rgba(16,185,129,0.15)', color: '#10b981' } 
                                : { background: 'var(--accent-light)', color: 'var(--accent-primary)' }">
                            <span v-if="step.done">✓</span>
                            <span v-else>{{ idx + 1 }}</span>
                        </div>
                        <div>
                            <h3 class="text-base font-bold" :style="{ color: 'var(--text-primary)' }">{{ step.title }}</h3>
                            <p class="text-xs mt-0.5" :style="{ color: 'var(--text-muted)' }">{{ step.description }}</p>
                        </div>
                    </div>
                    <div>
                        <Link :href="step.url"
                            class="px-4 py-2 rounded-xl text-xs font-bold transition-all inline-block"
                            :class="step.done ? 'btn-secondary' : 'btn-primary'">
                            {{ step.done ? 'Ubah' : 'Mulai →' }}
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
