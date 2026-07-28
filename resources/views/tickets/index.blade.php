@extends('layouts.app')

@section('title', 'Repair Tickets')

@section('breadcrumb')
    <a href="#" class="hover:text-slate-600 transition-colors">Tickets</a>
    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-slate-500">All Tickets</span>
@endsection

@section('content')
<!-- ===== TOOLBAR: Live Search + Filters ===== -->
<div class="bg-white rounded-lg border border-slate-100 shadow-soft mb-2">
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 px-3 py-2">
        <!-- Live Search -->
        <div class="relative flex-1 w-full">
            <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" placeholder="Search by ticket #, customer, device, IMEI..." class="w-full pl-8 pr-3 py-1.5 rounded-lg border border-slate-200 bg-slate-50 text-xs text-slate-700 placeholder:text-slate-400 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 transition-all">
        </div>
        <!-- Filters -->
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <select class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white text-xs text-slate-600 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 transition-all">
                <option>All Status</option>
                <option>Diagnosing</option>
                <option>Waiting Parts</option>
                <option>Repairing</option>
                <option>Ready Pickup</option>
                <option>Completed</option>
                <option>Cancelled</option>
            </select>
            <select class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white text-xs text-slate-600 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 transition-all">
                <option>All Techs</option>
                <option>A. Dwi</option>
                <option>F. Nur</option>
                <option>B. Santoso</option>
            </select>
            <input type="date" value="2026-07-19" class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white text-xs text-slate-600 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 transition-all">
            <button class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 transition-all shadow-soft flex-shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                New Ticket
            </button>
        </div>
    </div>
</div>

