<template>
    <GuestLayout>
        <div class="text-center">
            <!-- Success Icon -->
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-success-50 mb-6">
                <svg class="h-10 w-10 text-success-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <h2 class="text-2xl font-bold text-dark-900 mb-2">Pendaftaran Berhasil! 🎉</h2>
            
            <div v-if="emailSent" class="bg-premium-50 border border-premium-200 rounded-xl p-4 mb-6">
                <p class="text-sm text-premium-700">
                    ✉️ Email telah dikirim ke <strong>{{ email }}</strong>
                </p>
            </div>
            <div v-else class="bg-accent-50 border border-accent-200 rounded-xl p-4 mb-6">
                <p class="text-sm text-accent-700">
                    ⚠️ Gagal mengirim email. Tapi jangan khawatir, Anda tetap bisa login menggunakan informasi di bawah ini.
                </p>
            </div>

            <!-- Info Login -->
            <div class="bg-dark-50 rounded-2xl shadow-soft border border-dark-100/50 p-6 mb-6 text-left">
                <h3 class="text-sm font-medium text-dark-600 mb-3">📋 Informasi Akun</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-dark-400">Paket:</span>
                        <span class="font-semibold text-premium-600">{{ plan }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-dark-400">Link Login:</span>
                        <a :href="'http://' + domain + '/login'" target="_blank" class="text-premium-600 hover:text-premium-500 font-semibold">
                            {{ domain }}/login
                        </a>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-dark-400">Email:</span>
                        <span class="font-medium text-dark-900">{{ email }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-dark-400">Password:</span>
                        <span class="font-medium text-dark-900">(password yang Anda daftarkan)</span>
                    </div>
                </div>
            </div>

            <!-- Steps -->
            <div class="text-left mb-8">
                <h3 class="text-sm font-medium text-dark-600 mb-3">📝 Langkah Selanjutnya</h3>
                <ol class="space-y-2 text-sm text-dark-500">
                    <li class="flex items-start gap-2">
                        <span class="text-success-700 font-bold">1.</span>
                        <span>Cek email <strong>{{ email }}</strong> untuk link login</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-success-700 font-bold">2.</span>
                        <span>Klik link atau buka <strong>{{ domain }}/login</strong> di browser</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-success-700 font-bold">3.</span>
                        <span>Login menggunakan email dan password yang sudah didaftarkan</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-success-700 font-bold">4.</span>
                        <span>Mulai kelola toko Anda! 🚀</span>
                    </li>
                </ol>
            </div>

            <Link :href="'http://' + domain + '/login'"
                class="btn-premium-primary inline-block text-sm font-medium">
                🔑 Login ke {{ domain }}
            </Link>

            <p class="mt-4 text-xs text-dark-400">
                Butuh bantuan? Hubungi kami di support@serviceku.my.id
            </p>
        </div>
    </GuestLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const props = defineProps({
    email: { type: String, default: '' },
    domain: { type: String, default: '' },
    emailSent: { type: Boolean, default: false },
    plan: { type: String, default: 'Trial' },
});
</script>
