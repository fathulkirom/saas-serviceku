<template>
  <AuthenticatedLayout>
    <template #header>
      <PageHeader :title="pageTitle" :subtitle="subtitle">
        <button
          v-if="activeTab === 'shift'"
          @click="openShiftModal()"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold text-white transition-all hover:shadow-md cursor-pointer"
          style="background: var(--accent-primary);"
        >
          + Buka / Tutup Shift Kas
        </button>
        <button
          v-if="activeTab === 'setoran'"
          @click="openDepositModal()"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold text-white transition-all hover:shadow-md cursor-pointer"
          style="background: var(--accent-primary);"
        >
          + Catat Setoran Harian
        </button>
        <button
          v-if="activeTab === 'rekonsiliasi'"
          @click="openReconciliationModal()"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold text-white transition-all hover:shadow-md cursor-pointer"
          style="background: var(--accent-primary);"
        >
          + Tambah Rekonsiliasi Bank
        </button>
      </PageHeader>
    </template>

    <TabPage :tabs="tabs" v-model="activeTab" @update:model-value="switchTab">
      <!-- SHIFT KASIR -->
      <template #shift>
        <div class="space-y-6">
          <Skeleton v-if="!registers" type="table" :count="5" />
          <KTable
            v-else
            :columns="shiftColumns"
            :rows="registers?.data ?? []"
            :emptyTitle="'Belum ada data shift'"
            :emptyDescription="'Belum ada data shift kasir.'"
            :emptyActionLabel="'+ Buka Shift Kasir Baru'"
            @empty-action="openShiftModal()"
          >
            <template #cell-user_name="{ row }">
              <span class="font-medium">{{ row.user?.name ?? '-' }}</span>
            </template>
            <template #cell-branch_name="{ row }">
              {{ row.branch?.name ?? '-' }}
            </template>
            <template #cell-opening_balance="{ row }">
              Rp {{ formatNumber(row.opening_balance) }}
            </template>
            <template #cell-closing_balance="{ row }">
              Rp {{ formatNumber(row.closing_balance) }}
            </template>
            <template #cell-status="{ row }">
              <Badge v-if="row.status === 'open'" variant="green" dot>Aktif (Open)</Badge>
              <Badge v-else variant="default">Tutup (Closed)</Badge>
            </template>
            <template #cell-opened_at="{ row }">
              {{ formatDate(row.opened_at) }}
            </template>
            <template #cell-closed_at="{ row }">
              {{ row.closed_at ? formatDate(row.closed_at) : '-' }}
            </template>
            <template #cell-action="{ row }">
              <button v-if="row.status === 'open'" @click="openCloseShiftModal(row)" class="px-2.5 py-1 rounded text-xs font-bold text-white bg-red-600 hover:bg-red-700">
                Tutup Shift
              </button>
            </template>
          </KTable>

          <Pagination :meta="registers" />
        </div>
      </template>

      <!-- SETORAN HARIAN -->
      <template #setoran>
        <div class="space-y-6">
          <Skeleton v-if="!deposits" type="table" :count="5" />
          <KTable
            v-else
            :columns="depositColumns"
            :rows="deposits?.data ?? []"
            :emptyTitle="'Belum ada data setoran'"
            :emptyDescription="'Data setoran harian ke rekening utama akan muncul di sini.'"
            :emptyActionLabel="'+ Catat Setoran Harian'"
            @empty-action="openDepositModal()"
          >
            <template #cell-amount="{ row }">
              <span class="font-bold text-emerald-600">Rp {{ formatNumber(row.amount) }}</span>
            </template>
            <template #cell-deposit_date="{ row }">
              {{ formatDate(row.deposit_date) }}
            </template>
            <template #cell-note="{ row }">
              {{ row.note ?? '-' }}
            </template>
          </KTable>

          <Pagination :meta="deposits" />
        </div>
      </template>

      <!-- KOMISI TEKNISI -->
      <template #komisi>
        <div class="space-y-6">
          <Skeleton v-if="!commissions" type="table" :count="5" />
          <KTable
            v-else
            :columns="commissionColumns"
            :rows="commissions?.data ?? []"
            :emptyTitle="'Belum ada data komisi'"
            :emptyDescription="'Data komisi teknisi per servis akan muncul setelah servis selesai.'"
          >
            <template #cell-customer_name="{ row }">
              {{ row.service?.customer?.name ?? '-' }}
            </template>
            <template #cell-technician_name="{ row }">
              <span class="font-medium">{{ row.technician?.name ?? '-' }}</span>
            </template>
            <template #cell-commission_amount="{ row }">
              <span class="font-bold" style="color: var(--accent-primary);">Rp {{ formatNumber(row.commission_amount) }}</span>
            </template>
            <template #cell-status="{ row }">
              <Badge :status="row.status">{{ row.status === 'paid' ? 'Lunas / Dibayar' : 'Belum Dibayar' }}</Badge>
            </template>
            <template #cell-created_at="{ row }">
              {{ formatDate(row.created_at) }}
            </template>
            <template #cell-action="{ row }">
              <button v-if="row.status !== 'paid'" @click="payCommission(row)" class="px-2.5 py-1 rounded text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700">
                Bayar Komisi
              </button>
            </template>
          </KTable>

          <Pagination :meta="commissions" />
        </div>
      </template>

      <!-- REKONSILIASI BANK -->
      <template #rekonsiliasi>
        <div class="space-y-6">
          <Skeleton v-if="!reconciliations" type="table" :count="5" />
          <KTable
            v-else
            :columns="reconciliationColumns"
            :rows="reconciliations?.data ?? []"
            :emptyTitle="'Belum ada data rekonsiliasi'"
            :emptyDescription="'Data mutasi & rekonsiliasi bank akan muncul setelah ditambahkan.'"
            :emptyActionLabel="'+ Tambah Rekonsiliasi Bank'"
            @empty-action="openReconciliationModal()"
          >
            <template #cell-customer_name="{ row }">
              {{ row.sale?.customer?.name ?? '-' }}
            </template>
            <template #cell-bank_name="{ row }">
              <span class="font-bold text-xs px-2 py-0.5 rounded" style="background: var(--bg-hover);">{{ row.bank_name ?? 'Bank' }}</span>
            </template>
            <template #cell-amount="{ row }">
              <span class="font-bold">Rp {{ formatNumber(row.amount) }}</span>
            </template>
            <template #cell-status="{ row }">
              <Badge :status="row.status">{{ row.status }}</Badge>
            </template>
            <template #cell-action="{ row }">
              <div v-if="row.status === 'pending'" class="flex items-center justify-end gap-1">
                <button @click="updateReconcileStatus(row, 'verified')" class="px-2.5 py-1 rounded text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700">Verifikasi</button>
                <button @click="updateReconcileStatus(row, 'rejected')" class="px-2.5 py-1 rounded text-xs font-semibold text-red-600 border border-red-200 hover:bg-red-50">Tolak</button>
              </div>
            </template>
          </KTable>

          <Pagination :meta="reconciliations" />
        </div>
      </template>
    </TabPage>

    <!-- DRAWER BUKA / TUTUP SHIFT KAS -->
    <Drawer :open="showShiftDrawer" :title="closingShift ? 'Tutup Shift Kasir' : 'Buka Shift Kasir Baru'" @close="showShiftDrawer = false" width="420px">
      <form @submit.prevent="submitShift" class="space-y-4">
        <div v-if="!closingShift" class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Modal Kas Awal (Rp) *</label>
          <input v-model="shiftForm.opening_balance" type="number" min="0" required placeholder="e.g. 500000" class="input text-sm" />
        </div>
        <div v-else class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Total Saldo Kas Akhir (Rp) *</label>
          <input v-model="shiftForm.closing_balance" type="number" min="0" required placeholder="Total uang fisik di laci kas" class="input text-sm" />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Catatan Kasir</label>
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
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Nominal Setoran (Rp) *</label>
          <input v-model="depositForm.amount" type="number" min="1000" required placeholder="e.g. 2500000" class="input text-sm" />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Tanggal Setoran *</label>
          <input v-model="depositForm.deposit_date" type="date" required class="input text-sm" />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Catatan / Bukti Transfer</label>
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
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Nama Bank / QRIS *</label>
          <input v-model="reconcileForm.bank_name" required placeholder="e.g. Bank BCA / Mandiri / QRIS" class="input text-sm" />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Jumlah Transaksi (Rp) *</label>
          <input v-model="reconcileForm.amount" type="number" min="1" required placeholder="0" class="input text-sm" />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Nomor Referensi Mutasi</label>
          <input v-model="reconcileForm.reference_number" placeholder="No. Resi / Ref..." class="input text-sm" />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Catatan</label>
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

