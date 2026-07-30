<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const page = usePage()
const user = computed(() => page.props.auth?.user)
const role = computed(() => user.value?.role)

// Minimalist breadcrumbs computed from url or page props
const pageTitle = computed(() => page.props.title || 'Dashboard')

const initials = computed(() => {
  const name = user.value?.name ?? ''
  return name.split(' ').slice(0, 2).map(n => n[0]).join('').toUpperCase()
})
</script>

<template>
  <header class="flex items-center justify-between h-14 px-4 bg-white border-b border-slate-100 shrink-0">
    <!-- ── Left: Page Title / Breadcrumbs ── -->
    <div class="flex items-center gap-3">
      <h1 class="text-[15px] font-semibold text-slate-800 tracking-tight">{{ pageTitle }}</h1>
    </div>

    <!-- ── Center: Global Search ── -->
    <div class="hidden md:flex flex-1 max-w-md mx-6">
      <div class="relative w-full">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400">
          <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
        </svg>
        <input 
          type="text" 
          placeholder="Cari tiket, pelanggan, atau sparepart..." 
          class="w-full h-8 pl-9 pr-3 text-[13px] bg-slate-50 border border-slate-200 rounded-md text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-300 focus:border-slate-300 transition-shadow"
        >
        <div class="absolute right-2 top-1/2 -translate-y-1/2 flex gap-0.5">
          <kbd class="px-1.5 py-0.5 text-[9px] font-medium bg-white border border-slate-200 rounded text-slate-400">⌘</kbd>
          <kbd class="px-1.5 py-0.5 text-[9px] font-medium bg-white border border-slate-200 rounded text-slate-400">K</kbd>
        </div>
      </div>
    </div>

    <!-- ── Right: Actions & Profile ── -->
    <div class="flex items-center gap-3 shrink-0">
      
      <!-- Quick Action: Create Ticket (Only for roles that can create) -->
      <Link 
        v-if="['owner', 'manager', 'admin', 'cs'].includes(role)"
        href="/tickets/create"
        class="hidden sm:flex items-center gap-1.5 px-2.5 h-8 bg-slate-800 hover:bg-slate-700 text-white rounded-md transition-colors text-[12px] font-medium"
      >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        Tiket Baru
      </Link>

      <div class="w-px h-5 bg-slate-200 hidden sm:block mx-1"></div>

      <!-- Notifications -->
      <button class="relative flex items-center justify-center w-8 h-8 rounded-md text-slate-500 hover:bg-slate-50 hover:text-slate-800 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-4 h-4">
          <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
        </svg>
        <span class="absolute top-1.5 right-1.5 flex h-2 w-2">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
          <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
        </span>
      </button>

      <!-- Profile Dropdown Trigger (Placeholder for actual <Dropdown> component) -->
      <button class="flex items-center gap-2 hover:bg-slate-50 p-1 pr-2 rounded-md transition-colors border border-transparent hover:border-slate-100">
        <div class="flex items-center justify-center w-7 h-7 rounded-full bg-slate-100 border border-slate-200 text-slate-600 text-[10px] font-bold">
          {{ initials || '?' }}
        </div>
        <div class="hidden sm:flex flex-col items-start">
          <span class="text-[12px] font-semibold text-slate-700 leading-none">{{ user?.name ?? 'Pengguna' }}</span>
        </div>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3 h-3 text-slate-400">
          <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
        </svg>
      </button>

    </div>
  </header>
</template>
