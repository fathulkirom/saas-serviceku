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
                        <input type="text" v-model="form.app_name" name="app_name" class="w-full rounded-md border-slate-600 shadow-sm" required />
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-slate-300 mb-1">Deskripsi</label>
                        <textarea v-model="form.app_description" rows="2" name="app_description" class="w-full rounded-md border-slate-600 shadow-sm"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Max Tenant</label>
                        <input type="number" v-model="form.max_tenants" name="max_tenants" class="w-full rounded-md border-slate-600 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Notifikasi Email</label>
                        <input type="email" v-model="form.notify_email" name="notify_email" class="w-full rounded-md border-slate-600 shadow-sm" />
                    </div>
                </div>
            </div>

            <div class="rounded-2xl p-6 mb-6 border bg-slate-900/50 border-slate-800 backdrop-blur-xl">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Registrasi Tenant</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" v-model="form.registration_open_bool" name="registration_open_bool" class="rounded border-slate-600 text-indigo-600 shadow-sm" />
                            <span class="ml-2 text-sm text-slate-300">Buka Registrasi</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" v-model="form.require_approval_bool" name="require_approval_bool" class="rounded border-slate-600 text-indigo-600 shadow-sm" />
                            <span class="ml-2 text-sm text-slate-300">Perlu Persetujuan Admin</span>
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Default Plan</label>
                        <input type="text" v-model="form.default_plan_slug" name="default_plan_slug" class="w-full rounded-md border-slate-600 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Masa Trial (hari)</label>
                        <input type="number" v-model="form.default_trial_days" name="default_trial_days" class="w-full rounded-md border-slate-600 shadow-sm" />
                    </div>
                </div>
            </div>

            <div class="rounded-2xl p-6 mb-6 border bg-slate-900/50 border-slate-800 backdrop-blur-xl">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Maintenance</h3>
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" v-model="form.maintenance_mode_bool" name="maintenance_mode_bool" class="rounded border-slate-600 text-indigo-600 shadow-sm" />
                        <span class="ml-2 text-sm text-slate-300">Mode Maintenance</span>
                    </label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Pesan Maintenance</label>
                    <textarea v-model="form.maintenance_message" rows="2" name="maintenance_message" class="w-full rounded-md border-slate-600 shadow-sm"></textarea>
                </div>
            </div>

            <!-- FEATURE FLAGS -->
            <div class="rounded-2xl p-6 mb-6 border bg-slate-900/50 border-slate-800 backdrop-blur-xl">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">🎛️ Feature Flags</h3>
                <p class="text-sm text-slate-400 mb-4">Aktifkan atau nonaktifkan fitur secara global untuk semua tenant.</p>

                <form @submit.prevent="updateFeatureFlags" class="space-y-3">
                    <div v-for="(flag, key) in featureFlags" :key="key" class="flex items-center justify-between py-2 border-b border-gray-100">
                        <div>
                            <label class="text-sm font-medium text-gray-900">{{ flag.label }}</label>
                            <p class="text-xs text-slate-400">{{ flag.description }}</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" v-model="featureFlagsForm[key]" class="sr-only peer" />
                            <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-600 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>
                    <div class="flex justify-end pt-2">
                        <button type="submit" :disabled="featureFlagsForm.processing" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50 text-sm">
                            {{ featureFlagsForm.processing ? 'Menyimpan...' : 'Simpan Feature Flags' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- EMAIL / SMTP SETTINGS -->
            <div class="rounded-2xl p-6 mb-6 border bg-slate-900/50 border-slate-800 backdrop-blur-xl">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Email (SMTP)</h3>
                <p class="text-sm text-slate-400 mb-4">Konfigurasi email untuk notifikasi pendaftaran tenant. Bisa pakai Gmail, SendGrid, atau email domain sendiri.</p>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-300 mb-1">Mail Driver</label>
                    <select v-model="form.mail_driver" name="mail_driver" class="w-full rounded-md border-slate-600 shadow-sm">
                        <option value="log">Log (hanya untuk testing)</option>
                        <option value="smtp">SMTP (kirim email sungguhan)</option>
                    </select>
                </div>

                <div v-if="form.mail_driver === 'smtp'" class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-slate-300 mb-1">SMTP Host</label>
                        <input type="text" v-model="form.mail_host" placeholder="smtp.gmail.com" name="mail_host" class="w-full rounded-md border-slate-600 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Port</label>
                        <input type="number" v-model="form.mail_port" placeholder="587" name="mail_port" class="w-full rounded-md border-slate-600 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Encryption</label>
                        <select v-model="form.mail_encryption" name="mail_encryption" class="w-full rounded-md border-slate-600 shadow-sm">
                            <option value="tls">TLS</option>
                            <option value="ssl">SSL</option>
                            <option value="null">Tidak Ada</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Username</label>
                        <input type="text" v-model="form.mail_username" name="mail_username" class="w-full rounded-md border-slate-600 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Password</label>
                        <input type="password" v-model="form.mail_password" name="mail_password" class="w-full rounded-md border-slate-600 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">From Address</label>
                        <input type="email" v-model="form.mail_from_address" placeholder="notifications@serviceku.my.id" name="mail_from_address" class="w-full rounded-md border-slate-600 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">From Name</label>
                        <input type="text" v-model="form.mail_from_name" placeholder="ServiceKU" name="mail_from_name" class="w-full rounded-md border-slate-600 shadow-sm" />
                    </div>
                </div>

                <!-- Test Email -->
                <div v-if="form.mail_driver === 'smtp'" class="mt-4 pt-4 border-t">
                    <h4 class="text-sm font-medium text-slate-300 mb-2">🔍 Test Email</h4>
                    <p class="text-xs text-slate-400 mb-2">Kirim email test untuk memastikan konfigurasi SMTP berfungsi.</p>
                    <div class="flex gap-2">
                        <input type="email" v-model="testEmail" placeholder="email@example.com"
                            class="flex-1 rounded-md border-slate-600 shadow-sm text-sm" />
                        <button @click="sendTestEmail"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm whitespace-nowrap">
                            Kirim Test
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <button type="submit" :disabled="form.processing" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50">
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </AdminLayout>
</template>

<script setup>
import { useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    settings: { type: Object, default: () => ({}) },
    featureFlags: { type: Object, default: () => ({}) },
});

const testEmail = ref('');

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
    mail_password: props.settings.mail?.mail_password || '',
    mail_from_address: props.settings.mail?.mail_from_address || 'notifications@serviceku.my.id',
    mail_from_name: props.settings.mail?.mail_from_name || 'ServiceKU',
});

const featureFlagsForm = useForm({
    two_factor_auth: props.featureFlags?.two_factor_auth ?? true,
    email_verification: props.featureFlags?.email_verification ?? false,
    custom_fields: props.featureFlags?.custom_fields ?? true,
});

const sendTestEmail = () => {
    if (!testEmail.value) return;
    router.post(route('admin.settings.test-mail'), {
        email: testEmail.value,
    }, {
        preserveScroll: true,
        onSuccess: () => { testEmail.value = ''; },
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
