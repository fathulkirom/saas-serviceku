<template>
  <AuthenticatedLayout>
    <template #header>
      <PageHeader :title="pageTitle" :subtitle="subtitle">
        <div v-if="activeTab === 'stok'" class="flex items-center gap-2">
          <button
            @click="openQuickStockModal()"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold border transition-all hover:bg-gray-50 cursor-pointer"
            style="borderColor: var(--border-color); color: var(--text-primary);"
          >
            ⚡ Penyesuaian Stok
          </button>
          <Link
            :href="route('products.create')"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold text-white transition-all hover:shadow-md cursor-pointer"
            style="background: var(--accent-primary);"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Produk Baru
          </Link>
        </div>

        <Link
          v-if="activeTab === 'transfer'"
          :href="route('stock-allocations.create')"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold text-white transition-all hover:shadow-md cursor-pointer"
          style="background: var(--accent-primary);"
        >
          + Transfer Stok
        </Link>

        <button
          v-if="activeTab === 'rusak'"
          @click="openDamagedModal()"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold text-white transition-all hover:shadow-md cursor-pointer"
          style="background: var(--accent-primary);"
        >
          + Catat Stok Rusak
        </button>

        <Link
          v-if="activeTab === 'reorder' || activeTab === 'forecast'"
          :href="route('purchases.create')"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold text-white transition-all hover:shadow-md cursor-pointer"
          style="background: var(--accent-primary);"
        >
          + Buat Pembelian Stok
        </Link>
      </PageHeader>
    </template>

    <TabPage :tabs="tabs" v-model="activeTab" @update:model-value="switchTab">
      <!-- STOK PRODUK -->
      <template #stok>
        <div class="space-y-6">
          <Skeleton v-if="!products" type="table" :count="5" />
          <KTable
            v-else
            :columns="stockColumns"
            :rows="products?.data ?? []"
            :emptyTitle="'Belum ada data produk'"
            :emptyDescription="'Data produk akan muncul setelah ditambahkan.'"
            :emptyActionLabel="'+ Tambah Produk Baru'"
            @empty-action="router.visit(route('products.create'))"
          >
            <template #cell-name="{ row }">
              <span class="font-medium text-sm">{{ row.name }}</span>
              <p class="text-[11px]" style="color: var(--text-muted);">SKU: {{ row.sku || '-' }} | Kategori: {{ row.category?.name || '-' }}</p>
            </template>
            <template #cell-stock_quantity="{ row }">
              <span class="font-bold" :style="{ color: row.stock_quantity <= row.min_stock ? 'var(--text-danger)' : 'var(--text-primary)' }">
                {{ formatNumber(row.stock_quantity ?? 0) }} {{ row.unit?.name || '' }}
              </span>
            </template>
            <template #cell-price="{ row }">
              Rp {{ formatNumber(row.price) }}
            </template>
            <template #cell-min_stock="{ row }">
              {{ formatNumber(row.min_stock ?? 0) }}
            </template>
            <template #cell-action="{ row }">
              <div class="flex items-center justify-end gap-1">
                <button @click="openQuickStockModal(row)" class="px-2.5 py-1 rounded text-xs font-medium border" style="borderColor: var(--border-color); color: var(--accent-primary);">Adjust</button>
              </div>
            </template>
          </KTable>

          <Pagination :meta="products" />
        </div>
      </template>

      <!-- TRANSFER STOK -->
      <template #transfer>
        <div class="space-y-6">
          <Skeleton v-if="!allocations" type="table" :count="5" />
          <KTable
            v-else
            :columns="transferColumns"
            :rows="allocations?.data ?? []"
            :emptyTitle="'Belum ada transfer stok'"
            :emptyDescription="'Data transfer stok antar cabang akan muncul setelah ditambahkan.'"
            :emptyActionLabel="'+ Transfer Stok Baru'"
            @empty-action="router.visit(route('stock-allocations.create'))"
          >
            <template #cell-product_name="{ row }">
              <span class="font-medium">{{ row.product?.name ?? '-' }}</span>
            </template>
            <template #cell-from_branch="{ row }">
              {{ row.from_branch?.name ?? '-' }}
            </template>
            <template #cell-to_branch="{ row }">
              {{ row.to_branch?.name ?? '-' }}
            </template>
            <template #cell-quantity="{ row }">
              <span class="font-bold text-xs">{{ formatNumber(row.quantity) }}</span>
            </template>
            <template #cell-status="{ row }">
              <Badge :status="row.status">{{ row.status }}</Badge>
            </template>
            <template #cell-action="{ row }">
              <div v-if="row.status === 'pending'" class="flex items-center justify-end gap-1">
                <button @click="confirmTransfer(row)" class="px-2.5 py-1 rounded text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700">Terima</button>
                <button @click="rejectTransfer(row)" class="px-2.5 py-1 rounded text-xs font-semibold text-red-600 border border-red-200 hover:bg-red-50">Tolak</button>
              </div>
            </template>
          </KTable>

          <Pagination :meta="allocations" />
        </div>
      </template>

      <!-- STOK RUSAK -->
      <template #rusak>
        <div class="space-y-6">
          <Skeleton v-if="!damagedStocks" type="table" :count="5" />
          <KTable
            v-else
            :columns="damagedColumns"
            :rows="damagedStocks?.data ?? []"
            :emptyTitle="'Belum ada stok rusak'"
            :emptyDescription="'Data stok rusak akan muncul setelah ditambahkan.'"
            :emptyActionLabel="'+ Catat Stok Rusak'"
            @empty-action="openDamagedModal()"
          >
            <template #cell-product_name="{ row }">
              <span class="font-medium">{{ row.product?.name ?? '-' }}</span>
            </template>
            <template #cell-type="{ row }">
              <Badge :variant="row.type === 'damaged' ? 'red' : 'orange'">{{ row.type === 'damaged' ? 'Rusak' : 'Hilang / Expired' }}</Badge>
            </template>
            <template #cell-quantity="{ row }">
              <span class="font-bold text-xs text-red-600">{{ formatNumber(row.quantity) }}</span>
            </template>
            <template #cell-created_at="{ row }">
              {{ formatDate(row.created_at) }}
            </template>
          </KTable>

          <Pagination :meta="damagedStocks" />
        </div>
      </template>

      <!-- RIWAYAT MUTASI STOK -->
      <template #mutasi>
        <div class="space-y-6">
          <Skeleton v-if="!mutations" type="table" :count="5" />
          <template v-else>
            <KCard title="Filter Mutasi">
              <div class="flex flex-wrap items-center gap-3">
                <select v-model="localMutationFilters.product_id" @change="applyMutationFilter" class="text-xs font-semibold rounded-lg border px-3 py-2 bg-white text-gray-700 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 focus:outline-none" style="border-color: var(--border-color);">
                  <option value="">Semua Produk</option>
                  <option v-for="product in mutationProducts" :key="product.id" :value="product.id">{{ product.name }}</option>
                </select>
                <select v-model="localMutationFilters.mutation_type" @change="applyMutationFilter" class="text-xs font-semibold rounded-lg border px-3 py-2 bg-white text-gray-700 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 focus:outline-none" style="border-color: var(--border-color);">
                  <option value="">Semua Tipe</option>
                  <option value="masuk">Masuk</option>
                  <option value="keluar">Keluar</option>
                  <option value="transfer">Transfer</option>
                  <option value="rusak">Rusak</option>
                </select>
                <button @click="resetMutationFilter" class="px-3 py-2 rounded-lg text-xs font-semibold border border-gray-200 text-gray-500 hover:bg-gray-100 transition-all bg-white">
                  ↺ Reset
                </button>
              </div>
            </KCard>

            <KTable
              :columns="mutationColumns"
              :rows="mutations?.data ?? []"
              :emptyTitle="'Belum ada riwayat mutasi'"
              :emptyDescription="'Riwayat mutasi stok akan muncul setelah ada perubahan.'"
            >
              <template #cell-product_name="{ row }">
                <span class="font-medium">{{ row.product?.name ?? '-' }}</span>
              </template>
              <template #cell-type="{ row }">
                <Badge :variant="row.type === 'masuk' || row.type === 'in' ? 'green' : row.type === 'keluar' || row.type === 'out' ? 'red' : 'blue'">{{ row.type }}</Badge>
              </template>
              <template #cell-quantity="{ row }">
                <span class="font-bold text-xs">{{ formatNumber(row.quantity) }}</span>
              </template>
              <template #cell-created_at="{ row }">
                {{ formatDate(row.created_at) }}
              </template>
            </KTable>

            <Pagination :meta="mutations" />
          </template>
        </div>
      </template>

      <!-- REORDER ALERTS -->
      <template #reorder>
        <div class="space-y-6">
          <Skeleton v-if="!reorderAlerts" type="table" :count="5" />
          <KTable
            v-else
            :columns="reorderColumns"
            :rows="reorderAlerts?.data ?? reorderAlerts ?? []"
            :emptyTitle="'Tidak ada peringatan reorder'"
            :emptyDescription="'Semua stok produk mencukupi.'"
          >
            <template #cell-name="{ row }">
              <span class="font-medium">{{ row.name ?? row.product?.name }}</span>
            </template>
            <template #cell-stock_quantity="{ row }">
              <span class="font-bold text-red-600">{{ formatNumber(row.stock_quantity ?? 0) }}</span>
            </template>
            <template #cell-min_stock="{ row }">
              {{ formatNumber(row.min_stock ?? 0) }}
            </template>
            <template #cell-status="{ row }">
              <Badge :status="row.stock_quantity <= row.min_stock ? 'habis' : 'menipis'">
                {{ row.stock_quantity <= row.min_stock ? 'Perlu Restock' : 'Stok Menipis' }}
              </Badge>
            </template>
            <template #cell-action="{ row }">
              <Link :href="route('purchases.create')" class="px-2.5 py-1 rounded text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700">
                Order Pembelian
              </Link>
            </template>
          </KTable>
        </div>
      </template>

      <!-- STOK FORECASTING -->
      <template #forecast>
        <div class="space-y-6">
          <Skeleton v-if="!forecast" type="table" :count="5" />
          <KTable
            v-else
            :columns="forecastColumns"
            :rows="forecast?.data ?? forecast ?? []"
            :emptyTitle="'Belum ada data forecast'"
            :emptyDescription="'Data forecast stok akan muncul setelah ada data pemakaian.'"
          >
            <template #cell-name="{ row }">
              <span class="font-medium">{{ row.name ?? row.product?.name }}</span>
            </template>
            <template #cell-stock="{ row }">
              {{ formatNumber(row.stock ?? row.stock_quantity ?? 0) }}
            </template>
            <template #cell-monthly_usage="{ row }">
              {{ formatNumber(row.monthly_usage ?? 0) }} / bulan
            </template>
            <template #cell-days_until_empty="{ row }">
              <span :style="{ color: (row.days_until_empty ?? 999) < 14 ? 'var(--text-danger)' : 'var(--text-secondary)' }" class="font-bold">
                {{ formatNumber(row.days_until_empty ?? 0) }} hari
              </span>
            </template>
            <template #cell-needs_restock="{ row }">
              <Badge :variant="row.needs_restock ? 'red' : 'green'">{{ row.needs_restock ? 'Perlu Restock' : 'Stok Aman' }}</Badge>
            </template>
          </KTable>
        </div>
      </template>
    </TabPage>

    <!-- DRAWER PENYESUAIAN STOK CEPAT -->
    <Drawer :open="showQuickStockDrawer" title="Penyesuaian Stok Cepat" @close="showQuickStockDrawer = false" width="420px">
      <form @submit.prevent="submitQuickStock" class="space-y-4">
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Pilih Produk *</label>
          <select v-model="quickStockForm.product_id" required class="input text-sm">
            <option value="" disabled>-- Pilih Produk --</option>
            <option v-for="p in (products?.data ?? [])" :key="p.id" :value="p.id">{{ p.name }} (Stok Saat Ini: {{ p.stock_quantity }})</option>
          </select>
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Tipe Penyesuaian *</label>
          <select v-model="quickStockForm.type" required class="input text-sm">
            <option value="in">Stok Masuk (+)</option>
            <option value="out">Stok Keluar (-)</option>
            <option value="set">Set Total Stok</option>
          </select>
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Jumlah Unit *</label>
          <input v-model="quickStockForm.quantity" type="number" min="1" required class="input text-sm" />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Alasan / Catatan</label>
          <textarea v-model="quickStockForm.notes" rows="2" placeholder="e.g. Hasil Stock Opname / Penyesuaian..." class="input text-sm"></textarea>
        </div>
        <div class="flex justify-end gap-2 pt-3">
          <button type="button" @click="showQuickStockDrawer = false" class="btn-secondary text-xs">Batal</button>
          <button type="submit" :disabled="quickStockForm.processing" class="btn-primary text-xs">
            {{ quickStockForm.processing ? 'Menyimpan...' : 'Simpan Stok' }}
          </button>
        </div>
      </form>
    </Drawer>

    <!-- DRAWER CATAT STOK RUSAK -->
    <Drawer :open="showDamagedDrawer" title="Catat Stok Rusak / Expired" @close="showDamagedDrawer = false" width="420px">
      <form @submit.prevent="submitDamagedStock" class="space-y-4">
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Pilih Produk *</label>
          <select v-model="damagedForm.product_id" required class="input text-sm">
            <option value="" disabled>-- Pilih Produk --</option>
            <option v-for="p in mutationProducts" :key="p.id" :value="p.id">{{ p.name }}</option>
          </select>
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Kategori Kerusakan *</label>
          <select v-model="damagedForm.type" required class="input text-sm">
            <option value="damaged">Rusak / Fisik</option>
            <option value="lost">Hilang / Cacat</option>
            <option value="expired">Expired / Cacat Pabrik</option>
          </select>
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Jumlah Unit Rusak *</label>
          <input v-model="damagedForm.quantity" type="number" min="1" required class="input text-sm" />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Keterangan Kerusakan</label>
          <textarea v-model="damagedForm.notes" rows="2" placeholder="Detail kerusakan fisik..." class="input text-sm"></textarea>
        </div>
        <div class="flex justify-end gap-2 pt-3">
          <button type="button" @click="showDamagedDrawer = false" class="btn-secondary text-xs">Batal</button>
          <button type="submit" :disabled="damagedForm.processing" class="btn-primary text-xs">
            {{ damagedForm.processing ? 'Menyimpan...' : 'Simpan' }}
          </button>
        </div>
      </form>
    </Drawer>
  </AuthenticatedLayout>
