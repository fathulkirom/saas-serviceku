<template>
  <div>
    <div class="flex gap-1 p-1.5 rounded-2xl bg-zinc-100/80 border border-zinc-200 mb-6 overflow-x-auto scrollbar-hide shadow-sm"
      role="tablist">
      <KButton  v-for="tab in tabs" :key="tab.key"
        class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold whitespace-nowrap transition-all duration-200"
        :class="tab.key === modelValue 
          ? 'bg-white text-indigo-600 shadow-sm ring-1 ring-zinc-200/50' 
          : 'text-zinc-600 hover:text-zinc-900 hover:bg-zinc-200/50'"
        @click="selectTab(tab.key)"
        :aria-selected="tab.key === modelValue"
        role="tab">
        {{ tab.label }}
        <span v-if="tab.count !== undefined"
          class="inline-flex items-center justify-center min-w-[20px] h-[20px] px-1.5 rounded-full text-[11px] font-bold"
          :class="tab.key === modelValue
            ? 'bg-indigo-100 text-indigo-700'
            : 'bg-zinc-200 text-zinc-500'">
          {{ tab.count }}
        </span>
      </KButton>
    </div>
    <div class="tab-content">
      <slot :name="modelValue" />
    </div>
  </div>
</template>

<script setup>
import KButton from '@/Components/KButton.vue';

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
