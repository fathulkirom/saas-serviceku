<template>
  <div>
    <div class="flex gap-1 p-1 rounded-xl border mb-6 overflow-x-auto scrollbar-hide"
      :style="{ background: 'var(--bg-secondary)', borderColor: 'var(--border-color)' }"
      role="tablist">
      <button v-for="tab in tabs" :key="tab.key"
        class="flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold whitespace-nowrap transition-all duration-200"
        :style="tab.key === modelValue
          ? { background: 'var(--accent-primary)', color: '#ffffff', boxShadow: 'var(--shadow-sm)' }
          : { color: 'var(--text-secondary)' }"
        @click="selectTab(tab.key)"
        :aria-selected="tab.key === modelValue"
        role="tab">
        {{ tab.label }}
        <span v-if="tab.count !== undefined"
          class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full text-[10px] font-bold"
          :style="tab.key === modelValue
            ? { background: 'rgba(255,255,255,0.2)', color: '#ffffff' }
            : { background: 'var(--bg-hover)', color: 'var(--text-muted)' }">
          {{ tab.count }}
        </span>
      </button>
    </div>
    <div class="tab-content">
      <slot :name="modelValue" />
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  tabs: { type: Array, required: true },
  modelValue: { type: String, required: true },
});

const emit = defineEmits(['update:modelValue']);

function selectTab(key) {
  if (key !== props.modelValue) {
    emit('update:modelValue', key);
  }
}
</script>

<style scoped>
.tab-content {
  animation: fadeIn 0.2s ease;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(4px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
