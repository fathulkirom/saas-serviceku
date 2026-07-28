<template>
    <div class="min-h-screen flex flex-col items-center justify-center p-4 text-center" style="background: var(--bg-primary);">
        <div class="max-w-md w-full space-y-6">
            <div class="text-9xl font-black tracking-widest text-amber-500">500</div>
            <div class="space-y-2">
                <h1 class="text-2xl font-bold" style="color: var(--text-primary);">Terjadi Kesalahan Server</h1>
                <p class="text-sm" style="color: var(--text-muted);">
                    Maaf, sistem mengalami kendala teknis. Silakan coba beberapa saat lagi.
                </p>
            </div>
            <div>
                <Link :href="route('dashboard')" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white transition-all shadow-md hover:shadow-lg" style="background: var(--accent-primary);">
                    ← Kembali ke Dashboard
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>
