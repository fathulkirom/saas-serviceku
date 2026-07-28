<template>
    <GuestLayout>
        <h2 class="text-xl font-bold text-dark-900 mb-2">Lupa Password</h2>
        <p class="text-sm text-dark-400 mb-6">Masukkan email Anda, kami akan kirim link reset password.</p>

        <!-- Success Message -->
        <div v-if="$page.props.flash?.success" class="mb-4 p-3 bg-success-50 border border-success-200 rounded-xl">
            <p class="text-sm text-success-700">{{ $page.props.flash.success }}</p>
        </div>

        <!-- Error Message -->
        <div v-if="form.errors.email" class="mb-4 p-3 bg-accent-50 border border-accent-200 rounded-xl">
            <p class="text-sm text-accent-700">{{ form.errors.email }}</p>
        </div>

        <form @submit.prevent="submit">
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-dark-600 mb-1">Email</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="input-premium"
                    placeholder="nama@email.com"
                    required
                    autofocus
                    autocomplete="email"
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
                    {{ form.processing ? 'Mengirim...' : 'Kirim Link Reset' }}
                </button>
            </div>
        </form>
    </GuestLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>
