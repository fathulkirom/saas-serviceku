<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold text-dark-900">Servis Baru</h2>
                    <p class="text-xs text-dark-400 mt-0.5">Lengkapi data servis pelanggan</p>
                </div>
                <Link :href="route('services.index')" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold border transition-all text-dark-600 border-dark-200 hover:bg-dark-50">
                    ← Kembali
                </Link>
            </div>
        </template>

        <form @submit.prevent="submit" class="max-w-3xl mx-auto space-y-5">
            <!-- Data Pelanggan (Adopsi Halaman 18 & 19 PDF) -->
            <div class="bg-white rounded-xl border shadow-sm p-5" style="border-color: var(--border-color);">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-dark-900 flex items-center gap-2">
                        <span class="w-6 h-6 rounded flex items-center justify-center text-xs text-white" style="background: var(--accent-primary);">1</span>
                        Data Pelanggan (Unit Masuk)
                    </h3>
                    <button type="button" @click="openAddCustomerModal" class="px-3 py-1.5 rounded-lg text-xs font-bold text-white shadow-sm transition-all hover:shadow active:scale-95" style="background: #dc2626;">
                        + Tambah Pelanggan Baru
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-dark-500 mb-1.5 uppercase tracking-wider">Cari / Pilih Pelanggan</label>
                        <select v-model="form.customer_id" id="customer_id" name="customer_id" class="w-full rounded-lg border text-sm px-3 py-2.5 focus:ring-2 focus:outline-none border-dark-200 text-dark-700 focus:border-blue-500 focus:ring-blue-200 transition-all bg-white" required>
                            <option value="">-- Pilih Nama Pelanggan --</option>
                            <option v-for="c in customerOptions" :key="c.id" :value="c.id">{{ c.name }} {{ c.phone ? ' (' + c.phone + ')' : '' }}</option>
                        </select>
                    </div>
                    <div v-if="selectedCustomer" class="bg-dark-50/70 p-3 rounded-lg border border-dark-100 text-xs space-y-1">
                        <p class="font-bold text-dark-900">{{ selectedCustomer.name }}</p>
                        <p class="text-dark-600">Telp: {{ selectedCustomer.phone || '-' }}</p>
                        <p class="text-dark-500 truncate">Alamat: {{ selectedCustomer.address || '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Data Perangkat -->
            <div class="bg-white rounded-xl border shadow-sm p-5" style="border-color: var(--border-color);">
                <h3 class="text-sm font-bold text-dark-900 mb-4 flex items-center gap-2">
                    <span class="w-6 h-6 rounded flex items-center justify-center text-xs text-white" style="background: var(--accent-primary);">2</span>
                    Data Perangkat
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-dark-500 mb-1.5 uppercase tracking-wider">Jalur Kedatangan</label>
                        <select v-model="form.jalur_kedatangan_id" id="jalur_kedatangan_id" name="jalur_kedatangan_id" class="w-full rounded-lg border text-sm px-3 py-2.5 focus:ring-2 focus:outline-none border-dark-200 text-dark-700 focus:border-blue-500 focus:ring-blue-200 transition-all">
                            <option value="">Pilih Jalur</option>
                            <option v-for="am in arrivalMethods" :key="am.id" :value="am.id">{{ am.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-dark-500 mb-1.5 uppercase tracking-wider">Kategori Perangkat</label>
                        <select v-model="form.kategori_perangkat_id" id="kategori_perangkat_id" name="kategori_perangkat_id" class="w-full rounded-lg border text-sm px-3 py-2.5 focus:ring-2 focus:outline-none border-dark-200 text-dark-700 focus:border-blue-500 focus:ring-blue-200 transition-all">
                            <option value="">Pilih Kategori</option>
                            <option v-for="dc in deviceCategories" :key="dc.id" :value="dc.id">{{ dc.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-dark-500 mb-1.5 uppercase tracking-wider">Merek</label>
                        <select v-model="form.merek_id" id="merek_id" name="merek_id" class="w-full rounded-lg border text-sm px-3 py-2.5 focus:ring-2 focus:outline-none border-dark-200 text-dark-700 focus:border-blue-500 focus:ring-blue-200 transition-all">
                            <option value="">Pilih Merek</option>
                            <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-dark-500 mb-1.5 uppercase tracking-wider">Tipe / Model</label>
                        <input type="text" v-model="form.tipe_unit" id="tipe_unit" name="tipe_unit" class="w-full rounded-lg border text-sm px-3 py-2.5 focus:ring-2 focus:outline-none border-dark-200 text-dark-700 focus:border-blue-500 focus:ring-blue-200 transition-all" placeholder="Contoh: iPhone 13 Pro" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-dark-500 mb-1.5 uppercase tracking-wider">IMEI / Serial Number</label>
                        <input type="text" v-model="form.imei_sn" id="imei_sn" name="imei_sn" class="w-full rounded-lg border text-sm px-3 py-2.5 focus:ring-2 focus:outline-none border-dark-200 text-dark-700 focus:border-blue-500 focus:ring-blue-200 transition-all" placeholder="IMEI / SN" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-dark-500 mb-1.5 uppercase tracking-wider">Sandi / Pola / PIN</label>
                        <input type="text" v-model="form.sandi_pola" id="sandi_pola" name="sandi_pola" class="w-full rounded-lg border text-sm px-3 py-2.5 focus:ring-2 focus:outline-none border-dark-200 text-dark-700 focus:border-blue-500 focus:ring-blue-200 transition-all" placeholder="Jika ada" />
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-xs font-semibold text-dark-500 mb-2 uppercase tracking-wider">Kelengkapan Bawaan</label>
                    <div class="flex flex-wrap gap-3">
                        <label v-for="eq in equipment" :key="eq.id" class="flex items-center gap-2 text-sm cursor-pointer">
                            <input type="checkbox" :value="eq.name" v-model="form.kelengkapan" id="kelengkapan" name="kelengkapan" class="rounded border-dark-300 text-premium-600 focus:ring-premium-500" />
                            <span class="text-dark-700">{{ eq.name }}</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Checklist Masuk -->
            <div class="bg-white rounded-xl border shadow-sm p-5" style="border-color: var(--border-color);">
                <h3 class="text-sm font-bold text-dark-900 mb-4 flex items-center gap-2">
                    <span class="w-6 h-6 rounded flex items-center justify-center text-xs text-white" style="background: var(--accent-primary);">3</span>
                    Ceklis Kondisi Masuk
                </h3>
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-dark-500 mb-1.5 uppercase tracking-wider">Template Ceklis</label>
                    <select v-model="form.checklist_template_id" id="checklist_template_id" name="checklist_template_id" class="w-full rounded-lg border text-sm px-3 py-2.5 focus:ring-2 focus:outline-none border-dark-200 text-dark-700 focus:border-blue-500 focus:ring-blue-200 transition-all">
                        <option value="">Pilih Template (Opsional)</option>
                        <option v-for="t in templates" :key="t.id" :value="t.id">{{ t.name }}</option>
                    </select>
                </div>
                <div v-if="selectedTemplate" class="space-y-2">
                    <label v-for="item in selectedTemplate.items" :key="item.id" class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="checkbox" :value="item.item_name" v-model="form.checked_items" id="checked_items" name="checked_items" class="rounded border-dark-300 text-premium-600 focus:ring-premium-500" />
                        <span class="text-dark-700">{{ item.item_name }}</span>
                    </label>
                </div>
            </div>

            <!-- Deskripsi -->
            <div class="bg-white rounded-xl border shadow-sm p-5" style="border-color: var(--border-color);">
                <h3 class="text-sm font-bold text-dark-900 mb-4 flex items-center gap-2">
                    <span class="w-6 h-6 rounded flex items-center justify-center text-xs text-white" style="background: var(--accent-primary);">4</span>
                    Deskripsi Masalah & Kondisi
                </h3>
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-dark-500 mb-1.5 uppercase tracking-wider">Deskripsi Masalah / Keluhan</label>
                    <textarea v-model="form.problem_description" rows="3" id="problem_description" name="problem_description" class="w-full rounded-lg border text-sm px-3 py-2.5 focus:ring-2 focus:outline-none border-dark-200 text-dark-700 focus:border-blue-500 focus:ring-blue-200 transition-all" placeholder="Jelaskan keluhan pelanggan..."></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-dark-500 mb-1.5 uppercase tracking-wider">Catatan Kondisi Fisik</label>
                    <textarea v-model="form.condition_note" rows="2" id="condition_note" name="condition_note" class="w-full rounded-lg border text-sm px-3 py-2.5 focus:ring-2 focus:outline-none border-dark-200 text-dark-700 focus:border-blue-500 focus:ring-blue-200 transition-all" placeholder="Catatan kondisi fisik perangkat (lecet, retak, dll)"></textarea>
                </div>
            </div>

            <!-- Foto Perangkat -->
            <div v-if="driveConnected" class="bg-white rounded-xl border shadow-sm p-5" style="border-color: var(--border-color);">
                <h3 class="text-sm font-bold text-dark-900 mb-4 flex items-center gap-2">
                    <span class="w-6 h-6 rounded flex items-center justify-center text-xs text-white" style="background: var(--accent-primary);">5</span>
                    Foto Perangkat
                </h3>
                <div>
                    <label class="block text-xs font-semibold text-dark-500 mb-1.5 uppercase tracking-wider">Upload Foto (maks. 10)</label>
                    <input type="file" @change="onPhotosChange" accept="image/*" multiple class="block w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                    <div v-if="photoPreviews.length" class="mt-3 flex flex-wrap gap-2">
                        <div v-for="(preview, idx) in photoPreviews" :key="idx" class="relative">
                            <img :src="preview" class="h-20 w-20 object-cover rounded-lg border" />
                            <button type="button" @click="removePhoto(idx)" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white rounded-full text-xs flex items-center justify-center">&times;</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Custom Fields -->
            <div v-if="customFields.length > 0" class="bg-white rounded-xl border shadow-sm p-5" style="border-color: var(--border-color);">
                <h3 class="text-sm font-bold text-dark-900 mb-4 flex items-center gap-2">
                    <span class="w-6 h-6 rounded flex items-center justify-center text-xs text-white" style="background: var(--accent-primary);">6</span>
                    Informasi Tambahan
                </h3>
                <DynamicFormFields :fields="customFields" :form-data="form" />
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <Link :href="route('services.index')" class="px-5 py-2.5 rounded-lg border text-sm font-semibold text-dark-600 border-dark-200 hover:bg-dark-50 transition-all">Batal</Link>
                <button type="submit" :disabled="form.processing" class="px-5 py-2.5 rounded-lg text-sm font-bold text-white transition-all hover:shadow-md disabled:opacity-50" style="background: var(--accent-primary);">
                    {{ form.processing ? 'Menyimpan...' : 'Simpan & Proses' }}
                </button>
            </div>
        </form>

        <Teleport to="body">
            <div v-if="showAddCustomerModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm overflow-y-auto py-8" @click.self="showAddCustomerModal = false">
                <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-3 border border-dark-200">
                    <h3 class="text-base font-bold text-dark-900 mb-4 pb-2 border-b border-dark-100 flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-red-600 inline-block"></span>
                        Entry Pelanggan Baru (Fast Entry)
                    </h3>
                    <form @submit.prevent="submitFastCustomer" class="space-y-3 text-xs">
                        <div>
                            <label class="block font-semibold text-dark-600 mb-1">Nama Pelanggan *</label>
                            <input type="text" v-model="newCustomerForm.name" required placeholder="Nama Lengkap" class="w-full rounded-lg border text-xs px-3 py-2 border-dark-200 text-dark-800 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 focus:outline-none" />
                        </div>
                        <div>
                            <label class="block font-semibold text-dark-600 mb-1">No. Telepon / WA *</label>
                            <input type="text" v-model="newCustomerForm.phone" required placeholder="08xxxxxxxxxx" class="w-full rounded-lg border text-xs px-3 py-2 border-dark-200 text-dark-800 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 focus:outline-none" />
                        </div>
                        <div>
                            <label class="block font-semibold text-dark-600 mb-1">Alamat (opsional)</label>
                            <textarea v-model="newCustomerForm.address" placeholder="Alamat pelanggan" rows="2" class="w-full rounded-lg border text-xs px-3 py-2 border-dark-200 text-dark-800 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 focus:outline-none"></textarea>
                        </div>
                        <div>
                            <label class="block font-semibold text-dark-600 mb-1">Email (opsional)</label>
                            <input type="email" v-model="newCustomerForm.email" placeholder="email@example.com" class="w-full rounded-lg border text-xs px-3 py-2 border-dark-200 text-dark-800 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 focus:outline-none" />
                        </div>
                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" @click="showAddCustomerModal = false" class="px-4 py-2 rounded-lg border text-xs font-semibold border-dark-200 text-dark-600 hover:bg-dark-50">Batal</button>
                            <button type="submit" :disabled="savingCustomer" class="px-4 py-2 rounded-lg text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50">{{ savingCustomer ? 'Menyimpan...' : 'Simpan' }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DynamicFormFields from '@/Components/DynamicFormFields.vue';

const props = defineProps({
    customers: { type: Array, default: () => [] },
    templates: { type: Array, default: () => [] },
    products: { type: Array, default: () => [] },
    deviceCategories: { type: Array, default: () => [] },
    brands: { type: Array, default: () => [] },
    arrivalMethods: { type: Array, default: () => [] },
    equipment: { type: Array, default: () => [] },
    driveConnected: { type: Boolean, default: false },
    customFields: { type: Array, default: () => [] },
});

const photoFiles = ref([]);
const photoPreviews = ref([]);

const form = useForm({
    customer_id: '',
    jalur_kedatangan_id: '',
    kategori_perangkat_id: '',
    merek_id: '',
    tipe_unit: '',
    imei_sn: '',
    sandi_pola: '',
    kelengkapan: [],
    checklist_template_id: '',
    checked_items: [],
    problem_description: '',
    condition_note: '',
});

const customerList = ref([...props.customers]);
const showAddCustomerModal = ref(false);
const savingCustomer = ref(false);
const newCustomerForm = ref({ name: '', phone: '', email: '', address: '' });

const customerOptions = computed(() => customerList.value);

const selectedCustomer = computed(() => {
    return customerList.value.find(c => c.id == form.customer_id);
});

const openAddCustomerModal = () => {
    newCustomerForm.value = { name: '', phone: '', email: '', address: '' };
    showAddCustomerModal.value = true;
};

const submitFastCustomer = async () => {
    if (!newCustomerForm.value.name) return;
    savingCustomer.value = true;
    try {
        const { default: axios } = await import('axios');
        const response = await axios.post(route('customers.api-store'), newCustomerForm.value);
        if (response.data.success && response.data.customer) {
            customerList.value.unshift(response.data.customer);
            form.customer_id = response.data.customer.id;
            showAddCustomerModal.value = false;
        }
    } catch (e) {
        console.error('Fast Customer Error:', e);
        alert(e.response?.data?.message || 'Gagal menyimpan pelanggan.');
    } finally {
        savingCustomer.value = false;
    }
};

const selectedTemplate = computed(() => {
    return props.templates.find(t => t.id == form.checklist_template_id);
});

const onPhotosChange = (e) => {
    const files = Array.from(e.target.files);
    const remaining = 10 - photoFiles.value.length;
    const toAdd = files.slice(0, remaining);
    photoFiles.value = [...photoFiles.value, ...toAdd];
    toAdd.forEach(f => {
        photoPreviews.value.push(URL.createObjectURL(f));
    });
};

const removePhoto = (idx) => {
    photoFiles.value.splice(idx, 1);
    photoPreviews.value.splice(idx, 1);
};

const submit = () => {
    const data = new FormData();
    data.append('customer_id', form.customer_id);
    data.append('problem_description', form.problem_description);
    data.append('condition_note', form.condition_note);
    if (form.checklist_template_id) data.append('checklist_template_id', form.checklist_template_id);
    form.checked_items.forEach((item, i) => data.append(`checked_items[${i}]`, item));
    if (form.jalur_kedatangan_id) data.append('jalur_kedatangan_id', form.jalur_kedatangan_id);
    if (form.kategori_perangkat_id) data.append('kategori_perangkat_id', form.kategori_perangkat_id);
    if (form.merek_id) data.append('merek_id', form.merek_id);
    if (form.tipe_unit) data.append('tipe_unit', form.tipe_unit);
    if (form.imei_sn) data.append('imei_sn', form.imei_sn);
    if (form.sandi_pola) data.append('sandi_pola', form.sandi_pola);
    if (form.kelengkapan.length) form.kelengkapan.forEach((item, i) => data.append(`kelengkapan[${i}]`, item));
    photoFiles.value.forEach((file, i) => data.append(`photos[${i}]`, file));
    form.post(route('services.store'), { data });
};
</script>
