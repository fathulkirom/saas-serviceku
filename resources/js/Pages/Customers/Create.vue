<template>
    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-2xl font-bold text-zinc-900 tracking-tight">Tambah Pelanggan Baru</h2>
                    <p class="text-sm text-zinc-500 mt-1">Lengkapi data profil pelanggan untuk mendaftarkan ke sistem CRM.</p>
                </div>
                <Link :href="route('customers.index')" class="px-4 py-2 bg-white border border-zinc-200 rounded-xl text-zinc-700 text-sm font-semibold hover:bg-zinc-50 transition-colors shadow-sm">
                    Batal
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Data Utama Card -->
                <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-zinc-200 bg-zinc-50/50">
                        <h3 class="font-bold text-zinc-900">Informasi Dasar</h3>
                        <p class="text-xs text-zinc-500 mt-1">Data identitas utama pelanggan.</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                                <KInput  type="text" v-model="form.name" class="w-full rounded-xl border border-zinc-300 px-4 py-2.5 text-sm text-zinc-900 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all" required placeholder="Contoh: Budi Santoso" />
                                <p v-if="form.errors.name" class="mt-1.5 text-xs text-red-500 font-medium">{{ form.errors.name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-zinc-700 mb-1.5">No. Whatsapp / Telepon</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    </div>
                                    <KInput  type="text" v-model="form.phone" class="w-full pl-9 rounded-xl border border-zinc-300 px-4 py-2.5 text-sm text-zinc-900 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all" placeholder="081234567890" />
                                </div>
                                <p v-if="form.errors.phone" class="mt-1.5 text-xs text-red-500 font-medium">{{ form.errors.phone }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Alamat Email</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    </div>
                                    <KInput  type="email" v-model="form.email" class="w-full pl-9 rounded-xl border border-zinc-300 px-4 py-2.5 text-sm text-zinc-900 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all" placeholder="budi@example.com" />
                                </div>
                                <p v-if="form.errors.email" class="mt-1.5 text-xs text-red-500 font-medium">{{ form.errors.email }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Status Member</label>
                                <div class="relative">
                                    <KSelect  v-model="form.is_member" class="w-full appearance-none rounded-xl border border-zinc-300 px-4 py-2.5 text-sm text-zinc-900 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all">
                                        <option :value="false">Pelanggan Biasa</option>
                                        <option :value="true">Daftarkan sebagai Member</option>
                                    </KSelect>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </div>
                                <p class="mt-1.5 text-xs text-zinc-500">Member mendapatkan fitur loyalty poin.</p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Alamat Lengkap</label>
                                <KTextarea  v-model="form.address" rows="3" class="w-full rounded-xl border border-zinc-300 px-4 py-2.5 text-sm text-zinc-900 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all resize-none" placeholder="Masukkan alamat lengkap rumah/kantor pelanggan..."></KTextarea>
                                <p v-if="form.errors.address" class="mt-1.5 text-xs text-red-500 font-medium">{{ form.errors.address }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Custom Fields -->
                <div v-if="customFields.length > 0" class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-zinc-200 bg-zinc-50/50">
                        <h3 class="font-bold text-zinc-900">Informasi Tambahan</h3>
                        <p class="text-xs text-zinc-500 mt-1">Data kustom untuk kebutuhan CRM tambahan.</p>
                    </div>
                    <div class="p-6">
                        <DynamicFormFields :fields="customFields" :form-data="form.data" />
                    </div>
                </div>

                <!-- Sticky Action Bar -->
                <div class="sticky bottom-6 z-10 bg-white rounded-2xl border border-zinc-200 shadow-lg p-4 flex items-center justify-between">
                    <p class="text-xs text-zinc-500 font-medium hidden sm:block">
                        Pastikan data pelanggan sudah benar sebelum menyimpan.
                    </p>
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <Link :href="route('customers.index')" class="flex-1 sm:flex-none text-center px-6 py-2.5 bg-white border border-zinc-300 rounded-xl text-zinc-700 text-sm font-bold hover:bg-zinc-50 transition-colors">
                            Batal
                        </Link>
                        <KButton  type="submit" :disabled="form.processing" class="flex-1 sm:flex-none px-8 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-sm hover:shadow-md transition-all disabled:opacity-50 flex items-center justify-center gap-2">
                            <svg v-if="form.processing" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Pelanggan' }}
                        </KButton>
                    </div>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import KButton from '@/Components/KButton.vue';
import KInput from '@/Components/KInput.vue';
import KSelect from '@/Components/KSelect.vue';
import KTextarea from '@/Components/KTextarea.vue';

import { useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DynamicFormFields from '@/Components/DynamicFormFields.vue';

const props = defineProps({
    customFields: { type: Array, default: () => [] },
});

const form = useForm({
    name: '',
    phone: '',
    email: '',
    address: '',
    is_member: false,
    data: {},
});

const submit = () => form.post(route('customers.store'));
</script>
