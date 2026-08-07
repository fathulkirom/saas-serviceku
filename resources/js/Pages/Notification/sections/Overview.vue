<template>
  <div class="space-y-5">
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }"><p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Queue Pending</p><p class="text-xl font-bold mt-1" :style="{ color: 'var(--warning)' }">{{ stats?.queue_pending || 0 }}</p></div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }"><p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Sent Today</p><p class="text-xl font-bold mt-1" :style="{ color: 'var(--primary)' }">{{ stats?.sent_today || 0 }}</p></div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }"><p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Delivery Rate</p><p class="text-xl font-bold mt-1" :style="{ color: 'var(--success)' }">{{ stats?.delivery_rate || 0 }}%</p></div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }"><p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Failed</p><p class="text-xl font-bold mt-1" :style="{ color: stats?.failed_count > 0 ? 'var(--danger)' : 'var(--success)' }">{{ stats?.failed_count || 0 }}</p></div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }"><p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Read Rate</p><p class="text-xl font-bold mt-1" :style="{ color: 'var(--info)' }">{{ stats?.read_rate || 0 }}%</p></div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }"><p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Comm Score</p><p class="text-xl font-bold mt-1" :style="{ color: stats?.comm_score >= 80 ? 'var(--success)' : 'var(--warning)' }">{{ stats?.comm_score || 0 }}</p></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <SkCard title="📊 Channel Breakdown" size="sm">
        <div v-if="stats?.channel_breakdown?.length" class="space-y-2">
          <div v-for="c in stats.channel_breakdown" :key="c.channel" class="flex justify-between items-center py-1 text-sm">
            <span :style="{ color: 'var(--text-primary)' }">{{ channelLabel(c.channel) }}</span>
            <div class="flex items-center gap-2"><span class="text-xs" :style="{ color: 'var(--text-muted)' }">{{ c.count }}</span>
            <div class="w-16 h-1.5 rounded overflow-hidden" :style="{ background: 'var(--bg-hover)' }"><div class="h-full rounded" :style="{ width: barPct(c.count), background: channelColor(c.channel) }"></div></div></div>
          </div>
        </div>
      </SkCard>

      <SkCard title="⚠️ Recent Failures" size="sm">
        <div v-if="stats?.recent_failures?.length" class="space-y-1">
          <div v-for="f in stats.recent_failures" :key="f.id" class="flex justify-between items-center py-1 text-sm border-b" :style="{ borderColor: 'var(--border-light)' }">
            <span :style="{ color: 'var(--text-primary)' }">{{ f.channel }} · {{ f.recipient }}</span>
            <span class="text-xs" :style="{ color: 'var(--danger)' }">{{ f.error_code }}</span>
          </div>
        </div>
        <div v-else class="text-center py-4"><p class="sk-caption">No recent failures</p></div>
      </SkCard>

      <SkCard title="🎯 Active Campaigns" size="sm">
        <div v-if="stats?.active_campaigns?.length" class="space-y-2">
          <div v-for="c in stats.active_campaigns" :key="c.id" class="flex justify-between items-center py-1 text-sm">
            <div><p class="font-medium" :style="{ color: 'var(--text-primary)' }">{{ c.campaign_name }}</p><p class="text-[10px]" :style="{ color: 'var(--text-muted)' }">{{ c.campaign_type }} · {{ c.recipient_count }} recipients</p></div>
            <span class="text-xs font-bold" :style="{ color: 'var(--success)' }">{{ c.delivery_rate }}%</span>
          </div>
        </div>
        <div v-else class="text-center py-4"><p class="sk-caption">No active campaigns</p></div>
      </SkCard>
    </div>
  </div>
</template>

<script setup>
import SkCard from '@/Enterprise/Components/Cards/Card.vue';
defineProps({ data: Object, stats: Object });
function channelLabel(c) { const m = { whatsapp:'💬 WA', email:'📧 Email', sms:'📱 SMS', push:'📲 Push', internal:'📥 Internal' }; return m[c] || c; }
function channelColor(c) { const m = { whatsapp:'var(--success)', email:'var(--primary)', sms:'var(--warning)', push:'var(--info)', internal:'var(--text-muted)' }; return m[c] || 'var(--text-muted)'; }
function barPct(c) { const max = Math.max(...(stats?.channel_breakdown?.map(d => d.count) || [0]), 1); return ((c || 0) / max * 100).toFixed(1) + '%'; }
</script>
