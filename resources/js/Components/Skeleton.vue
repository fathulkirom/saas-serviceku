<template>
  <div v-if="type === 'card'" :style="{ width, height }" class="space-y-3">
    <div class="skeleton h-4 w-3/4" />
    <div class="skeleton h-3 w-full" />
    <div class="skeleton h-3 w-5/6" />
  </div>
  <div v-else-if="type === 'table'" class="space-y-2">
    <div class="skeleton h-8 w-full rounded-lg" />
    <div v-for="i in count" :key="i" class="skeleton h-10 w-full rounded-lg" />
  </div>
  <div v-else-if="type === 'text'" class="space-y-2">
    <div v-for="i in count" :key="i" class="skeleton h-3" :style="{ width: i === 1 ? '80%' : i === 2 ? '60%' : '90%' }" />
  </div>
  <div v-else-if="type === 'stat'" class="grid grid-cols-2 sm:grid-cols-4 gap-4">
    <div v-for="i in count" :key="i" class="p-4 rounded-xl border" :style="{ borderColor: 'var(--border-color)' }">
      <div class="flex items-center gap-3">
        <div class="skeleton w-10 h-10 rounded-xl flex-shrink-0" />
        <div class="flex-1 space-y-2">
          <div class="skeleton h-3 w-3/4" />
          <div class="skeleton h-5 w-1/2" />
        </div>
      </div>
    </div>
  </div>
  <div v-else-if="type === 'circle'" :style="{ width: width || '40px', height: height || '40px' }" class="skeleton rounded-full" />
</template>

<script setup>
defineProps({
  type: { type: String, default: 'text' },
  count: { type: Number, default: 3 },
  width: { type: String, default: '100%' },
  height: { type: String, default: '' },
});
</script>
