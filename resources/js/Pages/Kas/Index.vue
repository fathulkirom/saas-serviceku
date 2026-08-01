<template>
  <AuthenticatedLayout>
    <div class="flex flex-col min-h-[calc(100vh-64px)] bg-zinc-50">
      <!-- Header CRM Style -->
      <div class="px-6 sm:px-8 py-6 bg-white border-b border-zinc-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4 sticky top-0 z-20">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center border border-emerald-100">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 tracking-tight">{{ pageTitle }}</h1>
                <p class="text-sm text-zinc-500 font-medium mt-0.5">{{ subtitle }}</p>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-2">
          <button v-if="activeTab === 'shift'" @click="openShiftModal()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow-md transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Buka / Tutup Shift Kas
          </button>

          <button v-if="activeTab === 'setoran'" @click="openDepositModal()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow-md transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Catat Setoran Harian
          </button>

          <button v-if="activeTab === 'rekonsiliasi'" @click="openReconciliationModal()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow-md transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Tambah Rekonsiliasi Bank
          </button>
        </div>
      </div>

      <div class="flex-1 p-6 sm:p-8 max-w-[1400px] mx-auto w-full">

    <TabPage :tabs="tabs" v-model="activeTab" @update:model-value="switchTab">
      <!-- SHIFT KASIR -->
      <template #shift>
        <div class="space-y-6 mt-6">
          <Skeleton v-if="!registers" type="table" :count="5" />
          <div v-else class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden">
            <KTable
              :columns="shiftColumns"
              :rows="registers?.data ?? []"
              :emptyTitle="'Belum ada data shift'"
              :emptyDescription="'Belum ada data shift kasir.'"
              :emptyActionLabel="'+ Buka Shift Kasir Baru'"
              @empty-action="openShiftModal()"
            >
              <template #cell-user_name="{ row }">
                <span class="font-bold text-zinc-900">{{ row.user?.name ?? '-' }}</span>
              </template>
              <template #cell-branch_name="{ row }">
                <span class="text-zinc-600">{{ row.branch?.name ?? '-' }}</span>
              </template>
              <template #cell-opening_balance="{ row }">
                <span class="font-semibold text-zinc-900">Rp {{ formatNumber(row.opening_balance) }}</span>
              </template>
              <template #cell-closing_balance="{ row }">
                <span class="font-bold text-zinc-900">Rp {{ formatNumber(row.closing_balance) }}</span>
              </template>
              <template #cell-status="{ row }">
                <Badge v-if="row.status === 'open'" variant="green" dot>Aktif (Open)</Badge>
                <Badge v-else variant="default">Tutup (Closed)</Badge>
              </template>
              <template #cell-opened_at="{ row }">
                <span class="text-zinc-500">{{ formatDate(row.opened_at) }}</span>
              </template>
              <template #cell-closed_at="{ row }">
                <span class="text-zinc-500">{{ row.closed_at ? formatDate(row.closed_at) : '-' }}</span>
              </template>
              <template #cell-action="{ row }">
                <button v-if="row.status === 'open'" @click="openCloseShiftModal(row)" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-50 text-red-600 hover:bg-red-100 transition-all shadow-sm border border-red-200">
                  Tutup Shift
                </button>
              </template>
            </KTable>
            <div class="p-4 border-t border-zinc-200 bg-zinc-50/50">
              <Pagination :meta="registers" />
            </div>
          </div>
        </div>
      </template>

      <!-- SETORAN HARIAN -->
      <template #setoran>
        <div class="space-y-6 mt-6">
          <Skeleton v-if="!deposits" type="table" :count="5" />
          <div v-else class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden">
            <KTable
              :columns="depositColumns"
              :rows="deposits?.data ?? []"
              :emptyTitle="'Belum ada data setoran'"
              :emptyDescription="'Data setoran harian ke rekening utama akan muncul di sini.'"
              :emptyActionLabel="'+ Catat Setoran Harian'"
              @empty-action="openDepositModal()"
            >
              <template #cell-amount="{ row }">
                <span class="font-black text-emerald-600">Rp {{ formatNumber(row.amount) }}</span>
              </template>
              <template #cell-deposit_date="{ row }">
                <span class="text-zinc-900 font-medium">{{ formatDate(row.deposit_date) }}</span>
              </template>
              <template #cell-note="{ row }">
                <span class="text-zinc-500">{{ row.note ?? '-' }}</span>
              </template>
            </KTable>
            <div class="p-4 border-t border-zinc-200 bg-zinc-50/50">
              <Pagination :meta="deposits" />
            </div>
          </div>
        </div>
      </template>

      <!-- KOMISI TEKNISI -->
      <template #komisi>
        <div class="space-y-6 mt-6">
          <Skeleton v-if="!commissions" type="table" :count="5" />
          <div v-else class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden">
            <KTable
              :columns="commissionColumns"
              :rows="commissions?.data ?? []"
              :emptyTitle="'Belum ada data komisi'"
              :emptyDescription="'Data komisi teknisi per servis akan muncul setelah servis selesai.'"
            >
              <template #cell-customer_name="{ row }">
                <span class="font-medium text-zinc-900">{{ row.service?.customer?.name ?? '-' }}</span>
              </template>
              <template #cell-technician_name="{ row }">
                <span class="font-bold text-zinc-900">{{ row.technician?.name ?? '-' }}</span>
              </template>
              <template #cell-commission_amount="{ row }">
                <span class="font-black text-indigo-600">Rp {{ formatNumber(row.commission_amount) }}</span>
              </template>
              <template #cell-status="{ row }">
                <Badge :status="row.status">{{ row.status === 'paid' ? 'Lunas / Dibayar' : 'Belum Dibayar' }}</Badge>
              </template>
              <template #cell-created_at="{ row }">
                <span class="text-zinc-500">{{ formatDate(row.created_at) }}</span>
              </template>
              <template #cell-action="{ row }">
                <button v-if="row.status !== 'paid'" @click="payCommission(row)" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-600 text-white hover:bg-emerald-700 transition-all shadow-sm">
                  Bayar Komisi
                </button>
              </template>
            </KTable>
            <div class="p-4 border-t border-zinc-200 bg-zinc-50/50">
              <Pagination :meta="commissions" />
            </div>
          </div>
        </div>
      </template>

      <!-- REKONSILIASI BANK -->
      <template #rekonsiliasi>
        <div class="space-y-6 mt-6">
          <Skeleton v-if="!reconciliations" type="table" :count="5" />
          <div v-else class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden">
            <KTable
              :columns="reconciliationColumns"
              :rows="reconciliations?.data ?? []"
              :emptyTitle="'Belum ada data rekonsiliasi'"
              :emptyDescription="'Data mutasi & rekonsiliasi bank akan muncul setelah ditambahkan.'"
              :emptyActionLabel="'+ Tambah Rekonsiliasi Bank'"
              @empty-action="openReconciliationModal()"
            >
              <template #cell-customer_name="{ row }">
                <span class="font-medium text-zinc-900">{{ row.sale?.customer?.name ?? '-' }}</span>
              </template>
              <template #cell-bank_name="{ row }">
                <span class="font-bold text-xs px-2.5 py-1 rounded-md bg-blue-50 text-blue-700 border border-blue-100">{{ row.bank_name ?? 'Bank' }}</span>
              </template>
              <template #cell-amount="{ row }">
                <span class="font-black text-zinc-900">Rp {{ formatNumber(row.amount) }}</span>
              </template>
              <template #cell-status="{ row }">
                <Badge :status="row.status">{{ row.status }}</Badge>
              </template>
              <template #cell-action="{ row }">
                <div v-if="row.status === 'pending'" class="flex items-center justify-end gap-2">
                  <button @click="updateReconcileStatus(row, 'verified')" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 transition-all shadow-sm">Verifikasi</button>
                  <button @click="updateReconcileStatus(row, 'rejected')" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 border border-red-200 transition-all shadow-sm">Tolak</button>
                </div>
              </template>
            </KTable>
            <div class="p-4 border-t border-zinc-200 bg-zinc-50/50">
              <Pagination :meta="reconciliations" />
            </div>
          </div>
        </div>
      </template>
    </TabPage>
    </div>

    <!-- DRAWER BUKA / TUTUP SHIFT KAS -->
    <Drawer :open="showShiftDrawer" :title="closingShift ? 'Tutup Shift Kasir' : 'Buka Shift Kasir Baru'" @close="showShiftDrawer = false" width="420px">
      <form @submit.prevent="submitShift" class="space-y-4">
        <div v-if="!closingShift" class="space-y-1">
          <label class="text-xs font-semibold text-zinc-500">Modal Kas Awal (Rp) *</label>
          <input v-model="shiftForm.opening_balance" type="number" min="0" required placeholder="e.g. 500000" class="input text-sm" />
        </div>
        <div v-else class="space-y-1">
          <label class="text-xs font-semibold text-zinc-500">Total Saldo Kas Akhir (Rp) *</label>
          <input v-model="shiftForm.closing_balance" type="number" min="0" required placeholder="Total uang fisik di laci kas" class="input text-sm" />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold text-zinc-500">Catatan Kasir</label>
          <textarea v-model="shiftForm.note" rows="2" placeholder="Catatan pecahan atau serah terima..." class="input text-sm"></textarea>
        </div>
        <div class="flex justify-end gap-2 pt-3">
          <button type="button" @click="showShiftDrawer = false" class="btn-secondary text-xs">Batal</button>
          <button type="submit" :disabled="shiftForm.processing" class="btn-primary text-xs">
            {{ shiftForm.processing ? 'Menyimpan...' : (closingShift ? 'Tutup Shift' : 'Buka Shift') }}
          </button>
        </div>
      </form>
    </Drawer>

    <!-- DRAWER SETORAN HARIAN -->
    <Drawer :open="showDepositDrawer" title="Catat Setoran Harian" @close="showDepositDrawer = false" width="420px">
      <form @submit.prevent="submitDeposit" class="space-y-4">
        <div class="space-y-1">
          <label class="text-xs font-semibold text-zinc-500">Nominal Setoran (Rp) *</label>
          <input v-model="depositForm.amount" type="number" min="1000" required placeholder="e.g. 2500000" class="input text-sm" />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold text-zinc-500">Tanggal Setoran *</label>
          <input v-model="depositForm.deposit_date" type="date" required class="input text-sm" />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold text-zinc-500">Catatan / Bukti Transfer</label>
          <textarea v-model="depositForm.note" rows="2" placeholder="Disetor via BCA Mandiri / Tunai oleh..." class="input text-sm"></textarea>
        </div>
        <div class="flex justify-end gap-2 pt-3">
          <button type="button" @click="showDepositDrawer = false" class="btn-secondary text-xs">Batal</button>
          <button type="submit" :disabled="depositForm.processing" class="btn-primary text-xs">
            {{ depositForm.processing ? 'Menyimpan...' : 'Simpan Setoran' }}
          </button>
        </div>
      </form>
    </Drawer>

    <!-- DRAWER REKONSILIASI BANK -->
    <Drawer :open="showReconcileDrawer" title="Tambah Rekonsiliasi Bank" @close="showReconcileDrawer = false" width="420px">
      <form @submit.prevent="submitReconciliation" class="space-y-4">
        <div class="space-y-1">
          <label class="text-xs font-semibold text-zinc-500">Nama Bank / QRIS *</label>
          <input v-model="reconcileForm.bank_name" required placeholder="e.g. Bank BCA / Mandiri / QRIS" class="input text-sm" />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold text-zinc-500">Jumlah Transaksi (Rp) *</label>
          <input v-model="reconcileForm.amount" type="number" min="1" required placeholder="0" class="input text-sm" />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold text-zinc-500">Nomor Referensi Mutasi</label>
          <input v-model="reconcileForm.reference_number" placeholder="No. Resi / Ref..." class="input text-sm" />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold text-zinc-500">Catatan</label>
          <textarea v-model="reconcileForm.notes" rows="2" class="input text-sm"></textarea>
        </div>
        <div class="flex justify-end gap-2 pt-3">
          <button type="button" @click="showReconcileDrawer = false" class="btn-secondary text-xs">Batal</button>
          <button type="submit" :disabled="reconcileForm.processing" class="btn-primary text-xs">
            {{ reconcileForm.processing ? 'Menyimpan...' : 'Simpan Rekonsiliasi' }}
          </button>
        </div>
      </form>
    </Drawer>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
