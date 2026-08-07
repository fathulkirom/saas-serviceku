<template>
  <div :class="wrapperClass">
    <div
      :class="dropzoneClasses"
      :style="dropzoneStyle"
      @dragover.prevent="dragover = true"
      @dragleave.prevent="dragover = false"
      @drop.prevent="onDrop"
      @click="openFileDialog"
    >
      <input
        ref="fileInputRef"
        type="file"
        :accept="accept"
        :multiple="multiple"
        class="hidden"
        @change="onFileChange"
      />

      <!-- Upload icon -->
      <div
        v-if="!previewUrl && !uploading"
        class="w-12 h-12 rounded-2xl flex items-center justify-center mb-3 transition-colors"
        :style="{ background: dragover ? 'var(--primary-soft)' : 'var(--bg-hover)' }"
      >
        <svg class="w-6 h-6" :style="{ color: dragover ? 'var(--primary)' : 'var(--text-muted)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
        </svg>
      </div>

      <!-- Uploading spinner -->
      <div v-if="uploading" class="flex flex-col items-center">
        <div class="sk-animate-spin w-8 h-8 border-2 border-indigo-500 border-t-transparent rounded-full mb-2"></div>
        <p class="sk-label-sm">{{ uploadLabel }}</p>
      </div>

      <!-- Preview image -->
      <div v-else-if="previewUrl" class="relative">
        <img :src="previewUrl" class="max-h-48 rounded-xl object-contain" />
        <button
          v-if="clearable"
          @click.stop="clear"
          class="absolute -top-2 -right-2 w-6 h-6 rounded-full bg-red-500 text-white flex items-center justify-center shadow-md"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Text -->
      <template v-if="!uploading">
        <p class="sk-label mb-1">
          <template v-if="dragover">Lepas file di sini</template>
          <template v-else>{{ label }}</template>
        </p>
        <p class="sk-caption">{{ hint }}</p>
      </template>
    </div>

    <!-- File list -->
    <div v-if="files.length > 0 && !previewUrl" class="mt-3 space-y-2">
      <div
        v-for="(file, i) in files"
        :key="i"
        class="flex items-center gap-3 px-3 py-2 rounded-xl border"
        :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-card)' }"
      >
        <svg class="w-8 h-8 flex-shrink-0" :style="{ color: 'var(--text-muted)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <div class="flex-1 min-w-0">
          <p class="sk-label-sm truncate">{{ file.name }}</p>
          <p class="sk-caption">{{ formatSize(file.size) }}</p>
        </div>
        <button @click="removeFile(i)" class="w-6 h-6 rounded-full flex items-center justify-center" :style="{ color: 'var(--text-muted)' }">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
    </div>

    <p v-if="error" class="sk-error mt-1.5">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

/**
 * Enterprise File Upload — drag & drop + click.
 *
 * @example
 * <SkFileUpload v-model="files" label="Upload Dokumen" accept=".pdf,.docx" />
 * <SkFileUpload v-model="photo" label="Upload Foto" accept="image/*" :multiple="false" />
 */
const props = defineProps({
  modelValue: { type: [File, Array], default: () => [] },
  label: { type: String, default: 'Seret & lepas file di sini' },
  hint: { type: String, default: 'atau klik untuk memilih file' },
  accept: { type: String, default: '' },
  multiple: { type: Boolean, default: true },
  maxSize: { type: Number, default: 10 }, // MB
  uploading: { type: Boolean, default: false },
  uploadLabel: { type: String, default: 'Mengunggah...' },
  clearable: { type: Boolean, default: true },
  error: { type: String, default: '' },
  preview: { type: Boolean, default: false }, // Show image preview for single file
  wrapperClass: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue']);

const dragover = ref(false);
const fileInputRef = ref(null);

const files = computed(() => {
  if (!props.modelValue) return [];
  return Array.isArray(props.modelValue) ? props.modelValue : [props.modelValue];
});

const previewUrl = computed(() => {
  if (!props.preview || !props.modelValue) return null;
  const file = Array.isArray(props.modelValue) ? props.modelValue[0] : props.modelValue;
  if (file instanceof File && file.type.startsWith('image/')) {
    return URL.createObjectURL(file);
  }
  return null;
});

const openFileDialog = () => {
  if (props.uploading) return;
  fileInputRef.value?.click();
};

const onFileChange = (e) => {
  handleFiles(e.target.files);
};

const onDrop = (e) => {
  dragover.value = false;
  handleFiles(e.dataTransfer.files);
};

const handleFiles = (fileList) => {
  if (!fileList || fileList.length === 0) return;
  const newFiles = Array.from(fileList).filter(f => {
    if (f.size > props.maxSize * 1024 * 1024) return false;
    return true;
  });
  if (newFiles.length === 0) return;

  if (props.multiple) {
    emit('update:modelValue', [...files.value, ...newFiles]);
  } else {
    emit('update:modelValue', newFiles[0]);
  }
};

const removeFile = (index) => {
  if (props.multiple) {
    const updated = files.value.filter((_, i) => i !== index);
    emit('update:modelValue', updated);
  } else {
    emit('update:modelValue', null);
  }
};

const clear = () => {
  emit('update:modelValue', props.multiple ? [] : null);
};

const formatSize = (bytes) => {
  if (!bytes) return '0 B';
  const units = ['B', 'KB', 'MB', 'GB'];
  let i = 0;
  while (bytes >= 1024 && i < units.length - 1) { bytes /= 1024; i++; }
  return bytes.toFixed(1) + ' ' + units[i];
};

const dropzoneClasses = computed(() => [
  'border-2 border-dashed rounded-2xl p-6 text-center transition-all duration-200 cursor-pointer',
  dragover.value ? 'border-indigo-400 bg-indigo-50/50 scale-[1.01]' : '',
  props.error ? 'border-red-300' : '',
  props.uploading ? 'cursor-wait' : '',
].filter(Boolean).join(' '));

const dropzoneStyle = computed(() => ({
  borderColor: props.error ? 'var(--danger)' : dragover.value ? 'var(--primary)' : 'var(--border-color)',
}));
</script>
