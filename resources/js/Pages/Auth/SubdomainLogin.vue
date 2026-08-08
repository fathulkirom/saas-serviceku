<template>
    <GuestLayout>
        <div class="text-center mb-8">
            <h2 class="text-xl font-bold sk-text-primary mb-2">Masuk ke <span class="sk-text-primary-brand">{{ storeName }}</span></h2>
            <p class="text-sm sk-text-muted">Masukkan email dan password untuk masuk ke dasbor</p>
        </div>

        <!-- Quick Login (Dev Mode Only) -->
        <div v-if="isDevMode" class="mb-6 p-5 sk-bg-primary-soft border sk-border-primary rounded-2xl">
            <div class="flex items-center gap-2 mb-4">
                <span class="px-2 py-0.5 sk-bg-primary text-white text-[10px] font-bold rounded-md uppercase tracking-wider">DEV</span>
                <h3 class="text-xs font-bold text-indigo-900 uppercase tracking-wider">Akses Cepat (Demo)</h3>
            </div>
            <div class="flex flex-col gap-3">
                <a
                    v-for="account in demoAccounts"
                    :key="account.role"
                    :href="account.url"
                    class="flex items-center gap-4 p-3 sk-bg-card border sk-border-primary rounded-xl hover:shadow-md hover:border-indigo-300 transition-all group"
                >
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg shadow-sm" :class="account.bg">{{ account.icon }}</div>
                    <div class="flex-1 text-left">
                        <p class="text-sm font-bold sk-text-primary group-hover:sk-text-primary-brand transition-colors">{{ account.label }}</p>
                        <p class="text-xs sk-text-muted">{{ account.email }}</p>
                    </div>
                    <svg class="w-4 h-4 sk-text-muted group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        <!-- Error Message -->
        <div v-if="form.errors.login || form.errors.password" class="mb-6 p-4 sk-bg-danger-soft border border-red-100 rounded-xl flex items-start gap-3">
            <svg class="w-5 h-5 sk-text-danger shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="flex-1">
                <p v-if="form.errors.login" class="text-sm font-semibold sk-text-danger">{{ form.errors.login }}</p>
                <p v-else-if="form.errors.password" class="text-sm font-semibold sk-text-danger">{{ form.errors.password }}</p>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <label for="login" class="block text-sm font-bold sk-text-primary mb-2">Email / Nama Pengguna</label>
                <KInput 
                    id="login"
                    v-model="form.login"
                    type="text"
                    class="w-full px-4 py-3 rounded-xl border sk-border sk-bg-card text-sm font-semibold sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all shadow-sm"
                    placeholder="email@example.com"
                    required
                    autofocus
                    autocomplete="username" />
            </div>
            
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label for="password" class="block text-sm font-bold sk-text-primary">Password</label>
                    <Link :href="route('password.request')" class="text-xs font-bold sk-text-primary-brand hover:sk-text-primary-brand transition-colors">
                        Lupa password?
                    </Link>
                </div>
                <KInput 
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="w-full px-4 py-3 rounded-xl border sk-border sk-bg-card text-sm font-semibold sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all shadow-sm"
                    placeholder="••••••••"
                    required
                    autocomplete="current-password" />
            </div>

            <div class="pt-2">
                <KButton 
                    type="submit"
                    class="w-full flex items-center justify-center px-6 py-3 rounded-xl sk-bg-inverse text-white text-sm font-bold shadow-md hover:sk-bg-inverse focus:ring-2 focus:ring-offset-2 focus:ring-zinc-900 transition-all disabled:opacity-70"
                    :disabled="form.processing">
                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    Masuk ke Dasbor
                </KButton>
            </div>
        </form>

        <!-- Google Login -->
        <div class="mt-8">
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t sk-border"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-3 sk-bg-card sk-text-muted font-medium">atau masuk dengan</span>
                </div>
            </div>
            
            <div class="mt-6">
                <a :href="route('google.login')"
                    class="w-full flex items-center justify-center gap-3 px-4 py-3 border sk-border rounded-xl sk-bg-card hover:sk-bg-hover hover:shadow-sm transition-all text-sm font-bold sk-text-primary shadow-sm">
                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Google
                </a>
            </div>
        </div>
    </GuestLayout>
</template>

<script setup>
import KButton from '@/Components/KButton.vue';
import KInput from '@/Components/KInput.vue';

import { useForm, usePage, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const props = defineProps({
    tenantName: { type: String, default: '' },
});

const page = usePage();
const storeName = computed(() => {
    if (props.tenantName) return props.tenantName;
    return page.props.tenant?.name || '';
});

const isDevMode = computed(() => page.props.app_env === 'local' || page.props.app_env === 'development');

const demoAccounts = [
    { role: 'owner', label: 'Owner', email: 'demo@serviceku.app', icon: '👑', bg: 'sk-bg-primary', url: '/dev-login?role=owner' },
    { role: 'cs', label: 'CS', email: 'cs@serviceku.app', icon: '💬', bg: 'bg-blue-600', url: '/dev-login?role=cs' },
    { role: 'technician', label: 'Teknisi', email: 'teknisi@serviceku.app', icon: '🔧', bg: 'bg-emerald-600', url: '/dev-login?role=technician' },
    { role: 'cashier', label: 'Kasir', email: 'kasir@serviceku.app', icon: '💳', bg: 'bg-amber-600', url: '/dev-login?role=cashier' },
    { role: 'courier', label: 'Kurir', email: 'kurir@serviceku.app', icon: '🚚', bg: 'bg-teal-600', url: '/dev-login?role=courier' },
];

const form = useForm({
    login: '',
    password: '',
});

const submit = () => {
    form.post(route('login.post'));
};
</script>