</template>

<script setup>
import { computed, reactive, ref } from 'vue';
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
  activeTab: { type: String, default: 'stok' },
  products: { type: Object, default: null },
  allocations: { type: Object, default: null },
  damagedStocks: { type: Object, default: null },
  mutations: { type: Object, default: null },
  mutationProducts: { type: Array, default: () => [] },
  mutationFilters: { type: Object, default: () => ({ product_id: '', mutation_type: '' }) },
  reorderAlerts: { type: [Object, Array], default: null },
  forecast: { type: [Object, Array], default: null },
});

const activeTab = ref(props.activeTab);

const localMutationFilters = reactive({
  product_id: props.mutationFilters?.product_id || '',
  mutation_type: props.mutationFilters?.mutation_type || props.mutationFilters?.type || '',
});

// Drawers
const showQuickStockDrawer = ref(false);
const quickStockForm = useForm({ product_id: '', type: 'in', quantity: 1, notes: '' });

const showDamagedDrawer = ref(false);
const damagedForm = useForm({ product_id: '', type: 'damaged', quantity: 1, notes: '' });

const openQuickStockModal = (row = null) => {
  quickStockForm.reset();
  if (row) {
    quickStockForm.product_id = row.id;
  }
  showQuickStockDrawer.value = true;
};

