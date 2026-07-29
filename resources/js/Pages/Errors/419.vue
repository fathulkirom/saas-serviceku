<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50">
        <div class="text-center px-6">
            <div class="text-8xl font-bold text-orange-500 mb-4">419</div>
            <h1 class="text-2xl font-semibold text-gray-800 mb-2">Sesi Habis</h1>
            <p class="text-gray-500 mb-6">Sesi Anda telah berakhir. Silakan login kembali untuk melanjutkan.</p>
            <Link :href="route('login')" class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                Login Kembali
            </Link>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>
