<template>
    <AdminLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-bold text-slate-100">Pengaturan Payment Gateway</h2>
                <p class="text-sm text-slate-400 mt-0.5">Konfigurasi metode pembayaran tenant</p>
            </div>
        </template>

        <!-- Flash Message -->
        <div v-if="$page.props.flash?.success" class="mb-4 p-4 rounded-xl border flex items-center gap-3" style="background: rgba(34,197,94,0.1); border-color: rgba(34,197,94,0.2);">
            <p class="text-sm text-emerald-300">{{ $page.props.flash.success }}</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Payment Gateway Selection -->
            <div class="lg:col-span-2 space-y-6">
                <div class="rounded-2xl p-6 border" style="background: var(--bg-card); border-color: var(--border-color);">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Pilih Payment Gateway</h3>
                    <form @submit.prevent="submitSettings">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Gateway</label>
                            <div class="space-y-2">
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50"
                                    :class="form.payment_gateway === 'manual' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200'">
                                    <input type="radio" v-model="form.payment_gateway" value="manual" name="payment_gateway" class="mr-3" />
                                    <div>
                                        <p class="font-medium text-gray-900">💰 Manual Transfer</p>
                                        <p class="text-sm text-gray-500">Pelanggan transfer ke rekening, admin konfirmasi manual</p>
                                    </div>
                                </label>
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50"
                                    :class="form.payment_gateway === 'midtrans' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200'">
                                    <input type="radio" v-model="form.payment_gateway" value="midtrans" name="payment_gateway" class="mr-3" />
                                    <div>
                                        <p class="font-medium text-gray-900">🔵 Midtrans</p>
                                        <p class="text-sm text-gray-500">Support: Bank Transfer, GOPAY, QRIS, ShopeePay</p>
                                    </div>
                                </label>
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50"
                                    :class="form.payment_gateway === 'xendit' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200'">
                                    <input type="radio" v-model="form.payment_gateway" value="xendit" name="payment_gateway" class="mr-3" />
                                    <div>
                                        <p class="font-medium text-gray-900">🟠 Xendit</p>
                                        <p class="text-sm text-gray-500">Support: Bank Transfer, QRIS, E-Wallet</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Midtrans Settings -->
                        <div v-if="form.payment_gateway === 'midtrans'" class="border-t pt-4 mt-4 space-y-3">
                            <h4 class="font-medium text-gray-900">Konfigurasi Midtrans</h4>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Merchant ID</label>
                                    <input v-model="form.midtrans_merchant_id" type="text" name="midtrans_merchant_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Client Key</label>
                                    <input v-model="form.midtrans_client_key" type="text" name="midtrans_client_key" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Server Key</label>
                                    <input v-model="form.midtrans_server_key" type="password" name="midtrans_server_key" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Environment</label>
                                    <select v-model="form.midtrans_is_production" name="midtrans_is_production" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                        <option value="false">Sandbox (Development)</option>
                                        <option value="true">Production</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Auto Confirm</label>
                                    <select v-model="form.payment_auto_confirm" name="payment_auto_confirm" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                        <option value="false">Manual</option>
                                        <option value="true">Otomatis</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Xendit Settings -->
                        <div v-if="form.payment_gateway === 'xendit'" class="border-t pt-4 mt-4 space-y-3">
                            <h4 class="font-medium text-gray-900">Konfigurasi Xendit</h4>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">API Key</label>
                                <input v-model="form.xendit_api_key" type="password" name="xendit_api_key" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Auto Confirm</label>
                                <select v-model="form.payment_auto_confirm" name="payment_auto_confirm" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <option value="false">Manual</option>
                                    <option value="true">Otomatis</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex justify-end mt-6 pt-4 border-t">
                            <button type="submit" :disabled="form.processing"
                                class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50 text-sm">
                                Simpan Pengaturan
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Webhook URL Info -->
                <div class="rounded-2xl p-6 border" style="background: var(--bg-card); border-color: var(--border-color);">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">🔗 Webhook URL</h3>
                    <p class="text-sm text-gray-600 mb-3">Daftarkan URL berikut di dashboard payment gateway untuk menerima notifikasi otomatis:</p>
                    <div class="bg-gray-50 rounded-md p-3 font-mono text-sm text-gray-700 break-all">
                        {{ webhookUrl }}
                    </div>
                </div>
            </div>

            <!-- Rekening Manual -->
            <div class="space-y-6">
                <div class="rounded-2xl p-6 border" style="background: var(--bg-card); border-color: var(--border-color);">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">🏦 Rekening Manual</h3>
                    <p class="text-sm text-gray-500 mb-4">Untuk pembayaran transfer manual</p>
                    <form @submit.prevent="submitSettings">
                        <div class="space-y-3 mb-4 pb-4 border-b">
                            <h4 class="text-sm font-medium text-gray-700">Rekening 1</h4>
                            <div>
                                <label class="block text-xs text-gray-500">Bank</label>
                                <input v-model="form.bank_name_1" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500">Atas Nama</label>
                                <input v-model="form.bank_account_name_1" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500">No. Rekening</label>
                                <input v-model="form.bank_account_number_1" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>
                        </div>
                        <div class="space-y-3 mb-4">
                            <h4 class="text-sm font-medium text-gray-700">Rekening 2</h4>
                            <div>
                                <label class="block text-xs text-gray-500">Bank</label>
                                <input v-model="form.bank_name_2" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500">Atas Nama</label>
                                <input v-model="form.bank_account_name_2" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500">No. Rekening</label>
                                <input v-model="form.bank_account_number_2" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Instruksi Pembayaran</label>
                            <textarea v-model="form.payment_instructions" rows="4" name="payment_instructions" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"></textarea>
                        </div>
                        <button type="submit" :disabled="form.processing"
                            class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50 text-sm">
                            Simpan Rekening
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    config: { type: Object, default: () => ({}) },
    bankAccounts: { type: Array, default: () => [] },
});

const webhookUrl = window.location.origin + '/payment/webhook';

const form = useForm({
    payment_gateway: props.config.gateway || 'manual',
    midtrans_merchant_id: props.config.midtrans_merchant_id || '',
    midtrans_client_key: props.config.midtrans_client_key || '',
    midtrans_server_key: props.config.midtrans_server_key || '',
    midtrans_is_production: props.config.midtrans_is_production || 'false',
    xendit_api_key: props.config.xendit_api_key || '',
    payment_auto_confirm: props.config.payment_auto_confirm || 'false',
    payment_instructions: props.config.payment_instructions || '',
    bank_name_1: props.config.bank_name_1 || 'BCA',
    bank_account_name_1: props.config.bank_account_name_1 || '',
    bank_account_number_1: props.config.bank_account_number_1 || '',
    bank_name_2: props.config.bank_name_2 || 'Mandiri',
    bank_account_name_2: props.config.bank_account_name_2 || '',
    bank_account_number_2: props.config.bank_account_number_2 || '',
});

const submitSettings = () => {
    form.post(route('admin.payment-settings.update'));
};
</script>
