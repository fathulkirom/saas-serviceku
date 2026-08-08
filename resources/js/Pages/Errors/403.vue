<template>
    <div class="min-h-screen flex flex-col items-center justify-center p-4 text-center sk-bg-hover">
        <div class="max-w-md w-full space-y-6">
            <div class="text-9xl font-black tracking-widest sk-text-danger">403</div>
            <div class="space-y-2">
                <h1 class="text-2xl font-bold sk-text-primary">Akses Ditolak</h1>
                <p class="text-sm sk-text-muted">
                    Anda tidak memiliki izin untuk mengakses halaman atau fitur ini.
                </p>
            </div>
            <div>
                <Link :href="route('dashboard')" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white transition-all shadow-md hover:shadow-lg sk-bg-primary text-white">
                    ← Kembali ke Dashboard
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>
