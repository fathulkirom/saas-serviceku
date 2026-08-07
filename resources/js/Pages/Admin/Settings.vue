<template>
    <AdminLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-bold text-slate-100">Pengaturan Sistem</h2>
                <p class="text-sm text-slate-400 mt-0.5">Konfigurasi aplikasi dan pembatasan</p>
            </div>
        </template>

        <form @submit.prevent="updateSettings" class="max-w-3xl">
            <div class="rounded-2xl p-6 mb-6 border bg-slate-900/50 border-slate-800 backdrop-blur-xl">
                <h3 class="text-base font-bold text-slate-100 mb-4">Umum</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-slate-300 mb-1">Nama Aplikasi</label>
                        <KInput  type="text" v-model="form.app_name" name="app_name" class="w-full rounded-md border-slate-600 shadow-sm" required />
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-slate-300 mb-1">Deskripsi</label>
                        <KTextarea  v-model="form.app_description" rows="2" name="app_description" class="w-full rounded-md border-slate-600 shadow-sm"></KTextarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Max Tenant</label>
                        <KInput  type="number" v-model="form.max_tenants" name="max_tenants" class="w-full rounded-md border-slate-600 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Notifikasi Email</label>
                        <KInput  type="email" v-model="form.notify_email" name="notify_email" class="w-full rounded-md border-slate-600 shadow-sm" />
                    </div>
                </div>
            </div>

            <div class="rounded-2xl p-6 mb-6 border bg-slate-900/50 border-slate-800 backdrop-blur-xl">
                <h3 class="text-lg font-semibold text-slate-100 mb-4">Registrasi Tenant</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="flex items-center">
                            <KCheckbox  v-model="form.registration_open_bool" name="registration_open_bool" class="rounded border-slate-600 text-indigo-600 shadow-sm" />
                            <span class="ml-2 text-sm text-slate-300">Buka Registrasi</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <KCheckbox  v-model="form.require_approval_bool" name="require_approval_bool" class="rounded border-slate-600 text-indigo-600 shadow-sm" />
                            <span class="ml-2 text-sm text-slate-300">Perlu Persetujuan Admin</span>
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Default Plan</label>
                        <KInput  type="text" v-model="form.default_plan_slug" name="default_plan_slug" class="w-full rounded-md border-slate-600 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Masa Trial (hari)</label>
                        <KInput  type="number" v-model="form.default_trial_days" name="default_trial_days" class="w-full rounded-md border-slate-600 shadow-sm" />
                    </div>
                </div>
            </div>

            <div class="rounded-2xl p-6 mb-6 border bg-slate-900/50 border-slate-800 backdrop-blur-xl">
                <h3 class="text-lg font-semibold text-slate-100 mb-4">Maintenance</h3>
                <div class="mb-4">
                    <label class="flex items-center">
                        <KCheckbox  v-model="form.maintenance_mode_bool" name="maintenance_mode_bool" class="rounded border-slate-600 text-indigo-600 shadow-sm" />
                        <span class="ml-2 text-sm text-slate-300">Mode Maintenance</span>
                    </label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Pesan Maintenance</label>
                    <KTextarea  v-model="form.maintenance_message" rows="2" name="maintenance_message" class="w-full rounded-md border-slate-600 shadow-sm"></KTextarea>
                </div>
            </div>

            <!-- FEATURE FLAGS -->
            <div class="rounded-2xl p-6 mb-6 border bg-slate-900/50 border-slate-800 backdrop-blur-xl">
                <h3 class="text-lg font-semibold text-slate-100 mb-4">🎛️ Feature Flags</h3>
                <p class="text-sm text-slate-400 mb-4">Aktifkan atau nonaktifkan fitur secara global untuk semua tenant.</p>

                <form @submit.prevent="updateFeatureFlags" class="space-y-3">
                    <div v-for="(flag, key) in featureFlags" :key="key" class="flex items-center justify-between py-2 border-b border-slate-700">
                        <div>
                            <label class="text-sm font-medium text-slate-100">{{ flag.label }}</label>
                            <p class="text-xs text-slate-400">{{ flag.description }}</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <KCheckbox  v-model="featureFlagsForm[key]" class="sr-only peer" />
                            <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-600 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>
                    <div class="flex justify-end pt-2">
                        <KButton  type="submit" :disabled="featureFlagsForm.processing" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50 text-sm">
                            {{ featureFlagsForm.processing ? 'Menyimpan...' : 'Simpan Feature Flags' }}
                        </KButton>
                    </div>
                </form>
            </div>

            <!-- EMAIL TRANSAKSIONAL — single provider-driven mail config (MAIL-UNIFY-01) -->
            <div class="rounded-2xl p-6 mb-6 border bg-slate-900/50 border-slate-800 backdrop-blur-xl">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-100">Email Transaksional</h3>
                        <p class="text-sm text-slate-400">Satu konfigurasi email transaksional (registrasi OTP). Provider menentukan jalur pengiriman.</p>
                    </div>
                    <span v-if="mailStatus.configured" class="text-xs font-bold px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300">✅ Terkonfigurasi</span>
                    <span v-else class="text-xs font-bold px-3 py-1 rounded-full bg-amber-500/20 text-amber-300">⚠️ Belum dikonfigurasi</span>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Provider</label>
                        <KSelect  v-model="form.mail_resend_provider" name="mail_resend_provider" class="w-full rounded-md border-slate-600 shadow-sm">
                            <option value="resend">Resend API</option>
                            <option value="smtp">SMTP</option>
                            <option value="off">Off</option>
                        </KSelect>
                    </div>
                </div>

                <!-- PROVIDER = RESEND -->
                <div v-if="form.mail_resend_provider === 'resend'" class="mt-5 grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-slate-300 mb-1">Resend API Key</label>
                        <KInput  type="password" v-model="form.mail_resend_api_key"
                            :placeholder="mailStatus.has_api_key ? ('Tersimpan: ' + (mailStatus.masked_api_key || '••••') + ' — kosongkan untuk mempertahankan') : 're_... (Resend API key)'"
                            name="mail_resend_api_key" autocomplete="new-password" class="w-full rounded-md border-slate-600 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">From Email</label>
                        <KInput  type="email" v-model="form.mail_resend_from_address" placeholder="noreply@serviceku.my.id" name="mail_resend_from_address" class="w-full rounded-md border-slate-600 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">From Name</label>
                        <KInput  type="text" v-model="form.mail_resend_from_name" placeholder="ServiceKU" name="mail_resend_from_name" class="w-full rounded-md border-slate-600 shadow-sm" />
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-slate-300 mb-1">Reply-To (opsional)</label>
                        <KInput  type="email" v-model="form.mail_resend_reply_to" placeholder="support@serviceku.my.id" name="mail_resend_reply_to" class="w-full rounded-md border-slate-600 shadow-sm" />
                    </div>
                </div>

                <!-- PROVIDER = SMTP -->
                <div v-else-if="form.mail_resend_provider === 'smtp'" class="mt-5 grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-slate-300 mb-1">SMTP Host</label>
                        <KInput  type="text" v-model="form.mail_host" placeholder="smtp.resend.com" name="mail_host" class="w-full rounded-md border-slate-600 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Port</label>
                        <KInput  type="number" v-model="form.mail_port" placeholder="587" name="mail_port" class="w-full rounded-md border-slate-600 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Encryption</label>
                        <KSelect  v-model="form.mail_encryption" name="mail_encryption" class="w-full rounded-md border-slate-600 shadow-sm">
                            <option value="tls">TLS</option>
                            <option value="ssl">SSL</option>
                            <option value="null">Tidak Ada</option>
                        </KSelect>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Username</label>
                        <KInput  type="text" v-model="form.mail_username" name="mail_username" class="w-full rounded-md border-slate-600 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Password</label>
                        <KInput  type="password" v-model="form.mail_password" name="mail_password" autocomplete="new-password"
                            :placeholder="mailStatus.smtp_has_password ? 'Tersimpan — kosongkan untuk mempertahankan' : ''"
                            class="w-full rounded-md border-slate-600 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">From Address</label>
                        <KInput  type="email" v-model="form.mail_from_address" placeholder="notifications@serviceku.my.id" name="mail_from_address" class="w-full rounded-md border-slate-600 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">From Name</label>
                        <KInput  type="text" v-model="form.mail_from_name" placeholder="ServiceKU" name="mail_from_name" class="w-full rounded-md border-slate-600 shadow-sm" />
                    </div>
                </div>

                <!-- PROVIDER = OFF -->
                <div v-else class="mt-5 p-4 rounded-xl border border-slate-700 bg-slate-800/40">
                    <p class="text-sm font-medium text-slate-400">Email transaksional dinonaktifkan. Pilih Resend API atau SMTP untuk mengaktifkan.</p>
                </div>

                <p v-if="mailStatus.last_test_result" class="text-xs mt-3">
                    <span :class="mailStatus.last_test_result === 'success' ? 'text-emerald-400' : 'text-red-400'">
                        {{ mailStatus.last_test_result === 'success' ? '✅ Test terakhir: berhasil' : '❌ Test terakhir: gagal' }}
                    </span>
                    <span class="text-slate-500"> · {{ mailStatus.last_test_at || '' }}</span>
                </p>

                <div v-if="form.mail_resend_provider !== 'off'" class="mt-4 pt-4 border-t border-slate-700">
                    <h4 class="text-sm font-medium text-slate-300 mb-2">🔍 Kirim Email Tes</h4>
                    <p class="text-xs text-slate-400 mb-2">Email Tujuan Tes hanya menentukan penerima email tes — tidak mengubah Reply-To atau pengaturan tersimpan.</p>
                    <div class="flex gap-2">
                        <KInput  type="email" v-model="testEmailRecipient" placeholder="email@example.com"
                            class="flex-1 rounded-md border-slate-600 shadow-sm text-sm" />
                        <KButton  @click="sendTestEmail"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm whitespace-nowrap">
                            Kirim Test
                        </KButton>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <KButton  type="submit" :disabled="form.processing" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50">
                    Simpan Pengaturan
                </KButton>
            </div>
        </form>
    </AdminLayout>
