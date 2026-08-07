<template>
  <SkWidgetCard title="Peringatan Stok" :loading="loading" collapsible>
    <div v-if="!alerts.length" class="py-6">
      <SkEmptyState variant="empty" title="Stok aman" description="Semua produk memiliki stok yang cukup." />
    </div>

    <div v-else class="space-y-2 max-h-[280px] overflow-y-auto pr-1">
      <div
        v-for="item in alerts"
        :key="item.id"
        class="flex items-center justify-between px-3 py-2 rounded-xl border"
        :style="{ borderColor: 'var(--border-light)' }"
      >
        <div class="flex items-center gap-2.5 min-w-0">
          <span class="w-2 h-2 rounded-full flex-shrink-0" :class="item.stock === 0 ? 'bg-red-500 animate-pulse' : 'bg-orange-400'"></span>
          <div class="min-w-0">
            <p class="sk-label-sm truncate">{{ item.name }}</p>
            <p class="sk-caption">SKU: {{ item.sku || '-' }} · Stok: <span :class="item.stock === 0 ? 'text-red-600 font-bold' : 'text-orange-600 font-bold'">{{ item.stock }}</span></p>
          </div>
        </div>
        <Link
          :href="route('inventaris.index', { search: item.sku })"
          class="text-[10px] font-semibold px-2 py-1 rounded-lg flex-shrink-0 transition-colors"
          :style="{ background: 'var(--primary-soft)', color: 'var(--primary)' }"
        >
          Restock
        </Link>
      </div>
    </div>
  </SkWidgetCard>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import SkWidgetCard from '@/Enterprise/Components/Cards/WidgetCard.vue';
import SkEmptyState from '@/Enterprise/Components/Empty/EmptyState.vue';

defineProps({
  alerts: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
});
</script>
