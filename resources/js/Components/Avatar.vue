<template>
  <div class="relative inline-flex items-center justify-center flex-shrink-0"
    :class="sizeClasses"
    :style="{ width: dimension, height: dimension }">
    <img v-if="src" :src="src" :alt="name" class="w-full h-full rounded-full object-cover"
      @error="imgError = true" />
    <div v-else-if="!src || imgError"
      class="w-full h-full rounded-full flex items-center justify-center font-bold text-white select-none"
      :style="{ background: bgColor, fontSize: fontSize }">
      {{ initials }}
    </div>
    <span v-if="status"
      class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full border-2"
      :class="statusClass"
      :style="{ borderColor: 'var(--bg-card)' }" />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  name: { type: String, default: '' },
  src: { type: String, default: '' },
  size: { type: String, default: 'md' },
  status: { type: String, default: '' },
});

const imgError = ref(false);

const sizes = { sm: '28px', md: '36px', lg: '48px' };
const fontSizes = { sm: '10px', md: '13px', lg: '16px' };

const dimension = computed(() => sizes[props.size] || sizes.md);
const fontSize = computed(() => fontSizes[props.size] || fontSizes.md);

const sizeClasses = computed(() => `rounded-full ${props.size === 'sm' ? 'w-7 h-7' : props.size === 'lg' ? 'w-12 h-12' : 'w-9 h-9'}`);

const initials = computed(() => {
  if (!props.name) return '?';
  return props.name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
});

const bgColor = computed(() => {
  const colors = ['#7c3aed', '#10b981', '#3b82f6', '#f97316', '#ef4444', '#ec4899', '#06b6d4', '#8b5cf6'];
  let hash = 0;
  for (let i = 0; i < (props.name || '').length; i++) {
    hash = props.name.charCodeAt(i) + ((hash << 5) - hash);
  }
  return colors[Math.abs(hash) % colors.length];
});

const statusClass = computed(() => {
  if (props.status === 'online') return 'bg-emerald-500';
  if (props.status === 'away') return 'bg-amber-500';
  if (props.status === 'offline') return 'bg-dark-300';
  return '';
});
</script>
