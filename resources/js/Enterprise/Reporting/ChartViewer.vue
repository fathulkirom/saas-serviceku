<template>
  <SkCard size="md">
    <!-- Chart using pure SVG/CSS — no heavy chart library needed -->
    <div class="w-full" :style="{ height: chartHeight }">
      
      <!-- PIE / DONUT -->
      <div v-if="chartType === 'pie' || chartType === 'donut'" class="flex items-center gap-6 h-full">
        <svg viewBox="0 0 200 200" class="w-48 h-48 flex-shrink-0">
          <g v-for="(slice, i) in pieSlices" :key="i">
            <path :d="slice.path" :fill="slice.color" stroke="white" stroke-width="2">
              <title>{{ slice.label }}: {{ slice.value }}</title>
            </path>
          </g>
          <circle v-if="chartType === 'donut'" cx="100" cy="100" r="55" fill="white" />
          <text x="100" y="100" text-anchor="middle" dominant-baseline="central" class="text-lg font-bold" fill="currentColor">
            {{ totalValue }}
          </text>
        </svg>
        <div class="flex-1 space-y-2">
          <div v-for="(slice, i) in pieSlices" :key="i" class="flex items-center gap-2 text-xs">
            <span class="w-3 h-3 rounded-full flex-shrink-0" :style="{ background: slice.color }"></span>
            <span class="flex-1 truncate" :style="{ color: 'var(--text-secondary)' }">{{ slice.label }}</span>
            <span class="font-semibold" :style="{ color: 'var(--text-primary)' }">{{ formatNumber(slice.value) }}</span>
          </div>
        </div>
      </div>

      <!-- BAR / LINE / AREA -->
      <div v-else class="h-full relative">
        <!-- Y-axis -->
        <div class="absolute inset-0 flex flex-col justify-between pb-6 pt-1 z-0">
          <div v-for="i in 5" :key="i" class="border-t border-dashed w-full" :style="{ borderColor: 'var(--border-light)' }" />
        </div>

        <!-- Bars/Lines -->
        <div class="absolute inset-0 flex items-end pb-6 z-10">
          <div v-for="(item, i) in chartLabels" :key="i"
            class="flex-1 flex flex-col items-center justify-end gap-1 group cursor-pointer px-0.5">
            <span class="text-[9px] font-semibold opacity-0 group-hover:opacity-100 transition-opacity absolute -top-5"
              :style="{ color: 'var(--text-primary)' }">
              {{ formatNumber(seriesData[0]?.data[i]) }}
            </span>
            <div class="w-full max-w-[40px] rounded-t-md transition-all duration-300 group-hover:opacity-80"
              :style="{ height: getBarHeight(seriesData[0]?.data[i]) + '%', background: seriesColors[0] || 'var(--primary)', opacity: 0.8 }" />
            <span class="text-[9px] font-medium mt-1 text-center truncate w-full"
              :style="{ color: 'var(--text-muted)' }" :title="item">{{ item }}</span>
          </div>
        </div>
      </div>

    </div>
  </SkCard>
</template>

<script setup>
import { computed } from 'vue';
import SkCard from '@/Enterprise/Components/Cards/Card.vue';

const props = defineProps({
  chartType: { type: String, default: 'bar' },
  chartData: { type: [Object, Array], default: () => ({}) },
  height: { type: String, default: '300px' },
});

const chartHeight = computed(() => props.height);
const chartLabels = computed(() => props.chartData?.labels || []);

const seriesData = computed(() => {
  if (Array.isArray(props.chartData)) return [];
  return props.chartData?.series || [];
});

const seriesColors = ['var(--primary)', 'var(--success)', 'var(--warning)', 'var(--danger)', 'var(--info)'];

function getBarHeight(val) {
  if (!seriesData.value.length) return 0;
  const maxVal = Math.max(...seriesData.value.flatMap(s => s.data || []), 1);
  return Math.round(((val || 0) / maxVal) * 100);
}

// ── Pie chart ──
const pieColors = ['#3B82F6', '#22C55E', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#14B8A6', '#F97316', '#6366F1', '#84CC16'];

const pieSlices = computed(() => {
  const data = Array.isArray(props.chartData) ? props.chartData : [];
  const total = data.reduce((s, d) => s + (Number(d.value) || 0), 0) || 1;
  let cumulative = 0;

  return data.map((d, i) => {
    const percent = (Number(d.value) || 0) / total;
    const startAngle = cumulative * 360;
    cumulative += percent;
    const endAngle = cumulative * 360;
    const path = describeArc(100, 100, 80, startAngle, endAngle);
    return { ...d, path, color: pieColors[i % pieColors.length], percent };
  });
});

const totalValue = computed(() => {
  const data = Array.isArray(props.chartData) ? props.chartData : [];
  return data.reduce((s, d) => s + (Number(d.value) || 0), 0);
});

function polarToCartesian(cx, cy, r, angleDeg) {
  const rad = ((angleDeg - 90) * Math.PI) / 180;
  return { x: cx + r * Math.cos(rad), y: cy + r * Math.sin(rad) };
}

function describeArc(cx, cy, r, startAngle, endAngle) {
  const start = polarToCartesian(cx, cy, r, endAngle);
  const end = polarToCartesian(cx, cy, r, startAngle);
  const largeArc = endAngle - startAngle > 180 ? 1 : 0;
  return `M ${start.x} ${start.y} A ${r} ${r} 0 ${largeArc} 0 ${end.x} ${end.y} L ${cx} ${cy} Z`;
}

function formatNumber(n) {
  if (n === null || n === undefined) return '-';
  return new Intl.NumberFormat('id-ID').format(Number(n));
}
</script>
