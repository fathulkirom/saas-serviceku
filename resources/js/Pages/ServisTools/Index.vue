<template>
  <AuthenticatedLayout>
    <template #header>
      <PageHeader :title="pageTitle" :subtitle="subtitle">
        <button
          v-if="activeTab === 'ceklis'"
          @click="openCeklisModal()"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold text-white transition-all hover:shadow-md cursor-pointer"
          style="background: var(--accent-primary);"
        >
          + Template Ceklis
        </button>
        <button
          v-if="activeTab === 'partner'"
          @click="openPartnerModal()"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold text-white transition-all hover:shadow-md cursor-pointer"
          style="background: var(--accent-primary);"
        >
          + Partner Baru
        </button>
        <button
          v-if="activeTab === 'pickup'"
          @click="openPickupModal()"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold text-white transition-all hover:shadow-md cursor-pointer"
          style="background: var(--accent-primary);"
        >
          + Jadwal Pickup/Delivery
        </button>
        <button
          v-if="activeTab === 'inden'"
          @click="openIndenModal()"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold text-white transition-all hover:shadow-md cursor-pointer"
          style="background: var(--accent-primary);"
        >
          + Catat Inden Baru
        </button>
      </PageHeader>
    </template>

    <TabPage :tabs="tabs" v-model="activeTab" @update:model-value="switchTab">
      <!-- CEKLIS TEMPLATES -->
      <template #ceklis>
        <div class="space-y-6">
          <Skeleton v-if="!templates" type="table" :count="5" />
          <KTable
            v-else
            :columns="ceklisColumns"
            :rows="templates?.data ?? templates ?? []"
            :emptyTitle="'Belum ada template ceklis'"
            :emptyDescription="'Template ceklis akan muncul setelah ditambahkan.'"
            :emptyActionLabel="'+ Template Ceklis Baru'"
            @empty-action="openCeklisModal()"
          >
            <template #cell-name="{ row }">
              <span class="font-medium">{{ row.name }}</span>
            </template>
            <template #cell-item_count="{ row }">
              <span class="font-bold text-xs">{{ row.items?.length ?? 0 }} item</span>
            </template>
            <template #cell-is_active="{ row }">
              <Badge :variant="row.is_active ? 'green' : 'default'">{{ row.is_active ? 'Aktif' : 'Nonaktif' }}</Badge>
            </template>
            <template #cell-created_at="{ row }">
              {{ formatDate(row.created_at) }}
            </template>
            <template #cell-action="{ row }">
              <div class="flex items-center justify-end gap-1">
                <button @click="openCeklisModal(row)" class="px-2.5 py-1 rounded text-xs font-medium border" style="borderColor: var(--border-color); color: var(--accent-primary);">Edit</button>
                <button @click="deleteTemplate(row)" class="px-2.5 py-1 rounded text-xs font-medium text-red-600 border border-red-200 hover:bg-red-50">Hapus</button>
              </div>
            </template>
          </KTable>

          <Pagination :meta="templates" />
        </div>
      </template>

      <!-- PARTNER TEKNISI -->
      <template #partner>
        <div class="space-y-6">
          <Skeleton v-if="!partners" type="table" :count="5" />
          <KTable
            v-else
            :columns="partnerColumns"
            :rows="partners?.data ?? []"
            :emptyTitle="'Belum ada data partner'"
            :emptyDescription="'Data partner teknisi akan muncul setelah ditambahkan.'"
            :emptyActionLabel="'+ Partner Teknisi Baru'"
            @empty-action="openPartnerModal()"
          >
            <template #cell-name="{ row }">
              <span class="font-medium">{{ row.name }}</span>
            </template>
            <template #cell-expertise="{ row }">
              <span class="text-xs font-semibold px-2 py-0.5 rounded" style="background: var(--bg-hover);">{{ row.expertise ?? '-' }}</span>
            </template>
            <template #cell-phone="{ row }">
              {{ row.phone ?? '-' }}
            </template>
            <template #cell-status="{ row }">
              <Badge :variant="row.status === 'active' ? 'green' : 'red'">{{ row.status === 'active' ? 'Aktif' : 'Nonaktif' }}</Badge>
            </template>
            <template #cell-action="{ row }">
              <div class="flex items-center justify-end gap-1">
                <button @click="openPartnerModal(row)" class="px-2.5 py-1 rounded text-xs font-medium border" style="borderColor: var(--border-color); color: var(--accent-primary);">Edit</button>
                <button @click="deletePartner(row)" class="px-2.5 py-1 rounded text-xs font-medium text-red-600 border border-red-200 hover:bg-red-50">Hapus</button>
              </div>
            </template>
          </KTable>

          <Pagination :meta="partners" />
        </div>
      </template>

      <!-- PICKUP & DELIVERY -->
      <template #pickup>
        <div class="space-y-6">
          <Skeleton v-if="!pickups" type="table" :count="5" />
          <KTable
            v-else
            :columns="pickupColumns"
            :rows="pickups?.data ?? []"
            :emptyTitle="'Belum ada data pickup'"
            :emptyDescription="'Data pickup/delivery akan muncul setelah ditambahkan.'"
            :emptyActionLabel="'+ Tambah Pickup/Delivery'"
            @empty-action="openPickupModal()"
          >
            <template #cell-customer_name="{ row }">
              <span class="font-medium">{{ row.service?.customer?.name ?? '-' }}</span>
            </template>
            <template #cell-type="{ row }">
              <Badge :variant="row.type === 'pickup' ? 'blue' : 'purple'">{{ row.type === 'pickup' ? 'Jemput (Pickup)' : 'Antar (Delivery)' }}</Badge>
            </template>
            <template #cell-address="{ row }">
              <span class="text-xs">{{ row.address ?? '-' }}</span>
            </template>
            <template #cell-status="{ row }">
              <Badge :status="row.status">{{ row.status }}</Badge>
            </template>
            <template #cell-scheduled_date="{ row }">
              {{ formatDate(row.scheduled_date) }}
            </template>
            <template #cell-action="{ row }">
              <div class="flex items-center justify-end gap-1">
                <select @change="updatePickupStatus(row, $event.target.value)" class="text-xs py-1 px-2 rounded border font-semibold bg-white" style="borderColor: var(--border-color);">
                  <option disabled selected>Ubah Status</option>
                  <option value="pending">Pending</option>
                  <option value="scheduled">Terjadwal</option>
                  <option value="in_transit">Dalam Perjalanan</option>
                  <option value="completed">Selesai</option>
                  <option value="cancelled">Batal</option>
                </select>
              </div>
            </template>
          </KTable>

          <Pagination :meta="pickups" />
        </div>
      </template>

      <!-- TRANSFER SERVIS -->
      <template #transfer>
        <div class="space-y-6">
          <Skeleton v-if="!transferServices" type="table" :count="5" />
          <KTable
            v-else
            :columns="serviceTransferColumns"
            :rows="transferServices?.data ?? transferServices ?? []"
            :emptyTitle="'Belum ada transfer servis'"
            :emptyDescription="'Data transfer servis antar cabang akan muncul setelah ditambahkan.'"
          >
            <template #cell-service_info="{ row }">
              <span class="font-medium">{{ row.customer?.name ?? '-' }}</span>
              <p class="text-xs" style="color: var(--text-muted);">{{ row.tipe_unit ?? '' }}</p>
            </template>
            <template #cell-from_branch="{ row }">
              {{ row.branch?.name ?? '-' }}
            </template>
            <template #cell-to_branch="{ row }">
              {{ row.to_branch?.name ?? '-' }}
            </template>
            <template #cell-status="{ row }">
              <Badge :status="row.status">{{ row.status }}</Badge>
            </template>
            <template #cell-created_at="{ row }">
              {{ formatDate(row.created_at) }}
            </template>
          </KTable>
        </div>
      </template>

      <!-- INDEN SPAREPART -->
      <template #inden>
        <div class="space-y-6">
          <Skeleton v-if="!indents" type="table" :count="5" />
          <template v-else>
            <KCard title="Filter Inden">
              <div class="flex flex-wrap items-center gap-3">
                <select v-model="indentFilters.status" @change="applyIndentFilter" class="text-xs font-semibold rounded-lg border px-3 py-2 bg-white text-gray-700 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 focus:outline-none" style="border-color: var(--border-color);">
                  <option value="">Semua Status</option>
                  <option value="menunggu">Menunggu</option>
                  <option value="diproses">Diproses</option>
                  <option value="selesai">Selesai</option>
                  <option value="cancel">Cancel</option>
                </select>
                <div class="relative flex-1 min-w-[180px]">
                  <input
                    type="text"
                    v-model="indentFilters.search"
                    @keyup.enter="applyIndentFilter"
                    placeholder="Cari nama barang / pelanggan..."
                    class="w-full rounded-lg border text-xs px-3 py-2 pl-8 focus:ring-2 focus:outline-none bg-white text-gray-700 focus:border-blue-500 focus:ring-blue-200 transition-all"
                    style="border-color: var(--border-color);"
                  />
                  <svg class="absolute left-2.5 top-2.5 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <button @click="resetIndentFilter" class="px-3 py-2 rounded-lg text-xs font-semibold border border-gray-200 text-gray-500 hover:bg-gray-100 transition-all bg-white">
                  ↺ Reset
                </button>
              </div>
            </KCard>

            <KTable
              :columns="indenColumns"
              :rows="indents?.data ?? []"
              :emptyTitle="'Belum ada data inden'"
              :emptyDescription="'Data inden sparepart akan muncul setelah ditambahkan.'"
              :emptyActionLabel="'+ Catat Inden Baru'"
              @empty-action="openIndenModal()"
            >
              <template #cell-customer_name="{ row }">
                <span class="font-medium">{{ row.customer?.name ?? row.service?.customer?.name ?? '-' }}</span>
              </template>
              <template #cell-product_name="{ row }">
                <span class="font-medium" style="color: var(--accent-primary);">{{ row.product_name ?? row.product?.name ?? '-' }}</span>
              </template>
              <template #cell-estimated_price="{ row }">
                Rp {{ formatNumber(row.estimated_price || 0) }}
              </template>
              <template #cell-status="{ row }">
                <Badge :status="row.status">{{ row.status }}</Badge>
              </template>
              <template #cell-created_at="{ row }">
                {{ formatDate(row.created_at) }}
              </template>
              <template #cell-action="{ row }">
                <div class="flex items-center justify-end gap-1">
                  <a :href="route('indents.print', row.id)" target="_blank" class="px-2.5 py-1 rounded text-xs font-semibold text-blue-600 border border-blue-200 hover:bg-blue-50">Nota</a>
                  <button @click="deleteInden(row)" class="px-2.5 py-1 rounded text-xs font-medium text-red-600 border border-red-200 hover:bg-red-50">Hapus</button>
                </div>
              </template>
            </KTable>

            <Pagination :meta="indents" />
          </template>
        </div>
      </template>
    </TabPage>

    <!-- MODAL / DRAWER TEMPLATE CEKLIS -->
    <Drawer :open="showCeklisDrawer" :title="editingCeklis ? 'Edit Template Ceklis' : 'Tambah Template Ceklis'" @close="showCeklisDrawer = false" width="450px">
      <form @submit.prevent="submitCeklis" class="space-y-4">
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Nama Template *</label>
          <input v-model="ceklisForm.name" required placeholder="e.g. Ceklis Handphone / Laptop" class="input text-sm" />
        </div>
        <div class="space-y-2">
          <label class="text-xs font-semibold flex items-center justify-between" style="color: var(--text-muted);">
            Item Ceklis (Daftar Fisik & Kelengkapan)
            <button type="button" @click="addCeklisItem" class="text-xs text-blue-600 font-bold hover:underline">+ Tambah Item</button>
          </label>
          <div v-for="(item, idx) in ceklisForm.items" :key="idx" class="flex gap-2 items-center">
            <input v-model="ceklisForm.items[idx]" placeholder="e.g. Layar / Baterai / Charger" required class="input text-xs flex-1" />
            <button type="button" @click="ceklisForm.items.splice(idx, 1)" class="text-red-500 font-bold text-xs px-2 py-1">✕</button>
          </div>
        </div>
        <div class="flex items-center gap-2 pt-2">
          <input type="checkbox" v-model="ceklisForm.is_active" id="ceklis_active" class="rounded" />
          <label for="ceklis_active" class="text-xs font-medium">Aktifkan Template</label>
        </div>
        <div class="flex justify-end gap-2 pt-3">
          <button type="button" @click="showCeklisDrawer = false" class="btn-secondary text-xs">Batal</button>
          <button type="submit" :disabled="ceklisForm.processing" class="btn-primary text-xs">
            {{ ceklisForm.processing ? 'Menyimpan...' : 'Simpan' }}
          </button>
        </div>
      </form>
    </Drawer>

    <!-- MODAL / DRAWER PARTNER TEKNISI -->
    <Drawer :open="showPartnerDrawer" :title="editingPartner ? 'Edit Partner' : 'Tambah Partner Teknisi'" @close="showPartnerDrawer = false" width="450px">
      <form @submit.prevent="submitPartner" class="space-y-4">
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Nama Partner *</label>
          <input v-model="partnerForm.name" required placeholder="e.g. Specialist Micro Soldering" class="input text-sm" />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Keahlian / Spesialisasi</label>
          <input v-model="partnerForm.expertise" placeholder="e.g. Reball CPU, MacBook, TV LED" class="input text-sm" />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">No. Telepon / WA *</label>
          <input v-model="partnerForm.phone" required placeholder="08xxxxxxxxxx" class="input text-sm" />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Alamat / Lokasi Workshop</label>
          <textarea v-model="partnerForm.address" rows="2" class="input text-sm"></textarea>
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Status</label>
          <select v-model="partnerForm.status" class="input text-sm">
            <option value="active">Aktif</option>
            <option value="inactive">Nonaktif</option>
          </select>
        </div>
        <div class="flex justify-end gap-2 pt-3">
          <button type="button" @click="showPartnerDrawer = false" class="btn-secondary text-xs">Batal</button>
          <button type="submit" :disabled="partnerForm.processing" class="btn-primary text-xs">
            {{ partnerForm.processing ? 'Menyimpan...' : 'Simpan' }}
          </button>
        </div>
      </form>
    </Drawer>

    <!-- MODAL / DRAWER PICKUP -->
    <Drawer :open="showPickupDrawer" title="Jadwal Pickup / Delivery Baru" @close="showPickupDrawer = false" width="450px">
      <form @submit.prevent="submitPickup" class="space-y-4">
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Tipe Layanan *</label>
          <select v-model="pickupForm.type" required class="input text-sm">
            <option value="pickup">Jemput Unit (Pickup)</option>
            <option value="delivery">Antar Unit (Delivery)</option>
          </select>
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Alamat Penjemputan / Pengantaran *</label>
          <textarea v-model="pickupForm.address" required rows="2" placeholder="Jl. Anggrek No. 123..." class="input text-sm"></textarea>
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Tanggal & Waktu Penjadwalan *</label>
          <input v-model="pickupForm.scheduled_date" type="datetime-local" required class="input text-sm" />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Catatan / Instruksi Driver</label>
          <textarea v-model="pickupForm.note" rows="2" placeholder="Hubungi wa dulu sebelum sampai..." class="input text-sm"></textarea>
        </div>
        <div class="flex justify-end gap-2 pt-3">
          <button type="button" @click="showPickupDrawer = false" class="btn-secondary text-xs">Batal</button>
          <button type="submit" :disabled="pickupForm.processing" class="btn-primary text-xs">
            {{ pickupForm.processing ? 'Menyimpan...' : 'Simpan' }}
          </button>
        </div>
      </form>
    </Drawer>

    <!-- MODAL / DRAWER INDEN SPAREPART -->
    <Drawer :open="showIndenDrawer" title="Catat Inden Sparepart Baru" @close="showIndenDrawer = false" width="450px">
      <form @submit.prevent="submitInden" class="space-y-4">
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Nama Barang / Sparepart *</label>
          <input v-model="indenForm.product_name" required placeholder="e.g. LCD OLED iPhone 13 Pro Max Original" class="input text-sm" />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Estimasi Harga (Rp)</label>
          <input v-model="indenForm.estimated_price" type="number" min="0" placeholder="0" class="input text-sm" />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Uang Muka / DP (Rp)</label>
          <input v-model="indenForm.down_payment" type="number" min="0" placeholder="0" class="input text-sm" />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Catatan / Deskripsi</label>
          <textarea v-model="indenForm.notes" rows="2" placeholder="Pesanan warna Hitam..." class="input text-sm"></textarea>
        </div>
        <div class="flex justify-end gap-2 pt-3">
          <button type="button" @click="showIndenDrawer = false" class="btn-secondary text-xs">Batal</button>
          <button type="submit" :disabled="indenForm.processing" class="btn-primary text-xs">
            {{ indenForm.processing ? 'Menyimpan...' : 'Simpan' }}
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
  activeTab: { type: String, default: 'ceklis' },
  templates: { type: [Object, Array], default: null },
  partners: { type: Object, default: null },
  pickups: { type: Object, default: null },
  indents: { type: Object, default: null },
  indentFilters: { type: Object, default: () => ({ status: '', search: '' }) },
  transferServices: { type: Object, default: null },
  transferBranches: { type: Array, default: () => [] },
});

