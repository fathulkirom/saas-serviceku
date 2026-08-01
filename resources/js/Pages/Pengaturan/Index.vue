<template>
  <AuthenticatedLayout>
    <div class="flex flex-col min-h-[calc(100vh-64px)] bg-zinc-50">
      <!-- Header CRM Style -->
      <div class="px-6 sm:px-8 py-6 bg-white border-b border-zinc-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4 sticky top-0 z-20">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-zinc-100 rounded-xl flex items-center justify-center border border-zinc-200">
                <svg class="w-6 h-6 text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 tracking-tight">{{ pageTitle }}</h1>
                <p class="text-sm text-zinc-500 font-medium mt-0.5">{{ subtitle }}</p>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-2">
          <button v-if="activeTab === 'custom-fields'" @click="openCustomFieldModal()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow-md transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Kolom Kustom
          </button>
        </div>
      </div>

      <div class="flex-1 p-6 sm:p-8 max-w-[1400px] mx-auto w-full">
        <TabPage :tabs="tabs" v-model="activeTab" @update:model-value="switchTab">
      <!-- PROFIL TOKO -->
      <template #profil>
        <div class="space-y-6 mt-6">
          <template v-if="!profile">
            <Skeleton type="stat" :count="4" />
          </template>
          <template v-else>
            <form @submit.prevent="submitProfil" class="space-y-6">
              <div class="bg-white p-6 rounded-2xl border border-zinc-200 shadow-sm">
                <h3 class="text-lg font-bold text-zinc-900 mb-6">Identitas Toko</h3>
                <div class="flex flex-col sm:flex-row sm:items-start gap-6">
                  <div class="flex-shrink-0">
                    <div class="w-24 h-24 rounded-2xl flex items-center justify-center text-4xl font-black text-indigo-700 bg-indigo-50 border border-indigo-100 shadow-inner">
                      {{ profileSettings?.store_name?.charAt(0) || 'T' }}
                    </div>
                  </div>
                  <div class="flex-1 space-y-4">
                    <div>
                      <label class="text-sm font-semibold text-zinc-700">Nama Toko</label>
                      <input v-model="profilForm.store_name" class="w-full rounded-xl border border-zinc-300 px-4 py-2 mt-1.5 text-sm bg-white text-zinc-900 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all" />
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                      <Badge :variant="planVariant(profile?.plan?.name)">{{ profile?.plan?.name || 'Trial' }} Plan</Badge>
                      <Link :href="route('pengaturan.index', { tab: 'tagihan' })" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700 hover:underline">Kelola Paket & Tagihan →</Link>
                    </div>
                  </div>
                </div>
              </div>

              <div class="bg-white p-6 rounded-2xl border border-zinc-200 shadow-sm">
                <h3 class="text-lg font-bold text-zinc-900 mb-6">Kontak & Alamat</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                  <div class="space-y-1.5">
                    <label class="text-sm font-semibold text-zinc-700">Telepon Utama</label>
                    <input v-model="profilForm.phone" class="w-full rounded-xl border border-zinc-300 px-4 py-2 text-sm bg-white text-zinc-900 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all" />
                  </div>
                  <div class="space-y-1.5">
                    <label class="text-sm font-semibold text-zinc-700">Nomor WhatsApp Admin</label>
                    <input v-model="profilForm.whatsapp_number" class="w-full rounded-xl border border-zinc-300 px-4 py-2 text-sm bg-white text-zinc-900 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all" placeholder="08xxxxxxxxxx" />
                  </div>
                  <div class="space-y-1.5 sm:col-span-2">
                    <label class="text-sm font-semibold text-zinc-700">Alamat Toko</label>
                    <textarea v-model="profilForm.address" rows="3" class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm bg-white text-zinc-900 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all"></textarea>
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white p-6 rounded-2xl border border-zinc-200 shadow-sm">
                  <h3 class="text-lg font-bold text-zinc-900 mb-6">Branding & Tampilan</h3>
                  <div class="space-y-5">
                    <div class="space-y-2">
                      <label class="text-sm font-semibold text-zinc-700">Warna Aksen Utama</label>
                      <div class="flex items-center gap-3">
                        <input type="color" v-model="profilForm.primary_color" class="w-12 h-12 rounded-xl border-none cursor-pointer p-0 bg-transparent" />
                        <span class="text-sm font-mono font-bold text-zinc-600 bg-zinc-100 px-3 py-1.5 rounded-lg border border-zinc-200">{{ profilForm.primary_color }}</span>
                      </div>
                    </div>
                    <div class="space-y-2">
                      <label class="text-sm font-semibold text-zinc-700">Logo Toko (Nota/Struk)</label>
                      <div class="flex items-center gap-3">
                        <span class="text-xs font-semibold px-2 py-1 rounded-md bg-zinc-100 text-zinc-600 border border-zinc-200">{{ profileSettings?.logo ? 'Logo Terunggah' : 'Belum ada logo' }}</span>
                        <input type="file" accept="image/png,image/jpeg" class="text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-all cursor-pointer" @change="onLogoChange" />
                      </div>
                    </div>
                  </div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-zinc-200 shadow-sm">
                  <h3 class="text-lg font-bold text-zinc-900 mb-6">Pengaturan Cetak Nota</h3>
                  <div class="space-y-5">
                    <div class="space-y-2">
                      <label class="text-sm font-semibold text-zinc-700">Ukuran Kertas Default</label>
                      <select v-model="profilForm.paper_size" class="w-full rounded-xl border border-zinc-300 px-4 py-2.5 text-sm font-medium bg-white text-zinc-900 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all">
                        <option value="a4">A4 Standard</option>
                        <option value="a5">A5 Half Sheet</option>
                        <option value="thermal_80">Thermal 80mm</option>
                        <option value="thermal_58">Thermal 58mm</option>
                      </select>
                      <p class="text-xs text-zinc-500 mt-1">Ukuran ini akan digunakan saat mencetak nota servis / penjualan.</p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="flex justify-end gap-2 pt-2">
                <button type="submit" :disabled="profilForm.processing" class="px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md hover:shadow-lg transition-all">
                  {{ profilForm.processing ? 'Menyimpan...' : 'Simpan Perubahan Profil' }}
                </button>
              </div>
            </form>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 mt-8">
              <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-sm">
                <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1">Cabang Aktif</p>
                <h3 class="text-3xl font-black text-zinc-900">{{ profileStats?.branches ?? 0 }}</h3>
              </div>
              <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-sm">
                <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1">Pengguna / Staf</p>
                <h3 class="text-3xl font-black text-zinc-900">{{ profileStats?.users ?? 0 }}</h3>
              </div>
              <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-sm">
                <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1">Servis Aktif</p>
                <h3 class="text-3xl font-black text-zinc-900">{{ profileStats?.active_services ?? 0 }}</h3>
              </div>
              <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-sm">
                <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1">Total Produk</p>
                <h3 class="text-3xl font-black text-zinc-900">{{ profileStats?.products ?? 0 }}</h3>
              </div>
            </div>
          </template>
        </div>
      </template>

      <!-- REFERENSI & MASTER DATA -->
      <template #settings>
        <div class="space-y-6 mt-6">
          <Skeleton v-if="!settings" type="table" :count="3" />
          <template v-else>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
              
              <div class="bg-white p-6 rounded-2xl border border-zinc-200 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                <div>
                  <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-zinc-900">Kategori Perangkat</h3>
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm">{{ deviceCategories.length }}</div>
                  </div>
                  <div v-if="deviceCategories.length" class="space-y-2 mb-4">
                    <p v-for="item in deviceCategories.slice(0, 4)" :key="item.id" class="text-sm text-zinc-600 font-medium truncate flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-zinc-300"></span>{{ item.name }}</p>
                  </div>
                  <p v-else class="text-sm text-zinc-400 italic mb-4">Belum ada data</p>
                </div>
                <button @click="openMasterDrawer('device_category', 'Kategori Perangkat', deviceCategories)" class="w-full py-2 rounded-xl text-sm font-semibold bg-zinc-50 hover:bg-zinc-100 text-zinc-700 transition-all border border-zinc-200">Kelola Data</button>
              </div>

              <div class="bg-white p-6 rounded-2xl border border-zinc-200 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                <div>
                  <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-zinc-900">Merek & Brand</h3>
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm">{{ brands.length }}</div>
                  </div>
                  <div v-if="brands.length" class="space-y-2 mb-4">
                    <p v-for="item in brands.slice(0, 4)" :key="item.id" class="text-sm text-zinc-600 font-medium truncate flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-zinc-300"></span>{{ item.name }}</p>
                  </div>
                  <p v-else class="text-sm text-zinc-400 italic mb-4">Belum ada data</p>
                </div>
                <button @click="openMasterDrawer('brand', 'Merek & Brand', brands)" class="w-full py-2 rounded-xl text-sm font-semibold bg-zinc-50 hover:bg-zinc-100 text-zinc-700 transition-all border border-zinc-200">Kelola Data</button>
              </div>

              <div class="bg-white p-6 rounded-2xl border border-zinc-200 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                <div>
                  <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-zinc-900">Satuan Barang</h3>
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm">{{ units.length }}</div>
                  </div>
                  <div v-if="units.length" class="space-y-2 mb-4">
                    <p v-for="item in units.slice(0, 4)" :key="item.id" class="text-sm text-zinc-600 font-medium truncate flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-zinc-300"></span>{{ item.name }}</p>
                  </div>
                  <p v-else class="text-sm text-zinc-400 italic mb-4">Belum ada data</p>
                </div>
                <button @click="openMasterDrawer('unit', 'Satuan Barang', units)" class="w-full py-2 rounded-xl text-sm font-semibold bg-zinc-50 hover:bg-zinc-100 text-zinc-700 transition-all border border-zinc-200">Kelola Data</button>
              </div>

              <div class="bg-white p-6 rounded-2xl border border-zinc-200 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                <div>
                  <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-zinc-900">Peralatan Unit</h3>
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm">{{ equipment.length }}</div>
                  </div>
                  <div v-if="equipment.length" class="space-y-2 mb-4">
                    <p v-for="item in equipment.slice(0, 4)" :key="item.id" class="text-sm text-zinc-600 font-medium truncate flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-zinc-300"></span>{{ item.name }}</p>
                  </div>
                  <p v-else class="text-sm text-zinc-400 italic mb-4">Belum ada data</p>
                </div>
                <button @click="openMasterDrawer('equipment', 'Peralatan Unit', equipment)" class="w-full py-2 rounded-xl text-sm font-semibold bg-zinc-50 hover:bg-zinc-100 text-zinc-700 transition-all border border-zinc-200">Kelola Data</button>
              </div>

              <div class="bg-white p-6 rounded-2xl border border-zinc-200 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                <div>
                  <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-zinc-900">Master Jasa Servis</h3>
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm">{{ laborServices.length }}</div>
                  </div>
                  <div v-if="laborServices.length" class="space-y-2 mb-4">
                    <p v-for="item in laborServices.slice(0, 4)" :key="item.id" class="text-sm text-zinc-600 font-medium truncate flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-zinc-300"></span>{{ item.name }}</p>
                  </div>
                  <p v-else class="text-sm text-zinc-400 italic mb-4">Belum ada data</p>
                </div>
                <Link :href="route('master-services.index')" class="w-full py-2 rounded-xl text-sm font-semibold bg-zinc-50 hover:bg-zinc-100 text-zinc-700 transition-all border border-zinc-200 text-center block">Kelola Master Jasa</Link>
              </div>

              <div class="bg-white p-6 rounded-2xl border border-zinc-200 shadow-sm hover:shadow-md transition-all flex flex-col justify-between border-t-4 border-t-blue-500">
                <div>
                  <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-zinc-900">Integrasi Google Drive</h3>
                  </div>
                  <div class="flex flex-col gap-2 mb-6">
                    <div class="flex items-center gap-2">
                        <span v-if="driveConnected" class="w-3 h-3 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]"></span>
                        <span v-else class="w-3 h-3 rounded-full bg-zinc-300"></span>
                        <span class="text-sm font-bold" :class="driveConnected ? 'text-emerald-600' : 'text-zinc-500'">{{ driveConnected ? 'Terhubung' : 'Belum Terhubung' }}</span>
                    </div>
                    <p v-if="driveInfo?.email" class="text-sm text-zinc-600 font-medium break-all">{{ driveInfo.email }}</p>
                    <p v-else class="text-xs text-zinc-500">Hubungkan untuk menyimpan lampiran ke Google Drive secara otomatis.</p>
                  </div>
                </div>
                <a v-if="!driveConnected && driveAuthUrl" :href="driveAuthUrl" class="w-full py-2 rounded-xl text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white transition-all text-center block shadow-sm">Hubungkan Drive Sekarang</a>
              </div>

            </div>
          </template>
        </div>
      </template>

      <!-- WA NOTIFIKASI -->
      <template #wa>
        <div class="space-y-6 mt-6 max-w-3xl">
          <form @submit.prevent="submitWaGateway">
            <div class="bg-white p-6 rounded-2xl border border-zinc-200 shadow-sm">
              <h3 class="text-lg font-bold text-zinc-900 mb-6">Pengaturan Gateway WhatsApp</h3>
              <div class="space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                  <div class="space-y-2">
                    <label class="text-sm font-semibold text-zinc-700">Penyedia Gateway *</label>
                    <select v-model="waForm.provider" required class="w-full rounded-xl border border-zinc-300 px-4 py-2.5 text-sm bg-white text-zinc-900 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all">
                      <option value="fonnte">Fonnte (fonnte.com)</option>
                      <option value="wablas">Wablas (wablas.com)</option>
                    </select>
                  </div>
                  <div class="space-y-2">
                    <label class="text-sm font-semibold text-zinc-700">Kunci API (API Token) *</label>
                    <input v-model="waForm.api_key" type="password" required placeholder="Masukkan Token API Fonnte/Wablas" class="w-full rounded-xl border border-zinc-300 px-4 py-2 text-sm bg-white text-zinc-900 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all" />
                  </div>
                </div>

                <div class="space-y-2 pt-4 border-t border-zinc-100">
                  <label class="text-sm font-semibold text-zinc-700">Template Pesan: Servis Diterima</label>
                  <textarea v-model="waForm.template_service_received" rows="3" class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm bg-white text-zinc-900 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all" placeholder="Halo {customer_name}, unit {unit_type} Anda telah kami terima dengan No. Servis {service_code}. Cek status di {tracking_url}"></textarea>
                  <p class="text-xs text-zinc-500">Variabel: {customer_name}, {unit_type}, {service_code}, {tracking_url}</p>
                </div>

                <div class="space-y-2 pt-4 border-t border-zinc-100">
                  <label class="text-sm font-semibold text-zinc-700">Template Pesan: Servis Selesai</label>
                  <textarea v-model="waForm.template_service_finished" rows="3" class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm bg-white text-zinc-900 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all" placeholder="Halo {customer_name}, servis unit {unit_type} Anda telah SELESAI. Silakan ambil di toko kami."></textarea>
                  <p class="text-xs text-zinc-500">Variabel: {customer_name}, {unit_type}, {service_code}</p>
                </div>

                <div class="flex items-center gap-3 pt-6">
                  <input type="checkbox" v-model="waForm.is_active" id="wa_active" class="w-5 h-5 rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500" />
                  <label for="wa_active" class="text-sm font-bold text-zinc-800 cursor-pointer">Aktifkan Kirim Notifikasi WhatsApp Otomatis</label>
                </div>

                <div class="flex justify-end pt-4">
                  <button type="submit" :disabled="waForm.processing" class="px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md hover:shadow-lg transition-all">
                    {{ waForm.processing ? 'Menyimpan...' : 'Simpan Pengaturan WA' }}
                  </button>
                </div>
              </div>
            </div>
          </form>
        </div>
      </template>

      <!-- TAGIHAN / VOUCHER -->
      <template #tagihan>
        <div class="space-y-8 mt-6">
          <Skeleton v-if="!currentPlan" type="stat" :count="3" />
          <template v-else>
            <div class="bg-gradient-to-br from-indigo-900 to-indigo-800 p-8 rounded-3xl shadow-xl text-white relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full blur-3xl -mr-20 -mt-20"></div>
                <div class="relative z-10 flex flex-col sm:flex-row items-center justify-between gap-6">
                    <div>
                        <p class="text-indigo-200 font-semibold mb-1 text-sm tracking-wider uppercase">Paket Langganan Saat Ini</p>
                        <h3 class="text-4xl font-black mb-2">{{ currentPlan?.name ?? 'Trial' }}</h3>
                        <p class="text-indigo-100 font-medium text-lg">Rp {{ formatNumber(currentPlan?.price ?? 0) }} <span class="text-sm font-normal opacity-80">/bulan</span></p>
                    </div>
                    <div class="flex flex-col items-end gap-4">
                        <span class="px-4 py-1.5 bg-emerald-500 text-white text-sm font-bold rounded-full shadow-lg shadow-emerald-500/30">Aktif</span>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-zinc-200 shadow-sm max-w-xl">
              <h3 class="text-lg font-bold text-zinc-900 mb-4">Gunakan Kode Voucher / Promo</h3>
              <form @submit.prevent="applyVoucher" class="flex items-end gap-3">
                <div class="flex-1 space-y-1.5">
                  <input v-model="voucherCode" placeholder="Masukkan kode voucher discount" class="w-full rounded-xl border border-zinc-300 px-4 py-2.5 text-sm bg-white text-zinc-900 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all uppercase placeholder-normal" />
                </div>
                <button type="submit" :disabled="!voucherCode" class="px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md hover:shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                    Terapkan
                </button>
              </form>
            </div>

            <div v-if="plans?.length">
                <h3 class="text-xl font-bold text-zinc-900 mb-6">Pilihan Paket Langganan</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                  <div v-for="plan in plans" :key="plan.id" class="bg-white p-8 rounded-3xl border border-zinc-200 shadow-sm hover:shadow-xl transition-all relative flex flex-col justify-between" :class="{'border-indigo-500 shadow-indigo-100': currentPlan?.id === plan.id}">
                    <div v-if="currentPlan?.id === plan.id" class="absolute top-0 right-0 bg-indigo-500 text-white text-xs font-bold px-3 py-1 rounded-bl-xl rounded-tr-2xl">Paket Saat Ini</div>
                    
                    <div>
                        <h4 class="text-xl font-bold text-zinc-900 mb-2">{{ plan.name }}</h4>
                        <p class="text-3xl font-black text-zinc-900 mb-2">
                        Rp {{ formatNumber(plan.price ?? 0) }}
                        <span class="text-sm font-normal text-zinc-500">/bln</span>
                        </p>
                        <p class="text-sm text-zinc-600 font-medium mb-6 min-h-[40px]">{{ plan.description ?? 'Fasilitas premium untuk bisnis Anda.' }}</p>
                        
                        <div class="space-y-3 mb-8">
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span class="text-sm font-semibold text-zinc-700">{{ plan.max_users ?? 1 }} Pengguna</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span class="text-sm font-semibold text-zinc-700">{{ plan.max_branches ?? 1 }} Cabang</span>
                            </div>
                        </div>
                    </div>

                    <button class="w-full py-3 rounded-xl text-sm font-bold text-center transition-all" :class="currentPlan?.id === plan.id ? 'bg-zinc-100 text-zinc-400 cursor-default' : 'bg-zinc-900 hover:bg-zinc-800 text-white shadow-md'">
                        {{ currentPlan?.id === plan.id ? 'Dipilih' : 'Pilih Paket Ini' }}
                    </button>
                  </div>
                </div>
            </div>
          </template>
        </div>
      </template>

      <!-- DEMO MODE -->
      <template #demo>
        <div class="space-y-8 mt-6">
          <Skeleton v-if="!demoStats" type="stat" :count="4" />
          <div v-else class="space-y-6">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">
              <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-sm">
                <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1">Pelanggan Demo</p>
                <h3 class="text-3xl font-black text-zinc-900">{{ demoStats?.customers_count ?? 0 }}</h3>
              </div>
              <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-sm">
                <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1">Produk Demo</p>
                <h3 class="text-3xl font-black text-zinc-900">{{ demoStats?.products_count ?? 0 }}</h3>
              </div>
              <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-sm">
                <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1">Servis Demo</p>
                <h3 class="text-3xl font-black text-zinc-900">{{ demoStats?.services_count ?? 0 }}</h3>
              </div>
              <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-sm flex flex-col justify-center items-start">
                <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-2">Status Mode Demo</p>
                <Badge :variant="demoStats?.demo_mode ? 'green' : 'red'">{{ demoStats?.demo_mode ? 'Demo Aktif' : 'Demo Nonaktif' }}</Badge>
              </div>
            </div>

            <div class="bg-amber-50 border border-amber-200 p-6 rounded-2xl max-w-3xl">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-amber-900 mb-1">Mode Simulasi (Demo)</h3>
                        <p class="text-sm text-amber-800 mb-6">Gunakan mode ini untuk mencoba fitur aplikasi tanpa mengubah data asli Anda. Data demo dapat dibuat dan dihapus kapan saja.</p>
                        
                        <div class="flex flex-wrap gap-3">
                            <Link v-if="!demoStats?.demo_data_generated" :href="route('demo.generate')" method="post" as="button" class="px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-amber-600 hover:bg-amber-700 shadow-sm transition-all">Buat Data Demo Simulasi</Link>
                            
                            <Link :href="route('demo.toggle')" method="post" as="button" class="px-5 py-2.5 rounded-xl text-sm font-bold bg-white text-zinc-900 border border-zinc-200 hover:bg-zinc-50 shadow-sm transition-all">{{ demoStats?.demo_mode ? 'Nonaktifkan' : 'Aktifkan' }} Mode Demo</Link>
                            
                            <Link :href="route('demo.reset')" method="post" as="button" class="px-5 py-2.5 rounded-xl text-sm font-bold bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 shadow-sm transition-all">Reset Data Demo</Link>
                        </div>
                    </div>
                </div>
            </div>
          </div>
        </div>
      </template>

      <!-- KOLOM KUSTOM -->
      <template #custom-fields>
        <div class="space-y-6 mt-6">
          <Skeleton v-if="!customFields" type="table" :count="5" />
          <div v-else class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden">
            <KTable
              :columns="customFieldColumns"
              :rows="customFields?.data ?? customFields ?? []"
              :emptyTitle="'Belum ada kolom kustom'"
              :emptyDescription="'Kolom kustom form servis akan muncul setelah ditambahkan.'"
              :emptyActionLabel="'+ Tambah Kolom Kustom'"
              @empty-action="openCustomFieldModal()"
            >
              <template #cell-name="{ row }">
                <span class="font-bold text-zinc-900">{{ row.name }}</span>
              </template>
              <template #cell-type="{ row }">
                <Badge variant="blue">{{ row.type }}</Badge>
              </template>
              <template #cell-ordering="{ row }">
                <span class="font-medium text-zinc-600">{{ row.ordering ?? 0 }}</span>
              </template>
              <template #cell-action="{ row }">
                <button @click="deleteCustomField(row)" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-50 text-red-600 hover:bg-red-100 transition-all border border-red-200">Hapus</button>
              </template>
            </KTable>
          </div>
        </div>
      </template>
    </TabPage>
    </div>

    <!-- DRAWER KOLOM KUSTOM -->
    <Drawer :open="showCustomFieldDrawer" title="Tambah Kolom Form Kustom" @close="showCustomFieldDrawer = false" width="420px">
      <form @submit.prevent="submitCustomField" class="space-y-4">
        <div class="space-y-1">
          <label class="text-xs font-semibold text-zinc-500">Nama Kolom / Label *</label>
          <input v-model="customFieldForm.name" required placeholder="e.g. Nomor IMEI 2 / Kondisi Dus" class="input text-sm" />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold text-zinc-500">Tipe Input *</label>
          <select v-model="customFieldForm.type" required class="input text-sm">
            <option value="text">Teks Singkat (Text)</option>
            <option value="textarea">Teks Panjang (Textarea)</option>
            <option value="number">Angka (Number)</option>
            <option value="date">Tanggal (Date)</option>
          </select>
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold text-zinc-500">Urutan Tampilan</label>
          <input v-model="customFieldForm.ordering" type="number" min="0" class="input text-sm" />
        </div>
        <div class="flex justify-end gap-2 pt-3">
          <button type="button" @click="showCustomFieldDrawer = false" class="btn-secondary text-xs">Batal</button>
          <button type="submit" :disabled="customFieldForm.processing" class="btn-primary text-xs">
            {{ customFieldForm.processing ? 'Menyimpan...' : 'Simpan Kolom' }}
          </button>
        </div>
      </form>
    </Drawer>

    <!-- DRAWER KELOLA MASTER DATA INLINE -->
    <Drawer :open="showMasterDrawer" :title="'Kelola ' + masterDrawerTitle" @close="showMasterDrawer = false" width="420px">
      <div class="space-y-4">
        <!-- Form Tambah Baru -->
        <form @submit.prevent="submitMasterData" class="flex gap-2">
          <input v-model="masterForm.name" required placeholder="Nama baru..." class="input text-sm flex-1" />
          <button type="submit" :disabled="masterForm.processing" class="btn-primary text-xs whitespace-nowrap">+ Tambah</button>
        </form>

        <!-- Daftar Item -->
        <div class="space-y-2 max-h-80 overflow-y-auto pr-1">
          <div v-if="masterDrawerItems.length === 0" class="text-center py-6 text-xs text-muted">Belum ada data</div>
          <div v-for="item in masterDrawerItems" :key="item.id" class="flex items-center justify-between p-2.5 rounded-xl border" style="borderColor: var(--border-color); background: var(--bg-hover);">
            <span class="text-sm font-medium text-zinc-900">{{ item.name }}</span>
            <button type="button" @click="deleteMasterData(item)" class="text-xs text-red-500 hover:underline font-semibold cursor-pointer">Hapus</button>
          </div>
        </div>
      </div>
    </Drawer>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { computed, ref, reactive, watch } from 'vue';
