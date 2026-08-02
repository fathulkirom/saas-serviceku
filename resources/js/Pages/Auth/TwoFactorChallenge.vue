<template>
    <Head title="Verifikasi 2FA" />
    <GuestLayout>
        <div class="text-center mb-8">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-indigo-100 mb-6 shadow-sm ring-8 ring-indigo-50">
                <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            
            <h1 class="text-2xl font-black text-zinc-900 tracking-tight mb-2">Verifikasi Dua Langkah</h1>
            <p class="text-zinc-500 font-medium text-sm leading-relaxed">
                Masukkan kode keamanan untuk <strong class="text-zinc-900 font-bold">{{ email }}</strong>.
            </p>
        </div>

        <div v-if="$page.props.flash?.success" class="mb-6 p-4 bg-emerald-50 border border-emerald-100 rounded-xl flex items-start gap-3">
            <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm font-semibold text-emerald-800">{{ $page.props.flash.success }}</p>
        </div>

        <div v-if="form.errors.code" class="mb-6 p-4 bg-red-50 border border-red-100 rounded-xl flex items-start gap-3">
            <svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm font-semibold text-red-800">{{ form.errors.code }}</p>
        </div>

        <!-- TOTP Form -->
        <form v-if="!showBackup && !showEmailCode" @submit.prevent="submit" class="space-y-6">
            <div>
                <label class="block text-sm font-bold text-zinc-900 mb-2 text-center">Kode Aplikasi Authenticator</label>
                <KInput 
                    v-model="form.code"
                    type="text"
                    inputmode="numeric"
                    maxlength="6"
                    placeholder="000000"
                    class="w-full text-center text-3xl font-black tracking-[0.5em] px-4 py-4 rounded-xl border border-zinc-300 bg-white text-zinc-900 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all shadow-sm"
                    autofocus />
            </div>
            
            <div class="pt-2 flex flex-col gap-4">
                <KButton  type="submit"
                    :disabled="form.processing || form.code.length !== 6"
                    class="w-full flex items-center justify-center px-6 py-3 rounded-xl bg-zinc-900 text-white text-sm font-bold shadow-md hover:bg-zinc-800 focus:ring-2 focus:ring-offset-2 focus:ring-zinc-900 transition-all disabled:opacity-70">
                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    Verifikasi Kode
                </KButton>
            </div>
        </form>

        <!-- Backup Code Form -->
        <form v-if="showBackup" @submit.prevent="submitBackup" class="space-y-6">
            <div class="bg-amber-50 text-amber-900 border border-amber-100 p-4 rounded-xl mb-2 text-sm font-medium">
                Masukkan salah satu dari kode pemulihan cadangan Anda.
            </div>
            <div>
                <label class="block text-sm font-bold text-zinc-900 mb-2 text-center">Kode Cadangan</label>
                <KInput 
                    v-model="form.code"
                    type="text"
                    placeholder="XXXXXX-XXXXXX"
                    class="w-full text-center text-xl font-bold tracking-widest px-4 py-4 rounded-xl border border-zinc-300 bg-white text-zinc-900 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all shadow-sm"
                    autofocus />
            </div>
            
            <div class="pt-2 flex flex-col gap-4">
                <KButton  type="submit"
                    :disabled="form.processing || !form.code"
                    class="w-full flex items-center justify-center px-6 py-3 rounded-xl bg-zinc-900 text-white text-sm font-bold shadow-md hover:bg-zinc-800 focus:ring-2 focus:ring-offset-2 focus:ring-zinc-900 transition-all disabled:opacity-70">
                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    Gunakan Kode Cadangan
                </KButton>
                <KButton  type="button" @click="showBackup = false; form.code = ''"
                    class="w-full py-2 text-sm font-bold text-zinc-500 hover:text-zinc-900 transition-colors">
                    Kembali ke Authenticator
                </KButton>
            </div>
        </form>

        <!-- Email Code Form -->
        <form v-if="showEmailCode" @submit.prevent="submitEmailCode" class="space-y-6">
            <div class="bg-indigo-50 text-indigo-900 border border-indigo-100 p-4 rounded-xl mb-2 text-sm font-medium">
                Kode keamanan telah dikirim ke email <strong class="font-bold">{{ email }}</strong>. Silakan periksa kotak masuk Anda.
            </div>
            <div>
                <label class="block text-sm font-bold text-zinc-900 mb-2 text-center">Kode dari Email</label>
                <KInput 
                    v-model="form.code"
                    type="text"
                    inputmode="numeric"
                    maxlength="6"
                    placeholder="000000"
                    class="w-full text-center text-3xl font-black tracking-[0.5em] px-4 py-4 rounded-xl border border-zinc-300 bg-white text-zinc-900 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all shadow-sm"
                    autofocus />
            </div>
            
            <div class="pt-2 flex flex-col gap-4">
                <KButton  type="submit"
                    :disabled="form.processing || form.code.length !== 6"
                    class="w-full flex items-center justify-center px-6 py-3 rounded-xl bg-zinc-900 text-white text-sm font-bold shadow-md hover:bg-zinc-800 focus:ring-2 focus:ring-offset-2 focus:ring-zinc-900 transition-all disabled:opacity-70">
                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    Verifikasi Kode Email
                </KButton>
                <KButton  type="button" @click="showEmailCode = false; form.code = ''"
                    class="w-full py-2 text-sm font-bold text-zinc-500 hover:text-zinc-900 transition-colors">
                    Kembali ke Authenticator
                </KButton>
            </div>
        </form>

        <!-- Options -->
        <div v-if="!showBackup && !showEmailCode" class="mt-8 space-y-3 pt-6 border-t border-zinc-100">
            <KButton  @click="sendEmailCode" class="w-full flex items-center justify-center gap-2 py-2 text-sm font-bold text-indigo-600 hover:text-indigo-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                Kirim kode keamanan via Email
            </KButton>
            <KButton  @click="showBackup = true" class="w-full flex items-center justify-center gap-2 py-2 text-sm font-bold text-zinc-500 hover:text-zinc-900 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" /></svg>
                Gunakan Kode Cadangan (Backup)
            </KButton>
        </div>
    </GuestLayout>
</template>

<script setup>
import KButton from '@/Components/KButton.vue';
import KInput from '@/Components/KInput.vue';

import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    email: String,
});

const showBackup = ref(false);
const showEmailCode = ref(false);

const form = useForm({ code: '' });

function submit() {
    form.post(route('two-factor.verify'));
}

function submitBackup() {
    form.post(route('two-factor.verify'));
}

function sendEmailCode() {
    form.post(route('two-factor.send-email'), {
        preserveState: true,
        onSuccess: () => { showEmailCode.value = true; },
    });
}

function submitEmailCode() {
    form.post(route('two-factor.verify-email'));
}
</script>
