<template>
    <AuthenticatedLayout>
        <div class="max-w-6xl mx-auto flex flex-col gap-6">
            <!-- HEADER -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-2">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight" :style="{ color: 'var(--text-primary)' }">Tiket Servis Baru</h2>
                    <p class="text-sm mt-1" :style="{ color: 'var(--text-muted)' }">Lengkapi informasi pelanggan dan detail perangkat yang akan diservis.</p>
                </div>
                <Link :href="route('services.index')" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold rounded-xl transition-colors shadow-sm" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)', color: 'var(--text-primary)' }">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Batal & Kembali
                </Link>
            </div>

            <!-- VALIDATION ALERTS -->
            <div v-if="!canSubmitCore" class="rounded-2xl p-5 flex items-start gap-4 shadow-sm" :style="{ background: 'var(--color-warning-soft)', borderColor: 'var(--color-warning-soft)' }">
                <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0" :style="{ background: 'var(--color-warning-soft)' }">
                    <svg class="w-5 h-5" :style="{ color: 'var(--color-warning-text)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold" :style="{ color: 'var(--color-warning-text)' }">Wajib Diisi</h3>
                    <p class="text-sm mt-1 leading-relaxed" :style="{ color: 'var(--color-warning-text)' }">Anda harus mengisi <strong>Pelanggan</strong>, <strong>Tipe Unit</strong>, dan <strong>Keluhan</strong> sebelum dapat menyimpan tiket ini.</p>
                </div>
            </div>

            <form @submit.prevent="submit" @keydown.ctrl.enter.prevent="handleShortcutSubmit" @keydown.meta.enter.prevent="handleShortcutSubmit">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- LEFT COLUMN (MAIN) -->
                    <div class="lg:col-span-2 space-y-6">
                        
                        <!-- 1. DATA PELANGGAN -->
                        <div class="rounded-2xl shadow-sm overflow-hidden" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
                            <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4" :style="{ background: 'var(--bg-hover)', borderBottom: '1px solid var(--border-color)' }">
                                <h3 class="font-bold tracking-tight text-base flex items-center gap-3" :style="{ color: 'var(--text-primary)' }">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold" :style="{ background: 'var(--color-primary-soft)', color: 'var(--color-primary-text)' }">1</div>
                                    Informasi Pelanggan
                                </h3>
                                <KButton  type="button" @click="openAddCustomerModal" class="inline-flex items-center text-sm font-bold px-3 py-1.5 rounded-lg transition-colors" :style="{ color: 'var(--color-primary-text)', background: 'var(--color-primary-soft)' }">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Pelanggan Baru
                                </KButton>
                            </div>
                            <div class="p-6">
                                <div class="space-y-2">
                                    <label for="customer_id" class="block text-xs font-bold uppercase tracking-wider mb-2" :style="{ color: 'var(--text-muted)' }">Cari / Pilih Pelanggan <span class="text-red-500">*</span></label>
                                    <KSelect  ref="customerSelectEl" v-model="form.customer_id" id="customer_id" class="w-full rounded-xl text-sm text-sm px-4 py-3 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500  transition-all" :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-card)', color: 'var(--text-primary)' }" required>
                                        <option value="">-- Pilih Nama Pelanggan --</option>
                                        <option v-for="c in customerOptions" :key="c.id" :value="c.id">{{ c.name }} {{ c.phone ? ' (' + c.phone + ')' : '' }}</option>
                                    </KSelect>
                                    <p v-if="form.errors.customer_id" class="text-xs text-red-500 font-medium">{{ form.errors.customer_id }}</p>
                                </div>
                                <div v-if="selectedCustomer" class="rounded-xl p-4 text-sm mt-4 animate-in slide-in-from-top-2" :style="{ background: 'var(--bg-hover)', borderColor: 'var(--border-color)' }">
                                    <p class="font-bold text-base" :style="{ color: 'var(--text-primary)' }">{{ selectedCustomer.name }}</p>
                                    <div class="flex flex-col sm:flex-row sm:gap-6 mt-2 font-medium" :style="{ color: 'var(--text-muted)' }">
                                        <p class="flex items-center gap-1.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg> {{ selectedCustomer.phone || '-' }}</p>
                                        <p class="flex items-center gap-1.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg> {{ selectedCustomer.address || '-' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. DATA PERANGKAT -->
                        <div class="rounded-2xl shadow-sm overflow-hidden" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
                            <div class="px-6 py-4" :style="{ background: 'var(--bg-hover)', borderBottom: '1px solid var(--border-color)' }">
                                <h3 class="font-bold tracking-tight text-base flex items-center gap-3" :style="{ color: 'var(--text-primary)' }">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold" :style="{ background: 'var(--color-primary-soft)', color: 'var(--color-primary-text)' }">2</div>
                                    Data Perangkat
                                </h3>
                            </div>
                            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider mb-2" :style="{ color: 'var(--text-muted)' }">Kategori Perangkat</label>
                                    <KSelect  v-model="form.kategori_perangkat_id" class="w-full rounded-xl text-sm text-sm px-4 py-2.5 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500  transition-all" :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-card)', color: 'var(--text-primary)' }">
                                        <option value="">Pilih Kategori</option>
                                        <option v-for="dc in deviceCategories" :key="dc.id" :value="dc.id">{{ dc.name }}</option>
                                    </KSelect>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider mb-2" :style="{ color: 'var(--text-muted)' }">Merek (Brand)</label>
                                    <KSelect  v-model="form.merek_id" class="w-full rounded-xl text-sm text-sm px-4 py-2.5 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500  transition-all" :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-card)', color: 'var(--text-primary)' }">
                                        <option value="">Pilih Merek</option>
                                        <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
                                    </KSelect>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold uppercase tracking-wider mb-2" :style="{ color: 'var(--text-muted)' }">Tipe / Model <span class="text-red-500">*</span></label>
                                    <KInput  ref="tipeUnitInputEl" type="text" v-model="form.tipe_unit" placeholder="Contoh: iPhone 13 Pro 256GB" class="w-full rounded-xl text-sm text-sm px-4 py-2.5 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500  transition-all" :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-card)', color: 'var(--text-primary)' }" required />
                                    <p v-if="form.errors.tipe_unit" class="text-xs font-medium mt-1" :style="{ color: 'var(--color-danger-text)' }">{{ form.errors.tipe_unit }}</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider mb-2" :style="{ color: 'var(--text-muted)' }">IMEI / Serial Number</label>
                                    <div class="relative">
                                      <KInput  type="text" v-model="form.imei_sn" placeholder="Nomor IMEI / SN" class="w-full rounded-xl text-sm text-sm px-4 py-2.5 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500  transition-all" :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-card)', color: 'var(--text-primary)' }" />
                                      <span v-if="isLookingUpIMEI" class="absolute right-3 top-1/2 -translate-y-1/2 animate-spin text-indigo-500 text-xs">⏳</span>
                                      <span v-else-if="imeiLookupResult?.customer" class="absolute right-3 top-1/2 -translate-y-1/2 text-green-500 text-xs font-bold">✅ Ditemukan</span>
                                    </div>
                                </div>
                                <!-- IMEI Lookup: Blacklist Warning -->
                                <div v-if="intake.isBlacklisted()" class="md:col-span-2 rounded-xl p-4 text-sm" :style="{ background: 'var(--color-danger-soft)', borderColor: 'var(--color-danger-soft)' }">
                                  <p class="font-bold flex items-center gap-2" :style="{ color: 'var(--color-danger-text)' }">🚫 Customer Blacklist</p>
                                  <p class="mt-1" :style="{ color: 'var(--color-danger-text)' }">Customer ini terdaftar dalam blacklist. Intake tidak dapat dilanjutkan.</p>
                                </div>
                                <!-- IMEI Lookup: Device History -->
                                <div v-if="showDeviceHistory && imeiLookupResult" class="md:col-span-2 rounded-xl p-4 text-sm space-y-3 animate-in slide-in-from-top-2" :style="{ background: 'var(--color-primary-soft)', borderColor: 'var(--color-primary-soft)' }">
                                  <p class="font-bold flex items-center gap-2" :style="{ color: 'var(--color-primary-text)' }">📱 Device Ditemukan — Riwayat</p>
                                  <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                    <div class="rounded-lg p-2 text-center" :style="{ background: 'var(--bg-card)' }">
                                      <p class="text-lg font-bold" :style="{ color: 'var(--color-primary-text)' }">{{ intake.deviceHistory.services?.length || 0 }}</p>
                                      <p class="text-[10px]" :style="{ color: 'var(--text-muted)' }">Service</p>
                                    </div>
                                    <div class="rounded-lg p-2 text-center" :style="{ background: 'var(--bg-card)' }">
                                      <p class="text-lg font-bold" :style="{ color: intake.isUnderWarranty() ? '#16a34a' : '#dc2626' }">{{ intake.isUnderWarranty() ? '✅ Aktif' : '❌ Habis' }}</p>
                                      <p class="text-[10px]" :style="{ color: 'var(--text-muted)' }">Garansi</p>
                                    </div>
                                    <div class="rounded-lg p-2 text-center" :style="{ background: 'var(--bg-card)' }">
                                      <p class="text-lg font-bold" :style="{ color: 'var(--color-primary-text)' }">{{ intake.deviceHistory.damages?.length || 0 }}</p>
                                      <p class="text-[10px]" :style="{ color: 'var(--text-muted)' }">Kerusakan</p>
                                    </div>
                                    <div class="rounded-lg p-2 text-center" :style="{ background: 'var(--bg-card)' }">
                                      <p class="text-lg font-bold" :style="{ color: 'var(--color-primary-text)' }">{{ intake.deviceHistory.parts?.length || 0 }}</p>
                                      <p class="text-[10px]" :style="{ color: 'var(--text-muted)' }">Sparepart</p>
                                    </div>
                                  </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider mb-2" :style="{ color: 'var(--text-muted)' }">Sandi / Pola / PIN Layar</label>
                                    <KInput  type="text" v-model="form.sandi_pola" placeholder="Contoh: 123456 atau huruf L" class="w-full rounded-xl text-sm text-sm px-4 py-2.5 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500  transition-all" :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-card)', color: 'var(--text-primary)' }" />
                                </div>
                            </div>
                        </div>

                        <!-- 3. DESKRIPSI MASALAH -->
                        <div class="rounded-2xl shadow-sm overflow-hidden" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
                            <div class="px-6 py-4" :style="{ background: 'var(--bg-hover)', borderBottom: '1px solid var(--border-color)' }">
                                <h3 class="font-bold tracking-tight text-base flex items-center gap-3" :style="{ color: 'var(--text-primary)' }">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold" :style="{ background: 'var(--color-primary-soft)', color: 'var(--color-primary-text)' }">3</div>
                                    Keluhan & Kondisi Fisik
                                </h3>
                            </div>
                            <div class="p-6 space-y-5">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider mb-2" :style="{ color: 'var(--text-muted)' }">Deskripsi Keluhan / Masalah <span class="text-red-500">*</span></label>
                                    <KTextarea  ref="problemDescriptionEl" v-model="form.problem_description" rows="3" class="w-full rounded-xl text-sm text-sm px-4 py-3 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500  transition-all resize-none" :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-card)', color: 'var(--text-primary)' }" placeholder="Jelaskan secara detail keluhan pelanggan..." required></KTextarea>
                                    <p v-if="form.errors.problem_description" class="text-xs font-medium mt-1" :style="{ color: 'var(--color-danger-text)' }">{{ form.errors.problem_description }}</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider mb-2" :style="{ color: 'var(--text-muted)' }">Catatan Kondisi Fisik (Opsional)</label>
                                    <KTextarea  v-model="form.condition_note" rows="2" class="w-full rounded-xl text-sm text-sm px-4 py-3 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500  transition-all resize-none" :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-card)', color: 'var(--text-primary)' }" placeholder="Layar retak rambut, casing lecet kiri atas..."></KTextarea>
                                </div>
                            </div>
                        </div>

                        <!-- CUSTOM FIELDS -->
                        <div v-if="customFields.length > 0" class="rounded-2xl shadow-sm overflow-hidden" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
                            <div class="px-6 py-4" :style="{ background: 'var(--bg-hover)', borderBottom: '1px solid var(--border-color)' }">
                                <h3 class="font-bold tracking-tight text-base" :style="{ color: 'var(--text-primary)' }">Informasi Tambahan</h3>
                            </div>
                            <div class="p-6">
                                <DynamicFormFields :fields="customFields" :form-data="form" />
                            </div>
                        </div>
                    </div>
                    
                    <!-- RIGHT COLUMN (SIDEBAR) -->
                    <div class="space-y-6">
                        
                        <!-- KELENGKAPAN -->
                        <div class="rounded-2xl shadow-sm overflow-hidden" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
                            <div class="px-6 py-4" :style="{ background: 'var(--bg-hover)', borderBottom: '1px solid var(--border-color)' }">
                                <h3 class="font-bold tracking-tight text-base" :style="{ color: 'var(--text-primary)' }">Kelengkapan Masuk</h3>
                            </div>
                            <div class="p-6 space-y-5">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider mb-2" :style="{ color: 'var(--text-muted)' }">Jalur Kedatangan</label>
                                    <KSelect  v-model="form.jalur_kedatangan_id" class="w-full rounded-xl text-sm text-sm px-4 py-2.5 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500  transition-all" :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-card)', color: 'var(--text-primary)' }">
                                        <option value="">Walk-in (Datang Langsung)</option>
                                        <option v-for="am in arrivalMethods" :key="am.id" :value="am.id">{{ am.name }}</option>
                                    </KSelect>
                                </div>
                                <div class="pt-5" :style="{ borderTop: '1px solid var(--border-light)' }">
                                    <label class="block text-xs font-bold uppercase tracking-wider mb-3" :style="{ color: 'var(--text-muted)' }">Kelengkapan Bawaan</label>
                                    <div class="flex flex-col gap-1.5">
                                        <label v-for="eq in equipment" :key="eq.id" class="flex items-center gap-3 text-sm cursor-pointer p-2 rounded-lg transition-colors -mx-2" :style="{}" >
                                            <KCheckbox  :value="eq.name" v-model="form.kelengkapan" class="w-4 h-4 rounded" :style="{ borderColor: 'var(--border-color)' }" />
                                            <span class="font-bold" :style="{ color: 'var(--text-primary)' }">{{ eq.name }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CHECKLIST -->
                        <div class="rounded-2xl shadow-sm overflow-hidden" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
                            <div class="px-6 py-4" :style="{ background: 'var(--bg-hover)', borderBottom: '1px solid var(--border-color)' }">
                                <h3 class="font-bold tracking-tight text-base" :style="{ color: 'var(--text-primary)' }">Ceklis Awal (Pre-repair)</h3>
                            </div>
                            <div class="p-6 space-y-5">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider mb-2" :style="{ color: 'var(--text-muted)' }">Gunakan Template</label>
                                    <KSelect  v-model="form.checklist_template_id" class="w-full rounded-xl text-sm text-sm px-4 py-2.5 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500  transition-all" :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-card)', color: 'var(--text-primary)' }">
                                        <option value="">Tidak Menggunakan Template</option>
                                        <option v-for="t in templates" :key="t.id" :value="t.id">{{ t.name }}</option>
                                    </KSelect>
                                </div>
                                <div v-if="selectedTemplate" class="pt-5" :style="{ borderTop: '1px solid var(--border-light)' }">
                                    <label class="block text-xs font-bold uppercase tracking-wider mb-3" :style="{ color: 'var(--text-muted)' }">Kondisi Komponen (Centang jika OK)</label>
                                    <div class="flex flex-col gap-1.5 max-h-48 overflow-y-auto pr-2 custom-scrollbar">
                                        <label v-for="item in selectedTemplate.items" :key="item.id" class="flex items-center gap-3 text-sm cursor-pointer p-2 rounded-lg transition-colors -mx-2" :style="{}" >
                                            <KCheckbox  :value="item.item_name" v-model="form.checked_items" class="w-4 h-4 rounded" :style="{ borderColor: 'var(--border-color)' }" />
                                            <span class="font-bold" :style="{ color: 'var(--text-primary)' }">{{ item.item_name }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- FOTO -->
                        <div v-if="driveConnected" class="rounded-2xl shadow-sm overflow-hidden" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
                            <div class="px-6 py-4" :style="{ background: 'var(--bg-hover)', borderBottom: '1px solid var(--border-color)' }">
                                <h3 class="font-bold tracking-tight text-base" :style="{ color: 'var(--text-primary)' }">Foto Unit Masuk</h3>
                            </div>
                            <div class="p-6">
                                <label class="block text-xs font-bold uppercase tracking-wider mb-3" :style="{ color: 'var(--text-muted)' }">Upload Foto (Maks 10) — Minimal Depan, Belakang, Kerusakan</label>
                                <KInput  type="file" @change="onPhotosChange" accept="image/*" multiple class="block w-full text-sm cursor-pointer transition-colors" :style="{ color: 'var(--text-muted)' }" />
                                <p v-if="photoFiles.length > 0 && photoFiles.length < 3" class="text-xs mt-2 font-medium" :style="{ color: 'var(--color-warning-text)' }">⚠️ Disarankan upload minimal 3 foto: Depan, Belakang, dan Kerusakan.</p>
                                <div v-if="photoPreviews.length" class="mt-4 grid grid-cols-4 gap-2">
                                    <div v-for="(preview, idx) in photoPreviews" :key="idx" class="relative group">
                                        <img :src="preview" class="w-full aspect-square object-cover rounded-lg shadow-sm" :style="{ borderColor: 'var(--border-color)' }" />
                                        <KButton  type="button" @click="removePhoto(idx)" class="absolute -top-1.5 -right-1.5 w-6 h-6 bg-red-600 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow-md">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </KButton>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BOTTOM ACTIONS -->
                <div class="sticky bottom-0 backdrop-blur-md mt-8 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-5 flex items-center justify-between z-10 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]" :style="{ background: 'rgba(255,255,255,0.8)', borderTop: '1px solid var(--border-color)' }">
                    <div class="hidden sm:block">
                        <p class="text-xs font-medium" :style="{ color: 'var(--text-muted)' }">Shortcut Simpan: <kbd class="px-2 py-1 rounded-md font-mono text-[10px] font-bold" :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-hover)' }">Ctrl+Enter</kbd></p>
                    </div>
                    <div class="flex gap-3 w-full sm:w-auto">
                        <Link :href="route('services.index')" class="flex-1 sm:flex-none">
                            <KButton  type="button" class="w-full px-6 py-2.5 text-sm font-bold rounded-xl transition-colors shadow-sm" :style="{ color: 'var(--text-primary)', background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">Batal</KButton>
                        </Link>
                        <KButton  type="submit" :disabled="form.processing || !canSubmitCore" class="flex-1 sm:flex-none min-w-[160px] px-6 py-2.5 text-sm font-bold text-white rounded-xl transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed flex justify-center items-center gap-2" style="background: var(--color-primary)">
                            <svg v-if="form.processing" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Tiket' }}
                        </KButton>
                    </div>
                </div>
            </form>
        </div>

        <!-- ADD CUSTOMER MODAL -->
        <Teleport to="body">
            <div v-if="showAddCustomerModal" class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm p-4" :style="{ background: 'rgba(0,0,0,0.4)' }" @click.self="showAddCustomerModal = false">
                <div class="w-full max-w-md rounded-2xl shadow-xl overflow-hidden animate-in zoom-in-95 duration-200" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
                    <form @submit.prevent="submitFastCustomer">
                        <div class="p-6">
                            <h3 class="text-xl font-bold mb-6" :style="{ color: 'var(--text-primary)' }">Tambah Pelanggan Baru</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider mb-2" :style="{ color: 'var(--text-muted)' }">Nama Pelanggan <span class="text-red-500">*</span></label>
                                    <KInput  ref="newCustomerNameEl" type="text" v-model="newCustomerForm.name" placeholder="Nama lengkap..." class="w-full rounded-xl text-sm text-sm px-4 py-2.5 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500  transition-all" :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-card)', color: 'var(--text-primary)' }" required />
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider mb-2" :style="{ color: 'var(--text-muted)' }">No. WhatsApp / Telepon <span class="text-red-500">*</span></label>
                                    <KInput  type="text" v-model="newCustomerForm.phone" placeholder="08xxxxxxxxxx" class="w-full rounded-xl text-sm text-sm px-4 py-2.5 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500  transition-all" :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-card)', color: 'var(--text-primary)' }" required />
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider mb-2" :style="{ color: 'var(--text-muted)' }">Alamat (Opsional)</label>
                                    <KTextarea  v-model="newCustomerForm.address" placeholder="Alamat pelanggan..." rows="2" class="w-full rounded-xl text-sm text-sm px-4 py-3 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500  transition-all resize-none" :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-card)', color: 'var(--text-primary)' }"></KTextarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider mb-2" :style="{ color: 'var(--text-muted)' }">Email (Opsional)</label>
                                    <KInput  type="email" v-model="newCustomerForm.email" placeholder="email@example.com" class="w-full rounded-xl text-sm text-sm px-4 py-2.5 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500  transition-all" :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-card)', color: 'var(--text-primary)' }" />
                                </div>
                            </div>
                        </div>
                        <div class="p-6 pt-0 flex gap-3">
                            <KButton  type="button" @click="showAddCustomerModal = false" class="flex-1 px-4 py-2.5 rounded-xl text-sm font-bold transition-colors" :style="{ color: 'var(--text-primary)', background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">Batal</KButton>
                            <KButton  type="submit" :disabled="savingCustomer || !newCustomerForm.name" class="flex-1 px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition-colors disabled:opacity-50 flex justify-center items-center gap-2">
                                <svg v-if="savingCustomer" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Simpan
                            </KButton>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>

