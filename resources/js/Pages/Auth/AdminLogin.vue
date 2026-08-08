<template>
    <div class="min-h-screen flex items-center justify-center relative overflow-hidden sk-bg-hover">
        <!-- Background Decor -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none z-0">
            <!-- Subtle grid background -->
            <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(#000 1px, transparent 1px); background-size: 24px 24px;"></div>
            <div class="absolute -top-40 -right-40 w-96 h-96 bg-zinc-300/20 rounded-full blur-[100px]"></div>
            <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-indigo-500/10 rounded-full blur-[100px]"></div>
        </div>

        <div class="w-full max-w-[400px] relative z-10 px-4 py-12">
            <div class="text-center mb-8 animate-fade-in">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-zinc-900 shadow-md mb-4">
                    <span class="text-xl font-black text-white">SK</span>
                </div>
                <h1 class="text-2xl font-black tracking-tight sk-text-primary">Admin Panel</h1>
                <p class="sk-text-muted text-sm mt-1 font-medium">ServiceKU Central Management</p>
            </div>

            <div class="sk-bg-card p-8 rounded-2xl border sk-border shadow-xl shadow-zinc-200/40 animate-slide-up">
                <div v-if="$page.props.flash?.error" class="mb-6 p-4 sk-bg-danger-soft border border-red-100 rounded-xl flex items-start gap-3">
                    <svg class="w-5 h-5 sk-text-danger shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm font-semibold sk-text-danger">{{ $page.props.flash.error }}</p>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label class="block text-sm font-bold sk-text-primary mb-2">Email</label>
                        <KInput  type="email" v-model="form.email" required autofocus
                            class="w-full px-4 py-3 rounded-xl border sk-border sk-bg-card text-sm font-semibold sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all shadow-sm"
                            placeholder="admin@serviceku.com" />
                        <p v-if="form.errors.email" class="mt-1.5 text-xs font-semibold sk-text-danger">{{ form.errors.email }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-bold sk-text-primary mb-2">Password</label>
                        <KInput  type="password" v-model="form.password" required
                            class="w-full px-4 py-3 rounded-xl border sk-border sk-bg-card text-sm font-semibold sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all shadow-sm"
                            placeholder="••••••••" />
                    </div>
                    <div class="pt-2">
                        <KButton  type="submit" :disabled="form.processing"
                            class="w-full flex items-center justify-center px-6 py-3 rounded-xl bg-zinc-900 text-white text-sm font-bold shadow-md hover:bg-zinc-800 focus:ring-2 focus:ring-offset-2 focus:ring-zinc-900 transition-all disabled:opacity-70">
                            <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                            {{ form.processing ? 'Memproses...' : 'Masuk ke Panel' }}
                        </KButton>
                    </div>
                </form>
            </div>

            <div class="text-center mt-8">
                <a href="/" class="text-sm font-bold sk-text-muted hover:sk-text-primary transition-colors inline-flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</template>

<script setup>
import KButton from '@/Components/KButton.vue';
import KInput from '@/Components/KInput.vue';

import { useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
});

const submit = () => {
    form.post(route('admin.login.post'));
};
</script>
