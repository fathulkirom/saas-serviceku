@extends('layouts.app')

@section('title', 'Dashboard')

@section('breadcrumb')
    <a href="#" class="hover:text-slate-600 transition-colors">Dashboard</a>
    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-slate-500">Overview</span>
@endsection

@section('content')
<!-- ===== 5 METRIC CARDS (compact) ===== -->
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2 mb-3">
    <div class="bg-white rounded-lg border border-slate-100 p-3 shadow-soft flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center text-white shadow-sm flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
        </div>
        <div class="min-w-0">
            <p class="text-[10px] font-semibold text-slate-400 uppercase truncate">To Repair Today</p>
            <p class="text-lg font-bold text-slate-900 leading-none mt-0.5">12</p>
        </div>
    </div>
    <div class="bg-white rounded-lg border border-slate-100 p-3 shadow-soft flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white shadow-sm flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="min-w-0">
            <p class="text-[10px] font-semibold text-slate-400 uppercase truncate">Waiting for Parts</p>
            <p class="text-lg font-bold text-slate-900 leading-none mt-0.5">4</p>
        </div>
    </div>
    <div class="bg-white rounded-lg border border-slate-100 p-3 shadow-soft flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center text-white shadow-sm flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="min-w-0">
            <p class="text-[10px] font-semibold text-slate-400 uppercase truncate">Ready to Pickup</p>
            <p class="text-lg font-bold text-slate-900 leading-none mt-0.5">7</p>
        </div>
    </div>
    <div class="bg-white rounded-lg border border-slate-100 p-3 shadow-soft flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-sky-500 to-cyan-600 flex items-center justify-center text-white shadow-sm flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="min-w-0">
            <p class="text-[10px] font-semibold text-slate-400 uppercase truncate">Today's Revenue</p>
            <p class="text-lg font-bold text-slate-900 leading-none mt-0.5">Rp 2,450,000</p>
        </div>
    </div>
    <div class="bg-white rounded-lg border border-slate-100 p-3 shadow-soft flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-rose-500 to-red-600 flex items-center justify-center text-white shadow-sm flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        </div>
        <div class="min-w-0">
            <p class="text-[10px] font-semibold text-slate-400 uppercase truncate">Low Stock Items</p>
            <p class="text-lg font-bold text-rose-600 leading-none mt-0.5">3</p>
        </div>
    </div>
</div>

