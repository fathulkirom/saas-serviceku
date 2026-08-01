<template>
  <AuthenticatedLayout>
    <template #header>
      <PageHeader :title="pageTitle" :subtitle="subtitle">
        <Link
          v-if="activeTab === 'penjualan'"
          :href="route('sales.create')"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold text-white transition-all hover:shadow-md cursor-pointer"
          style="background: var(--accent-primary);"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
          Transaksi Penjualan Baru
        </Link>
        <button
          v-if="activeTab === 'pengeluaran'"
          @click="openExpenseModal()"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold text-white transition-all hover:shadow-md cursor-pointer"
          style="background: var(--accent-primary);"
        >
          + Catat Pengeluaran Baru
        </button>
        <button
          v-if="activeTab === 'pembelian'"
          @click="openPurchaseDrawer()"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold text-white transition-all hover:shadow-md cursor-pointer"
          style="background: var(--accent-primary);"
        >
          + Pembelian Baru
        </button>
        <button
          v-if="activeTab === 'retur'"
          @click="openReturnDrawer()"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold text-white transition-all hover:shadow-md cursor-pointer"
          style="background: var(--accent-primary);"
        >
          + Retur Pembelian Baru
        </button>
      </PageHeader>
    </template>

    <TabPage :tabs="tabs" v-model="activeTab" @update:model-value="switchTab">
      <!-- PENJUALAN -->
      <template #penjualan>
        <div class="space-y-6">
          <Skeleton v-if="!sales" type="stat" :count="3" />
          <div v-else class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <StatCard label="Servis Selesai" :value="salesStats?.completed ?? 0" color="green" icon="✓" />
            <StatCard label="Draft" :value="salesStats?.draft ?? 0" color="yellow" icon="📝" />
            <StatCard label="Lunas" :value="salesStats?.paid ?? 0" color="blue" icon="💰" />
          </div>

          <KCard title="Filter Penjualan">
            <div class="flex flex-wrap items-center gap-3">
              <select v-model="salesFilters.status" @change="applySalesFilter" class="text-xs font-semibold rounded-lg border px-3 py-2 bg-white text-gray-700 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 focus:outline-none" style="border-color: var(--border-color);">
                <option value="">Semua Status</option>
                <option value="lunas">Lunas</option>
                <option value="draft">Draft</option>
                <option value="dp">DP</option>
                <option value="unpaid">Belum Bayar</option>
              </select>
              <select v-model="salesFilters.sale_type" @change="applySalesFilter" class="text-xs font-semibold rounded-lg border px-3 py-2 bg-white text-gray-700 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 focus:outline-none" style="border-color: var(--border-color);">
                <option value="">Semua Tipe</option>
                <option value="service">Servis</option>
                <option value="product">Produk</option>
              </select>
              <div class="relative flex-1 min-w-[180px]">
                <input
                  type="text"
                  v-model="salesFilters.search"
                  @keyup.enter="applySalesFilter"
                  placeholder="Cari pelanggan..."
                  class="w-full rounded-lg border text-xs px-3 py-2 pl-8 focus:ring-2 focus:outline-none bg-white text-gray-700 focus:border-blue-500 focus:ring-blue-200 transition-all"
                  style="border-color: var(--border-color);"
                />
                <svg class="absolute left-2.5 top-2.5 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
              </div>
              <button @click="resetSalesFilter" class="px-3 py-2 rounded-lg text-xs font-semibold border border-gray-200 text-gray-500 hover:bg-gray-100 transition-all bg-white">
                ↺ Reset
              </button>
            </div>
          </KCard>

          <KTable
            :columns="salesColumns"
            :rows="sales?.data ?? []"
            :emptyTitle="'Belum ada data penjualan'"
            :emptyDescription="'Data penjualan akan muncul setelah ada transaksi.'"
            :emptyActionLabel="'+ Transaksi Penjualan Baru'"
            @empty-action="router.visit(route('sales.create'))"
          >
            <template #cell-id="{ row }">
              <span class="font-mono text-xs font-bold" style="color: var(--accent-primary);">#{{ row.id }}</span>
            </template>
            <template #cell-customer="{ row }">
              <span class="font-medium">{{ row.customer?.name ?? '-' }}</span>
            </template>
            <template #cell-total="{ row }">
              <span class="font-bold">Rp {{ formatNumber(row.total) }}</span>
            </template>
            <template #cell-status="{ row }">
              <Badge :status="row.status">{{ row.status }}</Badge>
            </template>
            <template #cell-created_at="{ row }">
              {{ formatDate(row.created_at) }}
            </template>
            <template #cell-action="{ row }">
              <div class="flex items-center justify-end gap-1">
                <Link :href="route('sales.show', row.id)" class="px-2 py-1 rounded text-xs font-semibold text-blue-600 border border-blue-200 hover:bg-blue-50">Detail</Link>
                <a :href="route('sales.print', row.id)" target="_blank" class="px-2 py-1 rounded text-xs font-semibold text-gray-700 border border-gray-200 hover:bg-gray-50">Cetak Nota</a>
                <button v-if="row.status === 'draft'" @click="payDraft(row)" class="px-2 py-1 rounded text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700">Bayar Draft</button>
              </div>
            </template>
          </KTable>

          <Pagination :meta="sales" />
        </div>
      </template>

      <!-- PENGELUARAN -->
      <template #pengeluaran>
        <div class="space-y-6">
          <Skeleton v-if="!expenses" type="table" :count="5" />
          <template v-else>
            <KCard title="Filter Pengeluaran">
              <div class="flex flex-wrap items-center gap-3">
                <select v-model="expenseFilters.category_id" @change="applyExpenseFilter" class="text-xs font-semibold rounded-lg border px-3 py-2 bg-white text-gray-700 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 focus:outline-none" style="border-color: var(--border-color);">
                  <option value="">Semua Kategori</option>
                  <option v-for="cat in expenseCategories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>
                <div class="relative flex-1 min-w-[180px]">
                  <input
                    type="text"
                    v-model="expenseFilters.search"
                    @keyup.enter="applyExpenseFilter"
                    placeholder="Cari deskripsi..."
                    class="w-full rounded-lg border text-xs px-3 py-2 pl-8 focus:ring-2 focus:outline-none bg-white text-gray-700 focus:border-blue-500 focus:ring-blue-200 transition-all"
                    style="border-color: var(--border-color);"
                  />
                  <svg class="absolute left-2.5 top-2.5 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <button @click="resetExpenseFilter" class="px-3 py-2 rounded-lg text-xs font-semibold border border-gray-200 text-gray-500 hover:bg-gray-100 transition-all bg-white">
                  ↺ Reset
                </button>
              </div>
            </KCard>

            <KTable
              :columns="expenseColumns"
              :rows="expenses?.data ?? []"
              :emptyTitle="'Belum ada data pengeluaran'"
              :emptyDescription="'Data pengeluaran operasional toko akan muncul setelah ditambahkan.'"
              :emptyActionLabel="'+ Catat Pengeluaran Baru'"
              @empty-action="openExpenseModal()"
            >
              <template #cell-description="{ row }">
                <span class="font-medium">{{ row.description }}</span>
              </template>
              <template #cell-amount="{ row }">
                <span class="font-bold text-red-600">Rp {{ formatNumber(row.amount) }}</span>
              </template>
              <template #cell-category="{ row }">
                <Badge variant="purple">{{ row.category?.name ?? '-' }}</Badge>
              </template>
              <template #cell-expense_date="{ row }">
                {{ formatDate(row.expense_date) }}
              </template>
            </KTable>

            <Pagination :meta="expenses" />
          </template>
        </div>
      </template>

      <!-- PEMBELIAN STOK -->
      <template #pembelian>
        <div class="space-y-6">
          <Skeleton v-if="!purchases" type="table" :count="5" />
          <KTable
            v-else
            :columns="purchaseColumns"
            :rows="purchases?.data ?? []"
            :emptyTitle="'Belum ada data pembelian'"
            :emptyDescription="'Data pembelian dari supplier akan muncul di sini.'"
            :emptyActionLabel="'+ Buat Pembelian Stok Baru'"
            @empty-action="openPurchaseDrawer()"
          >
            <template #cell-reference_number="{ row }">
              <span class="font-mono text-xs font-bold" style="color: var(--accent-primary);">{{ row.reference_number ?? '-' }}</span>
            </template>
            <template #cell-supplier_name="{ row }">
              {{ row.supplier?.name ?? '-' }}
            </template>
            <template #cell-total="{ row }">
              <span class="font-bold">Rp {{ formatNumber(row.total) }}</span>
            </template>
            <template #cell-type="{ row }">
              <Badge :variant="row.type === 'product' ? 'blue' : 'purple'">{{ row.type === 'product' ? 'Stok Produk' : 'Alat Servis' }}</Badge>
            </template>
            <template #cell-created_at="{ row }">
              {{ formatDate(row.created_at) }}
            </template>
            <template #cell-action="{ row }">
              <button @click="openPurchaseDetail(row)" class="px-2.5 py-1 rounded text-xs font-semibold text-blue-600 border border-blue-200 hover:bg-blue-50">Detail</button>
            </template>
          </KTable>

          <Pagination :meta="purchases" />
        </div>
      </template>

      <!-- RETUR PEMBELIAN -->
      <template #retur>
        <div class="space-y-6">
          <Skeleton v-if="!returns" type="table" :count="5" />
          <KTable
            v-else
            :columns="returnColumns"
            :rows="returns?.data ?? []"
            :emptyTitle="'Belum ada data retur pembelian'"
            :emptyDescription="'Data pengembalian barang ke supplier akan muncul di sini.'"
            :emptyActionLabel="'+ Retur Pembelian Baru'"
            @empty-action="openReturnDrawer()"
          >
            <template #cell-reason="{ row }">
              <span class="font-medium">{{ row.reason ?? '-' }}</span>
            </template>
            <template #cell-status="{ row }">
              <Badge :status="row.status">{{ row.status }}</Badge>
            </template>
            <template #cell-created_at="{ row }">
              {{ formatDate(row.created_at) }}
            </template>
            <template #cell-action="{ row }">
              <select @change="updateReturnStatus(row, $event.target.value)" class="text-xs py-1 px-2 rounded border font-semibold bg-white" style="borderColor: var(--border-color);">
                <option disabled selected>Ubah Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Disetujui Supplier</option>
                <option value="completed">Selesai</option>
                <option value="rejected">Ditolak</option>
              </select>
            </template>
          </KTable>

          <Pagination :meta="returns" />
        </div>
      </template>
    </TabPage>

    <!-- DRAWER TAMBAH PENGELUARAN -->
    <Drawer :open="showExpenseDrawer" title="Catat Pengeluaran Operasional Baru" @close="showExpenseDrawer = false" width="450px">
      <form @submit.prevent="submitExpense" class="space-y-4">
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Deskripsi Pengeluaran *</label>
          <input v-model="expenseForm.description" required placeholder="e.g. Bayar Listrik / Beli Kertas Thermal" class="input text-sm" />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Jumlah Nominal (Rp) *</label>
          <input v-model="expenseForm.amount" type="number" min="100" required placeholder="0" class="input text-sm" />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Kategori *</label>
          <select v-model="expenseForm.category_id" required class="input text-sm">
            <option value="" disabled>-- Pilih Kategori --</option>
            <option v-for="cat in expenseCategories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
          </select>
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Tanggal Pengeluaran *</label>
          <input v-model="expenseForm.expense_date" type="date" required class="input text-sm" />
        </div>
        <div class="flex justify-end gap-2 pt-3">
          <button type="button" @click="showExpenseDrawer = false" class="btn-secondary text-xs">Batal</button>
          <button type="submit" :disabled="expenseForm.processing" class="btn-primary text-xs">
            {{ expenseForm.processing ? 'Menyimpan...' : 'Simpan Pengeluaran' }}
          </button>
        </div>
      </form>
    </Drawer>

    <!-- DRAWER PEMBELIAN BARU -->
    <Drawer :open="showPurchaseDrawer" title="Pembelian Baru" @close="showPurchaseDrawer = false" width="520px">
      <form @submit.prevent="submitPurchase" class="space-y-4">
        <div class="grid grid-cols-2 gap-3">
          <div class="space-y-1">
            <label class="text-xs font-semibold" style="color: var(--text-muted);">Tipe *</label>
            <select v-model="purchaseForm.type" required class="input text-sm">
              <option value="po">PO (Purchase Order)</option>
              <option value="cash">Cash Langsung</option>
            </select>
          </div>
          <div class="space-y-1">
            <label class="text-xs font-semibold" style="color: var(--text-muted);">Supplier</label>
            <select v-model="purchaseForm.supplier_id" class="input text-sm">
              <option value="">-- Pilih / Kosongkan --</option>
              <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </div>
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Nama Supplier Baru (jika tidak ada di daftar)</label>
          <input v-model="purchaseForm.supplier_name" type="text" class="input text-sm" placeholder="e.g. PT Sumber Jaya" />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Item Pembelian *</label>
          <div v-for="(item, i) in purchaseForm.items" :key="i" class="space-y-2 rounded-lg border p-3" style="border-color: var(--border-color);">
            <div class="grid grid-cols-2 gap-2">
              <div class="col-span-2">
                <select v-model="item.product_id" required class="input text-sm">
                  <option value="" disabled>-- Pilih Produk --</option>
                  <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
              </div>
              <input v-model="item.quantity" type="number" min="1" required class="input text-sm" placeholder="Qty" />
              <input v-model="item.unit_price" type="number" min="0" required class="input text-sm" placeholder="Harga Satuan" />
            </div>
            <button type="button" v-if="purchaseForm.items.length > 1" @click="removePurchaseItem(i)" class="text-xs font-semibold text-red-500 hover:underline">− Hapus item</button>
          </div>
          <button type="button" @click="addPurchaseItem" class="text-xs font-bold text-blue-600 hover:underline mt-1">+ Tambah item</button>
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Catatan</label>
          <textarea v-model="purchaseForm.note" rows="2" class="input text-sm" placeholder="Catatan pembelian..."></textarea>
        </div>
        <div class="flex justify-end gap-2 pt-3">
          <button type="button" @click="showPurchaseDrawer = false" class="btn-secondary text-xs">Batal</button>
          <button type="submit" :disabled="purchaseForm.processing" class="btn-primary text-xs">
            {{ purchaseForm.processing ? 'Menyimpan...' : 'Simpan Pembelian' }}
          </button>
        </div>
      </form>
    </Drawer>

    <!-- DRAWER DETAIL PEMBELIAN -->
    <Drawer :open="showPurchaseDetailDrawer" title="Detail Pembelian" @close="showPurchaseDetailDrawer = false" width="480px">
      <div v-if="selectedPurchase" class="space-y-4">
        <div class="grid grid-cols-2 gap-3">
          <div class="space-y-1">
            <label class="text-xs font-semibold" style="color: var(--text-muted);">No. Referensi</label>
            <div class="text-sm font-bold font-mono" style="color: var(--accent-primary);">{{ selectedPurchase.reference_number }}</div>
          </div>
          <div class="space-y-1">
            <label class="text-xs font-semibold" style="color: var(--text-muted);">Supplier</label>
            <div class="text-sm">{{ selectedPurchase.supplier?.name ?? '-' }}</div>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div class="space-y-1">
            <label class="text-xs font-semibold" style="color: var(--text-muted);">Tipe</label>
            <Badge :variant="selectedPurchase.type === 'product' ? 'blue' : 'purple'">{{ selectedPurchase.type === 'product' ? 'Stok Produk' : 'Alat Servis' }}</Badge>
          </div>
          <div class="space-y-1">
            <label class="text-xs font-semibold" style="color: var(--text-muted);">Tanggal</label>
            <div class="text-sm">{{ formatDate(selectedPurchase.created_at) }}</div>
          </div>
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Item</label>
          <div class="rounded-lg border divide-y" style="border-color: var(--border-color);">
            <div v-for="it in (selectedPurchase.items ?? [])" :key="it.id" class="flex items-center justify-between px-3 py-2 text-sm">
              <span>{{ it.product?.name ?? '-' }} <span class="text-xs" style="color: var(--text-muted);">x{{ it.quantity }}</span></span>
              <span class="font-semibold">Rp {{ formatNumber(it.unit_price ?? 0) }}</span>
            </div>
            <div v-if="!(selectedPurchase.items ?? []).length" class="px-3 py-2 text-xs" style="color: var(--text-muted);">Tidak ada item.</div>
          </div>
        </div>
        <div class="flex items-center justify-between border-t pt-3" style="border-color: var(--border-color);">
          <span class="text-xs font-semibold" style="color: var(--text-muted);">Total</span>
          <span class="text-lg font-bold">Rp {{ formatNumber(selectedPurchase.total) }}</span>
        </div>
        <div class="space-y-1" v-if="selectedPurchase.note">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Catatan</label>
          <div class="text-sm">{{ selectedPurchase.note }}</div>
        </div>
      </div>
    </Drawer>

    <!-- DRAWER RETUR PEMBELIAN BARU -->
    <Drawer :open="showReturnDrawer" title="Retur Pembelian Baru" @close="showReturnDrawer = false" width="520px">
      <form @submit.prevent="submitReturn" class="space-y-4">
        <div class="grid grid-cols-2 gap-3">
          <div class="space-y-1">
            <label class="text-xs font-semibold" style="color: var(--text-muted);">Supplier *</label>
            <select v-model="returnForm.supplier_id" required class="input text-sm">
              <option value="" disabled>-- Pilih Supplier --</option>
              <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </div>
          <div class="space-y-1">
            <label class="text-xs font-semibold" style="color: var(--text-muted);">Referensi Pembelian</label>
            <select v-model="returnForm.purchase_id" class="input text-sm">
              <option value="">-- Pilih / Kosongkan --</option>
              <option v-for="p in (purchases?.data ?? [])" :key="p.id" :value="p.id">{{ p.reference_number }} ({{ p.supplier?.name ?? '-' }})</option>
            </select>
          </div>
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Alasan Retur</label>
          <input v-model="returnForm.reason" type="text" class="input text-sm" placeholder="e.g. Barang rusak / salah kirim" />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Item Retur *</label>
          <div v-for="(item, i) in returnForm.items" :key="i" class="space-y-2 rounded-lg border p-3" style="border-color: var(--border-color);">
            <div class="grid grid-cols-2 gap-2">
              <div class="col-span-2">
                <select v-model="item.product_id" required class="input text-sm">
                  <option value="" disabled>-- Pilih Produk --</option>
                  <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
              </div>
              <input v-model="item.quantity" type="number" min="1" required class="input text-sm" placeholder="Qty" />
              <input v-model="item.price" type="number" min="0" class="input text-sm" placeholder="Harga Satuan" />
            </div>
            <div class="flex items-center justify-between">
              <select v-model="item.condition" class="input text-sm w-40">
                <option value="rusak">Rusak</option>
                <option value="salah">Salah Kirim</option>
                <option value="expired">Expired</option>
                <option value="lain">Lainnya</option>
              </select>
              <button type="button" v-if="returnForm.items.length > 1" @click="removeReturnItem(i)" class="text-xs font-semibold text-red-500 hover:underline">− Hapus</button>
            </div>
          </div>
          <button type="button" @click="addReturnItem" class="text-xs font-bold text-blue-600 hover:underline mt-1">+ Tambah item</button>
        </div>
        <div class="flex justify-end gap-2 pt-3">
          <button type="button" @click="showReturnDrawer = false" class="btn-secondary text-xs">Batal</button>
          <button type="submit" :disabled="returnForm.processing" class="btn-primary text-xs">
            {{ returnForm.processing ? 'Menyimpan...' : 'Simpan Retur' }}
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
import StatCard from '@/Components/StatCard.vue';
import { useFormatter } from '@/Composables/useFormatter.js';

