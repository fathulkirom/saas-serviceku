<template>
    <div class="space-y-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">🔐 Autentikasi Dua Langkah (2FA)</h3>
            <p class="text-gray-500 text-sm mb-6">
                Tambahkan lapisan keamanan ekstra ke akun Anda menggunakan aplikasi authenticator seperti Google Authenticator atau Authy.
            </p>

            <div v-if="loading" class="text-center py-8 text-gray-500">Memuat...</div>

            <!-- 2FA is OFF -->
            <div v-if="!loading && !enabled">
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <p class="text-yellow-800 text-sm font-medium">⚠️ 2FA Belum Diaktifkan</p>
                    <p class="text-yellow-600 text-sm mt-1">Akun Anda belum memiliki autentikasi dua langkah. Aktifkan sekarang untuk keamanan ekstra.</p>
                </div>

                <button @click="enable2FA"
                    :disabled="processing"
                    class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 transition">
                    Aktifkan 2FA
                </button>

                <!-- QR Code Step -->
                <div v-if="showQr" class="mt-6 space-y-4">
                    <div class="border rounded-lg p-6 bg-gray-50">
                        <p class="text-sm font-medium text-gray-700 mb-3">Scan QR Code dengan aplikasi authenticator:</p>
                        <div class="flex justify-center mb-4" v-html="qrSvg"></div>
                        <p class="text-xs text-gray-500 text-center">Atau masukkan kode manual: <code class="bg-gray-200 px-2 py-1 rounded">{{ secret }}</code></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Masukkan kode 6 digit dari aplikasi</label>
                        <input v-model="confirmCode" type="text" inputmode="numeric" maxlength="6"
                            class="w-full text-center text-2xl tracking-[0.5em] px-4 py-3 border rounded-lg"
                            placeholder="000000" />
                    </div>

                    <div v-if="confirmError" class="text-red-600 text-sm">{{ confirmError }}</div>

                    <button @click="confirm2FA"
                        :disabled="confirmProcessing"
                        class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 transition">
                        Konfirmasi & Aktifkan
                    </button>
                </div>
            </div>

            <!-- 2FA is ON -->
            <div v-if="!loading && enabled">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <p class="text-green-800 text-sm font-medium">✅ 2FA Aktif</p>
                    <p class="text-green-600 text-sm mt-1">Akun Anda dilindungi dengan autentikasi dua langkah.</p>
                </div>

                <!-- Recovery Codes -->
                <div v-if="recoveryCodes.length > 0" class="mb-6">
                    <p class="text-sm font-medium text-gray-700 mb-2">Kode Cadangan</p>
                    <p class="text-xs text-gray-500 mb-3">Simpan kode-kode ini di tempat aman. Setiap kode hanya bisa digunakan sekali.</p>
                    <div class="bg-gray-900 text-green-400 p-4 rounded-lg font-mono text-sm">
                        <div v-for="(code, i) in recoveryCodes" :key="i" class="py-1">{{ code }}</div>
                    </div>
                    <button @click="regenerateCodes" class="mt-2 text-sm text-blue-600 hover:text-blue-800">
                        🔄 Generate ulang kode cadangan
                    </button>
                </div>

                <button @click="disable2FA"
                    :disabled="processing"
                    class="px-6 py-3 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 disabled:opacity-50 transition">
                    Nonaktifkan 2FA
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const emit = defineEmits(['message']);

const enabled = ref(false);
const loading = ref(true);
const processing = ref(false);
const showQr = ref(false);
const qrSvg = ref('');
const secret = ref('');
const confirmCode = ref('');
const confirmProcessing = ref(false);
const confirmError = ref('');
const recoveryCodes = ref([]);

onMounted(fetchStatus);

function fetchStatus() {
    axios.get(route('two-factor.status'))
        .then(res => {
            enabled.value = res.data.enabled;
            if (enabled.value) {
                recoveryCodes.value = res.data.recovery_codes ?? [];
            }
        })
        .finally(() => loading.value = false);
}

function enable2FA() {
    processing.value = true;
    axios.post(route('two-factor.enable'))
        .then(res => {
            qrSvg.value = res.data.qr_code;
            secret.value = res.data.secret;
            showQr.value = true;
            emit('message', { type: 'info', text: 'Scan QR code dengan aplikasi authenticator, lalu masukkan kode untuk konfirmasi.' });
        })
        .catch(() => emit('message', { type: 'error', text: 'Gagal mengaktifkan 2FA.' }))
        .finally(() => processing.value = false);
}

function confirm2FA() {
    if (confirmCode.value.length !== 6) {
        confirmError.value = 'Masukkan 6 digit kode dari aplikasi authenticator.';
        return;
    }
    confirmError.value = '';
    confirmProcessing.value = true;
    axios.post(route('two-factor.confirm'), { code: confirmCode.value })
        .then(res => {
            enabled.value = true;
            showQr.value = false;
            recoveryCodes.value = res.data.recovery_codes ?? [];
            emit('message', { type: 'success', text: '✅ 2FA berhasil diaktifkan! Simpan kode cadangan Anda.' });
        })
        .catch(err => {
            confirmError.value = err.response?.data?.errors?.code?.[0] || 'Kode tidak valid. Coba lagi.';
        })
        .finally(() => confirmProcessing.value = false);
}

function disable2FA() {
    if (!confirm('Yakin ingin menonaktifkan 2FA? Keamanan akun akan berkurang.')) return;
    processing.value = true;
    axios.post(route('two-factor.disable'))
        .then(() => {
            enabled.value = false;
            showQr.value = false;
            recoveryCodes.value = [];
            emit('message', { type: 'success', text: '2FA dinonaktifkan.' });
        })
        .catch(() => emit('message', { type: 'error', text: 'Gagal menonaktifkan 2FA.' }))
        .finally(() => processing.value = false);
}

function regenerateCodes() {
    if (!confirm('Kode cadangan lama akan tidak berlaku. Lanjutkan?')) return;
    axios.post(route('two-factor.regenerate-codes'))
        .then(res => {
            recoveryCodes.value = res.data.recovery_codes ?? [];
            emit('message', { type: 'success', text: 'Kode cadangan baru telah digenerate.' });
        })
        .catch(() => emit('message', { type: 'error', text: 'Gagal generate kode.' }));
}
</script>
