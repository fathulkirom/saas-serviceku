<template>
    <div class="min-h-screen flex flex-col items-center justify-center p-4 text-center bg-zinc-50">
        <div class="max-w-md w-full space-y-6">
            <div class="text-9xl font-black tracking-widest text-indigo-600">404</div>
            <div class="space-y-2">
                <h1 class="text-2xl font-bold text-zinc-900">Halaman Tidak Ditemukan</h1>
                <p class="text-sm text-zinc-500">
                    Halaman yang Anda cari tidak ada, telah dihapus, atau dipindahkan.
                </p>
            </div>
            <div>
                <Link :href="route('dashboard')" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white transition-all shadow-md hover:shadow-lg bg-indigo-600 text-white">
                    ← Kembali ke Dashboard
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>
