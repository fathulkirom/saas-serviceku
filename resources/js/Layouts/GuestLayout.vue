<template>
    <div class="min-h-screen flex items-center justify-center relative overflow-hidden" style="background: var(--bg-primary);">
        <!-- Background Decor -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-80 h-80 rounded-full blur-3xl" style="background: var(--accent-light);"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 rounded-full blur-3xl" style="background: var(--accent-light); opacity: 0.5;"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full" style="background: radial-gradient(circle, var(--accent-glow) 0%, transparent 70%);"></div>
        </div>

        <div class="w-full max-w-md relative z-10 px-4">
            <!-- Logo -->
            <div class="text-center mb-6 md:mb-10 animate-fade-in flex flex-col items-center">
                <img :src="logoSrc" alt="ServiceKU" class="h-12 md:h-16 w-auto mb-2 md:mb-3" />
                <h1 class="text-2xl md:text-3xl font-bold mb-2 md:mb-3" style="color: var(--text-primary);">ServiceKU</h1>
                <p class="mt-1 font-medium text-xs md:text-sm" style="color: var(--text-muted);">Service Center Management</p>
            </div>

            <!-- Flash Notifications -->
            <div v-if="flash.message" class="mb-4 p-3 rounded-xl animate-slide-up border"
                :style="{
                    background: flash.type === 'success' ? 'rgba(34,197,94,0.1)' :
                            flash.type === 'error' ? 'rgba(239,68,68,0.1)' :
                            flash.type === 'warning' ? 'rgba(245,158,11,0.1)' :
                            'var(--accent-light)',
                    borderColor: flash.type === 'success' ? 'rgba(34,197,94,0.2)' :
                            flash.type === 'error' ? 'rgba(239,68,68,0.2)' :
                            flash.type === 'warning' ? 'rgba(245,158,11,0.2)' :
                            'var(--border-focus)'
                }">
                <div class="flex items-start gap-2.5">
                    <!-- Icon Success -->
                    <svg v-if="flash.type === 'success'" class="w-5 h-5 mt-0.5 shrink-0" style="color: #4ade80;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <!-- Icon Error -->
                    <svg v-else-if="flash.type === 'error'" class="w-5 h-5 mt-0.5 shrink-0" style="color: #f87171;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <!-- Icon Warning -->
                    <svg v-else-if="flash.type === 'warning'" class="w-5 h-5 mt-0.5 shrink-0" style="color: #fbbf24;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                    <!-- Icon Info -->
                    <svg v-else class="w-5 h-5 mt-0.5 shrink-0" style="color: var(--accent-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm font-medium"
                        :style="{ color: flash.type === 'success' ? '#4ade80' :
                                flash.type === 'error' ? '#f87171' :
                                flash.type === 'warning' ? '#fbbf24' :
                                'var(--accent-primary)' }">
                        {{ flash.message }}
                    </p>
                </div>
            </div>

            <!-- Card -->
            <div class="rounded-2xl p-5 md:p-8 border animate-slide-up" style="background: var(--bg-card); border-color: var(--border-color);">
                <slot />
            </div>

            <!-- Footer -->
            <p class="text-center mt-6 md:mt-8 text-xs" style="color: var(--text-muted);">
                &copy; {{ new Date().getFullYear() }} ServiceKU. All rights reserved.
            </p>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const logoSrc = '/images/logo.svg';

const flash = computed(() => {
    const props = page.props;
    const success = props.flash?.success || props.success || '';
    const error = props.flash?.error || props.flash?.errors || props.error || '';
    const warning = props.flash?.warning || props.warning || '';
    const info = props.flash?.info || props.info || '';

    if (success) return { type: 'success', message: success };
    if (error) return { type: 'error', message: error };
    if (warning) return { type: 'warning', message: warning };
    if (info) return { type: 'info', message: info };
    return { type: '', message: '' };
});
</script>
