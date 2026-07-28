<template>
    <AuthenticatedLayout>
            <!-- ===== HEADER ===== -->
            <div class="border-b border-gray-200 bg-white -mx-3 sm:-mx-6 lg:-mx-8">
                <div class="px-3 sm:px-6 lg:px-8 py-3">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-900">🔧 Servis</h2>
                        <div class="flex items-center gap-2">
                            <Link :href="route('services.create')" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-bold text-white transition-all hover:shadow-md" style="background: var(--accent-primary);">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                Unit Baru
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== SEARCH & FILTER (PALING ATAS) ===== -->
            <div class="border-b border-gray-200 bg-gray-50/80 -mx-3 sm:-mx-6 lg:-mx-8">
                <div class="px-3 sm:px-6 lg:px-8 py-2.5">
                    <div class="flex flex-wrap items-center gap-2">
                        <select v-model="activeFilter" class="text-xs font-semibold rounded-lg border border-gray-200 px-3 py-1.5 bg-white text-gray-700 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 focus:outline-none">
                            <option v-for="opt in filterOptions" :key="opt.value" :value="opt.value">
                                {{ opt.label }} ({{ countByStatus(opt.value) }})
                            </option>
                        </select>
                        <div class="flex-1 min-w-[160px]">
                            <div class="relative">
                                <input type="text" v-model="filters.customer_name" placeholder="Cari nama pelanggan..." class="w-full rounded-lg border text-xs px-3 py-1.5 pl-7 focus:ring-2 focus:outline-none border-gray-200 text-gray-700 focus:border-blue-500 focus:ring-blue-200 transition-all bg-white" />
                                <svg class="absolute left-2 top-2 w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                        </div>
                        <div class="min-w-[110px]">
                            <input type="text" v-model="filters.phone" placeholder="No. HP..." class="w-full rounded-lg border text-xs px-3 py-1.5 focus:ring-2 focus:outline-none border-gray-200 text-gray-700 focus:border-blue-500 focus:ring-blue-200 transition-all bg-white" />
                        </div>
                        <div class="min-w-[90px]">
                            <input type="text" v-model="filters.sr_code" placeholder="Kode SR..." class="w-full rounded-lg border text-xs px-3 py-1.5 focus:ring-2 focus:outline-none border-gray-200 text-gray-700 focus:border-blue-500 focus:ring-blue-200 transition-all bg-white" />
                        </div>
                        <button @click="clearFilters" class="px-3 py-1.5 rounded-lg text-xs font-semibold border border-gray-200 text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-all bg-white whitespace-nowrap">
                            ↺ Reset
                        </button>
                    </div>
                </div>
            </div>

            <!-- ===== BULK ACTION BAR ===== -->
            <div v-if="selectedIds.length > 0"
                class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm text-white mb-3 shadow-sm"
                :style="{ background: 'var(--accent-primary)' }">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span class="font-semibold">{{ selectedIds.length }} servis dipilih</span>
                <div class="flex items-center gap-2 ml-2">
                    <button @click="bulkUpdateStatus('diterima')" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-white/20 hover:bg-white/30 transition-colors">
                        Terima Semua
                    </button>
                    <button @click="bulkAssignTechnician" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-white/20 hover:bg-white/30 transition-colors">
                        Assign Teknisi
                    </button>
                    <button @click="bulkUpdateStatus('cancel')" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-white/20 hover:bg-white/30 transition-colors">
                        Batalkan Semua
                    </button>
                </div>
                <button @click="selectedIds = []" class="ml-auto text-white/70 hover:text-white transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- ===== TABLE ===== -->
            <div class="-mx-3 sm:-mx-6 lg:-mx-8 pb-6">
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full" style="border-collapse: collapse;">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-2.5 py-2.5 text-[11px] font-bold text-gray-500 uppercase tracking-wider text-center border border-gray-200 w-10">
                                        <input type="checkbox" :checked="allSelected" @change="toggleAll" class="rounded" />
                                    </th>
                                    <th class="px-2.5 py-2.5 text-[11px] font-bold text-gray-500 uppercase tracking-wider text-center border border-gray-200 w-10">No</th>
                                    <th class="px-2.5 py-2.5 text-[11px] font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap border border-gray-200">Masuk</th>
                                    <th class="px-2.5 py-2.5 text-[11px] font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap border border-gray-200">SR</th>
                                    <th class="px-2.5 py-2.5 text-[11px] font-bold text-gray-500 uppercase tracking-wider border border-gray-200">Pelanggan</th>
                                    <th class="px-2.5 py-2.5 text-[11px] font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap border border-gray-200">Unit</th>
                                    <th class="px-2.5 py-2.5 text-[11px] font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap border border-gray-200">Status</th>
                                    <th class="px-2.5 py-2.5 text-[11px] font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap border border-gray-200">Teknisi / CS</th>
                                    <th class="px-2.5 py-2.5 text-[11px] font-bold text-gray-500 uppercase tracking-wider border border-gray-200">Kerusakan / Problem</th>
                                    <th class="px-2.5 py-2.5 text-[11px] font-bold text-gray-500 uppercase tracking-wider border border-gray-200">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="paginatedServices.length === 0">
                                    <td colspan="10" class="px-6 py-14 text-center border border-gray-200">
                                        <div class="flex flex-col items-center gap-3">
                                            <span class="text-4xl opacity-30">🔧</span>
                                            <p class="text-sm text-gray-400">Belum ada data servis</p>
                                            <Link :href="route('services.create')" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-bold text-white transition-all" style="background: var(--accent-primary);">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                                + Unit Baru
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-for="(service, idx) in paginatedServices" :key="service.id"
                                    class="transition-all hover:bg-blue-50/30 cursor-pointer"
                                    @click="router.visit(route('services.show', service.id))">
                                    <td class="px-2.5 py-2 text-center border border-gray-200" @click.stop>
                                        <input type="checkbox" :value="service.id" v-model="selectedIds" class="rounded" />
                                    </td>
                                    <td class="px-2.5 py-2 text-xs text-gray-400 text-center font-mono border border-gray-200">{{ idx + 1 }}</td>
                                    <td class="px-2.5 py-2 text-xs text-gray-500 whitespace-nowrap border border-gray-200">{{ formatDate(service.created_at) }}</td>
                                    <td class="px-2.5 py-2 text-xs font-mono font-bold border border-gray-200" style="color: var(--accent-primary);">SR{{ 1000 + service.id }}</td>
                                    <td class="px-2.5 py-2 border border-gray-200">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-lg flex items-center justify-center text-[10px] font-bold text-white flex-shrink-0" style="background: var(--accent-primary);">
                                                {{ getInitials(service.customer?.name) }}
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-xs font-semibold text-gray-800 leading-tight truncate max-w-[130px]">{{ service.customer?.name || '-' }}</p>
                                                <p v-if="service.customer?.phone" class="text-[10px] text-gray-400">{{ service.customer.phone }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-2.5 py-2 text-xs text-gray-700 font-medium border border-gray-200">{{ service.tipe_unit || '-' }}</td>
                                    <td class="px-2.5 py-2 border border-gray-200">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold whitespace-nowrap"
                                            :style="statusStyle(service.status)">
                                            <span class="w-1.5 h-1.5 rounded-full" :style="{ background: statusDot(service.status) }"></span>
                                            {{ statusLabel(service.status) }}
                                        </span>
                                    </td>
                                    <td class="px-2.5 py-2 text-xs text-gray-500 border border-gray-200">
                                        <div>{{ service.technician?.name || 'Partner' }}</div>
                                        <div class="text-[10px] text-gray-400">CS: {{ service.creator?.name || '-' }}</div>
                                    </td>
                                    <td class="px-2.5 py-2 text-xs text-gray-600 border border-gray-200 max-w-xs truncate" :title="service.problem_description">
                                        {{ service.problem_description || '-' }}
                                    </td>
                                    <td class="px-2.5 py-2 border border-gray-200">
                                        <div class="flex items-center gap-1">
                                            <!-- Detail -->
                                            <button @click.stop="router.visit(route('services.show', service.id))" 
                                                class="w-6 h-6 rounded flex items-center justify-center text-xs text-white transition-all hover:scale-110" 
                                                style="background: var(--success);" title="Detail">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </button>

                                            <!-- Print Receipt -->
                                            <a :href="route('services.print-receipt', service.id)" target="_blank" @click.stop
                                                class="w-6 h-6 rounded flex items-center justify-center text-xs text-white transition-all hover:scale-110" 
                                                style="background: var(--info);" title="Cetak Tanda Terima">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                            </a>

                                            <!-- Checklist Masuk (for initial statuses that might have missed checklist) -->
                                            <button v-if="canChecklistMasuk(service.status)" @click.stop="openChecklistMasuk(service)"
                                                class="w-6 h-6 rounded flex items-center justify-center text-xs text-white transition-all hover:scale-110" 
                                                style="background: var(--success);" title="Checklist Masuk">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                            </button>

                                            <!-- Alokasi Teknisi (for menunggu_alokasi) -->
                                            <button v-if="canAlokasi(service.status)" @click.stop="openAlokasiModal(service)"
                                                class="w-6 h-6 rounded flex items-center justify-center text-xs text-white transition-all hover:scale-110" 
                                                style="background: var(--accent-primary);" title="Alokasi Teknisi">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                            </button>

                                            <!-- Checklist Keluar (for dikerjakan) -->
                                            <button v-if="canChecklistKeluar(service.status)" @click.stop="router.visit(route('services.show', service.id))"
                                                class="w-6 h-6 rounded flex items-center justify-center text-xs text-white transition-all hover:scale-110" 
                                                style="background: var(--success);" title="Checklist Keluar">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </button>

                                            <!-- Selesai (for dikerjakan) -->
                                            <button v-if="canSelesai(service.status)" @click.stop="confirmSelesai(service)"
                                                class="w-6 h-6 rounded flex items-center justify-center text-xs text-white transition-all hover:scale-110" 
                                                style="background: #2ecc71;" title="Selesaikan">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            </button>

                                            <!-- Buat Nota (for siap_diambil/selesai) -->
                                            <button v-if="canBuatNota(service.status)" @click.stop="router.visit(route('keuangan.index', { tab: 'penjualan' }))"
                                                class="w-6 h-6 rounded flex items-center justify-center text-xs text-white transition-all hover:scale-110" 
                                                style="background: var(--warning);" title="Buat Nota">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            </button>

                                            <!-- Cancel -->
                                            <button v-if="canCancel(service.status)" @click.stop="confirmCancel(service)"
                                                class="w-6 h-6 rounded flex items-center justify-center text-xs text-white transition-all hover:scale-110" 
                                                style="background: var(--danger);" title="Batalkan">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <Pagination :meta="services" :per-page="services.per_page" />
                </div>
            </div>

        <!-- ===== CANCEL CONFIRMATION MODAL ===== -->
        <Teleport to="body">
            <div v-if="showCancelModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showCancelModal = false">
                <div class="bg-white rounded-2xl shadow-2xl p-5 w-full max-w-sm mx-3 border border-gray-200">
                    <div class="text-center">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center mx-auto mb-3 bg-red-50">
                            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-gray-900 mb-1">Batalkan Servis?</h3>
                        <p class="text-xs text-gray-400 mb-1">Servis #{{ cancelTarget?.id }} — {{ cancelTarget?.customer?.name }}</p>
                        <p class="text-xs text-gray-400 mb-4">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                    <div class="flex gap-2">
                        <button @click="showCancelModal = false" class="flex-1 px-4 py-2 rounded-lg border text-sm font-semibold text-gray-600 border-gray-200 hover:bg-gray-50 transition-all">
                            Batal
                        </button>
                        <button @click="executeCancel" :disabled="processingAction === 'cancel'" class="flex-1 px-4 py-2 rounded-lg text-sm font-bold text-white transition-all hover:shadow-md disabled:opacity-50 bg-red-500">
                            {{ processingAction === 'cancel' ? 'Memproses...' : 'Ya, Batalkan' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- ===== ALOKASI TEKNISI MODAL ===== -->
        <Teleport to="body">
            <div v-if="showAlokasiModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showAlokasiModal = false">
                <div class="bg-white rounded-2xl shadow-2xl p-5 w-full max-w-sm mx-3 border border-gray-200">
                    <div class="text-center mb-4">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center mx-auto mb-3 bg-purple-50">
                            <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        </div>
                        <h3 class="text-base font-bold text-gray-900">Alokasi Teknisi</h3>
                        <p class="text-xs text-gray-400 mt-1">Servis #{{ alokasiTarget?.id }} — {{ alokasiTarget?.customer?.name }}</p>
                    </div>
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Pilih Teknisi</label>
                        <select v-model="selectedTechnicianId" class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2.5 bg-white text-gray-700 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 focus:outline-none">
                            <option value="" disabled>-- Pilih Teknisi --</option>
                            <option v-for="user in userList" :key="user.id" :value="user.id">{{ user.name }}</option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button @click="showAlokasiModal = false" class="flex-1 px-4 py-2 rounded-lg border text-sm font-semibold text-gray-600 border-gray-200 hover:bg-gray-50 transition-all">
                            Batal
                        </button>
                        <button @click="executeAlokasi" :disabled="!selectedTechnicianId || processingAction === 'alokasi'" class="flex-1 px-4 py-2 rounded-lg text-sm font-bold text-white transition-all hover:shadow-md disabled:opacity-50" :class="selectedTechnicianId ? 'bg-purple-600' : 'bg-gray-300'">
                            {{ processingAction === 'alokasi' ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- ===== SELESAI CONFIRMATION MODAL ===== -->
        <Teleport to="body">
            <div v-if="showSelesaiModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showSelesaiModal = false">
                <div class="bg-white rounded-2xl shadow-2xl p-5 w-full max-w-sm mx-3 border border-gray-200">
                    <div class="text-center">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center mx-auto mb-3 bg-green-50">
                            <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <h3 class="text-base font-bold text-gray-900 mb-1">Selesaikan Servis?</h3>
                        <p class="text-xs text-gray-400 mb-1">Servis #{{ selesaiTarget?.id }} — {{ selesaiTarget?.customer?.name }}</p>
                                        <p class="text-xs text-gray-400 mb-4">Status akan berubah menjadi "Selesai".</p>
                    </div>
                    <div class="flex gap-2">
                        <button @click="showSelesaiModal = false" class="flex-1 px-4 py-2 rounded-lg border text-sm font-semibold text-gray-600 border-gray-200 hover:bg-gray-50 transition-all">
                            Batal
                        </button>
                        <button @click="executeSelesai" :disabled="processingAction === 'selesai'" class="flex-1 px-4 py-2 rounded-lg text-sm font-bold text-white transition-all hover:shadow-md disabled:opacity-50 bg-green-500">
                            {{ processingAction === 'selesai' ? 'Memproses...' : 'Ya, Selesaikan' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const page = usePage();

const props = defineProps({
    services: { type: Object, default: () => ({ data: [], links: [], from: 0, to: 0, total: 0 }) },
    stats: { type: Object, default: () => ({ all: 0 }) },
});

const activeFilter = ref('all');
const filters = ref({ customer_name: '', phone: '', sr_code: '' });

// Bulk actions
const selectedIds = ref([]);
const allSelected = computed({
  get: () => selectedIds.value.length === props.services.data.length && props.services.data.length > 0,
  set: (val) => { selectedIds.value = val ? props.services.data.map(s => s.id) : []; }
});
function toggleAll() { allSelected.value = !allSelected.value; }
function bulkUpdateStatus(status) {
  if (!selectedIds.value.length) return;
  router.post(route('services.bulk-status'), { ids: selectedIds.value, status }, {
    preserveScroll: true, onSuccess: () => { selectedIds.value = []; }
  });
}
function bulkAssignTechnician() {
  if (!selectedIds.value.length) return;
  bulkUpdateStatus('diterima');
}

// Cancel modal
const processingAction = ref(null);
const showCancelModal = ref(false);
const cancelTarget = ref(null);

// Alokasi modal
const showAlokasiModal = ref(false);
const alokasiTarget = ref(null);
const selectedTechnicianId = ref('');
const userList = ref([]);

// Selesai modal
const showSelesaiModal = ref(false);
const selesaiTarget = ref(null);

const filterOptions = [
    { value: 'all', label: 'All' },
    { value: 'menunggu_alokasi', label: 'Pending' },
    { value: 'dikerjakan', label: 'On Progress' },
    { value: 'indent', label: 'Waiting Parts' },
    { value: 'onpartner', label: 'Partner' },
    { value: 'siap_diambil', label: 'Siap Diambil' },
    { value: 'selesai', label: 'Finish' },
    { value: 'cancel', label: 'Cancel' },
];

const allServices = computed(() => props.services.data || []);

const paginatedServices = computed(() => props.services.data || []);

watch(activeFilter, (status) => {
    router.get(route('services.index'), { status: status === 'all' ? '' : status }, {
        preserveState: true,
        preserveScroll: true,
    });
});

const getInitials = (name) => {
    if (!name) return '?';
    return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
};

const statusLabel = (status) => ({
    menunggu_alokasi: 'Pending',
    diterima: 'Diterima',
    dikerjakan: 'On Progress',
    menunggu_konfirmasi_pelanggan: 'Konfirmasi',
    menunggu_konfirmasi_internal: 'Konfirmasi Internal',
    siap_diambil: 'Siap Diambil',
    indent: 'Waiting Parts',
    onpartner: 'Partner',
    selesai: 'Finish',
    cancel: 'Cancel',
    void: 'Void',
    close: 'Close',
    diambil: 'Taken',
}[status] || status);

const statusDot = (status) => ({
    menunggu_alokasi: 'var(--warning)',
    diterima: 'var(--info)',
    dikerjakan: 'var(--info)',
    menunggu_konfirmasi_pelanggan: 'var(--danger)',
    menunggu_konfirmasi_internal: 'var(--danger)',
    siap_diambil: 'var(--success)',
    indent: 'var(--accent-primary)',
    onpartner: 'var(--accent-primary)',
    selesai: 'var(--success)',
    cancel: 'var(--danger)',
    void: 'var(--danger)',
    diambil: 'var(--success)',
}[status] || '#8e8ea0');

const statusStyle = (status) => {
    const colors = {
        menunggu_alokasi: { bg: 'rgba(243,156,18,0.12)', color: '#b87c0e' },
        diterima: { bg: 'var(--info-bg)', color: 'var(--info-text)' },
        dikerjakan: { bg: 'var(--info-bg)', color: 'var(--info-text)' },
        menunggu_konfirmasi_pelanggan: { bg: 'var(--danger-bg)', color: 'var(--danger-text)' },
        menunggu_konfirmasi_internal: { bg: 'var(--danger-bg)', color: 'var(--danger-text)' },
        indent: { bg: 'var(--accent-light)', color: 'var(--accent-primary)' },
        onpartner: { bg: 'var(--accent-light)', color: 'var(--accent-primary)' },
        selesai: { bg: 'var(--success-bg)', color: 'var(--success-text)' },
        cancel: { bg: 'var(--danger-bg)', color: 'var(--danger-text)' },
        void: { bg: 'var(--danger-bg)', color: 'var(--danger-text)' },
        diambil: { bg: 'var(--success-bg)', color: 'var(--success-text)' },
    };
    const c = colors[status] || { bg: 'rgba(142,142,160,0.12)', color: '#71717a' };
    return `background: ${c.bg}; color: ${c.color};`;
};

const countByStatus = (key) => {
    if (key === 'all') {
        return allServices.value.length;
    }
    if (key === 'dikerjakan') {
        return allServices.value.filter(s => s.status === 'diterima' || s.status === 'dikerjakan').length;
    }
    if (key === 'cancel') {
        return allServices.value.filter(s => ['cancel', 'void', 'close'].includes(s.status)).length;
    }
    return allServices.value.filter(s => s.status === key).length;
};

const clearFilters = () => {
    filters.value = { customer_name: '', phone: '', sr_code: '' };
    activeFilter.value = 'all';
};

// ===== ACTION BUTTON HELPERS =====

const canChecklistMasuk = (status) => {
    return ['menunggu_alokasi', 'diterima'].includes(status);
};

const canAlokasi = (status) => {
    return status === 'menunggu_alokasi';
};

const canChecklistKeluar = (status) => {
    return status === 'dikerjakan';
};

const canSelesai = (status) => {
    return status === 'dikerjakan';
};

const canBuatNota = (status) => {
    return status === 'selesai';
};

const canCancel = (status) => {
    return ['menunggu_alokasi', 'diterima', 'dikerjakan'].includes(status);
};

// ===== CANCEL =====

const confirmCancel = (service) => {
    cancelTarget.value = service;
    showCancelModal.value = true;
};

const executeCancel = () => {
    if (!cancelTarget.value) return;
    processingAction.value = 'cancel';
    router.post(route('services.cancel', cancelTarget.value.id), {}, {
        onSuccess: () => { showCancelModal.value = false; cancelTarget.value = null; },
        onFinish: () => { processingAction.value = null; },
    });
};

// ===== ALOKASI TEKNISI =====

const fetchUsers = async () => {
    try {
        const response = await fetch('/users');
        const data = await response.json();
        userList.value = (data.data || data || []).filter(u => u.active !== false);
    } catch (e) {
        console.error('Gagal memuat data user:', e);
        userList.value = [];
    }
};

const openAlokasiModal = async (service) => {
    alokasiTarget.value = service;
    selectedTechnicianId.value = '';
    if (userList.value.length === 0) {
        await fetchUsers();
    }
    showAlokasiModal.value = true;
};

const executeAlokasi = () => {
    if (!alokasiTarget.value || !selectedTechnicianId.value) return;
    processingAction.value = 'alokasi';
    router.post(route('services.assign-technician', alokasiTarget.value.id), {
        technician_id: selectedTechnicianId.value,
    }, {
        onSuccess: () => { showAlokasiModal.value = false; alokasiTarget.value = null; selectedTechnicianId.value = ''; },
        onFinish: () => { processingAction.value = null; },
    });
};

// ===== SELESAI =====

const confirmSelesai = (service) => {
    selesaiTarget.value = service;
    showSelesaiModal.value = true;
};

const executeSelesai = () => {
    if (!selesaiTarget.value) return;
    processingAction.value = 'selesai';
    router.post(route('services.finish', selesaiTarget.value.id), {}, {
        onSuccess: () => { showSelesaiModal.value = false; selesaiTarget.value = null; },
        onFinish: () => { processingAction.value = null; },
    });
};

// ===== CHECKLIST MASUK =====

const openChecklistMasuk = (service) => {
    router.visit(route('services.show', service.id));
};
</script>