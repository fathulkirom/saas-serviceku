<template>
  <SkWidgetCard title="Aktivitas Terkini" :loading="loading" collapsible>
    <template #action>
      <Link :href="route('services.index')" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">
        Lihat Semua
      </Link>
    </template>

    <div v-if="!activities.length" class="py-6">
      <SkEmptyState variant="empty" title="Belum ada aktivitas" description="Aktivitas servis akan muncul di sini." />
    </div>

    <div v-else class="space-y-4 max-h-[320px] overflow-y-auto pr-1">
      <div
        v-for="(item, i) in activities"
        :key="i"
        class="flex items-center gap-3 group cursor-pointer"
        @click="$emit('navigate', item)"
      >
        <div
          class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-bold"
          :style="{ background: item.color || 'var(--primary-soft)', color: item.textColor || 'var(--primary)' }"
        >
          {{ item.customer?.name?.charAt(0) || '?' }}
        </div>
        <div class="flex-1 min-w-0">
          <p class="sk-label-sm truncate">{{ item.customer?.name || 'Pelanggan' }}</p>
          <p class="sk-caption truncate">{{ item.device_type }} · {{ item.damage_type || 'Perbaikan' }}</p>
        </div>
        <div class="flex-shrink-0">
          <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full" :style="{ background: 'var(--bg-hover)', color: 'var(--text-muted)' }">
            {{ statusLabel(item.status) }}
          </span>
        </div>
      </div>
    </div>
  </SkWidgetCard>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import SkWidgetCard from '@/Enterprise/Components/Cards/WidgetCard.vue';
import SkEmptyState from '@/Enterprise/Components/Empty/EmptyState.vue';
import { statusLabel as getStatusLabel } from '@/Utils/statusMaps.js';

defineProps({
  activities: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
});

defineEmits(['navigate']);

const statusLabel = (s) => getStatusLabel(s);
</script>