<!-- ===== HIGH-DENSITY DATA TABLE ===== -->
<div class="bg-white rounded-lg border border-slate-100 shadow-soft overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="sticky top-0 z-10 bg-slate-50 px-3 py-2 text-left text-[10px] font-semibold text-slate-500 uppercase tracking-wider w-20">Ticket #</th>
                    <th class="sticky top-0 z-10 bg-slate-50 px-3 py-2 text-left text-[10px] font-semibold text-slate-500 uppercase tracking-wider w-24">Date In</th>
                    <th class="sticky top-0 z-10 bg-slate-50 px-3 py-2 text-left text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Customer</th>
                    <th class="sticky top-0 z-10 bg-slate-50 px-3 py-2 text-left text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Device &amp; Issue</th>
                    <th class="sticky top-0 z-10 bg-slate-50 px-3 py-2 text-left text-[10px] font-semibold text-slate-500 uppercase tracking-wider w-36">Status</th>
                    <th class="sticky top-0 z-10 bg-slate-50 px-3 py-2 text-left text-[10px] font-semibold text-slate-500 uppercase tracking-wider w-28">Assigned To</th>
                    <th class="sticky top-0 z-10 bg-slate-50 px-3 py-2 text-center text-[10px] font-semibold text-slate-500 uppercase tracking-wider w-24">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                <!-- Row 1 -->
                <tr class="hover:bg-indigo-50/20 transition-colors even:bg-slate-50/50">
                    <td class="px-3 py-1.5">
                        <span class="font-mono text-xs font-bold text-indigo-600">#TKT-1024</span>
                    </td>
                    <td class="px-3 py-1.5">
                        <span class="text-xs text-slate-600 whitespace-nowrap">19 Jul 2026</span>
                        <span class="text-[10px] text-slate-400 block leading-none">09:30</span>
                    </td>
                    <td class="px-3 py-1.5">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-md bg-gradient-to-br from-sky-100 to-sky-50 flex items-center justify-center text-[8px] font-bold text-sky-600 flex-shrink-0">RN</div>
                            <div>
                                <p class="text-xs font-semibold text-slate-800 leading-tight">Rina N.</p>
                                <p class="text-[10px] text-slate-400 leading-tight">0821-1234-5678</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-3 py-1.5">
                        <p class="text-xs font-medium text-slate-800">MacBook Air M4</p>
                        <p class="text-[10px] text-slate-500 leading-tight truncate max-w-[220px]">Dead / NAND Flash replacement — not powering on after liquid spill</p>
                    </td>
                    <td class="px-3 py-1.5">
                        <select class="w-full px-2 py-1 rounded-md border border-slate-200 bg-white text-[11px] font-medium text-slate-700 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 cursor-pointer transition-all">
                            <option value="diagnosing" class="text-amber-600" selected>🔍 Diagnosing</option>
                            <option value="waiting_parts" class="text-violet-600">⏳ Waiting Parts</option>
                            <option value="repairing" class="text-blue-600">🔧 Repairing</option>
                            <option value="ready_pickup" class="text-emerald-600">✅ Ready Pickup</option>
                            <option value="completed" class="text-slate-500">✔ Completed</option>
                            <option value="cancelled" class="text-rose-500">✖ Cancelled</option>
                        </select>
                    </td>
                    <td class="px-3 py-1.5">
                        <div class="flex items-center gap-1.5">
                            <div class="w-5 h-5 rounded-md bg-gradient-to-br from-indigo-400 to-indigo-500 flex items-center justify-center text-white text-[7px] font-bold flex-shrink-0">AD</div>
                            <span class="text-xs text-slate-600">A. Dwi</span>
                        </div>
                    </td>
                    <td class="px-3 py-1.5">
                        <div class="flex items-center justify-center gap-0.5">
                            <button class="p-1 rounded-md text-slate-400 hover:text-indigo-600 hover:bg-white transition-all" title="View">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                            <button class="p-1 rounded-md text-slate-400 hover:text-emerald-600 hover:bg-white transition-all" title="Message">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            </button>
                            <button class="p-1 rounded-md text-slate-400 hover:text-amber-600 hover:bg-white transition-all" title="Print">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                <!-- Row 2 -->
                <tr class="hover:bg-indigo-50/20 transition-colors even:bg-slate-50/50">
                    <td class="px-3 py-1.5">
                        <span class="font-mono text-xs font-bold text-indigo-600">#TKT-1023</span>
                    </td>
                    <td class="px-3 py-1.5">
                        <span class="text-xs text-slate-600 whitespace-nowrap">18 Jul 2026</span>
                        <span class="text-[10px] text-slate-400 block leading-none">14:15</span>
                    </td>
                    <td class="px-3 py-1.5">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-md bg-gradient-to-br from-amber-100 to-yellow-50 flex items-center justify-center text-[8px] font-bold text-amber-600 flex-shrink-0">DP</div>
                            <div>
                                <p class="text-xs font-semibold text-slate-800 leading-tight">Doni P.</p>
                                <p class="text-[10px] text-slate-400 leading-tight">0812-9876-5432</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-3 py-1.5">
                        <p class="text-xs font-medium text-slate-800">MacBook Air M4</p>
                        <p class="text-[10px] text-slate-500 leading-tight truncate max-w-[220px]">NAND Flash replacement — in-circuit programming in progress</p>
                    </td>
                    <td class="px-3 py-1.5">
                        <select class="w-full px-2 py-1 rounded-md border border-slate-200 bg-white text-[11px] font-medium text-slate-700 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 cursor-pointer transition-all">
                            <option value="diagnosing" class="text-amber-600">🔍 Diagnosing</option>
                            <option value="waiting_parts" class="text-violet-600">⏳ Waiting Parts</option>
                            <option value="repairing" class="text-blue-600" selected>🔧 Repairing</option>
                            <option value="ready_pickup" class="text-emerald-600">✅ Ready Pickup</option>
                            <option value="completed" class="text-slate-500">✔ Completed</option>
                            <option value="cancelled" class="text-rose-500">✖ Cancelled</option>
                        </select>
                    </td>
                    <td class="px-3 py-1.5">
                        <div class="flex items-center gap-1.5">
                            <div class="w-5 h-5 rounded-md bg-gradient-to-br from-cyan-400 to-cyan-500 flex items-center justify-center text-white text-[7px] font-bold flex-shrink-0">FN</div>
                            <span class="text-xs text-slate-600">F. Nur</span>
                        </div>
                    </td>
                    <td class="px-3 py-1.5">
                        <div class="flex items-center justify-center gap-0.5">
                            <button class="p-1 rounded-md text-slate-400 hover:text-indigo-600 hover:bg-white transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
                            <button class="p-1 rounded-md text-slate-400 hover:text-emerald-600 hover:bg-white transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg></button>
                            <button class="p-1 rounded-md text-slate-400 hover:text-amber-600 hover:bg-white transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg></button>
                        </div>
                    </td>
                </tr>
                <!-- Row 3 -->
                <tr class="hover:bg-indigo-50/20 transition-colors even:bg-slate-50/50">
                    <td class="px-3 py-1.5">
                        <span class="font-mono text-xs font-bold text-indigo-600">#TKT-1022</span>
                    </td>
                    <td class="px-3 py-1.5">
                        <span class="text-xs text-slate-600 whitespace-nowrap">18 Jul 2026</span>
                        <span class="text-[10px] text-slate-400 block leading-none">10:00</span>
                    </td>
                    <td class="px-3 py-1.5">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-md bg-gradient-to-br from-violet-100 to-purple-50 flex items-center justify-center text-[8px] font-bold text-violet-600 flex-shrink-0">DH</div>
                            <div>
                                <p class="text-xs font-semibold text-slate-800 leading-tight">Dimas H.</p>
                                <p class="text-[10px] text-slate-400 leading-tight">0855-4321-8765</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-3 py-1.5">
                        <p class="text-xs font-medium text-slate-800">iPhone 16 Pro Max</p>
                        <p class="text-[10px] text-slate-500 leading-tight truncate max-w-[220px]">Overheating & battery drain — component-level diagnostic</p>
                    </td>
                    <td class="px-3 py-1.5">
                        <select class="w-full px-2 py-1 rounded-md border border-slate-200 bg-white text-[11px] font-medium text-slate-700 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 cursor-pointer transition-all">
                            <option value="diagnosing" class="text-amber-600" selected>🔍 Diagnosing</option>
                            <option value="waiting_parts" class="text-violet-600">⏳ Waiting Parts</option>
                            <option value="repairing" class="text-blue-600">🔧 Repairing</option>
                            <option value="ready_pickup" class="text-emerald-600">✅ Ready Pickup</option>
                            <option value="completed" class="text-slate-500">✔ Completed</option>
                            <option value="cancelled" class="text-rose-500">✖ Cancelled</option>
                        </select>
                    </td>
                    <td class="px-3 py-1.5">
                        <div class="flex items-center gap-1.5">
                            <div class="w-5 h-5 rounded-md bg-gradient-to-br from-indigo-400 to-indigo-500 flex items-center justify-center text-white text-[7px] font-bold flex-shrink-0">AD</div>
                            <span class="text-xs text-slate-600">A. Dwi</span>
                        </div>
                    </td>
                    <td class="px-3 py-1.5">
                        <div class="flex items-center justify-center gap-0.5">
                            <button class="p-1 rounded-md text-slate-400 hover:text-indigo-600 hover:bg-white transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
                            <button class="p-1 rounded-md text-slate-400 hover:text-emerald-600 hover:bg-white transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg></button>
                            <button class="p-1 rounded-md text-slate-400 hover:text-amber-600 hover:bg-white transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg></button>
                        </div>
                    </td>
                </tr>
                <!-- Row 4 -->
                <tr class="hover:bg-indigo-50/20 transition-colors even:bg-slate-50/50">
                    <td class="px-3 py-1.5">
                        <span class="font-mono text-xs font-bold text-indigo-600">#TKT-1021</span>
                    </td>
                    <td class="px-3 py-1.5">
                        <span class="text-xs text-slate-600 whitespace-nowrap">17 Jul 2026</span>
                        <span class="text-[10px] text-slate-400 block leading-none">16:45</span>
                    </td>
                    <td class="px-3 py-1.5">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-md bg-gradient-to-br from-emerald-100 to-teal-50 flex items-center justify-center text-[8px] font-bold text-emerald-600 flex-shrink-0">FW</div>
                            <div>
                                <p class="text-xs font-semibold text-slate-800 leading-tight">Fitri W.</p>
                                <p class="text-[10px] text-slate-400 leading-tight">0878-1111-2222</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-3 py-1.5">
                        <p class="text-xs font-medium text-slate-800">iPhone 15 Pro</p>
                        <p class="text-[10px] text-slate-500 leading-tight truncate max-w-[220px]">Rear camera module replacement — waiting for OEM part</p>
                    </td>
                    <td class="px-3 py-1.5">
                        <select class="w-full px-2 py-1 rounded-md border border-slate-200 bg-white text-[11px] font-medium text-slate-700 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 cursor-pointer transition-all">
                            <option value="diagnosing" class="text-amber-600">🔍 Diagnosing</option>
                            <option value="waiting_parts" class="text-violet-600" selected>⏳ Waiting Parts</option>
                            <option value="repairing" class="text-blue-600">🔧 Repairing</option>
                            <option value="ready_pickup" class="text-emerald-600">✅ Ready Pickup</option>
                            <option value="completed" class="text-slate-500">✔ Completed</option>
                            <option value="cancelled" class="text-rose-500">✖ Cancelled</option>
                        </select>
                    </td>
                    <td class="px-3 py-1.5">
                        <div class="flex items-center gap-1.5">
                            <div class="w-5 h-5 rounded-md bg-gradient-to-br from-cyan-400 to-cyan-500 flex items-center justify-center text-white text-[7px] font-bold flex-shrink-0">FN</div>
                            <span class="text-xs text-slate-600">F. Nur</span>
                        </div>
                    </td>
                    <td class="px-3 py-1.5">
                        <div class="flex items-center justify-center gap-0.5">
                            <button class="p-1 rounded-md text-slate-400 hover:text-indigo-600 hover:bg-white transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
                            <button class="p-1 rounded-md text-slate-400 hover:text-emerald-600 hover:bg-white transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg></button>
                            <button class="p-1 rounded-md text-slate-400 hover:text-amber-600 hover:bg-white transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg></button>
                        </div>
                    </td>
                </tr>
                <!-- Row 5 -->
                <tr class="hover:bg-indigo-50/20 transition-colors even:bg-slate-50/50">
                    <td class="px-3 py-1.5">
                        <span class="font-mono text-xs font-bold text-indigo-600">#TKT-1020</span>
                    </td>
                    <td class="px-3 py-1.5">
                        <span class="text-xs text-slate-600 whitespace-nowrap">17 Jul 2026</span>
                        <span class="text-[10px] text-slate-400 block leading-none">11:20</span>
                    </td>
                    <td class="px-3 py-1.5">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-md bg-gradient-to-br from-rose-100 to-pink-50 flex items-center justify-center text-[8px] font-bold text-rose-600 flex-shrink-0">SA</div>
                            <div>
                                <p class="text-xs font-semibold text-slate-800 leading-tight">Sari A.</p>
                                <p class="text-[10px] text-slate-400 leading-tight">0899-5555-7777</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-3 py-1.5">
                        <p class="text-xs font-medium text-slate-800">Samsung Galaxy S25 Ultra</p>
                        <p class="text-[10px] text-slate-500 leading-tight truncate max-w-[220px]">Intermittent boot loop — component-level diagnostic required</p>
                    </td>
                    <td class="px-3 py-1.5">
                        <select class="w-full px-2 py-1 rounded-md border border-slate-200 bg-white text-[11px] font-medium text-slate-700 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 cursor-pointer transition-all">
                            <option value="diagnosing" class="text-amber-600" selected>🔍 Diagnosing</option>
                            <option value="waiting_parts" class="text-violet-600">⏳ Waiting Parts</option>
                            <option value="repairing" class="text-blue-600">🔧 Repairing</option>
                            <option value="ready_pickup" class="text-emerald-600">✅ Ready Pickup</option>
                            <option value="completed" class="text-slate-500">✔ Completed</option>
                            <option value="cancelled" class="text-rose-500">✖ Cancelled</option>
                        </select>
                    </td>
                    <td class="px-3 py-1.5">
                        <span class="text-xs text-slate-400 italic">Unassigned</span>
                    </td>
                    <td class="px-3 py-1.5">
                        <div class="flex items-center justify-center gap-0.5">
                            <button class="p-1 rounded-md text-slate-400 hover:text-indigo-600 hover:bg-white transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
                            <button class="p-1 rounded-md text-slate-400 hover:text-emerald-600 hover:bg-white transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg></button>
                            <button class="p-1 rounded-md text-slate-400 hover:text-amber-600 hover:bg-white transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg></button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <!-- Footer -->
    <div class="px-3 py-2 border-t border-slate-100 flex items-center justify-between">
        <p class="text-[10px] text-slate-400">Showing 1 to 5 of 24 tickets</p>
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
@endsection