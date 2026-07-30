<template>
    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="Pembayaran" subtitle="Selesaikan pembayaran langganan Anda" />
        </template>

        <div class="max-w-2xl mx-auto space-y-6">
            <!-- Payment Summary -->
            <KCard title="Ringkasan Pembayaran">
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Paket</span>
                        <span class="font-semibold">{{ plan.name }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Invoice</span>
                        <span class="font-mono text-xs">{{ payment.invoice_number }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Jumlah</span>
                        <span class="font-bold text-lg text-indigo-600">Rp {{ formatRupiah(payment.amount) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Batas Bayar</span>
                        <span class="text-orange-600 text-xs">{{ formatDate(payment.expired_at) }}</span>
                    </div>
                </div>
            </KCard>

            <!-- Midtrans Payment -->
            <div v-if="gateway === 'midtrans' && snapToken">
                <KCard title="Pembayaran Online">
                    <p class="text-sm text-gray-600 mb-4">Klik tombol di bawah untuk membayar melalui berbagai metode (Virtual Account, QRIS, GoPay, dll).</p>
                    <button @click="payWithMidtrans"
                        class="w-full py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold transition">
                        Bayar Sekarang Rp {{ formatRupiah(payment.amount) }}
                    </button>
                </KCard>
            </div>

            <!-- Manual Payment -->
            <div v-if="gateway === 'manual'">
                <KCard title="Transfer Manual">
                    <p class="text-sm text-gray-600 mb-4">Transfer ke salah satu rekening berikut:</p>
                    <div v-for="(bank, i) in manualBanks" :key="i" class="bg-gray-50 rounded-lg p-4 mb-3 border">
                        <p class="text-xs text-gray-500 uppercase font-semibold">{{ bank.bank }}</p>
                        <p class="text-lg font-bold text-gray-800 mt-1">{{ bank.account_number }}</p>
                        <p class="text-sm text-gray-600">a.n. {{ bank.account_name }}</p>
                        <button @click="copyToClipboard(bank.account_number)"
                            class="mt-2 text-xs text-indigo-600 hover:text-indigo-800">
                            📋 Salin No. Rekening
                        </button>
                    </div>
                </KCard>
            </div>

            <!-- Upload Proof for Manual -->
            <div v-if="gateway === 'manual'">
                <KCard title="Konfirmasi Pembayaran">
                    <p class="text-sm text-gray-600 mb-3">Upload bukti transfer setelah pembayaran:</p>
                    <form @submit.prevent="submitProof">
                        <input type="file" @change="onFileChange" accept="image/*"
                            class="w-full text-sm mb-3" />
                        <button type="submit" :disabled="uploading"
                            class="w-full py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 transition">
                            {{ uploading ? 'Mengupload...' : 'Kirim Bukti Transfer' }}
                        </button>
                    </form>
                </KCard>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import KCard from '@/Components/KCard.vue';
import axios from 'axios';

const props = defineProps({
    payment: Object,
    plan: Object,
    gateway: String,
    snapToken: String,
    snapUrl: String,
    clientKey: String,
    manualBanks: { type: Array, default: () => [] },
});

const uploading = ref(false);
const proofFile = ref(null);

function formatRupiah(num) {
    return Number(num).toLocaleString('id-ID');
}

function formatDate(date) {
    if (!date) return '-';
    return new Date(date).toLocaleString('id-ID', { dateStyle: 'long', timeStyle: 'short' });
}

function payWithMidtrans() {
    if (window.snap) {
        window.snap.pay(props.snapToken, {
            onSuccess: () => window.location.href = route('payment.callback', { order_id: props.payment.invoice_number }),
            onPending: () => window.location.href = route('payment.callback', { order_id: props.payment.invoice_number }),
            onError: () => alert('Pembayaran gagal. Silakan coba lagi.'),
            onClose: () => { /* user closed popup without paying */ },
        });
    } else {
        // Fallback: direct to Snap redirect URL
        if (props.snapUrl) {
            window.location.href = props.snapUrl;
        }
    }
}

function onFileChange(e) {
    proofFile.value = e.target.files[0] || null;
}

function submitProof() {
    if (!proofFile.value) return;
    uploading.value = true;
    const formData = new FormData();
    formData.append('proof_image', proofFile.value);
    axios.post(route('payment.confirm-manual', props.payment.id), formData)
        .then(() => {
            router.reload();
        })
        .catch(() => {
            alert('Gagal upload. Coba lagi.');
        })
        .finally(() => uploading.value = false);
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Nomor rekening disalin!');
    });
}

// Load Midtrans Snap script
onMounted(() => {
    if (props.gateway === 'midtrans' && props.clientKey) {
        if (!document.getElementById('midtrans-script')) {
            const script = document.createElement('script');
            script.id = 'midtrans-script';
            script.src = 'https://app.midtrans.com/snap/snap.js';
            script.setAttribute('data-client-key', props.clientKey);
            document.body.appendChild(script);
        }
    }
});
</script>