const { formatNumber, formatCurrency, formatDate, currentDate } = useFormatter();

const props = defineProps({
  activeTab: { type: String, default: 'penjualan' },
  sales: { type: Object, default: null },
  salesStats: { type: Object, default: () => ({}) },
  salesFilters: { type: Object, default: () => ({ status: '', sale_type: '', search: '' }) },
  expenses: { type: Object, default: null },
  expenseCategories: { type: Array, default: () => [] },
  purchases: { type: Object, default: null },
  returns: { type: Object, default: null },
  suppliers: { type: Array, default: () => [] },
  products: { type: Array, default: () => [] },
});

const activeTab = ref(props.activeTab);
const expenseFilters = reactive({ category_id: '', search: '' });

// Expense Drawer
const showExpenseDrawer = ref(false);
const expenseForm = useForm({ description: '', amount: 0, category_id: '', expense_date: new Date().toISOString().slice(0, 10) });

const openExpenseModal = () => {
  expenseForm.reset();
  expenseForm.expense_date = new Date().toISOString().slice(0, 10);
  if (props.expenseCategories?.length > 0) {
    expenseForm.category_id = props.expenseCategories[0].id;
  }
  showExpenseDrawer.value = true;
};

const submitExpense = () => {
  expenseForm.post(route('expenses.store'), {
    preserveScroll: true,
    onSuccess: () => { showExpenseDrawer.value = false; }
  });
};

