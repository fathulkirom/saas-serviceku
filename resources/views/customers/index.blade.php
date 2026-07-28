@extends('layouts.app')

@section('title', 'Customers')

@section('breadcrumb')
    <a href="#" class="hover:text-slate-600 transition-colors">Customers</a>
    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-slate-500">All Customers</span>
@endsection

@section('content')
<!-- ===== PROMINENT SEARCH BAR ===== -->
<div class="bg-white rounded-lg border border-slate-100 shadow-soft mb-2">
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 px-3 py-2.5">
        <div class="relative flex-1 w-full">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" autofocus placeholder="Search by Name, Phone Number, or IMEI..." class="w-full pl-10 pr-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-sm text-slate-700 placeholder:text-slate-400 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 transition-all">
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto flex-shrink-0">
            <select class="px-2.5 py-2 rounded-lg border border-slate-200 bg-white text-xs text-slate-600 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 transition-all">
                <option>All Time</option>
                <option>Last 7 days</option>
                <option>Last 30 days</option>
                <option>Last 90 days</option>
            </select>
            <button class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 transition-all shadow-soft flex-shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Add Customer
            </button>
        </div>
    </div>
</div>

<!-- ===== CUSTOMER TABLE WITH ACCORDION ===== -->
<div class="bg-white rounded-lg border border-slate-100 shadow-soft overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="sticky top-0 z-10 bg-slate-50 px-2 py-1.5 text-left text-[10px] font-semibold text-slate-500 uppercase tracking-wider w-8"></th>
                    <th class="sticky top-0 z-10 bg-slate-50 px-2 py-1.5 text-left text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Name</th>
                    <th class="sticky top-0 z-10 bg-slate-50 px-2 py-1.5 text-left text-[10px] font-semibold text-slate-500 uppercase tracking-wider w-36">Phone</th>
                    <th class="sticky top-0 z-10 bg-slate-50 px-2 py-1.5 text-center text-[10px] font-semibold text-slate-500 uppercase tracking-wider w-24">Devices Repaired</th>
                    <th class="sticky top-0 z-10 bg-slate-50 px-2 py-1.5 text-right text-[10px] font-semibold text-slate-500 uppercase tracking-wider w-28">Total Spent</th>
                    <th class="sticky top-0 z-10 bg-slate-50 px-2 py-1.5 text-left text-[10px] font-semibold text-slate-500 uppercase tracking-wider w-28">Last Visit</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                <!-- Row 1: Rina Wijaya -->
                <tr x-data="{ open: false }" class="hover:bg-indigo-50/20 transition-colors even:bg-slate-50/50 cursor-pointer">
                    <td class="px-2 py-1.5 text-center" @click="open = !open">
                        <button class="p-0.5 rounded text-slate-400 hover:text-indigo-600 transition-all" x-bind:class="{ 'rotate-180': open }">
                            <svg class="w-3.5 h-3.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </td>
                    <td class="px-2 py-1.5" @click="open = !open">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-sky-100 to-sky-50 flex items-center justify-center text-[9px] font-bold text-sky-600 border border-sky-200/50 flex-shrink-0">RN</div>
                            <div>
                                <p class="text-sm font-semibold text-slate-800">Rina Wijaya</p>
                                <p class="text-[10px] text-slate-400">rina.wijaya@email.com</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-2 py-1.5" @click="open = !open">
                        <span class="text-xs text-slate-600">0821-1234-5678</span>
                    </td>
                    <td class="px-2 py-1.5 text-center" @click="open = !open">
                        <span class="text-xs font-semibold text-slate-800">4</span>
                    </td>
                    <td class="px-2 py-1.5 text-right" @click="open = !open">
                        <span class="text-xs font-bold text-slate-900">Rp 2,450,000</span>
                    </td>
                    <td class="px-2 py-1.5" @click="open = !open">
                        <span class="text-xs text-slate-500">19 Jul 2026</span>
                    </td>
                </tr>
                <!-- Accordion: Rina's repair history -->
                <tr x-data="{ open: false }" x-show="open">
                    <td colspan="6" class="px-0 py-0">
                        <div class="bg-slate-50/80 border-t border-b border-slate-100">
                            <div class="px-6 py-2">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Repair History</span>
                                    <span class="text-[10px] text-slate-300">(4 tickets)</span>
                                </div>
                                <table class="w-full">
                                    <thead>
                                        <tr class="border-b border-slate-200">
                                            <th class="px-2 py-1 text-left text-[9px] font-semibold text-slate-400 uppercase">Ticket</th>
                                            <th class="px-2 py-1 text-left text-[9px] font-semibold text-slate-400 uppercase">Device</th>
                                            <th class="px-2 py-1 text-left text-[9px] font-semibold text-slate-400 uppercase">Issue</th>
                                            <th class="px-2 py-1 text-left text-[9px] font-semibold text-slate-400 uppercase">Status</th>
                                            <th class="px-2 py-1 text-left text-[9px] font-semibold text-slate-400 uppercase">Date</th>
                                            <th class="px-2 py-1 text-right text-[9px] font-semibold text-slate-400 uppercase">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <tr class="hover:bg-white/50 transition-colors">
                                            <td class="px-2 py-1"><span class="font-mono text-[11px] font-bold text-indigo-600">#TKT-1024</span></td>
                                            <td class="px-2 py-1"><span class="text-[11px] text-slate-700">MacBook Air M4</span></td>
                                            <td class="px-2 py-1"><span class="text-[11px] text-slate-500">Dead / NAND Flash replacement</span></td>
                                            <td class="px-2 py-1"><span class="inline-flex px-1.5 py-0.5 rounded text-[9px] font-medium bg-amber-50 text-amber-600 border border-amber-200">Diagnosing</span></td>
                                            <td class="px-2 py-1"><span class="text-[11px] text-slate-400">19 Jul 2026</span></td>
                                            <td class="px-2 py-1 text-right"><span class="text-[11px] font-semibold text-slate-700">Pending</span></td>
                                        </tr>
                                        <tr class="hover:bg-white/50 transition-colors">
                                            <td class="px-2 py-1"><span class="font-mono text-[11px] font-bold text-indigo-600">#TKT-1018</span></td>
                                            <td class="px-2 py-1"><span class="text-[11px] text-slate-700">iPhone 15 Pro Max</span></td>
                                            <td class="px-2 py-1"><span class="text-[11px] text-slate-500">Battery replacement + charging port cleaning</span></td>
                                            <td class="px-2 py-1"><span class="inline-flex px-1.5 py-0.5 rounded text-[9px] font-medium bg-emerald-50 text-emerald-600 border border-emerald-200">Completed</span></td>
                                            <td class="px-2 py-1"><span class="text-[11px] text-slate-400">15 Jul 2026</span></td>
                                            <td class="px-2 py-1 text-right"><span class="text-[11px] font-semibold text-slate-700">Rp 625,000</span></td>
                                        </tr>
                                        <tr class="hover:bg-white/50 transition-colors">
                                            <td class="px-2 py-1"><span class="font-mono text-[11px] font-bold text-indigo-600">#TKT-1005</span></td>
                                            <td class="px-2 py-1"><span class="text-[11px] text-slate-700">iPad Pro 12.9"</span></td>
                                            <td class="px-2 py-1"><span class="text-[11px] text-slate-500">LCD display replacement — cracked screen</span></td>
                                            <td class="px-2 py-1"><span class="inline-flex px-1.5 py-0.5 rounded text-[9px] font-medium bg-emerald-50 text-emerald-600 border border-emerald-200">Completed</span></td>
                                            <td class="px-2 py-1"><span class="text-[11px] text-slate-400">10 Jul 2026</span></td>
                                            <td class="px-2 py-1 text-right"><span class="text-[11px] font-semibold text-slate-700">Rp 1,200,000</span></td>
                                        </tr>
                                        <tr class="hover:bg-white/50 transition-colors">
                                            <td class="px-2 py-1"><span class="font-mono text-[11px] font-bold text-indigo-600">#TKT-0992</span></td>
                                            <td class="px-2 py-1"><span class="text-[11px] text-slate-700">MacBook Pro 14"</span></td>
                                            <td class="px-2 py-1"><span class="text-[11px] text-slate-500">Keyboard + trackpad replacement</span></td>
                                            <td class="px-2 py-1"><span class="inline-flex px-1.5 py-0.5 rounded text-[9px] font-medium bg-emerald-50 text-emerald-600 border border-emerald-200">Completed</span></td>
                                            <td class="px-2 py-1"><span class="text-[11px] text-slate-400">28 Jun 2026</span></td>
                                            <td class="px-2 py-1 text-right"><span class="text-[11px] font-semibold text-slate-700">Rp 625,000</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </td>
                </tr>

                <!-- Row 2: Doni Prasetyo -->
                <tr x-data="{ open: false }" class="hover:bg-indigo-50/20 transition-colors even:bg-slate-50/50 cursor-pointer">
                    <td class="px-2 py-1.5 text-center" @click="open = !open">
                        <button class="p-0.5 rounded text-slate-400 hover:text-indigo-600 transition-all" x-bind:class="{ 'rotate-180': open }">
                            <svg class="w-3.5 h-3.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </td>
                    <td class="px-2 py-1.5" @click="open = !open">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-amber-100 to-yellow-50 flex items-center justify-center text-[9px] font-bold text-amber-600 border border-amber-200/50 flex-shrink-0">DP</div>
                            <div>
                                <p class="text-sm font-semibold text-slate-800">Doni Prasetyo</p>
                                <p class="text-[10px] text-slate-400">doni.prasetyo@email.com</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-2 py-1.5" @click="open = !open">
                        <span class="text-xs text-slate-600">0812-9876-5432</span>
                    </td>
                    <td class="px-2 py-1.5 text-center" @click="open = !open">
                        <span class="text-xs font-semibold text-slate-800">3</span>
                    </td>
                    <td class="px-2 py-1.5 text-right" @click="open = !open">
                        <span class="text-xs font-bold text-slate-900">Rp 1,875,000</span>
                    </td>
                    <td class="px-2 py-1.5" @click="open = !open">
                        <span class="text-xs text-slate-500">18 Jul 2026</span>
                    </td>
                </tr>
                <!-- Accordion: Doni's repair history -->
                <tr x-data="{ open: false }" x-show="open" >
                    <td colspan="6" class="px-0 py-0">
                        <div class="bg-slate-50/80 border-t border-b border-slate-100">
                            <div class="px-6 py-2">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Repair History</span>
                                    <span class="text-[10px] text-slate-300">(3 tickets)</span>
                                </div>
                                <table class="w-full">
                                    <thead>
                                        <tr class="border-b border-slate-200">
                                            <th class="px-2 py-1 text-left text-[9px] font-semibold text-slate-400 uppercase">Ticket</th>
                                            <th class="px-2 py-1 text-left text-[9px] font-semibold text-slate-400 uppercase">Device</th>
                                            <th class="px-2 py-1 text-left text-[9px] font-semibold text-slate-400 uppercase">Issue</th>
                                            <th class="px-2 py-1 text-left text-[9px] font-semibold text-slate-400 uppercase">Status</th>
                                            <th class="px-2 py-1 text-left text-[9px] font-semibold text-slate-400 uppercase">Date</th>
                                            <th class="px-2 py-1 text-right text-[9px] font-semibold text-slate-400 uppercase">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <tr class="hover:bg-white/50 transition-colors">
                                            <td class="px-2 py-1"><span class="font-mono text-[11px] font-bold text-indigo-600">#TKT-1023</span></td>
                                            <td class="px-2 py-1"><span class="text-[11px] text-slate-700">MacBook Air M4</span></td>
                                            <td class="px-2 py-1"><span class="text-[11px] text-slate-500">NAND Flash — in-circuit programming</span></td>
                                            <td class="px-2 py-1"><span class="inline-flex px-1.5 py-0.5 rounded text-[9px] font-medium bg-blue-50 text-blue-600 border border-blue-200">Repairing</span></td>
                                            <td class="px-2 py-1"><span class="text-[11px] text-slate-400">18 Jul 2026</span></td>
                                            <td class="px-2 py-1 text-right"><span class="text-[11px] font-semibold text-slate-700">Pending</span></td>
                                        </tr>
                                        <tr class="hover:bg-white/50 transition-colors">
                                            <td class="px-2 py-1"><span class="font-mono text-[11px] font-bold text-indigo-600">#TKT-1010</span></td>
                                            <td class="px-2 py-1"><span class="text-[11px] text-slate-700">iPhone 14 Pro</span></td>
                                            <td class="px-2 py-1"><span class="text-[11px] text-slate-500">Face ID sensor replacement</span></td>
                                            <td class="px-2 py-1"><span class="inline-flex px-1.5 py-0.5 rounded text-[9px] font-medium bg-emerald-50 text-emerald-600 border border-emerald-200">Completed</span></td>
                                            <td class="px-2 py-1"><span class="text-[11px] text-slate-400">5 Jul 2026</span></td>
                                            <td class="px-2 py-1 text-right"><span class="text-[11px] font-semibold text-slate-700">Rp 850,000</span></td>
                                        </tr>
                                        <tr class="hover:bg-white/50 transition-colors">
                                            <td class="px-2 py-1"><span class="font-mono text-[11px] font-bold text-indigo-600">#TKT-0975</span></td>
                                            <td class="px-2 py-1"><span class="text-[11px] text-slate-700">Samsung Galaxy S24</span></td>
                                            <td class="px-2 py-1"><span class="text-[11px] text-slate-500">USB-C port replacement</span></td>
                                            <td class="px-2 py-1"><span class="inline-flex px-1.5 py-0.5 rounded text-[9px] font-medium bg-emerald-50 text-emerald-600 border border-emerald-200">Completed</span></td>
                                            <td class="px-2 py-1"><span class="text-[11px] text-slate-400">20 Jun 2026</span></td>
                                            <td class="px-2 py-1 text-right"><span class="text-[11px] font-semibold text-slate-700">Rp 400,000</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </td>
                </tr>

                <!-- Row 3: Mega Sari -->
                <tr x-data="{ open: false }" class="hover:bg-indigo-50/20 transition-colors even:bg-slate-50/50 cursor-pointer">
                    <td class="px-2 py-1.5 text-center" @click="open = !open">
                        <button class="p-0.5 rounded text-slate-400 hover:text-indigo-600 transition-all" x-bind:class="{ 'rotate-180': open }">
                            <svg class="w-3.5 h-3.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </td>
                    <td class="px-2 py-1.5" @click="open = !open">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-violet-100 to-purple-50 flex items-center justify-center text-[9px] font-bold text-violet-600 border border-violet-200/50 flex-shrink-0">MS</div>
                            <div>
                                <p class="text-sm font-semibold text-slate-800">Mega Sari</p>
                                <p class="text-[10px] text-slate-400">mega.sari@email.com</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-2 py-1.5" @click="open = !open">
                        <span class="text-xs text-slate-600">0833-5555-7777</span>
                    </td>
                    <td class="px-2 py-1.5 text-center" @click="open = !open">
                        <span class="text-xs font-semibold text-slate-800">6</span>
                    </td>
                    <td class="px-2 py-1.5 text-right" @click="open = !open">
                        <span class="text-xs font-bold text-slate-900">Rp 3,850,000</span>
                    </td>
                    <td class="px-2 py-1.5" @click="open = !open">
                        <span class="text-xs text-slate-500">16 Jul 2026</span>
                    </td>
                </tr>
                <!-- Accordion: Mega's repair history -->
                <tr x-data="{ open: false }" x-show="open" >
                    <td colspan="6" class="px-0 py-0">
                        <div class="bg-slate-50/80 border-t border-b border-slate-100">
                            <div class="px-6 py-2">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Repair History</span>
                                    <span class="text-[10px] text-slate-300">(6 tickets)</span>
                                </div>
                                <table class="w-full">
                                    <thead>
                                        <tr class="border-b border-slate-200">
                                            <th class="px-2 py-1 text-left text-[9px] font-semibold text-slate-400 uppercase">Ticket</th>
                                            <th class="px-2 py-1 text-left text-[9px] font-semibold text-slate-400 uppercase">Device</th>
                                            <th class="px-2 py-1 text-left text-[9px] font-semibold text-slate-400 uppercase">Issue</th>
                                            <th class="px-2 py-1 text-left text-[9px] font-semibold text-slate-400 uppercase">Status</th>
                                            <th class="px-2 py-1 text-left text-[9px] font-semibold text-slate-400 uppercase">Date</th>
                                            <th class="px-2 py-1 text-right text-[9px] font-semibold text-slate-400 uppercase">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <tr class="hover:bg-white/50 transition-colors">
                                            <td class="px-2 py-1"><span class="font-mono text-[11px] font-bold text-indigo-600">#TKT-1022</span></td>
                                            <td class="px-2 py-1"><span class="text-[11px] text-slate-700">iPhone 16 Pro Max</span></td>
                                            <td class="px-2 py-1"><span class="text-[11px] text-slate-500">Overheating — component diagnostic</span></td>
                                            <td class="px-2 py-1"><span class="inline-flex px-1.5 py-0.5 rounded text-[9px] font-medium bg-amber-50 text-amber-600 border border-amber-200">Diagnosing</span></td>
                                            <td class="px-2 py-1"><span class="text-[11px] text-slate-400">16 Jul 2026</span></td>
                                            <td class="px-2 py-1 text-right"><span class="text-[11px] font-semibold text-slate-700">Pending</span></td>
                                        </tr>
                                        <tr class="hover:bg-white/50 transition-colors">
                                            <td class="px-2 py-1"><span class="font-mono text-[11px] font-bold text-indigo-600">#TKT-1020</span></td>
                                            <td class="px-2 py-1"><span class="text-[11px] text-slate-700">Samsung Galaxy S25 Ultra</span></td>
                                            <td class="px-2 py-1"><span class="text-[11px] text-slate-500">Intermittent boot loop — diagnostic</span></td>
                                            <td class="px-2 py-1"><span class="inline-flex px-1.5 py-0.5 rounded text-[9px] font-medium bg-amber-50 text-amber-600 border border-amber-200">Diagnosing</span></td>
                                            <td class="px-2 py-1"><span class="text-[11px] text-slate-400">16 Jul 2026</span></td>
                                            <td class="px-2 py-1 text-right"><span class="text-[11px] font-semibold text-slate-700">Pending</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </td>
                </tr>

                <!-- Row 4: Agus Hermawan -->
                <tr x-data="{ open: false }" class="hover:bg-indigo-50/20 transition-colors even:bg-slate-50/50 cursor-pointer">
                    <td class="px-2 py-1.5 text-center" @click="open = !open">
                        <button class="p-0.5 rounded text-slate-400 hover:text-indigo-600 transition-all" x-bind:class="{ 'rotate-180': open }">
                            <svg class="w-3.5 h-3.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </td>
                    <td class="px-2 py-1.5" @click="open = !open">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-emerald-100 to-teal-50 flex items-center justify-center text-[9px] font-bold text-emerald-600 border border-emerald-200/50 flex-shrink-0">AH</div>
                            <div>
                                <p class="text-sm font-semibold text-slate-800">Agus Hermawan</p>
                                <p class="text-[10px] text-slate-400">agus.hermawan@email.com</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-2 py-1.5" @click="open = !open">
                        <span class="text-xs text-slate-600">0844-4321-8765</span>
                    </td>
                    <td class="px-2 py-1.5 text-center" @click="open = !open">
                        <span class="text-xs font-semibold text-slate-800">2</span>
                    </td>
                    <td class="px-2 py-1.5 text-right" @click="open = !open">
                        <span class="text-xs font-bold text-slate-900">Rp 1,700,000</span>
                    </td>
                    <td class="px-2 py-1.5" @click="open = !open">
                        <span class="text-xs text-slate-500">15 Jul 2026</span>
                    </td>
                </tr>
                <!-- Accordion: Agus' repair history -->
                <tr x-data="{ open: false }" x-show="open" >
                    <td colspan="6" class="px-0 py-0">
                        <div class="bg-slate-50/80 border-t border-b border-slate-100">
                            <div class="px-6 py-2">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Repair History</span>
                                    <span class="text-[10px] text-slate-300">(2 tickets)</span>
                                </div>
                                <table class="w-full">
                                    <thead>
                                        <tr class="border-b border-slate-200">
                                            <th class="px-2 py-1 text-left text-[9px] font-semibold text-slate-400 uppercase">Ticket</th>
                                            <th class="px-2 py-1 text-left text-[9px] font-semibold text-slate-400 uppercase">Device</th>
                                            <th class="px-2 py-1 text-left text-[9px] font-semibold text-slate-400 uppercase">Issue</th>
                                            <th class="px-2 py-1 text-left text-[9px] font-semibold text-slate-400 uppercase">Status</th>
                                            <th class="px-2 py-1 text-left text-[9px] font-semibold text-slate-400 uppercase">Date</th>
                                            <th class="px-2 py-1 text-right text-[9px] font-semibold text-slate-400 uppercase">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <tr class="hover:bg-white/50 transition-colors">
                                            <td class="px-2 py-1"><span class="font-mono text-[11px] font-bold text-indigo-600">#TKT-1019</span></td>
                                            <td class="px-2 py-1"><span class="text-[11px] text-slate-700">MacBook Pro 16" M4 Max</span></td>
                                            <td class="px-2 py-1"><span class="text-[11px] text-slate-500">Logic board repair — power IC replacement</span></td>
                                            <td class="px-2 py-1"><span class="inline-flex px-1.5 py-0.5 rounded text-[9px] font-medium bg-emerald-50 text-emerald-600 border border-emerald-200">Completed</span></td>
                                            <td class="px-2 py-1"><span class="text-[11px] text-slate-400">15 Jul 2026</span></td>
                                            <td class="px-2 py-1 text-right"><span class="text-[11px] font-semibold text-slate-700">Rp 1,200,000</span></td>
                                        </tr>
                                        <tr class="hover:bg-white/50 transition-colors">
                                            <td class="px-2 py-1"><span class="font-mono text-[11px] font-bold text-indigo-600">#TKT-0980</span></td>
                                            <td class="px-2 py-1"><span class="text-[11px] text-slate-700">Samsung Galaxy Tab S9</span></td>
                                            <td class="px-2 py-1"><span class="text-[11px] text-slate-500">Charging port replacement</span></td>
                                            <td class="px-2 py-1"><span class="inline-flex px-1.5 py-0.5 rounded text-[9px] font-medium bg-emerald-50 text-emerald-600 border border-emerald-200">Completed</span></td>
                                            <td class="px-2 py-1"><span class="text-[11px] text-slate-400">22 Jun 2026</span></td>
                                            <td class="px-2 py-1 text-right"><span class="text-[11px] font-semibold text-slate-700">Rp 500,000</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <!-- Footer -->
    <div class="px-3 py-2 border-t border-slate-100 flex items-center justify-between">
        <p class="text-[10px] text-slate-400">Showing 1 to 4 of 24 customers</p>
        <div class="flex items-center gap-1">
            <button class="px-2 py-1 rounded-md text-[10px] font-medium text-slate-400 hover:text-slate-600 hover:bg-slate-50 border border-slate-200">Previous</button>
            <button class="px-2.5 py-1 rounded-md text-[10px] font-medium bg-indigo-600 text-white border border-indigo-600">1</button>
            <button class="px-2.5 py-1 rounded-md text-[10px] font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 border border-slate-200">2</button>
            <button class="px-2.5 py-1 rounded-md text-[10px] font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 border border-slate-200">3</button>
            <span class="px-1 text-[10px] text-slate-300">...</span>
            <button class="px-2.5 py-1 rounded-md text-[10px] font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 border border-slate-200">6</button>
            <button class="px-2 py-1 rounded-md text-[10px] font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 border border-slate-200">Next</button>
        </div>
    </div>
</div>
@endsection