</template>

<script setup>
import KButton from '@/Components/KButton.vue';
import KInput from '@/Components/KInput.vue';
import KSelect from '@/Components/KSelect.vue';
import KTextarea from '@/Components/KTextarea.vue';
import KCheckbox from '@/Components/KCheckbox.vue';

import { useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    settings: { type: Object, default: () => ({}) },
    featureFlags: { type: Object, default: () => ({}) },
});

// PILOT-MAIL-04R / MAIL-UNIFY-01 — transactional mail status (masked, never raw).
// Reflects the CURRENTLY selected provider (resend/smtp/off).
const mailStatus = computed(() => props.settings.mail_resend || {});

// MAIL-UI-FIX-01 — dedicated TEMPORARY recipient for the test email (any
// provider). Separate from the persistent Reply-To setting and never persisted
// into system_settings and never becomes from/reply-to.
const testEmailRecipient = ref('');

const form = useForm({
    app_name: props.settings.general?.app_name || 'ServiceKU',
    app_description: props.settings.general?.app_description || '',
    max_tenants: props.settings.general?.max_tenants || '100',
    notify_email: props.settings.general?.notify_email || '',
    registration_open_bool: props.settings.registration?.registration_open === 'true',
    require_approval_bool: props.settings.registration?.require_approval === 'true',
    default_plan_slug: props.settings.registration?.default_plan_slug || 'trial',
    default_trial_days: props.settings.registration?.default_trial_days || '14',
    maintenance_mode_bool: props.settings.maintenance?.maintenance_mode === 'true',
    maintenance_message: props.settings.maintenance?.maintenance_message || '',
    mail_driver: props.settings.mail?.mail_driver || 'log',
    mail_host: props.settings.mail?.mail_host || '',
    mail_port: props.settings.mail?.mail_port || '587',
    mail_encryption: props.settings.mail?.mail_encryption || 'tls',
    mail_username: props.settings.mail?.mail_username || '',
    // MAIL-UNIFY-01: SMTP password is masked server-side; the form stays empty
    // and a placeholder indicates a stored password (blank update preserves).
    mail_password: '',
    mail_from_address: props.settings.mail?.mail_from_address || 'notifications@serviceku.my.id',
    mail_from_name: props.settings.mail?.mail_from_name || 'ServiceKU',
    // PILOT-MAIL-04R — transactional mail (Resend)
    mail_resend_provider: props.settings.mail_resend?.provider || 'off',
    mail_resend_api_key: '',
    mail_resend_from_address: props.settings.mail_resend?.from_address || 'noreply@serviceku.my.id',
    mail_resend_from_name: props.settings.mail_resend?.from_name || 'ServiceKU',
    mail_resend_reply_to: props.settings.mail_resend?.reply_to || '',
});