<script setup>
import KButton from '@/Components/KButton.vue';
import KInput from '@/Components/KInput.vue';
import KSelect from '@/Components/KSelect.vue';
import KTextarea from '@/Components/KTextarea.vue';
import KCheckbox from '@/Components/KCheckbox.vue';

import { useForm, Link } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DynamicFormFields from '@/Components/DynamicFormFields.vue';
import { useCSIntake } from '@/Composables/useCSIntake.js';
import { useDebounce } from '@/Composables/usePerformance.js';

const props = defineProps({
    customers: { type: Array, default: () => [] },
    templates: { type: Array, default: () => [] },
    products: { type: Array, default: () => [] },
    deviceCategories: { type: Array, default: () => [] },
    brands: { type: Array, default: () => [] },
    arrivalMethods: { type: Array, default: () => [] },
    equipment: { type: Array, default: () => [] },
    driveConnected: { type: Boolean, default: false },
    customFields: { type: Array, default: () => [] },
});

const photoFiles = ref([]);
const photoPreviews = ref([]);
const customerSelectEl = ref(null);
const tipeUnitInputEl = ref(null);
const problemDescriptionEl = ref(null);
const newCustomerNameEl = ref(null);

const form = useForm({
    customer_id: '', jalur_kedatangan_id: '', kategori_perangkat_id: '', merek_id: '',
    tipe_unit: '', imei_sn: '', sandi_pola: '', kelengkapan: [], checklist_template_id: '',
    checked_items: [], problem_description: '', condition_note: '',
});

