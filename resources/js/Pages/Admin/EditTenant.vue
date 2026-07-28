<template>
    <AdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Tenant: {{ tenant.tenant_name }}</h2>
                <Link :href="route('admin.tenant.index')" class="text-sm text-indigo-600 hover:text-indigo-500">← Kembali</Link>
            </div>
        </template>

        <!-- Flash Message -->
        <div v-if="$page.props.flash?.success" class="mb-4 p-4 bg-green-50 border border-green-200 rounded-md">
            <p class="text-sm text-green-700">{{ $page.props.flash.success }}</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Form Edit -->
            <div class="lg:col-span-2 rounded-2xl p-6 border" style="background: var(--bg-card); border-color: var(--border-color);">
                <h3 class="font-semibold text-gray-900 mb-4">Informasi Dasar</h3>
                <form @submit.prevent="submit">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Nama Toko</label>
                            <input v-model="form.tenant_name" type="text" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
                            <p v-if="form.errors.tenant_name" class="mt-1 text-xs text-red-600">{{ form.errors.tenant_name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input v-model="form.email" type="email" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
                            <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Telepon</label>
                            <input v-model="form.phone" type="text"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
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

                    <div class="flex justify-end gap-3 mt-6 pt-4 border-t">
                        <Link :href="route('admin.tenant.index')"
                            class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                            Batal
                        </Link>
                        <button type="submit" :disabled="form.processing"
                            class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50 text-sm">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Sidebar Actions -->
            <div class="space-y-4">
                <!-- Quick Actions -->
                <div class="rounded-2xl p-6 border">
                    <h3 class="font-semibold text-gray-900 mb-4">⚡ Aksi Cepat</h3>
                    <div class="space-y-3">
                        <Link :href="route('admin.tenant.show', tenant.id)"
                            class="block w-full text-center px-4 py-2 bg-indigo-100 text-indigo-700 rounded-md text-sm hover:bg-indigo-200">
                            📋 Detail Tenant
                        </Link>
                        <a v-if="domainUrl" :href="domainUrl" target="_blank"
                            class="block w-full text-center px-4 py-2 bg-green-100 text-green-700 rounded-md text-sm hover:bg-green-200">
                            🔗 Buka Toko
                        </a>
                    </div>
                </div>

                <!-- Change Plan -->
                <div class="rounded-2xl p-6 border">
                    <h3 class="font-semibold text-gray-900 mb-4">📦 Ganti Paket</h3>
                    <form @submit.prevent="changePlan">
                        <select v-model="planForm.plan_id" required
                            class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500 mb-3"
                            @change="onPlanChange">
                            <option value="">Pilih Paket</option>
                            <option v-for="p in plans" :key="p.id" :value="p.id" :selected="tenant.plan_id === p.id">
                                {{ p.name }} - Rp {{ formatNumber(p.effective_price || p.price) }}/bln
                            </option>
                        </select>

                        <!-- Simulasi Upgrade/Downgrade -->
                        <div v-if="simulation" class="p-3 rounded-lg mb-3 text-sm"
                            :class="simulation.type === 'upgrade' ? 'bg-blue-50 border border-blue-200' : simulation.type === 'downgrade' ? 'bg-yellow-50 border border-yellow-200' : 'bg-gray-50 border border-gray-200'">
                            <div class="flex items-center gap-2 mb-2">
                                <span v-if="simulation.type === 'upgrade'" class="text-blue-600 font-semibold">⬆️ Upgrade</span>
                                <span v-else-if="simulation.type === 'downgrade'" class="text-yellow-600 font-semibold">⬇️ Downgrade</span>
                                <span v-else class="text-gray-600 font-semibold">🔄 Paket Sama</span>
                            </div>
                            <div class="space-y-1 text-xs">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Paket Lama:</span>
                                    <span class="font-medium">{{ simulation.old_plan }} — Rp {{ formatNumber(simulation.old_price) }}/bln</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Paket Baru:</span>
                                    <span class="font-medium">{{ simulation.new_plan }} — Rp {{ formatNumber(simulation.new_price) }}/bln</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Sisa Periode:</span>
                                    <span class="font-medium">{{ simulation.remaining_days }} hari</span>
                                </div>
                                <hr class="my-1">
                                <div class="flex justify-between font-medium"
                                    :class="simulation.diff > 0 ? 'text-blue-700' : simulation.diff < 0 ? 'text-yellow-700' : 'text-gray-700'">
                                    <span>{{ simulation.diff > 0 ? 'Tambahan Biaya:' : simulation.diff < 0 ? 'Kredit:' : 'Tidak Ada Biaya:' }}</span>
                                    <span>Rp {{ formatNumber(Math.abs(simulation.diff)) }}</span>
                                </div>
                            </div>
                        </div>

                        <button type="submit" :disabled="planForm.processing || !planForm.plan_id"
                            class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700 disabled:opacity-50">
                            {{ planForm.processing ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </form>
                </div>

                <!-- Extend Trial -->
                <div class="rounded-2xl p-6 border">
                    <h3 class="font-semibold text-gray-900 mb-4">⏰ Perpanjang Trial</h3>
                    <p class="text-xs text-gray-500 mb-3">Trial saat ini: {{ tenant.trial_ends_at || 'Tidak ada' }}</p>
                    <form @submit.prevent="extendTrial">
                        <div class="flex gap-2">
                            <select v-model="trialForm.days"
                                class="flex-1 rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="7">7 hari</option>
                                <option value="14">14 hari</option>
                                <option value="30">30 hari</option>
                                <option value="60">60 hari</option>
                                <option value="90">90 hari</option>
                            </select>
                            <button type="submit" :disabled="trialForm.processing"
                                class="px-4 py-2 bg-yellow-500 text-white rounded-md text-sm hover:bg-yellow-600 disabled:opacity-50">
                                Perpanjang
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Danger Zone -->
                <div class="rounded-2xl p-6 border border-2 border-red-200">
                    <h3 class="font-semibold text-red-600 mb-4">⚠️ Zona Berbahaya</h3>

                    <!-- Tenant Aktif -> Harus Suspend Dulu -->
                    <template v-if="tenant.is_active">
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-3">
                            <p class="text-xs text-yellow-800">
                                🛑 Tenant masih <strong>aktif</strong>. Untuk menghapus, harus di-<strong>Suspend</strong> terlebih dahulu.
                            </p>
                        </div>
                        <button @click="suspendTenant" :disabled="suspendForm.processing"
                            class="w-full px-4 py-2 bg-orange-500 text-white rounded-md text-sm hover:bg-orange-600 disabled:opacity-50">
                            🔒 Suspend Tenant
                        </button>
                    </template>

                    <!-- Tenant Tidak Aktif -> Bisa Hapus -->
                    <template v-else>
                        <p class="text-xs text-gray-500 mb-3">Menghapus tenant akan menghapus semua data termasuk database tenant.</p>
                        <button @click="confirmDelete"
                            class="w-full px-4 py-2 bg-red-600 text-white rounded-md text-sm hover:bg-red-700">
                            🗑️ Hapus Tenant
                        </button>
                    </template>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="showDeleteModal = false">
            <div class="rounded-2xl p-6 max-w-md mx-4 border" style="background: var(--bg-card); border-color: var(--border-color); box-shadow: var(--shadow-lg);">
                <h3 class="text-lg font-bold text-red-600 mb-2">Konfirmasi Hapus Tenant</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Apakah Anda yakin ingin menghapus <strong>{{ tenant.tenant_name }}</strong>?<br>
                    Tindakan ini <strong>tidak bisa dibatalkan</strong>. Semua data termasuk database tenant akan dihapus permanen.
                </p>
                <div class="flex justify-end gap-3">
                    <button @click="showDeleteModal = false" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md text-sm hover:bg-gray-200">
                        Batal
                    </button>
                    <button @click="deleteTenant" :disabled="deleteForm.processing"
                        class="px-4 py-2 bg-red-600 text-white rounded-md text-sm hover:bg-red-700 disabled:opacity-50">
                        {{ deleteForm.processing ? 'Menghapus...' : 'Ya, Hapus!' }}
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { useForm, Link, router } from '@inertiajs/vue3';
import { ref, computed, reactive } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    tenant: { type: Object, required: true },
    plans: { type: Array, default: () => [] },
});

const showDeleteModal = ref(false);
const simulation = ref(null);

const form = useForm({
    tenant_name: props.tenant.tenant_name || '',
    email: props.tenant.email || '',
    phone: props.tenant.phone || '',
    business_type: props.tenant.data?.business_type || 'full_service',
});

const planForm = useForm({
    plan_id: '',
});

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num || 0);

const domainUrl = computed(() => {
    if (props.tenant.domains?.length > 0) {
        return 'http://' + props.tenant.domains[0].domain + '/login';
    }
    return null;
});

// Hitung simulasi prorata saat pilih paket berbeda
function onPlanChange() {
    const newPlanId = planForm.plan_id;
    if (!newPlanId || newPlanId == props.tenant.plan_id) {
        simulation.value = null;
        return;
    }

    const oldPlan = props.plans.find(p => p.id == props.tenant.plan_id);
    const newPlan = props.plans.find(p => p.id == newPlanId);
    if (!oldPlan || !newPlan) {
        simulation.value = null;
        return;
    }

    const oldPrice = oldPlan.effective_price || oldPlan.price;
    const newPrice = newPlan.effective_price || newPlan.price;

    // Tentukan sisa hari dalam periode billing
    const billingEnd = props.tenant.subscription_ends_at || null;
    const billingStart = props.tenant.subscribed_at || props.tenant.trial_ends_at || props.tenant.created_at;
    const totalDays = 30; // asumsi 30 hari per siklus

    let remainingDays = 0;
    if (billingEnd) {
        const end = new Date(billingEnd);
        const now = new Date();
        remainingDays = Math.max(0, Math.ceil((end - now) / (1000 * 60 * 60 * 24)));
    } else {
        remainingDays = totalDays;
    }

    if (remainingDays === 0) remainingDays = 1;

    // Hitung prorata
    const remainingValueOld = (oldPrice / totalDays) * remainingDays;
    const proratedCostNew = (newPrice / totalDays) * remainingDays;
    const diff = proratedCostNew - remainingValueOld;

    let type = 'same';
    if (diff > 1000) type = 'upgrade';
    else if (diff < -1000) type = 'downgrade';

    simulation.value = {
        type,
        old_plan: oldPlan.name,
        new_plan: newPlan.name,
        old_price: oldPrice,
        new_price: newPrice,
        remaining_days: remainingDays,
        diff: Math.round(diff),
    };
}

const trialForm = useForm({
    days: 14,
});

const deleteForm = useForm({});
const suspendForm = useForm({});

const submit = () => {
    form.post(route('admin.tenant.update', props.tenant.id));
};

const changePlan = () => {
    planForm.post(route('admin.tenant.change-plan', props.tenant.id));
};

const extendTrial = () => {
    trialForm.post(route('admin.tenant.extend-trial', props.tenant.id));
};

const suspendTenant = () => {
    suspendForm.post(route('admin.tenant.suspend', props.tenant.id));
};

const confirmDelete = () => {
    showDeleteModal.value = true;
};

const deleteTenant = () => {
    deleteForm.post(route('admin.tenant.delete', props.tenant.id));
};
</script>
