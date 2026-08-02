<template>
  <AuthenticatedLayout>
    <template #header>
      <PageHeader :title="pageTitle" :subtitle="subtitle">
        <KButton 
          v-if="activeTab === 'kb'"
          @click="openKbDrawer()"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold text-white transition-all hover:shadow-md cursor-pointer bg-indigo-600 text-white">
          + Artikel KB Baru
        </KButton>
        <KButton 
          v-if="activeTab === 'sop'"
          @click="openSopDrawer()"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold text-white transition-all hover:shadow-md cursor-pointer bg-indigo-600 text-white">
          + SOP Baru
        </KButton>
        <KButton 
          v-if="activeTab === 'balasan'"
          @click="openReplyModal()"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold text-white transition-all hover:shadow-md cursor-pointer bg-indigo-600 text-white">
          + Balasan Cepat Baru
        </KButton>
      </PageHeader>
    </template>

    <TabPage :tabs="tabs" v-model="activeTab" @update:model-value="switchTab">
      <!-- KNOWLEDGE BASE -->
      <template #kb>
        <div class="space-y-6">
          <Skeleton v-if="!articles" type="table" :count="5" />
          <KTable
            v-else
            :columns="kbColumns"
            :rows="articles?.data ?? []"
            hoverable
            :emptyTitle="'Belum ada artikel KB'"
            :emptyDescription="'Artikel pengetahuan panduan perbaikan servis akan muncul di sini.'"
            :emptyActionLabel="'+ Tambah Artikel KB'"
            @empty-action="openKbDrawer()"
          >
            <template #cell-title="{ row }">
              <span class="font-medium text-sm">{{ row.judul }}</span>
              <p class="text-[11px] text-zinc-500">Penulis: {{ row.creator?.name || 'Admin' }}</p>
            </template>
            <template #cell-device_type="{ row }">
              <span class="text-xs font-semibold px-2 py-0.5 rounded bg-zinc-50">{{ row.device_type ?? 'Umum' }}</span>
            </template>
            <template #cell-created_at="{ row }">
              {{ formatDate(row.created_at) }}
            </template>
            <template #cell-action="{ row }">
              <div class="flex items-center justify-end gap-1">
                <KButton  @click="openKbDetail(row)" class="px-2.5 py-1 rounded text-xs font-semibold text-blue-600 border border-blue-200 hover:bg-blue-50">Baca</KButton>
                <KButton  @click="openKbDrawer(row)" class="px-2.5 py-1 rounded text-xs font-medium border border-zinc-200 text-indigo-600">Edit</KButton>
                <KButton  @click="deleteKb(row)" class="px-2.5 py-1 rounded text-xs font-medium text-red-600 border border-red-200 hover:bg-red-50">Hapus</KButton>
              </div>
            </template>
          </KTable>

          <Pagination :meta="articles" />
        </div>
      </template>

      <!-- SOP PROSEDUR -->
      <template #sop>
        <div class="space-y-6">
          <Skeleton v-if="!sops" type="table" :count="5" />
          <KTable
            v-else
            :columns="sopColumns"
            :rows="sops?.data ?? []"
            hoverable
            :emptyTitle="'Belum ada data SOP'"
            :emptyDescription="'Dokumen Prosedur Operasional Standar akan muncul setelah ditambahkan.'"
            :emptyActionLabel="'+ Buat SOP Baru'"
            @empty-action="openSopDrawer()"
          >
            <template #cell-title="{ row }">
              <span class="font-medium text-sm">{{ row.title }}</span>
            </template>
            <template #cell-target_role="{ row }">
              <Badge variant="blue">{{ (row.target_roles ?? []).length ? row.target_roles.join(', ') : 'Semua Role' }}</Badge>
            </template>
            <template #cell-created_at="{ row }">
              {{ formatDate(row.created_at) }}
            </template>
            <template #cell-action="{ row }">
              <div class="flex items-center justify-end gap-1">
                <KButton  @click="openSopDetail(row)" class="px-2.5 py-1 rounded text-xs font-semibold text-blue-600 border border-blue-200 hover:bg-blue-50">Lihat</KButton>
                <KButton  @click="openSopDrawer(row)" class="px-2.5 py-1 rounded text-xs font-medium border border-zinc-200 text-indigo-600">Edit</KButton>
                <KButton  @click="deleteSop(row)" class="px-2.5 py-1 rounded text-xs font-medium text-red-600 border border-red-200 hover:bg-red-50">Hapus</KButton>
              </div>
            </template>
          </KTable>

          <Pagination :meta="sops" />
        </div>
      </template>

      <!-- BALASAN CEPAT -->
      <template #balasan>
        <div class="space-y-6">
          <Skeleton v-if="!quickReplies" type="table" :count="5" />
          <KTable
            v-else
            :columns="replyColumns"
            :rows="quickReplies?.data ?? quickReplies ?? []"
            hoverable
            :emptyTitle="'Belum ada balasan cepat'"
            :emptyDescription="'Template balasan cepat pesan WA/Chat akan muncul di sini.'"
            :emptyActionLabel="'+ Tambah Balasan Cepat'"
            @empty-action="openReplyModal()"
          >
            <template #cell-keyword="{ row }">
              <span class="font-mono text-xs font-bold text-indigo-600">/{{ row.keyword }}</span>
            </template>
            <template #cell-reply="{ row }">
              <span class="text-xs text-zinc-600" :title="row.reply">
                {{ truncateText(row.reply, 80) }}
              </span>
            </template>
            <template #cell-action="{ row }">
              <div class="flex items-center justify-end gap-1">
                <KButton  @click="openReplyModal(row)" class="px-2.5 py-1 rounded text-xs font-medium border border-zinc-200 text-indigo-600">Edit</KButton>
                <KButton  @click="deleteReply(row)" class="px-2.5 py-1 rounded text-xs font-medium text-red-600 border border-red-200 hover:bg-red-50">Hapus</KButton>
              </div>
            </template>
          </KTable>
        </div>
      </template>
    </TabPage>

    <!-- DRAWER BALASAN CEPAT -->
    <Drawer :open="showReplyDrawer" :title="editingReply ? 'Edit Balasan Cepat' : 'Tambah Balasan Cepat Baru'" @close="showReplyDrawer = false" width="450px">
      <form @submit.prevent="submitReply" class="space-y-4">
        <div class="space-y-1">
          <label class="text-xs font-semibold text-zinc-500">Kata Kunci (Shortcut) *</label>
          <div class="flex items-center gap-1">
            <span class="text-sm font-bold font-mono text-gray-500">/</span>
            <KInput  v-model="replyForm.keyword" required placeholder="e.g. rekening / lokasi / jam_buka" class="input text-sm flex-1" />
          </div>
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold text-zinc-500">Teks Balasan Lengkap *</label>
          <KTextarea  v-model="replyForm.reply" rows="4" required placeholder="Tuliskan isi pesan otomatis..." class="input text-sm"></KTextarea>
        </div>
        <div class="flex justify-end gap-2 pt-3">
          <KButton  type="button" @click="showReplyDrawer = false" class="btn-secondary text-xs">Batal</KButton>
          <KButton  type="submit" :disabled="replyForm.processing" class="btn-primary text-xs">
            {{ replyForm.processing ? 'Menyimpan...' : 'Simpan Balasan' }}
          </KButton>
        </div>
      </form>
    </Drawer>

    <!-- DRAWER KNOWLEDGE BASE (TAMBAH / EDIT) -->
    <Drawer :open="showKbDrawer" :title="editingKb ? 'Edit Artikel KB' : 'Tambah Artikel KB'" @close="showKbDrawer = false" width="520px">
      <form @submit.prevent="submitKb" class="space-y-4">
        <div class="space-y-1">
          <label class="text-xs font-semibold text-zinc-500">Judul *</label>
          <KInput  v-model="kbForm.judul" type="text" required class="input text-sm" placeholder="Judul artikel" />
        </div>
        <div class="grid grid-cols-3 gap-3">
          <div class="space-y-1">
            <label class="text-xs font-semibold text-zinc-500">Tipe Device</label>
            <KSelect  v-model="kbForm.device_type" class="input text-sm">
              <option value="">Umum</option>
              <option v-for="t in kbDeviceTypes" :key="t" :value="t">{{ t }}</option>
            </KSelect>
          </div>
          <div class="space-y-1">
            <label class="text-xs font-semibold text-zinc-500">Brand</label>
            <KSelect  v-model="kbForm.device_brand" class="input text-sm">
              <option value="">-</option>
              <option v-for="b in kbBrands" :key="b" :value="b">{{ b }}</option>
            </KSelect>
          </div>
          <div class="space-y-1">
            <label class="text-xs font-semibold text-zinc-500">Model</label>
            <KInput  v-model="kbForm.device_model" type="text" class="input text-sm" placeholder="e.g. A52" />
          </div>
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold text-zinc-500">Masalah *</label>
          <KTextarea  v-model="kbForm.masalah" rows="2" required class="input text-sm" placeholder="Gejala / masalah..."></KTextarea>
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold text-zinc-500">Solusi *</label>
          <KTextarea  v-model="kbForm.solusi" rows="4" required class="input text-sm" placeholder="Langkah solusi..."></KTextarea>
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold text-zinc-500">Lampiran Gambar (opsional)</label>
          <KInput  type="file" accept="image/*" @change="kbForm.lampiran = $event.target.files[0] || null" class="input text-sm" />
        </div>
        <div class="flex justify-end gap-2 pt-3">
          <KButton  type="button" @click="showKbDrawer = false" class="btn-secondary text-xs">Batal</KButton>
          <KButton  type="submit" :disabled="kbForm.processing" class="btn-primary text-xs">
            {{ kbForm.processing ? 'Menyimpan...' : 'Simpan Artikel' }}
          </KButton>
        </div>
      </form>
    </Drawer>

    <!-- DRAWER DETAIL KNOWLEDGE BASE -->
    <Drawer :open="showKbDetailDrawer" title="Detail Artikel KB" @close="showKbDetailDrawer = false" width="520px">
      <div v-if="selectedKb" class="space-y-4">
        <div>
          <h3 class="text-base font-bold text-zinc-900">{{ selectedKb.judul }}</h3>
          <p class="text-[11px] text-zinc-500">
            {{ selectedKb.device_type ?? 'Umum' }}{{ selectedKb.device_brand ? ' / ' + selectedKb.device_brand : '' }}{{ selectedKb.device_model ? ' / ' + selectedKb.device_model : '' }}
          </p>
        </div>
        <div class="rounded-lg border p-3 space-y-2 border-zinc-200">
          <p class="text-xs font-bold text-zinc-500">Masalah</p>
          <p class="text-sm whitespace-pre-wrap">{{ selectedKb.masalah }}</p>
        </div>
        <div class="rounded-lg border p-3 space-y-2 border-zinc-200">
          <p class="text-xs font-bold text-zinc-500">Solusi</p>
          <p class="text-sm whitespace-pre-wrap">{{ selectedKb.solusi }}</p>
        </div>
        <div v-if="selectedKb.lampiran" class="space-y-1">
          <label class="text-xs font-semibold text-zinc-500">Lampiran</label>
          <a :href="'/storage/' + selectedKb.lampiran" target="_blank" class="text-xs font-bold text-blue-600 hover:underline">Lihat gambar ↗</a>
        </div>
      </div>
    </Drawer>

    <!-- DRAWER SOP (TAMBAH / EDIT) -->
    <Drawer :open="showSopDrawer" :title="editingSop ? 'Edit SOP' : 'Buat SOP Baru'" @close="showSopDrawer = false" width="520px">
      <form @submit.prevent="submitSop" class="space-y-4">
        <div class="space-y-1">
          <label class="text-xs font-semibold text-zinc-500">Judul SOP *</label>
          <KInput  v-model="sopForm.title" type="text" required class="input text-sm" placeholder="Judul prosedur" />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold text-zinc-500">Isi Prosedur *</label>
          <KTextarea  v-model="sopForm.content" rows="6" required class="input text-sm" placeholder="Langkah-langkah prosedur..."></KTextarea>
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold text-zinc-500">Target Role</label>
          <div class="flex flex-wrap gap-2">
            <label v-for="r in sopRoles" :key="r" class="inline-flex items-center gap-1.5 text-xs cursor-pointer">
              <KCheckbox  :value="r" v-model="sopForm.target_roles" class="w-3.5 h-3.5 rounded accent-purple-600" />
              {{ r }}
            </label>
          </div>
          <p class="text-[11px] text-zinc-500">Kosongkan jika berlaku untuk semua role.</p>
        </div>
        <label class="inline-flex items-center gap-2 text-xs cursor-pointer">
          <KCheckbox  v-model="sopForm.is_mandatory" class="w-3.5 h-3.5 rounded accent-purple-600" />
          Wajib dibaca semua karyawan
        </label>
        <div class="flex justify-end gap-2 pt-3">
          <KButton  type="button" @click="showSopDrawer = false" class="btn-secondary text-xs">Batal</KButton>
          <KButton  type="submit" :disabled="sopForm.processing" class="btn-primary text-xs">
            {{ sopForm.processing ? 'Menyimpan...' : 'Simpan SOP' }}
          </KButton>
        </div>
      </form>
    </Drawer>

    <!-- DRAWER DETAIL SOP -->
    <Drawer :open="showSopDetailDrawer" title="Detail SOP" @close="showSopDetailDrawer = false" width="520px">
      <div v-if="selectedSop" class="space-y-4">
        <div class="flex items-start justify-between gap-3">
          <h3 class="text-base font-bold text-zinc-900">{{ selectedSop.title }}</h3>
          <Badge variant="blue">{{ (selectedSop.target_roles ?? []).length ? selectedSop.target_roles.join(', ') : 'Semua Role' }}</Badge>
        </div>
        <div class="rounded-lg border p-3 border-zinc-200">
          <p class="text-sm whitespace-pre-wrap">{{ selectedSop.content }}</p>
        </div>
        <p class="text-[11px] text-zinc-500">Versi {{ selectedSop.version ?? 1 }} · {{ selectedSop.is_mandatory ? 'Wajib dibaca' : 'Opsional' }}</p>
      </div>
    </Drawer>
  </AuthenticatedLayout>
