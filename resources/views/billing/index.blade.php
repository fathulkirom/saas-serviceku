@extends('layouts.app')

@section('title', 'Subscription & Billing')

@section('breadcrumb')
    <a href="#" class="hover:text-slate-600 transition-colors">Settings</a>
    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-slate-500">Subscription & Billing</span>
@endsection

@section('content')
<div class="space-y-3">

    <!-- ===== CURRENT PLAN CARD ===== -->
    <div class="bg-white rounded-lg border border-slate-100 shadow-soft overflow-hidden">
        <div class="p-4 sm:p-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <!-- Plan Badge -->
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center text-white shadow-premium flex-shrink-0">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5 mb-1">
                            <h2 class="text-lg font-bold text-slate-900">Pro Plan</h2>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-600 text-[10px] font-semibold border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse-soft"></span>
                                Active
                            </span>
                        </div>
                        <div class="flex items-center gap-4 text-xs text-slate-500">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span class="font-medium text-slate-600">Next billing:</span> 20 Aug 2026
                            </span>
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span class="font-medium text-slate-600">Amount:</span> Rp 199,000<span class="text-slate-300">/mo</span>
                            </span>
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span class="font-medium text-slate-600">Subscribed since:</span> 20 Jan 2026
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-500 text-white text-xs font-bold hover:from-indigo-700 hover:to-indigo-600 hover:shadow-premium hover:-translate-y-0.5 active:translate-y-0 transition-all shadow-soft">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                        Upgrade Plan
                    </button>
                    <button class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-slate-200 text-slate-500 text-xs font-medium hover:border-rose-200 hover:text-rose-600 hover:bg-rose-50/30 transition-all">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
        <!-- Plan features mini bar -->
        <div class="bg-slate-50/80 border-t border-slate-100 px-4 sm:px-5 py-2.5 flex flex-wrap items-center gap-x-6 gap-y-1">
            <span class="text-xs text-slate-500 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Multi-Cabang
            </span>
            <span class="text-xs text-slate-500 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                10 User
            </span>
            <span class="text-xs text-slate-500 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Manajemen Stok Komponen
            </span>
            <span class="text-xs text-slate-500 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Laporan Keuangan Lengkap
            </span>
        </div>
    </div>

    <!-- ===== USAGE LIMITS + PAYMENT HISTORY 2-COL ===== -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-3">

        <!-- LEFT: Usage Limits (1/4) -->
        <div class="lg:col-span-1 bg-white rounded-lg border border-slate-100 shadow-soft p-4">
            <h3 class="text-xs font-bold text-slate-900 mb-3 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Usage Limits
            </h3>
            <div class="space-y-3.5">
                <!-- Staff Accounts -->
                <div>
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span class="text-slate-600">Staff Accounts</span>
                        <span class="font-semibold text-slate-800">4 <span class="text-slate-400 font-normal">/ 5</span></span>
                    </div>
                    <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-indigo-500 rounded-full" style="width: 80%"></div>
                    </div>
                </div>
                <!-- Storage -->
                <div>
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span class="text-slate-600">Storage</span>
                        <span class="font-semibold text-slate-800">1.2 GB <span class="text-slate-400 font-normal">/ 5 GB</span></span>
                    </div>
                    <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-emerald-500 rounded-full" style="width: 24%"></div>
                    </div>
                </div>
                <!-- Branches -->
                <div>
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span class="text-slate-600">Branches</span>
                        <span class="font-semibold text-slate-800">2 <span class="text-slate-400 font-normal">/ 5</span></span>
                    </div>
                    <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-amber-500 rounded-full" style="width: 40%"></div>
                    </div>
                </div>
                <!-- API Calls (monthly) -->
                <div>
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span class="text-slate-600">API Calls (mo)</span>
                        <span class="font-semibold text-slate-800">847 <span class="text-slate-400 font-normal">/ 5,000</span></span>
                    </div>
                    <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-violet-500 rounded-full" style="width: 17%"></div>
                    </div>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100">
                <a href="#" class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-700 transition-colors">
                    Upgrade to increase limits
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>
        </div>

        <!-- RIGHT: Payment History (3/4) -->
        <div class="lg:col-span-3 bg-white rounded-lg border border-slate-100 shadow-soft overflow-hidden">
            <div class="px-4 py-2.5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Payment History
                </h3>
                <span class="text-[10px] text-slate-400">Last 6 invoices</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="sticky top-0 z-10 bg-slate-50 px-3 py-1.5 text-left text-[10px] font-semibold text-slate-500 uppercase tracking-wider w-28">Invoice ID</th>
                            <th class="sticky top-0 z-10 bg-slate-50 px-3 py-1.5 text-left text-[10px] font-semibold text-slate-500 uppercase tracking-wider w-24">Date</th>
                            <th class="sticky top-0 z-10 bg-slate-50 px-3 py-1.5 text-left text-[10px] font-semibold text-slate-500 uppercase tracking-wider w-24">Amount</th>
                            <th class="sticky top-0 z-10 bg-slate-50 px-3 py-1.5 text-left text-[10px] font-semibold text-slate-500 uppercase tracking-wider w-20">Plan</th>
                            <th class="sticky top-0 z-10 bg-slate-50 px-3 py-1.5 text-left text-[10px] font-semibold text-slate-500 uppercase tracking-wider w-20">Status</th>
                            <th class="sticky top-0 z-10 bg-slate-50 px-3 py-1.5 text-center text-[10px] font-semibold text-slate-500 uppercase tracking-wider w-16">PDF</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr class="hover:bg-indigo-50/20 transition-colors even:bg-slate-50/50">
                            <td class="px-3 py-1.5">
                                <span class="font-mono text-xs font-bold text-indigo-600">INV-2026-0042</span>
                            </td>
                            <td class="px-3 py-1.5">
                                <span class="text-xs text-slate-600">20 Jul 2026</span>
                            </td>
                            <td class="px-3 py-1.5">
                                <span class="text-xs font-semibold text-slate-800">Rp 199,000</span>
                            </td>
                            <td class="px-3 py-1.5">
                                <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-medium bg-indigo-50 text-indigo-600 border border-indigo-200">Pro</span>
                            </td>
                            <td class="px-3 py-1.5">
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-emerald-50 text-emerald-600 text-[10px] font-semibold border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Paid
                                </span>
                            </td>
                            <td class="px-3 py-1.5 text-center">
                                <button class="p-1 rounded-md text-slate-400 hover:text-indigo-600 hover:bg-white transition-all" title="Download PDF">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-indigo-50/20 transition-colors even:bg-slate-50/50">
                            <td class="px-3 py-1.5">
                                <span class="font-mono text-xs font-bold text-indigo-600">INV-2026-0041</span>
                            </td>
                            <td class="px-3 py-1.5">
                                <span class="text-xs text-slate-600">20 Jun 2026</span>
                            </td>
                            <td class="px-3 py-1.5">
                                <span class="text-xs font-semibold text-slate-800">Rp 199,000</span>
                            </td>
                            <td class="px-3 py-1.5">
                                <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-medium bg-indigo-50 text-indigo-600 border border-indigo-200">Pro</span>
                            </td>
                            <td class="px-3 py-1.5">
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-emerald-50 text-emerald-600 text-[10px] font-semibold border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Paid
                                </span>
                            </td>
                            <td class="px-3 py-1.5 text-center">
                                <button class="p-1 rounded-md text-slate-400 hover:text-indigo-600 hover:bg-white transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></button>
                            </td>
                        </tr>
                        <tr class="hover:bg-indigo-50/20 transition-colors even:bg-slate-50/50">
                            <td class="px-3 py-1.5">
                                <span class="font-mono text-xs font-bold text-indigo-600">INV-2026-0040</span>
                            </td>
                            <td class="px-3 py-1.5">
                                <span class="text-xs text-slate-600">20 May 2026</span>
                            </td>
                            <td class="px-3 py-1.5">
                                <span class="text-xs font-semibold text-slate-800">Rp 99,000</span>
                            </td>
                            <td class="px-3 py-1.5">
                                <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-500 border border-slate-200">Basic</span>
                            </td>
                            <td class="px-3 py-1.5">
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-emerald-50 text-emerald-600 text-[10px] font-semibold border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Paid
                                </span>
                            </td>
                            <td class="px-3 py-1.5 text-center">
                                <button class="p-1 rounded-md text-slate-400 hover:text-indigo-600 hover:bg-white transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></button>
                            </td>
                        </tr>
                        <tr class="hover:bg-indigo-50/20 transition-colors even:bg-slate-50/50">
                            <td class="px-3 py-1.5">
                                <span class="font-mono text-xs font-bold text-indigo-600">INV-2026-0039</span>
                            </td>
                            <td class="px-3 py-1.5">
                                <span class="text-xs text-slate-600">20 Apr 2026</span>
                            </td>
                            <td class="px-3 py-1.5">
                                <span class="text-xs font-semibold text-slate-800">Rp 99,000</span>
                            </td>
                            <td class="px-3 py-1.5">
                                <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-500 border border-slate-200">Basic</span>
                            </td>
                            <td class="px-3 py-1.5">
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-emerald-50 text-emerald-600 text-[10px] font-semibold border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Paid
                                </span>
                            </td>
                            <td class="px-3 py-1.5 text-center">
                                <button class="p-1 rounded-md text-slate-400 hover:text-indigo-600 hover:bg-white transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></button>
                            </td>
                        </tr>
                        <tr class="hover:bg-indigo-50/20 transition-colors even:bg-slate-50/50">
                            <td class="px-3 py-1.5">
                                <span class="font-mono text-xs font-bold text-indigo-600">INV-2026-0038</span>
                            </td>
                            <td class="px-3 py-1.5">
                                <span class="text-xs text-slate-600">20 Mar 2026</span>
                            </td>
                            <td class="px-3 py-1.5">
                                <span class="text-xs font-semibold text-slate-800">Rp 99,000</span>
                            </td>
                            <td class="px-3 py-1.5">
                                <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-500 border border-slate-200">Basic</span>
                            </td>
                            <td class="px-3 py-1.5">
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-amber-50 text-amber-600 text-[10px] font-semibold border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                    Pending
                                </span>
                            </td>
                            <td class="px-3 py-1.5 text-center">
                                <button class="p-1 rounded-md text-slate-400 hover:text-indigo-600 hover:bg-white transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></button>
                            </td>
                        </tr>
                        <tr class="hover:bg-indigo-50/20 transition-colors even:bg-slate-50/50">
                            <td class="px-3 py-1.5">
                                <span class="font-mono text-xs font-bold text-indigo-600">INV-2026-0037</span>
                            </td>
                            <td class="px-3 py-1.5">
                                <span class="text-xs text-slate-600">20 Feb 2026</span>
                            </td>
                            <td class="px-3 py-1.5">
                                <span class="text-xs font-semibold text-slate-800">Rp 0</span>
                            </td>
                            <td class="px-3 py-1.5">
                                <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-medium bg-emerald-50 text-emerald-600 border border-emerald-200">Trial</span>
                            </td>
                            <td class="px-3 py-1.5">
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-slate-100 text-slate-400 text-[10px] font-semibold border border-slate-200">—</span>
                            </td>
                            <td class="px-3 py-1.5 text-center">
                                <button class="p-1 rounded-md text-slate-400 hover:text-indigo-600 hover:bg-white transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Footer -->
            <div class="px-3 py-1.5 border-t border-slate-100 flex items-center justify-between">
                <p class="text-[10px] text-slate-400">Showing 6 of 6 invoices</p>
                <div class="flex items-center gap-1">
                    <button class="px-2 py-1 rounded-md text-[10px] font-medium bg-indigo-600 text-white border border-indigo-600">1</button>
                    <button class="px-2 py-1 rounded-md text-[10px] font-medium text-slate-500 hover:bg-slate-50 border border-slate-200">All</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection