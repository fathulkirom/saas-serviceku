<template>
    <GuestLayout>
        <div class="text-center mb-8">
            <h2 class="text-xl font-bold text-zinc-900 mb-2">Lupa Password?</h2>
            <p class="text-sm text-zinc-500">Masukkan email Anda dan kami akan mengirimkan link reset password.</p>
        </div>

        <!-- Success Message -->
        <div v-if="$page.props.flash?.success" class="mb-6 p-4 bg-emerald-50 border border-emerald-100 rounded-xl flex items-start gap-3">
            <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm font-semibold text-emerald-800">{{ $page.props.flash.success }}</p>
        </div>

        <!-- Error Message -->
        <div v-if="form.errors.email" class="mb-6 p-4 bg-red-50 border border-red-100 rounded-xl flex items-start gap-3">
            <svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm font-semibold text-red-800">{{ form.errors.email }}</p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-bold text-zinc-900 mb-2">Email Akun</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="w-full px-4 py-3 rounded-xl border border-zinc-300 bg-white text-sm font-semibold text-zinc-900 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all shadow-sm"
                    placeholder="nama@email.com"
                    required
                    autofocus
                    autocomplete="email"
                />
            </div>

            <div class="pt-2 flex flex-col gap-4">
                <button
                    type="submit"
                    class="w-full flex items-center justify-center px-6 py-3 rounded-xl bg-zinc-900 text-white text-sm font-bold shadow-md hover:bg-zinc-800 focus:ring-2 focus:ring-offset-2 focus:ring-zinc-900 transition-all disabled:opacity-70"
                    :disabled="form.processing"
                >
                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    {{ form.processing ? 'Mengirim...' : 'Kirim Link Reset' }}
                </button>
                <Link :href="route('login')" class="text-center text-sm font-bold text-indigo-600 hover:text-indigo-700 transition-colors">
                    Kembali ke halaman Login
                </Link>
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
