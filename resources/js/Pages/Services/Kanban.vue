<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
  columns: Object,
  services: Object,
})

const columnKeys = Object.keys(props.columns)

function countInColumn(key) { return (props.services[key] || []).length }

const statusColor = (s) => ({ 'menunggu_alokasi': 'sk-bg-hover', 'diterima': 'sk-bg-info-soft', 'diagnosa': 'bg-purple-50', 'dikerjakan': 'sk-bg-warning-soft', 'menunggu_konfirmasi_pelanggan': 'sk-bg-danger-soft', 'menunggu_konfirmasi_internal': 'bg-orange-50', 'indent': 'bg-pink-50', 'onpartner': 'bg-teal-50', 'siap_diambil': 'sk-bg-success-soft' }[s] || 'sk-bg-hover')
</script>

<template>
  <div class="h-full flex flex-col">
    <PageHeader title="Service Queue Board" description="Kanban view — drag & drop untuk melihat bottleneck operasional." class="px-6 pt-4" />

    <div class="flex-1 overflow-x-auto p-4 flex gap-3">
      <div v-for="key in columnKeys" :key="key"
        class="flex-1 min-w-[200px] max-w-[280px] rounded-xl p-3 flex flex-col"
        :class="statusColor(key)">
        <div class="flex items-center justify-between mb-3 sticky top-0">
          <h3 class="text-sm font-bold sk-text-primary">{{ columns[key] }}</h3>
          <span class="text-xs sk-bg-card rounded-full px-2 py-0.5 font-semibold shadow-sm">{{ countInColumn(key) }}</span>
        </div>

        <div class="space-y-2 flex-1 overflow-y-auto">
          <div v-for="s in (services[key] || [])" :key="s.id"
            class="sk-bg-card rounded-lg border sk-border p-3 shadow-sm cursor-pointer hover:shadow-md transition-shadow text-sm"
            @click="router.visit(route('services.show', s.id))">
            <div class="font-semibold sk-text-primary">#{{ s.id }}</div>
            <div class="text-xs sk-text-muted mt-0.5">{{ s.customer?.name || 'Walk-in' }}</div>
            <div class="text-xs sk-text-muted truncate mt-1">{{ s.problem_description }}</div>
            <div v-if="s.technician" class="flex items-center gap-1 mt-2 text-xs sk-text-muted">
              <span class="w-5 h-5 rounded-full sk-bg-primary-soft flex items-center justify-center text-[10px] font-bold sk-text-primary-brand">{{ s.technician.name?.charAt(0) }}</span>
              {{ s.technician.name }}
            </div>
          </div>
          <div v-if="!services[key]?.length" class="text-center py-4 text-xs sk-text-muted">Kosong</div>
        </div>
      </div>
    </div>
  </div>
</template>
