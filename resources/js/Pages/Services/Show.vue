<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <Link :href="route('services.index')" class="w-8 h-8 rounded-lg flex items-center justify-center border transition-all"
                        style="border-color: var(--border-color); color: var(--text-muted); background: var(--bg-hover);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </Link>
                    <div>
                        <h2 class="text-xl font-bold" style="color: var(--text-primary);">Servis #{{ service.id }}</h2>
                        <p class="text-xs mt-0.5" style="color: var(--text-muted);">{{ service.customer?.name || '-' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold" :style="statusStyle(service.status)">
                        <span class="w-1.5 h-1.5 rounded-full" :style="{ background: statusDot(service.status) }"></span>
                        {{ statusLabel(service.status) }}
                    </span>
                </div>
            </div>
        </template>

        <div class="max-w-5xl mx-auto space-y-5">
            <!-- ACTION BUTTONS -->
            <div class="flex flex-wrap items-center gap-2 p-4 rounded-xl border" style="border-color: var(--border-color); background: var(--bg-secondary);">
                <template v-if="isActive">
                    <button v-if="canAssign" :disabled="processingAction" @click="openAssignModal" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-white transition-all hover:shadow-sm disabled:opacity-50" style="background: var(--accent-primary);">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        Assign Teknisi
                    </button>
                    <button v-if="canAccept" :disabled="processingAction === 'services.accept'" @click="postAction('services.accept')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-white transition-all hover:shadow-sm disabled:opacity-50" style="background: var(--info);">
                        {{ processingAction === 'services.accept' ? 'Memproses...' : 'Terima Pekerjaan' }}
                    </button>
                    <button v-if="canStart" :disabled="processingAction === 'services.start'" @click="postAction('services.start')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-white transition-all hover:shadow-sm disabled:opacity-50" style="background: var(--info);">
                        {{ processingAction === 'services.start' ? 'Memproses...' : 'Mulai Pekerjaan' }}
                    </button>
                    <button v-if="canFinish" :disabled="processingAction === 'services.finish'" @click="postAction('services.finish')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-white transition-all hover:shadow-sm disabled:opacity-50" style="background: var(--success);">
                        <svg v-if="processingAction !== 'services.finish'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ processingAction === 'services.finish' ? 'Memproses...' : 'Selesaikan Pekerjaan' }}
                    </button>
                    <button v-if="canConfirmCustomer" :disabled="processingAction === 'services.confirm-customer'" @click="postAction('services.confirm-customer')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-white transition-all hover:shadow-sm disabled:opacity-50" style="background: var(--warning);">
                        {{ processingAction === 'services.confirm-customer' ? 'Memproses...' : 'Konfirmasi Pelanggan' }}
                    </button>
                    <button v-if="canConfirmInternal" :disabled="processingAction === 'services.confirm-internal'" @click="postAction('services.confirm-internal')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-white transition-all hover:shadow-sm disabled:opacity-50" style="background: var(--warning);">
                        {{ processingAction === 'services.confirm-internal' ? 'Memproses...' : 'Konfirmasi Internal' }}
                    </button>
                    <button v-if="canApprove" :disabled="processingAction === 'services.approve-confirmation'" @click="postAction('services.approve-confirmation')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-white transition-all hover:shadow-sm disabled:opacity-50" style="background: var(--success);">
                        {{ processingAction === 'services.approve-confirmation' ? 'Memproses...' : 'Setujui Konfirmasi' }}
                    </button>
                    <button v-if="canReallocate" :disabled="processingAction === 'services.request-reallocation'" @click="postAction('services.request-reallocation')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-white transition-all hover:shadow-sm disabled:opacity-50" style="background: var(--danger);">
                        {{ processingAction === 'services.request-reallocation' ? 'Memproses...' : 'Request Alokasi Ulang' }}
                    </button>
                    <button v-if="canPartner" :disabled="processingAction" @click="openPartnerModal" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-white transition-all hover:shadow-sm disabled:opacity-50" style="background: var(--accent-primary);">
                        Kirim ke Partner
                    </button>
                    <button v-if="canCompletePartner" :disabled="processingAction === 'services.complete-partner'" @click="postAction('services.complete-partner')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-white transition-all hover:shadow-sm disabled:opacity-50" style="background: var(--accent-primary);">
                        {{ processingAction === 'services.complete-partner' ? 'Memproses...' : 'Partner Selesai' }}
                    </button>
                    <button v-if="canTakeOver" :disabled="processingAction === 'services.take-over'" @click="postAction('services.take-over')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-white transition-all hover:shadow-sm disabled:opacity-50" style="background: var(--warning);">
                        {{ processingAction === 'services.take-over' ? 'Memproses...' : 'Ambil Alih' }}
                    </button>
                    <button v-if="canCancel" :disabled="processingAction" @click="openCancelModal" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-white transition-all hover:shadow-sm disabled:opacity-50" style="background: var(--danger);">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Batalkan
                    </button>
                    <button v-if="canIndent" :disabled="processingAction === 'services.indent'" @click="postAction('services.indent')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-white transition-all hover:shadow-sm disabled:opacity-50" style="background: var(--accent-primary);">
                        {{ processingAction === 'services.indent' ? 'Memproses...' : 'Indent Sparepart' }}
                    </button>
                    <button v-if="canResumeIndent" :disabled="processingAction === 'services.resume-from-indent'" @click="postAction('services.resume-from-indent')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-white transition-all hover:shadow-sm disabled:opacity-50" style="background: var(--success);">
                        {{ processingAction === 'services.resume-from-indent' ? 'Memproses...' : 'Lanjutkan dari Indent' }}
                    </button>
                    <button v-if="canWork" :disabled="processingAction" @click="openChecklistMasukModal" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all hover:shadow-sm disabled:opacity-50"
                        style="background: var(--bg-hover); color: var(--text-secondary); border: 1px solid var(--border-color);">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        {{ checklistMasuk ? 'Edit Checklist Masuk' : 'Isi Checklist Masuk' }}
                    </button>
                    <button v-if="canWork" :disabled="processingAction" @click="openChecklistKeluarModal" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-white transition-all hover:shadow-sm disabled:opacity-50" style="background: #2563eb;">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        {{ checklistKeluar ? 'Edit Checklist Keluar' : 'Isi Checklist Keluar' }}
                    </button>
                </template>
                <template v-if="service.status === 'selesai'">
                    <Link v-if="!service.sale" :href="route('keuangan.index', { tab: 'penjualan' })"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-white transition-all hover:shadow-sm" style="background: var(--warning);">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Buat Nota
                    </Link>
                    <button v-if="canComplete" @click="openCompleteModal"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-white transition-all hover:shadow-sm" style="background: var(--success);">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Complete Servis
                    </button>
                    <button v-if="canClaimWarranty" @click="executeWarrantyClaim"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-white transition-all hover:shadow-sm" style="background: var(--warning);">
                        Klaim Garansi
                    </button>
                </template>
                <a :href="route('services.print-receipt', service.id)" target="_blank"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all hover:shadow-sm"
                    style="background: var(--bg-hover); color: var(--text-secondary); border: 1px solid var(--border-color);">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak Tanda Terima
                </a>
                <Link v-if="service.parent_service_id" :href="route('services.show', service.parent_service_id)"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all hover:shadow-sm"
                    style="background: var(--bg-hover); color: var(--text-secondary); border: 1px solid var(--border-color);">
                    ← Servis Induk #{{ service.parent_service_id }}
                </Link>
                <Link v-if="service.sale" :href="route('sales.show', service.sale.id)"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-white transition-all hover:shadow-sm" style="background: var(--accent-primary);">
                    Lihat Nota #{{ service.sale.id }}
                </Link>
            </div>

            <!-- STATUS TIMELINE -->
            <div v-if="isActive" class="flex items-center gap-2 px-2 py-3 overflow-x-auto" style="border-bottom: 1px solid var(--border-color);">
                <div v-for="(step, i) in statusTimeline" :key="step.key" class="flex items-center gap-2 whitespace-nowrap">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold shrink-0 transition-all"
                        :style="{
                            background: isStepDone(step.key) ? 'var(--accent-primary)' : 'var(--bg-hover)',
                            color: isStepDone(step.key) ? '#fff' : 'var(--text-muted)',
                            boxShadow: step.key === service.status ? '0 0 0 3px var(--accent-glow)' : 'none',
                        }">{{ i + 1 }}</div>
                    <span class="text-xs font-medium whitespace-nowrap" :style="{
                        color: isStepDone(step.key) ? 'var(--text-primary)' : 'var(--text-muted)',
                        fontWeight: step.key === service.status ? '700' : '500',
                    }">{{ step.label }}</span>
                    <svg v-if="i < statusTimeline.length - 1" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--text-muted);"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
            </div>

            <!-- INFO CARDS: Customer + Device -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="rounded-xl border p-5" style="border-color: var(--border-color); background: var(--bg-secondary);">
                    <h3 class="text-sm font-bold mb-4" style="color: var(--text-primary);">👤 Data Pelanggan</h3>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-bold text-white shadow-sm" style="background: var(--accent-primary);">
                                {{ getInitials(service.customer?.name) }}
                            </div>
                            <div>
                                <p class="text-sm font-bold" style="color: var(--text-primary);">{{ service.customer?.name || '-' }}</p>
                                <p class="text-xs" style="color: var(--text-muted);">{{ service.customer?.phone || '-' }}</p>
                                <p v-if="service.customer?.address" class="text-xs mt-0.5" style="color: var(--text-muted);">{{ service.customer.address }}</p>
                            </div>
                        </div>
                        <div v-if="previousServices?.length" class="pt-2 border-t" style="border-color: var(--border-light);">
                            <p class="text-xs font-semibold mb-2" style="color: var(--text-muted);">Riwayat Servis Sebelumnya:</p>
                            <Link v-for="ps in previousServices" :key="ps.id" :href="route('services.show', ps.id)"
                                class="block text-xs py-1" style="color: var(--accent-primary);">
                                #{{ ps.id }} — {{ formatDate(ps.created_at) }}
                            </Link>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border p-5" style="border-color: var(--border-color); background: var(--bg-secondary);">
                    <h3 class="text-sm font-bold mb-4" style="color: var(--text-primary);">📱 Data Perangkat</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span style="color: var(--text-muted);">Teknisi</span><span class="font-semibold" style="color: var(--text-primary);">{{ service.technician?.name || '-' }}</span></div>
                        <div class="flex justify-between"><span style="color: var(--text-muted);">Tipe</span><span class="font-semibold" style="color: var(--text-primary);">{{ service.tipe_unit || '-' }}</span></div>
                        <div class="flex justify-between"><span style="color: var(--text-muted);">IMEI/SN</span><span class="font-semibold" style="color: var(--text-primary);">{{ service.imei_sn || '-' }}</span></div>
                        <div class="flex justify-between"><span style="color: var(--text-muted);">Sandi/PIN</span><span class="font-semibold" style="color: var(--text-primary);">{{ service.sandi_pola || '-' }}</span></div>
                        <div class="flex justify-between"><span style="color: var(--text-muted);">Kelengkapan</span><span class="font-semibold" style="color: var(--text-primary);">{{ Array.isArray(service.kelengkapan) ? service.kelengkapan.join(', ') : service.kelengkapan || '-' }}</span></div>
                        <div class="flex justify-between"><span style="color: var(--text-muted);">Cabang</span><span class="font-semibold" style="color: var(--text-primary);">{{ service.branch?.name || '-' }}</span></div>
                        <div class="flex justify-between"><span style="color: var(--text-muted);">Dibuat oleh</span><span class="font-semibold" style="color: var(--text-primary);">{{ service.creator?.name || '-' }}</span></div>
                    </div>
                </div>
            </div>

            <!-- PROBLEM + CONDITION -->
            <div class="rounded-xl border p-5" style="border-color: var(--border-color); background: var(--bg-secondary);">
                <h3 class="text-sm font-bold mb-3" style="color: var(--text-primary);">📝 Deskripsi Masalah</h3>
                <p class="text-sm whitespace-pre-wrap" style="color: var(--text-secondary);">{{ service.problem_description || 'Tidak ada deskripsi' }}</p>
                <p v-if="service.condition_note" class="text-sm mt-3 pt-3 border-t whitespace-pre-wrap" style="border-color: var(--border-light); color: var(--text-muted);">{{ service.condition_note }}</p>
            </div>

            <!-- CHECKLIST MASUK -->
            <div v-if="checklistMasuk" class="rounded-xl border p-5" style="border-color: var(--border-color); background: var(--bg-secondary);">
                <h3 class="text-sm font-bold mb-3" style="color: var(--text-primary);">✅ Checklist Masuk</h3>
                <div class="flex flex-wrap gap-2">
                    <span v-for="item in checklistMasuk.checked_items" :key="item"
                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold"
                        style="background: var(--success-bg); color: var(--success-text);">
                        ✓ {{ getChecklistItemName(item) }}
                    </span>
                </div>
                <p v-if="checklistMasuk.notes" class="text-xs mt-2" style="color: var(--text-muted);">Catatan: {{ checklistMasuk.notes }}</p>
            </div>

            <!-- CHECKLIST KELUAR -->
            <div v-if="checklistKeluar" class="rounded-xl border p-5" style="border-color: var(--border-color); background: var(--bg-secondary);">
                <h3 class="text-sm font-bold mb-3" style="color: var(--text-primary);">📋 Checklist Keluar</h3>
                <div class="flex flex-wrap gap-2">
                    <span v-for="item in checklistKeluar.checked_items" :key="item"
                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold"
                        style="background: var(--success-bg); color: var(--success-text);">
                        ✓ {{ getChecklistItemName(item) }}
                    </span>
                </div>
                <p v-if="checklistKeluar.notes" class="text-xs mt-2" style="color: var(--text-muted);">Catatan: {{ checklistKeluar.notes }}</p>
            </div>

            <!-- SPAREPART + COST -->
            <div v-if="service.spareparts?.length" class="rounded-xl border p-5" style="border-color: var(--border-color); background: var(--bg-secondary);">
                <h3 class="text-sm font-bold mb-4" style="color: var(--text-primary);">🔧 Sparepart Terpakai</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b" style="border-color: var(--border-light);">
                                <th class="text-left py-2 px-2 text-xs font-semibold" style="color: var(--text-muted);">Produk</th>
                                <th class="text-right py-2 px-2 text-xs font-semibold" style="color: var(--text-muted);">Qty</th>
                                <th class="text-right py-2 px-2 text-xs font-semibold" style="color: var(--text-muted);">Harga</th>
                                <th class="text-right py-2 px-2 text-xs font-semibold" style="color: var(--text-muted);">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="sp in service.spareparts" :key="sp.id" class="border-b" style="border-color: var(--border-light);">
                                <td class="py-2 px-2" style="color: var(--text-primary);">{{ sp.product?.name || 'Produk dihapus' }}</td>
                                <td class="text-right py-2 px-2" style="color: var(--text-secondary);">{{ sp.quantity }}</td>
                                <td class="text-right py-2 px-2" style="color: var(--text-secondary);">Rp {{ formatNumber(sp.unit_price) }}</td>
                                <td class="text-right py-2 px-2 font-semibold" style="color: var(--text-primary);">Rp {{ formatNumber(sp.subtotal) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- BIAYA -->
            <div v-if="showCost" class="rounded-xl border p-5" style="border-color: var(--border-color); background: var(--bg-secondary);">
                <h3 class="text-sm font-bold mb-4" style="color: var(--text-primary);">💰 Rincian Biaya</h3>
                <div class="space-y-2 text-sm max-w-sm">
                    <div class="flex justify-between">
                        <span style="color: var(--text-muted);">Biaya Jasa</span>
                        <span style="color: var(--text-primary);">Rp {{ formatNumber(service.service_charge) }}</span>
                    </div>
                    <div v-if="service.spareparts?.length" class="flex justify-between">
                        <span style="color: var(--text-muted);">Sparepart</span>
                        <span style="color: var(--text-primary);">Rp {{ formatNumber(sparepartTotal) }}</span>
                    </div>
                    <div class="flex justify-between pt-2 border-t font-bold text-base" style="border-color: var(--border-color);">
                        <span style="color: var(--text-primary);">Total</span>
                        <span style="color: var(--accent-primary);">Rp {{ formatNumber(service.total_cost || service.service_charge + sparepartTotal) }}</span>
                    </div>
                    <div v-if="service.payment_status" class="flex justify-between pt-2">
                        <span style="color: var(--text-muted);">Status Bayar</span>
                        <span class="font-semibold" :style="{ color: service.payment_status === 'paid' ? 'var(--success)' : 'var(--danger)' }">
                            {{ service.payment_status === 'paid' ? 'Lunas' : 'Belum Bayar' }}
                        </span>
                    </div>
                    <div v-if="service.warranty_expired_at" class="flex justify-between">
                        <span style="color: var(--text-muted);">Garansi s.d.</span>
                        <span style="color: var(--text-primary);">{{ formatDate(service.warranty_expired_at) }}</span>
                    </div>
                </div>
            </div>

            <!-- PHOTOS -->
            <div v-if="service.photos?.length" class="rounded-xl border p-5" style="border-color: var(--border-color); background: var(--bg-secondary);">
                <h3 class="text-sm font-bold mb-4" style="color: var(--text-primary);">📸 Foto Perangkat</h3>
                <div class="grid grid-cols-4 gap-3">
                    <div v-for="photo in service.photos" :key="photo.id" class="relative group cursor-pointer" @click="previewPhoto = photo.photo_url">
                        <img :src="photo.photo_url" class="w-full h-24 object-cover rounded-lg border" style="border-color: var(--border-color);" />
                        <div class="absolute inset-0 rounded-lg bg-black/0 group-hover:bg-black/10 transition-all"></div>
                    </div>
                </div>
            </div>

            <!-- UPLOAD PHOTO -->
            <div v-if="driveConnected && isActive" class="rounded-xl border p-5" style="border-color: var(--border-color); background: var(--bg-secondary);">
                <h3 class="text-sm font-bold mb-4" style="color: var(--text-primary);">📤 Upload Foto Tambahan</h3>
                <form @submit.prevent="uploadPhotos">
                    <input type="file" @change="onAdditionalPhotos" accept="image/*" multiple
                        class="block w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                    <div v-if="additionalPreviews.length" class="mt-3 flex flex-wrap gap-2">
                        <div v-for="(preview, idx) in additionalPreviews" :key="idx" class="relative">
                            <img :src="preview" class="h-16 w-16 object-cover rounded-lg border" style="border-color: var(--border-color);" />
                        </div>
                    </div>
                    <button v-if="additionalFiles.length" type="submit" :disabled="processingAction === 'upload_photos'"
                        class="mt-3 px-4 py-2 rounded-lg text-xs font-bold text-white transition-all hover:shadow-sm disabled:opacity-50" style="background: var(--accent-primary);">
                        {{ processingAction === 'upload_photos' ? 'Mengupload...' : 'Upload' }}
                    </button>
                </form>
            </div>

            <!-- WARRANTY CLAIMS -->
            <div v-if="service.warranty_claims?.length" class="rounded-xl border p-5" style="border-color: var(--border-color); background: var(--bg-secondary);">
                <h3 class="text-sm font-bold mb-4" style="color: var(--text-primary);">🛡️ Klaim Garansi</h3>
                <div class="space-y-2">
                    <Link v-for="claim in service.warranty_claims" :key="claim.id" :href="route('services.show', claim.id)"
                        class="flex items-center justify-between px-3 py-2 rounded-lg text-sm transition-all"
                        style="background: var(--bg-hover);">
                        <span style="color: var(--text-primary);">#{{ claim.id }}</span>
                        <span class="text-xs" :style="statusStyle(claim.status)">{{ statusLabel(claim.status) }}</span>
                    </Link>
                </div>
            </div>

            <!-- TIMELINE -->
            <div class="rounded-xl border p-5" style="border-color: var(--border-color); background: var(--bg-secondary);">
                <h3 class="text-sm font-bold mb-4" style="color: var(--text-primary);">📊 Timeline Servis</h3>
                <div class="space-y-4">
                    <div v-for="(evt, idx) in timeline" :key="idx" class="flex gap-3">
                        <div class="flex flex-col items-center">
                            <div class="w-3 h-3 rounded-full flex-shrink-0" :style="{ background: evt.active ? 'var(--accent-primary)' : '#d1d5db' }"></div>
                            <div v-if="idx < timeline.length - 1" class="w-0.5 flex-1" style="background: #d1d5db; min-height: 24px;"></div>
                        </div>
                        <div class="pb-1">
                            <p class="text-sm font-semibold" style="color: var(--text-primary);">{{ evt.label }}</p>
                            <p class="text-xs" style="color: var(--text-muted);">{{ evt.date }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- LIGHTBOX -->
            <Teleport to="body">
                <div v-if="previewPhoto" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click="previewPhoto = null">
                    <img :src="previewPhoto" class="max-h-screen max-w-full object-contain rounded-lg" />
                </div>
            </Teleport>

            <!-- ===== MODAL: ASSIGN TECHNICIAN ===== -->
            <Teleport to="body">
                <div v-if="showAssignModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showAssignModal = false">
                    <div class="rounded-2xl shadow-2xl p-5 w-full max-w-sm mx-3 border" style="background: var(--bg-secondary); border-color: var(--border-color);">
                        <h3 class="text-base font-bold mb-4" style="color: var(--text-primary);">Assign Teknisi</h3>
                        <select v-model="assignTechnicianId"
                            class="w-full rounded-xl border px-3 py-2.5 text-sm mb-4"
                            :style="{ background: 'var(--bg-input)', color: 'var(--text-primary)', borderColor: 'var(--border-color)' }">
                            <option value="" disabled>-- Pilih Teknisi --</option>
                            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }} ({{ u.role }})</option>
                        </select>
                        <div class="flex gap-2">
                            <button @click="showAssignModal = false"
                                class="flex-1 px-4 py-2 rounded-xl border text-sm font-semibold transition-all"
                                style="border-color: var(--border-color); color: var(--text-secondary); background: var(--bg-hover);">Batal</button>
                            <button @click="executeAssign" :disabled="!assignTechnicianId || processingAction === 'assign'"
                                class="flex-1 px-4 py-2 rounded-xl text-sm font-bold text-white transition-all disabled:opacity-50"
                                :class="assignTechnicianId ? 'shadow-sm' : ''"
                                style="background: var(--accent-primary);">{{ processingAction === 'assign' ? 'Menyimpan...' : 'Simpan' }}</button>
                        </div>
                    </div>
                </div>
            </Teleport>

            <!-- ===== MODAL: CANCEL ===== -->
            <Teleport to="body">
                <div v-if="showCancelModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showCancelModal = false">
                    <div class="rounded-2xl shadow-2xl p-5 w-full max-w-sm mx-3 border" style="background: var(--bg-secondary); border-color: var(--border-color);">
                        <h3 class="text-base font-bold mb-2" style="color: var(--text-primary);">Batalkan Servis?</h3>
                        <p class="text-sm mb-4" style="color: var(--text-muted);">Servis #{{ service.id }} akan dibatalkan. Tindakan ini tidak dapat dibatalkan.</p>
                        <div class="flex gap-2">
                            <button @click="showCancelModal = false"
                                class="flex-1 px-4 py-2 rounded-xl border text-sm font-semibold transition-all"
                                style="border-color: var(--border-color); color: var(--text-secondary); background: var(--bg-hover);">Tidak</button>
                            <button @click="executeCancel" :disabled="processingAction === 'cancel'"
                                class="flex-1 px-4 py-2 rounded-xl text-sm font-bold text-white transition-all shadow-sm disabled:opacity-50" style="background: var(--danger);">{{ processingAction === 'cancel' ? 'Memproses...' : 'Ya, Batalkan' }}</button>
                        </div>
                    </div>
                </div>
            </Teleport>

            <!-- ===== MODAL: PARTNER ===== -->
            <Teleport to="body">
                <div v-if="showPartnerModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showPartnerModal = false">
                    <div class="rounded-2xl shadow-2xl p-5 w-full max-w-sm mx-3 border" style="background: var(--bg-secondary); border-color: var(--border-color);">
                        <h3 class="text-base font-bold mb-2" style="color: var(--text-primary);">Kirim ke Partner</h3>
                        <p class="text-xs mb-3" style="color: var(--text-muted);">Servis #{{ service.id }} akan dikerjakan oleh partner eksternal.</p>
                        <textarea v-model="partnerNote" rows="3" placeholder="Catatan untuk partner (opsional)..."
                            class="w-full rounded-xl border px-3 py-2.5 text-sm mb-4 resize-none"
                            :style="{ background: 'var(--bg-input)', color: 'var(--text-primary)', borderColor: 'var(--border-color)' }"></textarea>
                        <div class="flex gap-2">
                            <button @click="showPartnerModal = false"
                                class="flex-1 px-4 py-2 rounded-xl border text-sm font-semibold transition-all"
                                style="border-color: var(--border-color); color: var(--text-secondary); background: var(--bg-hover);">Batal</button>
                            <button @click="executePartner" :disabled="processingAction === 'partner'"
                                class="flex-1 px-4 py-2 rounded-xl text-sm font-bold text-white transition-all shadow-sm disabled:opacity-50" style="background: var(--accent-primary);">{{ processingAction === 'partner' ? 'Mengirim...' : 'Kirim' }}</button>
                        </div>
                    </div>
                </div>
            </Teleport>

            <!-- ===== MODAL: COMPLETE ===== -->
            <Teleport to="body">
                <div v-if="showCompleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm overflow-y-auto py-8" @click.self="showCompleteModal = false">
                    <div class="rounded-2xl shadow-2xl p-5 w-full max-w-lg mx-3 border" style="background: var(--bg-secondary); border-color: var(--border-color);">
                        <h3 class="text-base font-bold mb-4" style="color: var(--text-primary);">✅ Complete Servis</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold mb-1" style="color: var(--text-muted);">Checklist Keluar (opsional)</label>
                                <select v-model="completeForm.checklist_template_id"
                                    class="w-full rounded-xl border px-3 py-2 text-sm"
                                    :style="{ background: 'var(--bg-input)', color: 'var(--text-primary)', borderColor: 'var(--border-color)' }">
                                    <option value="">-- Tanpa Checklist --</option>
                                    <option v-for="tpl in templatesKeluar" :key="tpl.id" :value="tpl.id">{{ tpl.name }}</option>
                                </select>
                                <div v-if="selectedChecklistItems.length" class="mt-2 space-y-1">
                                    <label v-for="item in selectedChecklistItems" :key="item.id"
                                        class="flex items-center gap-2 text-sm cursor-pointer py-0.5"
                                        style="color: var(--text-secondary);">
                                        <input type="checkbox" :value="item.id" v-model="completeForm.checked_items"
                                            class="rounded" style="accent-color: var(--accent-primary);" />
                                        {{ item.item_name }}
                                    </label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-1" style="color: var(--text-muted);">Biaya Jasa</label>
                                <input type="number" v-model.number="completeForm.service_charge" min="0"
                                    class="w-full rounded-xl border px-3 py-2 text-sm"
                                    :style="{ background: 'var(--bg-input)', color: 'var(--text-primary)', borderColor: 'var(--border-color)' }" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-1" style="color: var(--text-muted);">Tambah Sparepart</label>
                                <div class="space-y-2">
                                    <div v-for="(sp, idx) in completeForm.spareparts" :key="idx" class="flex gap-2 items-center">
                                        <select v-model="sp.product_id" class="flex-1 rounded-xl border px-2 py-1.5 text-xs"
                                            :style="{ background: 'var(--bg-input)', color: 'var(--text-primary)', borderColor: 'var(--border-color)' }">
                                            <option value="">-- Pilih --</option>
                                            <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }} (stok: {{ p.stock_quantity }})</option>
                                        </select>
                                        <input type="number" v-model.number="sp.quantity" min="1" placeholder="Qty"
                                            class="w-16 rounded-xl border px-2 py-1.5 text-xs text-center"
                                            :style="{ background: 'var(--bg-input)', color: 'var(--text-primary)', borderColor: 'var(--border-color)' }" />
                                        <button @click="completeForm.spareparts.splice(idx, 1)" class="text-xs" style="color: var(--danger);">✕</button>
                                    </div>
                                    <button @click="completeForm.spareparts.push({ product_id: '', quantity: 1 })"
                                        class="text-xs font-semibold" style="color: var(--accent-primary);">+ Tambah Sparepart</button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-1" style="color: var(--text-muted);">Catatan (opsional)</label>
                                <textarea v-model="completeForm.condition_note" rows="2"
                                    class="w-full rounded-xl border px-3 py-2 text-sm resize-none"
                                    :style="{ background: 'var(--bg-input)', color: 'var(--text-primary)', borderColor: 'var(--border-color)' }"></textarea>
                            </div>
                        </div>
                        <div class="flex gap-2 mt-5">
                            <button @click="showCompleteModal = false"
                                class="flex-1 px-4 py-2 rounded-xl border text-sm font-semibold transition-all"
                                style="border-color: var(--border-color); color: var(--text-secondary); background: var(--bg-hover);">Batal</button>
                            <button @click="executeComplete" :disabled="processingAction === 'complete'"
                                class="flex-1 px-4 py-2 rounded-xl text-sm font-bold text-white transition-all shadow-sm disabled:opacity-50" style="background: var(--success);">{{ processingAction === 'complete' ? 'Memproses...' : 'Simpan & Selesaikan' }}</button>
                        </div>
                    </div>
                </div>
            </Teleport>
            <!-- ===== MODAL: CHECKLIST MASUK ===== -->
            <Teleport to="body">
                <div v-if="showChecklistMasukModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm overflow-y-auto py-8" @click.self="showChecklistMasukModal = false">
                    <div class="rounded-2xl shadow-2xl p-5 w-full max-w-lg mx-3 border" style="background: var(--bg-secondary); border-color: var(--border-color);">
                        <h3 class="text-base font-bold mb-4" style="color: var(--text-primary);">{{ checklistMasuk ? 'Edit Checklist Masuk' : 'Isi Checklist Masuk' }}</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold mb-1" style="color: var(--text-muted);">Template Checklist</label>
                                <select v-model="checklistMasukForm.template_id"
                                    class="w-full rounded-xl border px-3 py-2 text-sm"
                                    :style="{ background: 'var(--bg-input)', color: 'var(--text-primary)', borderColor: 'var(--border-color)' }">
                                    <option value="">-- Pilih Template --</option>
                                    <option v-for="tpl in templatesMasuk" :key="tpl.id" :value="tpl.id">{{ tpl.name }}</option>
                                </select>
                            </div>
                            <div v-if="masukChecklistItems.length" class="space-y-1">
                                <label v-for="item in masukChecklistItems" :key="item.id"
                                    class="flex items-center gap-2 text-sm cursor-pointer py-0.5"
                                    style="color: var(--text-secondary);">
                                    <input type="checkbox" :value="item.id" v-model="checklistMasukForm.checked_items"
                                        class="rounded" style="accent-color: var(--accent-primary);" />
                                    {{ item.item_name }}
                                </label>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-1" style="color: var(--text-muted);">Catatan</label>
                                <textarea v-model="checklistMasukForm.notes" rows="2"
                                    class="w-full rounded-xl border px-3 py-2 text-sm resize-none"
                                    :style="{ background: 'var(--bg-input)', color: 'var(--text-primary)', borderColor: 'var(--border-color)' }"></textarea>
                            </div>
                        </div>
                        <div class="flex gap-2 mt-5">
                            <button @click="showChecklistMasukModal = false"
                                class="flex-1 px-4 py-2 rounded-xl border text-sm font-semibold transition-all"
                                style="border-color: var(--border-color); color: var(--text-secondary); background: var(--bg-hover);">Batal</button>
                            <button @click="executeSaveChecklistMasuk" :disabled="processingAction === 'save_checklist_masuk'"
                                class="flex-1 px-4 py-2 rounded-xl text-sm font-bold text-white transition-all shadow-sm disabled:opacity-50" style="background: var(--accent-primary);">{{ processingAction === 'save_checklist_masuk' ? 'Menyimpan...' : 'Simpan' }}</button>
                        </div>
                    </div>
                </div>
            </Teleport>

            <!-- MODAL CHECKLIST KELUAR -->
            <Teleport to="body">
                <div v-if="showChecklistKeluarModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm overflow-y-auto py-8" @click.self="showChecklistKeluarModal = false">
                    <div class="rounded-2xl shadow-2xl p-5 w-full max-w-lg mx-3 border" style="background: var(--bg-secondary); border-color: var(--border-color);">
                        <h3 class="text-base font-bold mb-4" style="color: var(--text-primary);">{{ checklistKeluar ? 'Edit Checklist Keluar' : 'Isi Checklist Keluar' }}</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold mb-1" style="color: var(--text-muted);">Template Checklist Keluar</label>
                                <select v-model="checklistKeluarForm.template_id"
                                    class="w-full rounded-xl border px-3 py-2 text-sm"
                                    :style="{ background: 'var(--bg-input)', color: 'var(--text-primary)', borderColor: 'var(--border-color)' }">
                                    <option value="">-- Pilih Template --</option>
                                    <option v-for="tpl in templatesKeluar" :key="tpl.id" :value="tpl.id">{{ tpl.name }}</option>
                                </select>
                            </div>
                            <div v-if="keluarChecklistItems.length" class="space-y-1 max-h-60 overflow-y-auto pr-1">
                                <label v-for="item in keluarChecklistItems" :key="item.id"
                                    class="flex items-center gap-2 text-sm cursor-pointer py-1 border-b border-dark-100/30"
                                    style="color: var(--text-secondary);">
                                    <input type="checkbox" :value="item.id" v-model="checklistKeluarForm.checked_items"
                                        class="rounded" style="accent-color: #2563eb;" />
                                    {{ item.item_name }}
                                </label>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-1" style="color: var(--text-muted);">Catatan Keluar</label>
                                <textarea v-model="checklistKeluarForm.notes" rows="2"
                                    class="w-full rounded-xl border px-3 py-2 text-sm resize-none"
                                    :style="{ background: 'var(--bg-input)', color: 'var(--text-primary)', borderColor: 'var(--border-color)' }"></textarea>
                            </div>
                        </div>
                        <div class="flex gap-2 mt-5">
                            <button @click="showChecklistKeluarModal = false"
                                class="flex-1 px-4 py-2 rounded-xl border text-sm font-semibold transition-all"
                                style="border-color: var(--border-color); color: var(--text-secondary); background: var(--bg-hover);">Batal</button>
                            <button @click="executeSaveChecklistKeluar" :disabled="processingAction === 'save_checklist_keluar'"
                                class="flex-1 px-4 py-2 rounded-xl text-sm font-bold text-white transition-all shadow-sm disabled:opacity-50" style="background: #2563eb;">{{ processingAction === 'save_checklist_keluar' ? 'Menyimpan...' : 'Simpan Checklist Keluar' }}</button>
                        </div>
                    </div>
                </div>
            </Teleport>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user || {});

