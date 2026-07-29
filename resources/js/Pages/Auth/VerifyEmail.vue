<template>
    <Head title="Verifikasi Email" />
    <div class="min-h-screen flex items-center justify-center bg-gray-50">
        <div class="max-w-md w-full bg-white rounded-xl shadow-lg p-8">
            <div class="text-center mb-6">
                <div class="text-5xl mb-4">📧</div>
                <h1 class="text-2xl font-bold text-gray-800">Verifikasi Email</h1>
                <p class="text-gray-500 mt-2">
                    Link verifikasi telah dikirim ke <strong>{{ user.email }}</strong>.
                    Silakan cek inbox email Anda.
                </p>
            </div>

            <div v-if="$page.props.flash?.success" class="bg-green-50 text-green-700 p-3 rounded-lg mb-4 text-sm">
                {{ $page.props.flash.success }}
            </div>

            <div class="bg-blue-50 text-blue-700 p-4 rounded-lg mb-6 text-sm">
                <p class="font-medium">Tidak menerima email?</p>
                <ul class="list-disc list-inside mt-1 text-blue-600">
                    <li>Cek folder <strong>Spam</strong> atau <strong>Promosi</strong></li>
                    <li>Pastikan alamat email sudah benar</li>
                </ul>
            </div>

            <form @submit.prevent="resend" class="space-y-4">
                <button type="submit"
                    :disabled="form.processing"
                    class="w-full py-3 px-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 transition">
                    <span v-if="form.processing">Mengirim...</span>
                    <span v-else>Kirim Ulang Link Verifikasi</span>
                </button>
            </form>

            <div class="mt-4 text-center">
                <Link :href="route('logout')" method="post" as="button"
                    class="text-sm text-gray-500 hover:text-gray-700">
                    Logout
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

const user = usePage().props.auth?.user ?? {};
const form = useForm({});

function resend() {
    form.post(route('tenant.verification.resend'));
}
</script>