const activeTab = ref(props.activeTab);

// Drawer states
const showCeklisDrawer = ref(false);
const editingCeklis = ref(null);
const ceklisForm = useForm({ name: '', items: ['Layar / Touchscreen', 'Baterai', 'Body / Fisik'], is_active: true });

const showPartnerDrawer = ref(false);
const editingPartner = ref(null);
const partnerForm = useForm({ name: '', expertise: '', phone: '', address: '', status: 'active' });

const showPickupDrawer = ref(false);
const pickupForm = useForm({ type: 'pickup', address: '', scheduled_date: '', note: '' });

const showIndenDrawer = ref(false);
const indenForm = useForm({ product_name: '', estimated_price: 0, down_payment: 0, notes: '' });

// Ceklis Template handlers
const openCeklisModal = (row = null) => {
  editingCeklis.value = row;
  if (row) {
    ceklisForm.name = row.name;
    ceklisForm.items = Array.isArray(row.items) ? [...row.items] : [];
    ceklisForm.is_active = Boolean(row.is_active);
  } else {
    ceklisForm.reset();
  }
  showCeklisDrawer.value = true;
};
const addCeklisItem = () => { ceklisForm.items.push(''); };
const submitCeklis = () => {
  const url = editingCeklis.value ? route('checklist-templates.update', editingCeklis.value.id) : route('checklist-templates.store');
  const method = editingCeklis.value ? 'put' : 'post';
  ceklisForm[method](url, { preserveScroll: true, onSuccess: () => { showCeklisDrawer.value = false; } });
};
const deleteTemplate = (row) => {
  if (confirm(`Hapus template ceklis "${row.name}"?`)) {
    router.delete(route('checklist-templates.destroy', row.id), { preserveScroll: true });
  }
};

