<template>
  <div class="space-y-5">
    <!-- KPI Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Cash Balance</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--success)' }">Rp {{ formatNumber(stats?.cash_balance) }}</p>
        <p class="text-[10px]" :style="{ color: 'var(--text-muted)' }">{{ stats?.bank_count || 0 }} bank accounts</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Today Revenue</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--primary)' }">Rp {{ formatNumber(stats?.revenue_today) }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Today Expense</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--danger)' }">Rp {{ formatNumber(stats?.expense_today) }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">AR Outstanding</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--warning)' }">Rp {{ formatNumber(stats?.ar_outstanding) }}</p>
        <p class="text-[10px]" :style="{ color: 'var(--text-muted)' }">{{ stats?.ar_overdue || 0 }} overdue</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">AP Outstanding</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--danger)' }">Rp {{ formatNumber(stats?.ap_outstanding) }}</p>
        <p class="text-[10px]" :style="{ color: 'var(--text-muted)' }">{{ stats?.ap_due || 0 }} due soon</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Net Profit MTD</p>
        <p class="text-xl font-bold mt-1" :style="{ color: stats?.net_profit_mtd >= 0 ? 'var(--success)' : 'var(--danger)' }">Rp {{ formatNumber(stats?.net_profit_mtd) }}</p>
        <p class="text-[10px]" :style="{ color: 'var(--text-muted)' }">Margin {{ stats?.margin_pct || 0 }}%</p>
      </div>
    </div>

    <!-- Quick Summary Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <!-- Cash & Bank Summary -->
      <SkCard title="💵 Cash & Bank" size="sm">
        <div v-if="stats?.banks?.length" class="space-y-2">
          <div v-for="bank in stats.banks" :key="bank.id" class="flex justify-between items-center py-1">
            <div>
              <p class="text-sm font-bold" :style="{ color: 'var(--text-primary)' }">{{ bank.account_name }}</p>
              <p class="text-[10px]" :style="{ color: 'var(--text-muted)' }">{{ bank.bank_name }} · {{ bank.account_number }}</p>
            </div>
            <p class="text-sm font-bold" :style="{ color: 'var(--text-primary)' }">Rp {{ formatNumber(bank.current_balance) }}</p>
          </div>
        </div>
        <div v-else class="text-center py-4">
          <p class="sk-caption">Belum ada rekening bank</p>
        </div>
        <div class="mt-2 pt-2 border-t" :style="{ borderColor: 'var(--border-light)' }">
          <p class="text-xs font-bold text-right" :style="{ color: 'var(--primary)' }">Total: Rp {{ formatNumber(stats?.total_bank_balance) }}</p>
        </div>
      </SkCard>

      <!-- Top 5 AR -->
      <SkCard title="📥 Top Receivables" size="sm">
        <div v-if="stats?.top_ar?.length" class="space-y-2">
          <div v-for="ar in stats.top_ar" :key="ar.id" class="flex justify-between items-center py-1">
            <div class="min-w-0 flex-1">
              <p class="text-sm font-medium truncate" :style="{ color: 'var(--text-primary)' }">{{ ar.customer_name }}</p>
              <p class="text-[10px]" :style="{ color: 'var(--text-muted)' }">{{ ar.invoice_number }} · {{ ar.days_overdue }}d overdue</p>
            </div>
            <p class="text-sm font-bold ml-2" :style="{ color: 'var(--warning)' }">Rp {{ formatNumber(ar.outstanding) }}</p>
          </div>
        </div>
        <div v-else class="text-center py-4"><p class="sk-caption">No outstanding AR</p></div>
      </SkCard>

      <!-- Top 5 AP -->
      <SkCard title="📤 Top Payables" size="sm">
        <div v-if="stats?.top_ap?.length" class="space-y-2">
          <div v-for="ap in stats.top_ap" :key="ap.id" class="flex justify-between items-center py-1">
            <div class="min-w-0 flex-1">
              <p class="text-sm font-medium truncate" :style="{ color: 'var(--text-primary)' }">{{ ap.supplier_name }}</p>
              <p class="text-[10px]" :style="{ color: 'var(--text-muted)' }">{{ ap.invoice_number }} · Due {{ formatDate(ap.due_date) }}</p>
            </div>
            <p class="text-sm font-bold ml-2" :style="{ color: 'var(--danger)' }">Rp {{ formatNumber(ap.outstanding) }}</p>
          </div>
        </div>
        <div v-else class="text-center py-4"><p class="sk-caption">No outstanding AP</p></div>
      </SkCard>
    </div>

    <!-- Month-to-Month Revenue vs Expense Chart -->
    <SkCard title="Revenue vs Expense (6 Months)" size="md">
      <div v-if="stats?.monthly_trend?.length" class="space-y-1">
        <div v-for="m in stats.monthly_trend" :key="m.month" class="flex items-center gap-3 text-xs">
          <span class="w-14 text-right" :style="{ color: 'var(--text-muted)' }">{{ m.month_label }}</span>
          <div class="flex-1 h-5 flex rounded overflow-hidden" :style="{ background: 'var(--bg-hover)' }">
            <div :style="{ width: revenueBarWidth(m), background: 'var(--primary)', minWidth: m.revenue > 0 ? '2px' : '0' }"></div>
            <div :style="{ width: expenseBarWidth(m), background: 'var(--danger)', minWidth: m.expense > 0 ? '2px' : '0' }"></div>
          </div>
          <span class="w-20 text-right text-[10px] font-bold" :style="{ color: m.net >= 0 ? 'var(--success)' : 'var(--danger)' }">
            Rp {{ formatNumber(Math.abs(m.net)) }} {{ m.net >= 0 ? '▲' : '▼' }}
          </span>
        </div>
      </div>
    </SkCard>

    <!-- Recent Journal Entries -->
    <SkCard title="📝 Recent Journal Entries" size="md">
      <div v-if="stats?.recent_journals?.length" class="space-y-1">
        <div v-for="j in stats.recent_journals" :key="j.id" class="flex justify-between items-center py-1.5 text-sm"
          :style="{ borderBottom: '1px solid var(--border-light)' }">
          <div class="flex items-center gap-3">
            <span class="text-[10px] font-mono" :style="{ color: 'var(--text-muted)' }">{{ j.journal_number }}</span>
            <span :style="{ color: 'var(--text-primary)' }">{{ j.description }}</span>
            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold" :style="journalTypeStyle(j.journal_type)">{{ j.journal_type }}</span>
          </div>
          <div class="flex items-center gap-4">
            <span class="text-xs" :style="{ color: 'var(--text-muted)' }">{{ formatDate(j.journal_date) }}</span>
            <span class="text-xs font-bold" :style="{ color: 'var(--text-primary)' }">Rp {{ formatNumber(j.total_debit) }}</span>
          </div>
        </div>
      </div>
      <div v-else class="text-center py-4"><p class="sk-caption">No journal entries yet</p></div>
    </SkCard>

    <!-- Alerts -->
    <div v-if="alerts?.length" class="space-y-2">
      <div v-for="a in alerts" :key="a.id" class="flex items-center gap-3 p-3 rounded-lg text-sm"
        :style="{ background: alertBg(a.severity), color: 'var(--text-primary)', borderLeft: '4px solid ' + alertColor(a.severity) }">
        <span>{{ alertIcon(a.type) }}</span>
        <span class="flex-1">{{ a.message }}</span>
        <span class="text-[10px]" :style="{ color: 'var(--text-muted)' }">{{ formatDate(a.created_at) }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import SkCard from '@/Enterprise/Components/Cards/Card.vue';

const props = defineProps({
  data: { type: Object, default: () => ({}) },
  stats: { type: Object, default: () => ({}) },
  alerts: { type: Array, default: () => [] },
});

const maxAmount = props.stats?.monthly_trend?.length
  ? Math.max(...props.stats.monthly_trend.map(m => Math.max(m.revenue || 0, m.expense || 0)), 1)
  : 1;

function revenueBarWidth(m) {
  return ((m.revenue || 0) / maxAmount * 100).toFixed(1) + '%';
}
function expenseBarWidth(m) {
  return ((m.expense || 0) / maxAmount * 100).toFixed(1) + '%';
}
function formatNumber(v) {
  if (v == null) return '0';
  return Number(v).toLocaleString('id-ID');
}
function formatDate(v) {
  if (!v) return '-';
  return new Date(v).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}
function journalTypeStyle(t) {
  const map = { manual:'var(--info-soft)', automatic:'var(--success-soft)', adjustment:'var(--warning-soft)', closing:'var(--primary-soft)' };
  return { background: map[t] || 'var(--bg-hover)', color: 'var(--text-primary)' };
}
function alertBg(s) { return s === 'critical' ? 'var(--danger-soft)' : s === 'warning' ? 'var(--warning-soft)' : 'var(--info-soft)'; }
function alertColor(s) { return s === 'critical' ? 'var(--danger)' : s === 'warning' ? 'var(--warning)' : 'var(--info)'; }
function alertIcon(t) { return t === 'overdue' ? '⚠️' : t === 'low_balance' ? '💰' : t === 'budget' ? '🎯' : '📌'; }
</script>
