<template>
  <div ref="containerRef" class="relative">
    <div @click="open = !open">
      <slot name="trigger" />
    </div>
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-show="open"
        class="absolute z-50 mt-2 rounded-xl shadow-lg"
        :class="widthClass"
        :style="panelStyle"
        @click="open = false"
      >
        <div class="py-1" role="menu">
          <slot name="content" />
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  align: { type: String, default: 'right' },
  width: { type: [String, Number], default: '48' },
});

const open = ref(false);
const containerRef = ref(null);

const widthClass = computed(() => `w-${props.width}`);

const panelStyle = computed(() => ({
  background: 'var(--bg-card)',
  border: '1px solid var(--border-color)',
  boxShadow: 'var(--shadow-lg)',
  ...(props.align === 'left' ? { left: 0, right: 'auto' } : { left: 'auto', right: 0 }),
}));

function handleClickOutside(event) {
  if (containerRef.value && !containerRef.value.contains(event.target)) {
    open.value = false;
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>
