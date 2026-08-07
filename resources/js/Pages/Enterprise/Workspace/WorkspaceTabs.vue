<template>
  <div class="border-b px-4 sm:px-6 lg:px-8" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-light)' }">
    <div class="max-w-[1600px] mx-auto w-full flex items-center gap-0 overflow-x-auto">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        @click="$emit('switch', tab.id)"
        class="flex items-center gap-1.5 px-4 py-2.5 text-sm font-semibold whitespace-nowrap border-b-2 transition-colors -mb-[1px]"
        :style="{
          color: activeTab === tab.id ? 'var(--primary)' : 'var(--text-muted)',
          borderColor: activeTab === tab.id ? 'var(--primary)' : 'transparent',
        }"
      >
        <span>{{ tab.icon }}</span>
        <span>{{ tab.label }}</span>
        <!-- Badge (optional) -->
        <span v-if="tab.badge" class="text-[10px] px-1.5 py-0.5 rounded-full ml-1"
          :style="{ background: activeTab === tab.id ? 'var(--primary-soft)' : 'var(--bg-hover)', color: activeTab === tab.id ? 'var(--primary)' : 'var(--text-muted)' }"
        >{{ tab.badge }}</span>
      </button>
    </div>
  </div>
</template>

<script setup>
defineProps({
  tabs: { type: Array, default: () => [] },
  activeTab: { type: String, default: 'overview' },
});

defineEmits(['switch']);
</script>
