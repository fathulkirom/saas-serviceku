<template>
    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="Pembayaran" subtitle="Selesaikan pembayaran langganan Anda" />
        </template>

        <div class="max-w-3xl mx-auto space-y-6">
            <!-- Payment Summary -->
            <KCard title="Ringkasan Pembayaran" padding="lg">
                <div class="space-y-4">
                    <div class="flex items-center justify-between py-3 border-b border-zinc-100">
                        <span class="text-zinc-500 font-medium">Paket Langganan</span>
                        <span class="font-bold text-zinc-900">{{ plan.name }}</span>
                    </div>
                    <div class="flex items-center justify-between py-3 border-b border-zinc-100">
                        <span class="text-zinc-500 font-medium">Nomor Invoice</span>
                        <span class="font-mono text-sm text-zinc-900 font-medium bg-zinc-100 px-2 py-1 rounded-md">{{ payment.invoice_number }}</span>
                    </div>
                    <div class="flex items-center justify-between py-3 border-b border-zinc-100">
                        <span class="text-zinc-500 font-medium">Jumlah Tagihan</span>
                        <span class="font-black text-xl text-indigo-600">Rp {{ formatRupiah(payment.amount) }}</span>
                    </div>
                    <div class="flex items-center justify-between py-3">
                        <span class="text-zinc-500 font-medium">Batas Pembayaran</span>
                        <span class="text-orange-600 text-sm font-bold bg-orange-50 px-3 py-1 rounded-full border border-orange-200">{{ formatDate(payment.expired_at) }}</span>
                    </div>
                </div>
            </KCard>

            <!-- Midtrans Payment -->
            <div v-if="gateway === 'midtrans' && snapToken">
                <KCard title="Pembayaran Online" padding="lg" class="border-indigo-100 bg-indigo-50/30">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-zinc-600 mb-5 font-medium leading-relaxed">Klik tombol di bawah untuk membayar melalui berbagai metode instan (Virtual Account, QRIS, E-Wallet, dll) yang diproses secara otomatis.</p>
                            <KButton  @click="payWithMidtrans"
                                class="w-full sm:w-auto px-8 py-3.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 font-bold transition-all shadow-sm hover:shadow-md hover:-translate-y-0.5 flex items-center justify-center gap-2">
                                <span>Bayar Sekarang Rp {{ formatRupiah(payment.amount) }}</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </KButton>
                        </div>
                    </div>
                </KCard>
            </div>

            <!-- Manual Payment -->
            <div v-if="gateway === 'manual'">
                <KCard title="Instruksi Transfer Manual" padding="lg">
                    <p class="text-sm text-zinc-500 mb-6 font-medium">Silakan lakukan transfer ke salah satu rekening bank resmi kami di bawah ini:</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div v-for="(bank, i) in manualBanks" :key="i" class="bg-zinc-50 rounded-2xl p-5 border border-zinc-200 hover:border-indigo-300 transition-colors group relative overflow-hidden">
                            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                                <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 24 24"><path d="M4 10h16v2H4zm0 4h16v2H4zm0 4h16v2H4zM4 6h16v2H4z"/></svg>
                            </div>
                            <p class="text-xs text-zinc-500 uppercase font-bold tracking-wider mb-2">{{ bank.bank }}</p>
                            <p class="text-xl font-black text-zinc-900 font-mono tracking-tight">{{ bank.account_number }}</p>
                            <p class="text-sm text-zinc-600 font-medium mt-1">a.n. {{ bank.account_name }}</p>
                            <KButton  @click="copyToClipboard(bank.account_number)"
                                class="mt-4 text-xs font-bold text-indigo-600 hover:text-indigo-700 bg-indigo-50 px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1.5 w-fit">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                Salin Nomor
                            </KButton>
                        </div>
                    </div>
                </KCard>

                <KCard title="Konfirmasi Pembayaran" padding="lg" class="mt-6 border-indigo-100 bg-indigo-50/30">
                    <p class="text-sm text-zinc-600 mb-5 font-medium">Setelah melakukan transfer, silakan konfirmasi pembayaran Anda ke tim kami dengan melampirkan bukti transfer.</p>
                    <a :href="whatsappUrl" target="_blank"
                        class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-8 py-3.5 bg-emerald-500 text-white rounded-xl hover:bg-emerald-600 font-bold transition-all shadow-sm hover:shadow-md hover:-translate-y-0.5">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                        Konfirmasi via WhatsApp
                    </a>
                </KCard>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import KButton from '@/Components/KButton.vue';

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
