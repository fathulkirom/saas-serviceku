<template>
    <div class="min-h-screen flex items-center justify-center sk-bg-hover">
        <div class="text-center px-6">
            <div class="text-8xl font-bold text-amber-500 mb-4">503</div>
            <h1 class="text-2xl font-semibold sk-text-primary mb-2">Sedang Dalam Pemeliharaan</h1>
            <p class="sk-text-muted mb-6">Kami sedang melakukan pemeliharaan sistem. Silakan coba lagi beberapa saat.</p>
            <p class="text-sm sk-text-muted">Harap refresh halaman ini secara berkala.</p>
        </div>
    </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3';
const page = usePage();
</script>
