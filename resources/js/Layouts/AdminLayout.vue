<template>
    <div class="admin-shell min-h-screen bg-[#f6f8fb]">
        <!-- Admin Top Navigation -->
        <nav class="sticky top-0 z-50 border-b border-slate-200 bg-white/90 backdrop-blur-xl">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between">
                    <div class="flex items-center gap-1">
                        <Logo :link="route('admin.dashboard')" size="sm" theme="light" badge="Admin" />
                        <!-- Desktop Nav -->
                        <div class="hidden lg:flex ml-6 space-x-0.5">
                            <Link v-for="item in navItems" :key="item.label" :href="item.href"
                                class="rounded-lg px-3 py-2 text-xs font-black transition-all duration-200"
                                :class="route().current(item.active)
                                    ? 'bg-teal-50 text-teal-800 ring-1 ring-teal-100'
                                    : 'text-slate-500 hover:bg-slate-100 hover:text-slate-950'"
                            >
                                {{ item.label }}
                            </Link>
                        </div>
                        <!-- Mobile Nav -->
                        <div class="lg:hidden ml-2">
                            <KButton  @click="showMobileMenu = !showMobileMenu" class="rounded-lg p-2 text-slate-600 transition-colors hover:bg-slate-100">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path v-if="!showMobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </KButton>
                        </div>
                    </div>
                    <!-- Mobile Dropdown -->
                    <div v-if="showMobileMenu" class="fixed inset-0 z-50 lg:hidden" @click.self="showMobileMenu = false">
                        <div class="absolute right-0 top-16 mx-2 w-64 rounded-xl border border-slate-200 bg-white p-2 shadow-xl">
                            <Link v-for="item in navItems" :key="item.label" :href="item.href"
                                @click="showMobileMenu = false"
                                class="block rounded-lg px-3 py-2.5 text-sm font-bold transition-colors"
                                :class="route().current(item.active) ? 'bg-teal-50 text-teal-800' : 'text-slate-600 hover:bg-slate-100'"
                            >
                                {{ item.label }}
                            </Link>
                            <hr class="my-2 border-slate-200">
                            <Link :href="route('dashboard')" class="block rounded-lg px-3 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-100">← Ke Aplikasi</Link>
                            <Link :href="route('logout')" method="post" as="button" class="block w-full rounded-lg px-3 py-2.5 text-left text-sm font-bold text-red-600 hover:bg-red-50">Logout</Link>
                        </div>
                    </div>
                    <!-- Right -->
                    <div class="flex items-center gap-3">
                        <Link :href="route('dashboard')" class="hidden rounded-lg px-3 py-2 text-xs font-black text-slate-600 transition-colors hover:bg-slate-100 sm:inline-flex">
                            ← Ke Aplikasi
                        </Link>
                        <Link :href="route('logout')" method="post" as="button" class="rounded-lg px-3 py-2 text-xs font-black text-slate-600 transition-colors hover:bg-red-50 hover:text-red-700">
                            Logout
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Header -->
        <header v-if="$slots.header" class="border-b border-slate-200 bg-white">
            <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <!-- Main Content -->
        <main>
            <div class="max-w-7xl mx-auto py-6 sm:py-8 px-4 sm:px-6 lg:px-8">
                <slot />
            </div>
        </main>
    </div>
</template>

<script setup>
import KButton from '@/Components/KButton.vue';

import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import Logo from '@/Components/Logo.vue';

const showMobileMenu = ref(false);

const navItems = [
    { label: 'Dashboard', href: route('admin.dashboard'), active: 'admin.dashboard' },
    { label: 'Tenant', href: route('admin.tenant.index'), active: 'admin.tenant.*' },
    { label: 'Paket', href: route('admin.plans'), active: 'admin.plans' },
    { label: 'Voucher', href: route('admin.vouchers.index'), active: 'admin.vouchers.*' },
    { label: 'Pembayaran', href: route('admin.payments'), active: 'admin.payments' },
    { label: 'Payment Settings', href: route('admin.payment-settings'), active: 'admin.payment-settings' },
    { label: 'Monitoring', href: route('admin.monitoring'), active: 'admin.monitoring' },
    { label: 'Backup', href: route('admin.backup'), active: 'admin.backup' },
    { label: 'Logs', href: route('admin.logs'), active: 'admin.logs' },
    { label: 'Pengaturan', href: route('admin.settings'), active: 'admin.settings' },
];
</script>