</template>

<script setup>
import KButton from '@/Components/KButton.vue';
import KInput from '@/Components/KInput.vue';
import KSelect from '@/Components/KSelect.vue';
import KTextarea from '@/Components/KTextarea.vue';
import KCheckbox from '@/Components/KCheckbox.vue';

import { computed, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
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
  activeTab: { type: String, default: 'kb' },
  articles: { type: Object, default: null },
  kbDeviceTypes: { type: Array, default: () => [] },
  kbBrands: { type: Array, default: () => [] },
  sops: { type: Object, default: null },
  sopRoles: { type: Array, default: () => [] },
  quickReplies: { type: [Object, Array], default: null },
});

const activeTab = ref(props.activeTab);

// Quick Reply Drawer
const showReplyDrawer = ref(false);
const editingReply = ref(null);
const replyForm = useForm({ keyword: '', reply: '' });

const openReplyModal = (row = null) => {
  editingReply.value = row;
  if (row) {
    replyForm.keyword = row.keyword;
    replyForm.reply = row.reply;
  } else {
    replyForm.reset();
  }
  showReplyDrawer.value = true;
};

const submitReply = () => {
  const url = editingReply.value ? route('quick-replies.update', editingReply.value.id) : route('quick-replies.store');
  const method = editingReply.value ? 'put' : 'post';
  replyForm[method](url, { preserveScroll: true, onSuccess: () => { showReplyDrawer.value = false; } });
};

