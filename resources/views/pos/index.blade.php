@extends('layouts.app')

@section('title', 'POS & Invoice')

@section('breadcrumb')
    <a href="#" class="hover:text-slate-600 transition-colors">POS</a>
    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-slate-500">New Invoice</span>
@endsection

@section('content')
<div class="flex gap-2 h-[calc(100vh-8.5rem)]">
    {{-- make sure h-[calc(100vh-8.5rem)] works: header ~ 3rem, breadcrumb ~ 1.5rem, padding ~ 1rem, gap ~ 0.5rem, toolbar ~ 2.5rem --}}

    <!-- ============================================================ -->
    <!-- LEFT PANEL (60%): Inventory & Services                       -->
    <!-- ============================================================ -->
    <div class="w-[60%] flex flex-col gap-2">

        <!-- Search / Barcode Input (sticky) -->
        <div class="bg-white rounded-lg border border-slate-100 shadow-soft flex-shrink-0">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" autofocus placeholder="Search by name, SKU, or scan barcode..." class="w-full pl-10 pr-3 py-2.5 rounded-lg border-0 bg-transparent text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-0">
                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[9px] font-medium text-slate-300 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200">SCAN</span>
            </div>
        </div>

        <!-- Category Tabs -->
        <div class="flex items-center gap-1.5 flex-shrink-0 overflow-x-auto">
            <button class="px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-[11px] font-semibold whitespace-nowrap shadow-soft">All Items</button>
            <button class="px-3 py-1.5 rounded-lg bg-white text-slate-500 text-[11px] font-semibold border border-slate-200 hover:border-indigo-300 hover:text-indigo-600 transition-all whitespace-nowrap">LCD & Display</button>
            <button class="px-3 py-1.5 rounded-lg bg-white text-slate-500 text-[11px] font-semibold border border-slate-200 hover:border-indigo-300 hover:text-indigo-600 transition-all whitespace-nowrap">Battery</button>
            <button class="px-3 py-1.5 rounded-lg bg-white text-slate-500 text-[11px] font-semibold border border-slate-200 hover:border-indigo-300 hover:text-indigo-600 transition-all whitespace-nowrap">Charging</button>
            <button class="px-3 py-1.5 rounded-lg bg-white text-slate-500 text-[11px] font-semibold border border-slate-200 hover:border-indigo-300 hover:text-indigo-600 transition-all whitespace-nowrap">Service Labor</button>
            <button class="px-3 py-1.5 rounded-lg bg-white text-slate-500 text-[11px] font-semibold border border-slate-200 hover:border-indigo-300 hover:text-indigo-600 transition-all whitespace-nowrap">Accessories</button>
        </div>

        <!-- Scrollable Parts Table -->
        <div class="bg-white rounded-lg border border-slate-100 shadow-soft flex-1 overflow-hidden">
            <div class="overflow-y-auto h-full divide-y divide-slate-50">
                <!-- Sparepart 1 -->
                <div class="flex items-center gap-3 px-3 py-2 hover:bg-indigo-50/30 transition-colors group">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-sky-100 to-sky-50 flex items-center justify-center text-[10px] font-bold text-sky-600 border border-sky-200/50 flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800 truncate">LCD iPhone 16 Pro Max — OEM Grade</p>
                        <div class="flex items-center gap-2 text-[11px] text-slate-400">
                            <span>SKU: LCD-IP16PM-OEM</span>
                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                            <span class="text-emerald-600 font-medium">Stock: 5</span>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-sm font-bold text-slate-900">Rp 1,250,000</p>
                        <p class="text-[10px] text-slate-400 line-through">Rp 1,500,000</p>
                    </div>
                    <button class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-indigo-600 text-white text-[11px] font-semibold hover:bg-indigo-700 hover:shadow-soft transition-all opacity-0 group-hover:opacity-100 flex-shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Add
                    </button>
                </div>
                <!-- Sparepart 2 -->
                <div class="flex items-center gap-3 px-3 py-2 hover:bg-indigo-50/30 transition-colors group">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-100 to-purple-50 flex items-center justify-center text-[10px] font-bold text-violet-600 border border-violet-200/50 flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800 truncate">Baterai Samsung Galaxy S25 Ultra — Original</p>
                        <div class="flex items-center gap-2 text-[11px] text-slate-400">
                            <span>SKU: BAT-S25U-ORI</span>
                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                            <span class="text-emerald-600 font-medium">Stock: 8</span>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-sm font-bold text-slate-900">Rp 425,000</p>
                    </div>
                    <button class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-indigo-600 text-white text-[11px] font-semibold hover:bg-indigo-700 hover:shadow-soft transition-all opacity-0 group-hover:opacity-100 flex-shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Add
                    </button>
                </div>
                <!-- Sparepart 3 -->
                <div class="flex items-center gap-3 px-3 py-2 hover:bg-indigo-50/30 transition-colors group">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-100 to-yellow-50 flex items-center justify-center text-[10px] font-bold text-amber-600 border border-amber-200/50 flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800 truncate">Charging Port USB-C — MacBook Air M4</p>
                        <div class="flex items-center gap-2 text-[11px] text-slate-400">
                            <span>SKU: CHG-MBA4-USBC</span>
                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                            <span class="text-rose-600 font-medium">Low Stock: 2</span>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-sm font-bold text-slate-900">Rp 185,000</p>
                    </div>
                    <button class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-indigo-600 text-white text-[11px] font-semibold hover:bg-indigo-700 hover:shadow-soft transition-all opacity-0 group-hover:opacity-100 flex-shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Add
                    </button>
                </div>
                <!-- Sparepart 4 -->
                <div class="flex items-center gap-3 px-3 py-2 hover:bg-indigo-50/30 transition-colors group">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-100 to-teal-50 flex items-center justify-center text-[10px] font-bold text-emerald-600 border border-emerald-200/50 flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800 truncate">Tempered Glass — iPhone 16 Series</p>
                        <div class="flex items-center gap-2 text-[11px] text-slate-400">
                            <span>SKU: TGL-IP16-BLK</span>
                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                            <span class="text-emerald-600 font-medium">Stock: 25</span>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-sm font-bold text-slate-900">Rp 35,000</p>
                    </div>
                    <button class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-indigo-600 text-white text-[11px] font-semibold hover:bg-indigo-700 hover:shadow-soft transition-all opacity-0 group-hover:opacity-100 flex-shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Add
                    </button>
                </div>
                <!-- Sparepart 5 -->
                <div class="flex items-center gap-3 px-3 py-2 hover:bg-indigo-50/30 transition-colors group">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-rose-100 to-pink-50 flex items-center justify-center text-[10px] font-bold text-rose-600 border border-rose-200/50 flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800 truncate">Flex Cable Charging — iPhone 15 Pro</p>
                        <div class="flex items-center gap-2 text-[11px] text-slate-400">
                            <span>SKU: FLC-IP15P</span>
                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                            <span class="text-emerald-600 font-medium">Stock: 12</span>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-sm font-bold text-slate-900">Rp 65,000</p>
                    </div>
                    <button class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-indigo-600 text-white text-[11px] font-semibold hover:bg-indigo-700 hover:shadow-soft transition-all opacity-0 group-hover:opacity-100 flex-shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Add
                    </button>
                </div>
                <!-- Divider: Service Fees -->
                <div class="px-3 py-1.5 bg-slate-50/80">
                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Service Fees</span>
                </div>
                <!-- Service 1 -->
                <div class="flex items-center gap-3 px-3 py-2 hover:bg-indigo-50/30 transition-colors group">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-100 to-indigo-50 flex items-center justify-center text-[10px] font-bold text-indigo-600 border border-indigo-200/50 flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800 truncate">Component-Level Diagnostic</p>
                        <p class="text-[11px] text-slate-400 truncate">Full motherboard inspection with thermal imaging</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-sm font-bold text-slate-900">Rp 150,000</p>
                    </div>
                    <button class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-indigo-600 text-white text-[11px] font-semibold hover:bg-indigo-700 hover:shadow-soft transition-all opacity-0 group-hover:opacity-100 flex-shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Add
                    </button>
                </div>
                <!-- Service 2 -->
                <div class="flex items-center gap-3 px-3 py-2 hover:bg-indigo-50/30 transition-colors group">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-cyan-100 to-sky-50 flex items-center justify-center text-[10px] font-bold text-cyan-600 border border-cyan-200/50 flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800 truncate">NAND Flash BGA Rework</p>
                        <p class="text-[11px] text-slate-400 truncate">NAND chip removal, reball, reflow & programming</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-sm font-bold text-slate-900">Rp 350,000</p>
                    </div>
                    <button class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-indigo-600 text-white text-[11px] font-semibold hover:bg-indigo-700 hover:shadow-soft transition-all opacity-0 group-hover:opacity-100 flex-shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Add
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- RIGHT PANEL (40%): Active Cart / Invoice Draft               -->
    <!-- ============================================================ -->
    <div class="w-[40%] bg-white rounded-lg border border-slate-100 shadow-soft flex flex-col overflow-hidden">

        <!-- Customer Selection (sticky top) -->
        <div class="flex-shrink-0 px-3 py-2.5 border-b border-slate-100">
            <label class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5 block">Customer</label>
            <div class="relative">
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <select class="w-full pl-8 pr-3 py-1.5 rounded-lg border border-slate-200 bg-slate-50 text-sm text-slate-700 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 transition-all appearance-none cursor-pointer">
                    <option>Rina Wijaya — iPhone 16 Pro Max</option>
                    <option>Doni Prasetyo — MacBook Air M4</option>
                    <option>— Walk-in Customer —</option>
                </select>
                <svg class="absolute right-2.5 top-1/2 -translate-y-1/2 w-3 h-3 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>
        </div>

        <!-- Cart Items Header -->
        <div class="flex items-center justify-between px-3 py-1.5 border-b border-slate-100 flex-shrink-0 bg-slate-50/50">
            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Item</span>
            <div class="flex items-center gap-6">
                <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Qty</span>
                <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider w-16 text-right">Price</span>
                <span class="w-5"></span>
            </div>
        </div>

        <!-- Scrollable Cart Items -->
        <div class="flex-1 overflow-y-auto divide-y divide-slate-50">
            <!-- Cart Item 1 -->
            <div class="flex items-center gap-2 px-3 py-2 hover:bg-indigo-50/20 transition-colors group">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-800 truncate">LCD iPhone 16 Pro Max</p>
                    <p class="text-[10px] text-slate-400">LCD-IP16PM-OEM</p>
                </div>
                <div class="flex items-center gap-1 border border-slate-200 rounded-md">
                    <button class="px-1.5 py-0.5 text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition-all text-xs font-bold">−</button>
                    <span class="px-1.5 py-0.5 text-xs font-semibold text-slate-700 min-w-[20px] text-center">1</span>
                    <button class="px-1.5 py-0.5 text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition-all text-xs font-bold">+</button>
                </div>
                <span class="text-sm font-bold text-slate-900 w-16 text-right">Rp 1,250,000</span>
                <button class="p-1 rounded-md text-slate-300 hover:text-rose-500 hover:bg-white transition-all opacity-0 group-hover:opacity-100">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <!-- Cart Item 2 -->
            <div class="flex items-center gap-2 px-3 py-2 hover:bg-indigo-50/20 transition-colors group">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-800 truncate">Component-Level Diagnostic</p>
                    <p class="text-[10px] text-slate-400">Service Fee</p>
                </div>
                <div class="flex items-center gap-1 border border-slate-200 rounded-md">
                    <button class="px-1.5 py-0.5 text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition-all text-xs font-bold">−</button>
                    <span class="px-1.5 py-0.5 text-xs font-semibold text-slate-700 min-w-[20px] text-center">1</span>
                    <button class="px-1.5 py-0.5 text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition-all text-xs font-bold">+</button>
                </div>
                <span class="text-sm font-bold text-slate-900 w-16 text-right">Rp 150,000</span>
                <button class="p-1 rounded-md text-slate-300 hover:text-rose-500 hover:bg-white transition-all opacity-0 group-hover:opacity-100">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <!-- Cart Item 3 -->
            <div class="flex items-center gap-2 px-3 py-2 hover:bg-indigo-50/20 transition-colors group">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-800 truncate">Tempered Glass — iPhone 16</p>
                    <p class="text-[10px] text-slate-400">TGL-IP16-BLK</p>
                </div>
                <div class="flex items-center gap-1 border border-slate-200 rounded-md">
                    <button class="px-1.5 py-0.5 text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition-all text-xs font-bold">−</button>
                    <span class="px-1.5 py-0.5 text-xs font-semibold text-slate-700 min-w-[20px] text-center">2</span>
                    <button class="px-1.5 py-0.5 text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition-all text-xs font-bold">+</button>
                </div>
                <span class="text-sm font-bold text-slate-900 w-16 text-right">Rp 70,000</span>
                <button class="p-1 rounded-md text-slate-300 hover:text-rose-500 hover:bg-white transition-all opacity-0 group-hover:opacity-100">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <!-- Add Item placeholder -->
            <div class="flex items-center justify-center px-3 py-4">
                <button class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border-2 border-dashed border-slate-200 text-slate-400 text-xs font-medium hover:border-indigo-300 hover:text-indigo-500 hover:bg-indigo-50/30 transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Add Item or Service Fee
                </button>
            </div>
        </div>

        <!-- ===== STICKY SUMMARY FOOTER ===== -->
        <div class="flex-shrink-0 border-t border-slate-100 bg-white">
            <!-- Totals -->
            <div class="px-3 py-2.5 space-y-1.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-500">Subtotal</span>
                    <span class="text-sm font-semibold text-slate-700">Rp 1,470,000</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-500">Tax (PPN 11%)</span>
                    <span class="text-sm font-semibold text-slate-700">Rp 161,700</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-500">Discount</span>
                    <span class="text-sm font-semibold text-emerald-600">− Rp 50,000</span>
                </div>
                <div class="flex items-center justify-between pt-2 border-t-2 border-slate-200">
                    <span class="text-sm font-bold text-slate-900">Total</span>
                    <span class="text-lg font-bold text-indigo-600">Rp 1,581,700</span>
                </div>
            </div>

            <!-- Payment Method -->
            <div class="px-3 pb-2">
                <div class="grid grid-cols-3 gap-1.5 mb-2.5">
                    <button class="flex flex-col items-center gap-0.5 px-2 py-1.5 rounded-lg bg-indigo-50 border-2 border-indigo-300 text-indigo-700 transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span class="text-[9px] font-semibold">Cash</span>
                    </button>
                    <button class="flex flex-col items-center gap-0.5 px-2 py-1.5 rounded-lg border-2 border-slate-200 text-slate-500 hover:border-indigo-200 hover:text-indigo-500 transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        <span class="text-[9px] font-semibold">Transfer</span>
                    </button>
                    <button class="flex flex-col items-center gap-0.5 px-2 py-1.5 rounded-lg border-2 border-slate-200 text-slate-500 hover:border-indigo-200 hover:text-indigo-500 transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-[9px] font-semibold">QRIS</span>
                    </button>
                </div>

                <!-- Pay Button -->
                <button class="w-full flex items-center justify-center gap-2.5 px-5 py-3 rounded-xl bg-gradient-to-r from-indigo-600 via-indigo-500 to-indigo-600 text-white text-sm font-bold hover:from-indigo-700 hover:via-indigo-600 hover:to-indigo-700 hover:shadow-premium hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 shadow-soft group">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Pay & Print Invoice
                    <svg class="w-4 h-4 opacity-70 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </button>
                <p class="text-center text-[9px] text-slate-400 mt-1.5">Invoice akan digenerate sebagai PDF</p>
            </div>
        </div>
    </div>
</div>
@endsection