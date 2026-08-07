<template>
  <div class="space-y-5">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <SkMetricCard label="Total Orders" :value="data?.total_orders || 0" format="number" color="primary" icon="📦" />
      <SkMetricCard label="Outstanding PO" :value="data?.outstanding_po || 0" format="number" color="warning" icon="📋" />
      <SkMetricCard label="On-Time Rate" :value="data?.on_time_rate || 0" format="percent" color="success" icon="✅" />
      <SkMetricCard label="Total Value" :value="data?.total_value || 0" format="currency" color="primary" icon="💰" />
    </div>

    <SkCard title="Supplier Info" size="md" v-if="data">
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div><p class="sk-caption">Name</p><p class="sk-label-sm">{{ data.name || '-' }}</p></div>
        <div><p class="sk-caption">Code</p><p class="sk-code text-xs">{{ data.code || '-' }}</p></div>
        <div><p class="sk-caption">Phone</p><p class="sk-label-sm">{{ data.phone || '-' }}</p></div>
        <div><p class="sk-caption">Email</p><p class="sk-label-sm">{{ data.email || '-' }}</p></div>
        <div><p class="sk-caption">Contact Person</p><p class="sk-label-sm">{{ data.contact_person || '-' }}</p></div>
        <div><p class="sk-caption">Payment Terms</p><p class="sk-label-sm">{{ data.payment_terms || 'Net 30' }}</p></div>
        <div><p class="sk-caption">Rating</p><p class="sk-label-sm">{{ '⭐'.repeat(data.rating || 0) }}</p></div>
        <div><p class="sk-caption">Status</p><span class="text-xs font-bold px-2 py-0.5 rounded-full" :style="data.active ? {background:'var(--success-soft)',color:'var(--success-text)'} : {background:'var(--bg-hover)',color:'var(--text-secondary)'}">{{ data.active ? 'Active' : 'Inactive' }}</span></div>
      </div>
    </SkCard>

    <!-- Performance Metrics -->
    <SkCard title="Performance" size="md" v-if="data">
      <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
        <div class="p-3 rounded-xl text-center" :style="{ background:'var(--bg-hover)' }">
          <p class="text-[10px] uppercase tracking-wider" :style="{ color:'var(--text-muted)' }">On Time</p>
          <p class="text-xl font-bold mt-1" :style="{ color:'var(--success-text)' }">{{ data.on_time_rate || 0 }}%</p>
        </div>
        <div class="p-3 rounded-xl text-center" :style="{ background:'var(--bg-hover)' }">
          <p class="text-[10px] uppercase tracking-wider" :style="{ color:'var(--text-muted)' }">Quality</p>
          <p class="text-xl font-bold mt-1" :style="{ color:'var(--primary)' }">{{ data.quality_rate || 0 }}%</p>
        </div>
        <div class="p-3 rounded-xl text-center" :style="{ background:'var(--bg-hover)' }">
          <p class="text-[10px] uppercase tracking-wider" :style="{ color:'var(--text-muted)' }">Avg Lead Time</p>
          <p class="text-xl font-bold mt-1" :style="{ color:'var(--text-primary)' }">{{ data.avg_lead_time || 0 }}d</p>
        </div>
      </div>
    </SkCard>
  </div>
</template>

<script setup>
import SkCard from '@/Enterprise/Components/Cards/Card.vue';
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ data: { type: Object, default: () => ({}) }, workspace: { type: Object, default: null } });
</script>
