<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold text-dark-900">Tambah Pelanggan</h2>
                    <p class="text-xs text-dark-400 mt-0.5">Isi data pelanggan baru</p>
                </div>
                <Link :href="route('customers.index')" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold border transition-all text-dark-600 border-dark-200 hover:bg-dark-50">
                    ← Kembali
                </Link>
            </div>
        </template>

        <div class="max-w-2xl mx-auto">
            <form @submit.prevent="submit" class="bg-white rounded-xl border shadow-sm p-6" style="border-color: var(--border-color);">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-dark-500 mb-1.5 uppercase tracking-wider">Nama *</label>
                        <input type="text" v-model="form.name" id="name" name="name" class="w-full rounded-lg border text-sm px-3 py-2.5 focus:ring-2 focus:outline-none border-dark-200 text-dark-700 focus:border-blue-500 focus:ring-blue-200 transition-all" required placeholder="Nama lengkap" />
                        <p v-if="form.errors.name" class="mt-1 text-xs" style="color: #e74c3c;">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-dark-500 mb-1.5 uppercase tracking-wider">Telepon</label>
                        <input type="text" v-model="form.phone" id="phone" name="phone" class="w-full rounded-lg border text-sm px-3 py-2.5 focus:ring-2 focus:outline-none border-dark-200 text-dark-700 focus:border-blue-500 focus:ring-blue-200 transition-all" placeholder="08xxx" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-dark-500 mb-1.5 uppercase tracking-wider">Email</label>
                        <input type="email" v-model="form.email" id="email" name="email" class="w-full rounded-lg border text-sm px-3 py-2.5 focus:ring-2 focus:outline-none border-dark-200 text-dark-700 focus:border-blue-500 focus:ring-blue-200 transition-all" placeholder="email@example.com" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-dark-500 mb-1.5 uppercase tracking-wider">Member</label>
                        <select v-model="form.is_member" id="is_member" name="is_member" class="w-full rounded-lg border text-sm px-3 py-2.5 focus:ring-2 focus:outline-none border-dark-200 text-dark-700 focus:border-blue-500 focus:ring-blue-200 transition-all">
                            <option :value="false">Tidak</option>
                            <option :value="true">Ya</option>
                        </select>
                    </div>
                </div>
                <div class="mb-6">
                    <label class="block text-xs font-semibold text-dark-500 mb-1.5 uppercase tracking-wider">Alamat</label>
                    <textarea v-model="form.address" rows="2" id="address" name="address" class="w-full rounded-lg border text-sm px-3 py-2.5 focus:ring-2 focus:outline-none border-dark-200 text-dark-700 focus:border-blue-500 focus:ring-blue-200 transition-all" placeholder="Alamat lengkap"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <Link :href="route('customers.index')" class="px-5 py-2.5 rounded-lg border text-sm font-semibold text-dark-600 border-dark-200 hover:bg-dark-50 transition-all">Batal</Link>
                    <button type="submit" :disabled="form.processing" class="px-5 py-2.5 rounded-lg text-sm font-bold text-white transition-all hover:shadow-md disabled:opacity-50" style="background: var(--accent-primary);">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const form = useForm({
    name: '',
    phone: '',
    email: '',
    address: '',
    is_member: false,
});

const submit = () => form.post(route('customers.store'));
</script>
