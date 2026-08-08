<template>
    <GuestLayout>
        <!-- Step 1: Pilih Paket -->
        <div v-if="step === 'plan'" class="w-full max-w-[600px] mx-auto">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-black sk-text-primary mb-2 tracking-tight">Pilih Paket Langganan</h2>
                <p class="text-sm font-medium sk-text-muted">Mulai trial gratis atau pilih paket yang paling sesuai dengan kebutuhan toko Anda.</p>
            </div>

            <div class="grid gap-5">
                <div v-for="p in plans" :key="p.id"
                    @click="selectPlan(p)"
                    class="p-5 rounded-2xl border-2 cursor-pointer transition-all duration-200 sk-bg-card"
                    :class="selectedPlan?.id === p.id
                        ? 'border-zinc-900 shadow-md ring-4 ring-zinc-900/5'
                        : 'sk-border hover:border-zinc-400 hover:shadow-sm'">

                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-bold sk-text-primary">{{ p.name }}</h3>
                            <div class="flex items-baseline gap-2 mt-1">
                                <template v-if="p.is_promo_active">
                                    <span class="text-xs font-semibold sk-text-muted line-through">Rp {{ formatNumber(p.price) }}</span>
                                    <span class="text-2xl font-black sk-text-primary tracking-tight">Rp {{ formatNumber(p.promo_price) }}</span>
                                    <span class="px-2 py-0.5 text-[10px] font-black sk-bg-danger-soft sk-text-danger rounded-md tracking-wider">DISKON {{ p.discount_percent }}%</span>
                                </template>
                                <template v-else-if="p.slug === 'trial'">
                                    <span class="text-2xl font-black sk-text-success tracking-tight">Gratis</span>
                                </template>
                                <template v-else>
                                    <span class="text-2xl font-black sk-text-primary tracking-tight">Rp {{ formatNumber(p.price) }}</span>
                                </template>
                                <span class="text-xs font-bold sk-text-muted">/bln</span>
                            </div>
                        </div>
                        <span v-if="p.slug === 'trial'" class="px-2.5 py-1 text-[10px] font-black tracking-wider sk-bg-success-soft sk-text-success rounded-full whitespace-nowrap uppercase">
                            Trial {{ p.trial_days }} Hari
                        </span>
                    </div>

                    <p v-if="p.description" class="text-sm font-medium sk-text-muted mt-3">{{ p.description }}</p>

                    <div class="flex flex-wrap gap-2 mt-4">
                        <span v-for="(level, key) in featureSummary(p.features)" :key="key"
                            class="px-2.5 py-1 text-[10px] font-bold tracking-wide rounded-md border"
                            :class="level === 'full' ? 'sk-bg-primary-soft sk-text-primary-brand sk-border-primary' : 'sk-bg-hover sk-text-muted sk-border'">
                            {{ featureLabels[key] || key }}
                        </span>
                    </div>

                    <div class="mt-5 pt-4 border-t sk-border-light">
                        <KButton  type="button"
                            class="w-full py-2.5 text-sm font-bold rounded-xl transition-colors flex items-center justify-center gap-2"
                            :class="selectedPlan?.id === p.id
                                ? 'bg-zinc-900 text-white'
                                : 'sk-bg-hover sk-text-secondary group-hover:bg-zinc-200'">
                            <svg v-if="selectedPlan?.id === p.id" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            {{ selectedPlan?.id === p.id ? 'Paket Dipilih' : 'Pilih Paket Ini' }}
                        </KButton>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Form Registrasi -->
        <div v-else class="w-full">
            <div class="mb-8">
                <KButton  @click="step = 'plan'" class="text-xs font-bold sk-text-muted hover:sk-text-primary mb-4 flex items-center gap-1 transition-colors uppercase tracking-wider">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Ganti Paket
                </KButton>
                <h2 class="text-2xl font-black sk-text-primary tracking-tight">Lengkapi Data Toko</h2>
                <div class="mt-2 p-3 sk-bg-hover border sk-border rounded-xl flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm font-medium sk-text-primary">
                        Paket terpilih: <span class="font-bold sk-text-primary">{{ selectedPlan?.name }}</span>
                        <template v-if="selectedPlan?.slug === 'trial'">
                            <span class="sk-text-success font-bold ml-1">(Gratis {{ selectedPlan.trial_days }} hari)</span>
                        </template>
                        <template v-else>
                            <span class="sk-text-muted ml-1">
                                (Rp {{ formatNumber(selectedPlan?.effective_price || selectedPlan?.price) }}/bln)
                            </span>
                        </template>
                    </p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <div>
                    <label class="block text-sm font-bold sk-text-primary mb-2">Nama Toko</label>
                    <KInput  v-model="form.tenant_name" type="text" required
                        class="w-full px-4 py-3 rounded-xl border sk-border sk-bg-card text-sm font-semibold sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all shadow-sm"
                        placeholder="Contoh: Servis Maju Jaya" />
                    <p v-if="form.errors.tenant_name" class="mt-1.5 text-xs font-semibold sk-text-danger">{{ form.errors.tenant_name }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-bold sk-text-primary mb-2">Nama Pemilik</label>
                    <KInput  v-model="form.name" type="text" required
                        class="w-full px-4 py-3 rounded-xl border sk-border sk-bg-card text-sm font-semibold sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all shadow-sm"
                        placeholder="Nama lengkap pemilik" />
                    <p v-if="form.errors.name" class="mt-1.5 text-xs font-semibold sk-text-danger">{{ form.errors.name }}</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-bold sk-text-primary mb-2">Email</label>
                        <KInput  v-model="form.email" type="email" required
                            class="w-full px-4 py-3 rounded-xl border sk-border sk-bg-card text-sm font-semibold sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all shadow-sm"
                            placeholder="owner@email.com" />
                        <p v-if="form.errors.email" class="mt-1.5 text-xs font-semibold sk-text-danger">{{ form.errors.email }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-bold sk-text-primary mb-2">No. Telepon / WhatsApp</label>
                        <KInput  v-model="form.phone" type="text"
                            class="w-full px-4 py-3 rounded-xl border sk-border sk-bg-card text-sm font-semibold sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all shadow-sm"
                            placeholder="0812..." />
                        <p v-if="form.errors.phone" class="mt-1.5 text-xs font-semibold sk-text-danger">{{ form.errors.phone }}</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold sk-text-primary mb-2">Tipe Bisnis</label>
                    <KSelect  v-model="form.business_type"
                        class="w-full px-4 py-3 rounded-xl border sk-border sk-bg-card text-sm font-semibold sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all shadow-sm">
                        <option v-if="filteredBusinessTypes.length === 0" value="">Pilih tipe bisnis...</option>
                        <option v-for="(label, key) in filteredBusinessTypes" :key="key" :value="key">{{ label }}</option>
                    </KSelect>
                    <p v-if="form.errors.business_type" class="mt-1.5 text-xs font-semibold sk-text-danger">{{ form.errors.business_type }}</p>
                    <p v-if="!filteredBusinessTypes.length" class="mt-1.5 text-xs font-medium sk-text-muted">Semua tipe bisnis didukung untuk paket ini.</p>
                </div>

                <!-- Voucher Code -->
                <div>
                    <label class="block text-sm font-bold sk-text-primary mb-2">Kode Promo <span class="text-xs font-medium sk-text-muted font-normal">(opsional)</span></label>
                    <div class="flex gap-2">
                        <KInput  v-model="voucherCode" type="text"
                            class="flex-1 px-4 py-3 rounded-xl border sk-border sk-bg-card text-sm font-bold sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all shadow-sm uppercase tracking-wider"
                            placeholder="Masukkan kode promo"
                            @input="voucherStatus = null" />
                        <KButton  type="button" @click="applyVoucher"
                            class="px-6 py-3 rounded-xl sk-bg-card border sk-border sk-text-primary text-sm font-bold shadow-sm hover:sk-bg-hover transition-colors disabled:opacity-50"
                            :disabled="voucherLoading || !voucherCode">
                            {{ voucherLoading ? '...' : 'Gunakan' }}
                        </KButton>
                    </div>
                    <!-- Voucher Success -->
                    <div v-if="voucherStatus?.valid" class="mt-3 p-4 sk-bg-success-soft border border-emerald-100 rounded-xl">
                        <p class="text-sm font-bold sk-text-success flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            {{ voucherStatus.message }}
                        </p>
                        <p v-if="voucherStatus.discount > 0" class="text-xs font-medium sk-text-success mt-2">
                            Harga: <span class="line-through">Rp {{ formatNumber(voucherStatus.original_price) }}</span> → <strong class="sk-text-success">Rp {{ formatNumber(voucherStatus.final_price) }}</strong>
                        </p>
                        <p v-if="voucherStatus.extra_months" class="text-xs font-bold sk-text-success mt-1">
                            🎁 + {{ voucherStatus.extra_months }} bulan gratis langganan
                        </p>
                    </div>
                    <!-- Voucher Error -->
                    <div v-else-if="voucherStatus?.message" class="mt-3 p-3 sk-bg-danger-soft border border-red-100 rounded-xl">
                        <p class="text-sm font-bold sk-text-danger">✕ {{ voucherStatus.message }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-4 border-t sk-border-light">
                    <div>
                        <label class="block text-sm font-bold sk-text-primary mb-2">Password Akun</label>
                        <KInput  v-model="form.password" type="password" required minlength="8"
                            class="w-full px-4 py-3 rounded-xl border sk-border sk-bg-card text-sm font-semibold sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all shadow-sm"
                            placeholder="Minimal 8 karakter" />
                        <p v-if="form.errors.password" class="mt-1.5 text-xs font-semibold sk-text-danger">{{ form.errors.password }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-bold sk-text-primary mb-2">Konfirmasi Password</label>
                        <KInput  v-model="form.password_confirmation" type="password" required minlength="8"
                            class="w-full px-4 py-3 rounded-xl border sk-border sk-bg-card text-sm font-semibold sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all shadow-sm"
                            placeholder="Ulangi password" />
                    </div>
                </div>

                <div class="pt-6">
                    <KButton  type="submit"
                        class="w-full flex items-center justify-center px-6 py-4 rounded-xl bg-zinc-900 text-white text-base font-black tracking-wide shadow-md hover:bg-zinc-800 focus:ring-2 focus:ring-offset-2 focus:ring-zinc-900 transition-all disabled:opacity-70"
                        :disabled="form.processing">
                        <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        {{ form.processing ? 'Sedang Memproses...' : 'Daftar Sekarang' }}
                    </KButton>
                    
                    <div class="text-center mt-6">
                        <p class="text-sm font-medium sk-text-muted">Sudah memiliki akun? 
                            <Link :href="route('login')" class="sk-text-primary-brand hover:sk-text-primary-brand font-bold ml-1">Masuk ke Dasbor</Link>
                        </p>
                    </div>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>

<script setup>
import KButton from '@/Components/KButton.vue';
import KInput from '@/Components/KInput.vue';
import KSelect from '@/Components/KSelect.vue';

import { ref, computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const props = defineProps({
    plans: { type: Array, default: () => [] },
    businessTypes: { type: Object, default: () => ({}) },
});

const step = ref('plan');
const selectedPlan = ref(null);

const featureLabels = {
    services: 'Servis',
    customers: 'Pelanggan',
    products: 'Produk',
    sales: 'POS',
    reports: 'Laporan',
    settings: 'Pengaturan',
    multi_branch: 'Multi Cabang',
    transfer_stock: 'Transfer Stok',
    users: 'User',
    expenses: 'Biaya',
    purchases: 'Pembelian',
};

// Filter tipe bisnis berdasarkan paket yang dipilih
const filteredBusinessTypes = computed(() => {
    if (!selectedPlan.value) return props.businessTypes;
    const allowed = selectedPlan.value.business_types;
    if (!allowed || allowed.length === 0) return props.businessTypes; // semua didukung
    const filtered = {};
    for (const key of allowed) {
        if (props.businessTypes[key]) {
            filtered[key] = props.businessTypes[key];
        }
    }
    return filtered;
});

const voucherCode = ref('');
const voucherStatus = ref(null);
const voucherLoading = ref(false);

const applyVoucher = async () => {
    if (!voucherCode.value || !selectedPlan.value) return;
    voucherLoading.value = true;
    voucherStatus.value = null;
    try {
        const res = await fetch(route('voucher.apply'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
            body: JSON.stringify({ code: voucherCode.value, plan_id: selectedPlan.value.id, for: 'new' }),
        });
        const data = await res.json();
        voucherStatus.value = data;
        if (data.valid) {
            form.voucher_id = data.voucher_id;
            form.voucher_code = data.code;
        } else {
            form.voucher_id = null;
            form.voucher_code = null;
        }
    } catch (e) {
        voucherStatus.value = { valid: false, message: 'Gagal menghubungi server.' };
    } finally {
        voucherLoading.value = false;
    }
};

const form = useForm({
    tenant_name: '',
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    business_type: '',
    plan_id: null,
    voucher_id: null,
    voucher_code: null,
});

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num || 0);

const featureSummary = (features) => {
    if (!features) return {};
    const summary = {};
    for (const [key, val] of Object.entries(features)) {
        if (key in featureLabels) {
            summary[key] = (val === true || val === 'full' || val === 1) ? 'full' : (val === 'read_only' ? 'read_only' : 'none');
        }
    }
    return summary;
};

const selectPlan = (plan) => {
    selectedPlan.value = plan;
    form.plan_id = plan.id;
    // Set default business type berdasarkan paket
    const allowed = plan.business_types || [];
    const types = allowed.length > 0 ? allowed : Object.keys(props.businessTypes);
    form.business_type = types[0] || '';
    step.value = 'form';
};

const submit = () => {
    form.post(route('register.otp.send'));
};
</script>
