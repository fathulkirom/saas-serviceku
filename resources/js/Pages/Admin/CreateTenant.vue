<template>
    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Tenant Baru</h2>
        </template>

        <!-- Flash Message -->
        <div v-if="$page.props.flash?.success" class="mb-4 p-4 bg-green-50 border border-green-200 rounded-md">
            <p class="text-sm text-green-700">{{ $page.props.flash.success }}</p>
        </div>
        <div v-if="$page.props.flash?.error" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-md">
            <p class="text-sm text-red-700">{{ $page.props.flash.error }}</p>
        </div>

        <div class="rounded-2xl p-6 max-w-2xl border" style="background: var(--bg-card); border-color: var(--border-color);">
            <form @submit.prevent="submit">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Nama Toko</label>
                        <input v-model="form.tenant_name" type="text" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            placeholder="Contoh: Toko Servis ABC" />
                        <p v-if="form.errors.tenant_name" class="mt-1 text-xs text-red-600">{{ form.errors.tenant_name }}</p>
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-sm font-medium text-gray-700">Email Owner</label>
                        <input v-model="form.email" type="email" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            placeholder="owner@email.com" />
                        <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-sm font-medium text-gray-700">Password Owner</label>
                        <input v-model="form.password" type="password" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
                        <p v-if="form.errors.password" class="mt-1 text-xs text-red-600">{{ form.errors.password }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">No. Telepon</label>
                        <input v-model="form.phone" type="text"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Paket Langganan</label>
                        <select v-model="form.plan_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Trial (Gratis)</option>
                            <option v-for="p in plans" :key="p.id" :value="p.id">{{ p.name }} - Rp {{ formatNumber(p.price) }}/bln</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Tipe Bisnis</label>
                        <select v-model="form.business_type"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="full_service">Servis & Sparepart</option>
                            <option value="aksesoris_service">Aksesoris & Servis</option>
                            <option value="aksespare_service">Pusat Servis & Sparepart</option>
                            <option value="gadget_full">Gadget & Servis</option>
                            <option value="retail_only">Retail Saja</option>
                        </select>
                    </div>
                </div>

                <!-- Info -->
                <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-sm text-blue-700">
                        💡 Tenant akan aktif langsung dengan subdomain: <strong>{{ subdomainPreview }}</strong>
                    </p>
                </div>

                <div class="flex justify-end gap-3 mt-6 pt-4 border-t">
                    <Link :href="route('admin.dashboard')"
                        class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                        Batal
                    </Link>
                    <button type="submit" :disabled="form.processing"
                        class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50 text-sm">
                        {{ form.processing ? 'Membuat...' : 'Buat Tenant' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    plans: { type: Array, default: () => [] },
});

const form = useForm({
    tenant_name: '',
    email: '',
    password: '',
    phone: '',
    plan_id: '',
    business_type: 'full_service',
});

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num || 0);

const subdomainPreview = computed(() => {
    const slug = form.tenant_name
        ? form.tenant_name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '')
        : 'nama-toko';
    return slug + '-xxxx.serviceku.my.id';
});

const submit = () => {
    form.post(route('admin.tenant.store'));
};
</script>
