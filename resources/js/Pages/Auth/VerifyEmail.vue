<template>
    <Head title="Verifikasi Email" />
    <GuestLayout>
        <div class="text-center mb-6">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full sk-bg-primary-soft mb-6 shadow-sm ring-8 ring-indigo-50">
                <svg class="h-8 w-8 sk-text-primary-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
            
            <h1 class="text-2xl font-black sk-text-primary tracking-tight">Verifikasi Email</h1>
            <p class="sk-text-muted font-medium text-sm mt-3 leading-relaxed">
                Link verifikasi telah dikirim ke <strong class="sk-text-primary font-bold">{{ user.email }}</strong>.
                Silakan cek kotak masuk atau folder spam Anda.
            </p>
        </div>

        <div v-if="$page.props.flash?.success" class="mb-6 p-4 sk-bg-success-soft border border-emerald-100 rounded-xl flex items-start gap-3">
            <svg class="w-5 h-5 sk-text-success shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm font-semibold sk-text-success">{{ $page.props.flash.success }}</p>
        </div>

        <div class="sk-bg-hover border sk-border p-5 rounded-2xl mb-8">
            <p class="font-bold text-sm sk-text-primary mb-2">Tidak menerima email?</p>
            <ul class="list-disc list-inside text-sm font-medium sk-text-secondary space-y-1">
                <li>Cek folder <strong class="sk-text-primary">Spam</strong> atau <strong class="sk-text-primary">Promosi</strong></li>
                <li>Pastikan alamat email sudah benar</li>
            </ul>
        </div>

        <form @submit.prevent="resend" class="space-y-4">
            <KButton  type="submit"
                :disabled="form.processing"
                class="w-full flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-zinc-900 text-white text-sm font-bold shadow-md hover:bg-zinc-800 focus:ring-2 focus:ring-offset-2 focus:ring-zinc-900 transition-all disabled:opacity-70">
                <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                {{ form.processing ? 'Mengirim...' : 'Kirim Ulang Link Verifikasi' }}
            </KButton>
        </form>

        <div class="mt-6 text-center">
            <Link :href="route('logout')" method="post" as="button"
                class="text-sm font-bold sk-text-muted hover:sk-text-danger transition-colors">
                Keluar (Logout)
            </Link>
        </div>
    </GuestLayout>
</template>

<script setup>
import KButton from '@/Components/KButton.vue';

import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

const user = usePage().props.auth?.user ?? {};
const form = useForm({});

function resend() {
    form.post(route('tenant.verification.resend'));
}
</script>
