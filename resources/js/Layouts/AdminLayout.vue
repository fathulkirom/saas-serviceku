<template>
    <div class="min-h-screen" style="background: var(--bg-app);">
        <!-- Admin Top Navigation -->
        <nav class="sticky top-0 z-50 border-b backdrop-blur-xl" style="background: var(--bg-sidebar); border-color: var(--border-color);">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-14">
                    <div class="flex items-center gap-1">
                        <Logo :link="route('admin.dashboard')" size="sm" theme="dark" badge="Admin" />
                        <!-- Desktop Nav -->
                        <div class="hidden lg:flex ml-6 space-x-0.5">
                            <Link v-for="item in navItems" :key="item.label" :href="item.href"
                                class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all duration-200 hover:bg-white/5"
                                :style="{
                                    color: route().current(item.active) ? 'var(--text-sidebar-active)' : 'var(--text-sidebar)',
                                    background: route().current(item.active) ? 'var(--bg-sidebar-active)' : 'transparent'
                                }"
                            >
                                {{ item.label }}
                            </Link>
                        </div>
                        <!-- Mobile Nav -->
                        <div class="lg:hidden ml-2">
                            <button @click="showMobileMenu = !showMobileMenu" class="p-2 rounded-lg transition-colors" :style="{ color: 'var(--text-sidebar)' }">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path v-if="!showMobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <!-- Mobile Dropdown -->
                    <div v-if="showMobileMenu" class="fixed inset-0 z-50 lg:hidden" @click.self="showMobileMenu = false">
                        <div class="absolute right-0 top-14 w-60 rounded-xl shadow-lg border p-2 mx-2" style="background: var(--bg-card); border-color: var(--border-color);">
                            <Link v-for="item in navItems" :key="item.label" :href="item.href"
                                @click="showMobileMenu = false"
                                class="block px-3 py-2.5 text-sm rounded-lg transition-colors hover:bg-white/5"
                                :style="{
                                    color: route().current(item.active) ? 'var(--text-sidebar-active)' : 'var(--text-sidebar)',
                                    background: route().current(item.active) ? 'var(--bg-sidebar-active)' : 'transparent'
                                }"
                            >
                                {{ item.label }}
                            </Link>
                            <hr class="my-2" style="border-color: var(--border-light);">
                            <Link :href="route('dashboard')" class="block px-3 py-2.5 text-sm rounded-lg transition-colors" :style="{ color: 'var(--text-sidebar)' }">← Ke Aplikasi</Link>
                            <Link :href="route('logout')" method="post" as="button" class="block w-full text-left px-3 py-2.5 text-sm rounded-lg transition-colors" :style="{ color: '#f87171' }">Logout</Link>
                        </div>
                    </div>
                    <!-- Right -->
                    <div class="flex items-center gap-3">
                        <Link :href="route('dashboard')" class="hidden sm:inline-flex text-xs font-medium px-3 py-1.5 rounded-lg transition-colors" :style="{ color: 'var(--text-sidebar)' }">
                            ← Ke Aplikasi
                        </Link>
                        <Link :href="route('logout')" method="post" as="button" class="text-xs font-medium px-3 py-1.5 rounded-lg transition-colors" :style="{ color: 'var(--text-sidebar)' }">
                            Logout
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Header -->
        <header v-if="$slots.header" class="border-b" style="background: var(--bg-card); border-color: var(--border-light);">
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
