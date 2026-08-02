<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import KCard from '@/Components/KCard.vue'
import KButton from '@/Components/KButton.vue'
import KBadge from '@/Components/KBadge.vue'
import ServiceHeader from '@/Components/Services/ServiceHeader.vue'
import ServiceStatusStepper from '@/Components/Services/ServiceStatusStepper.vue'
import { formatCurrency, formatDate, formatDateTime } from '@/Composables/useFormatter'

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
  service: Object,
  customerSummary: Object,
  techSummary: Object,
  templatesKeluar: Array, templatesMasuk: Array,
  products: Array, users: Array,
  previousServices: Array, driveConnected: Boolean,
})

const activeTab = ref('overview')

const tabs = [
  { key: 'overview', label: 'Overview', icon: '📋' },
  { key: 'checklist', label: 'Checklist', icon: '✅', count: props.service?.checklist_results?.length },
  { key: 'diagnosis', label: 'Diagnosis', icon: '🔍', count: props.service?.diagnosis ? 1 : 0 },
  { key: 'quotation', label: 'Quotation', icon: '💰', count: props.service?.quotations?.length },
  { key: 'parts', label: 'Part', icon: '🔩', count: props.service?.required_parts?.length },
  { key: 'worklog', label: 'Worklog', icon: '📝', count: props.service?.work_orders?.flatMap(w => w.worklogs || [])?.length },
  { key: 'timeline', label: 'Timeline', icon: '📅' },
  { key: 'photos', label: 'Foto', icon: '📸', count: props.service?.photos?.length },
  { key: 'qc', label: 'QC', icon: '🔬', count: props.service?.qc_checks?.length },
  { key: 'delivery', label: 'Delivery', icon: '🚚', count: props.service?.delivery ? 1 : 0 },
  { key: 'warranty', label: 'Garansi', icon: '🛡️', count: props.service?.warranty ? 1 : 0 },
  { key: 'communication', label: 'Komunikasi', icon: '💬' },
  { key: 'notes', label: 'Catatan', icon: '📝' },
  { key: 'events', label: 'Event Log', icon: '📌' },
]

const statusColor = (s) => ({
  'menunggu_alokasi': 'bg-amber-100 text-amber-800',
  'diterima': 'bg-blue-100 text-blue-800',
  'dikerjakan': 'bg-orange-100 text-orange-800',
  'selesai': 'bg-green-100 text-green-800',
  'siap_diambil': 'bg-indigo-100 text-indigo-800',
  'cancel': 'bg-red-100 text-red-800',
}[s] || 'bg-gray-100 text-gray-600')
</script>

