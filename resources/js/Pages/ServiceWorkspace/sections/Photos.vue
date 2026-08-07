<template>
  <div class="space-y-5">
    <!-- ═══════════ UPLOAD FORM ═══════════ -->
    <SkCard v-if="canUpload" title="📸 Upload Foto" size="md">
      <div
        class="border-2 border-dashed rounded-xl p-6 text-center cursor-pointer transition-colors"
        :style="{ borderColor: isDragging ? 'var(--primary)' : 'var(--border-light)', background: isDragging ? 'var(--primary-soft)' : 'var(--bg-surface)' }"
        @dragover.prevent="isDragging = true"
        @dragleave.prevent="isDragging = false"
        @drop.prevent="handleDrop"
        @click="fileInput?.click()"
      >
        <svg class="w-10 h-10 mx-auto mb-2" :style="{ color: 'var(--text-muted)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <p class="text-sm font-medium" :style="{ color: 'var(--text-primary)' }">Klik atau tarik foto ke sini</p>
        <p class="text-[10px] mt-1" :style="{ color: 'var(--text-muted)' }">JPG, PNG, WebP · Maks 10MB per file · Maks 10 file</p>
        <input
          ref="fileInput"
          type="file"
          accept="image/jpeg,image/png,image/webp"
          multiple
          class="hidden"
          @change="handleFileSelect"
        />
      </div>

      <!-- Preview selected files -->
      <div v-if="previewFiles.length" class="mt-3 grid grid-cols-4 gap-2">
        <div v-for="(file, idx) in previewFiles" :key="idx" class="relative rounded-lg overflow-hidden aspect-square border" :style="{ borderColor: 'var(--border-light)' }">
          <img :src="file.preview" class="w-full h-full object-cover" />
          <button @click="removePreview(idx)" class="absolute top-1 right-1 w-5 h-5 rounded-full bg-red-500 text-white flex items-center justify-center text-[10px] font-bold">×</button>
        </div>
      </div>

      <!-- Upload button -->
      <button
        v-if="previewFiles.length"
        @click="uploadPhotos"
        :disabled="uploadLoading"
        class="mt-3 w-full px-4 py-2.5 rounded-xl text-sm font-bold text-white transition hover:opacity-90 disabled:opacity-50 flex items-center justify-center gap-2"
        style="background: var(--primary)"
      >
        <span v-if="uploadLoading" class="animate-spin">⏳</span>
        📤 Upload {{ previewFiles.length }} Foto
      </button>

      <div v-if="uploadError" class="mt-2 p-2 rounded-lg text-xs font-medium" :style="{ background: 'var(--danger-soft)', color: 'var(--danger-text)' }">
        {{ uploadError }}
      </div>
    </SkCard>

    <!-- ═══════════ PHOTO GRID ═══════════ -->
    <SkCard title="🖼️ Galeri Foto" size="md">
    <div v-if="!photos.length" class="py-8">
      <SkEmptyState variant="empty" title="Belum ada foto" description="Foto servis akan muncul di sini." />
    </div>

    <div v-else class="grid grid-cols-2 sm:grid-cols-3 gap-3">
      <div
        v-for="photo in photos"
        :key="photo.id"
        class="relative group rounded-xl overflow-hidden border aspect-square cursor-pointer"
        :style="{ borderColor: 'var(--border-light)' }"
        @click="selectedPhoto = photo"
      >
        <img
          :src="photo.thumbnail || photo.url"
          :alt="photo.category"
          class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
          loading="lazy"
        />

        <!-- Category badge -->
        <div class="absolute top-2 left-2">
          <span class="text-[9px] font-bold uppercase px-1.5 py-0.5 rounded-md bg-black/60 text-white">
            {{ photo.category || 'Umum' }}
          </span>
        </div>

        <!-- Delete button -->
        <button
          v-if="canDelete"
          @click.stop="deletePhoto(photo.id)"
          class="absolute top-2 right-2 w-6 h-6 rounded-full bg-red-500/80 hover:bg-red-600 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <!-- Hover overlay -->
        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors flex items-center justify-center">
          <svg class="w-8 h-8 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
          </svg>
        </div>
      </div>
    </div>

    <!-- Lightbox -->
    <Teleport to="body">
      <div
        v-if="selectedPhoto"
        class="fixed inset-0 z-[200] bg-black/90 flex items-center justify-center p-4"
        @click="selectedPhoto = null"
      >
        <button
          @click="selectedPhoto = null"
          class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/10 flex items-center justify-center text-white hover:bg-white/20 transition-colors"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
        <img
          :src="selectedPhoto.url"
          class="max-w-full max-h-[90vh] object-contain rounded-2xl"
          @click.stop
        />
        <p class="absolute bottom-4 left-1/2 -translate-x-1/2 text-white text-xs bg-black/60 px-3 py-1.5 rounded-full">
          {{ selectedPhoto.category || 'Umum' }}
          <span v-if="selectedPhoto.uploaded_by"> · {{ selectedPhoto.uploaded_by }}</span>
        </p>
      </div>
    </Teleport>
  </SkCard>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import SkCard from '@/Enterprise/Components/Cards/Card.vue';