const deleteReply = (row) => {
  if (confirm(`Hapus balasan cepat "/${row.keyword}"?`)) {
    router.delete(route('quick-replies.destroy', row.id), { preserveScroll: true });
  }
};

const deleteKb = (row) => {
  if (confirm(`Hapus artikel KB "${row.judul}"?`)) {
    router.delete(route('knowledge-base.destroy', row.id), { preserveScroll: true });
  }
};

const deleteSop = (row) => {
  if (confirm(`Hapus SOP "${row.title}"?`)) {
    router.delete(route('sops.destroy', row.id), { preserveScroll: true });
  }
};

// ==== Drawer Knowledge Base (tambah / edit) ====
const showKbDrawer = ref(false);
const editingKb = ref(null);
const kbForm = useForm({ judul: '', device_type: '', device_brand: '', device_model: '', masalah: '', solusi: '', lampiran: null });

const openKbDrawer = (row = null) => {
  editingKb.value = row;
  if (row) {
    kbForm.setData({
      judul: row.judul, device_type: row.device_type || '', device_brand: row.device_brand || '',
      device_model: row.device_model || '', masalah: row.masalah || '', solusi: row.solusi || '', lampiran: null,
    });
  } else {
    kbForm.reset();
  }
  showKbDrawer.value = true;
};

