<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
  <AuthenticatedLayout>
    <div class="max-w-4xl mx-auto py-12 px-4 text-center">
      <div class="text-6xl mb-6">🚧</div>
      <h1 class="text-2xl font-bold sk-text-primary mb-2">Request Detail</h1>
      <p class="sk-text-muted mb-4">Module sedang dikembangkan.</p>
      <p class="text-sm sk-text-muted">Fitur Request Management akan tersedia di sprint selanjutnya.</p>
    </div>
  </AuthenticatedLayout>
</template>
