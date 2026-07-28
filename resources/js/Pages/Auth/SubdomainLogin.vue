<template>
    <GuestLayout>
        <h2 class="text-xl font-bold text-dark-900 mb-2">Login <span class="text-premium-600">{{ storeName }}</span></h2>
        <p class="text-sm text-dark-400 mb-6">Masukkan email dan password untuk masuk</p>

        <!-- Quick Login (Dev Mode Only) -->
        <div v-if="isDevMode" class="mb-6 p-4 bg-gradient-to-br from-premium-50 to-blue-50 border border-premium-200 rounded-2xl">
            <div class="flex items-center gap-2 mb-3">
                <span class="px-2 py-0.5 bg-premium-500 text-white text-[10px] font-bold rounded-full">DEV</span>
                <h3 class="text-sm font-semibold text-premium-700">Quick Login</h3>
            </div>
            <p class="text-xs text-dark-500 mb-3">Login cepat sebagai:</p>
            <div class="flex flex-wrap gap-2">
                <a
                    v-for="account in demoAccounts"
                    :key="account.role"
                    :href="account.url"
                    class="flex items-center gap-2 px-3 py-2 bg-white border border-premium-200 rounded-xl hover:shadow-soft hover:border-premium-300 transition-all text-xs font-medium"
                >
                    <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold text-white" :class="account.bg">{{ account.icon }}</span>
                    <div>
                        <p class="text-xs font-semibold text-dark-900">{{ account.label }}</p>
                        <p class="text-[10px] text-dark-400">{{ account.email }}</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Error Message -->
        <div v-if="form.errors.login || form.errors.password" class="mb-4 p-3 bg-accent-50 border border-accent-200 rounded-xl">
            <p v-if="form.errors.login" class="text-sm text-accent-700">{{ form.errors.login }}</p>
            <p v-else-if="form.errors.password" class="text-sm text-accent-700">{{ form.errors.password }}</p>
        </div>

        <form @submit.prevent="submit">
            <div class="mb-4">
                <label for="login" class="block text-sm font-medium text-dark-600 mb-1.5">Email / Username</label>
                <input
                    id="login"
                    v-model="form.login"
                    type="text"
                    class="input-premium"
                    placeholder="email@example.com"
                    required
                    autofocus
                    autocomplete="username"
                />
                <p v-if="form.errors.login" class="mt-1.5 text-sm text-accent-600">{{ form.errors.login }}</p>
            </div>
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-dark-600 mb-1.5">Password</label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="input-premium"
                    placeholder="••••••••"
                    required
                    autocomplete="current-password"
                />
                <p v-if="form.errors.password" class="mt-1.5 text-sm text-accent-600">{{ form.errors.password }}</p>
            </div>
            <div class="flex items-center justify-between mt-6">
                <Link :href="route('password.request')" class="text-sm font-semibold text-premium-600 hover:text-premium-500 transition-colors">
                    Lupa Password?
                </Link>
                <button
                    type="submit"
                    class="btn-premium-primary"
                    :disabled="form.processing"
                >
                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    Masuk
                </button>
            </div>
        </form>

        <!-- Google Login -->
        <div class="mt-6">
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-dark-200"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-dark-400">Atau masuk dengan</span>
                </div>
            </div>
            <div class="mt-4">
                <a :href="route('google.login')"
                    class="w-full flex items-center justify-center gap-3 px-4 py-2.5 border border-dark-200 rounded-xl hover:bg-gray-50 transition-colors text-sm font-medium text-dark-700">
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
    { role: 'owner', label: 'Owner', email: 'demo@serviceku.app', icon: '👑', bg: 'bg-premium-600', url: '/dev-login?role=owner' },
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