<template>
  <div class="flex gap-0 h-full">
    <!-- MAIN CONTENT -->
    <div class="flex-1 min-w-0 overflow-y-auto">
      <ServiceHeader :service="service" />
      <ServiceStatusStepper :service="service" class="px-6 py-3" />

      <!-- TABS -->
      <div class="flex border-b border-gray-200 dark:border-gray-700 overflow-x-auto px-6 sticky top-0 bg-white dark:bg-gray-900 z-10">
        <button v-for="tab in tabs" :key="tab.key" @click="activeTab = tab.key"
          class="px-3 py-2.5 text-xs font-medium whitespace-nowrap border-b-2 transition-colors flex items-center gap-1"
          :class="activeTab === tab.key ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'">
          {{ tab.icon }} {{ tab.label }}
          <span v-if="tab.count > 0" class="text-[10px] bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded-full">{{ tab.count }}</span>
        </button>
      </div>

      <!-- TAB CONTENT -->
      <div class="p-6 space-y-4">
        <!-- Overview -->
        <div v-if="activeTab === 'overview'" class="space-y-4">
          <KCard>
            <h3 class="font-semibold mb-3">Informasi Service</h3>
            <dl class="grid grid-cols-2 gap-2 text-sm">
              <dt class="text-gray-500">Customer</dt><dd class="font-medium">{{ service.customer?.name || '-' }}</dd>
              <dt class="text-gray-500">Device</dt><dd class="font-medium">{{ service.tipe_unit || '-' }}</dd>
              <dt class="text-gray-500">IMEI/SN</dt><dd class="font-mono text-xs">{{ service.imei_sn || '-' }}</dd>
              <dt class="text-gray-500">Keluhan</dt><dd>{{ service.problem_description || '-' }}</dd>
              <dt class="text-gray-500">Prioritas</dt><dd><KBadge size="xs">Normal</KBadge></dd>
              <dt class="text-gray-500">Teknisi</dt><dd>{{ service.technician?.name || 'Belum ditugaskan' }}</dd>
              <dt class="text-gray-500">Status</dt><dd><KBadge size="xs" :class="statusColor(service.status)">{{ service.status }}</KBadge></dd>
              <dt class="text-gray-500">Estimasi Biaya</dt><dd class="font-semibold">{{ formatCurrency(service.estimated_cost) }}</dd>
            </dl>
          </KCard>
          <!-- Diagnosis Summary -->
          <KCard v-if="service.diagnosis">
            <h3 class="font-semibold mb-2">Diagnosis</h3>
            <p class="text-sm"><span class="text-gray-500">Temuan:</span> {{ service.diagnosis.findings }}</p>
            <p class="text-sm"><span class="text-gray-500">Solusi:</span> {{ service.diagnosis.solution }}</p>
            <p class="text-xs text-gray-400 mt-1">Estimasi: {{ service.diagnosis.estimated_minutes }} menit</p>
          </KCard>
        </div>

        <!-- Checklist -->
        <div v-if="activeTab === 'checklist'">
          <div v-if="!service.checklist_results?.length" class="text-center py-8 text-gray-400">Belum ada hasil checklist.</div>
          <div v-for="r in service.checklist_results" :key="r.id" class="flex justify-between items-center py-2 border-b border-gray-50 dark:border-gray-800 text-sm">
            <span>{{ r.item?.item_name }}</span>
            <span class="font-medium">{{ r.value }}{{ r.unit ? ' ' + r.unit : '' }}</span>
          </div>
        </div>

        <!-- Diagnosis -->
        <div v-if="activeTab === 'diagnosis'">
          <KCard v-if="service.diagnosis">
            <dl class="grid grid-cols-1 gap-3 text-sm">
              <div><dt class="text-gray-500 text-xs">Keluhan Customer</dt><dd class="mt-0.5">{{ service.diagnosis.customer_complaint || service.problem_description }}</dd></div>
              <div><dt class="text-gray-500 text-xs">Temuan Teknisi</dt><dd class="mt-0.5 font-medium">{{ service.diagnosis.findings }}</dd></div>
              <div><dt class="text-gray-500 text-xs">Penyebab</dt><dd class="mt-0.5">{{ service.diagnosis.cause || '-' }}</dd></div>
              <div><dt class="text-gray-500 text-xs">Solusi</dt><dd class="mt-0.5 text-green-700 font-medium">{{ service.diagnosis.solution }}</dd></div>
              <div class="flex gap-4"><dt class="text-gray-500 text-xs">Estimasi Biaya</dt><dd>{{ formatCurrency(service.diagnosis.estimated_cost) }}</dd><dt class="text-gray-500 text-xs">Estimasi Waktu</dt><dd>{{ service.diagnosis.estimated_minutes }} menit</dd></div>
            </dl>
          </KCard>
          <div v-else class="text-center py-8 text-gray-400">Belum ada diagnosis.</div>
        </div>

        <!-- Quotation -->
        <div v-if="activeTab === 'quotation'">
          <div v-if="!service.quotations?.length" class="text-center py-8 text-gray-400">Belum ada quotation.</div>
          <KCard v-for="q in service.quotations" :key="q.id" class="!p-3">
            <div class="flex justify-between items-center mb-2">
              <KBadge size="xs" :class="q.status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-gray-100'">{{ q.status }}</KBadge>
              <span class="text-xs text-gray-400">{{ formatDate(q.created_at) }}</span>
            </div>
            <p class="text-lg font-bold">{{ formatCurrency(q.total_cost) }}</p>
            <p v-if="q.notes" class="text-xs text-gray-500 mt-1">{{ q.notes }}</p>
          </KCard>
        </div>

        <!-- Parts -->
        <div v-if="activeTab === 'parts'">
          <div v-if="!service.required_parts?.length" class="text-center py-8 text-gray-400">Belum ada part.</div>
          <div v-for="p in service.required_parts" :key="p.id" class="flex justify-between items-center py-2 border-b border-gray-50 dark:border-gray-800 text-sm">
            <div>
              <span class="font-medium">{{ p.part_name }}</span>
              <span class="text-gray-400 ml-2">x{{ p.qty }}</span>
            </div>
            <div class="flex items-center gap-2">
              <KBadge size="xs">{{ p.status }}</KBadge>
              <span class="font-medium" v-if="p.status === 'used'">{{ formatCurrency(p.subtotal) }}</span>
            </div>
          </div>
        </div>

        <!-- Worklog -->
        <div v-if="activeTab === 'worklog'">
          <div v-if="!service.work_orders?.some(w => w.worklogs?.length)" class="text-center py-8 text-gray-400">Belum ada worklog.</div>
          <div v-for="wo in service.work_orders" :key="wo.id">
            <div class="text-xs text-gray-400 font-medium mb-2">{{ wo.work_item || wo.title }} — {{ wo.technician?.name }}</div>
            <div v-for="wl in wo.worklogs" :key="wl.id" class="flex gap-3 pl-4 border-l-2 border-gray-200 pb-3 last:pb-0 text-sm">
              <span class="text-xs text-gray-400 whitespace-nowrap mt-0.5">{{ formatDateTime(wl.created_at) }}</span>
              <span>{{ wl.description }}</span>
            </div>
          </div>
        </div>

        <!-- Timeline placeholder -->
        <div v-if="activeTab === 'timeline'" class="text-center py-8 text-gray-400">Timeline lengkap ada di Customer 360.</div>

        <!-- Photos -->
        <div v-if="activeTab === 'photos'" class="grid grid-cols-3 gap-2">
          <div v-if="!service.photos?.length" class="col-span-3 text-center py-8 text-gray-400">Belum ada foto.</div>
          <img v-for="p in service.photos" :key="p.id" :src="p.photo_path" class="rounded-lg object-cover aspect-square" />
        </div>

        <!-- QC -->
        <div v-if="activeTab === 'qc'">
          <div v-if="!service.qc_checks?.length" class="text-center py-8 text-gray-400">Belum ada QC.</div>
          <div v-for="qc in service.qc_checks" :key="qc.id" class="flex justify-between py-1 text-sm">
            <span>{{ qc.item }}</span>
            <KBadge size="xs" :class="qc.result === 'pass' ? 'bg-green-100 text-green-700' : qc.result === 'fail' ? 'bg-red-100 text-red-700' : 'bg-gray-100'">{{ qc.result }}</KBadge>
          </div>
        </div>

        <!-- Delivery -->
        <div v-if="activeTab === 'delivery'">
          <KCard v-if="service.delivery">
            <dl class="grid grid-cols-2 gap-2 text-sm">
              <dt class="text-gray-500">Penerima</dt><dd>{{ service.delivery.received_by || '-' }}</dd>
              <dt class="text-gray-500">Telepon</dt><dd>{{ service.delivery.receiver_phone || '-' }}</dd>
              <dt class="text-gray-500">Hubungan</dt><dd>{{ service.delivery.receiver_relation || '-' }}</dd>
              <dt class="text-gray-500">Tanggal Serah</dt><dd>{{ service.delivery.picked_up_at ? formatDateTime(service.delivery.picked_up_at) : '-' }}</dd>
            </dl>
          </KCard>
          <div v-else class="text-center py-8 text-gray-400">Belum diserahkan.</div>
        </div>

        <!-- Warranty -->
        <div v-if="activeTab === 'warranty'">
          <KCard v-if="service.warranty">
            <dl class="grid grid-cols-2 gap-2 text-sm">
              <dt class="text-gray-500">Tipe</dt><dd>{{ service.warranty.warranty_type }}</dd>
              <dt class="text-gray-500">Durasi</dt><dd>{{ service.warranty.duration_days }} hari</dd>
              <dt class="text-gray-500">Mulai</dt><dd>{{ service.warranty.start_date }}</dd>
              <dt class="text-gray-500">Berakhir</dt><dd>{{ service.warranty.end_date }}</dd>
              <dt class="text-gray-500">Status</dt><dd><KBadge size="xs" :class="service.warranty.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100'">{{ service.warranty.status }}</KBadge></dd>
            </dl>
          </KCard>
          <div v-else class="text-center py-8 text-gray-400">Belum ada garansi.</div>
        </div>

        <!-- Remaining tabs: communication, notes, events — placeholder -->
        <div v-if="['communication','notes','events'].includes(activeTab)" class="text-center py-8 text-gray-400">
          Lihat detail lengkap di Customer 360.
        </div>
      </div>
    </div>

    <!-- RIGHT SIDEBAR -->
    <div class="w-72 shrink-0 border-l border-gray-200 dark:border-gray-700 overflow-y-auto p-4 space-y-4 hidden xl:block bg-gray-50 dark:bg-gray-900/50">
      <!-- Customer Card -->
      <KCard v-if="customerSummary" class="!p-3">
        <h4 class="text-xs font-semibold text-gray-400 uppercase mb-2">Customer</h4>
        <a :href="route('customers.show', customerSummary.id)" class="font-medium text-sm text-indigo-600 hover:underline block">{{ customerSummary.name }}</a>
        <div class="text-xs text-gray-500 mt-1">{{ customerSummary.phone }}</div>
        <div class="flex items-center gap-1 mt-2">
          <KBadge size="xs" :class="customerSummary.risk.level === 'high' ? 'bg-red-100 text-red-700' : customerSummary.risk.level === 'medium' ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700'">
            {{ customerSummary.risk.icon }} {{ customerSummary.risk.label }}
          </KBadge>
          <KBadge v-if="customerSummary.is_member" size="xs" class="bg-amber-100 text-amber-700">⭐ Member</KBadge>
        </div>
        <div class="grid grid-cols-2 gap-1 mt-2 text-xs text-gray-500">
          <div><span class="font-semibold text-gray-700">{{ customerSummary.service_count }}</span> servis</div>
          <div><span class="font-semibold text-gray-700">{{ formatCurrency(customerSummary.total_spending) }}</span></div>
          <div><span class="font-semibold text-gray-700">{{ customerSummary.device_count }}</span> perangkat</div>
          <div>Last: {{ customerSummary.last_visit }}</div>
        </div>
      </KCard>

      <!-- Technician Card -->
      <KCard v-if="techSummary" class="!p-3">
        <h4 class="text-xs font-semibold text-gray-400 uppercase mb-2">Teknisi</h4>
        <div class="font-medium text-sm">{{ techSummary.name }}</div>
        <div class="text-xs text-gray-500 mt-1">{{ techSummary.active_work_orders }} pekerjaan aktif</div>
      </KCard>

      <!-- Quick Actions -->
      <KCard class="!p-3">
        <h4 class="text-xs font-semibold text-gray-400 uppercase mb-2">Quick Actions</h4>
        <div class="space-y-1.5">
          <KButton size="xs" variant="outline" class="w-full justify-start text-left" @click="router.visit(route('services.edit', service.id))">✏️ Edit Service</KButton>
          <KButton size="xs" variant="outline" class="w-full justify-start text-left" @click="window.print()">🖨️ Print</KButton>
          <KButton v-if="service.customer?.phone" size="xs" variant="outline" class="w-full justify-start text-left" @click="window.open('https://wa.me/' + service.customer.phone.replace(/\D/g,''))">💬 WhatsApp Customer</KButton>
          <KButton size="xs" variant="outline" class="w-full justify-start text-left" @click="router.visit(route('services.ready-pickup', service.id), {method:'post'})">📦 Ready Pickup</KButton>
        </div>
      </KCard>
    </div>
  </div>
</template>
