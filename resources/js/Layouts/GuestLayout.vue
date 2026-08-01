<template>
    <div class="min-h-screen flex items-center justify-center relative overflow-hidden bg-zinc-50">
        <!-- Background Decor -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none z-0">
            <!-- Subtle grid background similar to landing page -->
            <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(#000 1px, transparent 1px); background-size: 24px 24px;"></div>
            
            <div class="absolute -top-40 -right-40 w-96 h-96 rounded-full blur-[100px] bg-indigo-500/10"></div>
            <div class="absolute -bottom-40 -left-40 w-96 h-96 rounded-full blur-[100px] bg-blue-500/10"></div>
        </div>

        <div class="w-full max-w-[440px] relative z-10 px-4 py-12 flex flex-col justify-center min-h-screen">
            <!-- Logo -->
            <div class="text-center mb-8 animate-fade-in flex flex-col items-center">
                <div class="w-12 h-12 rounded-xl bg-zinc-900 flex items-center justify-center text-white font-black text-xl mb-4 shadow-md">
                    SK
                </div>
                <h1 class="text-2xl md:text-3xl font-black tracking-tight text-zinc-900 mb-1">ServiceKU</h1>
                <p class="font-medium text-sm text-zinc-500">Platform Manajemen Toko Servis</p>
            </div>

            <!-- Flash Notifications -->
            <div v-if="flash.message" class="mb-6 p-4 rounded-xl animate-slide-up border"
                :class="{
                    'bg-emerald-50 border-emerald-100 text-emerald-800': flash.type === 'success',
                    'bg-red-50 border-red-100 text-red-800': flash.type === 'error',
                    'bg-amber-50 border-amber-100 text-amber-800': flash.type === 'warning',
                    'bg-blue-50 border-blue-100 text-blue-800': !['success', 'error', 'warning'].includes(flash.type)
                }">
                <div class="flex items-start gap-3">
                    <!-- Icon Success -->
                    <svg v-if="flash.type === 'success'" class="w-5 h-5 mt-0.5 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <!-- Icon Error -->
                    <svg v-else-if="flash.type === 'error'" class="w-5 h-5 mt-0.5 shrink-0 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <!-- Icon Warning -->
                    <svg v-else-if="flash.type === 'warning'" class="w-5 h-5 mt-0.5 shrink-0 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                    <!-- Icon Info -->
                    <svg v-else class="w-5 h-5 mt-0.5 shrink-0 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm font-bold flex-1 pt-0.5">
                        {{ flash.message }}
                    </p>
                </div>
            </div>

            <!-- Card -->
            <div class="bg-white rounded-2xl p-6 md:p-8 border border-zinc-200 shadow-xl shadow-zinc-200/40 animate-slide-up w-full">
                <slot />
            </div>

            <!-- Footer -->
            <div class="text-center mt-12 space-y-2">
                <p class="text-xs font-medium text-zinc-500">
                    &copy; {{ new Date().getFullYear() }} ServiceKU. All rights reserved.
                </p>
                <div class="flex items-center justify-center gap-3 text-xs font-semibold text-zinc-400">
                    <a href="#" class="hover:text-zinc-600 transition-colors">Privasi</a>
                    <span>•</span>
                    <a href="#" class="hover:text-zinc-600 transition-colors">Ketentuan</a>
                    <span v-if="appVersion">· v{{ appVersion }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const logoSrc = '/images/logo.svg';
const appVersion = computed(() => page.props.app_version || '');

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
