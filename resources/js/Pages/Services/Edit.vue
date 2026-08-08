<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold sk-text-primary">Edit Servis #{{ service.id }}</h2>
                    <p class="text-xs mt-0.5 sk-text-muted">Ubah data servis pelanggan</p>
                </div>
                <Link :href="route('services.show', service.id)" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold border transition-all" style="border-color: var(--border-color); color: var(--text-secondary); background: var(--bg-hover);">
                    ← Kembali
                </Link>
            </div>
        </template>

        <form @submit.prevent="submit" class="max-w-3xl mx-auto space-y-5">
            <!-- Data Pelanggan -->
            <div class="rounded-xl border p-5" style="border-color: var(--border-color); background: var(--bg-card);">
                <h3 class="text-sm font-bold mb-4 flex items-center gap-2 sk-text-primary">
                    <span class="w-6 h-6 rounded flex items-center justify-center text-xs text-white sk-bg-primary text-white">1</span>
                    Data Pelanggan
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wider sk-text-muted">Pelanggan</label>
                        <KSelect  v-model="form.customer_id" class="w-full rounded-xl border px-3 py-2.5 text-sm" :style="{ background: 'var(--bg-input)', color: 'var(--text-primary)', borderColor: 'var(--border-color)' }" required>
                            <option value="">Pilih Pelanggan</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }} - {{ c.phone }}</option>
                        </KSelect>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wider sk-text-muted">Status Servis</label>
                        <div class="rounded-xl border px-3 py-2.5 text-sm font-medium" :style="{ background: 'var(--bg-input)', color: 'var(--text-primary)', borderColor: 'var(--border-color)' }">
                            {{ service.status }}
                        </div>
                        <p class="mt-1 text-[11px] sk-text-muted">Status diubah dari aksi workflow pada halaman detail servis, bukan dari form edit ini.</p>
                    </div>
                </div>
            </div>

            <!-- Data Perangkat -->
            <div class="rounded-xl border p-5" style="border-color: var(--border-color); background: var(--bg-card);">
                <h3 class="text-sm font-bold mb-4 flex items-center gap-2 sk-text-primary">
                    <span class="w-6 h-6 rounded flex items-center justify-center text-xs text-white sk-bg-primary text-white">2</span>
                    Data Perangkat
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wider sk-text-muted">Jalur Kedatangan</label>
                        <KSelect  v-model="form.jalur_kedatangan_id" class="w-full rounded-xl border px-3 py-2.5 text-sm" :style="{ background: 'var(--bg-input)', color: 'var(--text-primary)', borderColor: 'var(--border-color)' }">
                            <option value="">Pilih Jalur</option>
                            <option v-for="am in arrivalMethods" :key="am.id" :value="am.id">{{ am.name }}</option>
                        </KSelect>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wider sk-text-muted">Kategori Perangkat</label>
                        <KSelect  v-model="form.kategori_perangkat_id" class="w-full rounded-xl border px-3 py-2.5 text-sm" :style="{ background: 'var(--bg-input)', color: 'var(--text-primary)', borderColor: 'var(--border-color)' }">
                            <option value="">Pilih Kategori</option>
                            <option v-for="dc in deviceCategories" :key="dc.id" :value="dc.id">{{ dc.name }}</option>
                        </KSelect>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wider sk-text-muted">Merek</label>
                        <KSelect  v-model="form.merek_id" class="w-full rounded-xl border px-3 py-2.5 text-sm" :style="{ background: 'var(--bg-input)', color: 'var(--text-primary)', borderColor: 'var(--border-color)' }">
                            <option value="">Pilih Merek</option>
                            <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
                        </KSelect>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wider sk-text-muted">Tipe / Model</label>
                        <KInput  type="text" v-model="form.tipe_unit" class="w-full rounded-xl border px-3 py-2.5 text-sm" :style="{ background: 'var(--bg-input)', color: 'var(--text-primary)', borderColor: 'var(--border-color)' }" placeholder="Contoh: iPhone 13 Pro" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wider sk-text-muted">IMEI / Serial Number</label>
                        <KInput  type="text" v-model="form.imei_sn" class="w-full rounded-xl border px-3 py-2.5 text-sm" :style="{ background: 'var(--bg-input)', color: 'var(--text-primary)', borderColor: 'var(--border-color)' }" placeholder="IMEI / SN" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wider sk-text-muted">Sandi / Pola / PIN</label>
                        <KInput  type="text" v-model="form.sandi_pola" class="w-full rounded-xl border px-3 py-2.5 text-sm" :style="{ background: 'var(--bg-input)', color: 'var(--text-primary)', borderColor: 'var(--border-color)' }" placeholder="Jika ada" />
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-xs font-semibold mb-2 uppercase tracking-wider sk-text-muted">Kelengkapan Bawaan</label>
                    <div class="flex flex-wrap gap-3">
                        <label v-for="eq in equipment" :key="eq.id" class="flex items-center gap-2 text-sm cursor-pointer sk-text-secondary">
                            <KCheckbox  :value="eq.name" v-model="form.kelengkapan" class="rounded" style="accent-color: var(--primary);" />
                            {{ eq.name }}
                        </label>
                    </div>
                </div>
            </div>

            <!-- Checklist Masuk -->
            <div class="rounded-xl border p-5" style="border-color: var(--border-color); background: var(--bg-card);">
                <h3 class="text-sm font-bold mb-4 flex items-center gap-2 sk-text-primary">
                    <span class="w-6 h-6 rounded flex items-center justify-center text-xs text-white sk-bg-primary text-white">3</span>
                    Ceklis Kondisi Masuk
                </h3>
                <div class="mb-4">
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wider sk-text-muted">Template Ceklis</label>
                    <KSelect  v-model="form.checklist_template_id" class="w-full rounded-xl border px-3 py-2.5 text-sm" :style="{ background: 'var(--bg-input)', color: 'var(--text-primary)', borderColor: 'var(--border-color)' }">
                        <option value="">Pilih Template (Opsional)</option>
                        <option v-for="t in templates" :key="t.id" :value="t.id">{{ t.name }}</option>
                    </KSelect>
                </div>
                <div v-if="selectedTemplate" class="space-y-2">
                    <label v-for="item in selectedTemplate.items" :key="item.id" class="flex items-center gap-2 text-sm cursor-pointer sk-text-secondary">
                        <KCheckbox  :value="item.item_name" v-model="form.checked_items" class="rounded" style="accent-color: var(--primary);" />
                        {{ item.item_name }}
                    </label>
                </div>
            </div>

            <!-- Deskripsi -->
            <div class="rounded-xl border p-5" style="border-color: var(--border-color); background: var(--bg-card);">
                <h3 class="text-sm font-bold mb-4 flex items-center gap-2 sk-text-primary">
                    <span class="w-6 h-6 rounded flex items-center justify-center text-xs text-white sk-bg-primary text-white">4</span>
                    Deskripsi Masalah & Kondisi
                </h3>
                <div class="mb-4">
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wider sk-text-muted">Deskripsi Masalah / Keluhan</label>
                    <KTextarea  v-model="form.problem_description" rows="3" class="w-full rounded-xl border px-3 py-2.5 text-sm resize-none" :style="{ background: 'var(--bg-input)', color: 'var(--text-primary)', borderColor: 'var(--border-color)' }" placeholder="Jelaskan keluhan pelanggan..."></KTextarea>
                </div>
                <div class="mb-4">
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wider sk-text-muted">Catatan Kondisi Fisik</label>
                    <KTextarea  v-model="form.condition_note" rows="2" class="w-full rounded-xl border px-3 py-2.5 text-sm resize-none" :style="{ background: 'var(--bg-input)', color: 'var(--text-primary)', borderColor: 'var(--border-color)' }" placeholder="Catatan kondisi fisik perangkat (lecet, retak, dll)"></KTextarea>
                </div>
                <div class="mb-4">
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wider sk-text-muted">Biaya Jasa</label>
                    <KInput  type="number" v-model.number="form.service_charge" min="0" class="w-full rounded-xl border px-3 py-2.5 text-sm" :style="{ background: 'var(--bg-input)', color: 'var(--text-primary)', borderColor: 'var(--border-color)' }" />
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <Link :href="route('services.show', service.id)" class="px-5 py-2.5 rounded-xl border text-sm font-semibold transition-all" style="border-color: var(--border-color); color: var(--text-secondary); background: var(--bg-hover);">Batal</Link>
                <KButton  type="submit" :disabled="form.processing" class="px-5 py-2.5 rounded-xl text-sm font-bold text-white transition-all hover:shadow-md disabled:opacity-50 sk-bg-primary text-white">
                    {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                </KButton>
            </div>
        </form>
    </AuthenticatedLayout>
</template>

<script setup>
import KButton from '@/Components/KButton.vue';
import KInput from '@/Components/KInput.vue';
import KSelect from '@/Components/KSelect.vue';
import KTextarea from '@/Components/KTextarea.vue';
import KCheckbox from '@/Components/KCheckbox.vue';

import { useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    service: { type: Object, default: () => ({}) },
    customers: { type: Array, default: () => [] },
    templates: { type: Array, default: () => [] },
    products: { type: Array, default: () => [] },
    deviceCategories: { type: Array, default: () => [] },
    brands: { type: Array, default: () => [] },
    arrivalMethods: { type: Array, default: () => [] },
    equipment: { type: Array, default: () => [] },
    driveConnected: { type: Boolean, default: false },
});

const form = useForm({
    customer_id: props.service.customer_id || '',
    jalur_kedatangan_id: props.service.jalur_kedatangan_id || '',
    kategori_perangkat_id: props.service.kategori_perangkat_id || '',
    merek_id: props.service.merek_id || '',
    tipe_unit: props.service.tipe_unit || '',
    imei_sn: props.service.imei_sn || '',
    sandi_pola: props.service.sandi_pola || '',
    kelengkapan: props.service.kelengkapan || [],
    checklist_template_id: props.service.checklists?.find(c => c.type === 'masuk')?.checklist_template_id?.toString() || '',
    checked_items: props.service.checklists?.find(c => c.type === 'masuk')?.checked_items?.map(i => String(i)) || [],
    problem_description: props.service.problem_description || '',
    condition_note: props.service.condition_note || '',
    service_charge: props.service.service_charge || 0,
});

const selectedTemplate = computed(() => {
    return props.templates.find(t => t.id == form.checklist_template_id);
});

const submit = () => {
    form.put(route('services.update', props.service.id));
};
</script>