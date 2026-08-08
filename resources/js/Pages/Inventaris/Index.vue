<template>
  <AuthenticatedLayout>
    <!-- Header CRM Style -->
    <div class="px-6 sm:px-8 py-6 sk-bg-card border-b sk-border flex flex-col sm:flex-row sm:items-center justify-between gap-4 sticky top-0 z-20">
      <div class="flex items-center gap-4">
          <div class="w-12 h-12 sk-bg-success-soft rounded-xl flex items-center justify-center border border-emerald-100">
              <svg class="w-6 h-6 sk-text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
          </div>
          <div>
              <h1 class="text-2xl font-bold sk-text-primary tracking-tight">{{ pageTitle }}</h1>
              <p class="text-sm sk-text-muted font-medium mt-0.5">{{ subtitle }}</p>
          </div>
      </div>
      
      <!-- Action Buttons -->
      <div class="flex flex-wrap items-center gap-2">
        <div v-if="activeTab === 'stok'" class="flex gap-2">
          <KButton  @click="openQuickStockModal()" class="inline-flex items-center gap-1.5 px-4 py-2 sk-bg-card border sk-border hover:sk-bg-hover sk-text-primary text-sm font-semibold rounded-xl transition-all shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Adjust Stok
          </KButton>
          <KButton  @click="openProductDrawer()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow-md transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Produk Baru
          </KButton>
        </div>

        <KButton  v-if="activeTab === 'transfer'" @click="openTransferDrawer()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow-md transition-all">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
          Transfer Stok
        </KButton>

        <KButton  v-if="activeTab === 'rusak'" @click="openDamagedModal()" class="inline-flex items-center gap-1.5 px-4 py-2 sk-bg-danger hover:sk-bg-danger text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow-md transition-all">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
          Catat Stok Rusak
        </KButton>

        <Link v-if="activeTab === 'reorder' || activeTab === 'forecast'" :href="route('keuangan.index', { tab: 'pembelian' })" class="inline-flex items-center gap-1.5 px-4 py-2 sk-bg-primary hover:sk-bg-primary text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow-md transition-all">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
          Order Pembelian
        </Link>
      </div>
    </div>
    
    <div class="p-6 max-w-[1400px] mx-auto w-full">
      <TabPage :tabs="tabs" v-model="activeTab" @update:model-value="switchTab">
      <!-- STOK PRODUK -->
      <template #stok>
        <div class="sk-bg-card rounded-2xl border sk-border shadow-sm overflow-hidden mt-6">
          <Skeleton v-if="!products" type="table" :count="5" class="p-6" />
          <KTable
            v-else
            :columns="stockColumns"
            :rows="products?.data ?? []"
            :emptyTitle="'Belum ada data produk'"
            :emptyDescription="'Mulai kelola inventaris dengan menambahkan produk pertama Anda.'"
            :emptyActionLabel="'+ Tambah Produk Baru'"
            @empty-action="openProductDrawer()"
          >
            <template #cell-name="{ row }">
              <div class="flex items-center gap-3 py-1">
                <div class="w-10 h-10 rounded-lg sk-bg-hover flex items-center justify-center sk-text-muted font-bold text-sm">
                  {{ row.name.substring(0, 2).toUpperCase() }}
                </div>
                <div>
                    <span class="font-bold sk-text-primary">{{ row.name }}</span>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-xs sk-text-muted font-mono">{{ row.sku || 'No SKU' }}</span>
                        <span class="w-1 h-1 rounded-full bg-zinc-300"></span>
                        <span class="text-xs sk-text-muted">{{ row.category?.name || 'Uncategorized' }}</span>
                    </div>
                </div>
              </div>
            </template>
            <template #cell-stock_quantity="{ row }">
              <div class="flex items-center gap-2">
                  <div class="w-2 h-2 rounded-full" :class="row.stock_quantity <= row.min_stock ? 'bg-red-500' : 'bg-emerald-500'"></div>
                  <span class="font-bold sk-text-primary">
                    {{ formatNumber(row.stock_quantity ?? 0) }} <span class="sk-text-muted font-normal text-xs">{{ row.unit?.name || 'pcs' }}</span>
                  </span>
                  <button @click="openAdjust(row)" class="ml-1 text-[10px] px-1.5 py-0.5 rounded sk-text-muted hover:sk-text-primary-brand hover:sk-bg-primary-soft transition-colors" title="Sesuaikan stok">✎</button>
              </div>
            </template>
            <template #cell-price="{ row }">
              <span class="font-semibold sk-text-primary">Rp {{ formatNumber(row.price) }}</span>
            </template>
            <template #cell-min_stock="{ row }">
              <span class="sk-text-muted">{{ formatNumber(row.min_stock ?? 0) }}</span>
            </template>
            <template #cell-action="{ row }">
              <div class="flex items-center justify-end">
                <KButton  @click="openQuickStockModal(row)" class="px-3 py-1.5 rounded-lg text-xs font-semibold sk-bg-card border sk-border sk-text-primary hover:sk-bg-hover hover:sk-text-primary-brand hover:sk-border-primary transition-all shadow-sm">
                    Adjust
                </KButton>
              </div>
            </template>
          </KTable>
          <div class="p-4 border-t sk-border sk-bg-hover">
              <Pagination :meta="products" />
          </div>
        </div>
      </template>

      <!-- TRANSFER STOK -->
      <template #transfer>
        <div class="sk-bg-card rounded-2xl border sk-border shadow-sm overflow-hidden mt-6">
          <Skeleton v-if="!allocations" type="table" :count="5" class="p-6" />
          <KTable
            v-else
            :columns="transferColumns"
            :rows="allocations?.data ?? []"
            :emptyTitle="'Belum ada transfer stok'"
            :emptyDescription="'Data pemindahan stok antar cabang akan tercatat di sini.'"
            :emptyActionLabel="'+ Transfer Stok Baru'"
            @empty-action="openTransferDrawer()"
          >
            <template #cell-product_name="{ row }">
              <span class="font-bold sk-text-primary">{{ row.product?.name ?? '-' }}</span>
            </template>
            <template #cell-from_branch="{ row }">
              <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md sk-bg-hover sk-text-primary text-xs font-medium">
                  {{ row.from_branch?.name ?? '-' }}
              </span>
            </template>
            <template #cell-to_branch="{ row }">
              <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md sk-bg-hover sk-text-primary text-xs font-medium">
                  {{ row.to_branch?.name ?? '-' }}
              </span>
            </template>
            <template #cell-quantity="{ row }">
              <span class="font-bold sk-text-primary">{{ formatNumber(row.quantity) }}</span>
            </template>
            <template #cell-status="{ row }">
              <Badge :status="row.status">{{ row.status }}</Badge>
            </template>
            <template #cell-action="{ row }">
              <div v-if="row.status === 'pending'" class="flex items-center justify-end gap-2">
                <KButton  @click="rejectTransfer(row)" class="px-3 py-1.5 rounded-lg text-xs font-semibold sk-bg-card border sk-border-primary sk-text-danger hover:sk-bg-danger-soft transition-all">Tolak</KButton>
                <KButton  @click="confirmTransfer(row)" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-600 text-white hover:bg-emerald-700 transition-all shadow-sm">Terima</KButton>
              </div>
            </template>
          </KTable>
          <div class="p-4 border-t sk-border sk-bg-hover">
              <Pagination :meta="allocations" />
          </div>
        </div>
      </template>

      <!-- STOK RUSAK -->
      <template #rusak>
        <div class="sk-bg-card rounded-2xl border sk-border shadow-sm overflow-hidden mt-6">
          <Skeleton v-if="!damagedStocks" type="table" :count="5" class="p-6" />
          <KTable
            v-else
            :columns="damagedColumns"
            :rows="damagedStocks?.data ?? []"
            :emptyTitle="'Belum ada stok rusak'"
            :emptyDescription="'Pencatatan barang rusak/hilang/expired akan muncul di sini.'"
            :emptyActionLabel="'+ Catat Stok Rusak'"
            @empty-action="openDamagedModal()"
          >
            <template #cell-product_name="{ row }">
              <span class="font-bold sk-text-primary">{{ row.product?.name ?? '-' }}</span>
            </template>
            <template #cell-type="{ row }">
              <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold" :class="row.type === 'damaged' ? 'sk-bg-danger-soft sk-text-danger' : 'sk-bg-warning-soft sk-text-warning'">
                  {{ row.type === 'damaged' ? 'Rusak / Fisik' : 'Hilang / Expired' }}
              </span>
            </template>
            <template #cell-quantity="{ row }">
              <span class="font-bold sk-text-danger">-{{ formatNumber(row.quantity) }}</span>
            </template>
            <template #cell-created_at="{ row }">
              <span class="sk-text-muted">{{ formatDate(row.created_at) }}</span>
            </template>
          </KTable>
          <div class="p-4 border-t sk-border sk-bg-hover">
              <Pagination :meta="damagedStocks" />
          </div>
        </div>
      </template>

      <!-- RIWAYAT MUTASI STOK -->
      <template #mutasi>
        <div class="space-y-6 mt-6">
          <Skeleton v-if="!mutations" type="table" :count="5" />
          <template v-else>
            <div class="sk-bg-card p-4 rounded-2xl border sk-border shadow-sm flex flex-wrap items-center gap-3">
                <div class="flex-1 min-w-[200px]">
                    <KSelect  v-model="localMutationFilters.product_id" @change="applyMutationFilter" class="w-full text-sm font-medium rounded-xl border sk-border px-4 py-2 sk-bg-card sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all">
                    <option value="">Semua Produk</option>
                    <option v-for="product in mutationProducts" :key="product.id" :value="product.id">{{ product.name }}</option>
                    </KSelect>
                </div>
                <div class="w-48">
                    <KSelect  v-model="localMutationFilters.mutation_type" @change="applyMutationFilter" class="w-full text-sm font-medium rounded-xl border sk-border px-4 py-2 sk-bg-card sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all">
                    <option value="">Semua Tipe Mutasi</option>
                    <option value="masuk">Masuk (In)</option>
                    <option value="keluar">Keluar (Out)</option>
                    <option value="transfer">Transfer</option>
                    <option value="rusak">Rusak</option>
                    </KSelect>
                </div>
                <KButton  @click="resetMutationFilter" class="px-4 py-2 rounded-xl text-sm font-semibold border sk-border sk-text-secondary hover:sk-bg-hover hover:sk-text-primary transition-all sk-bg-card shadow-sm">
                Reset Filter
                </KButton>
            </div>

            <div class="sk-bg-card rounded-2xl border sk-border shadow-sm overflow-hidden">
                <KTable
                :columns="mutationColumns"
                :rows="mutations?.data ?? []"
                :emptyTitle="'Belum ada riwayat mutasi'"
                :emptyDescription="'Riwayat pergerakan stok (masuk/keluar) akan dicatat otomatis.'"
                >
                <template #cell-product_name="{ row }">
                    <span class="font-bold sk-text-primary">{{ row.product?.name ?? '-' }}</span>
                </template>
                <template #cell-type="{ row }">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold capitalize" 
                          :class="{
                              'sk-bg-success-soft sk-text-success border sk-border-primary': row.type === 'masuk' || row.type === 'in',
                              'sk-bg-danger-soft sk-text-danger border sk-border-primary': row.type === 'keluar' || row.type === 'out',
                              'sk-bg-info-soft sk-text-info border border-blue-200': row.type !== 'masuk' && row.type !== 'in' && row.type !== 'keluar' && row.type !== 'out'
                          }">
                        {{ row.type }}
                    </span>
                </template>
                <template #cell-quantity="{ row }">
                    <span class="font-bold" :class="(row.type === 'masuk' || row.type === 'in') ? 'sk-text-success' : 'sk-text-primary'">
                        {{ (row.type === 'masuk' || row.type === 'in') ? '+' : '' }}{{ formatNumber(row.quantity) }}
                    </span>
                </template>
                <template #cell-created_at="{ row }">
                    <span class="sk-text-muted">{{ formatDate(row.created_at) }}</span>
                </template>
                </KTable>
                <div class="p-4 border-t sk-border sk-bg-hover">
                    <Pagination :meta="mutations" />
                </div>
            </div>
          </template>
        </div>
      </template>

      <!-- REORDER ALERTS -->
      <template #reorder>
        <div class="sk-bg-card rounded-2xl border sk-border shadow-sm overflow-hidden mt-6">
          <Skeleton v-if="!reorderAlerts" type="table" :count="5" class="p-6" />
          <KTable
            v-else
            :columns="reorderColumns"
            :rows="reorderAlerts?.data ?? reorderAlerts ?? []"
            :emptyTitle="'Stok Aman'"
            :emptyDescription="'Semua produk masih di atas batas minimum stok.'"
          >
            <template #cell-name="{ row }">
              <span class="font-bold sk-text-primary">{{ row.name ?? row.product?.name }}</span>
            </template>
            <template #cell-stock_quantity="{ row }">
              <span class="font-bold sk-text-danger text-lg">{{ formatNumber(row.stock_quantity ?? 0) }}</span>
            </template>
            <template #cell-min_stock="{ row }">
              <span class="sk-text-muted">{{ formatNumber(row.min_stock ?? 0) }}</span>
            </template>
            <template #cell-status="{ row }">
              <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold sk-bg-danger-soft sk-text-danger">
                  <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
                  {{ row.stock_quantity <= 0 ? 'Habis Total' : 'Perlu Restock' }}
              </span>
            </template>
            <template #cell-action="{ row }">
              <div class="flex justify-end">
                  <Link :href="route('keuangan.index', { tab: 'pembelian' })" class="px-4 py-2 rounded-xl text-xs font-bold text-white sk-bg-primary hover:sk-bg-primary shadow-sm transition-all">
                    Buat PO (Pembelian)
                  </Link>
              </div>
            </template>
          </KTable>
        </div>
      </template>

      <!-- STOK FORECASTING -->
      <template #forecast>
        <div class="sk-bg-card rounded-2xl border sk-border shadow-sm overflow-hidden mt-6">
          <Skeleton v-if="!forecast" type="table" :count="5" class="p-6" />
          <KTable
            v-else
            :columns="forecastColumns"
            :rows="forecast?.data ?? forecast ?? []"
            :emptyTitle="'Belum ada data forecast'"
            :emptyDescription="'Prediksi stok akan muncul setelah ada pola penjualan yang cukup.'"
          >
            <template #cell-name="{ row }">
              <span class="font-bold sk-text-primary">{{ row.name ?? row.product?.name }}</span>
            </template>
            <template #cell-stock="{ row }">
              <span class="font-medium sk-text-primary">{{ formatNumber(row.stock ?? row.stock_quantity ?? 0) }}</span>
            </template>
            <template #cell-monthly_usage="{ row }">
              <span class="sk-text-muted">{{ formatNumber(row.monthly_usage ?? 0) }} / bulan</span>
            </template>
            <template #cell-days_until_empty="{ row }">
              <span class="font-bold px-2.5 py-1 rounded-md text-xs" :class="(row.days_until_empty ?? 999) < 14 ? 'sk-bg-danger-soft sk-text-danger' : 'sk-bg-hover sk-text-primary'">
                Estimasi {{ formatNumber(row.days_until_empty ?? 0) }} hari
              </span>
            </template>
            <template #cell-needs_restock="{ row }">
              <Badge :variant="row.needs_restock ? 'red' : 'green'">{{ row.needs_restock ? 'Siap-siap Restock' : 'Aman' }}</Badge>
            </template>
          </KTable>
        </div>
      </template>
    </TabPage>
    </div>

    <!-- DRAWER PENYESUAIAN STOK CEPAT -->
    <Drawer :open="showQuickStockDrawer" title="Penyesuaian Stok Manual" @close="showQuickStockDrawer = false" width="460px">
      <form @submit.prevent="submitQuickStock" class="p-6 space-y-5">
        <div>
          <label class="block text-sm font-semibold sk-text-primary mb-1.5">Pilih Produk <span class="sk-text-danger">*</span></label>
          <KSelect  v-model="quickStockForm.product_id" required class="w-full rounded-xl border sk-border px-4 py-2.5 text-sm sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all">
            <option value="" disabled>-- Pilih Produk --</option>
            <option v-for="p in (products?.data ?? [])" :key="p.id" :value="p.id">{{ p.name }} (Stok: {{ p.stock_quantity }})</option>
          </KSelect>
        </div>
        <div>
          <label class="block text-sm font-semibold sk-text-primary mb-1.5">Tipe Penyesuaian <span class="sk-text-danger">*</span></label>
          <div class="grid grid-cols-3 gap-2">
              <label class="cursor-pointer">
                  <KRadio  v-model="quickStockForm.type" value="in" class="peer sr-only" />
                  <div class="px-3 py-2 text-center rounded-lg border sk-border text-xs font-semibold sk-text-muted peer-checked:sk-bg-success-soft peer-checked:sk-text-success peer-checked:sk-border-primary transition-all">
                      + Masuk
                  </div>
              </label>
              <label class="cursor-pointer">
                  <KRadio  v-model="quickStockForm.type" value="out" class="peer sr-only" />
                  <div class="px-3 py-2 text-center rounded-lg border sk-border text-xs font-semibold sk-text-muted peer-checked:sk-bg-danger-soft peer-checked:sk-text-danger peer-checked:sk-border-primary transition-all">
                      - Keluar
                  </div>
              </label>
              <label class="cursor-pointer">
                  <KRadio  v-model="quickStockForm.type" value="set" class="peer sr-only" />
                  <div class="px-3 py-2 text-center rounded-lg border sk-border text-xs font-semibold sk-text-muted peer-checked:sk-bg-primary-soft peer-checked:sk-text-primary-brand peer-checked:sk-border-primary transition-all">
                      = Set Total
                  </div>
              </label>
          </div>
        </div>
        <div>
          <label class="block text-sm font-semibold sk-text-primary mb-1.5">Jumlah Unit <span class="sk-text-danger">*</span></label>
          <KInput  v-model="quickStockForm.quantity" type="number" min="1" required class="w-full rounded-xl border sk-border px-4 py-2.5 text-sm sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all" />
        </div>
        <div>
          <label class="block text-sm font-semibold sk-text-primary mb-1.5">Alasan / Catatan</label>
          <KTextarea  v-model="quickStockForm.notes" rows="2" placeholder="e.g. Hasil Stock Opname" class="w-full rounded-xl border sk-border px-4 py-2.5 text-sm sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all resize-none"></KTextarea>
        </div>
        <div class="flex justify-end gap-3 pt-4 border-t sk-border-light">
          <KButton  type="button" @click="showQuickStockDrawer = false" class="px-5 py-2.5 rounded-xl border sk-border sk-text-primary text-sm font-bold hover:sk-bg-hover transition-all">Batal</KButton>
          <KButton  type="submit" :disabled="quickStockForm.processing" class="px-6 py-2.5 rounded-xl sk-bg-primary hover:sk-bg-primary text-white text-sm font-bold shadow-sm transition-all disabled:opacity-50">
            {{ quickStockForm.processing ? 'Menyimpan...' : 'Simpan Stok' }}
          </KButton>
        </div>
      </form>
    </Drawer>

    <!-- DRAWER CATAT STOK RUSAK -->
    <Drawer :open="showDamagedDrawer" title="Catat Stok Rusak / Hilang" @close="showDamagedDrawer = false" width="460px">
      <form @submit.prevent="submitDamagedStock" class="p-6 space-y-5">
        <div>
          <label class="block text-sm font-semibold sk-text-primary mb-1.5">Pilih Produk <span class="sk-text-danger">*</span></label>
          <KSelect  v-model="damagedForm.product_id" required class="w-full rounded-xl border sk-border px-4 py-2.5 text-sm sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all">
            <option value="" disabled>-- Pilih Produk --</option>
            <option v-for="p in mutationProducts" :key="p.id" :value="p.id">{{ p.name }}</option>
          </KSelect>
        </div>
        <div>
          <label class="block text-sm font-semibold sk-text-primary mb-1.5">Kategori Kerusakan <span class="sk-text-danger">*</span></label>
          <KSelect  v-model="damagedForm.type" required class="w-full rounded-xl border sk-border px-4 py-2.5 text-sm sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all">
            <option value="damaged">Fisik Rusak</option>
            <option value="lost">Hilang (Cacat)</option>
            <option value="expired">Expired / Kadaluarsa</option>
          </KSelect>
        </div>
        <div>
          <label class="block text-sm font-semibold sk-text-primary mb-1.5">Jumlah Unit <span class="sk-text-danger">*</span></label>
          <KInput  v-model="damagedForm.quantity" type="number" min="1" required class="w-full rounded-xl border sk-border px-4 py-2.5 text-sm sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all" />
        </div>
        <div>
          <label class="block text-sm font-semibold sk-text-primary mb-1.5">Keterangan Tambahan</label>
          <KTextarea  v-model="damagedForm.notes" rows="2" placeholder="Detail kerusakan fisik..." class="w-full rounded-xl border sk-border px-4 py-2.5 text-sm sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all resize-none"></KTextarea>
        </div>
        <div class="flex justify-end gap-3 pt-4 border-t sk-border-light">
          <KButton  type="button" @click="showDamagedDrawer = false" class="px-5 py-2.5 rounded-xl border sk-border sk-text-primary text-sm font-bold hover:sk-bg-hover transition-all">Batal</KButton>
          <KButton  type="submit" :disabled="damagedForm.processing" class="px-6 py-2.5 rounded-xl sk-bg-danger hover:sk-bg-danger text-white text-sm font-bold shadow-sm transition-all disabled:opacity-50">
            {{ damagedForm.processing ? 'Mencatat...' : 'Simpan Data' }}
          </KButton>
        </div>
      </form>
    </Drawer>

    <!-- DRAWER TAMBAH PRODUK BARU -->
    <Drawer :open="showProductDrawer" title="Tambah Produk Baru" @close="showProductDrawer = false" width="500px">
      <form @submit.prevent="submitProduct" class="p-6 space-y-5">
        <div class="grid grid-cols-2 gap-4">
          <div class="col-span-2">
            <label class="block text-sm font-semibold sk-text-primary mb-1.5">Nama Produk <span class="sk-text-danger">*</span></label>
            <KInput  v-model="productForm.name" type="text" required class="w-full rounded-xl border sk-border px-4 py-2.5 text-sm sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all" placeholder="Contoh: Oli Mesin Yamalube 1L" />
          </div>
          <div class="col-span-1">
            <label class="block text-sm font-semibold sk-text-primary mb-1.5">Kode / SKU</label>
            <KInput  v-model="productForm.code" type="text" class="w-full rounded-xl border sk-border px-4 py-2.5 text-sm sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all" placeholder="YML-001" />
          </div>
          <div class="col-span-1">
            <label class="block text-sm font-semibold sk-text-primary mb-1.5">Satuan</label>
            <KInput  v-model="productForm.unit" type="text" class="w-full rounded-xl border sk-border px-4 py-2.5 text-sm sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all" placeholder="pcs, botol, liter" />
          </div>
        </div>
        <div>
          <label class="block text-sm font-semibold sk-text-primary mb-1.5">Deskripsi Produk</label>
          <KTextarea  v-model="productForm.description" rows="2" class="w-full rounded-xl border sk-border px-4 py-2.5 text-sm sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all resize-none" placeholder="Opsional..."></KTextarea>
        </div>
        
        <div class="sk-bg-hover p-4 rounded-xl border sk-border grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold sk-text-primary mb-1.5">Harga Beli/Modal</label>
            <div class="relative">
                <span class="absolute left-3 top-2.5 sk-text-muted text-sm">Rp</span>
                <KInput  v-model="productForm.cost_price" type="number" min="0" class="w-full pl-9 rounded-xl border sk-border px-4 py-2.5 text-sm sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all" />
            </div>
          </div>
          <div>
            <label class="block text-sm font-semibold sk-text-primary mb-1.5">Harga Jual <span class="sk-text-danger">*</span></label>
            <div class="relative">
                <span class="absolute left-3 top-2.5 sk-text-muted text-sm">Rp</span>
                <KInput  v-model="productForm.selling_price" type="number" min="0" required class="w-full pl-9 rounded-xl border sk-border px-4 py-2.5 text-sm sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all" />
            </div>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold sk-text-primary mb-1.5">Stok Awal</label>
            <KInput  v-model="productForm.stock_quantity" type="number" min="0" class="w-full rounded-xl border sk-border px-4 py-2.5 text-sm sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all" />
          </div>
          <div>
            <label class="block text-sm font-semibold sk-text-primary mb-1.5">Batas Minimum (Alert)</label>
            <KInput  v-model="productForm.min_stock" type="number" min="0" class="w-full rounded-xl border sk-border px-4 py-2.5 text-sm sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all" />
          </div>
        </div>
        
        <div class="flex justify-end gap-3 pt-4 border-t sk-border-light">
          <KButton  type="button" @click="showProductDrawer = false" class="px-5 py-2.5 rounded-xl border sk-border sk-text-primary text-sm font-bold hover:sk-bg-hover transition-all">Batal</KButton>
          <KButton  type="submit" :disabled="productForm.processing" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold shadow-sm transition-all disabled:opacity-50">
            {{ productForm.processing ? 'Menyimpan...' : 'Simpan Produk' }}
          </KButton>
        </div>
      </form>
    </Drawer>

    <!-- DRAWER TRANSFER STOK ANTAR CABANG -->
    <Drawer :open="showTransferDrawer" title="Transfer Stok Antar Cabang" @close="showTransferDrawer = false" width="460px">
      <form @submit.prevent="submitTransfer" class="p-6 space-y-5">
        <div>
          <label class="block text-sm font-semibold sk-text-primary mb-1.5">Pilih Produk <span class="sk-text-danger">*</span></label>
          <KSelect  v-model="transferForm.product_id" required class="w-full rounded-xl border sk-border px-4 py-2.5 text-sm sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all">
            <option value="" disabled>-- Pilih Produk --</option>
            <option v-for="p in (products?.data ?? [])" :key="p.id" :value="p.id">{{ p.name }} (Stok saat ini: {{ p.stock_quantity }})</option>
          </KSelect>
        </div>
        <div class="flex items-center gap-2">
            <div class="flex-1">
                <label class="block text-sm font-semibold sk-text-primary mb-1.5">Cabang Asal</label>
                <KInput  type="text" disabled value="Cabang Saat Ini" class="w-full rounded-xl border sk-border px-4 py-2.5 text-sm sk-text-muted sk-bg-hover" />
            </div>
            <div class="mt-6 sk-text-muted">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </div>
            <div class="flex-1">
                <label class="block text-sm font-semibold sk-text-primary mb-1.5">Cabang Tujuan <span class="sk-text-danger">*</span></label>
                <KSelect  v-model="transferForm.to_branch_id" required class="w-full rounded-xl border sk-border px-4 py-2.5 text-sm sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all">
                    <option value="" disabled>-- Tujuan --</option>
                    <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                </KSelect>
            </div>
        </div>
        <div>
          <label class="block text-sm font-semibold sk-text-primary mb-1.5">Jumlah Transfer <span class="sk-text-danger">*</span></label>
          <KInput  v-model="transferForm.quantity" type="number" min="1" required class="w-full rounded-xl border sk-border px-4 py-2.5 text-sm sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all" />
        </div>
        <div class="flex justify-end gap-3 pt-4 border-t sk-border-light">
          <KButton  type="button" @click="showTransferDrawer = false" class="px-5 py-2.5 rounded-xl border sk-border sk-text-primary text-sm font-bold hover:sk-bg-hover transition-all">Batal</KButton>
          <KButton  type="submit" :disabled="transferForm.processing" class="px-6 py-2.5 rounded-xl sk-bg-primary hover:sk-bg-primary text-white text-sm font-bold shadow-sm transition-all disabled:opacity-50">
            {{ transferForm.processing ? 'Memproses...' : 'Transfer Stok' }}
          </KButton>
        </div>
      </form>
    </Drawer>
  </AuthenticatedLayout>
