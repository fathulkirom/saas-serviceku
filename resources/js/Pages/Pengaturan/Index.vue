<template>
  <AuthenticatedLayout>
    <template #header>
      <PageHeader :title="pageTitle" :subtitle="subtitle">
        <button
          v-if="activeTab === 'custom-fields'"
          @click="openCustomFieldModal()"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold text-white transition-all hover:shadow-md cursor-pointer"
          style="background: var(--accent-primary);"
        >
          + Tambah Kolom Kustom
        </button>
      </PageHeader>
    </template>

    <TabPage :tabs="tabs" v-model="activeTab" @update:model-value="switchTab">
      <!-- PROFIL TOKO -->
      <template #profil>
        <div class="space-y-6">
          <template v-if="!profile">
            <Skeleton type="stat" :count="4" />
          </template>
          <template v-else>
            <form @submit.prevent="submitProfil">
              <KCard title="Identitas Toko">
                <div class="flex items-start gap-5 mb-4">
                  <div class="flex-shrink-0">
                    <div class="w-20 h-20 rounded-2xl flex items-center justify-center text-2xl font-bold text-white"
                      :style="{ background: 'var(--accent-primary)' }">
                      {{ profileSettings?.store_name?.charAt(0) || 'T' }}
                    </div>
                  </div>
                  <div class="flex-1 space-y-3">
                    <div>
                      <label class="text-xs font-semibold" :style="{ color: 'var(--text-muted)' }">Nama Toko</label>
                      <input v-model="profilForm.store_name" class="input text-sm mt-1" />
                    </div>
                    <div class="flex items-center gap-2">
                      <Badge :variant="planVariant(profile?.plan?.name)">{{ profile?.plan?.name || 'Trial' }}</Badge>
                      <Link :href="route('pengaturan.index', { tab: 'tagihan' })" class="text-xs font-semibold" style="color: var(--accent-primary);">Kelola Paket & Tagihan →</Link>
                    </div>
                  </div>
                </div>
              </KCard>

              <KCard title="Kontak Toko">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div class="space-y-1">
                    <label class="text-xs font-semibold" :style="{ color: 'var(--text-muted)' }">Alamat Toko</label>
                    <textarea v-model="profilForm.address" rows="2" class="input text-sm mt-1"></textarea>
                  </div>
                  <div class="space-y-1">
                    <label class="text-xs font-semibold" :style="{ color: 'var(--text-muted)' }">Telepon</label>
                    <input v-model="profilForm.phone" class="input text-sm mt-1" />
                  </div>
                  <div class="space-y-1">
                    <label class="text-xs font-semibold" :style="{ color: 'var(--text-muted)' }">Nomor WhatsApp Toko</label>
                    <input v-model="profilForm.whatsapp_number" class="input text-sm mt-1" placeholder="08xxxxxxxxxx" />
                  </div>
                </div>
              </KCard>

              <KCard title="Branding & Tampilan">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div class="space-y-1">
                    <label class="text-xs font-semibold" :style="{ color: 'var(--text-muted)' }">Warna Akses Utama</label>
                    <div class="flex items-center gap-2 mt-1">
                      <input type="color" v-model="profilForm.primary_color" class="w-10 h-10 rounded-lg border cursor-pointer" :style="{ borderColor: 'var(--border-color)' }" />
                      <span class="text-xs font-mono font-semibold" style="color: var(--text-secondary);">{{ profilForm.primary_color }}</span>
                    </div>
                  </div>
                  <div class="space-y-1">
                    <label class="text-xs font-semibold" :style="{ color: 'var(--text-muted)' }">Logo Toko</label>
                    <div class="flex items-center gap-2 mt-1">
                      <span class="text-xs font-medium" style="color: var(--text-secondary);">{{ profileSettings?.logo ? 'Logo sudah diupload' : 'Belum ada logo' }}</span>
                      <input type="file" accept="image/png,image/jpeg" class="text-xs" @change="onLogoChange" />
                    </div>
                  </div>
                </div>
              </KCard>

              <KCard title="Pengaturan Cetak Nota">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div class="space-y-1">
                    <label class="text-xs font-semibold" :style="{ color: 'var(--text-muted)' }">Ukuran Kertas Default</label>
                    <select v-model="profilForm.paper_size" class="input text-sm mt-1">
                      <option value="a4">A4 Standard</option>
                      <option value="a5">A5 Half Sheet</option>
                      <option value="thermal_80">Thermal 80mm</option>
                      <option value="thermal_58">Thermal 58mm</option>
                    </select>
                  </div>
                </div>
              </KCard>

              <div class="flex justify-end gap-2 mb-6">
                <button type="submit" :disabled="profilForm.processing" class="btn-primary text-xs">
                  {{ profilForm.processing ? 'Menyimpan...' : 'Simpan Perubahan Profil' }}
                </button>
              </div>
            </form>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
              <StatCard label="Cabang" :value="profileStats?.branches ?? 0" color="blue" variant="glass" />
              <StatCard label="Pengguna" :value="profileStats?.users ?? 0" color="purple" variant="glass" />
              <StatCard label="Servis Aktif" :value="profileStats?.active_services ?? 0" color="green" variant="glass" />
              <StatCard label="Produk" :value="profileStats?.products ?? 0" color="orange" variant="glass" />
            </div>
          </template>
        </div>
      </template>

      <!-- REFERENSI & MASTER DATA -->
      <template #settings>
        <div class="space-y-6">
          <Skeleton v-if="!settings" type="table" :count="3" />
          <template v-else>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
              <KCard title="Kategori Perangkat">
                <div class="flex items-center justify-between">
                  <p class="text-2xl font-bold" style="color: var(--accent-primary);">{{ deviceCategories.length }}</p>
                  <button @click="openMasterDrawer('device_category', 'Kategori Perangkat', deviceCategories)" class="text-xs font-bold text-blue-600 hover:underline cursor-pointer">Kelola →</button>
                </div>
                <div v-if="deviceCategories.length" class="mt-2 space-y-1">
                  <p v-for="item in deviceCategories.slice(0, 4)" :key="item.id" class="text-xs truncate" style="color: var(--text-secondary);">• {{ item.name }}</p>
                </div>
              </KCard>

              <KCard title="Merek & Brand">
                <div class="flex items-center justify-between">
                  <p class="text-2xl font-bold" style="color: var(--accent-primary);">{{ brands.length }}</p>
                  <button @click="openMasterDrawer('brand', 'Merek & Brand', brands)" class="text-xs font-bold text-blue-600 hover:underline cursor-pointer">Kelola →</button>
                </div>
                <div v-if="brands.length" class="mt-2 space-y-1">
                  <p v-for="item in brands.slice(0, 4)" :key="item.id" class="text-xs truncate" style="color: var(--text-secondary);">• {{ item.name }}</p>
                </div>
              </KCard>

              <KCard title="Satuan Barang">
                <div class="flex items-center justify-between">
                  <p class="text-2xl font-bold" style="color: var(--accent-primary);">{{ units.length }}</p>
                  <button @click="openMasterDrawer('unit', 'Satuan Barang', units)" class="text-xs font-bold text-blue-600 hover:underline cursor-pointer">Kelola →</button>
                </div>
                <div v-if="units.length" class="mt-2 space-y-1">
                  <p v-for="item in units.slice(0, 4)" :key="item.id" class="text-xs truncate" style="color: var(--text-secondary);">• {{ item.name }}</p>
                </div>
              </KCard>

              <KCard title="Peralatan Unit">
                <div class="flex items-center justify-between">
                  <p class="text-2xl font-bold" style="color: var(--accent-primary);">{{ equipment.length }}</p>
                  <button @click="openMasterDrawer('equipment', 'Peralatan Unit', equipment)" class="text-xs font-bold text-blue-600 hover:underline cursor-pointer">Kelola →</button>
                </div>
                <div v-if="equipment.length" class="mt-2 space-y-1">
                  <p v-for="item in equipment.slice(0, 4)" :key="item.id" class="text-xs truncate" style="color: var(--text-secondary);">• {{ item.name }}</p>
                </div>
              </KCard>

              <KCard title="Master Jasa Servis">
                <div class="flex items-center justify-between">
                  <p class="text-2xl font-bold" style="color: var(--accent-primary);">{{ laborServices.length }}</p>
                  <Link :href="route('master-services.index')" class="text-xs font-bold text-blue-600 hover:underline">Kelola →</Link>
                </div>
                <div v-if="laborServices.length" class="mt-2 space-y-1">
                  <p v-for="item in laborServices.slice(0, 4)" :key="item.id" class="text-xs" style="color: var(--text-secondary);">• {{ item.name }}</p>
                </div>
              </KCard>

              <KCard title="Integrasi Google Drive">
                <div class="flex items-center justify-between mt-1">
                  <div class="flex items-center gap-2">
                    <span v-if="driveConnected" class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <span v-else class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                    <span class="text-sm font-semibold" :style="{ color: driveConnected ? '#10b981' : '#ef4444' }">{{ driveConnected ? 'Terhubung' : 'Belum Terhubung' }}</span>
                  </div>
                  <a v-if="!driveConnected && driveAuthUrl" :href="driveAuthUrl" class="px-2.5 py-1 rounded text-xs font-bold text-white bg-blue-600 hover:bg-blue-700">Hubungkan</a>
                </div>
                <p v-if="driveInfo?.email" class="text-xs mt-2" style="color: var(--text-muted);">{{ driveInfo.email }}</p>
              </KCard>
            </div>
          </template>
        </div>
      </template>

      <!-- WA NOTIFIKASI -->
      <template #wa>
        <div class="space-y-6">
          <form @submit.prevent="submitWaGateway">
            <KCard title="Pengaturan Gateway Notifikasi WhatsApp">
              <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div class="space-y-1">
                    <label class="text-xs font-semibold" style="color: var(--text-muted);">Penyedia Gateway *</label>
                    <select v-model="waForm.provider" required class="input text-sm">
                      <option value="fonnte">Fonnte (fonnte.com)</option>
                      <option value="wablas">Wablas (wablas.com)</option>
                    </select>
                  </div>
                  <div class="space-y-1">
                    <label class="text-xs font-semibold" style="color: var(--text-muted);">Kunci API (API Token) *</label>
                    <input v-model="waForm.api_key" type="password" required placeholder="Masukkan Token API Fonnte/Wablas" class="input text-sm" />
                  </div>
                </div>

                <div class="space-y-1">
                  <label class="text-xs font-semibold" style="color: var(--text-muted);">Template Pesan: Servis Diterima</label>
                  <textarea v-model="waForm.template_service_received" rows="3" class="input text-sm" placeholder="Halo {customer_name}, unit {unit_type} Anda telah kami terima dengan No. Servis {service_code}. Cek status di {tracking_url}"></textarea>
                </div>

                <div class="space-y-1">
                  <label class="text-xs font-semibold" style="color: var(--text-muted);">Template Pesan: Servis Selesai</label>
                  <textarea v-model="waForm.template_service_finished" rows="3" class="input text-sm" placeholder="Halo {customer_name}, servis unit {unit_type} Anda telah SELESAI. Silakan ambil di toko kami."></textarea>
                </div>

                <div class="flex items-center gap-2 pt-2">
                  <input type="checkbox" v-model="waForm.is_active" id="wa_active" class="rounded" />
                  <label for="wa_active" class="text-xs font-medium">Aktifkan Kirim Notifikasi WhatsApp Otomatis</label>
                </div>

                <div class="flex justify-end pt-2">
                  <button type="submit" :disabled="waForm.processing" class="btn-primary text-xs">
                    {{ waForm.processing ? 'Menyimpan...' : 'Simpan Pengaturan WA' }}
                  </button>
                </div>
              </div>
            </KCard>
          </form>
        </div>
      </template>

      <!-- TAGIHAN / VOUCHER -->
      <template #tagihan>
        <div class="space-y-6">
          <Skeleton v-if="!currentPlan" type="stat" :count="3" />
          <template v-else>
            <KCard title="Paket Langganan Saat Ini">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-lg font-bold" style="color: var(--text-primary);">{{ currentPlan?.name ?? 'Trial' }}</h3>
                  <p class="text-xs mt-1" style="color: var(--text-muted);">Rp {{ formatNumber(currentPlan?.price ?? 0) }}/bulan</p>
                </div>
                <Badge variant="green">Aktif</Badge>
              </div>
            </KCard>

            <KCard title="Gunakan Kode Voucher / Promo">
              <form @submit.prevent="applyVoucher" class="flex items-end gap-3">
                <div class="flex-1">
                  <input v-model="voucherCode" placeholder="Masukkan kode voucher discount" class="input text-sm" />
                </div>
                <button type="submit" :disabled="!voucherCode" class="btn-primary text-xs">Gunakan Voucher</button>
              </form>
            </KCard>

            <div v-if="plans?.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <KCard v-for="plan in plans" :key="plan.id" :title="plan.name">
                <p class="text-2xl font-bold mb-2" style="color: var(--text-primary);">
                  Rp {{ formatNumber(plan.price ?? 0) }}
                  <span class="text-xs font-normal" style="color: var(--text-muted);">/bulan</span>
                </p>
                <p class="text-xs mb-4" style="color: var(--text-secondary);">{{ plan.description ?? '-' }}</p>
                <div class="flex flex-wrap gap-1 mb-3">
                  <span class="px-2 py-0.5 text-[10px] rounded-full font-semibold" style="background: var(--bg-hover); color: var(--text-muted);">{{ plan.max_users ?? 1 }} Pengguna</span>
                  <span class="px-2 py-0.5 text-[10px] rounded-full font-semibold" style="background: var(--bg-hover); color: var(--text-muted);">{{ plan.max_branches ?? 1 }} Cabang</span>
                </div>
              </KCard>
            </div>
          </template>
        </div>
      </template>

      <!-- DEMO MODE -->
      <template #demo>
        <div class="space-y-6">
          <Skeleton v-if="!demoStats" type="stat" :count="4" />
          <div v-else class="space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
              <StatCard label="Pelanggan" :value="demoStats?.customers_count ?? 0" color="purple" variant="glass" />
              <StatCard label="Produk" :value="demoStats?.products_count ?? 0" color="blue" variant="glass" />
              <StatCard label="Servis" :value="demoStats?.services_count ?? 0" color="green" variant="glass" />
              <StatCard label="Mode Demo" color="orange" variant="glass">
                <template #value>
                  <Badge :variant="demoStats?.demo_mode ? 'green' : 'red'">{{ demoStats?.demo_mode ? 'Aktif' : 'Nonaktif' }}</Badge>
                </template>
              </StatCard>
            </div>
            <KCard title="Aksi Kelola Mode Demo">
              <div class="flex flex-wrap gap-3">
                <Link v-if="!demoStats?.demo_data_generated" :href="route('demo.generate')" method="post" as="button" class="btn-primary text-xs">Buat Data Demo Simulasi</Link>
                <Link :href="route('demo.toggle')" method="post" as="button" class="btn-secondary text-xs">{{ demoStats?.demo_mode ? 'Nonaktifkan' : 'Aktifkan' }} Mode Demo</Link>
                <Link :href="route('demo.reset')" method="post" as="button" class="btn-secondary text-xs text-red-600">Reset Data Demo</Link>
              </div>
            </KCard>
          </div>
        </div>
      </template>

      <!-- KOLOM KUSTOM -->
      <template #custom-fields>
        <div class="space-y-6">
          <Skeleton v-if="!customFields" type="table" :count="5" />
          <KTable
            v-else
            :columns="customFieldColumns"
            :rows="customFields?.data ?? customFields ?? []"
            :emptyTitle="'Belum ada kolom kustom'"
            :emptyDescription="'Kolom kustom form servis akan muncul setelah ditambahkan.'"
            :emptyActionLabel="'+ Tambah Kolom Kustom'"
            @empty-action="openCustomFieldModal()"
          >
            <template #cell-name="{ row }">
              <span class="font-medium">{{ row.name }}</span>
            </template>
            <template #cell-type="{ row }">
              <Badge variant="blue">{{ row.type }}</Badge>
            </template>
            <template #cell-ordering="{ row }">
              {{ row.ordering ?? 0 }}
            </template>
            <template #cell-action="{ row }">
              <button @click="deleteCustomField(row)" class="px-2.5 py-1 rounded text-xs font-medium text-red-600 border border-red-200 hover:bg-red-50">Hapus</button>
            </template>
          </KTable>
        </div>
      </template>
    </TabPage>

    <!-- DRAWER KOLOM KUSTOM -->
    <Drawer :open="showCustomFieldDrawer" title="Tambah Kolom Form Kustom" @close="showCustomFieldDrawer = false" width="420px">
      <form @submit.prevent="submitCustomField" class="space-y-4">
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Nama Kolom / Label *</label>
          <input v-model="customFieldForm.name" required placeholder="e.g. Nomor IMEI 2 / Kondisi Dus" class="input text-sm" />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Tipe Input *</label>
          <select v-model="customFieldForm.type" required class="input text-sm">
            <option value="text">Teks Singkat (Text)</option>
            <option value="textarea">Teks Panjang (Textarea)</option>
            <option value="number">Angka (Number)</option>
            <option value="date">Tanggal (Date)</option>
          </select>
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Urutan Tampilan</label>
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
            <span class="text-sm font-medium" style="color: var(--text-primary);">{{ item.name }}</span>
            <button type="button" @click="deleteMasterData(item)" class="text-xs text-red-500 hover:underline font-semibold cursor-pointer">Hapus</button>
          </div>
        </div>
      </div>
    </Drawer>
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

const tabs = [
  { key: 'profil', label: 'Profil' },
  { key: 'settings', label: 'Referensi' },
  { key: 'wa', label: 'WA Notifikasi' },
  { key: 'tagihan', label: 'Tagihan' },
  { key: 'demo', label: 'Demo' },
  { key: 'custom-fields', label: 'Kolom Kustom' },
];

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