const props = defineProps({
    service: { type: Object, default: () => ({}) },
    templatesKeluar: { type: Array, default: () => [] },
    templatesMasuk: { type: Array, default: () => [] },
    products: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
    previousServices: { type: Array, default: () => [] },
    driveConnected: { type: Boolean, default: false },
});

const previewPhoto = ref(null);
const additionalFiles = ref([]);
const additionalPreviews = ref([]);

// Processing state for action buttons
const processingAction = ref(null);

const statusTimeline = [
    { key: 'menunggu_alokasi', label: 'Masuk' },
    { key: 'diterima', label: 'Diterima' },
    { key: 'dikerjakan', label: 'Dikerjakan' },
    { key: 'menunggu_konfirmasi_pelanggan', label: 'Konfirmasi' },
    { key: 'siap_diambil', label: 'Siap Diambil' },
    { key: 'selesai', label: 'Selesai' },
];

function isStepDone(key) {
    const order = statusTimeline.map(s => s.key);
    const currentIdx = order.indexOf(props.service.status);
    const stepIdx = order.indexOf(key);
    return stepIdx <= currentIdx;
}

// Modals
const showAssignModal = ref(false);
const assignTechnicianId = ref('');
const showCancelModal = ref(false);
const showPartnerModal = ref(false);
const partnerNote = ref('');
const showCompleteModal = ref(false);
const completeForm = ref({
    checklist_template_id: '',
    checked_items: [],
    service_charge: 0,
    spareparts: [],
    condition_note: '',
});
const showChecklistMasukModal = ref(false);
const checklistMasukForm = ref({
    template_id: '',
    checked_items: [],
    notes: '',
});
const showChecklistKeluarModal = ref(false);
const checklistKeluarForm = ref({
    template_id: '',
    checked_items: [],
    notes: '',
});