const customerList = ref([...props.customers]);
const showAddCustomerModal = ref(false);
const savingCustomer = ref(false);
const newCustomerForm = ref({ name: '', phone: '', email: '', address: '' });

// ── Sprint v1.0: IMEI Auto-Detect via useCSIntake ──
const intake = useCSIntake();
const imeiLookupResult = ref(null);
const isLookingUpIMEI = ref(false);
const showDeviceHistory = ref(false);

const debouncedIMEILookup = useDebounce(async (imei) => {
  if (!imei || imei.length < 5) { imeiLookupResult.value = null; showDeviceHistory.value = false; return; }
  isLookingUpIMEI.value = true;
  const result = await intake.lookupIMEI(imei);
  imeiLookupResult.value = result;
  showDeviceHistory.value = !!result;
  isLookingUpIMEI.value = false;

  // Auto-select customer if found
  if (result?.customer?.id && !form.customer_id) {
    const existsInList = customerList.value.find(c => c.id == result.customer.id);
    if (!existsInList) customerList.value.unshift(result.customer);
    form.customer_id = result.customer.id;
  }
  // Auto-fill device fields
  if (result?.device) {
    if (result.device.type && !form.kategori_perangkat_id) {
      const cat = props.deviceCategories.find(dc => dc.name?.toLowerCase() === result.device.type?.toLowerCase());
      if (cat) form.kategori_perangkat_id = cat.id;
    }
    if (result.device.brand && !form.merek_id) {
      const brand = props.brands.find(b => b.name?.toLowerCase() === result.device.brand?.toLowerCase());
      if (brand) form.merek_id = brand.id;
    }
    if (result.device.model && !form.tipe_unit) form.tipe_unit = result.device.model;
  }
}, 500);

