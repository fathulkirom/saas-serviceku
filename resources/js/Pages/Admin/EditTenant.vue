<template>
    <AdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-black text-2xl text-slate-100 tracking-tight">Edit Tenant: <span class="text-indigo-400">{{ tenant.tenant_name }}</span></h2>
                <Link :href="route('admin.tenant.index')" class="text-sm font-bold text-slate-400 hover:text-slate-300 transition-colors">&larr; Kembali</Link>
            </div>
        </template>

        <!-- Flash Message -->
        <div v-if="$page.props.flash?.success" class="mb-5 p-4 rounded-xl border flex items-center gap-3 animate-slide-down bg-emerald-500/10 border-emerald-500/20">
            <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <p class="text-sm text-emerald-300 font-medium">{{ $page.props.flash.success }}</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Form Edit -->
            <div class="lg:col-span-2 rounded-2xl p-6 border bg-slate-800/50 border-slate-700/50 backdrop-blur-xl shadow-sm">
                <h3 class="font-bold text-lg text-slate-100 mb-6">Informasi Dasar</h3>
                <form @submit.prevent="submit">
                    <div class="grid grid-cols-2 gap-5">
                        <div class="col-span-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Nama Toko</label>
                            <KInput  v-model="form.tenant_name" type="text" required
                                class="w-full rounded-xl text-sm py-2.5 px-4 border transition-all duration-200 focus:ring-2 focus:ring-indigo-500/50 focus:outline-none bg-slate-900/50 border-slate-700 text-slate-200 placeholder-slate-500" />
                            <p v-if="form.errors.tenant_name" class="mt-1.5 text-xs font-medium text-rose-400">{{ form.errors.tenant_name }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Email</label>
                            <KInput  v-model="form.email" type="email" required
                                class="w-full rounded-xl text-sm py-2.5 px-4 border transition-all duration-200 focus:ring-2 focus:ring-indigo-500/50 focus:outline-none bg-slate-900/50 border-slate-700 text-slate-200 placeholder-slate-500" />
                            <p v-if="form.errors.email" class="mt-1.5 text-xs font-medium text-rose-400">{{ form.errors.email }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Telepon</label>
                            <KInput  v-model="form.phone" type="text"
                                class="w-full rounded-xl text-sm py-2.5 px-4 border transition-all duration-200 focus:ring-2 focus:ring-indigo-500/50 focus:outline-none bg-slate-900/50 border-slate-700 text-slate-200 placeholder-slate-500" />
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

                    <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-slate-700/50">
                        <KButton  type="submit" :disabled="form.processing"
                            class="px-6 py-2.5 rounded-xl text-sm font-bold text-white transition-all shadow-sm hover:shadow-md hover:-translate-y-0.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:hover:translate-y-0 flex items-center gap-2">
                            <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </KButton>
                    </div>
                </form>
            </div>

            <!-- Sidebar Actions -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="rounded-2xl p-6 border bg-slate-800/50 border-slate-700/50 backdrop-blur-xl shadow-sm">
                    <h3 class="font-bold text-lg text-slate-100 mb-4 flex items-center gap-2">
                        ⚡ Aksi Cepat
                    </h3>
                    <div class="space-y-3">
                        <Link :href="route('admin.tenant.show', tenant.id)"
                            class="block w-full text-center px-4 py-2.5 bg-indigo-500/10 text-indigo-400 font-bold rounded-xl text-sm hover:bg-indigo-500/20 transition-colors">
                            📋 Detail Tenant
                        </Link>
                    </div>
                </div>

                <!-- Change Plan -->
                <div class="rounded-2xl p-6 border bg-slate-800/50 border-slate-700/50 backdrop-blur-xl shadow-sm">
                    <h3 class="font-bold text-lg text-slate-100 mb-4 flex items-center gap-2">
                        📦 Ganti Paket
                    </h3>
                    <form @submit.prevent="changePlan">
                        <KSelect  v-model="planForm.plan_id" required
                            class="w-full rounded-xl text-sm py-2.5 px-4 border transition-all duration-200 focus:ring-2 focus:ring-indigo-500/50 focus:outline-none bg-slate-900/50 border-slate-700 text-slate-200 mb-4"
                            @change="onPlanChange">
                            <option value="">Pilih Paket</option>
                            <option v-for="p in plans" :key="p.id" :value="p.id" :selected="tenant.plan_id === p.id">
                                {{ p.name }} - Rp {{ formatNumber(p.effective_price || p.price) }}/bln
                            </option>
                        </KSelect>

                        <!-- Simulasi Upgrade/Downgrade -->
                        <div v-if="simulation" class="p-4 rounded-xl mb-4 text-sm"
                            :class="simulation.type === 'upgrade' ? 'bg-indigo-500/10 border border-indigo-500/20' : simulation.type === 'downgrade' ? 'bg-amber-500/10 border border-amber-500/20' : 'bg-slate-700/30 border border-slate-600/50'">
                            <div class="flex items-center gap-2 mb-3">
                                <span v-if="simulation.type === 'upgrade'" class="text-indigo-400 font-bold">⬆️ Upgrade</span>
                                <span v-else-if="simulation.type === 'downgrade'" class="text-amber-400 font-bold">⬇️ Downgrade</span>
                                <span v-else class="text-slate-300 font-bold">🔄 Paket Sama</span>
                            </div>
                            <div class="space-y-1.5 text-xs font-medium">
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Paket Lama:</span>
                                    <span class="text-slate-200">{{ simulation.old_plan }} — Rp {{ formatNumber(simulation.old_price) }}/bln</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Paket Baru:</span>
                                    <span class="text-slate-200">{{ simulation.new_plan }} — Rp {{ formatNumber(simulation.new_price) }}/bln</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Sisa Periode:</span>
                                    <span class="text-slate-200">{{ simulation.remaining_days }} hari</span>
                                </div>
                                <hr class="my-2 border-slate-700/50">
                                <div class="flex justify-between font-bold"
                                    :class="simulation.diff > 0 ? 'text-indigo-400' : simulation.diff < 0 ? 'text-amber-400' : 'text-slate-300'">
                                    <span>{{ simulation.diff > 0 ? 'Tambahan Biaya:' : simulation.diff < 0 ? 'Kredit:' : 'Tidak Ada Biaya:' }}</span>
                                    <span>Rp {{ formatNumber(Math.abs(simulation.diff)) }}</span>
                                </div>
                            </div>
                        </div>

                        <KButton  type="submit" :disabled="planForm.processing"
                            class="w-full px-4 py-2.5 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition-all shadow-sm hover:shadow-md hover:-translate-y-0.5 disabled:opacity-50 text-sm flex items-center justify-center gap-2">
                            <svg v-if="planForm.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            {{ planForm.processing ? 'Menyimpan...' : 'Simpan Paket' }}
                        </KButton>
                    </form>
                </div>

                <!-- Extend Trial -->
                <div v-if="tenant.subscription_status === 'trial'" class="rounded-2xl p-6 border bg-slate-800/50 border-slate-700/50 backdrop-blur-xl shadow-sm">
                    <h3 class="font-bold text-lg text-slate-100 mb-4 flex items-center gap-2">⏰ Perpanjang Trial</h3>
                    <p class="text-xs text-slate-400 mb-3">Trial saat ini: <span class="text-slate-200">{{ tenant.trial_ends_at || 'Tidak ada' }}</span></p>
                    <form @submit.prevent="extendTrial">
                        <div class="flex gap-2">
                            <KSelect  v-model="trialForm.days"
                                class="flex-1 rounded-xl text-sm py-2.5 px-4 border transition-all duration-200 focus:ring-2 focus:ring-indigo-500/50 focus:outline-none bg-slate-900/50 border-slate-700 text-slate-200">
                                <option value="7">7 hari</option>
                                <option value="14">14 hari</option>
                                <option value="30">30 hari</option>
                                <option value="60">60 hari</option>
                                <option value="90">90 hari</option>
                            </KSelect>
                            <KButton  type="submit" :disabled="trialForm.processing"
                                class="px-5 py-2.5 bg-yellow-500 text-yellow-950 font-bold rounded-xl hover:bg-yellow-400 transition-all shadow-sm hover:shadow-md hover:-translate-y-0.5 disabled:opacity-50 text-sm">
                                Perpanjang
                            </KButton>
                        </div>
                    </form>
                </div>

                <!-- Extend Subscription (for paid plans) -->
                <div v-if="tenant.subscription_status === 'active'" class="rounded-2xl p-6 border bg-slate-800/50 border-slate-700/50 backdrop-blur-xl shadow-sm">
                    <h3 class="font-bold text-lg text-slate-100 mb-4 flex items-center gap-2">📅 Perpanjang Paket Aktif</h3>
                    <p class="text-xs text-slate-400 mb-3">Masa aktif saat ini: <span class="text-emerald-300">{{ tenant.subscription_ends_at || 'Seumur hidup' }}</span></p>
                    <form @submit.prevent="extendSubscription">
                        <div class="flex gap-2">
                            <KSelect  v-model="subscriptionForm.months"
                                class="flex-1 rounded-xl text-sm py-2.5 px-4 border transition-all duration-200 focus:ring-2 focus:ring-indigo-500/50 focus:outline-none bg-slate-900/50 border-slate-700 text-slate-200">
                                <option value="1">1 bulan</option>
                                <option value="3">3 bulan</option>
                                <option value="6">6 bulan</option>
                                <option value="12">12 bulan (1 tahun)</option>
                                <option value="24">24 bulan (2 tahun)</option>
                            </KSelect>
                            <KButton  type="submit" :disabled="subscriptionForm.processing"
                                class="px-5 py-2.5 bg-emerald-500 text-emerald-950 font-bold rounded-xl hover:bg-emerald-400 transition-all shadow-sm hover:shadow-md hover:-translate-y-0.5 disabled:opacity-50 text-sm">
                                Perpanjang
                            </KButton>
                        </div>
                    </form>
                </div>

                <!-- Danger Zone -->
                <div class="rounded-2xl p-6 border bg-rose-950/20 border-rose-900/50 backdrop-blur-xl shadow-sm">
                    <h3 class="font-bold text-lg text-rose-500 mb-4 flex items-center gap-2">⚠️ Zona Berbahaya</h3>

                    <!-- Tenant Aktif -> Harus Suspend Dulu -->
                    <template v-if="tenant.is_active">
                        <div class="bg-amber-950/30 border border-amber-900/50 rounded-xl p-4 mb-4">
                            <p class="text-sm font-medium text-amber-500 leading-relaxed">
                                🛑 Tenant masih <strong class="text-amber-400 font-bold">aktif</strong>. Untuk menghapus, harus di-<strong class="text-amber-400 font-bold">Suspend</strong> terlebih dahulu.
                            </p>
                        </div>
                        <KButton  @click="suspendTenant" :disabled="suspendForm.processing"
                            class="w-full px-4 py-2.5 bg-orange-600/20 border border-orange-500/30 text-orange-400 font-bold rounded-xl hover:bg-orange-600/30 transition-all shadow-sm hover:-translate-y-0.5 disabled:opacity-50 text-sm">
                            🔒 Suspend Tenant
                        </KButton>
                    </template>

                    <!-- Tenant Tidak Aktif -> Bisa Hapus -->
                    <template v-else>
                        <p class="text-sm font-medium text-rose-400/80 mb-4 leading-relaxed">Menghapus tenant akan menghapus semua data termasuk database tenant permanen.</p>
                        <KButton  @click="confirmDelete"
                            class="w-full px-4 py-2.5 bg-rose-600 text-white font-bold rounded-xl hover:bg-rose-700 transition-all shadow-sm hover:shadow-md hover:-translate-y-0.5 text-sm">
                            🗑️ Hapus Tenant
                        </KButton>
                    </template>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center z-50 animate-fade-in" @click.self="showDeleteModal = false">
            <div class="rounded-3xl p-8 max-w-md mx-4 border bg-slate-900 border-slate-700 shadow-2xl animate-slide-up">
                <h3 class="text-2xl font-black text-rose-500 mb-3 tracking-tight">Konfirmasi Hapus Tenant</h3>
                <p class="text-sm font-medium text-slate-300 mb-6 leading-relaxed">
                    Apakah Anda yakin ingin menghapus <strong class="text-white">{{ tenant.tenant_name }}</strong>?<br><br>
                    Tindakan ini <strong class="text-rose-400">tidak bisa dibatalkan</strong>. Semua data termasuk database tenant akan dihapus permanen.
                </p>
                <div class="flex justify-end gap-3">
                    <KButton  @click="showDeleteModal = false" class="px-5 py-2.5 bg-slate-800 text-slate-300 font-bold rounded-xl hover:bg-slate-700 transition-colors text-sm">
                        Batal
                    </KButton>
                    <KButton  @click="deleteTenant" :disabled="deleteForm.processing"
                        class="px-6 py-2.5 bg-rose-600 text-white font-bold rounded-xl hover:bg-rose-700 transition-all shadow-sm hover:shadow-md hover:-translate-y-0.5 disabled:opacity-50 text-sm flex items-center gap-2">
                        <svg v-if="deleteForm.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        {{ deleteForm.processing ? 'Menghapus...' : 'Ya, Hapus' }}
                    </KButton>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import KButton from '@/Components/KButton.vue';
import KInput from '@/Components/KInput.vue';
import KSelect from '@/Components/KSelect.vue';

import { useForm, Link, router } from '@inertiajs/vue3';
import { ref, reactive } from 'vue';
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

const subscriptionForm = useForm({
    months: 1,
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

const extendSubscription = () => {
    subscriptionForm.post(route('admin.tenant.extend-subscription', props.tenant.id));
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