import { router, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import TabPage from '@/Components/TabPage.vue';
import KCard from '@/Components/KCard.vue';
import KTable from '@/Components/KTable.vue';
import Pagination from '@/Components/Pagination.vue';
import Badge from '@/Components/Badge.vue';
import Skeleton from '@/Components/Skeleton.vue';
import Drawer from '@/Components/Drawer.vue';
import { useFormatter } from '@/Composables/useFormatter.js';

const { formatNumber, formatCurrency, formatDate, currentDate } = useFormatter();

const props = defineProps({
  activeTab: { type: String, default: 'shift' },
  registers: { type: Object, default: null },
  deposits: { type: Object, default: null },
  commissions: { type: Object, default: null },
  reconciliations: { type: Object, default: null },
});

const activeTab = ref(props.activeTab);

// Shift Drawer
const showShiftDrawer = ref(false);
const closingShift = ref(null);
const shiftForm = useForm({ opening_balance: 0, closing_balance: 0, note: '' });

const openShiftModal = () => {
  closingShift.value = null;
  shiftForm.reset();
  showShiftDrawer.value = true;
};

const openCloseShiftModal = (row) => {
  closingShift.value = row;
  shiftForm.reset();
  shiftForm.closing_balance = row.opening_balance || 0;
  showShiftDrawer.value = true;
};

const submitShift = () => {
  if (closingShift.value) {
    shiftForm.post(route('cash-registers.close'), { preserveScroll: true, onSuccess: () => { showShiftDrawer.value = false; } });
  } else {
    shiftForm.post(route('cash-registers.open'), { preserveScroll: true, onSuccess: () => { showShiftDrawer.value = false; } });
  }
};

// Deposit Drawer
const showDepositDrawer = ref(false);
const depositForm = useForm({ amount: 0, deposit_date: new Date().toISOString().slice(0, 10), note: '' });

const openDepositModal = () => {
  depositForm.reset();
  depositForm.deposit_date = new Date().toISOString().slice(0, 10);
  showDepositDrawer.value = true;
};

const submitDeposit = () => {
  depositForm.post(route('daily-deposits.store'), { preserveScroll: true, onSuccess: () => { showDepositDrawer.value = false; } });
};

// Commission Handler
const payCommission = (row) => {
  if (confirm(`Bayar komisi sebesar Rp ${formatNumber(row.commission_amount)} untuk ${row.technician?.name}?`)) {
    router.post(route('commissions.pay', row.id), {}, { preserveScroll: true });
  }
};

// Reconcile Drawer
const showReconcileDrawer = ref(false);
const reconcileForm = useForm({ bank_name: 'Bank BCA', amount: 0, reference_number: '', notes: '' });

const openReconciliationModal = () => {
  reconcileForm.reset();
  showReconcileDrawer.value = true;
};

const submitReconciliation = () => {
  reconcileForm.post(route('reconciliations.store'), { preserveScroll: true, onSuccess: () => { showReconcileDrawer.value = false; } });
};

const updateReconcileStatus = (row, status) => {
  router.post(route('reconciliations.status', row.id), { status }, { preserveScroll: true });
};

const tabs = [
  { key: 'shift', label: 'Shift' },
  { key: 'setoran', label: 'Setoran' },
  { key: 'komisi', label: 'Komisi' },
  { key: 'rekonsiliasi', label: 'Rekonsiliasi' },
];

const tabLabels = { shift: 'Shift', setoran: 'Setoran', komisi: 'Komisi', rekonsiliasi: 'Rekonsiliasi' };
const pageTitle = computed(() => 'Kas — ' + (tabLabels[activeTab.value] || 'Shift'));
const subtitle = computed(() => currentDate.value);

const shiftColumns = [
  { key: 'user_name', label: 'Kasir' },
  { key: 'branch_name', label: 'Cabang' },
  { key: 'opening_balance', label: 'Modal', align: 'right' },
  { key: 'closing_balance', label: 'Saldo Akhir', align: 'right' },
  { key: 'status', label: 'Status' },
  { key: 'opened_at', label: 'Buka' },
  { key: 'closed_at', label: 'Tutup' },
  { key: 'action', label: '', align: 'right' },
];

const depositColumns = [
  { key: 'amount', label: 'Jumlah', align: 'right' },
  { key: 'deposit_date', label: 'Tanggal' },
  { key: 'note', label: 'Catatan' },
];

const commissionColumns = [
  { key: 'customer_name', label: 'Pelanggan' },
  { key: 'technician_name', label: 'Teknisi' },
  { key: 'commission_amount', label: 'Jumlah Komisi', align: 'right' },
  { key: 'status', label: 'Status' },
  { key: 'created_at', label: 'Tanggal' },
  { key: 'action', label: '', align: 'right' },
];

const reconciliationColumns = [
  { key: 'customer_name', label: 'Pelanggan' },
  { key: 'bank_name', label: 'Bank' },
  { key: 'amount', label: 'Jumlah', align: 'right' },
  { key: 'status', label: 'Status' },
  { key: 'action', label: '', align: 'right' },
];

const switchTab = (key) => {
  router.get(route('kas.index'), { tab: key }, { preserveState: true, preserveScroll: true });
};
</script>