const submitQuickStock = () => {
  if (!quickStockForm.product_id) return;
  quickStockForm.post(route('products.quick-stock', quickStockForm.product_id), {
    preserveScroll: true,
    onSuccess: () => { showQuickStockDrawer.value = false; }
  });
};

const openDamagedModal = () => {
  damagedForm.reset();
  showDamagedDrawer.value = true;
};

const submitDamagedStock = () => {
  damagedForm.post(route('inventory.damaged.store'), {
    preserveScroll: true,
    onSuccess: () => { showDamagedDrawer.value = false; }
  });
};

const confirmTransfer = (row) => {
  if (confirm('Konfirmasi penerimaan transfer stok ini?')) {
    router.post(route('stock-allocations.confirm', row.id), {}, { preserveScroll: true });
  }
};

const rejectTransfer = (row) => {
  if (confirm('Tolak transfer stok ini?')) {
    router.post(route('stock-allocations.reject', row.id), {}, { preserveScroll: true });
  }
};

const tabs = [
  { key: 'stok', label: 'Stok' },
  { key: 'transfer', label: 'Transfer' },
  { key: 'rusak', label: 'Rusak' },
  { key: 'mutasi', label: 'Riwayat Mutasi' },
  { key: 'reorder', label: 'Reorder' },
  { key: 'forecast', label: 'Forecast' },
];

