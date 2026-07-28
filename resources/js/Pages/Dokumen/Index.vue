<template>
  <AuthenticatedLayout>
    <template #header>
      <PageHeader :title="pageTitle" :subtitle="subtitle">
        <Link
          v-if="activeTab === 'kb'"
          :href="route('knowledge-base.create')"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold text-white transition-all hover:shadow-md cursor-pointer"
          style="background: var(--accent-primary);"
        >
          + Artikel KB Baru
        </Link>
        <Link
          v-if="activeTab === 'sop'"
          :href="route('sops.create')"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold text-white transition-all hover:shadow-md cursor-pointer"
          style="background: var(--accent-primary);"
        >
          + SOP Baru
        </Link>
        <button
          v-if="activeTab === 'balasan'"
          @click="openReplyModal()"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold text-white transition-all hover:shadow-md cursor-pointer"
          style="background: var(--accent-primary);"
        >
          + Balasan Cepat Baru
        </button>
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
            @empty-action="router.visit(route('knowledge-base.create'))"
          >
            <template #cell-title="{ row }">
              <span class="font-medium text-sm">{{ row.title }}</span>
              <p class="text-[11px]" style="color: var(--text-muted);">Penulis: {{ row.creator?.name || 'Admin' }}</p>
            </template>
            <template #cell-device_type="{ row }">
              <span class="text-xs font-semibold px-2 py-0.5 rounded" style="background: var(--bg-hover);">{{ row.device_type ?? 'Umum' }}</span>
            </template>
            <template #cell-created_at="{ row }">
              {{ formatDate(row.created_at) }}
            </template>
            <template #cell-action="{ row }">
              <div class="flex items-center justify-end gap-1">
                <Link :href="route('knowledge-base.show', row.id)" class="px-2.5 py-1 rounded text-xs font-semibold text-blue-600 border border-blue-200 hover:bg-blue-50">Baca</Link>
                <Link :href="route('knowledge-base.edit', row.id)" class="px-2.5 py-1 rounded text-xs font-medium border" style="borderColor: var(--border-color); color: var(--accent-primary);">Edit</Link>
                <button @click="deleteKb(row)" class="px-2.5 py-1 rounded text-xs font-medium text-red-600 border border-red-200 hover:bg-red-50">Hapus</button>
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
            @empty-action="router.visit(route('sops.create'))"
          >
            <template #cell-title="{ row }">
              <span class="font-medium text-sm">{{ row.title }}</span>
            </template>
            <template #cell-target_role="{ row }">
              <Badge variant="blue">{{ row.target_role ?? 'Semua Role' }}</Badge>
            </template>
            <template #cell-created_at="{ row }">
              {{ formatDate(row.created_at) }}
            </template>
            <template #cell-action="{ row }">
              <div class="flex items-center justify-end gap-1">
                <Link :href="route('sops.show', row.id)" class="px-2.5 py-1 rounded text-xs font-semibold text-blue-600 border border-blue-200 hover:bg-blue-50">Lihat</Link>
                <Link :href="route('sops.edit', row.id)" class="px-2.5 py-1 rounded text-xs font-medium border" style="borderColor: var(--border-color); color: var(--accent-primary);">Edit</Link>
                <button @click="deleteSop(row)" class="px-2.5 py-1 rounded text-xs font-medium text-red-600 border border-red-200 hover:bg-red-50">Hapus</button>
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
              <span class="font-mono text-xs font-bold" style="color: var(--accent-primary);">/{{ row.keyword }}</span>
            </template>
            <template #cell-reply="{ row }">
              <span class="text-xs" style="color: var(--text-secondary);" :title="row.reply">
                {{ truncateText(row.reply, 80) }}
              </span>
            </template>
            <template #cell-action="{ row }">
              <div class="flex items-center justify-end gap-1">
                <button @click="openReplyModal(row)" class="px-2.5 py-1 rounded text-xs font-medium border" style="borderColor: var(--border-color); color: var(--accent-primary);">Edit</button>
                <button @click="deleteReply(row)" class="px-2.5 py-1 rounded text-xs font-medium text-red-600 border border-red-200 hover:bg-red-50">Hapus</button>
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
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Kata Kunci (Shortcut) *</label>
          <div class="flex items-center gap-1">
            <span class="text-sm font-bold font-mono text-gray-500">/</span>
            <input v-model="replyForm.keyword" required placeholder="e.g. rekening / lokasi / jam_buka" class="input text-sm flex-1" />
          </div>
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold" style="color: var(--text-muted);">Teks Balasan Lengkap *</label>
          <textarea v-model="replyForm.reply" rows="4" required placeholder="Tuliskan isi pesan otomatis..." class="input text-sm"></textarea>
        </div>
        <div class="flex justify-end gap-2 pt-3">
          <button type="button" @click="showReplyDrawer = false" class="btn-secondary text-xs">Batal</button>
          <button type="submit" :disabled="replyForm.processing" class="btn-primary text-xs">
            {{ replyForm.processing ? 'Menyimpan...' : 'Simpan Balasan' }}
          </button>
        </div>
      </form>
    </Drawer>
  </AuthenticatedLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
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
  if (confirm(`Hapus artikel KB "${row.title}"?`)) {
    router.delete(route('knowledge-base.destroy', row.id), { preserveScroll: true });
  }
};

const deleteSop = (row) => {
  if (confirm(`Hapus SOP "${row.title}"?`)) {
    router.delete(route('sops.destroy', row.id), { preserveScroll: true });
  }
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