const isActive = computed(() => !['selesai', 'cancel', 'void', 'close'].includes(props.service.status));
const authUserRole = computed(() => page.props.auth.user?.role || '');
const rolePerms = computed(() => page.props.role_permissions?.[authUserRole.value] || []);
const userRole = computed(() => user.value?.role || '');
const isOwner = computed(() => userRole.value === 'owner');
const isTechnician = computed(() => userRole.value === 'technician');
const canWork = computed(() => rolePerms.value.includes('work_on_services'));
const assignedToMe = computed(() => props.service.technician_id === user.value?.id);

const sparepartTotal = computed(() => {
    return (props.service.spareparts || []).reduce((sum, sp) => sum + Number(sp.subtotal || 0), 0);
});

const showCost = computed(() => {
    return Number(props.service.service_charge) > 0 || (props.service.spareparts?.length || 0) > 0;
});

const checklistMasuk = computed(() => {
    return (props.service.checklists || []).find(c => c.type === 'masuk');
});

const checklistKeluar = computed(() => {
    return (props.service.checklists || []).find(c => c.type === 'keluar');
});

const selectedChecklistItems = computed(() => {
    if (!completeForm.value.checklist_template_id) return [];
    const tpl = props.templatesKeluar.find(t => t.id == completeForm.value.checklist_template_id);
    return tpl?.items || [];
});