import { router, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import TabPage from '@/Components/TabPage.vue';
import KCard from '@/Components/KCard.vue';
import KTable from '@/Components/KTable.vue';
import Badge from '@/Components/Badge.vue';
import Skeleton from '@/Components/Skeleton.vue';
import Drawer from '@/Components/Drawer.vue';
import StatCard from '@/Components/StatCard.vue';
import { useFormatter } from '@/Composables/useFormatter.js';

const { formatNumber, formatCurrency, formatDate, currentDate } = useFormatter();

const props = defineProps({
  activeTab: { type: String, default: 'profil' },
  profile: { type: Object, default: null },
  profileSettings: { type: Object, default: () => ({}) },
  profileStats: { type: Object, default: null },
  profileBranches: { type: Array, default: () => [] },
  settings: { type: Object, default: () => ({}) },
  deviceCategories: { type: Array, default: () => [] },
  brands: { type: Array, default: () => [] },
  units: { type: Array, default: () => [] },
  arrivalMethods: { type: Array, default: () => [] },
  paymentMethods: { type: Array, default: () => [] },
  equipment: { type: Array, default: () => [] },
  laborServices: { type: Array, default: () => [] },
  checklistTemplates: { type: Array, default: () => [] },
  driveConnected: { type: Boolean, default: false },
  driveInfo: { type: Object, default: () => ({}) },
  driveAuthUrl: { type: String, default: '' },
  tenant: { type: Object, default: null },
  currentPlan: { type: Object, default: null },
  plans: { type: Array, default: () => [] },
  voucherDiscount: { type: [Object, Array], default: null },
  demoStats: { type: Object, default: null },
  customFields: { type: [Object, Array], default: null },
  waGateway: { type: Object, default: null },
  canManageCustomFields: { type: Boolean, default: false },
});

const activeTab = ref(props.activeTab);

const profilForm = reactive({
  store_name: props.settings?.store_name || '',
  address: props.settings?.address || '',
  phone: props.settings?.phone || '',
  whatsapp_number: props.settings?.whatsapp_number || '',
  primary_color: props.settings?.primary_color || '#7c3aed',
  paper_size: props.settings?.paper_size || 'a4',
});

watch(() => props.settings, (s) => {
  if (s) {
    profilForm.store_name = s.store_name || '';
    profilForm.address = s.address || '';
    profilForm.phone = s.phone || '';
    profilForm.whatsapp_number = s.whatsapp_number || '';
    profilForm.primary_color = s.primary_color || '#7c3aed';
    profilForm.paper_size = s.paper_size || 'a4';
  }
}, { immediate: true });

function submitProfil() {
  router.post(route('settings.update'), { ...profilForm }, { preserveState: true, preserveScroll: true });
}

function onLogoChange(e) {
  const file = e.target.files?.[0];
  if (!file) return;
  const formData = new FormData();
  formData.append('logo', file);
  router.post(route('settings.upload-logo'), formData, { preserveState: true, preserveScroll: true });
  e.target.value = '';
}

// WA Form
const waForm = useForm({
  provider: props.waGateway?.provider || 'fonnte',
  api_key: props.waGateway?.api_key || '',
  template_service_received: props.waGateway?.template_service_received || '',
  template_service_finished: props.waGateway?.template_service_finished || '',
  is_active: Boolean(props.waGateway?.is_active),
});

function submitWaGateway() {
  waForm.post(route('settings.whatsapp-gateway.update'), { preserveScroll: true });
}

// Custom Field Drawer
const showCustomFieldDrawer = ref(false);
const customFieldForm = useForm({ name: '', type: 'text', ordering: 0 });

const openCustomFieldModal = () => {
  customFieldForm.reset();
  showCustomFieldDrawer.value = true;
};

const submitCustomField = () => {
  customFieldForm.post(route('custom-fields.store'), { preserveScroll: true, onSuccess: () => { showCustomFieldDrawer.value = false; } });
};

const deleteCustomField = (row) => {
  if (confirm(`Hapus kolom kustom "${row.name}"?`)) {
    router.delete(route('custom-fields.destroy', row.id), { preserveScroll: true });
  }
};

const voucherCode = ref('');
function applyVoucher() {
  if (!voucherCode.value) return;
  router.post(route('billing.apply-voucher'), { code: voucherCode.value }, {
    preserveState: true, preserveScroll: true,
    onSuccess: () => { voucherCode.value = ''; },
  });
}

const tabs = computed(() => {
  const base = [
    { key: 'profil', label: 'Profil' },
    { key: 'settings', label: 'Referensi' },
    { key: 'wa', label: 'WA Notifikasi' },
    { key: 'tagihan', label: 'Tagihan' },
    { key: 'demo', label: 'Demo' },
  ];
  if (props.canManageCustomFields) {
    base.push({ key: 'custom-fields', label: 'Kolom Kustom' });
  }
  return base;
});

const tabLabels = { profil: 'Profil', settings: 'Referensi', wa: 'WA Notifikasi', tagihan: 'Tagihan', demo: 'Demo', 'custom-fields': 'Kolom Kustom' };
const pageTitle = computed(() => 'Pengaturan — ' + (tabLabels[activeTab.value] || 'Profil'));
const subtitle = computed(() => currentDate.value);

const customFieldColumns = [
  { key: 'name', label: 'Nama Kolom' },
  { key: 'type', label: 'Tipe' },
  { key: 'ordering', label: 'Urutan' },
  { key: 'action', label: '', align: 'right' },
];

const planVariant = (name) => {
  if (!name) return 'default';
  const lower = name.toLowerCase();
  if (lower.includes('basic') || lower.includes('starter')) return 'blue';
  if (lower.includes('pro') || lower.includes('business')) return 'purple';
  if (lower.includes('enterprise') || lower.includes('unlimited')) return 'green';
  return 'yellow';
};

const showMasterDrawer = ref(false);
const masterDrawerTitle = ref('');
const masterCategoryKey = ref('');
const masterDrawerItems = ref([]);
const masterForm = useForm({ category: '', name: '' });

const openMasterDrawer = (category, title, items) => {
  masterCategoryKey.value = category;
  masterDrawerTitle.value = title;
  masterDrawerItems.value = [...(items || [])];
  masterForm.category = category;
  masterForm.name = '';
  showMasterDrawer.value = true;
};

const submitMasterData = () => {
  if (!masterForm.name) return;
  masterForm.post(route('master-data.store'), {
    preserveScroll: true,
    onSuccess: () => {
      masterForm.name = '';
      showMasterDrawer.value = false;
    }
  });
};

const deleteMasterData = (item) => {
  if (confirm(`Hapus data "${item.name}"?`)) {
    router.delete(route('master-data.destroy', item.id), {
      preserveScroll: true,
      onSuccess: () => {
        masterDrawerItems.value = masterDrawerItems.value.filter(i => i.id !== item.id);
      }
    });
  }
};

const switchTab = (key) => {
  router.get(route('pengaturan.index'), { tab: key }, { preserveState: true, preserveScroll: true });
};
</script>

