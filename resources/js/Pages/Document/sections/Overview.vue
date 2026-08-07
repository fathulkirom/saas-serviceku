<template>
  <div class="space-y-5">
    <!-- KPI Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Total Documents</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--primary)' }">{{ stats?.total_documents || 0 }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Pending Approvals</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--warning)' }">{{ stats?.pending_approvals || 0 }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">KB Articles</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--success)' }">{{ stats?.kb_articles || 0 }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Uploaded Today</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--info)' }">{{ stats?.uploaded_today || 0 }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Expired</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--danger)' }">{{ stats?.expired_docs || 0 }}</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <!-- Pending Approvals -->
      <SkCard title="✅ Pending My Approval" size="sm">
        <div v-if="stats?.my_approvals?.length" class="space-y-2">
          <div v-for="a in stats.my_approvals" :key="a.id" class="flex justify-between items-center py-1 text-sm">
            <div class="min-w-0 flex-1">
              <p class="font-medium truncate" :style="{ color: 'var(--text-primary)' }">{{ a.document_title }}</p>
              <p class="text-[10px]" :style="{ color: 'var(--text-muted)' }">{{ a.requester_name }} · {{ formatTimeAgo(a.requested_at) }}</p>
            </div>
            <span class="text-[10px] font-bold ml-2" :style="{ color: a.sla_breach ? 'var(--danger)' : 'var(--text-muted)' }">{{ a.sla_deadline ? formatDate(a.sla_deadline) : '' }}</span>
          </div>
        </div>
        <div v-else class="text-center py-4"><p class="sk-caption">No pending approvals</p></div>
      </SkCard>

      <!-- Recent Documents -->
      <SkCard title="📄 Recent Documents" size="sm">
        <div v-if="stats?.recent_docs?.length" class="space-y-2">
          <div v-for="d in stats.recent_docs" :key="d.id" class="flex justify-between items-center py-1 text-sm">
            <div class="min-w-0 flex-1">
              <p class="font-medium truncate" :style="{ color: 'var(--text-primary)' }">{{ d.title }}</p>
              <p class="text-[10px]" :style="{ color: 'var(--text-muted)' }">{{ d.owner_name }} · v{{ d.version }}</p>
            </div>
            <span class="text-[10px] px-1.5 py-0.5 rounded font-bold ml-2" :style="fileTypeStyle(d.file_type)">{{ d.file_type }}</span>
          </div>
        </div>
      </SkCard>

      <!-- Top Knowledge -->
      <SkCard title="📚 Top Knowledge" size="sm">
        <div v-if="stats?.top_knowledge?.length" class="space-y-2">
          <div v-for="k in stats.top_knowledge" :key="k.id" class="flex justify-between items-center py-1 text-sm">
            <span class="truncate" :style="{ color: 'var(--text-primary)' }">{{ k.title }}</span>
            <div class="flex items-center gap-2 ml-2 text-xs">
              <span :style="{ color: 'var(--text-muted)' }">👁️ {{ k.view_count }}</span>
              <span :style="{ color: 'var(--warning)' }">⭐ {{ k.rating }}</span>
            </div>
          </div>
        </div>
      </SkCard>
    </div>

    <!-- Team Activity -->
    <SkCard title="💬 Team Activity" size="md">
      <div v-if="stats?.team_activity?.length" class="space-y-1">
        <div v-for="a in stats.team_activity" :key="a.id" class="flex justify-between items-center py-1.5 text-sm"
          :style="{ borderBottom: '1px solid var(--border-light)' }">
          <span :style="{ color: 'var(--text-primary)' }">{{ a.message }}</span>
          <span class="text-xs" :style="{ color: 'var(--text-muted)' }">{{ formatTimeAgo(a.created_at) }}</span>
        </div>
      </div>
      <div v-else class="text-center py-4"><p class="sk-caption">No recent activity</p></div>
    </SkCard>
  </div>
</template>

<script setup>
import SkCard from '@/Enterprise/Components/Cards/Card.vue';

defineProps({
  data: { type: Object, default: () => ({}) },
  stats: { type: Object, default: () => ({}) },
});

function formatDate(v) {
  if (!v) return '-';
  return new Date(v).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
}
function formatTimeAgo(v) {
  if (!v) return '-';
  const diff = Date.now() - new Date(v).getTime();
  const mins = Math.floor(diff / 60000);
  if (mins < 60) return mins + 'm ago';
  const hours = Math.floor(mins / 60);
  if (hours < 24) return hours + 'h ago';
  return Math.floor(hours / 24) + 'd ago';
}
function fileTypeStyle(t) {
  const map = { pdf: { background: 'var(--danger-soft)', color: 'var(--danger)' }, docx: { background: 'var(--primary-soft)', color: 'var(--primary)' }, xlsx: { background: 'var(--success-soft)', color: 'var(--success)' }, png: { background: 'var(--info-soft)', color: 'var(--info)' } };
  return map[t] || { background: 'var(--bg-hover)', color: 'var(--text-muted)' };
}
</script>