const masukChecklistItems = computed(() => {
    if (!checklistMasukForm.value.template_id) return [];
    const tpl = props.templatesMasuk.find(t => t.id == checklistMasukForm.value.template_id);
    return tpl?.items || [];
});

const keluarChecklistItems = computed(() => {
    if (!checklistKeluarForm.value.template_id) return [];
    const tpl = props.templatesKeluar.find(t => t.id == checklistKeluarForm.value.template_id);
    return tpl?.items || [];
});

const canClaimWarranty = computed(() => {
    if (props.service.status !== 'selesai') return false;
    if (props.service.payment_status !== 'paid') return false;
    if (!props.service.warranty_expired_at) return false;
    return new Date(props.service.warranty_expired_at) > new Date();
});

// Action permissions
const canAssign = computed(() => isOwner.value && props.service.status === 'menunggu_alokasi');
const canAccept = computed(() => isTechnician.value && props.service.status === 'menunggu_alokasi');
const canStart = computed(() => (isOwner.value || assignedToMe.value) && props.service.status === 'diterima');
const canFinish = computed(() => (isOwner.value || assignedToMe.value) && props.service.status === 'dikerjakan');
const canConfirmCustomer = computed(() => (isOwner.value || assignedToMe.value) && props.service.status === 'dikerjakan');
const canConfirmInternal = computed(() => (isOwner.value || assignedToMe.value) && props.service.status === 'dikerjakan');
const canApprove = computed(() => isOwner.value && ['menunggu_konfirmasi_pelanggan', 'menunggu_konfirmasi_internal'].includes(props.service.status));
const canReallocate = computed(() => (isOwner.value || assignedToMe.value) && ['diterima', 'dikerjakan'].includes(props.service.status));
const canPartner = computed(() => (isOwner.value || canWork.value) && ['dikerjakan', 'menunggu_alokasi'].includes(props.service.status));
const canCompletePartner = computed(() => (isOwner.value || canWork.value) && props.service.status === 'onpartner');
const canTakeOver = computed(() => !isTechnician.value && isActive.value && props.service.status !== 'onpartner');
const canCancel = computed(() => (isOwner.value || assignedToMe.value) && [
    'menunggu_alokasi', 'diterima', 'diagnosa', 'dikerjakan',
    'menunggu_konfirmasi_pelanggan', 'menunggu_konfirmasi_internal',
    'indent', 'onpartner'
].includes(props.service.status));
const canComplete = computed(() => (isOwner.value || canWork.value) && props.service.status === 'selesai');
const canIndent = computed(() => (isOwner.value || assignedToMe.value) && ['dikerjakan', 'menunggu_alokasi'].includes(props.service.status));
const canResumeIndent = computed(() => (isOwner.value || canWork.value) && props.service.status === 'indent');

