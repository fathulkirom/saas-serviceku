<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>POS & Invoice — ServiceKU</title>
    @vite('resources/css/app.css')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
        .font-mono { font-family: 'Inter', monospace; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-indigo-50/20 to-slate-50 text-slate-800 antialiased">

    <!-- ===== TOP NAVBAR ===== -->
    <nav class="sticky top-0 z-40 bg-white/70 backdrop-blur-xl border-b border-slate-200/50 shadow-soft">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-600 to-indigo-500 flex items-center justify-center text-white font-bold text-sm shadow-premium">SK</div>
                        <span class="text-lg font-bold text-slate-900 hidden sm:block">ServiceKU</span>
                        <span class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-600 text-[10px] font-semibold border border-indigo-200">POS</span>
                    </div>
                    <div class="hidden md:flex items-center gap-1.5">
                        <div class="w-px h-6 bg-slate-200 mx-2"></div>
                        <a href="#" class="px-3 py-1.5 rounded-lg text-xs font-medium text-slate-400 hover:text-slate-700 hover:bg-slate-50 transition-all">Dashboard</a>
                        <a href="#" class="px-3 py-1.5 rounded-lg text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200/50">POS / Invoice</a>
                        <a href="#" class="px-3 py-1.5 rounded-lg text-xs font-medium text-slate-400 hover:text-slate-700 hover:bg-slate-50 transition-all">Inventory</a>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button class="relative p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-rose-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center shadow-soft">2</span>
                    </button>
                    <div class="flex items-center gap-2 pl-2 pr-1 py-1 rounded-xl hover:bg-slate-50 transition-all border border-transparent hover:border-slate-100">
                        <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center text-white text-sm font-bold shadow-soft">BS</div>
                        <span class="text-sm font-medium text-slate-700 hidden sm:block">Budi Santoso</span>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- ===== PAGE CONTENT ===== -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">

        <!-- ===== PAGE HEADER ===== -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Point of Sale</h1>
                <p class="text-sm text-slate-400 mt-0.5">Buat invoice penjualan sparepart & jasa servis dengan cepat</p>
            </div>
            <div class="flex items-center gap-3">
                <button class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50 hover:border-slate-300 transition-all shadow-soft">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Cancel
                </button>
            </div>
        </div>

        <!-- ===== TWO-COLUMN LAYOUT ===== -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- ============================================================ -->
            <!-- LEFT COLUMN: Product & Service Catalog (8/12)                -->
            <!-- ============================================================ -->
            <div class="lg:col-span-8 space-y-5">

                <!-- Search & Filter Bar -->
                <div class="bg-white rounded-2xl border border-slate-100/50 p-4 shadow-soft">
                    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center">
                        <div class="relative flex-1 w-full">
                            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" placeholder="Cari sparepart atau jasa servis..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-white/80 text-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                        </div>
                        <div class="flex gap-2 w-full sm:w-auto">
                            <select class="px-3 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-600 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                                <option>All Categories</option>
                                <option>Spareparts</option>
                                <option>Service Fees</option>
                            </select>
                        </div>
                    </div>
                    <!-- Quick Category Tabs -->
                    <div class="flex flex-wrap gap-2 mt-4">
                        <button class="px-3.5 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-semibold shadow-soft">All Items</button>
                        <button class="px-3.5 py-1.5 rounded-lg bg-white text-slate-500 text-xs font-semibold border border-slate-200 hover:border-indigo-300 hover:text-indigo-600 transition-all">LCD & Display</button>
                        <button class="px-3.5 py-1.5 rounded-lg bg-white text-slate-500 text-xs font-semibold border border-slate-200 hover:border-indigo-300 hover:text-indigo-600 transition-all">Battery</button>
                        <button class="px-3.5 py-1.5 rounded-lg bg-white text-slate-500 text-xs font-semibold border border-slate-200 hover:border-indigo-300 hover:text-indigo-600 transition-all">Charging Port</button>
                        <button class="px-3.5 py-1.5 rounded-lg bg-white text-slate-500 text-xs font-semibold border border-slate-200 hover:border-indigo-300 hover:text-indigo-600 transition-all">Service Labor</button>
                        <button class="px-3.5 py-1.5 rounded-lg bg-white text-slate-500 text-xs font-semibold border border-slate-200 hover:border-indigo-300 hover:text-indigo-600 transition-all">Accessories</button>
                    </div>
                </div>

                <!-- ===== PARTS CATALOG ===== -->
                <div class="bg-white rounded-2xl border border-slate-100/50 shadow-soft overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-slate-900">Spareparts</h3>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-600 text-[10px] font-semibold border border-indigo-200">12 items</span>
                    </div>
                    <div class="divide-y divide-slate-50">
                        <!-- Item 1 -->
                        <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-indigo-50/40 transition-colors cursor-pointer group">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-sky-100 to-sky-50 flex items-center justify-center border border-sky-200/50 flex-shrink-0">
                                <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-800">LCD iPhone 16 Pro Max — OEM Grade</p>
                                <div class="flex items-center gap-2.5 mt-0.5">
                                    <span class="text-xs text-slate-400">SKU: LCD-IP16PM-OEM</span>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded-md bg-emerald-50 text-emerald-600 font-medium border border-emerald-200">In Stock: 5</span>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-sm font-bold text-slate-900">Rp 1,250,000</p>
                                <p class="text-[10px] text-slate-400 line-through">Rp 1,500,000</p>
                            </div>
                            <button class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-500 text-white text-xs font-semibold hover:from-indigo-700 hover:to-indigo-600 hover:shadow-premium hover:-translate-y-0.5 active:translate-y-0 transition-all shadow-soft opacity-0 group-hover:opacity-100 flex-shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                Add
                            </button>
                        </div>
                        <!-- Item 2 -->
                        <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-indigo-50/40 transition-colors cursor-pointer group">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-100 to-purple-50 flex items-center justify-center border border-violet-200/50 flex-shrink-0">
                                <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-800">Baterai Samsung Galaxy S25 Ultra</p>
                                <div class="flex items-center gap-2.5 mt-0.5">
                                    <span class="text-xs text-slate-400">SKU: BAT-S25U-ORI</span>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded-md bg-emerald-50 text-emerald-600 font-medium border border-emerald-200">In Stock: 8</span>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-sm font-bold text-slate-900">Rp 425,000</p>
                            </div>
                            <button class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-500 text-white text-xs font-semibold hover:from-indigo-700 hover:to-indigo-600 hover:shadow-premium hover:-translate-y-0.5 active:translate-y-0 transition-all shadow-soft opacity-0 group-hover:opacity-100 flex-shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                Add
                            </button>
                        </div>
                        <!-- Item 3 -->
                        <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-indigo-50/40 transition-colors cursor-pointer group">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-100 to-yellow-50 flex items-center justify-center border border-amber-200/50 flex-shrink-0">
                                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-800">Charging Port USB-C — MacBook Air M4</p>
                                <div class="flex items-center gap-2.5 mt-0.5">
                                    <span class="text-xs text-slate-400">SKU: CHG-MBA4-USBC</span>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded-md bg-rose-50 text-rose-600 font-medium border border-rose-200">Low Stock: 2</span>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-sm font-bold text-slate-900">Rp 185,000</p>
                            </div>
                            <button class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-500 text-white text-xs font-semibold hover:from-indigo-700 hover:to-indigo-600 hover:shadow-premium hover:-translate-y-0.5 active:translate-y-0 transition-all shadow-soft opacity-0 group-hover:opacity-100 flex-shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                Add
                            </button>
                        </div>
                        <!-- Item 4 -->
                        <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-indigo-50/40 transition-colors cursor-pointer group">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-100 to-teal-50 flex items-center justify-center border border-emerald-200/50 flex-shrink-0">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-800">Tempered Glass — iPhone 16 Series</p>
                                <div class="flex items-center gap-2.5 mt-0.5">
                                    <span class="text-xs text-slate-400">SKU: TGL-IP16-BLK</span>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded-md bg-emerald-50 text-emerald-600 font-medium border border-emerald-200">In Stock: 25</span>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-sm font-bold text-slate-900">Rp 35,000</p>
                            </div>
                            <button class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-500 text-white text-xs font-semibold hover:from-indigo-700 hover:to-indigo-600 hover:shadow-premium hover:-translate-y-0.5 active:translate-y-0 transition-all shadow-soft opacity-0 group-hover:opacity-100 flex-shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                Add
                            </button>
                        </div>
                        <!-- Item 5 -->
                        <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-indigo-50/40 transition-colors cursor-pointer group">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-rose-100 to-pink-50 flex items-center justify-center border border-rose-200/50 flex-shrink-0">
                                <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-800">Flex Cable Charging — iPhone 15 Pro</p>
                                <div class="flex items-center gap-2.5 mt-0.5">
                                    <span class="text-xs text-slate-400">SKU: FLC-IP15P</span>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded-md bg-emerald-50 text-emerald-600 font-medium border border-emerald-200">In Stock: 12</span>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-sm font-bold text-slate-900">Rp 65,000</p>
                            </div>
                            <button class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-500 text-white text-xs font-semibold hover:from-indigo-700 hover:to-indigo-600 hover:shadow-premium hover:-translate-y-0.5 active:translate-y-0 transition-all shadow-soft opacity-0 group-hover:opacity-100 flex-shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                Add
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ===== SERVICE FEES ===== -->
                <div class="bg-white rounded-2xl border border-slate-100/50 shadow-soft overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-slate-900">Service Fees</h3>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-600 text-[10px] font-semibold border border-indigo-200">8 items</span>
                    </div>
                    <div class="divide-y divide-slate-50">
                        <!-- Service 1 -->
                        <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-indigo-50/40 transition-colors cursor-pointer group">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-100 to-indigo-50 flex items-center justify-center border border-indigo-200/50 flex-shrink-0">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-800">Component-Level Diagnostic</p>
                                <p class="text-xs text-slate-400 mt-0.5">Full motherboard inspection with thermal imaging & multimeter</p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-sm font-bold text-slate-900">Rp 150,000</p>
                            </div>
                            <button class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-500 text-white text-xs font-semibold hover:from-indigo-700 hover:to-indigo-600 hover:shadow-premium hover:-translate-y-0.5 active:translate-y-0 transition-all shadow-soft opacity-0 group-hover:opacity-100 flex-shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                Add
                            </button>
                        </div>
                        <!-- Service 2 -->
                        <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-indigo-50/40 transition-colors cursor-pointer group">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-100 to-sky-50 flex items-center justify-center border border-cyan-200/50 flex-shrink-0">
                                <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-800">NAND Flash BGA Rework</p>
                                <p class="text-xs text-slate-400 mt-0.5">NAND chip removal, reball, reflow & programming</p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-sm font-bold text-slate-900">Rp 350,000</p>
                            </div>
                            <button class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-500 text-white text-xs font-semibold hover:from-indigo-700 hover:to-indigo-600 hover:shadow-premium hover:-translate-y-0.5 active:translate-y-0 transition-all shadow-soft opacity-0 group-hover:opacity-100 flex-shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                Add
                            </button>
                        </div>
                        <!-- Service 3 -->
                        <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-indigo-50/40 transition-colors cursor-pointer group">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-100 to-amber-50 flex items-center justify-center border border-orange-200/50 flex-shrink-0">
                                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-800">Microsoldering — Component Level</p>
                                <p class="text-xs text-slate-400 mt-0.5">IC replacement, jumper wire, pad repair under microscope</p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-sm font-bold text-slate-900">Rp 250,000</p>
                            </div>
                            <button class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-500 text-white text-xs font-semibold hover:from-indigo-700 hover:to-indigo-600 hover:shadow-premium hover:-translate-y-0.5 active:translate-y-0 transition-all shadow-soft opacity-0 group-hover:opacity-100 flex-shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                Add
                            </button>
                        </div>
                        <!-- Service 4 -->
                        <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-indigo-50/40 transition-colors cursor-pointer group">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-teal-100 to-cyan-50 flex items-center justify-center border border-teal-200/50 flex-shrink-0">
                                <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-800">Data Recovery — Flash Storage</p>
                                <p class="text-xs text-slate-400 mt-0.5">Chip-off, NAND reading, image reconstruction</p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-sm font-bold text-slate-900">Rp 500,000</p>
                            </div>
                            <button class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-500 text-white text-xs font-semibold hover:from-indigo-700 hover:to-indigo-600 hover:shadow-premium hover:-translate-y-0.5 active:translate-y-0 transition-all shadow-soft opacity-0 group-hover:opacity-100 flex-shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                Add
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- RIGHT COLUMN: Cart / Invoice Draft (4/12)                    -->
            <!-- ============================================================ -->
            <div class="lg:col-span-4">
                <div class="bg-white rounded-2xl border border-slate-100/50 shadow-soft overflow-hidden sticky top-24">

                    <!-- Invoice Header -->
                    <div class="px-5 py-5 border-b border-slate-100">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-600 to-indigo-500 flex items-center justify-center text-white font-bold text-sm shadow-premium">SK</div>
                                <div>
                                    <p class="text-sm font-bold text-slate-900">ServiceKU</p>
                                    <p class="text-[10px] text-slate-400">Invoice #INV-2026-0042</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 text-[10px] font-semibold border border-amber-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse-soft"></span>
                                Draft
                            </span>
                        </div>
                        <!-- Customer Info -->
                        <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-100">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Customer</p>
                                <button class="text-[10px] font-medium text-indigo-600 hover:text-indigo-700">Change</button>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-sky-100 to-sky-50 flex items-center justify-center text-sky-600 font-bold text-xs border border-sky-200/50">RN</div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800">Rina N.</p>
                                    <p class="text-[10px] text-slate-400">0821-1234-5678</p>
                                    <p class="text-[10px] text-slate-400">iPhone 16 Pro Max — Service #TKT-1024</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cart Items -->
                    <div class="px-5 py-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Invoice Items</h3>
                            <span class="text-xs font-semibold text-slate-600">3 items</span>
                        </div>

                        <!-- Items Table -->
                        <div class="space-y-2.5 mb-4">
                            <!-- Item 1 -->
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-indigo-50/40 border border-indigo-100/50">
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-slate-800">LCD iPhone 16 Pro Max — OEM</p>
                                    <p class="text-[10px] text-slate-400">1 × Rp 1,250,000</p>
                                </div>
                                <p class="text-sm font-bold text-slate-900">Rp 1,250,000</p>
                                <button class="p-1 rounded-lg hover:bg-white text-slate-400 hover:text-rose-500 transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            <!-- Item 2 -->
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-indigo-50/40 border border-indigo-100/50">
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-slate-800">Component-Level Diagnostic</p>
                                    <p class="text-[10px] text-slate-400">1 × Rp 150,000</p>
                                </div>
                                <p class="text-sm font-bold text-slate-900">Rp 150,000</p>
                                <button class="p-1 rounded-lg hover:bg-white text-slate-400 hover:text-rose-500 transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            <!-- Item 3 -->
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-indigo-50/40 border border-indigo-100/50">
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-slate-800">Tempered Glass — iPhone 16</p>
                                    <p class="text-[10px] text-slate-400">2 × Rp 35,000</p>
                                </div>
                                <p class="text-sm font-bold text-slate-900">Rp 70,000</p>
                                <button class="p-1 rounded-lg hover:bg-white text-slate-400 hover:text-rose-500 transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Add Item / Service Fee -->
                        <div class="flex gap-2 mb-5">
                            <button class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl border-2 border-dashed border-slate-200 text-slate-400 text-xs font-medium hover:border-indigo-300 hover:text-indigo-500 hover:bg-indigo-50/30 transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                Add Item
                            </button>
                            <button class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl border-2 border-dashed border-slate-200 text-slate-400 text-xs font-medium hover:border-indigo-300 hover:text-indigo-500 hover:bg-indigo-50/30 transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                Service Fee
                            </button>
                        </div>

                        <!-- Totals -->
                        <div class="space-y-2 py-4 border-t border-slate-100">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-500">Subtotal</span>
                                <span class="text-sm font-semibold text-slate-700">Rp 1,470,000</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-500">Tax (PPN 11%)</span>
                                <span class="text-sm font-semibold text-slate-700">Rp 161,700</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-500">Service Fee</span>
                                <span class="text-sm font-semibold text-slate-700">Rp 0</span>
                            </div>
                            <div class="flex items-center justify-between pt-3 border-t-2 border-slate-200">
                                <span class="text-sm font-bold text-slate-900">Total</span>
                                <span class="text-lg font-bold text-indigo-600">Rp 1,631,700</span>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="mb-5">
                            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Payment Method</p>
                            <div class="grid grid-cols-3 gap-2">
                                <button class="flex flex-col items-center gap-1.5 px-3 py-2.5 rounded-xl bg-indigo-50 border-2 border-indigo-300 text-indigo-700 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    <span class="text-[10px] font-semibold">Cash</span>
                                </button>
                                <button class="flex flex-col items-center gap-1.5 px-3 py-2.5 rounded-xl border-2 border-slate-200 text-slate-500 hover:border-indigo-200 hover:text-indigo-500 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                    <span class="text-[10px] font-semibold">Transfer</span>
                                </button>
                                <button class="flex flex-col items-center gap-1.5 px-3 py-2.5 rounded-xl border-2 border-slate-200 text-slate-500 hover:border-indigo-200 hover:text-indigo-500 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span class="text-[10px] font-semibold">QRIS</span>
                                </button>
                            </div>
                        </div>

                        <!-- Generate Invoice Button -->
                        <button class="w-full flex items-center justify-center gap-3 px-6 py-3.5 rounded-2xl bg-gradient-to-r from-indigo-600 via-indigo-500 to-indigo-600 text-white text-sm font-bold hover:from-indigo-700 hover:via-indigo-600 hover:to-indigo-700 hover:shadow-premium hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 shadow-soft group">
                            <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-white/15 group-hover:bg-white/20 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </span>
                            Generate Invoice & Print
                            <svg class="w-4 h-4 opacity-70 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17l2-2m0 0l-2-2m2 2H9m4 4v-2a4 4 0 00-4-4H5"/></svg>
                        </button>

                        <!-- Note -->
                        <p class="text-center text-[10px] text-slate-400 mt-3">Invoice akan digenerate sebagai PDF dan dapat dicetak</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== FOOTER ===== -->
        <p class="text-center mt-8 text-xs text-slate-400">&copy; 2026 ServiceKU. All rights reserved.</p>
    </div>
</body>
</html>