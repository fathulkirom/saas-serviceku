<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Super Admin — ServiceKU</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite('resources/css/app.css')
    <style>
        body { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
        .sidebar-scroll::-webkit-scrollbar { width: 3px; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
    </style>
</head>
<body class="antialiased bg-slate-50 text-slate-800">

    <div class="flex h-screen overflow-hidden">

        <!-- ===== SIDEBAR (dark) ===== -->
        <aside class="hidden lg:flex lg:flex-col w-60 bg-slate-900 text-slate-300 flex-shrink-0">
            <!-- Logo -->
            <div class="flex items-center gap-2.5 px-4 h-14 border-b border-slate-800/80 flex-shrink-0">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center text-white font-bold text-xs shadow-premium">SK</div>
                <div>
                    <span class="text-sm font-bold text-white">ServiceKU</span>
                    <span class="block text-[9px] text-slate-500 font-medium uppercase tracking-wider">Super Admin</span>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-3 py-4 overflow-y-auto sidebar-scroll space-y-1">
                <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg bg-indigo-600/20 text-indigo-300 text-sm font-medium border border-indigo-500/20">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard
                </a>
                <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800/60 text-sm font-medium transition-all">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Tenants
                </a>
                <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800/60 text-sm font-medium transition-all">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Users
                </a>
                <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800/60 text-sm font-medium transition-all">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Billing
                </a>
                <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800/60 text-sm font-medium transition-all">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Settings
                </a>
            </nav>

            <!-- Sidebar Footer -->
            <div class="px-3 py-3 border-t border-slate-800/80">
                <a href="{{ route('home') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-500 hover:text-slate-300 hover:bg-slate-800/60 text-xs font-medium transition-all">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Back to App
                </a>
            </div>
        </aside>

        <!-- ===== MAIN CONTENT AREA ===== -->
        <div class="flex-1 flex flex-col min-w-0">

            <!-- ===== TOP NAVBAR ===== -->
            <header class="bg-white border-b border-slate-200 flex-shrink-0">
                <div class="flex items-center justify-between h-14 px-4">
                    <div class="flex items-center gap-3">
                        <!-- Mobile menu button -->
                        <button class="lg:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                        <!-- Page Title -->
                        <h1 class="text-base font-bold text-slate-900 hidden sm:block">Dashboard</h1>
                    </div>
                    <div class="flex items-center gap-3">
                        <!-- Global Search -->
                        <div class="relative hidden md:block">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" placeholder="Search tenants, users..." class="w-56 pl-9 pr-3 py-1.5 rounded-lg border border-slate-200 bg-slate-50 text-xs text-slate-700 placeholder:text-slate-400 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 transition-all">
                        </div>
                        <div class="w-px h-6 bg-slate-200 hidden md:block"></div>
                        <!-- Notification -->
                        <button class="relative p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            <span class="absolute top-0 right-0 w-4 h-4 bg-rose-500 text-white text-[8px] font-bold rounded-full flex items-center justify-center ring-2 ring-white">3</span>
                        </button>
                        <!-- Admin Avatar -->
                        <div class="flex items-center gap-2 pl-1">
                            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center text-white text-[10px] font-bold shadow-soft">SA</div>
                            <div class="hidden sm:block">
                                <p class="text-xs font-semibold text-slate-800 leading-tight">Super Admin</p>
                                <p class="text-[9px] text-slate-400 leading-tight">admin@serviceku.my.id</p>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- ===== PAGE CONTENT (scrollable) ===== -->
            <main class="flex-1 overflow-y-auto px-4 py-4 space-y-4">

                <!-- ===== METRIC CARDS (compact) ===== -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                    <div class="bg-white rounded-xl border border-slate-100 p-3.5 shadow-soft flex items-center gap-3.5">
                        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center shadow-sm flex-shrink-0">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Active Tenants</p>
                            <p class="text-xl font-bold text-slate-900 leading-none mt-0.5">24</p>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl border border-slate-100 p-3.5 shadow-soft flex items-center gap-3.5">
                        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center shadow-sm flex-shrink-0">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Monthly Recurring Revenue</p>
                            <p class="text-xl font-bold text-slate-900 leading-none mt-0.5">Rp 24,750,000</p>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl border border-slate-100 p-3.5 shadow-soft flex items-center gap-3.5">
                        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-sm flex-shrink-0">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">New Signups (Week)</p>
                            <p class="text-xl font-bold text-slate-900 leading-none mt-0.5">8</p>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl border border-slate-100 p-3.5 shadow-soft flex items-center gap-3.5">
                        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-rose-500 to-red-600 flex items-center justify-center shadow-sm flex-shrink-0">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Suspended</p>
                            <p class="text-xl font-bold text-rose-600 leading-none mt-0.5">2</p>
                        </div>
                    </div>
                </div>

                <!-- ===== TOOLBAR: Search + Actions ===== -->
                <div class="bg-white rounded-xl border border-slate-100 shadow-soft">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 px-4 py-3">
                        <div class="relative flex-1 w-full">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" placeholder="Search by shop name, owner, email..." class="w-full pl-9 pr-3 py-1.5 rounded-lg border border-slate-200 bg-slate-50 text-xs text-slate-700 placeholder:text-slate-400 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 transition-all">
                        </div>
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <select class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white text-xs text-slate-600 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 transition-all">
                                <option>All Status</option>
                                <option>Active</option>
                                <option>Suspended</option>
                                <option>Trial</option>
                            </select>
                            <select class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white text-xs text-slate-600 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 transition-all">
                                <option>All Plans</option>
                                <option>Basic</option>
                                <option>Pro</option>
                                <option>Enterprise</option>
                            </select>
                            <button class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 transition-all shadow-soft flex-shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                Add Tenant
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ===== DATA TABLE ===== -->
                <div class="bg-white rounded-xl border border-slate-100 shadow-soft overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-100">
                                    <th class="sticky top-0 px-3 py-2 text-left text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Shop Name</th>
                                    <th class="sticky top-0 px-3 py-2 text-left text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Owner</th>
                                    <th class="sticky top-0 px-3 py-2 text-left text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Plan</th>
                                    <th class="sticky top-0 px-3 py-2 text-left text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                                    <th class="sticky top-0 px-3 py-2 text-left text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Next Billing</th>
                                    <th class="sticky top-0 px-3 py-2 text-left text-[10px] font-semibold text-slate-500 uppercase tracking-wider">MRR</th>
                                    <th class="sticky top-0 px-3 py-2 text-right text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr class="hover:bg-indigo-50/30 transition-colors group">
                                    <td class="px-3 py-2">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-100 to-indigo-50 flex items-center justify-center text-indigo-600 font-bold text-[10px]">TA</div>
                                            <div>
                                                <p class="text-xs font-semibold text-slate-800">Toko Servis ABC</p>
                                                <p class="text-[10px] text-slate-400">Joined: 15 Jan 2026</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2">
                                        <p class="text-xs font-medium text-slate-700">Budi Santoso</p>
                                        <p class="text-[10px] text-slate-400">budi@tokoservis.com</p>
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-600 text-[10px] font-semibold border border-indigo-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                            Pro
                                        </span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-600 text-[10px] font-semibold border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Active
                                        </span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="text-xs text-slate-600">15 Aug 2026</span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="text-xs font-semibold text-slate-700">Rp 199K</span>
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button class="p-1 rounded-md hover:bg-white text-slate-400 hover:text-indigo-600 transition-all shadow-soft" title="Edit">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                            <button class="p-1 rounded-md hover:bg-white text-slate-400 hover:text-amber-600 transition-all shadow-soft" title="Suspend">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                            </button>
                                            <button class="p-1 rounded-md hover:bg-white text-slate-400 hover:text-rose-600 transition-all shadow-soft" title="Delete">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="hover:bg-indigo-50/30 transition-colors group">
                                    <td class="px-3 py-2">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-emerald-100 to-teal-50 flex items-center justify-center text-emerald-600 font-bold text-[10px]">KR</div>
                                            <div>
                                                <p class="text-xs font-semibold text-slate-800">Kirom Apple Solution</p>
                                                <p class="text-[10px] text-slate-400">Joined: 20 Mar 2026</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2">
                                        <p class="text-xs font-medium text-slate-700">Kirom</p>
                                        <p class="text-[10px] text-slate-400">kirom@apple-sol.id</p>
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-violet-50 text-violet-600 text-[10px] font-semibold border border-violet-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-violet-500"></span>
                                            Enterprise
                                        </span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-600 text-[10px] font-semibold border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Active
                                        </span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="text-xs text-slate-600">20 Sep 2026</span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="text-xs font-semibold text-slate-700">Rp 499K</span>
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button class="p-1 rounded-md hover:bg-white text-slate-400 hover:text-indigo-600 transition-all shadow-soft"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                                            <button class="p-1 rounded-md hover:bg-white text-slate-400 hover:text-amber-600 transition-all shadow-soft"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg></button>
                                            <button class="p-1 rounded-md hover:bg-white text-slate-400 hover:text-rose-600 transition-all shadow-soft"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="hover:bg-indigo-50/30 transition-colors group">
                                    <td class="px-3 py-2">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-amber-100 to-yellow-50 flex items-center justify-center text-amber-600 font-bold text-[10px]">MS</div>
                                            <div>
                                                <p class="text-xs font-semibold text-slate-800">Makassar Servis Center</p>
                                                <p class="text-[10px] text-slate-400">Joined: 5 Apr 2026</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2">
                                        <p class="text-xs font-medium text-slate-700">Ahmad Fauzan</p>
                                        <p class="text-[10px] text-slate-400">ahmad@mks-servis.com</p>
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 text-slate-500 text-[10px] font-semibold border border-slate-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            Basic
                                        </span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-amber-50 text-amber-600 text-[10px] font-semibold border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                            Trial
                                        </span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="text-xs text-slate-600">5 May 2026</span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="text-xs font-semibold text-slate-700">Rp 0</span>
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button class="p-1 rounded-md hover:bg-white text-slate-400 hover:text-indigo-600 transition-all shadow-soft"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                                            <button class="p-1 rounded-md hover:bg-white text-slate-400 hover:text-amber-600 transition-all shadow-soft"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg></button>
                                            <button class="p-1 rounded-md hover:bg-white text-slate-400 hover:text-rose-600 transition-all shadow-soft"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="hover:bg-indigo-50/30 transition-colors group">
                                    <td class="px-3 py-2">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-rose-100 to-pink-50 flex items-center justify-center text-rose-600 font-bold text-[10px]">SB</div>
                                            <div>
                                                <p class="text-xs font-semibold text-slate-800">Surabaya Repair Hub</p>
                                                <p class="text-[10px] text-slate-400">Joined: 10 Jan 2026</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2">
                                        <p class="text-xs font-medium text-slate-700">Dewi Lestari</p>
                                        <p class="text-[10px] text-slate-400">dewi@surabaya-repair.com</p>
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-600 text-[10px] font-semibold border border-indigo-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                            Pro
                                        </span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-rose-50 text-rose-600 text-[10px] font-semibold border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            Suspended
                                        </span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="text-xs text-slate-400">—</span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="text-xs font-semibold text-slate-400">—</span>
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button class="p-1 rounded-md hover:bg-white text-slate-400 hover:text-indigo-600 transition-all shadow-soft"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                                            <button class="p-1 rounded-md hover:bg-white text-slate-400 hover:text-emerald-600 transition-all shadow-soft" title="Activate">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </button>
                                            <button class="p-1 rounded-md hover:bg-white text-slate-400 hover:text-rose-600 transition-all shadow-soft"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="hover:bg-indigo-50/30 transition-colors group">
                                    <td class="px-3 py-2">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-cyan-100 to-sky-50 flex items-center justify-center text-cyan-600 font-bold text-[10px]">BG</div>
                                            <div>
                                                <p class="text-xs font-semibold text-slate-800">Bogor Gadget Care</p>
                                                <p class="text-[10px] text-slate-400">Joined: 22 Feb 2026</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2">
                                        <p class="text-xs font-medium text-slate-700">Rudi Hermawan</p>
                                        <p class="text-[10px] text-slate-400">rudi@bogorgadget.com</p>
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-600 text-[10px] font-semibold border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Enterprise
                                        </span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-600 text-[10px] font-semibold border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Active
                                        </span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="text-xs text-slate-600">22 Aug 2026</span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="text-xs font-semibold text-slate-700">Rp 499K</span>
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button class="p-1 rounded-md hover:bg-white text-slate-400 hover:text-indigo-600 transition-all shadow-soft"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                                            <button class="p-1 rounded-md hover:bg-white text-slate-400 hover:text-amber-600 transition-all shadow-soft"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg></button>
                                            <button class="p-1 rounded-md hover:bg-white text-slate-400 hover:text-rose-600 transition-all shadow-soft"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Table Footer -->
                    <div class="px-4 py-2.5 border-t border-slate-100 flex items-center justify-between">
                        <p class="text-[10px] text-slate-400">Showing 1 to 5 of 24 tenants</p>
                        <div class="flex items-center gap-1">
                            <button class="px-2 py-1 rounded-md text-[10px] font-medium text-slate-400 hover:text-slate-600 hover:bg-slate-50 border border-slate-200">Previous</button>
                            <button class="px-2.5 py-1 rounded-md text-[10px] font-medium bg-indigo-600 text-white border border-indigo-600">1</button>
                            <button class="px-2.5 py-1 rounded-md text-[10px] font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 border border-slate-200">2</button>
                            <button class="px-2.5 py-1 rounded-md text-[10px] font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 border border-slate-200">3</button>
                            <span class="px-1 text-[10px] text-slate-300">...</span>
                            <button class="px-2.5 py-1 rounded-md text-[10px] font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 border border-slate-200">5</button>
                            <button class="px-2 py-1 rounded-md text-[10px] font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 border border-slate-200">Next</button>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

</body>
</html>