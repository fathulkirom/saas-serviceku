<template>
  <div :class="cn('rounded-xl border border-zinc-200 bg-white text-zinc-950 shadow-sm dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-50', $attrs.class ?? '')">
    <div v-if="$slots.header || title" class="flex flex-col space-y-1.5 p-6">
      <h3 v-if="title" class="font-semibold leading-none tracking-tight">{{ title }}</h3>
      <p v-if="description" class="text-sm text-zinc-500 dark:text-zinc-400">{{ description }}</p>
      <slot name="header" />
    </div>
    <div class="p-6 pt-0">
      <slot />
    </div>
    <div v-if="$slots.footer" class="flex items-center p-6 pt-0">
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup>
import { cn } from '@/lib/utils'

defineProps({
  title: String,
  description: String
})
</script>