// Actions
function postAction(routeName) {
    processingAction.value = routeName;
    router.post(route(routeName, props.service.id), {}, {
        preserveScroll: true,
        onFinish: () => { processingAction.value = null; },
    });
}

function openAssignModal() {
    assignTechnicianId.value = '';
    showAssignModal.value = true;
}

function executeAssign() {
    if (!assignTechnicianId.value) return;
    processingAction.value = 'assign';
    router.post(route('services.assign-technician', props.service.id), {
        technician_id: assignTechnicianId.value,
    }, {
        onSuccess: () => { showAssignModal.value = false; },
        onFinish: () => { processingAction.value = null; },
    });
}

function executeCancel() {
    processingAction.value = 'cancel';
    router.post(route('services.cancel', props.service.id), {}, {
        onSuccess: () => { showCancelModal.value = false; },
        onFinish: () => { processingAction.value = null; },
    });
}

function openPartnerModal() {
    partnerNote.value = '';
    showPartnerModal.value = true;
}

function executePartner() {
    processingAction.value = 'partner';
    router.post(route('services.partner', props.service.id), {
        partner_note: partnerNote.value,
    }, {
        onSuccess: () => { showPartnerModal.value = false; },
        onFinish: () => { processingAction.value = null; },
    });
}

function openCompleteModal() {
    completeForm.value = {
        checklist_template_id: '',
        checked_items: [],
        service_charge: Number(props.service.service_charge) || 0,
        spareparts: [],
        condition_note: '',
    };
    showCompleteModal.value = true;
}

