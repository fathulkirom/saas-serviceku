<template>
    <GuestLayout>
        <h2 class="text-xl font-bold text-dark-900 mb-2">Reset Password</h2>
        <p class="text-sm text-dark-400 mb-6">Masukkan password baru untuk akun Anda.</p>

        <!-- Error Message -->
        <div v-if="form.errors.email" class="mb-4 p-3 bg-accent-50 border border-accent-200 rounded-xl">
            <p class="text-sm text-accent-700">{{ form.errors.email }}</p>
        </div>
        <div v-if="form.errors.password" class="mb-4 p-3 bg-accent-50 border border-accent-200 rounded-xl">
            <p class="text-sm text-accent-700">{{ form.errors.password }}</p>
        </div>

        <form @submit.prevent="submit">
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-dark-600 mb-1">Email</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="input-premium bg-dark-50"
                    required
                    readonly
                    autocomplete="email"
                />
            </div>
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-dark-600 mb-1">Password Baru</label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="input-premium"
                    required
                    minlength="8"
                    autocomplete="new-password"
                />
                <p v-if="form.errors.password" class="mt-1 text-sm text-accent-700">{{ form.errors.password }}</p>
            </div>
            <div class="mb-4">
                <label for="password_confirmation" class="block text-sm font-medium text-dark-600 mb-1">Konfirmasi Password Baru</label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="input-premium"
                    required
                    minlength="8"
                    autocomplete="new-password"
                />
            </div>

            <div class="flex items-center justify-between mt-6">
                <Link :href="route('login')" class="text-sm text-premium-600 hover:text-premium-500 font-semibold">
                    ← Kembali ke Login
                </Link>
                <button
                    type="submit"
                    class="btn-premium-primary disabled:opacity-50"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Menyimpan...' : 'Reset Password' }}
                </button>
            </div>
        </form>
    </GuestLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const props = defineProps({
    token: { type: String, required: true },
    email: { type: String, default: '' },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.update'));
};
</script>