// Drawer Pembelian Baru (multi-item)
const showPurchaseDrawer = ref(false);
const purchaseForm = useForm({
  type: 'po', supplier_id: '', supplier_name: '', note: '',
  items: [{ product_id: '', quantity: 1, unit_price: '' }],
});

const openPurchaseDrawer = () => {
  purchaseForm.reset();
  purchaseForm.type = 'po';
  purchaseForm.items = [{ product_id: '', quantity: 1, unit_price: '' }];
  showPurchaseDrawer.value = true;
};
const addPurchaseItem = () => {
  purchaseForm.items.push({ product_id: '', quantity: 1, unit_price: '' });
};
const removePurchaseItem = (i) => {
  if (purchaseForm.items.length > 1) purchaseForm.items.splice(i, 1);
};
const submitPurchase = () => {
  purchaseForm.post(route('purchases.store'), {
    preserveScroll: true,
    onSuccess: () => { showPurchaseDrawer.value = false; }
  });
};

// Drawer Detail Pembelian
const showPurchaseDetailDrawer = ref(false);
const selectedPurchase = ref(null);
const openPurchaseDetail = (row) => {
  selectedPurchase.value = row;
  showPurchaseDetailDrawer.value = true;
};

// Drawer Retur Pembelian Baru (multi-item)
const showReturnDrawer = ref(false);
const returnForm = useForm({
  purchase_id: '', supplier_id: '', reason: '',
  items: [{ product_id: '', quantity: 1, price: '', condition: 'rusak' }],
});

