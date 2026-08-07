<template>
  <div :class="classes">
    <!-- Grid using CSS Grid -->
    <div
      class="grid gap-6"
      :style="gridStyle"
    >
      <div
        v-for="(widget, i) in widgets"
        :key="widget.id || i"
        class="widget-item"
        :style="{ gridColumn: `span ${widget.cols || 1}`, gridRow: `span ${widget.rows || 1}` }"
      >
        <div class="h-full">
          <slot :name="'widget-' + (widget.id || i)" :widget="widget">
            <!-- Default widget card -->
            <SkWidgetCard
              :title="widget.title"
              :icon="widget.icon"
              :loading="widget.loading"
              :collapsible="widget.collapsible !== false"
            >
              <p class="sk-caption">Widget content</p>
            </SkWidgetCard>
          </slot>
        </div>
      </div>
    </div>

    <!-- Empty state -->
    <div v-if="!widgets.length" class="text-center py-12">
      <SkEmptyState variant="empty" title="Belum ada widget" description="Tambahkan widget untuk memulai dashboard." />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import SkWidgetCard from '@/Enterprise/Components/Cards/WidgetCard.vue';
import SkEmptyState from '@/Enterprise/Components/Empty/EmptyState.vue';

/**
 * Enterprise Dashboard Widget Grid.
 * Layout grid responsif untuk widget dashboard.
 *
 * @example
 * <SkWidgetGrid :widgets="dashboardWidgets" :cols="4">
 *   <template #widget-revenue="{ widget }">
 *     <SkMetricCard :label="widget.title" :value="widget.value" format="currency" />
 *   </template>
 * </SkWidgetGrid>
 */
const props = defineProps({
  widgets: { type: Array, default: () => [] },
  cols: { type: Number, default: 4 },          // Default columns
  colsLaptop: { type: Number, default: 3 },
  colsTablet: { type: Number, default: 2 },
  colsMobile: { type: Number, default: 1 },
  gap: { type: String, default: '1.5rem' },
  extraClass: { type: String, default: '' },
});

const gridStyle = computed(() => ({
  gridTemplateColumns: `repeat(${props.colsMobile}, 1fr)`,
  gap: props.gap,
}));

const classes = computed(() => [
  props.extraClass,
].filter(Boolean).join(' '));
</script>

<style scoped>
@media (min-width: 640px) {
  .grid { grid-template-columns: repeat(v-bind('props.colsMobile'), 1fr); }
}
@media (min-width: 768px) {
  .grid { grid-template-columns: repeat(v-bind('props.colsTablet'), 1fr); }
}
@media (min-width: 1024px) {
  .grid { grid-template-columns: repeat(v-bind('props.colsLaptop'), 1fr); }
}
@media (min-width: 1280px) {
  .grid { grid-template-columns: repeat(v-bind('props.cols'), 1fr); }
}
</style>
