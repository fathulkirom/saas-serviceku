<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Repair Tickets — ServiceKU</title>
    @vite('resources/css/app.css')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-indigo-50/20 to-slate-50 text-slate-800 antialiased">

    <!-- ===== TOP NAVBAR ===== -->
    <nav class="sticky top-0 z-40 bg-white/70 backdrop-blur-xl border-b border-slate-200/50 shadow-soft">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 lg:h-18">
                <!-- Left: Logo + Branch -->
                <div class="flex items-center gap-4">
                    <a href="#" class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-600 to-indigo-500 flex items-center justify-center text-white font-bold text-sm shadow-premium">SK</div>
                        <span class="text-lg font-bold text-slate-900 hidden sm:block">ServiceKU</span>
                        <span class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-600 text-[10px] font-semibold border border-indigo-200">Pro Plan</span>
                    </a>
                    <div class="hidden md:flex items-center gap-1.5">
                        <div class="w-px h-6 bg-slate-200 mx-2"></div>
                        <a href="#" class="px-3 py-1.5 rounded-lg text-xs font-medium text-slate-400 hover:text-slate-700 hover:bg-slate-50 transition-all">Dashboard</a>
                        <a href="#" class="px-3 py-1.5 rounded-lg text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200/50">Repair Tickets</a>
                        <a href="#" class="px-3 py-1.5 rounded-lg text-xs font-medium text-slate-400 hover:text-slate-700 hover:bg-slate-50 transition-all">Customers</a>
                        <a href="#" class="px-3 py-1.5 rounded-lg text-xs font-medium text-slate-400 hover:text-slate-700 hover:bg-slate-50 transition-all">Inventory</a>
                    </div>
                </div>
                <!-- Right -->
                <div class="flex items-center gap-3">
                    <button class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                    <button class="relative p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-rose-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center shadow-soft">4</span>
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
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 animate__fadeIn">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Repair Tickets</h1>
                <p class="text-sm text-slate-400 mt-0.5">Kelola dan追踪 semua perbaikan perangkat pelanggan</p>
            </div>
            <div class="flex items-center gap-3">
                <button class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50 hover:border-slate-300 transition-all shadow-soft">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Filter
                </button>
                <button class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-500 text-white text-sm font-semibold hover:from-indigo-700 hover:to-indigo-600 hover:shadow-premium hover:-translate-y-0.5 active:translate-y-0 transition-all shadow-soft">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    New Ticket
                </button>
            </div>
        </div>

        <!-- ===== STATS OVERVIEW ===== -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-2xl border border-slate-100/50 p-4 shadow-soft flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center shadow-premium">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Tickets</p>
                    <p class="text-2xl font-bold text-slate-900">24</p>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-100/50 p-4 shadow-soft flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-lg">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">In Progress</p>
                    <p class="text-2xl font-bold text-slate-900">11</p>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-100/50 p-4 shadow-soft flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center shadow-lg">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Completed Today</p>
                    <p class="text-2xl font-bold text-slate-900">7</p>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-100/50 p-4 shadow-soft flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-rose-500 to-red-600 flex items-center justify-center shadow-lg">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Overdue</p>
                    <p class="text-2xl font-bold text-rose-600">4</p>
                </div>
            </div>
        </div>

        <!-- ===== FILTER TABS ===== -->
        <div class="bg-white rounded-2xl border border-slate-100/50 p-4 shadow-soft mb-6">
            <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
                <div class="flex flex-wrap gap-2">
                    <button class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-semibold shadow-soft border border-indigo-600">All Tickets (24)</button>
                    <button class="px-4 py-2 rounded-xl bg-white text-slate-500 text-xs font-semibold border border-slate-200 hover:border-indigo-300 hover:text-indigo-600 transition-all">Diagnosing (5)</button>
                    <button class="px-4 py-2 rounded-xl bg-white text-slate-500 text-xs font-semibold border border-slate-200 hover:border-indigo-300 hover:text-indigo-600 transition-all">Waiting Parts (3)</button>
                    <button class="px-4 py-2 rounded-xl bg-white text-slate-500 text-xs font-semibold border border-slate-200 hover:border-indigo-300 hover:text-indigo-600 transition-all">Repairing (6)</button>
                    <button class="px-4 py-2 rounded-xl bg-white text-slate-500 text-xs font-semibold border border-slate-200 hover:border-indigo-300 hover:text-indigo-600 transition-all">Ready Pickup (4)</button>
                    <button class="px-4 py-2 rounded-xl bg-white text-slate-500 text-xs font-semibold border border-slate-200 hover:border-indigo-300 hover:text-indigo-600 transition-all">Completed (6)</button>
                </div>
                <div class="relative w-full sm:w-60">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" placeholder="Search tickets..." class="w-full pl-10 pr-4 py-2 rounded-xl border border-slate-200 bg-white/80 text-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                </div>
            </div>
        </div>

        <!-- ===== KANBAN / BOARD VIEW ===== -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-5 mb-8">
            <!-- Column: Diagnosing -->
            <div class="bg-slate-100/60 rounded-2xl p-4 border border-slate-200/50">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                        <h3 class="text-sm font-bold text-slate-700">Diagnosing</h3>
                    </div>
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-amber-100 text-amber-700 text-xs font-bold">5</span>
                </div>
                <div class="space-y-3">
                    <!-- Card 1 -->
                    <div class="bg-white rounded-xl p-4 shadow-soft border border-slate-100 hover:shadow-soft-lg hover:-translate-y-0.5 transition-all duration-200 cursor-pointer group">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-sky-100 to-sky-50 flex items-center justify-center text-sky-600 font-bold text-xs border border-sky-200/50">RN</div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800 leading-tight">Rina N.</p>
                                    <p class="text-[10px] text-slate-400">Since 2 days</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">Diagnosing</span>
                        </div>
                        <div class="space-y-1.5 mb-3">
                            <div class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <span class="text-xs font-medium text-slate-700">MacBook Air M4</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                <span class="text-xs text-slate-500 leading-tight">NAND Flash replacement — device not powering on after liquid spill</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between pt-2.5 border-t border-slate-100">
                            <div class="flex -space-x-1.5">
                                <div class="w-6 h-6 rounded-full bg-gradient-to-br from-indigo-400 to-indigo-500 flex items-center justify-center text-white text-[8px] font-bold ring-2 ring-white">AD</div>
                                <div class="w-6 h-6 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-500 flex items-center justify-center text-white text-[8px] font-bold ring-2 ring-white">ST</div>
                            </div>
                            <div class="flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-indigo-600 transition-all" title="View Details">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                                <button class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-amber-600 transition-all" title="Update Status">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                </button>
                                <button class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-emerald-600 transition-all" title="Message Customer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- Card 2 -->
                    <div class="bg-white rounded-xl p-4 shadow-soft border border-slate-100 hover:shadow-soft-lg hover:-translate-y-0.5 transition-all duration-200 cursor-pointer group">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-100 to-purple-50 flex items-center justify-center text-violet-600 font-bold text-xs border border-violet-200/50">DH</div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800 leading-tight">Dimas H.</p>
                                    <p class="text-[10px] text-slate-400">Since 1 day</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">Diagnosing</span>
                        </div>
                        <div class="space-y-1.5 mb-3">
                            <div class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                <span class="text-xs font-medium text-slate-700">Samsung Galaxy S25 Ultra</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                <span class="text-xs text-slate-500 leading-tight">Intermittent boot loop — component-level diagnostic required</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between pt-2.5 border-t border-slate-100">
                            <div class="flex -space-x-1.5">
                                <div class="w-6 h-6 rounded-full bg-gradient-to-br from-indigo-400 to-indigo-500 flex items-center justify-center text-white text-[8px] font-bold ring-2 ring-white">AD</div>
                            </div>
                            <div class="flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-indigo-600 transition-all" title="View Details">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                                <button class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-amber-600 transition-all" title="Update Status">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                </button>
                                <button class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-emerald-600 transition-all" title="Message Customer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- Card 3 -->
                    <div class="bg-white rounded-xl p-4 shadow-soft border border-slate-100 hover:shadow-soft-lg hover:-translate-y-0.5 transition-all duration-200 cursor-pointer group">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-pink-100 to-rose-50 flex items-center justify-center text-pink-600 font-bold text-xs border border-pink-200/50">SA</div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800 leading-tight">Sari A.</p>
                                    <p class="text-[10px] text-slate-400">Since 3 days</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">Diagnosing</span>
                        </div>
                        <div class="space-y-1.5 mb-3">
                            <div class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <span class="text-xs font-medium text-slate-700">iPhone 16 Pro Max</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                <span class="text-xs text-slate-500 leading-tight">Overheating & battery drain — component-level diagnostic</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between pt-2.5 border-t border-slate-100">
                            <div class="flex -space-x-1.5">
                                <div class="w-6 h-6 rounded-full bg-gradient-to-br from-indigo-400 to-indigo-500 flex items-center justify-center text-white text-[8px] font-bold ring-2 ring-white">AD</div>
                                <div class="w-6 h-6 rounded-full bg-gradient-to-br from-cyan-400 to-cyan-500 flex items-center justify-center text-white text-[8px] font-bold ring-2 ring-white">FN</div>
                            </div>
                            <div class="flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-indigo-600 transition-all" title="View Details">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                                <button class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-amber-600 transition-all" title="Update Status">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                </button>
                                <button class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-emerald-600 transition-all" title="Message Customer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Column: Waiting for Parts -->
            <div class="bg-slate-100/60 rounded-2xl p-4 border border-slate-200/50">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-3 h-3 rounded-full bg-violet-500"></div>
                        <h3 class="text-sm font-bold text-slate-700">Waiting for Parts</h3>
                    </div>
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-violet-100 text-violet-700 text-xs font-bold">3</span>
                </div>
                <div class="space-y-3">
                    <!-- Card -->
                    <div class="bg-white rounded-xl p-4 shadow-soft border border-slate-100 hover:shadow-soft-lg hover:-translate-y-0.5 transition-all duration-200 cursor-pointer group">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-cyan-100 to-sky-50 flex items-center justify-center text-cyan-600 font-bold text-xs border border-cyan-200/50">AL</div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800 leading-tight">Andi L.</p>
                                    <p class="text-[10px] text-slate-400">Since 5 days</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-violet-50 text-violet-700 border border-violet-200">Waiting Parts</span>
                        </div>
                        <div class="space-y-1.5 mb-3">
                            <div class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <span class="text-xs font-medium text-slate-700">MacBook Pro 14" M3 Pro</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                <span class="text-xs text-slate-500 leading-tight">Display assembly replacement — waiting for Apple OEM panel</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between pt-2.5 border-t border-slate-100">
                            <div class="flex items-center gap-1.5">
                                <div class="w-1.5 h-1.5 rounded-full bg-violet-400 animate-pulse-soft"></div>
                                <span class="text-[10px] text-violet-500 font-medium">Est. arrival: 3 days</span>
                            </div>
                            <div class="flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-indigo-600 transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
                                <button class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-amber-600 transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg></button>
                                <button class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-emerald-600 transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg></button>
                            </div>
                        </div>
                    </div>
                    <!-- Card 2 -->
                    <div class="bg-white rounded-xl p-4 shadow-soft border border-slate-100 hover:shadow-soft-lg hover:-translate-y-0.5 transition-all duration-200 cursor-pointer group">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-100 to-teal-50 flex items-center justify-center text-emerald-600 font-bold text-xs border border-emerald-200/50">FW</div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800 leading-tight">Fitri W.</p>
                                    <p class="text-[10px] text-slate-400">Since 7 days</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-violet-50 text-violet-700 border border-violet-200">Waiting Parts</span>
                        </div>
                        <div class="space-y-1.5 mb-3">
                            <div class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                <span class="text-xs font-medium text-slate-700">iPhone 15 Pro</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                <span class="text-xs text-slate-500 leading-tight">Rear camera module replacement — waiting for OEM part</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between pt-2.5 border-t border-slate-100">
                            <div class="flex items-center gap-1.5">
                                <div class="w-1.5 h-1.5 rounded-full bg-violet-400 animate-pulse-soft"></div>
                                <span class="text-[10px] text-violet-500 font-medium">Est. arrival: 1 day</span>
                            </div>
                            <div class="flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-indigo-600 transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
                                <button class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-amber-600 transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg></button>
                                <button class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-emerald-600 transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Column: Repairing -->
            <div class="bg-slate-100/60 rounded-2xl p-4 border border-slate-200/50">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                        <h3 class="text-sm font-bold text-slate-700">Repairing</h3>
                    </div>
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-blue-100 text-blue-700 text-xs font-bold">6</span>
                </div>
                <div class="space-y-3">
                    <!-- Card -->
                    <div class="bg-white rounded-xl p-4 shadow-soft border border-slate-100 hover:shadow-soft-lg hover:-translate-y-0.5 transition-all duration-200 cursor-pointer group">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-100 to-yellow-50 flex items-center justify-center text-amber-600 font-bold text-xs border border-amber-200/50">DP</div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800 leading-tight">Doni P.</p>
                                    <p class="text-[10px] text-slate-400">Since 4 days</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">Repairing</span>
                        </div>
                        <div class="space-y-1.5 mb-3">
                            <div class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <span class="text-xs font-medium text-slate-700">MacBook Air M4</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                <span class="text-xs text-slate-500 leading-tight">NAND Flash replacement — in-circuit programming in progress</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between pt-2.5 border-t border-slate-100">
                            <div class="flex -space-x-1.5">
                                <div class="w-6 h-6 rounded-full bg-gradient-to-br from-cyan-400 to-cyan-500 flex items-center justify-center text-white text-[8px] font-bold ring-2 ring-white">FN</div>
                            </div>
                            <div class="flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-indigo-600 transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
                                <button class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-amber-600 transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg></button>
                                <button class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-emerald-600 transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg></button>
                            </div>
                        </div>
                    </div>
                    <!-- Card 2 -->
                    <div class="bg-white rounded-xl p-4 shadow-soft border border-slate-100 hover:shadow-soft-lg hover:-translate-y-0.5 transition-all duration-200 cursor-pointer group">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-sky-100 to-blue-50 flex items-center justify-center text-sky-600 font-bold text-xs border border-sky-200/50">MS</div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800 leading-tight">Mega S.</p>
                                    <p class="text-[10px] text-slate-400">Since 6 days</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">Repairing</span>
                        </div>
                        <div class="space-y-1.5 mb-3">
                            <div class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                <span class="text-xs font-medium text-slate-700">Samsung Galaxy Z Fold 6</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                <span class="text-xs text-slate-500 leading-tight">Inner screen replacement — hinge mechanism repair underway</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between pt-2.5 border-t border-slate-100">
                            <div class="flex -space-x-1.5">
                                <div class="w-6 h-6 rounded-full bg-gradient-to-br from-indigo-400 to-indigo-500 flex items-center justify-center text-white text-[8px] font-bold ring-2 ring-white">AD</div>
                                <div class="w-6 h-6 rounded-full bg-gradient-to-br from-cyan-400 to-cyan-500 flex items-center justify-center text-white text-[8px] font-bold ring-2 ring-white">FN</div>
                            </div>
                            <div class="flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-indigo-600 transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
                                <button class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-amber-600 transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg></button>
                                <button class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-emerald-600 transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Column: Ready for Pickup -->
            <div class="bg-slate-100/60 rounded-2xl p-4 border border-slate-200/50">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                        <h3 class="text-sm font-bold text-slate-700">Ready for Pickup</h3>
                    </div>
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-emerald-100 text-emerald-700 text-xs font-bold">4</span>
                </div>
                <div class="space-y-3">
                    <!-- Card -->
                    <div class="bg-white rounded-xl p-4 shadow-soft border border-emerald-200/50 hover:shadow-soft-lg hover:-translate-y-0.5 transition-all duration-200 cursor-pointer group">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-green-100 to-emerald-50 flex items-center justify-center text-emerald-600 font-bold text-xs border border-emerald-200/50">RW</div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800 leading-tight">Rina W.</p>
                                    <p class="text-[10px] text-slate-400">Completed today</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Ready Pickup</span>
                        </div>
                        <div class="space-y-1.5 mb-3">
                            <div class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                <span class="text-xs font-medium text-slate-700">iPhone 15 Pro Max</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                <span class="text-xs text-slate-500 leading-tight">Battery replacement + charging port cleaning — all tests passed</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between pt-2.5 border-t border-slate-100">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span class="text-[10px] text-emerald-600 font-medium">QC Passed</span>
                            </div>
                            <div class="flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-indigo-600 transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
                                <button class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-amber-600 transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg></button>
                                <button class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-emerald-600 transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg></button>
                            </div>
                        </div>
                    </div>
                    <!-- Card 2 -->
                    <div class="bg-white rounded-xl p-4 shadow-soft border border-emerald-200/50 hover:shadow-soft-lg hover:-translate-y-0.5 transition-all duration-200 cursor-pointer group">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-orange-100 to-amber-50 flex items-center justify-center text-orange-600 font-bold text-xs border border-orange-200/50">AH</div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800 leading-tight">Agus H.</p>
                                    <p class="text-[10px] text-slate-400">Completed yesterday</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Ready Pickup</span>
                        </div>
                        <div class="space-y-1.5 mb-3">
                            <div class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <span class="text-xs font-medium text-slate-700">MacBook Pro 16" M4 Max</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                <span class="text-xs text-slate-500 leading-tight">Logic board repair — power IC replacement, full functional test OK</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between pt-2.5 border-t border-slate-100">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span class="text-[10px] text-emerald-600 font-medium">QC Passed</span>
                            </div>
                            <div class="flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-indigo-600 transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
                                <button class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-amber-600 transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg></button>
                                <button class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-emerald-600 transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== BOTTOM: DATA TABLE VIEW ===== -->
        <div class="bg-white rounded-2xl border border-slate-100/50 shadow-soft overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <h3 class="text-base font-bold text-slate-900">All Repair Tickets</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-600 text-[10px] font-semibold border border-indigo-200">24 records</span>
                </div>
                <div class="flex items-center gap-2">
                    <button class="p-2 rounded-lg hover:bg-slate-50 text-slate-400 hover:text-slate-600 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <button class="p-2 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Ticket</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Device</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Issue</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Technician</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-slate-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr class="hover:bg-indigo-50/30 transition-colors cursor-pointer group">
                            <td class="px-6 py-4"><span class="font-mono font-bold text-indigo-600 text-sm">#TKT-1024</span></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-sky-100 to-sky-50 flex items-center justify-center text-sky-600 font-bold text-xs">RN</div>
                                    <div><p class="text-sm font-semibold text-slate-800">Rina N.</p><p class="text-[10px] text-slate-400">0821-1234-5678</p></div>
                                </div>
                            </td>
                            <td class="px-6 py-4"><span class="text-sm font-medium text-slate-700">MacBook Air M4</span></td>
                            <td class="px-6 py-4"><span class="text-xs text-slate-500 max-w-[200px] inline-block truncate">NAND Flash replacement — not powering on after liquid spill</span></td>
                            <td class="px-6 py-4"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200"><span class="w-1.5 h-1.5 rounded-full bg-amber-400 mr-1.5"></span>Diagnosing</span></td>
                            <td class="px-6 py-4"><div class="flex items-center gap-2"><div class="w-6 h-6 rounded-full bg-gradient-to-br from-indigo-400 to-indigo-500 flex items-center justify-center text-white text-[8px] font-bold">AD</div><span class="text-sm text-slate-600">A. Dwi</span></div></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button class="p-1.5 rounded-lg hover:bg-white text-slate-400 hover:text-indigo-600 transition-all shadow-soft" title="View Details"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
                                    <button class="p-1.5 rounded-lg hover:bg-white text-slate-400 hover:text-amber-600 transition-all shadow-soft" title="Update Status"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg></button>
                                    <button class="p-1.5 rounded-lg hover:bg-white text-slate-400 hover:text-emerald-600 transition-all shadow-soft" title="Message Customer"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg></button>
                                </div>
                            </td>
                        </tr>
                        <tr class="hover:bg-indigo-50/30 transition-colors cursor-pointer group">
                            <td class="px-6 py-4"><span class="font-mono font-bold text-indigo-600 text-sm">#TKT-1023</span></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-orange-100 to-amber-50 flex items-center justify-center text-orange-600 font-bold text-xs">AH</div>
                                    <div><p class="text-sm font-semibold text-slate-800">Agus H.</p><p class="text-[10px] text-slate-400">0855-4321-8765</p></div>
                                </div>
                            </td>
                            <td class="px-6 py-4"><span class="text-sm font-medium text-slate-700">MacBook Pro 16" M4 Max</span></td>
                            <td class="px-6 py-4"><span class="text-xs text-slate-500 max-w-[200px] inline-block truncate">Logic board repair — power IC replacement</span></td>
                            <td class="px-6 py-4"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>Ready Pickup</span></td>
                            <td class="px-6 py-4"><div class="flex items-center gap-2"><div class="w-6 h-6 rounded-full bg-gradient-to-br from-cyan-400 to-cyan-500 flex items-center justify-center text-white text-[8px] font-bold">FN</div><span class="text-sm text-slate-600">F. Nur</span></div></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button class="p-1.5 rounded-lg hover:bg-white text-slate-400 hover:text-indigo-600 transition-all shadow-soft"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
                                    <button class="p-1.5 rounded-lg hover:bg-white text-slate-400 hover:text-amber-600 transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg></button>
                                    <button class="p-1.5 rounded-lg hover:bg-white text-slate-400 hover:text-emerald-600 transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg></button>
                                </div>
                            </td>
                        </tr>
                        <tr class="hover:bg-indigo-50/30 transition-colors cursor-pointer group">
                            <td class="px-6 py-4"><span class="font-mono font-bold text-indigo-600 text-sm">#TKT-1022</span></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-100 to-yellow-50 flex items-center justify-center text-amber-600 font-bold text-xs">DP</div>
                                    <div><p class="text-sm font-semibold text-slate-800">Doni P.</p><p class="text-[10px] text-slate-400">0812-9876-5432</p></div>
                                </div>
                            </td>
                            <td class="px-6 py-4"><span class="text-sm font-medium text-slate-700">MacBook Air M4</span></td>
                            <td class="px-6 py-4"><span class="text-xs text-slate-500 max-w-[200px] inline-block truncate">NAND Flash replacement — in-circuit programming</span></td>
                            <td class="px-6 py-4"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200"><span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-1.5"></span>Repairing</span></td>
                            <td class="px-6 py-4"><div class="flex items-center gap-2"><div class="w-6 h-6 rounded-full bg-gradient-to-br from-cyan-400 to-cyan-500 flex items-center justify-center text-white text-[8px] font-bold">FN</div><span class="text-sm text-slate-600">F. Nur</span></div></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button class="p-1.5 rounded-lg hover:bg-white text-slate-400 hover:text-indigo-600 transition-all shadow-soft"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
                                    <button class="p-1.5 rounded-lg hover:bg-white text-slate-400 hover:text-amber-600 transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg></button>
                                    <button class="p-1.5 rounded-lg hover:bg-white text-slate-400 hover:text-emerald-600 transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg></button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between">
                <p class="text-xs text-slate-400">Showing 3 of 24 records</p>
                <div class="flex items-center gap-1">
                    <button class="px-3 py-1.5 rounded-lg text-xs font-medium text-slate-400 hover:text-slate-600 hover:bg-slate-50 border border-slate-200">Previous</button>
                    <button class="px-3 py-1.5 rounded-lg text-xs font-medium bg-indigo-600 text-white border border-indigo-600 shadow-soft">1</button>
                    <button class="px-3 py-1.5 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 border border-slate-200">2</button>
                    <button class="px-3 py-1.5 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 border border-slate-200">3</button>
                    <span class="px-1 text-slate-300">...</span>
                    <button class="px-3 py-1.5 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 border border-slate-200">8</button>
                    <button class="px-3 py-1.5 rounded-lg text-xs font-medium text-slate-400 hover:text-slate-600 hover:bg-slate-50 border border-slate-200">Next</button>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <p class="text-center mt-8 text-xs text-slate-400">&copy; 2026 ServiceKU. All rights reserved.</p>
    </div>
</body>
</html>