import SkEmptyState from '@/Enterprise/Components/Empty/EmptyState.vue';

const props = defineProps({
  photos: { type: Array, default: () => [] },
  canUpload: { type: Boolean, default: false },
  canDelete: { type: Boolean, default: false },
  serviceId: { type: [Number, String], default: null },
});

const emit = defineEmits(['refresh']);

const page = usePage();
const fileInput = ref(null);
const isDragging = ref(false);
const previewFiles = ref([]);
const uploadLoading = ref(false);
const uploadError = ref('');
const selectedPhoto = ref(null);

function handleFileSelect(e) {
  addFiles(e.target.files);
  if (fileInput.value) fileInput.value.value = '';
}

function handleDrop(e) {
  isDragging.value = false;
  addFiles(e.dataTransfer.files);
}

function addFiles(files) {
  for (const f of files) {
    if (!['image/jpeg', 'image/png', 'image/webp'].includes(f.type)) continue;
    if (f.size > 10 * 1024 * 1024) {
      uploadError.value = `File ${f.name} terlalu besar (maks 10MB).`;
      continue;
    }
    if (previewFiles.value.length >= 10) {
      uploadError.value = 'Maksimal 10 file.';
      break;
    }
    previewFiles.value.push({ file: f, preview: URL.createObjectURL(f) });
  }
}

function removePreview(idx) {
  URL.revokeObjectURL(previewFiles.value[idx].preview);
  previewFiles.value.splice(idx, 1);
}

async function uploadPhotos() {
  if (!previewFiles.value.length) return;
  uploadLoading.value = true;
  uploadError.value = '';

  try {
    const formData = new FormData();
    previewFiles.value.forEach(p => formData.append('photos[]', p.file));

    const r = await fetch(`/services/${props.serviceId}/photos`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': page.props.csrf_token || '',
        'Accept': 'application/json',
      },
      body: formData,
    });

    if (r.ok) {
      previewFiles.value.forEach(p => URL.revokeObjectURL(p.preview));
      previewFiles.value = [];
      uploadError.value = '';
      emit('refresh');
    } else if (r.status === 403) {
      uploadError.value = 'Anda tidak memiliki izin untuk upload foto ke servis ini.';
    } else {
      const data = await r.json().catch(() => ({}));
      uploadError.value = data.message || `Gagal upload foto (${r.status}).`;
    }
  } catch {
    uploadError.value = 'Gagal terhubung ke server.';
  } finally {
    uploadLoading.value = false;
  }
}

async function deletePhoto(photoId) {
  if (!confirm('Hapus foto ini?')) return;
  uploadError.value = '';
  try {
    const r = await fetch(`/services/${props.serviceId}/photos/${photoId}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': page.props.csrf_token || '',
        'Accept': 'application/json',
      },
    });
    if (r.ok) {
      emit('refresh');
    } else if (r.status === 403) {
      uploadError.value = 'Anda tidak memiliki izin untuk menghapus foto ini.';
    } else if (r.status === 404) {
      uploadError.value = 'Foto tidak ditemukan.';
      emit('refresh'); // Refresh to sync UI with actual data
    } else {
      const data = await r.json().catch(() => ({}));
      uploadError.value = data.message || 'Gagal menghapus foto.';
    }
  } catch {
    uploadError.value = 'Gagal terhubung ke server.';
  }
}
</script>
