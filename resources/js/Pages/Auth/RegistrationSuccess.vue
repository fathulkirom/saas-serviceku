<template>
    <GuestLayout>
        <div class="text-center">
            <!-- Success Icon -->
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full sk-bg-success-soft mb-6 shadow-sm ring-8 ring-emerald-50">
                <svg class="h-8 w-8 sk-text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <h2 class="text-2xl font-black tracking-tight sk-text-primary mb-2">Pendaftaran Berhasil!</h2>
            
            <div v-if="emailSent" class="sk-bg-primary-soft text-indigo-900 border sk-border-primary rounded-xl p-4 mb-8">
                <p class="text-sm font-medium">
                    ✉️ Email informasi akun telah dikirim ke <strong class="font-bold">{{ email }}</strong>
                </p>
            </div>
            <div v-else class="sk-bg-warning-soft text-amber-900 border border-amber-100 rounded-xl p-4 mb-8">
                <p class="text-sm font-medium">
                    ⚠️ Gagal mengirim email. Jangan khawatir, Anda tetap bisa login menggunakan informasi di bawah ini.
                </p>
            </div>

            <!-- Info Login -->
            <div class="sk-bg-card rounded-2xl shadow-sm border sk-border p-6 mb-8 text-left">
                <h3 class="text-sm font-bold sk-text-primary mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 sk-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Informasi Akun
                </h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between items-center py-2 border-b sk-border-light last:border-0">
                        <span class="sk-text-muted font-medium">Paket Langganan</span>
                        <span class="font-bold sk-text-primary-brand sk-bg-primary-soft px-2.5 py-1 rounded-md text-xs tracking-wide uppercase">{{ plan }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b sk-border-light last:border-0">
                        <span class="sk-text-muted font-medium">Link Login</span>
                        <a :href="'http://' + domain + '/login'" target="_blank" class="sk-text-primary-brand hover:sk-text-primary-brand font-bold hover:underline">
                            {{ domain }}/login
                        </a>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b sk-border-light last:border-0">
                        <span class="sk-text-muted font-medium">Email</span>
                        <span class="font-bold sk-text-primary">{{ email }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b sk-border-light last:border-0">
                        <span class="sk-text-muted font-medium">Password</span>
                        <span class="font-bold sk-text-muted text-xs">(Seperti yang didaftarkan)</span>
                    </div>
                </div>
            </div>

            <Link :href="'http://' + domain + '/login'"
                class="w-full flex items-center justify-center gap-2 px-6 py-4 rounded-xl sk-bg-inverse text-white text-base font-black shadow-md hover:sk-bg-inverse transition-all">
                Mulai Kelola Toko 🚀
            </Link>

            <p class="mt-6 text-xs font-medium sk-text-muted">
                Butuh bantuan? Hubungi <a href="mailto:support@serviceku.com" class="sk-text-primary font-bold hover:underline">support@serviceku.com</a>
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
