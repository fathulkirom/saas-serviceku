<template>
  <AuthenticatedLayout>
    <div class="flex flex-col gap-8 min-h-screen pb-12 bg-zinc-50/50">
      
      <!-- HERO HEADER -->
      <div class="relative overflow-hidden bg-white border-b border-zinc-200">
        <div class="absolute inset-0 bg-gradient-to-r from-indigo-50/50 to-emerald-50/50 opacity-50"></div>
        <div class="absolute right-0 top-0 w-96 h-96 bg-gradient-to-br from-indigo-100/40 to-purple-100/40 blur-3xl rounded-full -translate-y-1/2 translate-x-1/3"></div>
        <div class="px-8 py-10 relative z-10 max-w-[1600px] mx-auto w-full">
          <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
              <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white border border-zinc-200 text-xs font-semibold text-zinc-600 mb-4 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                {{ roleTitles[userRole] || 'Utama' }} Workspace
              </div>
              <h2 class="text-3xl md:text-4xl font-bold tracking-tight text-zinc-900">
                {{ greeting }}, <span class="bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 to-emerald-600">{{ $page.props.auth.user.name }}</span>
              </h2>
              <p class="text-zinc-500 font-medium mt-2 text-sm md:text-base">
                Berikut ringkasan aktivitas operasional Anda pada {{ currentDate }}
              </p>
            </div>
            
            <!-- QUICK ACTIONS -->
            <div class="flex flex-wrap items-center gap-3">
              <Button v-if="['owner', 'manager', 'admin'].includes(userRole)" variant="outline" class="bg-white/80 backdrop-blur-sm shadow-sm border-zinc-200 hover:bg-zinc-50 text-zinc-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Export Laporan
              </Button>
              <Link v-if="['cs', 'owner', 'manager', 'admin'].includes(userRole)" :href="route('services.create')">
                <Button class="bg-indigo-600 hover:bg-indigo-700 text-white shadow-md hover:shadow-lg transition-all group border-none">
                  <svg class="w-4 h-4 mr-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                  Buat Tiket Servis
                </Button>
              </Link>
              <Link v-if="['cashier'].includes(userRole)" :href="route('kas.index')">
                <Button class="bg-emerald-600 hover:bg-emerald-700 text-white shadow-md hover:shadow-lg transition-all group border-none">
                    <svg class="w-4 h-4 mr-2 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Buka Shift Kasir
                </Button>
              </Link>
            </div>
          </div>
        </div>
      </div>

      <div class="px-8 max-w-[1600px] mx-auto w-full">
        <!-- ROLE: OWNER / MANAGER / ADMIN -->
        <div v-if="['owner', 'manager', 'admin'].includes(userRole)" class="space-y-8">
          
          <!-- TOP STATS -->
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-2xl border border-zinc-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] hover:shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition-all relative overflow-hidden group">
              <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-full blur-3xl -mr-10 -mt-10 group-hover:bg-blue-100 transition-colors"></div>
              <div class="relative z-10">
                  <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Pendapatan</span>
                  </div>
                  <div>
                    <h3 class="text-3xl font-black text-zinc-900 tracking-tight">Rp {{ formatNumber(stats?.revenue_today ?? 0) }}</h3>
                    <p class="text-sm text-emerald-600 font-medium mt-2 flex items-center gap-1.5">
                        <span class="flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg></span>
                        +20.1% dari bulan lalu
                    </p>
                  </div>
              </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-zinc-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] hover:shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition-all relative overflow-hidden group">
              <div class="absolute top-0 right-0 w-32 h-32 bg-purple-50 rounded-full blur-3xl -mr-10 -mt-10 group-hover:bg-purple-100 transition-colors"></div>
              <div class="relative z-10">
                  <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 border border-purple-100 flex items-center justify-center text-purple-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <span class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Servis Baru</span>
                  </div>
                  <div>
                    <h3 class="text-3xl font-black text-zinc-900 tracking-tight">+{{ stats?.services_today ?? 0 }}</h3>
                    <p class="text-sm text-zinc-500 font-medium mt-2">Masuk hari ini</p>
                  </div>
              </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-zinc-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] hover:shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition-all relative overflow-hidden group">
              <div class="absolute top-0 right-0 w-32 h-32 bg-orange-50 rounded-full blur-3xl -mr-10 -mt-10 group-hover:bg-orange-100 transition-colors"></div>
              <div class="relative z-10">
                  <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 rounded-xl bg-orange-50 border border-orange-100 flex items-center justify-center text-orange-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Proses Servis</span>
                  </div>
                  <div>
                    <h3 class="text-3xl font-black text-zinc-900 tracking-tight">{{ stats?.active_services ?? 0 }}</h3>
                    <p class="text-sm text-zinc-500 font-medium mt-2">Dalam antrean teknisi</p>
                  </div>
              </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-zinc-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] hover:shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition-all relative overflow-hidden group">
              <div class="absolute top-0 right-0 w-32 h-32 bg-red-50 rounded-full blur-3xl -mr-10 -mt-10 group-hover:bg-red-100 transition-colors"></div>
              <div class="relative z-10">
                  <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 rounded-xl bg-red-50 border border-red-100 flex items-center justify-center text-red-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <span class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Alert Stok</span>
                  </div>
                  <div>
                    <h3 class="text-3xl font-black text-zinc-900 tracking-tight">{{ stats?.low_stock ?? 0 }}</h3>
                    <p class="text-sm text-red-600 font-semibold mt-2 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span> Butuh restock segera
                    </p>
                  </div>
              </div>
            </div>
          </div>

          <!-- SETUP PROGRESS CARD (Owner only, Sprint 7.5F) -->
          <SetupProgressCard v-if="setupSummary" :setupSummary="setupSummary" />

          <!-- MIDDLE SECTION -->
          <div class="grid grid-cols-1 lg:grid-cols-7 gap-6">
            <!-- CHART MOCKUP -->
            <div class="lg:col-span-4 bg-white border border-zinc-200 rounded-2xl p-6 shadow-sm flex flex-col">
              <div class="flex justify-between items-start mb-6">
                <div>
                  <h3 class="font-bold text-lg text-zinc-900 tracking-tight">Tren Penjualan & Servis</h3>
                  <p class="text-sm text-zinc-500 font-medium">Statistik 7 hari terakhir</p>
                </div>
                <KButton  class="text-zinc-400 hover:text-zinc-600 p-2 rounded-lg hover:bg-zinc-50 transition-colors">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                </KButton>
              </div>
              
              <!-- Dummy Chart Graphic (Enterprise Style) -->
              <div class="flex-1 min-h-[250px] relative mt-4">
                <div class="absolute inset-0 flex flex-col justify-between z-0 pb-6">
                    <div v-for="i in 5" :key="i" class="w-full border-t border-zinc-100 border-dashed h-0"></div>
                </div>
                <div class="h-full w-full flex items-end justify-between px-2 gap-4 relative z-10 pb-6">
                  <div v-for="i in 7" :key="i" class="w-full h-full flex flex-col justify-end group">
                      <div class="w-full bg-indigo-50 rounded-t-lg relative hover:bg-indigo-100 transition-all cursor-pointer flex flex-col justify-end" :style="{ height: `${Math.floor(Math.random() * 60) + 40}%` }">
                        <div class="w-full bg-gradient-to-t from-indigo-600 to-indigo-400 rounded-t-lg opacity-90 group-hover:opacity-100 transition-opacity" :style="{ height: `${Math.floor(Math.random() * 70) + 20}%` }"></div>
                        <div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-zinc-900 text-white text-xs font-bold py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">
                            Rp {{ Math.floor(Math.random() * 5) + 1 }} Jt
                        </div>
                      </div>
                  </div>
                </div>
                <div class="absolute bottom-0 left-0 right-0 flex justify-between px-2 text-xs font-semibold text-zinc-400">
                  <span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span><span>Min</span>
                </div>
              </div>
            </div>

            <!-- RECENT ACTIVITY -->
            <div class="lg:col-span-3 bg-white border border-zinc-200 rounded-2xl p-6 shadow-sm flex flex-col">
              <div class="flex justify-between items-center mb-6">
                <div>
                  <h3 class="font-bold text-lg text-zinc-900 tracking-tight">Aktivitas Terkini</h3>
                  <p class="text-sm text-zinc-500 font-medium">Log operasional hari ini</p>
                </div>
                <Link :href="route('services.index')" class="text-sm text-indigo-600 hover:text-indigo-700 font-semibold hover:underline">Lihat Semua</Link>
              </div>

              <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar">
                <div class="space-y-5">
                  <div v-for="(service, idx) in (recentServices || []).slice(0,5)" :key="idx" class="flex items-center group cursor-pointer">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-100 to-purple-100 border border-indigo-50 flex items-center justify-center mr-4 flex-shrink-0 group-hover:scale-105 transition-transform">
                      <span class="text-sm font-bold text-indigo-700">{{ service.customer?.name?.charAt(0) || 'U' }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-bold text-zinc-900 truncate group-hover:text-indigo-600 transition-colors">{{ service.customer?.name || 'Pelanggan' }}</p>
                      <p class="text-xs text-zinc-500 font-medium truncate mt-0.5">{{ service.device_type }} &middot; {{ service.damage_type || 'Perbaikan Umum' }}</p>
                    </div>
                    <div class="ml-3 flex-shrink-0">
                      <Badge :status="service.status">{{ statusLabel(service.status) }}</Badge>
                    </div>
                  </div>
                  
                  <div v-if="!recentServices || recentServices.length === 0" class="flex flex-col items-center justify-center py-10 text-center">
                    <div class="w-12 h-12 bg-zinc-50 rounded-full flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="text-sm font-semibold text-zinc-700">Belum ada aktivitas servis</p>
                    <p class="text-xs text-zinc-500 mt-1">Servis baru yang masuk akan muncul di sini.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ROLE: CS -->
        <div v-else-if="userRole === 'cs'" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-2xl border border-zinc-200 shadow-sm hover:shadow-md transition-all relative overflow-hidden">
                <div class="absolute right-0 top-0 w-24 h-24 bg-orange-50 rounded-full blur-2xl -mr-8 -mt-8"></div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 rounded-xl bg-orange-50 border border-orange-100 flex items-center justify-center text-orange-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <span class="text-xs font-bold text-zinc-500 uppercase">Menunggu Antrean</span>
                    </div>
                    <div class="text-3xl font-black text-zinc-900">{{ stats?.menunggu_alokasi ?? 0 }}</div>
                    <p class="text-sm text-zinc-500 font-medium mt-1">Belum dikerjakan teknisi</p>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-2xl border border-zinc-200 shadow-sm hover:shadow-md transition-all relative overflow-hidden">
                <div class="absolute right-0 top-0 w-24 h-24 bg-emerald-50 rounded-full blur-2xl -mr-8 -mt-8"></div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-xs font-bold text-zinc-500 uppercase">Siap Diambil</span>
                    </div>
                    <div class="text-3xl font-black text-zinc-900">{{ stats?.siap_diambil ?? 0 }}</div>
                    <p class="text-sm text-emerald-600 font-semibold mt-1">Hubungi pelanggan segera</p>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-zinc-200 shadow-sm hover:shadow-md transition-all relative overflow-hidden">
                <div class="absolute right-0 top-0 w-24 h-24 bg-blue-50 rounded-full blur-2xl -mr-8 -mt-8"></div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </div>
                        <span class="text-xs font-bold text-zinc-500 uppercase">Indent Sparepart</span>
                    </div>
                    <div class="text-3xl font-black text-zinc-900">{{ stats?.indent ?? 0 }}</div>
                    <p class="text-sm text-zinc-500 font-medium mt-1">Menunggu komponen</p>
                </div>
            </div>
          </div>
        </div>

        <!-- ROLE: TEKNISI -->
        <div v-else-if="userRole === 'technician'" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-2xl border border-blue-200 shadow-sm relative overflow-hidden ring-1 ring-blue-50">
                <div class="absolute right-0 top-0 w-24 h-24 bg-blue-50 rounded-full blur-2xl -mr-8 -mt-8"></div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        </div>
                        <span class="text-xs font-bold text-blue-600 uppercase">Tugas Baru (Diterima)</span>
                    </div>
                    <div class="text-4xl font-black text-blue-600">{{ stats?.diterima ?? 0 }}</div>
                    <p class="text-sm text-zinc-500 font-medium mt-1">Siap dikerjakan</p>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-2xl border border-zinc-200 shadow-sm relative overflow-hidden">
                <div class="absolute right-0 top-0 w-24 h-24 bg-orange-50 rounded-full blur-2xl -mr-8 -mt-8"></div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 rounded-xl bg-orange-50 border border-orange-100 flex items-center justify-center text-orange-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <span class="text-xs font-bold text-zinc-500 uppercase">Sedang Dikerjakan</span>
                    </div>
                    <div class="text-3xl font-black text-zinc-900">{{ stats?.dikerjakan ?? 0 }}</div>
                    <p class="text-sm text-zinc-500 font-medium mt-1">Dalam proses (Progress)</p>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-zinc-200 shadow-sm relative overflow-hidden">
                <div class="absolute right-0 top-0 w-24 h-24 bg-emerald-50 rounded-full blur-2xl -mr-8 -mt-8"></div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <span class="text-xs font-bold text-zinc-500 uppercase">Selesai Hari Ini</span>
                    </div>
                    <div class="text-3xl font-black text-zinc-900">{{ stats?.selesai ?? 0 }}</div>
                    <p class="text-sm text-emerald-600 font-semibold mt-1">Kerja bagus, tetap semangat!</p>
                </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import KButton from '@/Components/KButton.vue';

import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
import Badge from '@/Components/Badge.vue';
import SetupProgressCard from '@/Components/SetupProgressCard.vue';
import { useFormatter } from '@/Composables/useFormatter.js';
import { statusLabel } from '@/Utils/statusMaps.js';

const props = defineProps({
  stats: Object,
  recentServices: Array,
  isNotTechnician: Boolean,
  setupSummary: { type: Object, default: null },
});

const page = usePage();
const { formatNumber, currentDate, greeting } = useFormatter();

const userRole = computed(() => page.props.auth.user?.role || 'admin');

const roleTitles = {
  owner: 'Owner',
  manager: 'Manager',
  admin: 'Administrator',
  cs: 'Customer Service',
  cashier: 'Kasir',
  technician: 'Teknisi',
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #e4e4e7;
  border-radius: 10px;
}
</style>
