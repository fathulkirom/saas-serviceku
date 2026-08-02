<template>
    <AuthenticatedLayout>
        <div class="flex flex-col gap-6">
            <!-- PAGE HEADER -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-2">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-zinc-900">Daftar Servis</h2>
                    <p class="text-sm text-zinc-500 mt-1">Kelola seluruh tiket servis dan perbaikan unit pelanggan.</p>
                </div>
                <div class="flex items-center gap-2">
                    <KButton  @click="clearFilters" class="hidden sm:inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-zinc-50 border border-zinc-200 text-zinc-700 text-sm font-bold rounded-xl transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Refresh
                    </KButton>
                    <Link :href="route('services.create')" class="inline-flex items-center gap-2 px-4 py-2 bg-zinc-900 hover:bg-zinc-800 text-white text-sm font-bold rounded-xl transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Buat Tiket Baru
                    </Link>
                </div>
            </div>

            <!-- SEARCH & FILTERS -->
            <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-5">
                <div class="flex flex-col md:flex-row md:items-end gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-2">Cari Pelanggan</label>
                        <div class="relative">
                            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <KInput  type="text" v-model="filters.customer_name" placeholder="Nama pelanggan..." class="w-full rounded-xl border border-zinc-300 text-sm pl-10 pr-4 py-2.5 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all text-zinc-900 bg-white" />
                        </div>
                    </div>
                    <div class="flex-1 min-w-[150px]">
                        <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-2">No. Telepon / WA</label>
                        <KInput  type="text" v-model="filters.phone" placeholder="0812..." class="w-full rounded-xl border border-zinc-300 text-sm px-4 py-2.5 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all text-zinc-900 bg-white" />
                    </div>
                    <div class="flex-1 min-w-[150px]">
                        <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-2">Nomor Tiket (SR)</label>
                        <KInput  type="text" v-model="filters.sr_code" placeholder="SR1000..." class="w-full rounded-xl border border-zinc-300 text-sm px-4 py-2.5 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all text-zinc-900 bg-white" />
                    </div>
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-2">Status Servis</label>
                        <KSelect  v-model="activeFilter" class="w-full rounded-xl border border-zinc-300 text-sm px-4 py-2.5 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all text-zinc-900 bg-white">
                            <option v-for="opt in filterOptions" :key="opt.value" :value="opt.value">
                                {{ opt.label }} ({{ countByStatus(opt.value) }})
                            </option>
                        </KSelect>
                    </div>
                </div>
            </div>

            <!-- BULK ACTIONS BAR -->
            <div v-if="selectedIds.length > 0" class="flex items-center justify-between p-4 rounded-2xl bg-indigo-50 text-indigo-900 border border-indigo-200 shadow-sm animate-in slide-in-from-top-2">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="text-sm font-bold">{{ selectedIds.length }} baris dipilih</span>
                </div>
                <div class="flex items-center gap-2">
                    <KButton  v-if="selectedAcceptableIds.length > 0" @click="bulkUpdateStatus('diterima')" class="px-4 py-2 text-sm font-bold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition-colors shadow-sm">
                        Terima Tiket ({{ selectedAcceptableIds.length }})
                    </KButton>
                    <KButton  v-if="selectedCancelableIds.length > 0" @click="bulkUpdateStatus('cancel')" class="px-4 py-2 text-sm font-bold bg-red-600 hover:bg-red-700 text-white rounded-xl transition-colors shadow-sm">
                        Batalkan ({{ selectedCancelableIds.length }})
                    </KButton>
                    <KButton  @click="selectedIds = []" class="px-4 py-2 text-sm font-bold text-indigo-700 bg-white border border-indigo-200 hover:bg-indigo-50 rounded-xl transition-colors shadow-sm">
                        Batal Pilihan
                    </KButton>
                </div>
            </div>

            <!-- DATA TABLE -->
            <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs font-bold text-zinc-500 uppercase bg-zinc-50/50 border-b border-zinc-200 tracking-wider">
                            <tr>
                                <th scope="col" class="px-5 py-4 w-10 text-center">
                                    <KCheckbox  :checked="allSelected" @change="toggleAll" class="w-4 h-4 rounded border-zinc-300 text-indigo-600 focus:ring-indigo-600 cursor-pointer" :disabled="selectableServiceIds.length === 0" />
                                </th>
                                <th scope="col" class="px-5 py-4">Tiket</th>
                                <th scope="col" class="px-5 py-4">Pelanggan</th>
                                <th scope="col" class="px-5 py-4">Unit & Problem</th>
                                <th scope="col" class="px-5 py-4">Status</th>
                                <th scope="col" class="px-5 py-4">Teknisi</th>
                                <th scope="col" class="px-5 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            <tr v-if="paginatedServices.length === 0">
                                <td colspan="7" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-3">
                                        <div class="w-12 h-12 rounded-full bg-zinc-50 border border-zinc-100 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                        </div>
                                        <p class="text-sm font-medium text-zinc-500">Tidak ada data tiket servis.</p>
                                        <Link :href="route('services.create')" class="mt-2 text-sm font-bold text-indigo-600 hover:text-indigo-700">
                                            + Buat Tiket Baru
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                            
                            <tr v-for="service in paginatedServices" :key="service.id" 
                                class="hover:bg-zinc-50/50 transition-colors cursor-pointer group"
                                @click="router.visit(route('services.show', service.id))">
                                
                                <td class="px-5 py-4 text-center" @click.stop>
                                    <KCheckbox  :value="service.id" v-model="selectedIds" class="w-4 h-4 rounded border-zinc-300 text-indigo-600 focus:ring-indigo-600 cursor-pointer" :disabled="!canBulkActOnService(service)" />
                                </td>
                                
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="font-mono text-sm font-bold text-zinc-900 bg-zinc-100 px-2.5 py-1 rounded-md inline-block border border-zinc-200">SR{{ 1000 + service.id }}</div>
                                    <div class="text-[11px] font-medium text-zinc-400 mt-1.5 ml-1">{{ formatDate(service.created_at) }}</div>
                                </td>
                                
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-zinc-100 border border-zinc-200 flex items-center justify-center text-xs font-bold text-zinc-600 shrink-0">
                                            {{ getInitials(service.customer?.name) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold text-zinc-900 truncate">{{ service.customer?.name || '-' }}</p>
                                            <p class="text-xs font-mono font-medium text-zinc-500 mt-0.5">{{ service.customer?.phone || '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="px-5 py-4">
                                    <p class="text-sm font-bold text-zinc-900">{{ service.tipe_unit || 'Unknown Unit' }}</p>
                                    <p class="text-xs text-zinc-500 truncate max-w-[200px] mt-0.5 leading-relaxed" :title="service.problem_description">{{ service.problem_description || '-' }}</p>
                                </td>
                                
                                <td class="px-5 py-4">
                                    <span :class="['inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold border tracking-wide uppercase', statusClass(service.status)]">
                                        {{ statusLabel(service.status) }}
                                    </span>
                                </td>
                                
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2.5">
                                        <div v-if="service.technician" class="w-7 h-7 rounded-full bg-indigo-50 border border-indigo-100 flex items-center justify-center text-[10px] font-bold text-indigo-700 shrink-0">
                                            {{ getInitials(service.technician.name) }}
                                        </div>
                                        <div v-else class="w-7 h-7 rounded-full border border-dashed border-zinc-300 flex items-center justify-center text-zinc-400 shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        </div>
                                        <span class="text-xs font-medium text-zinc-700 truncate max-w-[120px]">{{ service.technician?.name || 'Belum Dialokasi' }}</span>
                                    </div>
                                </td>
                                
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <KButton  @click.stop="router.visit(route('services.show', service.id))" class="w-8 h-8 rounded-lg flex items-center justify-center text-zinc-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors" title="Detail">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </KButton>
                                        <a :href="route('services.print-receipt', service.id)" target="_blank" @click.stop class="w-8 h-8 rounded-lg flex items-center justify-center text-zinc-400 hover:text-zinc-900 hover:bg-zinc-100 transition-colors" title="Cetak Tanda Terima">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        </a>
                                        <KButton  v-if="canCancel(service)" @click.stop="confirmCancel(service)" class="w-8 h-8 rounded-lg flex items-center justify-center text-zinc-400 hover:text-red-600 hover:bg-red-50 transition-colors" title="Batalkan">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </KButton>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- PAGINATION -->
                <div class="border-t border-zinc-200 bg-zinc-50/50 p-4">
                    <Pagination :meta="services" :per-page="services.per_page" />
                </div>
            </div>
        </div>

        <!-- MODALS -->
        <Teleport to="body">
            <!-- CANCEL MODAL -->
            <div v-if="showCancelModal" class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-900/40 backdrop-blur-sm" @click.self="showCancelModal = false">
                <div class="bg-white w-full max-w-sm mx-4 rounded-2xl shadow-xl border border-zinc-200 overflow-hidden animate-in zoom-in-95 duration-200">
                    <div class="p-6 text-center">
                        <div class="w-14 h-14 rounded-full bg-red-50 border border-red-100 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        </div>
                        <h3 class="text-xl font-bold text-zinc-900">Batalkan Tiket Servis?</h3>
                        <p class="text-sm text-zinc-500 mt-2">Tiket <strong>SR{{ 1000 + (cancelTarget?.id || 0) }}</strong> ({{ cancelTarget?.customer?.name }}).<br>Aksi ini tidak dapat dibatalkan.</p>
                    </div>
                    <div class="p-6 pt-0 flex gap-3">
                        <KButton  class="flex-1 px-4 py-2.5 rounded-xl text-sm font-bold text-zinc-700 bg-white border border-zinc-300 hover:bg-zinc-50 transition-colors" @click="showCancelModal = false">Batal</KButton>
                        <KButton  class="flex-1 px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-red-600 hover:bg-red-700 transition-colors flex justify-center items-center gap-2" @click="executeCancel" :disabled="processingAction === 'cancel'">
                            <svg v-if="processingAction === 'cancel'" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Ya, Batalkan
                        </KButton>
                    </div>
                </div>
            </div>
            
            <!-- ALOKASI MODAL -->
            <div v-if="showAlokasiModal" class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-900/40 backdrop-blur-sm" @click.self="showAlokasiModal = false">
                <div class="bg-white w-full max-w-sm mx-4 rounded-2xl shadow-xl border border-zinc-200 overflow-hidden animate-in zoom-in-95 duration-200">
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-zinc-900 mb-1">Alokasi Teknisi</h3>
                        <p class="text-sm text-zinc-500 mb-5">Tiket <strong>SR{{ 1000 + (alokasiTarget?.id || 0) }}</strong> — {{ alokasiTarget?.customer?.name }}</p>
                        
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1">Pilih Teknisi</label>
                            <KSelect  v-model="selectedTechnicianId" class="w-full rounded-xl border border-zinc-300 text-sm px-4 py-2.5 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all text-zinc-900 bg-white">
                                <option value="" disabled>-- Pilih Teknisi --</option>
                                <option v-for="user in userList" :key="user.id" :value="user.id">{{ user.name }}</option>
                            </KSelect>
                        </div>
                    </div>
                    <div class="p-6 pt-0 flex gap-3">
                        <KButton  class="flex-1 px-4 py-2.5 rounded-xl text-sm font-bold text-zinc-700 bg-white border border-zinc-300 hover:bg-zinc-50 transition-colors" @click="showAlokasiModal = false">Batal</KButton>
                        <KButton  class="flex-1 px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition-colors disabled:opacity-50 flex justify-center items-center gap-2" @click="executeAlokasi" :disabled="!selectedTechnicianId || processingAction === 'alokasi'">
                            <svg v-if="processingAction === 'alokasi'" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Simpan
                        </KButton>
                    </div>
                </div>
            </div>
        </Teleport>

    </AuthenticatedLayout>
</template>

<script setup>
import KButton from '@/Components/KButton.vue';
import KInput from '@/Components/KInput.vue';
import KSelect from '@/Components/KSelect.vue';
import KCheckbox from '@/Components/KCheckbox.vue';

import { ref, computed, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const page = usePage();
const authUser = computed(() => page.props.auth?.user || {});
const authUserRole = computed(() => authUser.value?.role || '');
const isOwner = computed(() => authUserRole.value === 'owner');
const isAdmin = computed(() => authUserRole.value === 'admin');
const isCs = computed(() => authUserRole.value === 'cs');
const isTechnician = computed(() => authUserRole.value === 'technician');
const currentUserId = computed(() => authUser.value?.id || null);
const canWork = computed(() => (page.props.role_permissions?.[authUserRole.value] || []).includes('work_on_services'));

const props = defineProps({
    services: { type: Object, default: () => ({ data: [], links: [], from: 0, to: 0, total: 0 }) },
    users: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({ all: 0 }) },
});

const activeFilter = ref('all');
const filters = ref({ customer_name: '', phone: '', sr_code: '' });

// Bulk actions
const selectedIds = ref([]);
const selectableServiceIds = computed(() => props.services.data.filter(canBulkActOnService).map(service => service.id));
const allSelected = computed({
    get: () => selectableServiceIds.value.length > 0 && selectedIds.value.length === selectableServiceIds.value.length,
    set: (val) => { selectedIds.value = val ? [...selectableServiceIds.value] : []; }
});
function toggleAll() { allSelected.value = !allSelected.value; }
const selectedServices = computed(() => props.services.data.filter(service => selectedIds.value.includes(service.id)));
const selectedAcceptableIds = computed(() => selectedServices.value.filter(canBulkAcceptService).map(service => service.id));
const selectedCancelableIds = computed(() => selectedServices.value.filter(canBulkCancelService).map(service => service.id));
const actionableSelectedIds = computed(() => Array.from(new Set([...selectedAcceptableIds.value, ...selectedCancelableIds.value])));

function bulkUpdateStatus(status) {
    const eligibleIds = status === 'diterima' ? selectedAcceptableIds.value : status === 'cancel' ? selectedCancelableIds.value : [];
    if (!eligibleIds.length) return;
    router.post(route('services.bulk-status'), { ids: eligibleIds, status }, { preserveScroll: true, onSuccess: () => { selectedIds.value = []; } });
}

// Modals
const processingAction = ref(null);
const showCancelModal = ref(false);
const cancelTarget = ref(null);
const showAlokasiModal = ref(false);
const alokasiTarget = ref(null);
const selectedTechnicianId = ref('');
const userList = computed(() => (props.users || []).filter(user => user.active !== false));

const filterOptions = [
    { value: 'all', label: 'Semua Tiket' },
    { value: 'menunggu_alokasi', label: 'Pending / Antrean' },
    { value: 'dikerjakan', label: 'Sedang Dikerjakan' },
    { value: 'indent', label: 'Menunggu Sparepart (Indent)' },
    { value: 'siap_diambil', label: 'Siap Diambil' },
    { value: 'selesai', label: 'Selesai' },
    { value: 'cancel', label: 'Dibatalkan' },
];

const allServices = computed(() => props.services.data || []);
const paginatedServices = computed(() => props.services.data || []);

watch(() => props.services.data, (services) => {
    const validIds = new Set((services || []).filter(canBulkActOnService).map(service => service.id));
    selectedIds.value = selectedIds.value.filter(id => validIds.has(id));
}, { immediate: true });

watch(activeFilter, (status) => {
    router.get(route('services.index'), { status: status === 'all' ? '' : status }, { preserveState: true, preserveScroll: true });
});

const getInitials = (name) => {
    if (!name) return '?';
    return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};

const statusLabel = (status) => ({
    menunggu_alokasi: 'Pending', diterima: 'Diterima', dikerjakan: 'On Progress',
    siap_diambil: 'Siap Diambil', indent: 'Waiting Parts', selesai: 'Selesai', cancel: 'Batal'
}[status] || status);

const statusClass = (status) => ({
    menunggu_alokasi: 'bg-amber-50 text-amber-700 border-amber-200',
    diterima: 'bg-blue-50 text-blue-700 border-blue-200',
    dikerjakan: 'bg-indigo-50 text-indigo-700 border-indigo-200',
    siap_diambil: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    indent: 'bg-purple-50 text-purple-700 border-purple-200',
    selesai: 'bg-zinc-100 text-zinc-700 border-zinc-200',
    cancel: 'bg-red-50 text-red-700 border-red-200',
}[status] || 'bg-zinc-100 text-zinc-600 border-zinc-200');

const countByStatus = (key) => {
    if (key === 'all') return allServices.value.length;
    if (key === 'dikerjakan') return allServices.value.filter(s => s.status === 'diterima' || s.status === 'dikerjakan').length;
    return allServices.value.filter(s => s.status === key).length;
};

function canBulkAcceptService(service) { return ['menunggu_alokasi'].includes(service?.status) && (isOwner.value || isTechnician.value); }
function canBulkCancelService(service) { return ['menunggu_alokasi', 'diterima', 'diagnosa', 'dikerjakan', 'indent'].includes(service?.status) && (isOwner.value || service?.technician_id === currentUserId.value); }
function canBulkActOnService(service) { return canBulkAcceptService(service) || canBulkCancelService(service); }
const clearFilters = () => { filters.value = { customer_name: '', phone: '', sr_code: '' }; activeFilter.value = 'all'; };
const isAssignedToMe = (service) => service?.technician_id === currentUserId.value;
const canManageAssignment = computed(() => isOwner.value || isAdmin.value || isCs.value);
const canCancel = (service) => (isOwner.value || isAssignedToMe(service)) && ['menunggu_alokasi', 'diterima', 'diagnosa', 'dikerjakan', 'indent'].includes(service?.status);

const confirmCancel = (service) => { cancelTarget.value = service; showCancelModal.value = true; };
const executeCancel = () => {
    if (!cancelTarget.value) return;
    processingAction.value = 'cancel';
    router.post(route('services.cancel', cancelTarget.value.id), {}, { onSuccess: () => { showCancelModal.value = false; cancelTarget.value = null; }, onFinish: () => { processingAction.value = null; } });
};

const executeAlokasi = () => {
    if (!alokasiTarget.value || !selectedTechnicianId.value) return;
    processingAction.value = 'alokasi';
    router.post(route('services.assign-technician', alokasiTarget.value.id), { technician_id: selectedTechnicianId.value }, { onSuccess: () => { showAlokasiModal.value = false; alokasiTarget.value = null; selectedTechnicianId.value = ''; }, onFinish: () => { processingAction.value = null; } });
};
</script>