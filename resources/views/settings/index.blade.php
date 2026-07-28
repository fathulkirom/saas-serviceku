@extends('layouts.app')

@section('title', 'Settings')

@section('breadcrumb')
    <a href="#" class="hover:text-slate-600 transition-colors">Settings</a>
    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-slate-500">Tenant Settings</span>
@endsection

@section('content')
<div x-data="{ tab: 'general' }" class="flex gap-3">

    <!-- ===== LEFT: VERTICAL TABS ===== -->
    <div class="w-44 flex-shrink-0 space-y-0.5">
        <button @click="tab = 'general'" class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-all text-left"
            :class="tab === 'general' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50 border border-transparent'">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            General Info
        </button>
        <button @click="tab = 'receipt'" class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-all text-left"
            :class="tab === 'receipt' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50 border border-transparent'">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Receipt Settings
        </button>
        <button @click="tab = 'staff'" class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-all text-left"
            :class="tab === 'staff' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50 border border-transparent'">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
            Staff Management
            <span class="ml-auto inline-flex items-center justify-center w-5 h-5 rounded-full bg-indigo-100 text-indigo-600 text-[10px] font-bold">6</span>
        </button>
    </div>

    <!-- ===== RIGHT: TAB CONTENT ===== -->
    <div class="flex-1 min-w-0 space-y-3">

        <!-- ============================================================ -->
        <!-- TAB 1: GENERAL INFO                                         -->
        <!-- ============================================================ -->
        <div x-show="tab === 'general'" x-transition.duration.200ms>
            <div class="bg-white rounded-lg border border-slate-100 shadow-soft p-4">
                <h3 class="text-sm font-bold text-slate-900 mb-4">General Information</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Business Name</label>
                        <input type="text" value="Toko Servis ABC" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-sm text-slate-800 bg-white focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Business Email</label>
                        <input type="email" value="info@tokoservisabc.com" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-sm text-slate-800 bg-white focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 transition-all">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-slate-600 mb-1">Address</label>
                        <textarea rows="2" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-sm text-slate-800 bg-white focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 transition-all">Jl. Merdeka No. 123, Jakarta Pusat, DKI Jakarta 10110</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Phone</label>
                        <input type="text" value="021-1234-5678" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-sm text-slate-800 bg-white focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">WhatsApp Number</label>
                        <input type="text" value="6281212345678" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-sm text-slate-800 bg-white focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 transition-all">
                    </div>
                </div>
            </div>

            <!-- Business Logo Upload -->
            <div class="bg-white rounded-lg border border-slate-100 shadow-soft p-4">
                <h3 class="text-sm font-bold text-slate-900 mb-4">Business Logo</h3>
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-indigo-100 to-indigo-50 flex items-center justify-center text-indigo-600 font-bold text-xl border border-indigo-200/50 shadow-soft">TA</div>
                    <div>
                        <button class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-medium text-slate-600 hover:border-indigo-300 hover:text-indigo-600 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Upload Logo
                        </button>
                        <p class="text-[10px] text-slate-400 mt-1">Recommended: 200x200px, PNG or SVG, max 1MB</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- TAB 2: RECEIPT SETTINGS                                     -->
        <!-- ============================================================ -->
        <div x-show="tab === 'receipt'" x-transition.duration.200ms>
            <div class="bg-white rounded-lg border border-slate-100 shadow-soft p-4">
                <h3 class="text-sm font-bold text-slate-900 mb-4">Receipt / Invoice Preferences</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Receipt Prefix</label>
                        <input type="text" value="INV-" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-sm text-slate-800 bg-white focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 transition-all">
                        <p class="text-[10px] text-slate-400 mt-0.5">Generated: <span class="font-mono text-indigo-600">INV-2026-0042</span></p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Default Tax (PPN %)</label>
                        <input type="number" value="11" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-sm text-slate-800 bg-white focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 transition-all">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-slate-600 mb-1">Receipt Footer Text</label>
                        <textarea rows="2" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-sm text-slate-800 bg-white focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 transition-all" placeholder="Terima kasih telah menggunakan layanan kami. Barang yang sudah dibeli tidak dapat dikembalikan."></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Currency</label>
                        <select class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-sm text-slate-700 bg-white focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 transition-all">
                            <option selected>IDR (Rp)</option>
                            <option>USD ($)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Paper Size</label>
                        <select class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-sm text-slate-700 bg-white focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 transition-all">
                            <option selected>80mm Thermal</option>
                            <option>58mm Thermal</option>
                            <option>A4 (PDF)</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2 flex items-center gap-6 mt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" checked class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 transition-all">
                            <span class="text-xs text-slate-600">Show QRIS / Payment QR on receipt</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" checked class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 transition-all">
                            <span class="text-xs text-slate-600">Include warranty terms</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- TAB 3: STAFF MANAGEMENT                                      -->
        <!-- ============================================================ -->
        <div x-show="tab === 'staff'" x-transition.duration.200ms>
            <!-- Inline Add Staff Form -->
            <div class="bg-white rounded-lg border border-slate-100 shadow-soft p-3">
                <h3 class="text-xs font-bold text-slate-900 mb-3 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    Add New Staff
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-2">
                    <input type="text" placeholder="Full name" class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-sm text-slate-700 bg-white placeholder:text-slate-400 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 transition-all">
                    <input type="email" placeholder="Email address" class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-sm text-slate-700 bg-white placeholder:text-slate-400 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 transition-all">
                    <select class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-sm text-slate-700 bg-white focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 transition-all">
                        <option value="">Select role...</option>
                        <option>Admin</option>
                        <option>Frontdesk</option>
                        <option>Hardware Technician</option>
                        <option>Inventory Manager</option>
                    </select>
                    <button class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition-all shadow-soft">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Add Staff
                    </button>
                </div>
            </div>

            <!-- Staff Table -->
            <div class="bg-white rounded-lg border border-slate-100 shadow-soft overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100">
                                <th class="sticky top-0 z-10 bg-slate-50 px-3 py-1.5 text-left text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Name</th>
                                <th class="sticky top-0 z-10 bg-slate-50 px-3 py-1.5 text-left text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Role</th>
                                <th class="sticky top-0 z-10 bg-slate-50 px-3 py-1.5 text-left text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Email</th>
                                <th class="sticky top-0 z-10 bg-slate-50 px-3 py-1.5 text-center text-[10px] font-semibold text-slate-500 uppercase tracking-wider w-20">Status</th>
                                <th class="sticky top-0 z-10 bg-slate-50 px-3 py-1.5 text-center text-[10px] font-semibold text-slate-500 uppercase tracking-wider w-20">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr class="hover:bg-indigo-50/20 transition-colors even:bg-slate-50/50">
                                <td class="px-3 py-1.5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-100 to-indigo-50 flex items-center justify-center text-[9px] font-bold text-indigo-600 border border-indigo-200/50 flex-shrink-0">BS</div>
                                        <span class="text-sm font-semibold text-slate-800">Budi Santoso</span>
                                    </div>
                                </td>
                                <td class="px-3 py-1.5">
                                    <select class="w-full px-2 py-0.5 rounded-md border border-slate-200 bg-white text-xs font-medium text-slate-700 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 cursor-pointer transition-all">
                                        <option>Admin</option>
                                        <option>Frontdesk</option>
                                        <option selected>Hardware Technician</option>
                                        <option>Inventory Manager</option>
                                    </select>
                                </td>
                                <td class="px-3 py-1.5">
                                    <span class="text-xs text-slate-600">budi@tokoservis.com</span>
                                </td>
                                <td class="px-3 py-1.5 text-center">
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-emerald-50 text-emerald-600 text-[10px] font-semibold border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Active
                                    </span>
                                </td>
                                <td class="px-3 py-1.5 text-center">
                                    <button class="p-1 rounded-md text-slate-400 hover:text-rose-600 hover:bg-white transition-all" title="Remove">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </td>
                            </tr>
                            <tr class="hover:bg-indigo-50/20 transition-colors even:bg-slate-50/50">
                                <td class="px-3 py-1.5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-sky-100 to-sky-50 flex items-center justify-center text-[9px] font-bold text-sky-600 border border-sky-200/50 flex-shrink-0">SR</div>
                                        <span class="text-sm font-semibold text-slate-800">Siti Rahayu</span>
                                    </div>
                                </td>
                                <td class="px-3 py-1.5">
                                    <select class="w-full px-2 py-0.5 rounded-md border border-slate-200 bg-white text-xs font-medium text-slate-700 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 cursor-pointer transition-all">
                                        <option>Admin</option>
                                        <option selected>Frontdesk</option>
                                        <option>Hardware Technician</option>
                                        <option>Inventory Manager</option>
                                    </select>
                                </td>
                                <td class="px-3 py-1.5">
                                    <span class="text-xs text-slate-600">siti@tokoservis.com</span>
                                </td>
                                <td class="px-3 py-1.5 text-center">
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-emerald-50 text-emerald-600 text-[10px] font-semibold border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Active
                                    </span>
                                </td>
                                <td class="px-3 py-1.5 text-center">
                                    <button class="p-1 rounded-md text-slate-400 hover:text-rose-600 hover:bg-white transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                </td>
                            </tr>
                            <tr class="hover:bg-indigo-50/20 transition-colors even:bg-slate-50/50">
                                <td class="px-3 py-1.5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-cyan-100 to-sky-50 flex items-center justify-center text-[9px] font-bold text-cyan-600 border border-cyan-200/50 flex-shrink-0">AT</div>
                                        <span class="text-sm font-semibold text-slate-800">Ahmad Teknisi</span>
                                    </div>
                                </td>
                                <td class="px-3 py-1.5">
                                    <select class="w-full px-2 py-0.5 rounded-md border border-slate-200 bg-white text-xs font-medium text-slate-700 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 cursor-pointer transition-all">
                                        <option>Admin</option>
                                        <option>Frontdesk</option>
                                        <option selected>Hardware Technician</option>
                                        <option>Inventory Manager</option>
                                    </select>
                                </td>
                                <td class="px-3 py-1.5">
                                    <span class="text-xs text-slate-600">ahmad@tokoservis.com</span>
                                </td>
                                <td class="px-3 py-1.5 text-center">
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-amber-50 text-amber-600 text-[10px] font-semibold border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                        Vacation
                                    </span>
                                </td>
                                <td class="px-3 py-1.5 text-center">
                                    <button class="p-1 rounded-md text-slate-400 hover:text-rose-600 hover:bg-white transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                </td>
                            </tr>
                            <tr class="hover:bg-indigo-50/20 transition-colors even:bg-slate-50/50">
                                <td class="px-3 py-1.5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-violet-100 to-purple-50 flex items-center justify-center text-[9px] font-bold text-violet-600 border border-violet-200/50 flex-shrink-0">FN</div>
                                        <span class="text-sm font-semibold text-slate-800">Fajar Nugroho</span>
                                    </div>
                                </td>
                                <td class="px-3 py-1.5">
                                    <select class="w-full px-2 py-0.5 rounded-md border border-slate-200 bg-white text-xs font-medium text-slate-700 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 cursor-pointer transition-all">
                                        <option>Admin</option>
                                        <option>Frontdesk</option>
                                        <option>Hardware Technician</option>
                                        <option selected>Inventory Manager</option>
                                    </select>
                                </td>
                                <td class="px-3 py-1.5">
                                    <span class="text-xs text-slate-600">fajar@tokoservis.com</span>
                                </td>
                                <td class="px-3 py-1.5 text-center">
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-emerald-50 text-emerald-600 text-[10px] font-semibold border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Active
                                    </span>
                                </td>
                                <td class="px-3 py-1.5 text-center">
                                    <button class="p-1 rounded-md text-slate-400 hover:text-rose-600 hover:bg-white transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                </td>
                            </tr>
                            <tr class="hover:bg-indigo-50/20 transition-colors even:bg-slate-50/50">
                                <td class="px-3 py-1.5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-rose-100 to-pink-50 flex items-center justify-center text-[9px] font-bold text-rose-600 border border-rose-200/50 flex-shrink-0">DW</div>
                                        <span class="text-sm font-semibold text-slate-800">Dewi Lestari</span>
                                    </div>
                                </td>
                                <td class="px-3 py-1.5">
                                    <select class="w-full px-2 py-0.5 rounded-md border border-slate-200 bg-white text-xs font-medium text-slate-700 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 cursor-pointer transition-all">
                                        <option>Admin</option>
                                        <option selected>Frontdesk</option>
                                        <option>Hardware Technician</option>
                                        <option>Inventory Manager</option>
                                    </select>
                                </td>
                                <td class="px-3 py-1.5">
                                    <span class="text-xs text-slate-600">dewi@tokoservis.com</span>
                                </td>
                                <td class="px-3 py-1.5 text-center">
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-rose-50 text-rose-600 text-[10px] font-semibold border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Inactive
                                    </span>
                                </td>
                                <td class="px-3 py-1.5 text-center">
                                    <button class="p-1 rounded-md text-slate-400 hover:text-rose-600 hover:bg-white transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Footer -->
                <div class="px-3 py-1.5 border-t border-slate-100 flex items-center justify-between">
                    <p class="text-[10px] text-slate-400">Showing 5 of 6 staff members</p>
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-600 text-[10px] font-semibold border border-indigo-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                        4 Active · 1 Vacation · 1 Inactive
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== FLOATING SAVE BUTTON ===== -->
    <div class="fixed bottom-6 right-6 z-40">
        <button class="inline-flex items-center gap-2.5 px-5 py-3 rounded-2xl bg-gradient-to-r from-indigo-600 to-indigo-500 text-white text-sm font-bold hover:from-indigo-700 hover:to-indigo-600 hover:shadow-premium hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 shadow-soft group">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
            Save Changes
            <span class="text-[10px] opacity-60 hidden sm:inline">Ctrl+S</span>
        </button>
    </div>
</div>
@endsection