const submitKb = () => {
  const url = editingKb.value ? route('knowledge-base.update', editingKb.value.id) : route('knowledge-base.store');
  const method = editingKb.value ? 'put' : 'post';
  kbForm[method](url, { preserveScroll: true, onSuccess: () => { showKbDrawer.value = false; } });
};

// ==== Drawer Detail KB ====
const showKbDetailDrawer = ref(false);
const selectedKb = ref(null);
const openKbDetail = (row) => {
  selectedKb.value = row;
  showKbDetailDrawer.value = true;
};

// ==== Drawer SOP (tambah / edit) ====
const showSopDrawer = ref(false);
const editingSop = ref(null);
const sopForm = useForm({ title: '', content: '', target_roles: [], is_mandatory: false });

const openSopDrawer = (row = null) => {
  editingSop.value = row;
  if (row) {
    sopForm.setData({ title: row.title, content: row.content || '', target_roles: row.target_roles || [], is_mandatory: !!row.is_mandatory });
  } else {
    sopForm.reset();
    sopForm.target_roles = [];
  }
  showSopDrawer.value = true;
};

const submitSop = () => {
  const url = editingSop.value ? route('sops.update', editingSop.value.id) : route('sops.store');
  const method = editingSop.value ? 'put' : 'post';
  sopForm[method](url, { preserveScroll: true, onSuccess: () => { showSopDrawer.value = false; } });
};

