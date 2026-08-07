<template>
  <div
    :class="classes"
    :style="cardStyle"
    @click="clickable && $emit('click')"
  >
    <div class="flex items-center justify-between mb-3">
      <div class="flex items-center gap-2">
        <div v-if="icon" class="w-8 h-8 rounded-lg flex items-center justify-center" :style="{ background: iconBg }">
          <span class="text-sm" v-html="icon"></span>
        </div>
        <h4 class="sk-label">{{ title }}</h4>
      </div>
      <div v-if="$slots.action || collapsible" class="flex items-center gap-1">
        <slot name="action" />
        <button
          v-if="collapsible"
          @click="collapsed = !collapsed"
          class="w-7 h-7 rounded-lg flex items-center justify-center transition-colors hover:bg-zinc-100"
        >
          <svg
            class="w-4 h-4 transition-transform duration-200"
            :class="{ 'rotate-180': !collapsed }"
            :style="{ color: 'var(--text-muted)' }"
            fill="none" stroke="currentColor" viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>
      </div>
    </div>

    <div v-show="!collapsed">
      <slot />
    </div>

    <div v-if="loading" class="absolute inset-0 bg-white/60 rounded-2xl flex items-center justify-center">
      <div class="sk-animate-spin w-6 h-6 border-2 border-indigo-500 border-t-transparent rounded-full"></div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

/**
 * Enterprise Dashboard / Widget Card.
 * Digunakan sebagai container widget di dashboard.
 *
 * @example
 * <SkWidgetCard title="Servis Aktif" icon="🔧" collapsible>
 *   <p>Widget content</p>
 *   <template #action><SkButton size="xs">Refresh</SkButton></template>
 * </SkWidgetCard>
 */
const props = defineProps({
  title: { type: String, required: true },
  icon: { type: String, default: '' },
  variant: { type: String, default: 'default' },
  loading: { type: Boolean, default: false },
  collapsible: { type: Boolean, default: false },
  collapsed: { type: Boolean, default: false },
  clickable: { type: Boolean, default: false },
  extraClass: { type: String, default: '' },
});

defineEmits(['click']);

const collapsed = ref(props.collapsed);

const classes = computed(() => [
  'relative rounded-2xl border bg-white shadow-sm transition-all duration-300',
  'p-5',
  props.clickable ? 'hover:shadow-md hover:-translate-y-0.5 cursor-pointer' : '',
  props.extraClass,
].filter(Boolean).join(' '));

const iconBg = computed(() => 'var(--bg-hover)');

const cardStyle = computed(() => ({
  borderColor: 'var(--border-color)',
}));
</script>
