<template>
    <div class="min-h-screen bg-[#f6f8fb] text-slate-900">
        <div class="grid min-h-screen lg:grid-cols-[1.05fr_.95fr]">
            <section class="hidden border-r border-slate-200 bg-white lg:flex lg:flex-col lg:justify-between">
                <div class="px-10 py-8">
                    <a href="/" class="inline-flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-teal-700">
                            <img :src="logoSrc" alt="" class="h-6 w-6 brightness-0 invert">
                        </span>
                        <span class="text-lg font-black tracking-tight text-slate-950">ServiceKU</span>
                    </a>
                </div>
                <div class="px-10 pb-12">
                    <p class="text-xs font-black uppercase tracking-[.18em] text-teal-700">Service operations suite</p>
                    <h1 class="mt-4 max-w-xl text-5xl font-black leading-tight tracking-tight text-slate-950">
                        Satu meja kerja untuk servis, stok, POS, dan tim toko.
                    </h1>
                    <div class="mt-8 grid max-w-xl gap-3">
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm font-black text-slate-950">Tracking servis rapi</p>
                            <p class="mt-1 text-sm font-semibold leading-6 text-slate-500">Status, teknisi, sparepart, foto, dan garansi tetap dalam satu alur.</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm font-black text-slate-950">Data tenant terpisah</p>
                            <p class="mt-1 text-sm font-semibold leading-6 text-slate-500">Setiap toko punya user, cabang, stok, dan laporan sendiri.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="flex min-h-screen items-center justify-center px-4 py-10 sm:px-6">
                <div class="w-full max-w-[440px]">
            <!-- Logo -->
            <div class="mb-8 flex flex-col items-center text-center lg:hidden">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-teal-700 shadow-md">
                    <img :src="logoSrc" alt="" class="h-7 w-7 brightness-0 invert">
                </div>
                <h1 class="mb-1 text-2xl font-black tracking-tight text-slate-950 md:text-3xl">ServiceKU</h1>
                <p class="text-sm font-semibold text-slate-500">Platform Manajemen Toko Servis</p>
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
            <div class="w-full rounded-xl border border-slate-200 bg-white p-6 shadow-[0_24px_70px_rgba(15,23,42,.10)] md:p-8">
                <slot />
            </div>

            <!-- Footer -->
            <div class="text-center mt-12 space-y-2">
                <p class="text-xs font-medium text-zinc-500">
                    &copy; {{ new Date().getFullYear() }} ServiceKU. All rights reserved.
                </p>
                <div class="flex items-center justify-center gap-3 text-xs font-semibold text-zinc-400">
                    <a href="/privacy-policy" class="hover:text-zinc-600 transition-colors">Privasi</a>
                    <span>•</span>
                    <a href="/terms" class="hover:text-zinc-600 transition-colors">Ketentuan</a>
                    <span v-if="appVersion">· v{{ appVersion }}</span>
                </div>
            </div>
        </div>
            </section>
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