<!-- ===== 2-COLUMN SPLIT VIEW ===== -->
<div class="grid grid-cols-1 lg:grid-cols-5 gap-2" style="height: calc(100vh - 13rem);">

    <!-- ===== LEFT COLUMN (3/5): Urgent Action ===== -->
    <div class="lg:col-span-3 flex flex-col gap-2 overflow-hidden">

        <!-- Overdue Devices -->
        <div class="bg-white rounded-lg border border-slate-100 shadow-soft flex flex-col overflow-hidden">
            <div class="sticky top-0 z-10 flex items-center justify-between px-3 py-2 bg-slate-50 border-b border-slate-100">
                <h3 class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    Overdue Devices
                </h3>
                <span class="text-[10px] font-semibold text-rose-600 bg-rose-50 px-1.5 py-0.5 rounded border border-rose-200">4 overdue</span>
            </div>
            <div class="overflow-y-auto flex-1 divide-y divide-slate-50">
                <div class="flex items-center gap-2.5 px-3 py-2 hover:bg-rose-50/30 transition-colors">
                    <div class="w-6 h-6 rounded-md bg-gradient-to-br from-rose-100 to-rose-50 flex items-center justify-center text-[8px] font-bold text-rose-600 flex-shrink-0">MB</div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-slate-800 truncate">MacBook Air M4 — NAND Flash replacement</p>
                        <div class="flex items-center gap-1.5 text-[10px] text-slate-400">
                            <span>Rina N.</span>
                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                            <span>+7 days overdue</span>
                        </div>
                    </div>
                    <span class="text-[10px] font-semibold text-rose-600 whitespace-nowrap bg-rose-50/50 px-1.5 py-0.5 rounded">Urgent</span>
                </div>
                <div class="flex items-center gap-2.5 px-3 py-2 hover:bg-rose-50/30 transition-colors">
                    <div class="w-6 h-6 rounded-md bg-gradient-to-br from-rose-100 to-rose-50 flex items-center justify-center text-[8px] font-bold text-rose-600 flex-shrink-0">IP</div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-slate-800 truncate">iPhone 16 Pro — Battery replacement</p>
                        <div class="flex items-center gap-1.5 text-[10px] text-slate-400">
                            <span>Dimas H.</span>
                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                            <span>+5 days overdue</span>
                        </div>
                    </div>
                    <span class="text-[10px] font-semibold text-rose-600 whitespace-nowrap bg-rose-50/50 px-1.5 py-0.5 rounded">Urgent</span>
                </div>
                <div class="flex items-center gap-2.5 px-3 py-2 hover:bg-rose-50/30 transition-colors">
                    <div class="w-6 h-6 rounded-md bg-gradient-to-br from-rose-100 to-rose-50 flex items-center justify-center text-[8px] font-bold text-rose-600 flex-shrink-0">SG</div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-slate-800 truncate">Samsung S25 Ultra — Dead / Boot loop</p>
                        <div class="flex items-center gap-1.5 text-[10px] text-slate-400">
                            <span>Sari A.</span>
                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                            <span>+3 days overdue</span>
                        </div>
                    </div>
                    <span class="text-[10px] font-semibold text-warning-600 whitespace-nowrap bg-amber-50/50 px-1.5 py-0.5 rounded text-amber-600">Waiting</span>
                </div>
            </div>
        </div>

        <!-- New Incoming Devices -->
        <div class="bg-white rounded-lg border border-slate-100 shadow-soft flex flex-col overflow-hidden flex-1">
            <div class="sticky top-0 z-10 flex items-center justify-between px-3 py-2 bg-slate-50 border-b border-slate-100">
                <h3 class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    New Incoming Devices
                </h3>
                <span class="text-[10px] font-semibold text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded border border-indigo-200">Today: 3</span>
            </div>
            <div class="overflow-y-auto flex-1 divide-y divide-slate-50">
                <div class="flex items-center gap-2.5 px-3 py-2 hover:bg-indigo-50/30 transition-colors">
                    <div class="w-6 h-6 rounded-md bg-gradient-to-br from-sky-100 to-sky-50 flex items-center justify-center text-[8px] font-bold text-sky-600 flex-shrink-0">SG</div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-slate-800 truncate">Samsung Galaxy S25 Ultra — Dead / NAND issue</p>
                        <div class="flex items-center gap-1.5 text-[10px] text-slate-400">
                            <span>Customer: Sari A.</span>
                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                            <span>10 min ago</span>
                        </div>
                    </div>
                    <span class="text-[10px] font-medium text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200 whitespace-nowrap">Diagnosing</span>
                </div>
                <div class="flex items-center gap-2.5 px-3 py-2 hover:bg-indigo-50/30 transition-colors">
                    <div class="w-6 h-6 rounded-md bg-gradient-to-br from-violet-100 to-violet-50 flex items-center justify-center text-[8px] font-bold text-violet-600 flex-shrink-0">AL</div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-slate-800 truncate">MacBook Pro 14" M3 Pro — Display assembly</p>
                        <div class="flex items-center gap-1.5 text-[10px] text-slate-400">
                            <span>Customer: Andi L.</span>
                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                            <span>25 min ago</span>
                        </div>
                    </div>
                    <span class="text-[10px] font-medium text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200 whitespace-nowrap">Diagnosing</span>
                </div>
                <div class="flex items-center gap-2.5 px-3 py-2 hover:bg-indigo-50/30 transition-colors">
                    <div class="w-6 h-6 rounded-md bg-gradient-to-br from-emerald-100 to-teal-50 flex items-center justify-center text-[8px] font-bold text-emerald-600 flex-shrink-0">FW</div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-slate-800 truncate">iPhone 15 Pro Max — Rear camera module</p>
                        <div class="flex items-center gap-1.5 text-[10px] text-slate-400">
                            <span>Customer: Fitri W.</span>
                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                            <span>1 hour ago</span>
                        </div>
                    </div>
                    <span class="text-[10px] font-medium text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200 whitespace-nowrap">Diagnosing</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== RIGHT COLUMN (2/5): Ready for Pickup ===== -->
    <div class="lg:col-span-2 bg-white rounded-lg border border-slate-100 shadow-soft flex flex-col overflow-hidden">
        <div class="sticky top-0 z-10 flex items-center justify-between px-3 py-2 bg-slate-50 border-b border-slate-100">
            <h3 class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                Ready for Pickup
            </h3>
            <span class="text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">7 devices</span>
        </div>
        <div class="overflow-y-auto flex-1 divide-y divide-slate-50">
            <div class="flex items-center gap-2.5 px-3 py-2.5 hover:bg-emerald-50/30 transition-colors">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-emerald-100 to-emerald-50 flex items-center justify-center text-[9px] font-bold text-emerald-600 border border-emerald-200/50 flex-shrink-0">RW</div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-slate-800 truncate">Rina Wijaya</p>
                    <p class="text-[10px] text-slate-400 truncate">iPhone 15 Pro Max — Battery replaced</p>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-[10px] font-semibold text-emerald-600">Rp 425,000</span>
                        <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                        <span class="text-[9px] text-slate-400">QC Passed</span>
                    </div>
                </div>
                <button class="flex items-center gap-1 px-2 py-1.5 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 hover:text-emerald-700 border border-emerald-200 transition-all flex-shrink-0 text-[10px] font-semibold" title="Notify via WhatsApp">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    WA
                </button>
            </div>
            <div class="flex items-center gap-2.5 px-3 py-2.5 hover:bg-emerald-50/30 transition-colors">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-emerald-100 to-emerald-50 flex items-center justify-center text-[9px] font-bold text-emerald-600 border border-emerald-200/50 flex-shrink-0">AH</div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-slate-800 truncate">Agus Hermawan</p>
                    <p class="text-[10px] text-slate-400 truncate">MacBook Pro 16" M4 Max — Logic board repair</p>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-[10px] font-semibold text-emerald-600">Rp 1,200,000</span>
                        <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                        <span class="text-[9px] text-slate-400">QC Passed</span>
                    </div>
                </div>
                <button class="flex items-center gap-1 px-2 py-1.5 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 hover:text-emerald-700 border border-emerald-200 transition-all flex-shrink-0 text-[10px] font-semibold" title="Notify via WhatsApp">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    WA
                </button>
            </div>
            <div class="flex items-center gap-2.5 px-3 py-2.5 hover:bg-emerald-50/30 transition-colors">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-emerald-100 to-emerald-50 flex items-center justify-center text-[9px] font-bold text-emerald-600 border border-emerald-200/50 flex-shrink-0">FW</div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-slate-800 truncate">Fitri Wulandari</p>
                    <p class="text-[10px] text-slate-400 truncate">iPhone 15 Pro — Rear camera module replaced</p>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-[10px] font-semibold text-emerald-600">Rp 650,000</span>
                        <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                        <span class="text-[9px] text-slate-400">QC Passed</span>
                    </div>
                </div>
                <button class="flex items-center gap-1 px-2 py-1.5 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 hover:text-emerald-700 border border-emerald-200 transition-all flex-shrink-0 text-[10px] font-semibold" title="Notify via WhatsApp">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    WA
                </button>
            </div>
            <div class="flex items-center gap-2.5 px-3 py-2.5 hover:bg-emerald-50/30 transition-colors">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-emerald-100 to-emerald-50 flex items-center justify-center text-[9px] font-bold text-emerald-600 border border-emerald-200/50 flex-shrink-0">DP</div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-slate-800 truncate">Doni Prasetyo</p>
                    <p class="text-[10px] text-slate-400 truncate">MacBook Air M4 — NAND Flash programming</p>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-[10px] font-semibold text-emerald-600">Rp 875,000</span>
                        <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                        <span class="text-[9px] text-slate-400">QC Passed</span>
                    </div>
                </div>
                <button class="flex items-center gap-1 px-2 py-1.5 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 hover:text-emerald-700 border border-emerald-200 transition-all flex-shrink-0 text-[10px] font-semibold" title="Notify via WhatsApp">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    WA
                </button>
            </div>
        </div>
    </div>
</div>
@endsection