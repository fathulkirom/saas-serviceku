<template>
  <AuthenticatedLayout>
    <div class="flex flex-col min-h animate-fade-in-[calc(100vh-64px)] sk-bg-hover">
      <!-- Header CRM Style -->
      <div class="px-6 sm:px-8 py-6 sk-bg-card border-b sk-border flex flex-col sm:flex-row sm:items-center justify-between gap-4 sticky top-0 z-20">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 sk-bg-info-soft rounded-xl flex items-center justify-center border border-blue-100">
                <svg class="w-6 h-6 sk-text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold sk-text-primary tracking-tight">{{ pageTitle }}</h1>
                <p class="text-sm sk-text-muted font-medium mt-0.5">{{ subtitle }}</p>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-2">
          <Link v-if="activeTab === 'penjualan'" :href="route('sales.create')" class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow-md transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Transaksi Baru
          </Link>

          <KButton  v-if="activeTab === 'pengeluaran'" @click="openExpenseModal()" class="inline-flex items-center gap-1.5 px-4 py-2 sk-bg-danger hover:sk-bg-danger text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow-md transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Catat Pengeluaran
          </KButton>

          <KButton  v-if="activeTab === 'pembelian'" @click="openPurchaseDrawer()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow-md transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            Pembelian Stok
          </KButton>

          <a :href="route('keuangan.export', { month: currentMonth })" class="inline-flex items-center gap-1.5 px-4 py-2 bg-zinc-600 hover:bg-zinc-700 text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow-md transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export CSV
          </a>

          <KButton  v-if="activeTab === 'retur'" @click="openReturnDrawer()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow-md transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
            Retur Baru
          </KButton>
        </div>
      </div>

      <div class="flex-1 p-6 sm:p-8 max-w-[1400px] mx-auto w-full">

        <!-- Daily Close Summary -->
        <div v-if="dailySummary" class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-6">
          <div class="p-3 sk-bg-card rounded-xl border sk-border shadow-sm">
            <p class="text-xs sk-text-muted">Pemasukan Hari Ini</p>
            <p class="text-lg font-bold sk-text-success">Rp {{ formatNumber(dailySummary.income) }}</p>
          </div>
          <div class="p-3 sk-bg-card rounded-xl border sk-border shadow-sm">
            <p class="text-xs sk-text-muted">Pengeluaran Hari Ini</p>
            <p class="text-lg font-bold sk-text-danger">Rp {{ formatNumber(dailySummary.expense) }}</p>
          </div>
          <div class="p-3 sk-bg-card rounded-xl border sk-border shadow-sm">
            <p class="text-xs sk-text-muted">Saldo Hari Ini</p>
            <p class="text-lg font-bold" :class="dailySummary.income - dailySummary.expense >= 0 ? 'sk-text-info' : 'sk-text-danger'">
              Rp {{ formatNumber(dailySummary.income - dailySummary.expense) }}
            </p>
          </div>
        </div>
        <TabPage :tabs="tabs" v-model="activeTab" @update:model-value="switchTab">
      <!-- PENJUALAN -->
      <template #penjualan>
        <div class="space-y-6 mt-6">
          <Skeleton v-if="!sales" type="stat" :count="3" />
          <div v-else class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="sk-bg-card p-6 rounded-2xl border sk-border shadow-sm relative overflow-hidden group">
                <div class="absolute right-0 top-0 w-24 h-24 sk-bg-success-soft rounded-full blur-2xl -mr-8 -mt-8"></div>
                <div class="relative z-10 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold sk-text-muted uppercase tracking-wider mb-1">Servis Selesai</p>
                        <h3 class="text-3xl font-black sk-text-primary">{{ salesStats?.completed ?? 0 }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl sk-bg-success-soft border border-emerald-100 flex items-center justify-center sk-text-success">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                </div>
            </div>
            
            <div class="sk-bg-card p-6 rounded-2xl border sk-border shadow-sm relative overflow-hidden group">
                <div class="absolute right-0 top-0 w-24 h-24 sk-bg-warning-soft rounded-full blur-2xl -mr-8 -mt-8"></div>
                <div class="relative z-10 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold sk-text-muted uppercase tracking-wider mb-1">Draft & DP</p>
                        <h3 class="text-3xl font-black sk-text-primary">{{ salesStats?.draft ?? 0 }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl sk-bg-warning-soft border border-amber-100 flex items-center justify-center sk-text-warning">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                </div>
            </div>

            <div class="sk-bg-card p-6 rounded-2xl border sk-border shadow-sm relative overflow-hidden group">
                <div class="absolute right-0 top-0 w-24 h-24 sk-bg-info-soft rounded-full blur-2xl -mr-8 -mt-8"></div>
                <div class="relative z-10 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold sk-text-muted uppercase tracking-wider mb-1">Nota Lunas</p>
                        <h3 class="text-3xl font-black sk-text-primary">{{ salesStats?.paid ?? 0 }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl sk-bg-info-soft border border-blue-100 flex items-center justify-center sk-text-info">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
            </div>
          </div>

          <div class="sk-bg-card p-4 rounded-2xl border sk-border shadow-sm flex flex-wrap items-center gap-3">
              <KSelect  v-model="salesFilters.status" @change="applySalesFilter" class="w-40 text-sm font-medium rounded-xl border sk-border px-4 py-2 sk-bg-card sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all">
                <option value="">Semua Status</option>
                <option value="lunas">Lunas</option>
                <option value="draft">Draft</option>
                <option value="dp">DP</option>
                <option value="unpaid">Belum Bayar</option>
              </KSelect>
              <KSelect  v-model="salesFilters.sale_type" @change="applySalesFilter" class="w-40 text-sm font-medium rounded-xl border sk-border px-4 py-2 sk-bg-card sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all">
                <option value="">Semua Tipe</option>
                <option value="service">Servis</option>
                <option value="product">Produk</option>
              </KSelect>
              <div class="relative flex-1 min-w-[200px]">
                <KInput 
                  type="text"
                  v-model="salesFilters.search"
                  @keyup.enter="applySalesFilter"
                  placeholder="Cari nama pelanggan..."
                  class="w-full rounded-xl border sk-border px-4 py-2 pl-10 text-sm sk-bg-card sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all" />
                <svg class="absolute left-3.5 top-2.5 w-4 h-4 sk-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
              </div>
              <KButton  @click="resetSalesFilter" class="px-4 py-2 rounded-xl text-sm font-semibold border sk-border sk-text-secondary hover:sk-bg-hover transition-all sk-bg-card shadow-sm">
                Reset
              </KButton>
          </div>

          <div class="sk-bg-card rounded-2xl border sk-border shadow-sm overflow-hidden">
            <KTable
              :columns="salesColumns"
              :rows="sales?.data ?? []"
              :emptyTitle="'Belum ada data penjualan'"
              :emptyDescription="'Transaksi baru akan dicatat di sini.'"
              :emptyActionLabel="'+ Transaksi Penjualan Baru'"
              @empty-action="router.visit(route('sales.create'))"
            >
              <template #cell-id="{ row }">
                <span class="font-mono text-xs font-bold sk-text-primary-brand sk-bg-primary-soft px-2 py-1 rounded-md">#{{ row.id }}</span>
              </template>
              <template #cell-customer="{ row }">
                <div class="flex items-center gap-3 py-1">
                  <div class="w-8 h-8 rounded-full sk-bg-hover flex items-center justify-center sk-text-secondary font-bold text-xs shrink-0">
                    {{ (row.customer?.name ?? 'U').charAt(0).toUpperCase() }}
                  </div>
                  <span class="font-bold sk-text-primary">{{ row.customer?.name ?? 'Pelanggan Umum' }}</span>
                </div>
              </template>
              <template #cell-total="{ row }">
                <span class="font-bold sk-text-primary">Rp {{ formatNumber(row.total) }}</span>
              </template>
              <template #cell-status="{ row }">
                <Badge :status="row.status">{{ row.status }}</Badge>
              </template>
              <template #cell-created_at="{ row }">
                <span class="sk-text-muted">{{ formatDate(row.created_at) }}</span>
              </template>
              <template #cell-action="{ row }">
                <div class="flex items-center justify-end gap-2">
                  <KButton  v-if="row.status === 'draft'" @click="payDraft(row)" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-600 text-white hover:bg-emerald-700 transition-all shadow-sm">Bayar</KButton>
                  <Link :href="route('sales.show', row.id)" class="px-3 py-1.5 rounded-lg text-xs font-semibold sk-bg-card border sk-border sk-text-primary hover:sk-bg-hover transition-all">Detail</Link>
                  <a :href="route('sales.print', row.id)" target="_blank" class="p-1.5 rounded-lg sk-text-muted hover:sk-bg-hover hover:sk-text-secondary transition-all" title="Cetak Nota">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                  </a>
                </div>
              </template>
            </KTable>
            <div class="p-4 border-t sk-border sk-bg-hover">
              <Pagination :meta="sales" />
            </div>
          </div>
        </div>
      </template>

      <!-- PENGELUARAN -->
      <template #pengeluaran>
        <div class="space-y-6 mt-6">
          <Skeleton v-if="!expenses" type="table" :count="5" />
          <template v-else>
            <div class="sk-bg-card p-4 rounded-2xl border sk-border shadow-sm flex flex-wrap items-center gap-3">
                <KSelect  v-model="expenseFilters.category_id" @change="applyExpenseFilter" class="w-48 text-sm font-medium rounded-xl border sk-border px-4 py-2 sk-bg-card sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all">
                  <option value="">Semua Kategori</option>
                  <option v-for="cat in expenseCategories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </KSelect>
                <div class="relative flex-1 min-w-[200px]">
                  <KInput 
                    type="text"
                    v-model="expenseFilters.search"
                    @keyup.enter="applyExpenseFilter"
                    placeholder="Cari deskripsi pengeluaran..."
                    class="w-full rounded-xl border sk-border px-4 py-2 pl-10 text-sm sk-bg-card sk-text-primary focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all" />
                  <svg class="absolute left-3.5 top-2.5 w-4 h-4 sk-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <KButton  @click="resetExpenseFilter" class="px-4 py-2 rounded-xl text-sm font-semibold border sk-border sk-text-secondary hover:sk-bg-hover transition-all sk-bg-card shadow-sm">
                  Reset
                </KButton>
            </div>

            <div class="sk-bg-card rounded-2xl border sk-border shadow-sm overflow-hidden">
              <KTable
                :columns="expenseColumns"
                :rows="expenses?.data ?? []"
                :emptyTitle="'Belum ada pengeluaran'"
                :emptyDescription="'Data pengeluaran operasional toko akan muncul setelah ditambahkan.'"
                :emptyActionLabel="'+ Catat Pengeluaran Baru'"
                @empty-action="openExpenseModal()"
              >
                <template #cell-description="{ row }">
                  <span class="font-bold sk-text-primary">{{ row.description }}</span>
                </template>
                <template #cell-amount="{ row }">
                  <span class="font-bold sk-text-danger">Rp {{ formatNumber(row.amount) }}</span>
                </template>
                <template #cell-category="{ row }">
                  <Badge variant="purple">{{ row.category?.name ?? '-' }}</Badge>
                </template>
                <template #cell-expense_date="{ row }">
                  <span class="sk-text-muted">{{ formatDate(row.expense_date) }}</span>
                </template>
              </KTable>
              <div class="p-4 border-t sk-border sk-bg-hover">
                <Pagination :meta="expenses" />
              </div>
            </div>
          </template>
        </div>
      </template>

      <!-- PEMBELIAN STOK -->
      <template #pembelian>
        <div class="space-y-6 mt-6">
          <Skeleton v-if="!purchases" type="table" :count="5" />
          <div v-else class="sk-bg-card rounded-2xl border sk-border shadow-sm overflow-hidden">
            <KTable
              :columns="purchaseColumns"
              :rows="purchases?.data ?? []"
              :emptyTitle="'Belum ada pembelian stok'"
              :emptyDescription="'Data pembelian dari supplier akan muncul di sini.'"
              :emptyActionLabel="'+ Buat Pembelian Stok Baru'"
              @empty-action="openPurchaseDrawer()"
            >
              <template #cell-reference_number="{ row }">
                <span class="font-mono text-xs font-bold sk-text-primary-brand sk-bg-primary-soft px-2 py-1 rounded-md">{{ row.reference_number ?? '-' }}</span>
              </template>
              <template #cell-supplier_name="{ row }">
                <span class="font-bold sk-text-primary">{{ row.supplier?.name ?? '-' }}</span>
              </template>
              <template #cell-total="{ row }">
                <span class="font-bold sk-text-primary">Rp {{ formatNumber(row.total) }}</span>
              </template>
              <template #cell-type="{ row }">
                <Badge :variant="row.type === 'cash' ? 'green' : 'purple'">{{ row.type === 'cash' ? 'Cash' : 'PO' }}</Badge>
              </template>
              <template #cell-created_at="{ row }">
                <span class="sk-text-muted">{{ formatDate(row.created_at) }}</span>
              </template>
              <template #cell-action="{ row }">
                <KButton  @click="openPurchaseDetail(row)" class="px-3 py-1.5 rounded-lg text-xs font-semibold sk-bg-card border sk-border sk-text-primary hover:sk-bg-hover transition-all shadow-sm">Detail</KButton>
              </template>
            </KTable>
            <div class="p-4 border-t sk-border sk-bg-hover">
              <Pagination :meta="purchases" />
            </div>
          </div>
        </div>
      </template>

      <!-- RETUR PEMBELIAN -->
      <template #retur>
        <div class="space-y-6 mt-6">
          <Skeleton v-if="!returns" type="table" :count="5" />
          <div v-else class="sk-bg-card rounded-2xl border sk-border shadow-sm overflow-hidden">
            <KTable
              :columns="returnColumns"
              :rows="returns?.data ?? []"
              :emptyTitle="'Belum ada data retur'"
              :emptyDescription="'Data pengembalian barang ke supplier akan muncul di sini.'"
              :emptyActionLabel="'+ Retur Pembelian Baru'"
              @empty-action="openReturnDrawer()"
            >
              <template #cell-reason="{ row }">
                <span class="font-bold sk-text-primary">{{ row.reason ?? '-' }}</span>
              </template>
              <template #cell-status="{ row }">
                <Badge :status="row.status">{{ row.status }}</Badge>
              </template>
              <template #cell-created_at="{ row }">
                <span class="sk-text-muted">{{ formatDate(row.created_at) }}</span>
              </template>
              <template #cell-action="{ row }">
                <KSelect  @change="updateReturnStatus(row, $event.target.value)" class="text-xs py-1.5 px-3 rounded-lg border sk-border font-semibold sk-bg-card sk-text-primary outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500">
                  <option disabled selected>Ubah Status</option>
                  <option value="dikirim">Dikirim</option>
                  <option value="diproses_supplier">Diproses Supplier</option>
                  <option value="selesai">Selesai</option>
                  <option value="ditolak">Ditolak</option>
                </KSelect>
              </template>
            </KTable>
            <div class="p-4 border-t sk-border sk-bg-hover">
              <Pagination :meta="returns" />
            </div>
          </div>
        </div>
      </template>
    </TabPage>
    </div>

    <!-- DRAWER TAMBAH PENGELUARAN -->
    <Drawer :open="showExpenseDrawer" title="Catat Pengeluaran Operasional Baru" @close="showExpenseDrawer = false" width="450px">
      <form @submit.prevent="submitExpense" class="space-y-4">
        <div class="space-y-1">
          <label class="text-xs font-semibold sk-text-muted">Deskripsi Pengeluaran *</label>
          <KInput  v-model="expenseForm.description" required placeholder="e.g. Bayar Listrik / Beli Kertas Thermal" class="input text-sm" />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold sk-text-muted">Jumlah Nominal (Rp) *</label>
          <KInput  v-model="expenseForm.amount" type="number" min="100" required placeholder="0" class="input text-sm" />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold sk-text-muted">Kategori *</label>
          <KSelect  v-model="expenseForm.category_id" required class="input text-sm">
            <option value="" disabled>-- Pilih Kategori --</option>
            <option v-for="cat in expenseCategories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
          </KSelect>
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold sk-text-muted">Tanggal Pengeluaran *</label>
          <KInput  v-model="expenseForm.expense_date" type="date" required class="input text-sm" />
        </div>
        <div class="flex justify-end gap-2 pt-3">
          <KButton  type="button" @click="showExpenseDrawer = false" class="btn-secondary text-xs">Batal</KButton>
          <KButton  type="submit" :disabled="expenseForm.processing" class="btn-primary text-xs">
            {{ expenseForm.processing ? 'Menyimpan...' : 'Simpan Pengeluaran' }}
          </KButton>
        </div>
      </form>
    </Drawer>

    <!-- DRAWER PEMBELIAN BARU -->
    <Drawer :open="showPurchaseDrawer" title="Pembelian Baru" @close="showPurchaseDrawer = false" width="520px">
      <form @submit.prevent="submitPurchase" class="space-y-4">
        <div class="grid grid-cols-2 gap-3">
          <div class="space-y-1">
            <label class="text-xs font-semibold sk-text-muted">Tipe *</label>
            <KSelect  v-model="purchaseForm.type" required class="input text-sm">
              <option value="po">PO (Purchase Order)</option>
              <option value="cash">Cash Langsung</option>
            </KSelect>
          </div>
          <div class="space-y-1">
            <label class="text-xs font-semibold sk-text-muted">Supplier</label>
            <KSelect  v-model="purchaseForm.supplier_id" class="input text-sm">
              <option value="">-- Pilih / Kosongkan --</option>
              <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
            </KSelect>
          </div>
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold sk-text-muted">Nama Supplier Baru (jika tidak ada di daftar)</label>
          <KInput  v-model="purchaseForm.supplier_name" type="text" class="input text-sm" placeholder="e.g. PT Sumber Jaya" />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold sk-text-muted">Item Pembelian *</label>
          <div v-for="(item, i) in purchaseForm.items" :key="i" class="space-y-2 rounded-lg border p-3 sk-border">
            <div class="grid grid-cols-2 gap-2">
              <div class="col-span-2">
                <KSelect  v-model="item.product_id" required class="input text-sm">
                  <option value="" disabled>-- Pilih Produk --</option>
                  <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                </KSelect>
              </div>
              <KInput  v-model="item.quantity" type="number" min="1" required class="input text-sm" placeholder="Qty" />
              <KInput  v-model="item.unit_price" type="number" min="0" required class="input text-sm" placeholder="Harga Satuan" />
            </div>
            <KButton  type="button" v-if="purchaseForm.items.length > 1" @click="removePurchaseItem(i)" class="text-xs font-semibold sk-text-danger hover:underline">− Hapus item</KButton>
          </div>
          <KButton  type="button" @click="addPurchaseItem" class="text-xs font-bold sk-text-info hover:underline mt-1">+ Tambah item</KButton>
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold sk-text-muted">Catatan</label>
          <KTextarea  v-model="purchaseForm.note" rows="2" class="input text-sm" placeholder="Catatan pembelian..."></KTextarea>
        </div>
        <div class="flex justify-end gap-2 pt-3">
          <KButton  type="button" @click="showPurchaseDrawer = false" class="btn-secondary text-xs">Batal</KButton>
          <KButton  type="submit" :disabled="purchaseForm.processing" class="btn-primary text-xs">
            {{ purchaseForm.processing ? 'Menyimpan...' : 'Simpan Pembelian' }}
          </KButton>
        </div>
      </form>
    </Drawer>

    <!-- DRAWER DETAIL PEMBELIAN -->
    <Drawer :open="showPurchaseDetailDrawer" title="Detail Pembelian" @close="showPurchaseDetailDrawer = false" width="480px">
      <div v-if="selectedPurchase" class="space-y-4">
        <div class="grid grid-cols-2 gap-3">
          <div class="space-y-1">
            <label class="text-xs font-semibold sk-text-muted">No. Referensi</label>
            <div class="text-sm font-bold font-mono sk-text-primary-brand">{{ selectedPurchase.reference_number }}</div>
          </div>
          <div class="space-y-1">
            <label class="text-xs font-semibold sk-text-muted">Supplier</label>
            <div class="text-sm">{{ selectedPurchase.supplier?.name ?? '-' }}</div>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div class="space-y-1">
            <label class="text-xs font-semibold sk-text-muted">Tipe</label>
            <Badge :variant="selectedPurchase.type === 'cash' ? 'green' : 'purple'">{{ selectedPurchase.type === 'cash' ? 'Cash' : 'PO' }}</Badge>
          </div>
          <div class="space-y-1">
            <label class="text-xs font-semibold sk-text-muted">Tanggal</label>
            <div class="text-sm">{{ formatDate(selectedPurchase.created_at) }}</div>
          </div>
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold sk-text-muted">Item</label>
          <div class="rounded-lg border divide-y sk-border">
            <div v-for="it in (selectedPurchase.items ?? [])" :key="it.id" class="flex items-center justify-between px-3 py-2 text-sm">
              <span>{{ it.product?.name ?? '-' }} <span class="text-xs sk-text-muted">x{{ it.quantity }}</span></span>
              <span class="font-semibold">Rp {{ formatNumber(it.unit_price ?? 0) }}</span>
            </div>
            <div v-if="!(selectedPurchase.items ?? []).length" class="px-3 py-2 text-xs sk-text-muted">Tidak ada item.</div>
          </div>
        </div>
        <div class="flex items-center justify-between border-t pt-3 sk-border">
          <span class="text-xs font-semibold sk-text-muted">Total</span>
          <span class="text-lg font-bold">Rp {{ formatNumber(selectedPurchase.total) }}</span>
        </div>
        <div class="space-y-1" v-if="selectedPurchase.note">
          <label class="text-xs font-semibold sk-text-muted">Catatan</label>
          <div class="text-sm">{{ selectedPurchase.note }}</div>
        </div>
      </div>
    </Drawer>

    <!-- DRAWER RETUR PEMBELIAN BARU -->
    <Drawer :open="showReturnDrawer" title="Retur Pembelian Baru" @close="showReturnDrawer = false" width="520px">
      <form @submit.prevent="submitReturn" class="space-y-4">
        <div class="grid grid-cols-2 gap-3">
          <div class="space-y-1">
            <label class="text-xs font-semibold sk-text-muted">Supplier *</label>
            <KSelect  v-model="returnForm.supplier_id" required class="input text-sm">
              <option value="" disabled>-- Pilih Supplier --</option>
              <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
            </KSelect>
          </div>
          <div class="space-y-1">
            <label class="text-xs font-semibold sk-text-muted">Referensi Pembelian</label>
            <KSelect  v-model="returnForm.purchase_id" class="input text-sm">
              <option value="">-- Pilih / Kosongkan --</option>
              <option v-for="p in (purchases?.data ?? [])" :key="p.id" :value="p.id">{{ p.reference_number }} ({{ p.supplier?.name ?? '-' }})</option>
            </KSelect>
          </div>
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold sk-text-muted">Alasan Retur</label>
          <KInput  v-model="returnForm.reason" type="text" class="input text-sm" placeholder="e.g. Barang rusak / salah kirim" />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold sk-text-muted">Item Retur *</label>
          <div v-for="(item, i) in returnForm.items" :key="i" class="space-y-2 rounded-lg border p-3 sk-border">
            <div class="grid grid-cols-2 gap-2">
              <div class="col-span-2">
                <KSelect  v-model="item.product_id" required class="input text-sm">
                  <option value="" disabled>-- Pilih Produk --</option>
                  <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                </KSelect>
              </div>
              <KInput  v-model="item.quantity" type="number" min="1" required class="input text-sm" placeholder="Qty" />
              <KInput  v-model="item.price" type="number" min="0" class="input text-sm" placeholder="Harga Satuan" />
            </div>
            <div class="flex items-center justify-between">
              <KSelect  v-model="item.condition" class="input text-sm w-40">
                <option value="rusak">Rusak</option>
                <option value="salah">Salah Kirim</option>
                <option value="expired">Expired</option>
                <option value="lain">Lainnya</option>
              </KSelect>
              <KButton  type="button" v-if="returnForm.items.length > 1" @click="removeReturnItem(i)" class="text-xs font-semibold sk-text-danger hover:underline">− Hapus</KButton>
            </div>
          </div>
          <KButton  type="button" @click="addReturnItem" class="text-xs font-bold sk-text-info hover:underline mt-1">+ Tambah item</KButton>
        </div>
        <div class="flex justify-end gap-2 pt-3">
          <KButton  type="button" @click="showReturnDrawer = false" class="btn-secondary text-xs">Batal</KButton>
          <KButton  type="submit" :disabled="returnForm.processing" class="btn-primary text-xs">
            {{ returnForm.processing ? 'Menyimpan...' : 'Simpan Retur' }}
          </KButton>
        </div>
      </form>
    </Drawer>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import KButton from '@/Components/KButton.vue';
import KInput from '@/Components/KInput.vue';
import KSelect from '@/Components/KSelect.vue';
import KTextarea from '@/Components/KTextarea.vue';

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
  dailySummary: { type: Object, default: null },
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
const currentMonth = computed(() => new Date().toISOString().slice(0, 7));

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

