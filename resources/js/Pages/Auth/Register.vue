<template>
    <GuestLayout>
        <!-- Step 1: Pilih Paket -->
        <div v-if="step === 'plan'">
            <h2 class="text-xl font-bold text-dark-900 mb-2 text-center">Pilih Paket Langganan</h2>
            <p class="text-sm text-dark-400 mb-6 text-center">Mulai trial gratis atau pilih paket yang sesuai</p>

            <div class="grid gap-4">
                <div v-for="p in plans" :key="p.id"
                    @click="selectPlan(p)"
                    class="p-4 rounded-xl border-2 cursor-pointer transition-all"
                    :class="selectedPlan?.id === p.id
                        ? 'border-premium-500 bg-premium-50 shadow-premium-sm'
                        : 'border-dark-200 hover:border-premium-200 hover:shadow-sm'">

                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-semibold text-dark-900">{{ p.name }}</h3>
                            <div class="flex items-baseline gap-2 mt-1">
                                <template v-if="p.is_promo_active">
                                    <span class="text-xs text-dark-400 line-through">Rp {{ formatNumber(p.price) }}</span>
                                    <span class="text-2xl font-bold text-accent-700">Rp {{ formatNumber(p.promo_price) }}</span>
                                    <span class="px-1.5 py-0.5 text-[10px] font-bold bg-accent-50 text-accent-700 rounded">DISKON {{ p.discount_percent }}%</span>
                                </template>
                                <template v-else-if="p.slug === 'trial'">
                                    <span class="text-2xl font-bold text-success-700">Gratis</span>
                                </template>
                                <template v-else>
                                    <span class="text-2xl font-bold text-premium-600">Rp {{ formatNumber(p.price) }}</span>
                                </template>
                                <span class="text-xs text-dark-400">/bln</span>
                            </div>
                        </div>
                        <span v-if="p.slug === 'trial'" class="px-2 py-1 text-[10px] font-semibold bg-success-50 text-success-700 rounded-full whitespace-nowrap">
                            Trial {{ p.trial_days }} hari
                        </span>
                    </div>

                    <p v-if="p.description" class="text-xs text-dark-400 mt-2">{{ p.description }}</p>

                    <div class="flex flex-wrap gap-1.5 mt-3">
                        <span v-for="(level, key) in featureSummary(p.features)" :key="key"
                            class="px-2 py-0.5 text-[10px] rounded-full"
                            :class="level === 'full' ? 'bg-success-50 text-success-700' : 'bg-dark-50 text-dark-400'">
                            {{ featureLabels[key] || key }}
                        </span>
                    </div>

                    <button type="button"
                        class="mt-3 w-full py-2 text-sm font-medium rounded-lg transition"
                        :class="selectedPlan?.id === p.id
                            ? 'bg-premium-600 text-white'
                            : 'bg-premium-50 text-premium-700 hover:bg-premium-100'">
                        {{ selectedPlan?.id === p.id ? '✓ Dipilih' : 'Pilih Paket' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Step 2: Form Registrasi -->
        <div v-else>
            <div class="mb-6">
                <button @click="step = 'plan'" class="text-sm text-premium-600 hover:text-premium-500 mb-4 flex items-center gap-1 font-semibold">
                    ← Ganti Paket
                </button>
                <h2 class="text-xl font-bold text-dark-900">Lengkapi Data</h2>
                <p class="text-sm text-dark-400 mt-1">
                    Paket: <strong>{{ selectedPlan?.name }}</strong>
                    <template v-if="selectedPlan?.slug === 'trial'">
                        <span class="text-success-700 ml-1">(Gratis {{ selectedPlan.trial_days }} hari)</span>
                    </template>
                    <template v-else>
                        <span class="text-premium-600 ml-1">
                            (Rp {{ formatNumber(selectedPlan?.effective_price || selectedPlan?.price) }}/bln)
                        </span>
                    </template>
                </p>
            </div>

            <form @submit.prevent="submit">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-dark-600 mb-1">Nama Toko</label>
                    <input v-model="form.tenant_name" type="text" required
                        class="input-premium"
                        placeholder="Contoh: Toko Servis ABC" />
                    <p v-if="form.errors.tenant_name" class="mt-1 text-sm text-accent-700">{{ form.errors.tenant_name }}</p>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-dark-600 mb-1">Nama Pemilik</label>
                    <input v-model="form.name" type="text" required
                        class="input-premium"
                        placeholder="Nama lengkap pemilik" />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-accent-700">{{ form.errors.name }}</p>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-dark-600 mb-1">Email</label>
                    <input v-model="form.email" type="email" required
                        class="input-premium"
                        placeholder="owner@email.com" />
                    <p v-if="form.errors.email" class="mt-1 text-sm text-accent-700">{{ form.errors.email }}</p>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-dark-600 mb-1">No. Telepon</label>
                    <input v-model="form.phone" type="text"
                        class="input-premium" />
                    <p v-if="form.errors.phone" class="mt-1 text-sm text-accent-700">{{ form.errors.phone }}</p>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-dark-600 mb-1">Tipe Bisnis</label>
                    <select v-model="form.business_type"
                        class="input-premium">
                        <option v-if="filteredBusinessTypes.length === 0" value="">Pilih tipe bisnis...</option>
                        <option v-for="(label, key) in filteredBusinessTypes" :key="key" :value="key">{{ label }}</option>
                    </select>
                    <p v-if="form.errors.business_type" class="mt-1 text-sm text-accent-700">{{ form.errors.business_type }}</p>
                    <p v-if="!filteredBusinessTypes.length" class="mt-1 text-xs text-accent-600">Semua tipe bisnis didukung untuk paket ini.</p>
                </div>

                <!-- Voucher Code -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-dark-600 mb-1.5">Kode Promo <span class="text-xs text-dark-400">(opsional)</span></label>
                    <div class="flex gap-2">
                        <input v-model="voucherCode" type="text"
                            class="input-premium flex-1 uppercase tracking-wider"
                            placeholder="Masukkan kode promo"
                            @input="voucherStatus = null" />
                        <button type="button" @click="applyVoucher"
                            class="btn-premium-secondary text-sm whitespace-nowrap"
                            :disabled="voucherLoading || !voucherCode">
                            {{ voucherLoading ? '...' : 'Pakai' }}
                        </button>
                    </div>
                    <!-- Voucher Success -->
                    <div v-if="voucherStatus?.valid" class="mt-2 p-3 bg-success-50 border border-success-200 rounded-lg">
                        <p class="text-xs font-semibold text-success-700">✅ {{ voucherStatus.message }}</p>
                        <p v-if="voucherStatus.discount > 0" class="text-xs text-success-600 mt-1">
                            Harga: Rp {{ formatNumber(voucherStatus.original_price) }} → <strong>Rp {{ formatNumber(voucherStatus.final_price) }}</strong>
                        </p>
                        <p v-if="voucherStatus.extra_months" class="text-xs text-success-600 mt-0.5">
                            🎁 + {{ voucherStatus.extra_months }} bulan gratis langganan
                        </p>
                    </div>
                    <!-- Voucher Error -->
                    <div v-else-if="voucherStatus?.message" class="mt-2 p-2.5 bg-accent-50 border border-accent-200 rounded-lg">
                        <p class="text-xs font-semibold text-accent-700">✕ {{ voucherStatus.message }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="mb-4">
                    <label class="block text-sm font-medium text-dark-600 mb-1">Password</label>
                    <input v-model="form.password" type="password" required minlength="8"
                        class="input-premium" />
                    <p v-if="form.errors.password" class="mt-1 text-sm text-accent-700">{{ form.errors.password }}</p>
                    </div>
                    <div class="mb-4">
                    <label class="block text-sm font-medium text-dark-600 mb-1">Konfirmasi Password</label>
                    <input v-model="form.password_confirmation" type="password" required minlength="8"
                        class="input-premium" />
                    </div>
                </div>
                <div class="flex items-center justify-between mt-6 pt-4 border-t">
                    <Link :href="route('login')" class="text-sm text-premium-600 hover:text-premium-500 font-semibold">
                        Sudah punya akun? Login
                    </Link>
                    <button type="submit"
                        class="btn-premium-primary disabled:opacity-50 font-medium"
                        :disabled="form.processing">
                        {{ form.processing ? 'Mendaftarkan...' : 'Daftar Sekarang' }}
                    </button>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>

<script setup>
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
