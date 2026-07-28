<template>
    <GuestLayout>
        <h2 class="text-xl font-semibold text-slate-100 mb-2">Masuk ke Toko Anda</h2>
        <p class="text-sm text-slate-400 mb-6">Cari toko Anda untuk melanjutkan</p>

        <!-- Quick Login (Dev Mode Only) -->
        <div v-if="isDevMode" class="mb-6 p-4 bg-gradient-to-br from-purple-500/10 to-cyan-500/10 border border-purple-500/20 rounded-2xl">
            <div class="flex items-center gap-2 mb-3">
                <span class="px-2 py-0.5 bg-gradient-to-r from-cyan-500 to-purple-600 text-white text-[10px] font-bold rounded-full">DEV</span>
                <h3 class="text-sm font-semibold text-purple-300">Quick Login</h3>
            </div>
            <p class="text-xs text-slate-400 mb-3">Langsung login sebagai role dibawah ini:</p>
            <div class="flex flex-wrap gap-2">
                <a
                    v-for="account in demoAccounts"
                    :key="account.role"
                    :href="account.url"
                    class="flex items-center gap-2 px-4 py-2.5 bg-dark-900 border border-dark-700 rounded-xl hover:shadow-soft hover:border-purple-500/30 transition-all text-sm font-medium"
                    :class="account.color"
                >
                    <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold text-white" :class="account.bg">{{ account.icon }}</span>
                    <div>
                        <p class="text-xs font-semibold text-slate-100">{{ account.label }}</p>
                        <p class="text-[10px] text-slate-400">{{ account.email }} / <span class="font-mono">password</span></p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Error Message -->
        <div v-if="form.errors.search_value" class="mb-4 p-3 bg-accent-500/10 border border-accent-500/20 rounded-xl">
            <p class="text-sm text-accent-300">{{ form.errors.search_value }}</p>
        </div>

        <!-- Search Type Tabs -->
        <div class="flex gap-1 mb-5 p-1 bg-dark-800 rounded-xl border border-dark-700">
            <button
                v-for="opt in searchOptions"
                :key="opt.value"
                type="button"
                @click="searchType = opt.value"
                class="flex-1 px-3 py-2 text-xs font-semibold rounded-lg transition-all duration-200"
                :class="searchType === opt.value
                    ? 'bg-dark-900 text-slate-100 shadow-soft'
                    : 'text-slate-400 hover:text-slate-200'"
            >
                {{ opt.label }}
            </button>
        </div>

        <form @submit.prevent="submit">
            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-300 mb-1.5">{{ currentLabel }}</label>
                <div class="relative">
                    <input
                        v-model="form.search_value"
                        type="text"
                        name="search_value"
                        class="input-premium pl-10"
                        :placeholder="currentPlaceholder"
                        required
                        autofocus
                    />
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <!-- Icon: Store -->
                        <svg v-if="searchType === 'name'" class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <!-- Icon: Email -->
                        <svg v-else-if="searchType === 'email'" class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <!-- Icon: Phone -->
                        <svg v-else class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between mt-6">
                <p class="text-xs text-slate-400">Belum punya toko?
                    <Link :href="route('register')" class="text-purple-400 hover:text-purple-300 font-semibold">Daftar di sini</Link>
                </p>
                <button
                    type="submit"
                    class="btn-premium-primary text-sm"
                    :disabled="form.processing"
                >
                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    Lanjutkan
                </button>
            </div>
        </form>
    </GuestLayout>
</template>

<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { ref, computed } from 'vue';

const page = usePage();
const isDevMode = computed(() => page.props.app_env === 'local' || page.props.app_env === 'development');

const searchType = ref('name');

const searchOptions = [
    { value: 'name', label: 'Nama Toko' },
    { value: 'email', label: 'Email' },
    { value: 'phone', label: 'No. Telepon' },
];

const tenantSlug = 'toko-servis-abc';
const baseUrl = window.location.protocol + '//' + tenantSlug + '.localhost:' + (window.location.port || '8000');

const demoAccounts = [
    {
        role: 'owner',
        label: 'Owner',
        email: 'demo@serviceku.app',
        icon: '👑',
        bg: 'bg-premium-600',
        color: 'text-premium-700',
        url: baseUrl + '/dev-login?role=owner',
    },
    {
        role: 'cs',
        label: 'CS',
        email: 'cs@serviceku.app',
        icon: '💬',
        bg: 'bg-blue-600',
        color: 'text-blue-700',
        url: baseUrl + '/dev-login?role=cs',
    },
    {
        role: 'technician',
        label: 'Teknisi',
        email: 'teknisi@serviceku.app',
        icon: '🔧',
        bg: 'bg-emerald-600',
        color: 'text-emerald-700',
        url: baseUrl + '/dev-login?role=technician',
    },
];

const currentLabel = computed(() => {
    return searchOptions.find(o => o.value === searchType.value)?.label || 'Nama Toko';
});

const currentPlaceholder = computed(() => {
    switch (searchType.value) {
        case 'name': return 'contoh: Toko Servis ABC';
        case 'email': return 'contoh: tokosaya@email.com';
        case 'phone': return 'contoh: 08123456789';
        default: return '';
    }
});

const form = useForm({
    search_type: 'name',
    search_value: '',
});

const submit = () => {
    form.search_type = searchType.value;
    form.post(route('tenant.lookup'));
};
</script>
