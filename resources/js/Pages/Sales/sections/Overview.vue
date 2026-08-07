<template>
  <div class="space-y-5">
    <!-- KPI Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Sales Today</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--success)' }">Rp {{ formatNumber(stats?.sales_today) }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Transactions</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--primary)' }">{{ stats?.transactions_today || 0 }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Avg Basket</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--info)' }">Rp {{ formatNumber(stats?.avg_basket) }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Open Orders</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--warning)' }">{{ stats?.open_orders || 0 }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Pending Delivery</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--danger)' }">{{ stats?.pending_delivery || 0 }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Profit Today</p>
        <p class="text-xl font-bold mt-1" :style="{ color: stats?.profit_today >= 0 ? 'var(--success)' : 'var(--danger)' }">Rp {{ formatNumber(stats?.profit_today) }}</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <!-- Top Products -->
      <SkCard title="🏆 Top Products Today" size="sm">
        <div v-if="stats?.top_products?.length" class="space-y-2">
          <div v-for="p in stats.top_products" :key="p.id" class="flex justify-between items-center py-1 text-sm">
            <span class="truncate" :style="{ color: 'var(--text-primary)' }">{{ p.product_name }}</span>
            <div class="flex items-center gap-2 ml-2">
              <span class="text-xs" :style="{ color: 'var(--text-muted)' }">×{{ p.qty }}</span>
              <span class="text-xs font-bold" :style="{ color: 'var(--success)' }">Rp {{ formatNumber(p.revenue) }}</span>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-4"><p class="sk-caption">No sales yet today</p></div>
      </SkCard>

      <!-- Recent Transactions -->
      <SkCard title="💳 Recent Transactions" size="sm">
        <div v-if="stats?.recent_sales?.length" class="space-y-1">
          <div v-for="s in stats.recent_sales" :key="s.id" class="flex justify-between items-center py-1 text-sm">
            <div class="min-w-0 flex-1">
              <p class="text-xs font-medium truncate" :style="{ color: 'var(--text-primary)' }">{{ s.customer_name || 'Walk-in' }}</p>
              <p class="text-[10px]" :style="{ color: 'var(--text-muted)' }">{{ s.payment_method }} · {{ formatTime(s.created_at) }}</p>
            </div>
            <span class="text-sm font-bold ml-2" :style="{ color: 'var(--text-primary)' }">Rp {{ formatNumber(s.grand_total) }}</span>
          </div>
        </div>
      </SkCard>

      <!-- Active Promotions -->
      <SkCard title="🎫 Active Promotions" size="sm">
        <div v-if="stats?.active_promos?.length" class="space-y-2">
          <div v-for="p in stats.active_promos" :key="p.id" class="flex justify-between items-center py-1 text-sm">
            <span :style="{ color: 'var(--text-primary)' }">{{ p.promotion_name }}</span>
            <span class="text-[10px] ml-2" :style="{ color: 'var(--text-muted)' }">Ends {{ formatDate(p.end_date) }}</span>
          </div>
        </div>
        <div v-else class="text-center py-4"><p class="sk-caption">No active promotions</p></div>
      </SkCard>
    </div>

    <!-- Channel Breakdown -->
    <SkCard title="🌐 Sales by Channel" size="md" v-if="stats?.channel_breakdown?.length">
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div v-for="c in stats.channel_breakdown" :key="c.channel" class="p-3 rounded-lg text-center" :style="{ background: 'var(--bg-hover)' }">
          <p class="text-[10px] uppercase" :style="{ color: 'var(--text-muted)' }">{{ c.channel }}</p>
          <p class="text-lg font-bold mt-1" :style="{ color: 'var(--primary)' }">Rp {{ formatNumber(c.revenue) }}</p>
          <p class="text-[10px]" :style="{ color: 'var(--text-muted)' }">{{ c.count }} orders</p>
        </div>
      </div>
    </SkCard>
  </div>
</template>

<script setup>
import SkCard from '@/Enterprise/Components/Cards/Card.vue';

defineProps({
  data: { type: Object, default: () => ({}) },
  stats: { type: Object, default: () => ({}) },
});

function formatNumber(v) {
  if (v == null) return '0';
  return Number(v).toLocaleString('id-ID');
}
function formatDate(v) {
  if (!v) return '-';
  return new Date(v).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
}
function formatTime(v) {
  if (!v) return '-';
  return new Date(v).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
}
</script>
