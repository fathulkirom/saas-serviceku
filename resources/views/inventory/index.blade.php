@extends('layouts.app')

@section('title', 'Inventory')

@section('breadcrumb')
    <a href="#" class="hover:text-slate-600 transition-colors">Inventory</a>
    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-slate-500">All Parts</span>
@endsection

@section('content')
<!-- ===== TOOLBAR ===== -->
<div class="bg-white rounded-lg border border-slate-100 shadow-soft mb-2">
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 px-3 py-2">
        <!-- Live Search -->
        <div class="relative flex-1 w-full">
            <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" placeholder="Search by part name, SKU, or category..." class="w-full pl-8 pr-3 py-1.5 rounded-lg border border-slate-200 bg-slate-50 text-xs text-slate-700 placeholder:text-slate-400 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 transition-all">
        </div>
        <!-- Filters -->
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <select class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white text-xs text-slate-600 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 transition-all">
                <option>All Categories</option>
                <option>LCD & Display</option>
                <option>Battery</option>
                <option>Charging Port</option>
                <option>Flex Cable</option>
                <option>Accessories</option>
            </select>
            <select class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white text-xs text-slate-600 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 transition-all">
                <option>All Stock</option>
                <option>In Stock</option>
                <option>Low Stock (&lt; 3)</option>
                <option>Out of Stock</option>
            </select>
            <button class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 transition-all shadow-soft flex-shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Add New Part
            </button>
        </div>
    </div>
</div>