// Partner handlers
const openPartnerModal = (row = null) => {
  editingPartner.value = row;
  if (row) {
    partnerForm.name = row.name;
    partnerForm.expertise = row.expertise || '';
    partnerForm.phone = row.phone || '';
    partnerForm.address = row.address || '';
    partnerForm.status = row.status || 'active';
  } else {
    partnerForm.reset();
  }
  showPartnerDrawer.value = true;
};
const submitPartner = () => {
  const url = editingPartner.value ? route('partner-teknisi.update', editingPartner.value.id) : route('partner-teknisi.store');
  const method = editingPartner.value ? 'put' : 'post';
  partnerForm[method](url, { preserveScroll: true, onSuccess: () => { showPartnerDrawer.value = false; } });
};
const deletePartner = (row) => {
  if (confirm(`Hapus partner "${row.name}"?`)) {
    router.delete(route('partner-teknisi.destroy', row.id), { preserveScroll: true });
  }
};

// Pickup handlers
const openPickupModal = () => {
  pickupForm.reset();
  showPickupDrawer.value = true;
};
const submitPickup = () => {
  pickupForm.post(route('pickup-deliveries.store'), { preserveScroll: true, onSuccess: () => { showPickupDrawer.value = false; } });
};
const updatePickupStatus = (row, newStatus) => {
  router.post(route('pickup-deliveries.status', row.id), { status: newStatus }, { preserveScroll: true });
};

