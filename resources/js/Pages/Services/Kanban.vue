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

const statusColor = (s) => ({ 'menunggu_alokasi': 'bg-gray-100', 'diterima': 'bg-blue-50', 'diagnosa': 'bg-purple-50', 'dikerjakan': 'bg-amber-50', 'menunggu_konfirmasi_pelanggan': 'bg-red-50', 'menunggu_konfirmasi_internal': 'bg-orange-50', 'indent': 'bg-pink-50', 'onpartner': 'bg-teal-50', 'siap_diambil': 'bg-green-50' }[s] || 'bg-gray-50')
</script>

<template>
  <div class="h-full flex flex-col">
    <PageHeader title="Service Queue Board" description="Kanban view — drag & drop untuk melihat bottleneck operasional." class="px-6 pt-4" />

    <div class="flex-1 overflow-x-auto p-4 flex gap-3">
      <div v-for="key in columnKeys" :key="key"
        class="flex-1 min-w-[200px] max-w-[280px] rounded-xl p-3 flex flex-col"
        :class="statusColor(key)">
        <div class="flex items-center justify-between mb-3 sticky top-0">
          <h3 class="text-sm font-bold text-gray-700">{{ columns[key] }}</h3>
          <span class="text-xs bg-white rounded-full px-2 py-0.5 font-semibold shadow-sm">{{ countInColumn(key) }}</span>
        </div>

        <div class="space-y-2 flex-1 overflow-y-auto">
          <div v-for="s in (services[key] || [])" :key="s.id"
            class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm cursor-pointer hover:shadow-md transition-shadow text-sm"
            @click="router.visit(route('services.show', s.id))">
            <div class="font-semibold text-gray-900">#{{ s.id }}</div>
            <div class="text-xs text-gray-500 mt-0.5">{{ s.customer?.name || 'Walk-in' }}</div>
            <div class="text-xs text-gray-400 truncate mt-1">{{ s.problem_description }}</div>
            <div v-if="s.technician" class="flex items-center gap-1 mt-2 text-xs text-gray-500">
              <span class="w-5 h-5 rounded-full bg-indigo-100 flex items-center justify-center text-[10px] font-bold text-indigo-600">{{ s.technician.name?.charAt(0) }}</span>
              {{ s.technician.name }}
            </div>
          </div>
          <div v-if="!services[key]?.length" class="text-center py-4 text-xs text-gray-400">Kosong</div>
        </div>
      </div>
    </div>
  </div>
</template>