<!-- ===== INVENTORY TABLE ===== -->
<div class="bg-white rounded-lg border border-slate-100 shadow-soft overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="sticky top-0 z-10 bg-slate-50 px-2 py-1.5 text-left text-[10px] font-semibold text-slate-500 uppercase tracking-wider w-20">Part ID</th>
                    <th class="sticky top-0 z-10 bg-slate-50 px-2 py-1.5 text-left text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Name</th>
                    <th class="sticky top-0 z-10 bg-slate-50 px-2 py-1.5 text-left text-[10px] font-semibold text-slate-500 uppercase tracking-wider w-28">Category</th>
                    <th class="sticky top-0 z-10 bg-slate-50 px-2 py-1.5 text-right text-[10px] font-semibold text-slate-500 uppercase tracking-wider w-24">Cost</th>
                    <th class="sticky top-0 z-10 bg-slate-50 px-2 py-1.5 text-right text-[10px] font-semibold text-slate-500 uppercase tracking-wider w-28">Sell Price</th>
                    <th class="sticky top-0 z-10 bg-slate-50 px-2 py-1.5 text-center text-[10px] font-semibold text-slate-500 uppercase tracking-wider w-20">Stock</th>
                    <th class="sticky top-0 z-10 bg-slate-50 px-2 py-1.5 text-center text-[10px] font-semibold text-slate-500 uppercase tracking-wider w-20">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                <!-- Row 1: Normal stock -->
                <tr class="hover:bg-indigo-50/20 transition-colors even:bg-slate-50/50">
                    <td class="px-2 py-1">
                        <span class="font-mono text-xs font-bold text-indigo-600">LCD-IP16PM</span>
                    </td>
                    <td class="px-2 py-1">
                        <p class="text-xs font-semibold text-slate-800">LCD iPhone 16 Pro Max — OEM Grade</p>
                        <p class="text-[10px] text-slate-400">Original Apple quality, 120Hz Super Retina XDR</p>
                    </td>
                    <td class="px-2 py-1">
                        <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-medium bg-sky-50 text-sky-600 border border-sky-200">LCD & Display</span>
                    </td>
                    <td class="px-2 py-1 text-right">
                        <span class="text-xs text-slate-600">Rp 850,000</span>
                    </td>
                    <td class="px-2 py-1 text-right">
                        <input type="text" value="1,250,000" class="w-full text-right px-1.5 py-0.5 rounded-md border border-slate-200 bg-white text-xs font-semibold text-slate-800 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 transition-all hover:border-slate-300">
                    </td>
                    <td class="px-2 py-1 text-center">
                        <input type="number" value="5" min="0" class="w-14 text-center px-1 py-0.5 rounded-md border border-slate-200 bg-white text-xs font-semibold text-slate-800 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 transition-all hover:border-slate-300">
                    </td>
                    <td class="px-2 py-1 text-center">
                        <div class="flex items-center justify-center gap-0.5">
                            <button class="p-1 rounded-md text-slate-400 hover:text-indigo-600 hover:bg-white transition-all" title="Edit">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button class="p-1 rounded-md text-slate-400 hover:text-rose-600 hover:bg-white transition-all" title="Delete">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                <!-- Row 2: Normal stock -->
                <tr class="hover:bg-indigo-50/20 transition-colors even:bg-slate-50/50">
                    <td class="px-2 py-1">
                        <span class="font-mono text-xs font-bold text-indigo-600">BAT-S25U</span>
                    </td>
                    <td class="px-2 py-1">
                        <p class="text-xs font-semibold text-slate-800">Baterai Samsung Galaxy S25 Ultra</p>
                        <p class="text-[10px] text-slate-400">Original Samsung 5000mAh Li-Ion</p>
                    </td>
                    <td class="px-2 py-1">
                        <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-medium bg-violet-50 text-violet-600 border border-violet-200">Battery</span>
                    </td>
                    <td class="px-2 py-1 text-right">
                        <span class="text-xs text-slate-600">Rp 280,000</span>
                    </td>
                    <td class="px-2 py-1 text-right">
                        <input type="text" value="425,000" class="w-full text-right px-1.5 py-0.5 rounded-md border border-slate-200 bg-white text-xs font-semibold text-slate-800 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 transition-all hover:border-slate-300">
                    </td>
                    <td class="px-2 py-1 text-center">
                        <input type="number" value="8" min="0" class="w-14 text-center px-1 py-0.5 rounded-md border border-slate-200 bg-white text-xs font-semibold text-slate-800 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 transition-all hover:border-slate-300">
                    </td>
                    <td class="px-2 py-1 text-center">
                        <div class="flex items-center justify-center gap-0.5">
                            <button class="p-1 rounded-md text-slate-400 hover:text-indigo-600 hover:bg-white transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                            <button class="p-1 rounded-md text-slate-400 hover:text-rose-600 hover:bg-white transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                        </div>
                    </td>
                </tr>
                <!-- Row 3: LOW STOCK (stock < 3) - red background -->
                <tr class="hover:bg-red-50/30 transition-colors even:bg-red-50/30 bg-red-50/20">
                    <td class="px-2 py-1">
                        <span class="font-mono text-xs font-bold text-red-700">CHG-MBA4</span>
                    </td>
                    <td class="px-2 py-1">
                        <p class="text-xs font-semibold text-red-800">Charging Port USB-C — MacBook Air M4</p>
                        <p class="text-[10px] text-red-500">Original Apple USB-C flex assembly</p>
                    </td>
                    <td class="px-2 py-1">
                        <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-medium bg-amber-50 text-amber-600 border border-amber-200">Charging Port</span>
                    </td>
                    <td class="px-2 py-1 text-right">
                        <span class="text-xs text-red-700">Rp 120,000</span>
                    </td>
                    <td class="px-2 py-1 text-right">
                        <input type="text" value="185,000" class="w-full text-right px-1.5 py-0.5 rounded-md border border-red-200 bg-white text-xs font-semibold text-red-800 focus:border-red-400 focus:ring-1 focus:ring-red-400/30 transition-all hover:border-red-300">
                    </td>
                    <td class="px-2 py-1 text-center">
                        <input type="number" value="2" min="0" class="w-14 text-center px-1 py-0.5 rounded-md border border-red-200 bg-red-50 text-xs font-semibold text-red-700 focus:border-red-400 focus:ring-1 focus:ring-red-400/30 transition-all">
                    </td>
                    <td class="px-2 py-1 text-center">
                        <div class="flex items-center justify-center gap-0.5">
                            <button class="p-1 rounded-md text-red-400 hover:text-indigo-600 hover:bg-white transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                            <button class="p-1 rounded-md text-red-400 hover:text-rose-600 hover:bg-white transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                        </div>
                    </td>
                </tr>
                <!-- Row 4: Normal stock -->
                <tr class="hover:bg-indigo-50/20 transition-colors even:bg-slate-50/50">
                    <td class="px-2 py-1">
                        <span class="font-mono text-xs font-bold text-indigo-600">TGL-IP16</span>
                    </td>
                    <td class="px-2 py-1">
                        <p class="text-xs font-semibold text-slate-800">Tempered Glass — iPhone 16 Series</p>
                        <p class="text-[10px] text-slate-400">Premium 9H hardness, oleophobic coating, black frame</p>
                    </td>
                    <td class="px-2 py-1">
                        <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-medium bg-teal-50 text-teal-600 border border-teal-200">Accessories</span>
                    </td>
                    <td class="px-2 py-1 text-right">
                        <span class="text-xs text-slate-600">Rp 18,000</span>
                    </td>
                    <td class="px-2 py-1 text-right">
                        <input type="text" value="35,000" class="w-full text-right px-1.5 py-0.5 rounded-md border border-slate-200 bg-white text-xs font-semibold text-slate-800 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 transition-all hover:border-slate-300">
                    </td>
                    <td class="px-2 py-1 text-center">
                        <input type="number" value="25" min="0" class="w-14 text-center px-1 py-0.5 rounded-md border border-slate-200 bg-white text-xs font-semibold text-slate-800 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 transition-all hover:border-slate-300">
                    </td>
                    <td class="px-2 py-1 text-center">
                        <div class="flex items-center justify-center gap-0.5">
                            <button class="p-1 rounded-md text-slate-400 hover:text-indigo-600 hover:bg-white transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                            <button class="p-1 rounded-md text-slate-400 hover:text-rose-600 hover:bg-white transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                        </div>
                    </td>
                </tr>
                <!-- Row 5: LOW STOCK (stock < 3) - red background -->
                <tr class="hover:bg-red-50/30 transition-colors even:bg-red-50/30 bg-red-50/20">
                    <td class="px-2 py-1">
                        <span class="font-mono text-xs font-bold text-red-700">FLC-IP15P</span>
                    </td>
                    <td class="px-2 py-1">
                        <p class="text-xs font-semibold text-red-800">Flex Cable Charging — iPhone 15 Pro</p>
                        <p class="text-[10px] text-red-500">OEM quality, includes charging IC</p>
                    </td>
                    <td class="px-2 py-1">
                        <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-medium bg-rose-50 text-rose-600 border border-rose-200">Flex Cable</span>
                    </td>
                    <td class="px-2 py-1 text-right">
                        <span class="text-xs text-red-700">Rp 35,000</span>
                    </td>
                    <td class="px-2 py-1 text-right">
                        <input type="text" value="65,000" class="w-full text-right px-1.5 py-0.5 rounded-md border border-red-200 bg-white text-xs font-semibold text-red-800 focus:border-red-400 focus:ring-1 focus:ring-red-400/30 transition-all hover:border-red-300">
                    </td>
                    <td class="px-2 py-1 text-center">
                        <input type="number" value="1" min="0" class="w-14 text-center px-1 py-0.5 rounded-md border border-red-200 bg-red-50 text-xs font-semibold text-red-700 focus:border-red-400 focus:ring-1 focus:ring-red-400/30 transition-all">
                    </td>
                    <td class="px-2 py-1 text-center">
                        <div class="flex items-center justify-center gap-0.5">
                            <button class="p-1 rounded-md text-red-400 hover:text-indigo-600 hover:bg-white transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                            <button class="p-1 rounded-md text-red-400 hover:text-rose-600 hover:bg-white transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                        </div>
                    </td>
                </tr>
                <!-- Row 6: OUT OF STOCK (stock = 0) - red background -->
                <tr class="hover:bg-red-50/30 transition-colors even:bg-red-50/30 bg-red-50/20">
                    <td class="px-2 py-1">
                        <span class="font-mono text-xs font-bold text-red-700">LCD-IP15P</span>
                    </td>
                    <td class="px-2 py-1">
                        <p class="text-xs font-semibold text-red-800">LCD iPhone 15 Pro — OLED Grade A</p>
                        <p class="text-[10px] text-red-500">Super Retina XDR, 120Hz ProMotion, True Tone</p>
                    </td>
                    <td class="px-2 py-1">
                        <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-medium bg-sky-50 text-sky-600 border border-sky-200">LCD & Display</span>
                    </td>
                    <td class="px-2 py-1 text-right">
                        <span class="text-xs text-red-700">Rp 650,000</span>
                    </td>
                    <td class="px-2 py-1 text-right">
                        <input type="text" value="950,000" class="w-full text-right px-1.5 py-0.5 rounded-md border border-red-200 bg-white text-xs font-semibold text-red-800 focus:border-red-400 focus:ring-1 focus:ring-red-400/30 transition-all hover:border-red-300">
                    </td>
                    <td class="px-2 py-1 text-center">
                        <div class="flex items-center justify-center">
                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700 border border-red-200">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                                Out of Stock
                            </span>
                        </div>
                    </td>
                    <td class="px-2 py-1 text-center">
                        <div class="flex items-center justify-center gap-0.5">
                            <button class="p-1 rounded-md text-red-400 hover:text-indigo-600 hover:bg-white transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                            <button class="p-1 rounded-md text-red-400 hover:text-rose-600 hover:bg-white transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <!-- Footer -->
    <div class="px-3 py-2 border-t border-slate-100 flex items-center justify-between">
        <p class="text-[10px] text-slate-400">Showing 1 to 6 of 24 parts</p>
        <div class="flex items-center gap-1">
            <button class="px-2 py-1 rounded-md text-[10px] font-medium text-slate-400 hover:text-slate-600 hover:bg-slate-50 border border-slate-200">Previous</button>
            <button class="px-2.5 py-1 rounded-md text-[10px] font-medium bg-indigo-600 text-white border border-indigo-600">1</button>
            <button class="px-2.5 py-1 rounded-md text-[10px] font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 border border-slate-200">2</button>
            <button class="px-2.5 py-1 rounded-md text-[10px] font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 border border-slate-200">3</button>
            <span class="px-1 text-[10px] text-slate-300">...</span>
            <button class="px-2.5 py-1 rounded-md text-[10px] font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 border border-slate-200">4</button>
            <button class="px-2 py-1 rounded-md text-[10px] font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 border border-slate-200">Next</button>
        </div>
    </div>
</div>
@endsection