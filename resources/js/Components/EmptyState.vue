<template>
  <div class="flex flex-col items-center justify-center py-16 px-4">
    <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4 bg-zinc-100/80 border border-zinc-200">
      <span v-html="iconSvg" class="w-8 h-8 text-zinc-400"></span>
    </div>
    <p class="text-sm font-semibold mb-1 text-zinc-900">{{ title }}</p>
    <p class="text-xs mb-6 text-center max-w-xs text-zinc-500">{{ description }}</p>
    
    <template v-if="actionLabel">
      <Link
        v-if="actionUrl"
        :href="actionUrl"
        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition-all duration-200 hover:-translate-y-0.5 shadow-sm hover:shadow-md"
      >
        {{ actionLabel }}
      </Link>
      <button
        v-else
        @click="$emit('action')"
        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition-all duration-200 hover:-translate-y-0.5 shadow-sm hover:shadow-md cursor-pointer"
      >
        {{ actionLabel }}
      </button>
    </template>
    
    <slot />
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { getIcon } from '@/Components/Icons.js';

const props = defineProps({
  icon: { type: String, default: 'default' },
  title: { type: String, default: 'Belum ada data' },
  description: { type: String, default: '' },
  actionUrl: { type: String, default: '' },
  actionLabel: { type: String, default: '' },
});

defineEmits(['action']);

const iconSvg = computed(() => getIcon(props.icon));
</script>
