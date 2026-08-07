<template>
  <div class="space-y-5">
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }"><p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Services Active</p><p class="text-xl font-bold mt-1" :style="{ color: 'var(--primary)' }">{{ stats?.active_services || 0 }}</p></div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }"><p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Ready Pickup</p><p class="text-xl font-bold mt-1" :style="{ color: 'var(--success)' }">{{ stats?.ready_pickup || 0 }}</p></div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }"><p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Active Warranty</p><p class="text-xl font-bold mt-1" :style="{ color: 'var(--info)' }">{{ stats?.active_warranties || 0 }}</p></div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }"><p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Points</p><p class="text-xl font-bold mt-1" :style="{ color: 'var(--warning)' }">{{ stats?.reward_points || 0 }}</p></div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }"><p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Total Spending</p><p class="text-xl font-bold mt-1" :style="{ color: 'var(--success)' }">Rp {{ formatNumber(stats?.total_spending) }}</p></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <!-- Pending Quotations for Approval (Sprint v2.0E) -->
      <SkCard title="💰 Pending Approvals" size="sm" v-if="pendingQuotations.length">
        <div class="space-y-3">
          <div v-for="q in pendingQuotations" :key="q.id" class="p-3 rounded-lg border" :style="{ borderColor: 'var(--warning-soft)', background: 'var(--bg-surface)' }">
            <div class="flex justify-between mb-2">
              <span class="text-xs font-bold" :style="{ color: 'var(--text-primary)' }">#{{ q.service_tracking_code }}</span>
              <span class="text-[10px] px-1.5 py-0.5 rounded font-bold" :style="{ background: 'var(--warning-soft)', color: 'var(--warning-text)' }">Menunggu</span>
            </div>
            <p class="text-xs mb-2" :style="{ color: 'var(--text-muted)' }">{{ q.device_name || 'Device' }}</p>
            <div class="text-sm font-bold mb-3" :style="{ color: 'var(--text-primary)' }">Rp {{ formatNumber(q.total_cost) }}</div>
            <div class="flex gap-2">
              <button @click="approveQuotation(q.id)" class="flex-1 px-3 py-1.5 rounded-lg text-xs font-bold text-white" style="background: var(--success)">✅ Setujui</button>
              <button @click="showReject(q.id)" class="flex-1 px-3 py-1.5 rounded-lg text-xs font-bold text-white" style="background: var(--danger)">❌ Tolak</button>
            </div>
            <div v-if="rejectQuotationId === q.id" class="mt-2">
              <input v-model="rejectReason" placeholder="Alasan penolakan..." class="w-full rounded border text-xs p-1.5 mb-1" />
              <button @click="doReject(q.id)" class="w-full px-3 py-1 rounded text-xs font-bold text-white" style="background: var(--danger)">Kirim Penolakan</button>
            </div>
          </div>
        </div>
      </SkCard>

      <SkCard title="🔧 My Active Services" size="sm">
        <div v-if="stats?.my_services?.length" class="space-y-2">
          <div v-for="s in stats.my_services" :key="s.id" class="py-1 text-sm border-b" :style="{ borderColor: 'var(--border-light)' }">
            <div class="flex justify-between"><span class="font-bold" :style="{ color: 'var(--text-primary)' }">{{ s.service_number }}</span><span class="px-1.5 py-0.5 rounded text-[10px] font-bold" :style="statusStyle(s.status)">{{ s.status }}</span></div>
            <p class="text-xs mt-0.5" :style="{ color: 'var(--text-muted)' }">{{ s.device_name }} · {{ s.problem?.substring(0, 60) }}</p>
          </div>
        </div>
        <div v-else class="text-center py-4"><p class="sk-caption">No active services</p></div>
      </SkCard>

      <SkCard title="📅 Upcoming Appointments" size="sm">
        <div v-if="stats?.appointments?.length" class="space-y-2">
          <div v-for="a in stats.appointments" :key="a.id" class="flex justify-between items-center py-1 text-sm">
            <div><p class="font-medium" :style="{ color: 'var(--text-primary)' }">{{ formatDate(a.appointment_date) }} {{ a.time_slot }}</p><p class="text-[10px]" :style="{ color: 'var(--text-muted)' }">{{ a.branch_name }} · {{ a.technician_name || 'Any' }}</p></div>
            <span class="text-[10px] px-1.5 py-0.5 rounded font-bold" :style="{ background: 'var(--primary-soft)', color: 'var(--primary)' }">{{ a.service_type }}</span>
          </div>
        </div>
        <div v-else class="text-center py-4"><p class="sk-caption">No upcoming appointments</p></div>
      </SkCard>

      <SkCard title="🧾 Recent Invoices" size="sm">
        <div v-if="stats?.recent_invoices?.length" class="space-y-2">
          <div v-for="i in stats.recent_invoices" :key="i.id" class="flex justify-between items-center py-1 text-sm">
            <div><span class="font-medium" :style="{ color: 'var(--text-primary)' }">{{ i.invoice_number }}</span><p class="text-[10px]" :style="{ color: 'var(--text-muted)' }">{{ formatDate(i.invoice_date) }}</p></div>
            <span class="font-bold" :style="{ color: i.status === 'paid' ? 'var(--success)' : 'var(--danger)' }">Rp {{ formatNumber(i.total_amount) }}</span>
          </div>
        </div>
      </SkCard>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import SkCard from '@/Enterprise/Components/Cards/Card.vue';

defineProps({ data: Object, stats: Object });

const page = usePage();
const rejectQuotationId = ref(null);
const rejectReason = ref('');
const pendingQuotations = ref([]);
const loadingQuotations = ref(false);

onMounted(async () => {
  loadingQuotations.value = true;
  try {
    const r = await fetch('/api/customer/pending-quotations', {
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': page.props.csrf_token || '' },
    });
    if (r.ok) {
      const data = await r.json();
      pendingQuotations.value = data.quotations || [];
    }
  } catch (e) { /* silently fail */ }
  finally { loadingQuotations.value = false; }
});

function formatNumber(v) { return v != null ? Number(v).toLocaleString('id-ID') : '0'; }
function formatDate(v) { if (!v) return '-'; return new Date(v).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }); }
function statusStyle(s) {
  const map = { completed: { background: 'var(--success-soft)', color: 'var(--success)' }, repairing: { background: 'var(--warning-soft)', color: 'var(--warning)' }, diagnosing: { background: 'var(--info-soft)', color: 'var(--info)' } };
  return map[s] || { background: 'var(--bg-hover)', color: 'var(--text-muted)' };
}

function showReject(qId) { rejectQuotationId.value = qId; rejectReason.value = ''; }

async function approveQuotation(qId) {
  try {
    const r = await fetch(`/quotations/${qId}/approve`, {
      method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': page.props.csrf_token || '', 'Accept': 'application/json' },
      body: JSON.stringify({ method: 'customer_portal' }),
    });
    if (r.ok) window.location.reload();
    else alert('Gagal menyetujui estimasi.');
  } catch (e) { alert('Gagal menyetujui.'); }
}

async function doReject(qId) {
  if (!rejectReason.value.trim()) return alert('Alasan penolakan wajib diisi.');
  try {
    const r = await fetch(`/quotations/${qId}/reject`, {
      method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': page.props.csrf_token || '', 'Accept': 'application/json' },
      body: JSON.stringify({ reason: rejectReason.value }),
    });
    if (r.ok) window.location.reload();
    else alert('Gagal menolak estimasi.');
  } catch (e) { alert('Gagal menolak.'); }
}
</script>