// ==== Drawer Detail SOP ====
const showSopDetailDrawer = ref(false);
const selectedSop = ref(null);
const openSopDetail = (row) => {
  selectedSop.value = row;
  showSopDetailDrawer.value = true;
};

const tabs = [
  { key: 'kb', label: 'KB' },
  { key: 'sop', label: 'SOP' },
  { key: 'balasan', label: 'Balasan Cepat' },
];

const tabLabels = { kb: 'KB', sop: 'SOP', balasan: 'Balasan Cepat' };
const pageTitle = computed(() => 'Dokumen — ' + (tabLabels[activeTab.value] || 'KB'));
const subtitle = computed(() => currentDate.value);

const kbColumns = [
  { key: 'title', label: 'Judul Artikel' },
  { key: 'device_type', label: 'Tipe' },
  { key: 'created_at', label: 'Tanggal' },
  { key: 'action', label: '', align: 'right' },
];

const sopColumns = [
  { key: 'title', label: 'Judul SOP' },
  { key: 'target_role', label: 'Target Role' },
  { key: 'created_at', label: 'Tanggal' },
  { key: 'action', label: '', align: 'right' },
];

const replyColumns = [
  { key: 'keyword', label: 'Kata Kunci' },
  { key: 'reply', label: 'Teks Balasan' },
  { key: 'action', label: '', align: 'right' },
];

const truncateText = (text, maxLength) => {
  if (!text) return '-';
  return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
};

const switchTab = (key) => {
  router.get(route('dokumen.index'), { tab: key }, { preserveState: true, preserveScroll: true });
};
</script>