const tabLabels = { stok: 'Stok', transfer: 'Transfer', rusak: 'Rusak', mutasi: 'Riwayat Mutasi', reorder: 'Reorder', forecast: 'Forecast' };
const pageTitle = computed(() => 'Inventaris — ' + (tabLabels[activeTab.value] || 'Stok'));
const subtitle = computed(() => currentDate.value);

const stockColumns = [
  { key: 'name', label: 'Nama Produk' },
  { key: 'stock_quantity', label: 'Stok', align: 'right' },
  { key: 'price', label: 'Harga Jual', align: 'right' },
  { key: 'min_stock', label: 'Min' },
  { key: 'action', label: '', align: 'right' },
];

const transferColumns = [
  { key: 'product_name', label: 'Produk' },
  { key: 'from_branch', label: 'Dari' },
  { key: 'to_branch', label: 'Ke' },
  { key: 'quantity', label: 'Jumlah' },
  { key: 'status', label: 'Status' },
  { key: 'action', label: '', align: 'right' },
];

const damagedColumns = [
  { key: 'product_name', label: 'Produk' },
  { key: 'type', label: 'Tipe' },
  { key: 'quantity', label: 'Jumlah' },
  { key: 'created_at', label: 'Tanggal' },
];

const mutationColumns = [
  { key: 'product_name', label: 'Produk' },
  { key: 'type', label: 'Tipe' },
  { key: 'quantity', label: 'Jumlah' },
  { key: 'created_at', label: 'Tanggal' },
];

const reorderColumns = [
  { key: 'name', label: 'Produk' },
  { key: 'stock_quantity', label: 'Stok' },
  { key: 'min_stock', label: 'Min' },
  { key: 'status', label: 'Status' },
  { key: 'action', label: '', align: 'right' },
];

const forecastColumns = [
  { key: 'name', label: 'Produk' },
  { key: 'stock', label: 'Stok' },
  { key: 'monthly_usage', label: 'Pemakaian/bulan' },
  { key: 'days_until_empty', label: 'Habis dlm hari' },
  { key: 'needs_restock', label: 'Keterangan' },
];

const switchTab = (key) => {
  router.get(route('inventaris.index'), { tab: key }, { preserveState: true, preserveScroll: true });
};

const applyMutationFilter = () => {
  router.get(route('inventaris.index'), {
    tab: activeTab.value,
    ...localMutationFilters,
  }, { preserveState: true, preserveScroll: true });
};

const resetMutationFilter = () => {
  localMutationFilters.product_id = '';
  localMutationFilters.mutation_type = '';
  router.get(route('inventaris.index'), { tab: activeTab.value }, { preserveState: true, preserveScroll: true });
};
</script>