function executeComplete() {
    processingAction.value = 'complete';
    router.post(route('services.complete', props.service.id), completeForm.value, {
        onSuccess: () => { showCompleteModal.value = false; },
        onFinish: () => { processingAction.value = null; },
    });
}

function openChecklistMasukModal() {
    const existing = checklistMasuk.value;
    checklistMasukForm.value = {
        template_id: existing?.checklist_template_id?.toString() || '',
        checked_items: existing?.checked_items ? [...existing.checked_items] : [],
        notes: existing?.notes || '',
    };
    showChecklistMasukModal.value = true;
}

function executeSaveChecklistMasuk() {
    if (!checklistMasukForm.value.template_id) return;
    processingAction.value = 'save_checklist_masuk';
    router.post(route('services.checklists.store', props.service.id), {
        checklist_template_id: checklistMasukForm.value.template_id,
        type: 'masuk',
        checked_items: checklistMasukForm.value.checked_items,
        notes: checklistMasukForm.value.notes,
    }, {
        onSuccess: () => { showChecklistMasukModal.value = false; },
        onFinish: () => { processingAction.value = null; },
    });
}

function openChecklistKeluarModal() {
    const existing = checklistKeluar.value;
    checklistKeluarForm.value = {
        template_id: existing?.checklist_template_id?.toString() || (props.templatesKeluar[0]?.id?.toString() || ''),
        checked_items: existing?.checked_items ? [...existing.checked_items] : [],
        notes: existing?.notes || '',
    };
    showChecklistKeluarModal.value = true;
}

