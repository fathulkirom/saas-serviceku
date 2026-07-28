<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — ServiceKU</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite('resources/css/app.css')
    @stack('styles')
    <style>
        body { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
        .sidebar-scroll::-webkit-scrollbar { width: 3px; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #475569; border-radius: 10px; }
        .sidebar-collapsed { width: 56px; }
        .sidebar-collapsed .sidebar-label { display: none; }
        .sidebar-collapsed .sidebar-icon { margin: 0 auto; }
    </style>
</head>
<body class="antialiased bg-slate-950 text-slate-200">

    <div x-data="{ sidebarOpen: true }" class="flex h-screen overflow-hidden">

        <!-- ===== SIDEBAR ===== -->
        <aside
            class="hidden lg:flex lg:flex-col bg-slate-900/90 backdrop-blur-lg border-r border-slate-800 flex-shrink-0 transition-all duration-200"
            :class="sidebarOpen ? 'w-52' : 'w-14 sidebar-collapsed'"
        >
            <!-- Logo -->
            <div class="flex items-center h-12 px-3 border-b border-slate-800 flex-shrink-0" :class="sidebarOpen ? 'justify-between' : 'justify-center'">
                <template x-if="sidebarOpen">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-cyan-500 to-purple-600 flex items-center justify-center text-white font-bold text-[10px] shadow-lg shadow-cyan-500/30 flex-shrink-0">SK</div>
                        <span class="text-sm font-bold text-white truncate">ServiceKU</span>
                    </div>
                </template>
                <template x-if="!sidebarOpen">
                    <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-cyan-500 to-purple-600 flex items-center justify-center text-white font-bold text-[10px] shadow-lg shadow-cyan-500/30">SK</div>
                </template>
                <button @click="sidebarOpen = !sidebarOpen" class="p-1 rounded-md text-slate-400 hover:text-slate-200 hover:bg-slate-800 transition-all flex-shrink-0" x-show="sidebarOpen">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/></svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-2 py-3 overflow-y-auto sidebar-scroll space-y-0.5">
                <a href="#" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg bg-purple-500/10 text-purple-300 border border-purple-500/20 text-sm font-medium sidebar-active">
                    <svg class="w-4 h-4 flex-shrink-0 sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span class="sidebar-label text-sm">Dashboard</span>
                </a>
                <a href="#" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-slate-400 hover:text-slate-200 hover:bg-slate-800 text-sm font-medium transition-all">
                    <svg class="w-4 h-4 flex-shrink-0 sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    <span class="sidebar-label text-sm">Tickets</span>
                </a>
                <a href="#" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-slate-400 hover:text-slate-200 hover:bg-slate-800 text-sm font-medium transition-all">
                    <svg class="w-4 h-4 flex-shrink-0 sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                    <span class="sidebar-label text-sm">POS</span>
                </a>
                <a href="#" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-slate-400 hover:text-slate-200 hover:bg-slate-800 text-sm font-medium transition-all">
                    <svg class="w-4 h-4 flex-shrink-0 sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <span class="sidebar-label text-sm">Inventory</span>
                </a>
                <a href="#" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-slate-400 hover:text-slate-200 hover:bg-slate-800 text-sm font-medium transition-all">
                    <svg class="w-4 h-4 flex-shrink-0 sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span class="sidebar-label text-sm">Customers</span>
                </a>
                <div class="border-t border-slate-800 my-2"></div>
                <a href="#" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-slate-500 hover:text-slate-300 hover:bg-slate-800 text-sm font-medium transition-all">
                    <svg class="w-4 h-4 flex-shrink-0 sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h2a2 2 0 01-2-2z"/></svg>
                    <span class="sidebar-label text-sm">Reports</span>
                </a>
                <a href="#" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-slate-500 hover:text-slate-300 hover:bg-slate-800 text-sm font-medium transition-all">
                    <svg class="w-4 h-4 flex-shrink-0 sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924-1.756-3.35a1.724 1.724 0 001.066-2.573c.94 1.543-.826 3.31-2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="sidebar-label text-sm">Settings</span>
                </a>
            </nav>

            <!-- Sidebar Footer: User -->
            <div class="px-2 py-2 border-t border-slate-800 flex-shrink-0">
                <template x-if="sidebarOpen">
                    <div class="flex items-center gap-2 px-2.5 py-2 rounded-lg hover:bg-slate-800 transition-all cursor-pointer">
                        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-cyan-500 to-purple-600 flex items-center justify-center text-white text-[9px] font-bold shadow-lg shadow-cyan-500/30 flex-shrink-0">BS</div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold text-slate-200 truncate">Budi Santoso</p>
                            <p class="text-[9px] text-slate-500 truncate">Owner</p>
                        </div>
                        <svg class="w-3 h-3 text-slate-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </template>
                <template x-if="!sidebarOpen">
                    <div class="flex justify-center">
                        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-cyan-500 to-purple-600 flex items-center justify-center text-white text-[9px] font-bold shadow-lg shadow-cyan-500/30 cursor-pointer">BS</div>
                    </div>
                </template>
            </div>
        </aside>

        <!-- ===== MAIN AREA ===== -->
        <div class="flex-1 flex flex-col min-w-0">

            <!-- ===== TOP NAVBAR ===== -->
            <header class="bg-slate-900/90 backdrop-blur-lg border-b border-slate-800 flex-shrink-0">
                <div class="flex items-center h-12 px-3 gap-3">

                    <!-- Mobile Sidebar Toggle -->
                    <button class="lg:hidden p-1.5 rounded-md text-slate-400 hover:text-slate-200 hover:bg-slate-800 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>

                    <!-- Global Search -->
                    <div class="relative flex-1 max-w-xs">
                        <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input
                            type="text"
                            placeholder="Search IMEI, Ticket #, or Phone..."
                            class="w-full pl-8 pr-3 py-1.5 rounded-lg border border-slate-700 bg-slate-900 text-xs text-slate-200 placeholder:text-slate-600 focus:border-purple-500 focus:ring-1 focus:ring-purple-500/30 transition-all"
                        />
                        <span class="absolute right-2 top-1/2 -translate-y-1/2 text-[9px] font-medium text-slate-600 bg-slate-800 px-1 rounded border border-slate-700 hidden sm:inline">⌘K</span>
                    </div>

                    <!-- Spacer -->
                    <div class="flex-1"></div>

                    <!-- Tenant Switcher -->
                    <div class="hidden sm:flex items-center gap-1.5 px-2 py-1 rounded-lg border border-slate-700 bg-slate-900 text-xs">
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span class="text-xs font-medium text-slate-300">Cabang Utama</span>
                        <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>

                    <!-- Quick Add -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="p-1.5 rounded-lg bg-gradient-to-r from-cyan-500 to-purple-600 text-white hover:from-cyan-600 hover:to-purple-700 transition-all shadow-lg shadow-cyan-500/30 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        </button>
                        <div x-show="open" @click.outside="open = false" class="absolute right-0 top-full mt-1 w-44 bg-slate-900 border border-slate-700 rounded-xl shadow-lg py-1 z-50 animate-scale-in text-sm">
                            <a href="#" class="flex items-center gap-2.5 px-3.5 py-2 text-slate-300 hover:text-white hover:bg-slate-800 font-medium"><svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>New Ticket</a>
                            <a href="#" class="flex items-center gap-2.5 px-3.5 py-2 text-slate-300 hover:text-white hover:bg-slate-800 font-medium"><svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>New Sale</a>
                            <a href="#" class="flex items-center gap-2.5 px-3.5 py-2 text-slate-300 hover:text-white hover:bg-slate-800 font-medium"><svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>New Customer</a>
                            <div class="border-t border-slate-800 my-1"></div>
                            <a href="#" class="flex items-center gap-2.5 px-3.5 py-2 text-slate-300 hover:text-white hover:bg-slate-800 font-medium"><svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>New Product</a>
                        </div>
                    </div>

                    <!-- Notification -->
                    <button class="relative p-1.5 rounded-md text-slate-400 hover:text-slate-200 hover:bg-slate-800 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span class="absolute -top-0.5 -right-0.5 w-3.5 h-3.5 bg-rose-500 text-white text-[7px] font-bold rounded-full flex items-center justify-center ring-2 ring-slate-900">3</span>
                    </button>

                    <!-- User Avatar -->
                    <div class="flex items-center gap-2 pl-0.5 cursor-pointer">
                        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-cyan-500 to-purple-600 flex items-center justify-center text-white text-[10px] font-bold shadow-lg shadow-cyan-500/30">BS</div>
                        <div class="hidden sm:block">
                            <p class="text-xs font-semibold text-slate-200 leading-tight">Budi Santoso</p>
                            <p class="text-[9px] text-slate-500 leading-tight">Owner</p>
                        </div>
                        <svg class="w-3 h-3 text-slate-500 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </header>

            <!-- ===== BREADCRUMB (optional) ===== -->
            @hasSection('breadcrumb')
            <div class="bg-slate-900/50 border-b border-slate-800 px-3 py-1.5 flex-shrink-0">
                <div class="flex items-center gap-1.5 text-[11px] text-slate-500">
                    @yield('breadcrumb')
                </div>
            </div>
            @endif

            <!-- ===== MAIN CONTENT (scrollable) ===== -->
            <main class="flex-1 overflow-y-auto w-full max-w-full px-2 py-2 bg-slate-950">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Alpine.js for interactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('scripts')
</body>
</html>