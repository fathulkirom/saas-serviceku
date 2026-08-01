<template>
    <AuthenticatedLayout>
        <div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            
            <!-- Header CRM Profile -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center text-xl font-bold text-indigo-700 ring-4 ring-white shadow-sm">
                        {{ getInitials(customer.name) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-3">
                            <h2 class="text-2xl font-bold text-zinc-900 tracking-tight">{{ customer.name }}</h2>
                            <span v-if="customer.is_member" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Member
                            </span>
                        </div>
                        <p class="text-sm text-zinc-500 font-medium mt-1">Pelanggan sejak {{ formatDate(customer.created_at) }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('customers.index')" class="px-4 py-2 bg-white border border-zinc-200 rounded-xl text-zinc-700 text-sm font-semibold hover:bg-zinc-50 transition-colors shadow-sm">
                        Kembali ke Daftar
                    </Link>
                </div>
            </div>

            <!-- CRM 2-Column Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- KIRI: Informasi Profil -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-6">
                        <h3 class="text-sm font-bold text-zinc-900 mb-5 flex items-center gap-2">
                            <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Informasi Kontak
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-1">Telepon</p>
                                <p class="text-sm font-medium text-zinc-900 flex items-center gap-2">
                                    {{ customer.phone || '-' }}
                                    <a v-if="customer.phone" :href="'https://wa.me/' + cleanPhone(customer.phone)" target="_blank" class="text-emerald-500 hover:text-emerald-600">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                                    </a>
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-1">Email</p>
                                <p class="text-sm font-medium text-zinc-900">{{ customer.email || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-1">Alamat</p>
                                <p class="text-sm font-medium text-zinc-900 leading-relaxed">{{ customer.address || '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Member Status Card -->
                    <div v-if="customer.is_member" class="bg-gradient-to-br from-zinc-900 to-zinc-800 rounded-2xl border border-zinc-700 shadow-lg p-6 relative overflow-hidden">
                        <div class="absolute -right-10 -top-10 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                        <h3 class="text-sm font-semibold text-zinc-300 mb-6 flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                            Member Aktif
                        </h3>
                        <div class="mb-4">
                            <p class="text-xs text-zinc-400 uppercase tracking-wider mb-1">Nomor Kartu</p>
                            <p class="text-lg font-mono font-bold text-white tracking-widest">{{ customer.card_number || 'ACS' + customer.id }}</p>
                        </div>
                        <div class="flex justify-between items-end border-t border-zinc-700/50 pt-4 mt-2">
                            <div>
                                <p class="text-xs text-zinc-400 mb-1">Total Poin</p>
                                <p class="text-xl font-bold text-emerald-400">{{ customer.points || 0 }} <span class="text-sm font-medium text-emerald-500/70">pts</span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KANAN: Timeline / Riwayat -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Stats Quick View -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-sm">
                            <p class="text-sm font-semibold text-zinc-500 mb-1">Total Tiket Servis</p>
                            <h3 class="text-2xl font-bold text-zinc-900">{{ customer.services?.length || 0 }}</h3>
                        </div>
                        <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-sm">
                            <p class="text-sm font-semibold text-zinc-500 mb-1">Total Belanja (Penjualan)</p>
                            <h3 class="text-2xl font-bold text-indigo-600">Rp {{ formatNumber(totalBelanja) }}</h3>
                        </div>
                    </div>

                    <!-- Riwayat Servis -->
                    <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden">
                        <div class="px-6 py-5 border-b border-zinc-200 bg-zinc-50/50">
                            <h3 class="font-bold text-zinc-900">Riwayat Servis</h3>
                        </div>
                        <div class="p-0">
                            <div v-if="customer.services?.length > 0" class="divide-y divide-zinc-100">
                                <div v-for="s in customer.services" :key="s.id" class="p-5 hover:bg-zinc-50 transition-colors flex items-center justify-between group">
                                    <div class="flex items-start gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        </div>
                                        <div>
                                            <Link :href="route('services.show', s.id)" class="text-sm font-bold text-zinc-900 hover:text-indigo-600 transition-colors">Tiket #{{ s.id }}</Link>
                                            <p class="text-xs text-zinc-500 mt-0.5">{{ formatDateTime(s.created_at) }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-lg border" :class="statusClass(s.status)">
                                            {{ s.status }}
                                        </span>
                                        <Link :href="route('services.show', s.id)" class="opacity-0 group-hover:opacity-100 px-3 py-1.5 bg-white border border-zinc-200 rounded-lg text-xs font-semibold text-zinc-700 hover:text-indigo-600 hover:border-indigo-200 transition-all">
                                            Lihat
                                        </Link>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="p-10 text-center text-zinc-500">
                                <svg class="w-12 h-12 mx-auto text-zinc-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                <p class="text-sm font-medium">Belum ada riwayat servis.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Riwayat Penjualan -->
                    <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden">
                        <div class="px-6 py-5 border-b border-zinc-200 bg-zinc-50/50">
                            <h3 class="font-bold text-zinc-900">Riwayat Pembelian POS</h3>
                        </div>
                        <div class="p-0">
                            <div v-if="customer.sales?.length > 0" class="divide-y divide-zinc-100">
                                <div v-for="s in customer.sales" :key="s.id" class="p-5 hover:bg-zinc-50 transition-colors flex items-center justify-between group">
                                    <div class="flex items-start gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        </div>
                                        <div>
                                            <Link :href="route('sales.show', s.id)" class="text-sm font-bold text-zinc-900 hover:text-indigo-600 transition-colors">Invoice #{{ s.id }}</Link>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="text-xs text-zinc-500">{{ formatDateTime(s.created_at) }}</span>
                                                <span class="w-1 h-1 rounded-full bg-zinc-300"></span>
                                                <span class="text-xs font-medium text-zinc-600 capitalize">{{ s.sale_type }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-end gap-2">
                                        <p class="text-sm font-bold text-zinc-900">Rp {{ formatNumber(s.total) }}</p>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="p-10 text-center text-zinc-500">
                                <svg class="w-12 h-12 mx-auto text-zinc-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                <p class="text-sm font-medium">Belum ada riwayat pembelian POS.</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    customer: { type: Object, required: true },
});

const totalBelanja = computed(() => {
    if (!props.customer.sales) return 0;
    return props.customer.sales.reduce((sum, s) => sum + Number(s.total), 0);
});

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num || 0);

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });
};

const formatDateTime = (dateStr) => {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const getInitials = (name) => {
    if (!name) return '?';
    return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
};

const cleanPhone = (phone) => {
    if (!phone) return '';
    let cleaned = phone.replace(/\D/g, '');
    if (cleaned.startsWith('0')) {
        cleaned = '62' + cleaned.substring(1);
    }
    return cleaned;
};

const statusClass = (s) => {
    const map = {
        'menunggu_alokasi': 'bg-amber-50 text-amber-700 border-amber-200',
        'dikerjakan': 'bg-blue-50 text-blue-700 border-blue-200',
        'selesai': 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'batal': 'bg-red-50 text-red-700 border-red-200',
        'siap_diambil': 'bg-indigo-50 text-indigo-700 border-indigo-200',
    };
    return map[s] || 'bg-zinc-50 text-zinc-700 border-zinc-200';
};
</script>