function executeSaveChecklistKeluar() {
    if (!checklistKeluarForm.value.template_id) return;
    processingAction.value = 'save_checklist_keluar';
    router.post(route('services.checklists.store', props.service.id), {
        checklist_template_id: checklistKeluarForm.value.template_id,
        type: 'keluar',
        checked_items: checklistKeluarForm.value.checked_items,
        notes: checklistKeluarForm.value.notes,
    }, {
        onSuccess: () => { showChecklistKeluarModal.value = false; },
        onFinish: () => { processingAction.value = null; },
    });
}

function executeWarrantyClaim() {
    if (!confirm('Buat klaim garansi untuk servis #' + props.service.id + '?')) return;
    router.post(route('services.warranty-claim', props.service.id), {}, {
        preserveScroll: true,
    });
}

// Photos
const onAdditionalPhotos = (e) => {
    additionalFiles.value = Array.from(e.target.files);
    additionalPreviews.value = additionalFiles.value.map(f => URL.createObjectURL(f));
};

const uploadPhotos = () => {
    if (!additionalFiles.value.length) return;
    processingAction.value = 'upload_photos';
    const data = new FormData();
    additionalFiles.value.forEach((file, i) => data.append(`photos[${i}]`, file));
    router.post(route('services.photos.store', props.service.id), data, {
        onSuccess: () => {
            additionalFiles.value = [];
            additionalPreviews.value = [];
        },
        onFinish: () => { processingAction.value = null; },
    });
};