const featureFlagsForm = useForm({
    two_factor_auth: props.featureFlags?.two_factor_auth?.enabled ?? true,
    email_verification: props.featureFlags?.email_verification?.enabled ?? false,
    custom_fields: props.featureFlags?.custom_fields?.enabled ?? true,
});

// MAIL-UNIFY-01 — ONE test-mail handler for the selected provider. Backend
// routes by provider (resend/smtp/off). Sends only the temporary recipient;
// never touches any stored setting (Reply-To / From / credentials).
const sendTestEmail = () => {
    if (!testEmailRecipient.value) return;
    router.post(route('admin.settings.test-mail'), {
        email: testEmailRecipient.value,
    }, {
        preserveScroll: true,
        onSuccess: () => { testEmailRecipient.value = ''; },
    });
};

const updateSettings = () => {
    form.post(route('admin.settings.update'), {
        data: {
            ...form.data(),
            registration_open: form.registration_open_bool ? 'true' : 'false',
            require_approval: form.require_approval_bool ? 'true' : 'false',
            maintenance_mode: form.maintenance_mode_bool ? 'true' : 'false',
        },
    });
};

const updateFeatureFlags = () => {
    featureFlagsForm.post(route('admin.settings.feature-flags'), {
        data: {
            feature_two_factor_auth: featureFlagsForm.two_factor_auth ? 'true' : 'false',
            feature_email_verification: featureFlagsForm.email_verification ? 'true' : 'false',
            feature_custom_fields: featureFlagsForm.custom_fields ? 'true' : 'false',
        },
    });
};
</script>
