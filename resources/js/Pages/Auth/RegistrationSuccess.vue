<template>
    <GuestLayout>
        <div class="text-center">
            <!-- Success Icon -->
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-emerald-100 mb-6 shadow-sm ring-8 ring-emerald-50">
                <svg class="h-8 w-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <h2 class="text-2xl font-black tracking-tight text-zinc-900 mb-2">Pendaftaran Berhasil!</h2>
            
            <div v-if="emailSent" class="bg-indigo-50 text-indigo-900 border border-indigo-100 rounded-xl p-4 mb-8">
                <p class="text-sm font-medium">
                    ✉️ Email informasi akun telah dikirim ke <strong class="font-bold">{{ email }}</strong>
                </p>
            </div>
            <div v-else class="bg-amber-50 text-amber-900 border border-amber-100 rounded-xl p-4 mb-8">
                <p class="text-sm font-medium">
                    ⚠️ Gagal mengirim email. Jangan khawatir, Anda tetap bisa login menggunakan informasi di bawah ini.
                </p>
            </div>

            <!-- Info Login -->
            <div class="bg-white rounded-2xl shadow-sm border border-zinc-200 p-6 mb-8 text-left">
                <h3 class="text-sm font-bold text-zinc-900 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Informasi Akun
                </h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between items-center py-2 border-b border-zinc-100 last:border-0">
                        <span class="text-zinc-500 font-medium">Paket Langganan</span>
                        <span class="font-bold text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-md text-xs tracking-wide uppercase">{{ plan }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-zinc-100 last:border-0">
                        <span class="text-zinc-500 font-medium">Link Login</span>
                        <a :href="'http://' + domain + '/login'" target="_blank" class="text-indigo-600 hover:text-indigo-700 font-bold hover:underline">
                            {{ domain }}/login
                        </a>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-zinc-100 last:border-0">
                        <span class="text-zinc-500 font-medium">Email</span>
                        <span class="font-bold text-zinc-900">{{ email }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-zinc-100 last:border-0">
                        <span class="text-zinc-500 font-medium">Password</span>
                        <span class="font-bold text-zinc-400 text-xs">(Seperti yang didaftarkan)</span>
                    </div>
                </div>
            </div>

            <Link :href="'http://' + domain + '/login'"
                class="w-full flex items-center justify-center gap-2 px-6 py-4 rounded-xl bg-zinc-900 text-white text-base font-black shadow-md hover:bg-zinc-800 transition-all">
                Mulai Kelola Toko 🚀
            </Link>

            <p class="mt-6 text-xs font-medium text-zinc-500">
                Butuh bantuan? Hubungi <a href="mailto:support@serviceku.com" class="text-zinc-900 font-bold hover:underline">support@serviceku.com</a>
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