const openReturnDrawer = () => {
  returnForm.reset();
  returnForm.items = [{ product_id: '', quantity: 1, price: '', condition: 'rusak' }];
  showReturnDrawer.value = true;
};
const addReturnItem = () => {
  returnForm.items.push({ product_id: '', quantity: 1, price: '', condition: 'rusak' });
};
const removeReturnItem = (i) => {
  if (returnForm.items.length > 1) returnForm.items.splice(i, 1);
};
const submitReturn = () => {
  returnForm.post(route('purchase-returns.store'), {
    preserveScroll: true,
    onSuccess: () => { showReturnDrawer.value = false; }
  });
};

const payDraft = (row) => {
  if (confirm(`Proses pembayaran transaksi draft #${row.id}?`)) {
    router.post(route('sales.pay-draft', row.id), {}, { preserveScroll: true });
  }
};

const updateReturnStatus = (row, status) => {
  router.post(route('purchase-returns.status', row.id), { status }, { preserveScroll: true });
};

const tabs = [
  { key: 'penjualan', label: 'Penjualan' },
  { key: 'pengeluaran', label: 'Pengeluaran' },
  { key: 'pembelian', label: 'Pembelian' },
  { key: 'retur', label: 'Retur Pembelian' },
];

const tabLabels = { penjualan: 'Penjualan', pengeluaran: 'Pengeluaran', pembelian: 'Pembelian', retur: 'Retur Pembelian' };
const pageTitle = computed(() => 'Keuangan — ' + (tabLabels[activeTab.value] || 'Penjualan'));
const subtitle = computed(() => currentDate.value);

