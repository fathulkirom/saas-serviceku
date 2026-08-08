<template>
    <AdminLayout>
        <div class="max-w-4xl mx-auto">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold sk-text-primary tracking-tight">{{ isEditing ? 'Edit Voucher' : 'Buat Voucher Baru' }}</h1>
                    <p class="text-sm sk-text-muted mt-1">{{ isEditing ? 'Perbarui informasi kode promo yang sudah ada' : 'Buat kode diskon untuk menarik lebih banyak pelanggan' }}</p>
                </div>
                <Link :href="route('admin.vouchers.index')" class="px-4 py-2 sk-bg-card border sk-border rounded-xl sk-text-primary text-sm font-bold hover:sk-bg-hover transition-colors shadow-sm">
                    Kembali
                </Link>
            </div>

            <div class="sk-bg-card rounded-2xl border sk-border shadow-sm overflow-hidden">
                <form @submit.prevent="submit" class="divide-y sk-border-light">
                    
                    <!-- Basic Info -->
                    <div class="p-6">
                        <h3 class="text-sm font-bold sk-text-primary mb-5 flex items-center gap-2">
                            <svg class="w-4 h-4 sk-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                            Informasi Kode Promo
                        </h3>
                        
                        <div class="space-y-6">
                            <!-- Kode Voucher -->
                            <div>
                                <label class="block text-sm font-bold sk-text-primary mb-2">Kode Voucher <span class="sk-text-danger">*</span></label>
                                <div class="flex gap-3">
                                    <KInput  v-model="form.code" type="text" class="w-full px-4 py-3 rounded-xl border sk-border sk-bg-card sk-text-primary font-mono tracking-widest uppercase focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all shadow-sm" placeholder="KOSONGKAN UNTUK AUTO-GENERATE" />
                                    <KButton  type="button" @click="generateCode" class="px-5 py-3 sk-bg-hover hover:bg-zinc-200 sk-text-primary text-sm font-bold rounded-xl border sk-border transition-colors whitespace-nowrap flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        Generate
                                    </KButton>
                                </div>
                                <p v-if="form.errors.code" class="mt-2 text-sm text-red-400 font-medium">{{ form.errors.code }}</p>
                                <p v-else class="mt-2 text-xs sk-text-muted">Kosongkan kolom ini jika ingin sistem mengacak kode (8 karakter acak).</p>
                            </div>

                            <!-- Tipe & Nilai -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-bold sk-text-primary mb-2">Tipe Diskon</label>
                                    <KSelect  v-model="form.type" class="w-full px-4 py-3 rounded-xl border sk-border sk-bg-card sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all shadow-sm appearance-none">
                                        <option value="percent">Persentase (%)</option>
                                        <option value="fixed">Harga Tetap (Rp)</option>
                                    </KSelect>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold sk-text-primary mb-2">
                                        {{ form.type === 'percent' ? 'Diskon (%)' : 'Potongan (Rp)' }} <span class="sk-text-danger">*</span>
                                    </label>
                                    <KInput  v-model="form.value" type="number" step="0.01" min="0" class="w-full px-4 py-3 rounded-xl border sk-border sk-bg-card sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all shadow-sm" :placeholder="form.type === 'percent' ? 'Contoh: 10' : 'Contoh: 50000'" />
                                    <p v-if="form.errors.value" class="mt-2 text-sm text-red-400 font-medium">{{ form.errors.value }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold sk-text-primary mb-2">
                                        Gratis Bulan <span class="sk-text-muted font-normal">(opsional)</span>
                                    </label>
                                    <KInput  v-model="form.extra_months" type="number" min="0" max="60" class="w-full px-4 py-3 rounded-xl border sk-border sk-bg-card sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all shadow-sm" placeholder="Contoh: 1" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rules & Restrictions -->
                    <div class="p-6">
                        <h3 class="text-sm font-bold sk-text-primary mb-5 flex items-center gap-2">
                            <svg class="w-4 h-4 sk-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Aturan & Batasan
                        </h3>

                        <div class="space-y-6">
                            <!-- Untuk -->
                            <div>
                                <label class="block text-sm font-bold sk-text-primary mb-2">Berlaku Untuk Transaksi</label>
                                <KSelect  v-model="form.applicable_for" class="w-full px-4 py-3 rounded-xl border sk-border sk-bg-card sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all shadow-sm appearance-none">
                                    <option value="both">Semua (Pendaftaran Baru & Perpanjangan)</option>
                                    <option value="new">Hanya Pendaftaran Baru</option>
                                    <option value="existing">Hanya Perpanjangan / Upgrade</option>
                                </KSelect>
                            </div>

                            <!-- Khusus Tenant -->
                            <div v-if="form.applicable_for !== 'new'" class="p-4 sk-bg-warning-soft border border-amber-100 rounded-xl">
                                <label class="block text-sm font-bold text-amber-900 mb-2 flex items-center gap-2">
                                    <svg class="w-4 h-4 sk-text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                    Batasi Khusus Tenant Tertentu <span class="sk-text-warning/70 font-normal text-xs">(opsional)</span>
                                </label>
                                <KSelect  v-model="form.tenant_id" class="w-full px-4 py-3 rounded-xl border sk-border-primary sk-bg-card sk-text-primary focus:ring-2 focus:ring-amber-200 focus:border-amber-500 transition-all shadow-sm appearance-none">
                                    <option value="">Semua Tenant (Tidak dibatasi)</option>
                                    <option v-for="t in tenants" :key="t.id" :value="t.id">{{ t.tenant_name }}</option>
                                </KSelect>
                                <p class="mt-2 text-xs sk-text-warning">Jika Anda memilih tenant, maka voucher ini akan menjadi voucher eksklusif yang hanya bisa diklaim oleh tenant tersebut.</p>
                            </div>

                            <!-- Batasan Usage & Price -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-bold sk-text-primary mb-2">Kuota Pemakaian <span class="sk-text-muted font-normal">(opsional)</span></label>
                                    <KInput  v-model="form.max_uses" type="number" min="0" class="w-full px-4 py-3 rounded-xl border sk-border sk-bg-card sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all shadow-sm" placeholder="Kosongkan = Tanpa batas kuota" />
                                </div>
                                <div>
                                    <label class="block text-sm font-bold sk-text-primary mb-2">Minimal Harga Paket <span class="sk-text-muted font-normal">(opsional)</span></label>
                                    <KInput  v-model="form.min_plan_price" type="number" step="1000" min="0" class="w-full px-4 py-3 rounded-xl border sk-border sk-bg-card sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all shadow-sm" placeholder="Kosongkan = Tanpa batas minimal" />
                                </div>
                            </div>

                            <!-- Masa Berlaku -->
                            <div>
                                <label class="block text-sm font-bold sk-text-primary mb-2">Tanggal Berakhir <span class="sk-text-muted font-normal">(opsional)</span></label>
                                <KInput  v-model="form.expires_at" type="datetime-local" class="w-full md:w-1/2 px-4 py-3 rounded-xl border sk-border sk-bg-card sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all shadow-sm" />
                                <p class="mt-2 text-xs sk-text-muted">Kosongkan jika voucher berlaku selamanya.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Extras -->
                    <div class="p-6">
                        <h3 class="text-sm font-bold sk-text-primary mb-5 flex items-center gap-2">
                            <svg class="w-4 h-4 sk-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                            Lainnya
                        </h3>

                        <div class="space-y-6">
                            <!-- Deskripsi -->
                            <div>
                                <label class="block text-sm font-bold sk-text-primary mb-2">Catatan Internal <span class="sk-text-muted font-normal">(opsional)</span></label>
                                <KTextarea  v-model="form.description" rows="3" class="w-full px-4 py-3 rounded-xl border sk-border sk-bg-card sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all shadow-sm resize-none" placeholder="Catatan atau deskripsi untuk admin..."></KTextarea>
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <div class="relative flex items-center justify-center">
                                        <KCheckbox  v-model="form.is_active" class="w-5 h-5 rounded-md sk-border sk-text-primary-brand focus:ring-indigo-500 transition-all cursor-pointer" />
                                    </div>
                                    <span class="text-sm font-bold sk-text-primary group-hover:sk-text-primary-brand transition-colors">Voucher Aktif</span>
                                </label>
                                <p class="text-xs sk-text-muted mt-2 pl-8">Hanya voucher dengan status aktif yang bisa diklaim oleh pelanggan.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="px-6 py-5 sk-bg-hover flex items-center justify-end gap-4 rounded-b-2xl">
                        <Link :href="route('admin.vouchers.index')" class="px-6 py-2.5 sk-text-primary text-sm font-bold hover:sk-text-primary transition-colors">
                            Batal
                        </Link>
                        <KButton  type="submit" class="flex items-center gap-2 px-8 py-2.5 bg-zinc-900 hover:bg-zinc-800 text-white text-sm font-bold rounded-xl shadow-md transition-all disabled:opacity-70" :disabled="form.processing">
                            <svg v-if="form.processing" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <svg v-else class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            {{ form.processing ? 'Menyimpan...' : (isEditing ? 'Simpan Perubahan' : 'Buat Voucher') }}
                        </KButton>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import KButton from '@/Components/KButton.vue';
import KInput from '@/Components/KInput.vue';
import KSelect from '@/Components/KSelect.vue';
import KTextarea from '@/Components/KTextarea.vue';
import KCheckbox from '@/Components/KCheckbox.vue';

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