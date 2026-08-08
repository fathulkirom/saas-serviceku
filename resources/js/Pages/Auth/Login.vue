<template>
    <GuestLayout>
        <div class="text-center mb-8">
            <h2 class="text-2xl font-extrabold tracking-tight sk-text-primary mb-2">Masuk ke Toko Anda</h2>
            <p class="text-sm font-medium sk-text-muted">Cari toko Anda untuk melanjutkan ke dasbor</p>
        </div>

        <!-- Login Cepat (Hanya Dev Mode) -->
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
        <div v-if="form.errors.search_value" class="mb-6 p-4 sk-bg-danger-soft border border-red-100 rounded-xl flex items-start gap-3">
            <svg class="w-5 h-5 sk-text-danger shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm font-semibold sk-text-danger">{{ form.errors.search_value }}</p>
        </div>

        <!-- Search Type Tabs -->
        <div class="flex p-1 sk-bg-hover rounded-xl mb-6 shadow-inner">
            <KButton 
                v-for="opt in searchOptions"
                :key="opt.value"
                type="button"
                @click="searchType = opt.value"
                class="flex-1 px-3 py-2 text-xs font-bold rounded-lg transition-all duration-200"
                :class="searchType === opt.value
                    ? 'sk-bg-card sk-text-primary shadow-sm border sk-border/50'
                    : 'sk-text-muted hover:sk-text-primary'">
                {{ opt.label }}
            </KButton>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <label class="block text-sm font-bold sk-text-primary mb-2">{{ currentLabel }}</label>
                <div class="relative">
                    <KInput 
                        v-model="form.search_value"
                        type="text"
                        name="search_value"
                        class="w-full pl-11 pr-4 py-3 rounded-xl border sk-border sk-bg-card text-sm font-semibold sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all shadow-sm"
                        :placeholder="currentPlaceholder"
                        required
                        autofocus />
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <!-- Icon: Store -->
                        <svg v-if="searchType === 'name'" class="w-5 h-5 sk-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <!-- Icon: Email -->
                        <svg v-else-if="searchType === 'email'" class="w-5 h-5 sk-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <!-- Icon: Phone -->
                        <svg v-else class="w-5 h-5 sk-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="pt-2">
                <KButton 
                    type="submit"
                    class="w-full flex items-center justify-center px-6 py-3.5 rounded-xl text-white text-sm font-extrabold shadow-md hover:shadow-lg transition-all disabled:opacity-70 hover:-translate-y-0.5"
                    style="background: var(--color-primary)"
                    :disabled="form.processing">
                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    Lanjutkan
                </KButton>
            </div>
            
            <div class="text-center mt-8 pt-6 border-t" :style="{ borderColor: 'var(--border-light)' }">
                <p class="text-sm font-medium sk-text-muted">Belum memiliki toko? 
                    <Link :href="route('register')" class="font-extrabold hover:underline transition-all" :style="{ color: 'var(--color-primary-text)' }">Daftar gratis sekarang</Link>
                </p>
            </div>
        </form>
    </GuestLayout>
</template>

<script setup>
import KButton from '@/Components/KButton.vue';
import KInput from '@/Components/KInput.vue';

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

const tenantSlug = 'demo-toko';
const baseUrl = window.location.protocol + '//' + tenantSlug + '.localhost:' + (window.location.port || '8000');

const demoAccounts = [
    {
        role: 'owner',
        label: 'Owner',
        email: 'demo@serviceku.app',
        icon: '👑',
        bg: 'sk-bg-primary',
        color: 'sk-text-primary-brand',
        url: baseUrl + '/dev-login?role=owner',
    },
    {
        role: 'cs',
        label: 'CS',
        email: 'cs@serviceku.app',
        icon: '💬',
        bg: 'bg-blue-600',
        color: 'sk-text-info',
        url: baseUrl + '/dev-login?role=cs',
    },
    {
        role: 'technician',
        label: 'Teknisi',
        email: 'teknisi@serviceku.app',
        icon: '🔧',
        bg: 'bg-emerald-600',
        color: 'sk-text-success',
        url: baseUrl + '/dev-login?role=technician',
    },
];

const currentLabel = computed(() => {
    return searchOptions.find(o => o.value === searchType.value)?.label || 'Nama Toko';
});

const currentPlaceholder = computed(() => {
    switch (searchType.value) {
        case 'name': return 'contoh: Servis Maju Jaya';
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
