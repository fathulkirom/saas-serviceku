<template>
    <Head title="Verifikasi 2FA" />
    <div class="min-h-screen flex items-center justify-center bg-gray-50">
        <div class="max-w-md w-full bg-white rounded-xl shadow-lg p-8">
            <div class="text-center mb-6">
                <div class="text-5xl mb-4">🔐</div>
                <h1 class="text-2xl font-bold text-gray-800">Verifikasi Dua Langkah</h1>
                <p class="text-gray-500 mt-2">
                    Masukkan kode dari aplikasi authenticator Anda.
                </p>
                <p class="text-sm text-gray-400 mt-1">{{ email }}</p>
            </div>

            <div v-if="$page.props.flash?.success" class="bg-green-50 text-green-700 p-3 rounded-lg mb-4 text-sm">
                {{ $page.props.flash.success }}
            </div>

            <div v-if="form.errors.code" class="bg-red-50 text-red-700 p-3 rounded-lg mb-4 text-sm">
                {{ form.errors.code }}
            </div>

            <!-- TOTP Form -->
            <form v-if="!showBackup && !showEmailCode" @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode Authenticator</label>
                    <input
                        v-model="form.code"
                        type="text"
                        inputmode="numeric"
                        maxlength="6"
                        placeholder="000000"
                        class="w-full text-center text-2xl tracking-[0.5em] px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        autofocus
                    />
                </div>
                <button type="submit"
                    :disabled="form.processing"
                    class="w-full py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 transition">
                    Verifikasi
                </button>
            </form>

            <!-- Backup Code Form -->
            <form v-if="showBackup" @submit.prevent="submitBackup" class="space-y-4">
                <p class="text-sm text-gray-500 mb-2">Masukkan salah satu kode cadangan Anda:</p>
                <div>
                    <input
                        v-model="form.code"
                        type="text"
                        placeholder="XXXXXX-XXXXXX"
                        class="w-full text-center px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>
                <button type="submit"
                    :disabled="form.processing"
                    class="w-full py-3 bg-orange-600 text-white rounded-lg hover:bg-orange-700 disabled:opacity-50 transition">
                    Verifikasi Kode Cadangan
                </button>
                <button type="button" @click="showBackup = false"
                    class="w-full py-2 text-sm text-gray-500 hover:text-gray-700">
                    Kembali
                </button>
            </form>

            <!-- Email Code Form -->
            <form v-if="showEmailCode" @submit.prevent="submitEmailCode" class="space-y-4">
                <div class="bg-blue-50 text-blue-700 p-3 rounded-lg mb-2 text-sm">
                    Kode telah dikirim ke email <strong>{{ email }}</strong>. Cek inbox Anda.
                </div>
                <div>
                    <input
                        v-model="form.code"
                        type="text"
                        inputmode="numeric"
                        maxlength="6"
                        placeholder="000000"
                        class="w-full text-center text-2xl tracking-[0.5em] px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>
                <button type="submit"
                    :disabled="form.processing"
                    class="w-full py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 transition">
                    Verifikasi
                </button>
                <button type="button" @click="showEmailCode = false"
                    class="w-full py-2 text-sm text-gray-500 hover:text-gray-700">
                    Kembali
                </button>
            </form>

            <!-- Options -->
            <div v-if="!showBackup && !showEmailCode" class="mt-4 space-y-2 text-center">
                <button @click="sendEmailCode" class="text-sm text-blue-600 hover:text-blue-800 block w-full">
                    Kirim kode via email
                </button>
                <button @click="showBackup = true" class="text-sm text-gray-500 hover:text-gray-700 block w-full">
                    Gunakan kode cadangan
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    email: String,
});

const showBackup = ref(false);
const showEmailCode = ref(false);

const form = useForm({ code: '' });

function submit() {
    form.post(route('two-factor.verify'));
}

function submitBackup() {
    form.post(route('two-factor.verify'));
}

function sendEmailCode() {
    form.post(route('two-factor.send-email'), {
        preserveState: true,
        onSuccess: () => { showEmailCode.value = true; },
    });
}

function submitEmailCode() {
    form.post(route('two-factor.verify-email'));
}
</script>