const salesColumns = [
  { key: 'id', label: '#' },
  { key: 'customer', label: 'Pelanggan' },
  { key: 'total', label: 'Total', align: 'right' },
  { key: 'status', label: 'Status' },
  { key: 'created_at', label: 'Tanggal' },
  { key: 'action', label: '', align: 'right' },
];

const expenseColumns = [
  { key: 'description', label: 'Deskripsi' },
  { key: 'amount', label: 'Jumlah', align: 'right' },
  { key: 'category', label: 'Kategori' },
  { key: 'expense_date', label: 'Tanggal' },
];

const purchaseColumns = [
  { key: 'reference_number', label: 'Ref' },
  { key: 'supplier_name', label: 'Supplier' },
  { key: 'total', label: 'Total', align: 'right' },
  { key: 'type', label: 'Tipe' },
  { key: 'created_at', label: 'Tanggal' },
  { key: 'action', label: '', align: 'right' },
];

const returnColumns = [
  { key: 'reason', label: 'Alasan' },
  { key: 'status', label: 'Status' },
  { key: 'created_at', label: 'Tanggal' },
  { key: 'action', label: '', align: 'right' },
];

const switchTab = (key) => {
  router.get(route('keuangan.index'), { tab: key }, { preserveState: true, preserveScroll: true });
};

const applySalesFilter = () => {
  router.get(route('keuangan.index'), {
    tab: activeTab.value,
    ...props.salesFilters,
  }, { preserveState: true, preserveScroll: true });
};

const resetSalesFilter = () => {
  router.get(route('keuangan.index'), { tab: activeTab.value }, { preserveState: true, preserveScroll: true });
};

const applyExpenseFilter = () => {
  router.get(route('keuangan.index'), {
    tab: activeTab.value,
    ...expenseFilters,
  }, { preserveState: true, preserveScroll: true });
};

const resetExpenseFilter = () => {
  expenseFilters.category_id = '';
  expenseFilters.search = '';
  router.get(route('keuangan.index'), { tab: activeTab.value }, { preserveState: true, preserveScroll: true });
};
</script>

