<template>
  <AuthenticatedLayout>
    <div class="flex flex-col min-h animate-fade-in" :style="{ background: 'var(--bg-app)' }">
      <!-- Header -->
      <div class="px-4 sm:px-6 lg:px-8 py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sticky top-0 z-20" :style="{ background: 'var(--bg-topbar)', borderBottom: '1px solid var(--border-color)' }">
        <div>
          <h1 class="text-xl font-extrabold tracking-tight" :style="{ color: 'var(--text-primary)' }">Dashboard CS</h1>
          <p class="text-sm mt-0.5" :style="{ color: 'var(--text-muted)' }">Halo, {{ $page.props.auth.user.name }}! {{ currentDate }}</p>
        </div>
        <div class="flex items-center gap-2">
          <Link :href="route('services.create')" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-sm font-bold text-white transition-all shadow-sm hover:shadow-md" style="background: var(--color-primary)">
            + Service Baru
          </Link>
        </div>
      </div>

      <div class="flex-1 px-4 sm:px-6 lg:px-8 max-w-[1400px] mx-auto w-full py-6 space-y-5">
        <!-- Priority Stats -->
        <Skeleton v-if="!stats" type="stat" :count="4" />
        <div v-else class="grid grid-cols-2 lg:grid-cols-4 gap-3">
          <div class="p-4 rounded-xl border animate-slide-up" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg" style="background: var(--color-primary-soft)">📥</div>
              <div>
                <p class="text-xs font-semibold" :style="{ color: 'var(--text-muted)' }">Masuk Hari Ini</p>
                <p class="text-2xl font-extrabold" :style="{ color: 'var(--text-primary)' }">{{ stats.services_today ?? 0 }}</p>
              </div>
            </div>
          </div>
          <div class="p-4 rounded-xl border animate-slide-up" :style="{ animationDelay: '50ms', background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg" style="background: var(--color-warning-soft)">⏳</div>
              <div>
                <p class="text-xs font-semibold" :style="{ color: 'var(--text-muted)' }">Menunggu Alokasi</p>
                <p class="text-2xl font-extrabold" :style="{ color: 'var(--color-warning-text)' }">{{ stats.pending_allocation ?? 0 }}</p>
              </div>
            </div>
          </div>
          <div class="p-4 rounded-xl border animate-slide-up" :style="{ animationDelay: '100ms', background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg" style="background: var(--color-info-soft)">🔧</div>
              <div>
                <p class="text-xs font-semibold" :style="{ color: 'var(--text-muted)' }">Servis Aktif</p>
                <p class="text-2xl font-extrabold" :style="{ color: 'var(--color-info-text)' }">{{ stats.active_services ?? 0 }}</p>
              </div>
            </div>
          </div>
          <div class="p-4 rounded-xl border animate-slide-up" :style="{ animationDelay: '150ms', background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg" style="background: var(--color-success-soft)">✅</div>
              <div>
                <p class="text-xs font-semibold" :style="{ color: 'var(--text-muted)' }">Pelanggan Baru</p>
                <p class="text-2xl font-extrabold" :style="{ color: 'var(--color-success-text)' }">{{ stats.new_customers_today ?? 0 }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="p-5 rounded-xl border" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
          <h3 class="text-sm font-bold mb-3" :style="{ color: 'var(--text-primary)' }">Aksi Cepat</h3>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <Link :href="route('services.create')" class="flex flex-col items-center justify-center gap-2 px-3 py-4 rounded-xl border transition-all hover:-translate-y-0.5" :style="{ borderColor: 'var(--color-primary-soft)', background: 'var(--color-primary-soft)' }">
              <span class="text-xl">➕</span>
              <span class="text-xs font-bold" :style="{ color: 'var(--color-primary-text)' }">Servis Baru</span>
            </Link>
            <Link :href="route('customers.create')" class="flex flex-col items-center justify-center gap-2 px-3 py-4 rounded-xl border transition-all hover:-translate-y-0.5" :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-card)' }">
              <span class="text-xl">👤</span>
              <span class="text-xs font-semibold" :style="{ color: 'var(--text-secondary)' }">Pelanggan Baru</span>
            </Link>
            <Link :href="route('keuangan.index')" class="flex flex-col items-center justify-center gap-2 px-3 py-4 rounded-xl border transition-all hover:-translate-y-0.5" :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-card)' }">
              <span class="text-xl">💳</span>
              <span class="text-xs font-semibold" :style="{ color: 'var(--text-secondary)' }">Transaksi</span>
            </Link>
            <Link :href="route('customers.index')" class="flex flex-col items-center justify-center gap-2 px-3 py-4 rounded-xl border transition-all hover:-translate-y-0.5" :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-card)' }">
              <span class="text-xl">📋</span>
              <span class="text-xs font-semibold" :style="{ color: 'var(--text-secondary)' }">Data Member</span>
            </Link>
          </div>
        </div>

        <!-- Tables: Recent + Unallocated -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
          <div class="rounded-xl border overflow-hidden" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
            <div class="p-4 border-b" :style="{ borderColor: 'var(--border-light)' }">
              <h3 class="text-sm font-bold" :style="{ color: 'var(--text-primary)' }">Servis Terbaru</h3>
            </div>
            <template v-if="!recentServices"><Skeleton type="table" :count="5" /></template>
            <template v-else>
              <KTable title="" :columns="serviceColumns" :rows="recentServices" emptyIcon="services" emptyTitle="Belum ada servis" emptyDescription="Belum ada servis hari ini.">
                <template #cell-status="{ row }"><Badge :status="row.status">{{ statusLabel(row.status) }}</Badge></template>
              </KTable>
            </template>
          </div>
          <div class="rounded-xl border overflow-hidden" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
            <div class="p-4 border-b" :style="{ borderColor: 'var(--border-light)' }">
              <h3 class="text-sm font-bold" :style="{ color: 'var(--text-primary)' }">Belum Dialokasi</h3>
            </div>
            <template v-if="!unallocatedServices"><Skeleton type="table" :count="5" /></template>
            <template v-else>
              <KTable title="" :columns="unallocatedColumns" :rows="unallocatedServices" emptyIcon="services" emptyTitle="Semua sudah dialokasi" emptyDescription="Semua servis sudah memiliki teknisi.">
                <template #cell-status="{ row }"><Badge :status="row.status">{{ statusLabel(row.status) }}</Badge></template>
              </KTable>
            </template>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