// Inden handlers
const openIndenModal = () => {
  indenForm.reset();
  showIndenDrawer.value = true;
};
const submitInden = () => {
  indenForm.post(route('indents.store'), { preserveScroll: true, onSuccess: () => { showIndenDrawer.value = false; } });
};
const deleteInden = (row) => {
  if (confirm(`Hapus data inden "${row.product_name || row.product?.name}"?`)) {
    router.delete(route('indents.destroy', row.id), { preserveScroll: true });
  }
};

const tabs = [
  { key: 'ceklis', label: 'Ceklis' },
  { key: 'partner', label: 'Partner' },
  { key: 'pickup', label: 'Pickup' },
  { key: 'transfer', label: 'Transfer' },
  { key: 'inden', label: 'Inden' },
];

const tabLabels = { ceklis: 'Ceklis', partner: 'Partner', pickup: 'Pickup', transfer: 'Transfer', inden: 'Inden' };
const pageTitle = computed(() => 'Servis Tools — ' + (tabLabels[activeTab.value] || 'Ceklis'));
const subtitle = computed(() => currentDate.value);

const ceklisColumns = [
  { key: 'name', label: 'Nama' },
  { key: 'item_count', label: 'Item' },
  { key: 'is_active', label: 'Aktif' },
  { key: 'created_at', label: 'Tanggal' },
  { key: 'action', label: '', align: 'right' },
];

