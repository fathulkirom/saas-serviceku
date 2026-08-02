<template>
    <AuthenticatedLayout>
        <div class="flex flex-col h-[calc(100vh-64px)] overflow-hidden bg-zinc-50">
            <!-- Header CRM Style -->
            <div class="px-8 py-6 bg-white border-b border-zinc-200 flex items-center justify-between z-10">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center border border-indigo-100">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-zinc-900 tracking-tight">Pelanggan</h1>
                        <p class="text-sm text-zinc-500 font-medium mt-0.5">Manajemen Data Pelanggan & CRM</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('customers.create')" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow-md transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Pelanggan Baru
                    </Link>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="flex-1 overflow-y-auto p-8">
                <!-- Stats Row -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-sm flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-zinc-500 uppercase tracking-wider">Total Pelanggan</p>
                            <h3 class="text-2xl font-bold text-zinc-900 mt-1">{{ customers.total || 0 }}</h3>
                        </div>
                    </div>
                </div>

                <!-- Data Table Section -->
                <div class="bg-white border border-zinc-200 rounded-2xl shadow-sm flex flex-col">
                    <!-- Toolbar -->
                    <div class="p-4 border-b border-zinc-200 flex items-center justify-between bg-zinc-50/50 rounded-t-2xl">
                        <div class="relative w-80">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <KInput  
                                type="text" 
                                v-model="search" 
                                placeholder="Cari nama, telepon, email..." 
                                class="w-full pl-9 pr-4 py-2 bg-white border border-zinc-300 rounded-lg text-sm text-zinc-900 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all" />
                        </div>
                        <div class="text-sm font-medium text-zinc-500">
                            Menampilkan {{ filteredCustomers.length }} dari {{ customers.total }} data
                        </div>
                    </div>

                    <!-- Table (Tailwind Custom) -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="bg-zinc-50 text-zinc-500 font-semibold border-b border-zinc-200 uppercase tracking-wider text-xs">
                                <tr>
                                    <th class="px-6 py-4">Nama Pelanggan</th>
                                    <th class="px-6 py-4">Kontak</th>
                                    <th class="px-6 py-4 text-center">Status Member</th>
                                    <th class="px-6 py-4 text-center">Riwayat</th>
                                    <th class="px-6 py-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100">
                                <tr v-if="filteredCustomers.length === 0">
                                    <td colspan="5" class="px-6 py-12 text-center text-zinc-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-zinc-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                            <p class="text-base font-medium text-zinc-900">Belum ada pelanggan ditemukan</p>
                                            <p class="text-sm mt-1">Coba gunakan kata kunci pencarian yang lain.</p>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-for="customer in filteredCustomers" :key="customer.id" class="hover:bg-zinc-50/80 transition-colors group">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center flex-shrink-0 text-sm ring-2 ring-white shadow-sm">
                                                {{ getInitials(customer.name) }}
                                            </div>
                                            <div>
                                                <p class="font-bold text-zinc-900 group-hover:text-indigo-600 transition-colors">{{ customer.name }}</p>
                                                <p class="text-xs text-zinc-500 mt-0.5">ID: {{ customer.id }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col gap-1 text-sm">
                                            <span class="flex items-center gap-1.5 text-zinc-700">
                                                <svg class="w-3.5 h-3.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                                {{ customer.phone || '-' }}
                                            </span>
                                            <span v-if="customer.email" class="flex items-center gap-1.5 text-zinc-500 text-xs">
                                                <svg class="w-3.5 h-3.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                                {{ customer.email }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div v-if="customer.is_member" class="inline-flex flex-col items-center">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Member
                                            </span>
                                            <span class="text-[10px] font-mono text-emerald-600 mt-1 font-bold">{{ customer.card_number || 'ACS' + customer.id }}</span>
                                        </div>
                                        <KButton  v-else @click="registerMember(customer)" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-zinc-100 text-zinc-600 hover:bg-zinc-200 hover:text-zinc-900 transition-colors border border-zinc-200">
                                            + Terbitkan
                                        </KButton>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-4 text-xs">
                                            <div class="flex flex-col items-center" title="Total Servis">
                                                <span class="font-bold text-zinc-900">{{ customer.services_count || 0 }}</span>
                                                <span class="text-zinc-500">Servis</span>
                                            </div>
                                            <div class="w-px h-6 bg-zinc-200"></div>
                                            <div class="flex flex-col items-center" title="Total Poin">
                                                <span class="font-bold text-indigo-600">{{ customer.points || 0 }}</span>
                                                <span class="text-zinc-500">Poin</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <Link :href="route('customers.show', customer.id)" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-zinc-200 text-zinc-700 hover:bg-zinc-50 hover:text-indigo-600 rounded-lg text-sm font-semibold transition-all shadow-sm">
                                            Detail
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-4 border-t border-zinc-200 bg-zinc-50/50 rounded-b-2xl">
                        <Pagination :meta="customers" :per-page="customers.per_page" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import KButton from '@/Components/KButton.vue';
import KInput from '@/Components/KInput.vue';

import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    customers: { type: Object, default: () => ({ data: [], links: [], from: 0, to: 0, total: 0 }) },
});

const search = ref('');

const filteredCustomers = computed(() => {
    let items = props.customers.data || [];
    if (search.value.trim()) {
        const q = search.value.toLowerCase();
        items = items.filter(c => c.name?.toLowerCase().includes(q) || c.phone?.includes(q) || c.card_number?.toLowerCase().includes(q));
    }
    return items;
});

const registerMember = (customer) => {
    if (!confirm(`Terbitkan Kartu Member untuk ${customer.name}?`)) return;
    router.post(route('customers.register-member', customer.id), {}, {
        preserveScroll: true,
    });
};

const getInitials = (name) => {
    if (!name) return '?';
    return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
};
</script>
