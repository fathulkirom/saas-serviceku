<template>
  <div :class="classes">
    <div
      v-for="(item, i) in items"
      :key="item.key || i"
      class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm cursor-pointer transition-all"
      :style="{
        background: active === item.key ? 'var(--primary-soft)' : 'transparent',
        color: active === item.key ? 'var(--primary)' : 'var(--text-secondary)',
      }"
      @click="select(item)"
    >
      <span v-if="item.icon" class="text-base" v-html="item.icon"></span>
      <span class="flex-1 truncate">{{ item.label }}</span>
      <button
        v-if="removable"
        @click.stop="$emit('remove', item)"
        class="w-5 h-5 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity hover:bg-zinc-200"
      >
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>
    <div v-if="!items.length" class="sk-caption text-center py-4">Belum ada favorit</div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

/**
 * Enterprise Favorites list — untuk sidebar favorites menu.
 *
 * @example
 * <SkFavorites
 *   :items="favorites"
 *   active="services"
 *   @select="navigate"
 *   @remove="unfavorite"
 * />
 */
const props = defineProps({
  items: { type: Array, default: () => [] },   // [{ key, label, icon?, url? }]
  active: { type: String, default: '' },
  removable: { type: Boolean, default: false },
  extraClass: { type: String, default: '' },
});

const emit = defineEmits(['select', 'remove']);

const select = (item) => {
  emit('select', item);
};

const classes = computed(() => [
  'space-y-0.5',
  props.extraClass,
].filter(Boolean).join(' '));
</script>
import { computed } from 'vue';
</script>