const partnerColumns = [
  { key: 'name', label: 'Nama' },
  { key: 'expertise', label: 'Keahlian' },
  { key: 'phone', label: 'Telepon' },
  { key: 'status', label: 'Status' },
  { key: 'action', label: '', align: 'right' },
];

const pickupColumns = [
  { key: 'customer_name', label: 'Pelanggan' },
  { key: 'type', label: 'Tipe' },
  { key: 'address', label: 'Alamat' },
  { key: 'status', label: 'Status' },
  { key: 'scheduled_date', label: 'Tanggal' },
  { key: 'action', label: '', align: 'right' },
];

const serviceTransferColumns = [
  { key: 'service_info', label: 'Servis' },
  { key: 'from_branch', label: 'Dari' },
  { key: 'to_branch', label: 'Ke' },
  { key: 'status', label: 'Status' },
  { key: 'created_at', label: 'Tanggal' },
];

const indenColumns = [
  { key: 'customer_name', label: 'Pelanggan' },
  { key: 'product_name', label: 'Barang' },
  { key: 'estimated_price', label: 'Est. Harga', align: 'right' },
  { key: 'status', label: 'Status' },
  { key: 'created_at', label: 'Tanggal' },
  { key: 'action', label: '', align: 'right' },
];

const switchTab = (key) => {
  router.get(route('servis-tools.index'), { tab: key }, { preserveState: true, preserveScroll: true });
};

const applyIndentFilter = () => {
  router.get(route('servis-tools.index'), {
    tab: activeTab.value,
    ...props.indentFilters,
  }, { preserveState: true, preserveScroll: true });
};

const resetIndentFilter = () => {
  router.get(route('servis-tools.index'), { tab: activeTab.value }, { preserveState: true, preserveScroll: true });
};
</script>

