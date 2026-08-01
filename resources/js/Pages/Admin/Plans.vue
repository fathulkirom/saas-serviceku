<template>
    <AdminLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-100">Kelola Paket Langganan</h2>
                    <p class="text-sm text-slate-400 mt-0.5">Atur paket, harga, dan fitur per tipe bisnis</p>
                </div>
                <button @click="openCreateModal" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white transition-all duration-200 hover:-translate-y-0.5 shadow-lg" style="background: var(--primary);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Tambah Paket
                </button>
            </div>
        </template>

        <!-- Flash Message -->
        <div v-if="$page.props.flash?.success" class="mb-4 p-4 rounded-xl border flex items-center gap-3" style="background: rgba(34,197,94,0.1); border-color: rgba(34,197,94,0.2);">
            <p class="text-sm text-emerald-300">{{ $page.props.flash.success }}</p>
        </div>

        <!-- Plan Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div v-for="plan in plans" :key="plan.id" class="rounded-2xl p-6 border transition-all duration-300 hover:-translate-y-0.5 bg-slate-900/50 border-slate-800 backdrop-blur-xl"
                :style="plan.is_active ? { borderTop: '3px solid var(--primary)' } : { borderTop: '3px solid var(--border-color)' }">
                <div class="flex justify-between items-start">
                    <h3 class="text-lg font-bold text-slate-200">{{ plan.name }}</h3>
                    <span class="px-2 py-1 text-xs font-medium rounded-full whitespace-nowrap" :class="plan.is_active ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20'">
                        {{ plan.is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <template v-if="plan.is_promo_active">
                        <p class="text-xl text-slate-400 line-through">Rp {{ formatNumber(plan.price) }}</p>
                        <p class="text-3xl font-bold text-red-600">Rp {{ formatNumber(plan.promo_price) }}</p>
                        <span class="px-1.5 py-0.5 text-[10px] font-bold bg-red-100 text-red-700 rounded">DISKON {{ plan.discount_percent }}%</span>
                    </template>
                    <template v-else>
                        <p class="text-3xl font-bold text-indigo-600">Rp {{ formatNumber(plan.price) }}</p>
                    </template>
                </div>
                <p class="text-sm text-slate-400">/bulan</p>
                <p v-if="plan.is_promo_active && plan.promo_end" class="text-xs text-red-500 mt-1">Berakhir: {{ plan.promo_end }}</p>
                <p class="text-sm text-slate-400 mt-2">{{ plan.description || '-' }}</p>

                <div class="mt-4 space-y-1.5 text-sm text-slate-400">
                    <p v-if="plan.trial_days > 0" class="text-green-600">✓ Trial {{ plan.trial_days }} hari</p>
                    <template v-for="(val, key) in getCardFeatures(plan)" :key="key">
                        <p v-if="KNOWN_FEATURES[key]">
                            {{ featureLabel(key) }}:
                            <span class="font-medium"
                                :class="val === 'full' || val === true ? 'text-green-600' : val === 'read_only' ? 'text-yellow-600' : 'text-red-400'">
                                {{ accessLevelLabel(val) }}
                            </span>
                        </p>
                    </template>
                </div>

                <hr class="my-4">
                <div class="flex gap-2">
                    <button @click="openEditModal(plan)"
                        class="flex-1 px-3 py-1.5 text-sm bg-indigo-50 text-indigo-700 rounded-md hover:bg-indigo-100">
                        Edit
                    </button>
                    <button @click="toggleActive(plan)"
                        class="flex-1 px-3 py-1.5 text-sm rounded-md"
                        :class="plan.is_active ? 'bg-yellow-50 text-yellow-700 hover:bg-yellow-100' : 'bg-green-50 text-green-700 hover:bg-green-100'">
                        {{ plan.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="closeModal">
            <div class="rounded-2xl border" style="background: var(--bg-card); border-color: var(--border-color); box-shadow: var(--shadow-lg); w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">{{ isEditing ? 'Edit Paket' : 'Tambah Paket Baru' }}</h3>

                    <form @submit.prevent="submitForm">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2 sm:col-span-1">
                                <label class="block text-sm font-medium text-slate-300">Nama Paket</label>
                                <input v-model="form.name" type="text" required
                                    class="mt-1 block w-full rounded-md border-slate-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <p v-if="form.errors?.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                <label class="block text-sm font-medium text-slate-300">Slug</label>
                                <input v-model="form.slug" type="text" required
                                    class="mt-1 block w-full rounded-md border-slate-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                    :readonly="isEditing">
                                <p v-if="form.errors?.slug" class="mt-1 text-xs text-red-600">{{ form.errors.slug }}</p>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-slate-300">Deskripsi</label>
                                <textarea v-model="form.description" rows="2"
                                    class="mt-1 block w-full rounded-md border-slate-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"></textarea>
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                <label class="block text-sm font-medium text-slate-300">Harga Normal (Rp)</label>
                                <input v-model.number="form.price" name="price" type="number" min="0" required
                                    class="mt-1 block w-full rounded-md border-slate-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <p v-if="form.errors?.price" class="mt-1 text-xs text-red-600">{{ form.errors.price }}</p>
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                <label class="block text-sm font-medium text-slate-300">Trial (hari)</label>
                                <input v-model.number="form.trial_days" name="trial_days" type="number" min="0"
                                    class="mt-1 block w-full rounded-md border-slate-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>

                            <!-- Promo Fields -->
                            <div class="col-span-2 border-t pt-3 mt-1">
                                <div class="flex items-center gap-2 mb-3">
                                    <h4 class="text-sm font-semibold text-slate-200">🎉 Promo Diskon</h4>
                                    <span class="text-[10px] text-slate-400">(opsional)</span>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-300">Harga Promo (Rp)</label>
                                        <input v-model.number="form.promo_price" name="promo_price" type="number" min="0"
                                            class="mt-1 block w-full rounded-md border-slate-600 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm"
                                            placeholder="Kosongkan jika tidak promo">
                                    </div>
                                    <div></div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-300">Mulai Promo</label>
                                        <input v-model="form.promo_start" type="date"
                                            class="mt-1 block w-full rounded-md border-slate-600 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-300">Selesai Promo</label>
                                        <input v-model="form.promo_end" type="date"
                                            class="mt-1 block w-full rounded-md border-slate-600 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm">
                                    </div>
                                </div>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-slate-300">Status</label>
                                <select v-model="form.is_active"
                                    class="mt-1 block w-full rounded-md border-slate-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <option :value="true">Aktif</option>
                                    <option :value="false">Nonaktif</option>
                                </select>
                            </div>
                        </div>

                        <!-- Features / Permission Settings per Business Type -->
                        <div class="mt-6 border-t pt-4">
                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-slate-200 mb-1">Akses Fitur per Tipe Bisnis</label>
                                <p class="text-xs text-slate-400 mb-2">Pilih tipe bisnis di bawah ini untuk mengatur hak akses fiturnya secara khusus.</p>
                                
                                <select v-model="activeTabBusinessType" class="block w-full rounded-md border-slate-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm bg-indigo-50/50">
                                    <option v-for="(label, key) in BUSINESS_TYPES" :key="key" :value="key">
                                        💼 Tipe: {{ label }}
                                    </option>
                                </select>
                            </div>

                            <div class="bg-slate-800/50 p-4 rounded-xl border border-slate-700 mb-4">
                                <div class="flex justify-between items-center mb-3">
                                    <span class="text-xs font-bold text-indigo-700 uppercase tracking-wider">Fitur: {{ BUSINESS_TYPES[activeTabBusinessType] }}</span>
                                    <div class="flex gap-2 text-[10px] text-slate-400">
                                        <span>✅ Full</span>
                                        <span>👁️ Read Only</span>
                                        <span>❌ No Access</span>
                                    </div>
                                </div>

                                <div class="max-h-60 overflow-y-auto pr-1 space-y-2">
                                    <div v-for="(feature, key) in KNOWN_FEATURES" :key="key" class="flex items-center gap-2">
                                        <span class="w-1/2 text-xs font-medium text-slate-300">{{ feature }}</span>
                                        <select v-model="featureLevels[activeTabBusinessType][key]"
                                            class="w-1/2 py-1 rounded-md border-slate-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                                            <option value="full">✅ Full Akses</option>
                                            <option value="read_only">👁️ Read Only</option>
                                            <option value="none">❌ Tidak Ada</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Numeric limits -->
                            <div class="mt-4 pt-3 border-t">
                                <label class="text-sm font-medium text-slate-300 mb-2 block">Batas Numerik Paket</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs text-slate-400">Max User</label>
                                        <input v-model.number="numericLimits.max_users" name="max_users" type="number" min="0"
                                            class="mt-1 block w-full rounded-md border-slate-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-slate-400">Max Cabang</label>
                                        <input v-model.number="numericLimits.max_branches" name="max_branches" type="number" min="0"
                                            class="mt-1 block w-full rounded-md border-slate-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    </div>
                                </div>
                            </div>
                            <p v-if="form.errors?.features" class="mt-1 text-xs text-red-600">{{ form.errors.features }}</p>
                        </div>

                        <!-- Business Types -->
                        <div class="mt-6">
                            <label class="text-sm font-medium text-slate-300 mb-2 block">Tipe Bisnis yang Didukung</label>
                            <p class="text-xs text-slate-400 mb-2">Pilih tipe bisnis yang diizinkan untuk paket ini. Kosongkan = semua tipe didukung.</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <label v-for="(label, key) in BUSINESS_TYPES" :key="key"
                                    class="flex items-start gap-2 p-2 rounded border cursor-pointer hover:bg-slate-800/50"
                                    :class="selectedBusinessTypes.includes(key) ? 'border-indigo-300 bg-indigo-50' : 'border-slate-700'">
                                    <input type="checkbox" :value="key" v-model="selectedBusinessTypes"
                                        class="mt-0.5 rounded border-slate-600 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <span class="text-sm text-slate-300">{{ label }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 mt-6 pt-4 border-t">
                            <button type="button" @click="closeModal"
                                class="px-4 py-2 text-sm text-slate-300 bg-slate-800 rounded-md hover:bg-slate-700">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-sm text-white bg-indigo-600 rounded-md hover:bg-indigo-700"
                                :disabled="form.processing">
                                {{ isEditing ? 'Simpan' : 'Tambah' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    plans: { type: Array, default: () => [] },
});

const showModal = ref(false);
const isEditing = ref(false);
const editingPlan = ref(null);

// Daftar fitur yang dikenal dengan labelnya
const KNOWN_FEATURES = {
    services: 'Manajemen Servis',
    customers: 'Data Pelanggan',
    products: 'Manajemen Produk',
    sales: 'POS & Penjualan',
    reports: 'Laporan',
    settings: 'Pengaturan Toko',
    monitoring: 'Monitoring',
    multi_branch: 'Multi Cabang',
    transfer_stock: 'Transfer Stok',
    users: 'Manajemen User',
    expenses: 'Pengeluaran',
    purchases: 'Pembelian',
    deposits: 'Setor Harian',
    checklist: 'Template Ceklis',
    indents: 'Indent / Pre-order',
};

const ACCESS_LEVELS = [
    { value: 'full', label: '✅ Full Akses' },
    { value: 'read_only', label: '👁️ Read Only' },
    { value: 'none', label: '❌ Tidak Ada' },
];

const BUSINESS_TYPES = {
    full_service: 'Servis & Sparepart',
    aksesoris_service: 'Aksesoris & Servis',
    aksespare_service: 'Pusat Servis & Sparepart',
    gadget_full: 'Gadget & Servis',
    retail_only: 'Retail Saja',
};

const activeTabBusinessType = ref('full_service');

// Reactive state untuk permission levels per fitur
const featureLevels = reactive({
    full_service: {},
    aksesoris_service: {},
    aksespare_service: {},
    gadget_full: {},
    retail_only: {},
});
const numericLimits = reactive({
    max_users: 0,
    max_branches: 0,
});
const selectedBusinessTypes = reactive([]);

const featureLabel = (key) => KNOWN_FEATURES[key] || key;

const accessLevelLabel = (level) => {
    const found = ACCESS_LEVELS.find(a => a.value === level);
    return found ? found.label : level;
};

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num || 0);

const form = useForm({
    name: '',
    slug: '',
    description: '',
    price: 0,
    promo_price: null,
    promo_start: '',
    promo_end: '',
    trial_days: 0,
    features: {},
    business_types: [],
    is_active: true,
});

const getCardFeatures = (plan) => {
    const features = plan.features || {};
    if (features.full_service && typeof features.full_service === 'object') {
        return features.full_service;
    }
    return features;
};

function resetFeatureLevels(defaultLevel = 'none') {
    for (const type of Object.keys(BUSINESS_TYPES)) {
        featureLevels[type] = {};
        for (const key of Object.keys(KNOWN_FEATURES)) {
            featureLevels[type][key] = defaultLevel;
        }
    }
    numericLimits.max_users = 0;
    numericLimits.max_branches = 0;
    selectedBusinessTypes.length = 0;
}

function loadFeatureLevelsFromPlan(planFeatures) {
    const features = planFeatures || {};
    // Cek apakah format flat atau bersarang
    const isNested = Object.keys(BUSINESS_TYPES).some(type => typeof features[type] === 'object' && features[type] !== null);
    
    resetFeatureLevels('none');
    
    if (isNested) {
        for (const type of Object.keys(BUSINESS_TYPES)) {
            if (features[type]) {
                for (const key of Object.keys(KNOWN_FEATURES)) {
                    const val = features[type][key];
                    if (val === true || val === 'full' || val === 1 || val === '1') {
                        featureLevels[type][key] = 'full';
                    } else if (val === 'read_only') {
                        featureLevels[type][key] = 'read_only';
                    } else {
                        featureLevels[type][key] = 'none';
                    }
                }
            }
        }
    } else {
        // Fallback / Migrasi: sebarkan format flat lama ke semua tipe bisnis
        for (const type of Object.keys(BUSINESS_TYPES)) {
            for (const key of Object.keys(KNOWN_FEATURES)) {
                const val = features[key];
                if (val === true || val === 'full' || val === 1 || val === '1') {
                    featureLevels[type][key] = 'full';
                } else if (val === 'read_only') {
                    featureLevels[type][key] = 'read_only';
                } else {
                    featureLevels[type][key] = 'none';
                }
            }
        }
    }
    numericLimits.max_users = features.max_users ?? 0;
    numericLimits.max_branches = features.max_branches ?? 0;
}

function buildFeaturesFromForm() {
    const features = {};
    for (const type of Object.keys(BUSINESS_TYPES)) {
        features[type] = {};
        for (const key of Object.keys(KNOWN_FEATURES)) {
            const level = featureLevels[type][key];
            if (level === 'full') {
                features[type][key] = true;
            } else if (level === 'read_only') {
                features[type][key] = 'read_only';
            } else {
                features[type][key] = 'none';
            }
        }
    }
    // Tambah numeric limits
    if (numericLimits.max_users > 0) features.max_users = numericLimits.max_users;
    if (numericLimits.max_branches > 0) features.max_branches = numericLimits.max_branches;
    return features;
}

function buildBusinessTypesFromForm() {
    return [...selectedBusinessTypes];
}

function openCreateModal() {
    isEditing.value = false;
    editingPlan.value = null;
    form.reset();
    form.is_active = true;
    resetFeatureLevels('none');
    showModal.value = true;
}

function openEditModal(plan) {
    isEditing.value = true;
    editingPlan.value = plan;
    form.name = plan.name;
    form.slug = plan.slug;
    form.description = plan.description || '';
    form.price = plan.price;
    form.promo_price = plan.promo_price || null;
    form.promo_start = plan.promo_start || '';
    form.promo_end = plan.promo_end || '';
    form.trial_days = plan.trial_days || 0;
    form.is_active = plan.is_active;
    form.features = { ...(plan.features || {}) };
    form.business_types = plan.business_types || [];

    loadFeatureLevelsFromPlan(plan.features);
    // Load business types
    selectedBusinessTypes.length = 0;
    if (plan.business_types?.length > 0) {
        plan.business_types.forEach(t => selectedBusinessTypes.push(t));
    }
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    form.reset();
    resetFeatureLevels('none');
}

function submitForm() {
    form.features = buildFeaturesFromForm();
    form.business_types = buildBusinessTypesFromForm();

    if (isEditing.value && editingPlan.value) {
        form.post(route('admin.plans.update', editingPlan.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.plans.store'), {
            onSuccess: () => closeModal(),
        });
    }
}

function toggleActive(plan) {
    router.post(route('admin.plans.update', plan.id), {
        name: plan.name,
        slug: plan.slug,
        description: plan.description || '',
        price: plan.price,
        promo_price: plan.promo_price || null,
        promo_start: plan.promo_start || null,
        promo_end: plan.promo_end || null,
        trial_days: plan.trial_days || 0,
        features: plan.features || {},
        business_types: plan.business_types || [],
        is_active: !plan.is_active,
    });
}
</script>
