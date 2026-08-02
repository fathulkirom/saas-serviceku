<template>
    <AdminLayout>
        <template #header>
            <h2 class="font-black text-2xl text-slate-100 tracking-tight">Tambah Tenant Baru</h2>
            <p class="text-sm font-medium text-slate-400 mt-1">Daftarkan pengguna tenant baru ke dalam sistem</p>
        </template>

        <!-- Flash Message -->
        <div v-if="$page.props.flash?.success" class="mb-5 p-4 rounded-xl border flex items-center gap-3 animate-slide-down bg-emerald-500/10 border-emerald-500/20">
            <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <p class="text-sm text-emerald-300 font-medium">{{ $page.props.flash.success }}</p>
        </div>
        <div v-if="$page.props.flash?.error" class="mb-5 p-4 rounded-xl border flex items-center gap-3 animate-slide-down bg-rose-500/10 border-rose-500/20">
            <svg class="w-5 h-5 text-rose-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-sm text-rose-300 font-medium">{{ $page.props.flash.error }}</p>
        </div>

        <div class="rounded-2xl p-6 max-w-2xl border bg-slate-800/50 border-slate-700/50 backdrop-blur-xl shadow-sm">
            <form @submit.prevent="submit">
                <div class="grid grid-cols-2 gap-5">
                    <div class="col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Nama Toko</label>
                        <KInput  v-model="form.tenant_name" type="text" required
                            class="w-full rounded-xl text-sm py-2.5 px-4 border transition-all duration-200 focus:ring-2 focus:ring-indigo-500/50 focus:outline-none bg-slate-900/50 border-slate-700 text-slate-200 placeholder-slate-500"
                            placeholder="Contoh: Toko Servis ABC" />
                        <p v-if="form.errors.tenant_name" class="mt-1.5 text-xs font-medium text-rose-400">{{ form.errors.tenant_name }}</p>
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Email Owner</label>
                        <KInput  v-model="form.email" type="email" required
                            class="w-full rounded-xl text-sm py-2.5 px-4 border transition-all duration-200 focus:ring-2 focus:ring-indigo-500/50 focus:outline-none bg-slate-900/50 border-slate-700 text-slate-200 placeholder-slate-500"
                            placeholder="owner@email.com" />
                        <p v-if="form.errors.email" class="mt-1.5 text-xs font-medium text-rose-400">{{ form.errors.email }}</p>
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Password Owner</label>
                        <KInput  v-model="form.password" type="password" required
                            class="w-full rounded-xl text-sm py-2.5 px-4 border transition-all duration-200 focus:ring-2 focus:ring-indigo-500/50 focus:outline-none bg-slate-900/50 border-slate-700 text-slate-200" />
                        <p v-if="form.errors.password" class="mt-1.5 text-xs font-medium text-rose-400">{{ form.errors.password }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">No. Telepon</label>
                        <KInput  v-model="form.phone" type="text"
                            class="w-full rounded-xl text-sm py-2.5 px-4 border transition-all duration-200 focus:ring-2 focus:ring-indigo-500/50 focus:outline-none bg-slate-900/50 border-slate-700 text-slate-200 placeholder-slate-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Paket Langganan</label>
                        <KSelect  v-model="form.plan_id"
                            class="w-full rounded-xl text-sm py-2.5 px-4 border transition-all duration-200 focus:ring-2 focus:ring-indigo-500/50 focus:outline-none bg-slate-900/50 border-slate-700 text-slate-200">
                            <option value="">Trial (Gratis)</option>
                            <option v-for="p in plans" :key="p.id" :value="p.id">{{ p.name }} - Rp {{ formatNumber(p.price) }}/bln</option>
                        </KSelect>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Tipe Bisnis</label>
                        <KSelect  v-model="form.business_type"
                            class="w-full rounded-xl text-sm py-2.5 px-4 border transition-all duration-200 focus:ring-2 focus:ring-indigo-500/50 focus:outline-none bg-slate-900/50 border-slate-700 text-slate-200">
                            <option value="full_service">Servis & Sparepart</option>
                            <option value="aksesoris_service">Aksesoris & Servis</option>
                            <option value="aksespare_service">Pusat Servis & Sparepart</option>
                            <option value="gadget_full">Gadget & Servis</option>
                            <option value="retail_only">Retail Saja</option>
                        </KSelect>
                    </div>
                </div>

                <!-- Info -->
                <div class="mt-6 p-4 rounded-xl border flex items-start gap-3 bg-blue-500/10 border-blue-500/20">
                    <span class="text-lg opacity-80 mt-0.5">💡</span>
                    <p class="text-sm font-medium text-blue-300 leading-relaxed">
                        Tenant akan aktif langsung dengan subdomain: <strong class="text-blue-200 font-mono tracking-tight">{{ subdomainPreview }}</strong>
                    </p>
                </div>

                <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-slate-700/50">
                    <Link :href="route('admin.dashboard')"
                        class="px-5 py-2.5 rounded-xl text-sm font-bold transition-colors text-slate-300 bg-slate-700/50 hover:bg-slate-700">
                        Batal
                    </Link>
                    <KButton  type="submit" :disabled="form.processing"
                        class="px-6 py-2.5 rounded-xl text-sm font-bold text-white transition-all shadow-sm hover:shadow-md hover:-translate-y-0.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:hover:translate-y-0 flex items-center gap-2">
                        <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        {{ form.processing ? 'Membuat...' : 'Buat Tenant' }}
                    </KButton>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

<script setup>
import KButton from '@/Components/KButton.vue';
import KInput from '@/Components/KInput.vue';
import KSelect from '@/Components/KSelect.vue';

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