// Helpers
const getInitials = (name) => {
    if (!name) return '?';
    return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
};

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num || 0);

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
    menunggu_alokasi: 'var(--warning)', diterima: 'var(--info)', dikerjakan: 'var(--info)',
    menunggu_konfirmasi_pelanggan: 'var(--danger)', menunggu_konfirmasi_internal: 'var(--danger)',
    siap_diambil: 'var(--success)', indent: 'var(--accent-primary)', onpartner: 'var(--accent-primary)',
    selesai: 'var(--success)', cancel: 'var(--danger)', void: 'var(--danger)', close: 'var(--text-muted)', diambil: 'var(--success)',
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

const getChecklistItemName = (itemId) => {
    const allItems = [
        ...(props.templatesMasuk || []).flatMap(t => t.items || []),
        ...(props.templatesKeluar || []).flatMap(t => t.items || []),
    ];
    const found = allItems.find(i => String(i.id) === String(itemId) || String(i.sort_order) === String(itemId));
    return found?.item_name || itemId;
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const timeline = computed(() => {
    const items = [];
    if (props.service.created_at) {
        items.push({ label: 'Servis Dibuat', date: formatDate(props.service.created_at), active: true });
    }
    if (checklistMasuk.value) {
        items.push({ label: 'Checklist Masuk', date: formatDate(checklistMasuk.value.created_at), active: true });
    }
    items.push({ label: `Status: ${statusLabel(props.service.status)}`, date: formatDate(props.service.updated_at), active: true });
    return items;
});
</script>
