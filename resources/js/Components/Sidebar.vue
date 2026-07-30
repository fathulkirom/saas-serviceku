<script setup>
import { ref, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const page = usePage()
const role = computed(() => page.props.auth?.user?.role)
const collapsed = ref(false)
const openGroups = ref({})

const toggleGroup = (key) => {
  openGroups.value[key] = !openGroups.value[key]
}

const isGroupOpen = (key) => !!openGroups.value[key]

const isActive = (href) => {
  return page.url?.startsWith(href)
}

// ─── Menu Definitions ──────────────────────────────────────────────────────

const ownerMenu = [
  {
    group: 'Ringkasan',
    key: 'ringkasan',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>`,
    items: [
      { label: 'Dashboard', href: '/dashboard' },
      { label: 'Analitik Bisnis', href: '/analytics' },
      { label: 'Laporan Keuangan', href: '/reports/finance' },
    ]
  },
  {
    group: 'Manajemen',
    key: 'manajemen',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" /></svg>`,
    items: [
      { label: 'Cabang', href: '/branches' },
      { label: 'Pengguna & Staf', href: '/users' },
      { label: 'Struktur Role', href: '/roles' },
    ]
  },
  {
    group: 'Operasional',
    key: 'operasional',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z" /></svg>`,
    items: [
      { label: 'Tiket Servis', href: '/tickets' },
      { label: 'Sparepart', href: '/spareparts' },
      { label: 'Pelanggan', href: '/customers' },
      { label: 'Keuangan', href: '/finances' },
      { label: 'Pengiriman', href: '/deliveries' },
    ]
  },
  {
    group: 'Pengaturan',
    key: 'pengaturan',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>`,
    items: [
      { label: 'Pengaturan Aplikasi', href: '/settings' },
      { label: 'Audit Log', href: '/audit-logs' },
    ]
  },
]

const managerMenu = [
  {
    group: 'Ringkasan',
    key: 'ringkasan',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>`,
    items: [
      { label: 'Dashboard', href: '/dashboard' },
      { label: 'Laporan Cabang', href: '/reports/branch' },
    ]
  },
  {
    group: 'Tim & Staf',
    key: 'tim',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>`,
    items: [
      { label: 'Manajemen Staf', href: '/users' },
      { label: 'Jadwal Kerja', href: '/schedules' },
    ]
  },
  {
    group: 'Operasional',
    key: 'operasional',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z" /></svg>`,
    items: [
      { label: 'Tiket Servis', href: '/tickets' },
      { label: 'Pelanggan', href: '/customers' },
      { label: 'Sparepart', href: '/spareparts' },
      { label: 'Keuangan', href: '/finances' },
    ]
  },
]

const adminMenu = [
  {
    group: 'Dashboard',
    key: 'dashboard',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>`,
    items: [
      { label: 'Dashboard', href: '/dashboard' },
    ]
  },
  {
    group: 'Tiket & Servis',
    key: 'tiket',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a3 3 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" /></svg>`,
    items: [
      { label: 'Buat Tiket', href: '/tickets/create' },
      { label: 'Semua Tiket', href: '/tickets' },
      { label: 'Pelanggan', href: '/customers' },
    ]
  },
  {
    group: 'Pembayaran',
    key: 'pembayaran',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" /></svg>`,
    items: [
      { label: 'Terima Pembayaran', href: '/payments/receive' },
      { label: 'Riwayat Transaksi', href: '/payments/history' },
    ]
  },
]

const csMenu = [
  {
    group: 'Dashboard',
    key: 'dashboard',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>`,
    items: [
      { label: 'Dashboard', href: '/dashboard' },
    ]
  },
  {
    group: 'Penerimaan',
    key: 'penerimaan',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" /></svg>`,
    items: [
      { label: 'Penerimaan Barang', href: '/reception' },
      { label: 'Buat Tiket', href: '/tickets/create' },
      { label: 'Daftar Tiket', href: '/tickets' },
    ]
  },
  {
    group: 'Pelanggan',
    key: 'pelanggan',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>`,
    items: [
      { label: 'Data Pelanggan', href: '/customers' },
    ]
  },
]

const kasirMenu = [
  {
    group: 'Dashboard',
    key: 'dashboard',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>`,
    items: [
      { label: 'Dashboard', href: '/dashboard' },
    ]
  },
  {
    group: 'Kasir',
    key: 'kasir',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" /></svg>`,
    items: [
      { label: 'Terima Pembayaran', href: '/payments/receive' },
      { label: 'Cetak Invoice', href: '/invoices' },
      { label: 'Riwayat Transaksi', href: '/payments/history' },
    ]
  },
]

const teknisiMenu = [
  {
    group: 'Dashboard',
    key: 'dashboard',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>`,
    items: [
      { label: 'Dashboard', href: '/dashboard' },
    ]
  },
  {
    group: 'Antrean Servis',
    key: 'antrean',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z" /></svg>`,
    items: [
      { label: 'Antrean Saya', href: '/technician/queue' },
      { label: 'Update Status', href: '/technician/status' },
    ]
  },
  {
    group: 'Sparepart',
    key: 'sparepart',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" /></svg>`,
    items: [
      { label: 'Request Sparepart', href: '/technician/spareparts/request' },
      { label: 'Riwayat Request', href: '/technician/spareparts/history' },
    ]
  },
]

const kurirMenu = [
  {
    group: 'Dashboard',
    key: 'dashboard',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>`,
    items: [
      { label: 'Dashboard', href: '/dashboard' },
    ]
  },
  {
    group: 'Pengiriman',
    key: 'pengiriman',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" /></svg>`,
    items: [
      { label: 'Jadwal Antar/Jemput', href: '/courier/schedule' },
      { label: 'Tugas Aktif', href: '/courier/tasks' },
      { label: 'Update Status Kirim', href: '/courier/update' },
      { label: 'Riwayat Pengiriman', href: '/courier/history' },
    ]
  },
]

const partnerMenu = [
  {
    group: 'Partner',
    key: 'partner',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" /></svg>`,
    items: [
      { label: 'Profil Partner', href: '/partner/profile' },
      { label: 'Tiket Terkait', href: '/partner/tickets' },
    ]
  },
]

const menuMap = {
  owner: ownerMenu,
  manager: managerMenu,
  admin: adminMenu,
  cs: csMenu,
  kasir: kasirMenu,
  teknisi: teknisiMenu,
  kurir: kurirMenu,
  partner: partnerMenu,
}

const activeMenu = computed(() => menuMap[role.value] ?? [])

const roleBadge = computed(() => {
  const map = {
    owner: { label: 'Owner', cls: 'bg-rose-100 text-rose-700' },
    manager: { label: 'Manager', cls: 'bg-orange-100 text-orange-700' },
    admin: { label: 'Admin', cls: 'bg-sky-100 text-sky-700' },
    cs: { label: 'CS', cls: 'bg-teal-100 text-teal-700' },
    kasir: { label: 'Kasir', cls: 'bg-amber-100 text-amber-700' },
    teknisi: { label: 'Teknisi', cls: 'bg-indigo-100 text-indigo-700' },
    kurir: { label: 'Kurir', cls: 'bg-lime-100 text-lime-700' },
    partner: { label: 'Partner', cls: 'bg-slate-100 text-slate-600' },
  }
  return map[role.value] ?? { label: role.value, cls: 'bg-slate-100 text-slate-600' }
})

const user = computed(() => page.props.auth?.user)
const initials = computed(() => {
  const name = user.value?.name ?? ''
  return name.split(' ').slice(0, 2).map(n => n[0]).join('').toUpperCase()
})
</script>

<template>
  <!-- Sidebar Wrapper -->
  <aside
    :class="[
      'relative flex flex-col h-screen bg-white border-r border-slate-100 transition-all duration-300 ease-in-out select-none',
      collapsed ? 'w-[60px]' : 'w-[220px]'
    ]"
  >
    <!-- ── Logo / Brand ─────────────────────────────── -->
    <div class="flex items-center h-14 px-3 border-b border-slate-100 shrink-0 gap-2.5">
      <!-- Logo mark -->
      <div class="flex items-center justify-center w-7 h-7 rounded-lg bg-slate-800 shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="white" class="w-4 h-4">
          <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z" />
        </svg>
      </div>
      <!-- Brand name -->
      <Transition name="fade-slide">
        <span v-if="!collapsed" class="text-sm font-bold tracking-tight text-slate-800 whitespace-nowrap">
          service<span class="text-rose-500">ku</span>
        </span>
      </Transition>
      <!-- Spacer -->
      <div class="flex-1" v-if="!collapsed" />
      <!-- Collapse toggle -->
      <button
        @click="collapsed = !collapsed"
        class="flex items-center justify-center w-6 h-6 rounded-md text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors"
        :title="collapsed ? 'Expand' : 'Collapse'"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          fill="none" viewBox="0 0 24 24"
          stroke-width="2" stroke="currentColor"
          :class="['w-3.5 h-3.5 transition-transform duration-300', collapsed ? 'rotate-180' : '']"
        >
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
        </svg>
      </button>
    </div>

    <!-- ── Nav Menu ──────────────────────────────────── -->
    <nav class="flex-1 overflow-y-auto overflow-x-hidden py-3 px-2 space-y-0.5">
      <template v-for="group in activeMenu" :key="group.key">
        <!-- Group Header -->
        <div
          v-if="!collapsed"
          class="flex items-center gap-2 px-2 py-1.5 mt-2 first:mt-0 cursor-pointer rounded-md hover:bg-slate-50 transition-colors"
          @click="toggleGroup(group.key)"
        >
          <span v-html="group.icon" class="text-slate-400 shrink-0" />
          <span class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 flex-1">{{ group.group }}</span>
          <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none" viewBox="0 0 24 24"
            stroke-width="2.5" stroke="currentColor"
            :class="['w-3 h-3 text-slate-300 transition-transform duration-200', isGroupOpen(group.key) ? '' : '-rotate-90']"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
          </svg>
        </div>

        <!-- Collapsed: show icon-only items -->
        <div v-if="collapsed" class="space-y-0.5 mt-1">
          <template v-for="item in group.items" :key="item.href">
            <Link
              :href="item.href"
              :title="item.label"
              :class="[
                'flex items-center justify-center w-full h-8 rounded-md transition-colors text-slate-500 hover:bg-slate-100 hover:text-slate-800',
                isActive(item.href) ? 'bg-slate-100 text-slate-800 font-medium' : ''
              ]"
            >
              <span class="w-1.5 h-1.5 rounded-full bg-current opacity-60" />
            </Link>
          </template>
        </div>

        <!-- Expanded items -->
        <Transition name="collapse" v-else>
          <div v-show="isGroupOpen(group.key) !== false" class="space-y-0.5 mt-0.5">
            <template v-for="item in group.items" :key="item.href">
              <Link
                :href="item.href"
                :class="[
                  'flex items-center gap-2.5 px-3 py-1.5 rounded-md text-[13px] transition-colors',
                  isActive(item.href)
                    ? 'bg-slate-100 text-slate-800 font-medium'
                    : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700'
                ]"
              >
                <span
                  :class="[
                    'w-1 h-1 rounded-full shrink-0 transition-colors',
                    isActive(item.href) ? 'bg-rose-400' : 'bg-slate-300'
                  ]"
                />
                {{ item.label }}
              </Link>
            </template>
          </div>
        </Transition>
      </template>
    </nav>

    <!-- ── User Footer ───────────────────────────────── -->
    <div class="shrink-0 border-t border-slate-100 p-2">
      <Link
        href="/profile"
        :class="[
          'flex items-center gap-2.5 rounded-md px-2 py-2 hover:bg-slate-50 transition-colors group',
          collapsed ? 'justify-center' : ''
        ]"
        title="Profil Saya"
      >
        <!-- Avatar -->
        <div class="flex items-center justify-center w-7 h-7 rounded-full bg-slate-200 text-slate-600 text-[10px] font-bold shrink-0">
          {{ initials || '?' }}
        </div>
        <!-- Info -->
        <Transition name="fade-slide">
          <div v-if="!collapsed" class="flex-1 min-w-0">
            <div class="text-[12px] font-medium text-slate-700 truncate leading-tight">{{ user?.name ?? 'Pengguna' }}</div>
            <span :class="['inline-block text-[9px] font-semibold px-1.5 py-0.5 rounded-full mt-0.5 leading-none', roleBadge.cls]">
              {{ roleBadge.label }}
            </span>
          </div>
        </Transition>
        <!-- Logout link -->
        <Transition name="fade-slide">
          <Link
            v-if="!collapsed"
            href="/logout"
            method="post"
            as="button"
            class="shrink-0 text-slate-300 hover:text-rose-400 transition-colors opacity-0 group-hover:opacity-100"
            title="Keluar"
            @click.stop
          >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-3.5 h-3.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
            </svg>
          </Link>
        </Transition>
      </Link>
    </div>
  </aside>
</template>

<style scoped>
/* Fade + slide for brand text & user info */
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateX(-6px);
}

/* Collapse animation for menu groups */
.collapse-enter-active,
.collapse-leave-active {
  transition: max-height 0.2s ease, opacity 0.2s ease;
  overflow: hidden;
  max-height: 500px;
}
.collapse-enter-from,
.collapse-leave-to {
  max-height: 0;
  opacity: 0;
}

/* Scrollbar */
nav::-webkit-scrollbar {
  width: 3px;
}
nav::-webkit-scrollbar-track {
  background: transparent;
}
nav::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 99px;
}
</style>
