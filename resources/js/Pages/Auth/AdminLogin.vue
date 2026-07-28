<template>
    <div class="min-h-screen flex items-center justify-center relative overflow-hidden bg-gradient-to-br from-dark-50 via-premium-50/30 to-dark-50">
        <!-- Background Decor -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-premium-200/20 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-premium-300/20 rounded-full blur-3xl"></div>
        </div>

        <div class="w-full max-w-sm relative z-10 px-4">
            <div class="text-center mb-8 animate-fade-in">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-premium-500 to-premium-700 shadow-premium mb-3">
                    <span class="text-xl font-bold text-white">SK</span>
                </div>
                <h1 class="text-2xl font-bold text-dark-900">Admin Panel</h1>
                <p class="text-dark-400 text-sm mt-1">ServiceKU Management</p>
            </div>

            <div class="card-glass p-6 animate-slide-up">
                <div v-if="$page.props.flash?.error" class="mb-4 p-3 bg-accent-50 border border-accent-200 rounded-xl">
                    <p class="text-sm text-accent-700">{{ $page.props.flash.error }}</p>
                </div>

                <form @submit.prevent="submit">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-dark-700 mb-1.5">Email</label>
                        <input type="email" v-model="form.email" required
                            class="input-premium"
                            placeholder="admin@serviceku.my.id" />
                        <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-dark-700 mb-1.5">Password</label>
                        <input type="password" v-model="form.password" required
                            class="input-premium"
                            placeholder="Masukkan password" />
                    </div>
                    <button type="submit" :disabled="form.processing"
                        class="btn-premium-primary w-full mt-2">
                        {{ form.processing ? 'Memproses...' : 'Masuk' }}
                    </button>
                </form>
            </div>

            <p class="text-center mt-6 text-xs text-dark-400">
                <a href="/" class="hover:text-dark-600 transition-colors">← Kembali ke Beranda</a>
            </p>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
});

const submit = () => {
    form.post(route('admin.login.post'));
};
</script>
