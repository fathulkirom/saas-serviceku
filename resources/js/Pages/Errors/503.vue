<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50">
        <div class="text-center px-6">
            <div class="text-8xl font-bold text-amber-500 mb-4">503</div>
            <h1 class="text-2xl font-semibold text-gray-800 mb-2">Sedang Dalam Pemeliharaan</h1>
            <p class="text-gray-500 mb-6">Kami sedang melakukan pemeliharaan sistem. Silakan coba lagi beberapa saat.</p>
            <p class="text-sm text-gray-400">Harap refresh halaman ini secara berkala.</p>
        </div>
    </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3';
const page = usePage();
</script>