watch(() => form.imei_sn, (val) => { debouncedIMEILookup(val); });

const customerOptions = computed(() => customerList.value);
const selectedCustomer = computed(() => customerList.value.find(c => c.id == form.customer_id));
const selectedTemplate = computed(() => props.templates.find(t => t.id == form.checklist_template_id));
const canSubmitCore = computed(() => !!form.customer_id && !!String(form.tipe_unit || '').trim() && !!String(form.problem_description || '').trim() && !intake.isBlacklisted());

const openAddCustomerModal = () => { newCustomerForm.value = { name: '', phone: '', email: '', address: '' }; showAddCustomerModal.value = true; };

const submitFastCustomer = async () => {
    if (!newCustomerForm.value.name) return;
    savingCustomer.value = true;
    try {
        const { default: axios } = await import('axios');
        const response = await axios.post(route('customers.ajax-store'), newCustomerForm.value);
        if (response.data.success && response.data.customer) {
            customerList.value.unshift(response.data.customer);
            form.customer_id = response.data.customer.id;
            showAddCustomerModal.value = false;
        }
    } catch (e) {
        alert(e.response?.data?.message || 'Gagal menyimpan pelanggan.');
    } finally {
        savingCustomer.value = false;
    }
};

watch(() => form.customer_id, (newVal, oldVal) => { if (!oldVal && newVal) nextTick(() => tipeUnitInputEl.value?.focus()); });
watch(showAddCustomerModal, (isOpen) => { if (isOpen) nextTick(() => newCustomerNameEl.value?.focus()); });

