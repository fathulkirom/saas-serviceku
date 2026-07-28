<template>
    <AdminLayout>
        <div class="page-header">
            <div>
                <h1 class="text-2xl font-bold text-dark-900">{{ isEditing ? 'Edit Voucher' : 'Buat Voucher Baru' }}</h1>
                <p class="text-sm text-dark-400 mt-1">{{ isEditing ? 'Perbarui kode promo' : 'Buat kode diskon untuk pelanggan' }}</p>
            </div>
            <Link :href="route('admin.vouchers.index')" class="btn-premium-secondary">← Kembali</Link>
        </div>

        <div class="card-premium max-w-2xl">
            <form @submit.prevent="submit">
                <!-- Kode Voucher -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-dark-600 mb-1.5">Kode Voucher</label>
                    <div class="flex gap-2">
                        <input v-model="form.code" type="text" name="code" class="input-premium flex-1 font-mono tracking-wider uppercase"
                            placeholder="KOSONGKAN untuk auto-generate" />
                        <button type="button" @click="generateCode" class="btn-premium-secondary text-sm whitespace-nowrap">↻ Generate</button>
                    </div>
                    <p v-if="form.errors.code" class="mt-1 text-sm text-accent-600">{{ form.errors.code }}</p>
                    <p class="mt-1 text-xs text-dark-400">Kosongkan untuk generate otomatis (8 karakter acak)</p>
                </div>

                <!-- Tipe & Nilai -->
                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-dark-600 mb-1.5">Tipe Diskon</label>
                        <select v-model="form.type" name="type" class="input-premium">
                            <option value="percent">Persen (%)</option>
                            <option value="fixed">Harga Tetap (Rp)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-dark-600 mb-1.5">
                            {{ form.type === 'percent' ? 'Diskon (%)' : 'Potongan (Rp)' }}
                        </label>
                        <input v-model="form.value" type="number" step="0.01" min="0" name="value" class="input-premium"
                            :placeholder="form.type === 'percent' ? 'Contoh: 10' : 'Contoh: 50000'" />
                        <p v-if="form.errors.value" class="mt-1 text-sm text-accent-600">{{ form.errors.value }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-dark-600 mb-1.5">
                            Gratis Bulan <span class="text-xs text-dark-400">(opsional)</span>
                        </label>
                        <input v-model="form.extra_months" type="number" min="0" max="60" name="extra_months" class="input-premium"
                            placeholder="Contoh: 1" />
                        <p class="mt-1 text-xs text-dark-400">Tambahan bulan gratis langganan</p>
                    </div>
                </div>

                <!-- Untuk -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-dark-600 mb-1.5">Berlaku Untuk</label>
                    <select v-model="form.applicable_for" name="applicable_for" class="input-premium">
                        <option value="both">Semua (Pendaftaran Baru & Perpanjangan)</option>
                        <option value="new">Pendaftaran Baru Saja</option>
                        <option value="existing">Perpanjangan / Upgrade Saja</option>
                    </select>
                </div>

                <!-- Khusus Tenant -->
                <div class="mb-4" v-if="form.applicable_for !== 'new'">
                    <label class="block text-sm font-medium text-dark-600 mb-1.5">Batasi Khusus Toko / Tenant <span class="text-xs text-dark-400">(opsional)</span></label>
                    <select v-model="form.tenant_id" name="tenant_id" class="input-premium">
                        <option value="">Semua Toko / Tenant</option>
                        <option v-for="t in tenants" :key="t.id" :value="t.id">
                            {{ t.tenant_name }}
                        </option>
                    </select>
                    <p class="mt-1 text-xs text-dark-400">Jika diisi, voucher hanya dapat digunakan oleh toko terpilih saja.</p>
                </div>

                <!-- Batasan -->
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-dark-600 mb-1.5">Maksimal Pemakaian</label>
                        <input v-model="form.max_uses" type="number" min="0" name="max_uses" class="input-premium" placeholder="Kosongkan = tidak terbatas" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-dark-600 mb-1.5">Min. Harga Plan</label>
                        <input v-model="form.min_plan_price" type="number" step="1000" min="0" name="min_plan_price" class="input-premium" placeholder="Kosongkan = tanpa minimal" />
                    </div>
                </div>

                <!-- Masa Berlaku -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-dark-600 mb-1.5">Berlaku Sampai</label>
                    <input v-model="form.expires_at" type="datetime-local" name="expires_at" class="input-premium" />
                    <p class="mt-1 text-xs text-dark-400">Kosongkan = tidak ada batas waktu</p>
                </div>

                <!-- Deskripsi -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-dark-600 mb-1.5">Deskripsi (opsional)</label>
                    <textarea v-model="form.description" rows="3" name="description" class="input-premium" placeholder="Keterangan internal"></textarea>
                </div>

                <!-- Status -->
                <div class="mb-6">
                    <label class="flex items-center gap-3">
                        <input type="checkbox" v-model="form.is_active" name="is_active" class="rounded border-dark-200 text-premium-600 focus:ring-premium-500" />
                        <span class="text-sm font-medium text-dark-600">Aktif</span>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-dark-100">
                    <Link :href="route('admin.vouchers.index')" class="btn-premium-secondary">Batal</Link>
                    <button type="submit" class="btn-premium-primary" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : (isEditing ? 'Simpan Perubahan' : 'Buat Voucher') }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    voucher: { type: Object, default: null },
    isEditing: { type: Boolean, default: false },
    tenants: { type: Array, default: () => [] },
});

const form = useForm({
    code: props.voucher?.code || '',
    type: props.voucher?.type || 'percent',
    value: props.voucher?.value || '',
    extra_months: props.voucher?.extra_months || '',
    applicable_for: props.voucher?.applicable_for || 'both',
    tenant_id: props.voucher?.tenant_id || '',
    max_uses: props.voucher?.max_uses || '',
    min_plan_price: props.voucher?.min_plan_price || '',
    expires_at: props.voucher?.expires_at ? props.voucher.expires_at.substring(0, 16) : '',
    is_active: props.voucher?.is_active ?? true,
    description: props.voucher?.description || '',
});

watch(() => form.applicable_for, (newVal) => {
    if (newVal === 'new') {
        form.tenant_id = '';
    }
});

const generateCode = async () => {
    try {
        const res = await fetch(route('admin.vouchers.generate-code'));
        const data = await res.json();
        form.code = data.code;
    } catch (e) {
        // fallback
        form.code = Math.random().toString(36).substring(2, 10).toUpperCase();
    }
};

const submit = () => {
    if (props.isEditing) {
        form.post(route('admin.vouchers.update', props.voucher.id));
    } else {
        form.post(route('admin.vouchers.store'));
    }
};
</script>