</template>

<script setup>
import KButton from '@/Components/KButton.vue';
import KInput from '@/Components/KInput.vue';
import KSelect from '@/Components/KSelect.vue';
import KTextarea from '@/Components/KTextarea.vue';
import KRadio from '@/Components/KRadio.vue';

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
  branches: { type: Array, default: () => [] },
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

// Drawer Tambah Produk Baru
const showProductDrawer = ref(false);
const productForm = useForm({
  code: '', name: '', description: '', unit: '',
  cost_price: '', selling_price: '', stock_quantity: 0, min_stock: 0,
});

const openProductDrawer = () => {
  productForm.reset();
  showProductDrawer.value = true;
};

const submitProduct = () => {
  productForm.post(route('products.store'), {
    preserveScroll: true,
    onSuccess: () => { showProductDrawer.value = false; }
  });
};

// Drawer Transfer Stok Antar Cabang
const showTransferDrawer = ref(false);
const transferForm = useForm({ to_branch_id: '', product_id: '', quantity: 1 });

const openTransferDrawer = () => {
  transferForm.reset();
  transferForm.quantity = 1;
  showTransferDrawer.value = true;
};

const submitTransfer = () => {
  if (!transferForm.to_branch_id || !transferForm.product_id) return;
  transferForm.post(route('stock-allocations.store'), {
    preserveScroll: true,
    onSuccess: () => { showTransferDrawer.value = false; }
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