const handleGlobalShortcut = (event) => {
    const key = String(event.key || '').toLowerCase();
    const hasModifier = event.ctrlKey || event.metaKey;
    if (hasModifier && event.shiftKey && key === 'n') { event.preventDefault(); openAddCustomerModal(); return; }
    if (event.key === 'Escape' && showAddCustomerModal.value) { event.preventDefault(); showAddCustomerModal.value = false; }
};

onMounted(() => { nextTick(() => customerSelectEl.value?.focus()); window.addEventListener('keydown', handleGlobalShortcut); });
onUnmounted(() => { window.removeEventListener('keydown', handleGlobalShortcut); });

const onPhotosChange = (e) => {
    const files = Array.from(e.target.files);
    const remaining = 10 - photoFiles.value.length;
    const toAdd = files.slice(0, remaining);
    photoFiles.value = [...photoFiles.value, ...toAdd];
    toAdd.forEach(f => photoPreviews.value.push(URL.createObjectURL(f)));
};

const removePhoto = (idx) => { photoFiles.value.splice(idx, 1); photoPreviews.value.splice(idx, 1); };

const submit = () => {
    const data = new FormData();
    ['customer_id', 'problem_description', 'condition_note', 'jalur_kedatangan_id', 'kategori_perangkat_id', 'merek_id', 'tipe_unit', 'imei_sn', 'sandi_pola'].forEach(k => { if(form[k]) data.append(k, form[k]); });
    if (form.checklist_template_id) data.append('checklist_template_id', form.checklist_template_id);
    form.checked_items.forEach((item, i) => data.append(`checked_items[${i}]`, item));
    form.kelengkapan.forEach((item, i) => data.append(`kelengkapan[${i}]`, item));
    photoFiles.value.forEach((file, i) => data.append(`photos[${i}]`, file));
    form.post(route('services.store'), { data });
};

const handleShortcutSubmit = () => { if (!form.processing && canSubmitCore.value) submit(); };
</script>
