<template>
    <GuestLayout>
        <div class="text-center mb-8">
            <h2 class="text-xl font-bold text-zinc-900 mb-2">Reset Password</h2>
            <p class="text-sm text-zinc-500">Buat password baru untuk akun Anda agar bisa kembali mengakses dasbor.</p>
        </div>

        <!-- Error Message -->
        <div v-if="form.errors.email || form.errors.password" class="mb-6 p-4 bg-red-50 border border-red-100 rounded-xl flex items-start gap-3">
            <svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="flex-1">
                <p v-if="form.errors.email" class="text-sm font-semibold text-red-800">{{ form.errors.email }}</p>
                <p v-if="form.errors.password" class="text-sm font-semibold text-red-800">{{ form.errors.password }}</p>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <label for="email" class="block text-sm font-bold text-zinc-900 mb-2">Email</label>
                <KInput 
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="w-full px-4 py-3 rounded-xl border border-zinc-200 bg-zinc-50 text-sm font-semibold text-zinc-500 focus:ring-0 outline-none shadow-sm cursor-not-allowed"
                    required
                    readonly
                    autocomplete="email" />
            </div>
            
            <div>
                <label for="password" class="block text-sm font-bold text-zinc-900 mb-2">Password Baru</label>
                <KInput 
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="w-full px-4 py-3 rounded-xl border border-zinc-300 bg-white text-sm font-semibold text-zinc-900 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all shadow-sm"
                    required
                    minlength="8"
                    placeholder="Minimal 8 karakter"
                    autocomplete="new-password" />
            </div>
            
            <div>
                <label for="password_confirmation" class="block text-sm font-bold text-zinc-900 mb-2">Konfirmasi Password Baru</label>
                <KInput 
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="w-full px-4 py-3 rounded-xl border border-zinc-300 bg-white text-sm font-semibold text-zinc-900 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all shadow-sm"
                    required
                    minlength="8"
                    placeholder="Ulangi password baru"
                    autocomplete="new-password" />
            </div>

            <div class="pt-2 flex flex-col gap-4">
                <KButton 
                    type="submit"
                    class="w-full flex items-center justify-center px-6 py-3 rounded-xl bg-zinc-900 text-white text-sm font-bold shadow-md hover:bg-zinc-800 focus:ring-2 focus:ring-offset-2 focus:ring-zinc-900 transition-all disabled:opacity-70"
                    :disabled="form.processing">
                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    {{ form.processing ? 'Menyimpan...' : 'Simpan Password Baru' }}
                </KButton>
                <Link :href="route('login')" class="text-center text-sm font-bold text-indigo-600 hover:text-indigo-700 transition-colors">
                    Batalkan dan kembali ke Login
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>

<script setup>
import KButton from '@/Components/KButton.vue';
import KInput from '@/Components/KInput.vue';

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
