<template>
    <div class="min-h-screen flex" style="background: var(--bg-app);">
        
        <!-- SIDEBAR -->
        <aside v-if="showSidebar && !sidebarHidden" ref="sidebarRef"
            :class="[
                'fixed lg:static inset-y-0 z-40 flex flex-col transition-all duration-300 ease-out',
                sidebarWidth,
                sidebarPosition === 'right' ? 'right-0 border-l' : 'left-0 border-r',
                isMobile && !mobileOpen ? '-translate-x-full lg:translate-x-0' : 'translate-x-0'
            ]"
            :style="{ background: 'var(--bg-sidebar)', borderColor: 'var(--border-color)' }">

            <!-- Logo -->
            <div class="h-14 lg:h-16 flex items-center gap-2.5 px-4 border-b flex-shrink-0" :style="{ borderColor: 'var(--border-color)' }">
                <Logo :link="route('dashboard')" size="sm" theme="dark" />
                <span v-if="sidebarExpanded || activeLayout !== 'slim'" class="text-sm font-bold truncate" style="color: var(--text-sidebar);">
                    {{ $page.props.tenant?.name || 'ServiceKU' }}
                </span>
                <button v-if="isMobile" @click="$emit('close-mobile')" class="ml-auto w-7 h-7 rounded-lg flex items-center justify-center" style="color: var(--text-muted);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Branch Switcher (only if multi_branch is available) -->
            <div v-if="branches.length > 1" class="px-3 py-2.5 border-b flex-shrink-0" :style="{ borderColor: 'var(--border-color)' }">
                <Dropdown align="left" width="56">
                    <template #trigger>
                        <button class="w-full flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-xs font-medium transition-all"
                            :style="{ background: 'var(--bg-hover)', color: 'var(--text-sidebar)' }">
                            <div class="w-6 h-6 rounded-lg flex items-center justify-center text-[10px] font-bold text-white flex-shrink-0" style="background: var(--primary);">
                                {{ currentBranch?.name?.charAt(0) || 'P' }}
                            </div>
                            <span v-if="showBranchText" class="truncate max-w-[100px]">{{ currentBranch?.name || 'Pilih Cabang' }}</span>
                            <svg v-if="showBranchText" class="w-3 h-3 ml-auto" style="color: var(--text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </template>
                    <template #content>
                        <div class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-muted">Cabang</div>
                        <template v-for="branch in branches" :key="branch.id">
                            <DropdownLink v-if="branch.id !== currentBranch?.id" @click="switchBranch(branch.id)">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold text-white" style="background: var(--primary);">{{ branch.name.charAt(0) }}</div>
                                    <span class="text-xs font-medium truncate max-w-[120px]" style="color: var(--text-primary);">{{ branch.name }}</span>
                                </div>
                            </DropdownLink>
                        </template>
                    </template>
                </Dropdown>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto px-2.5 py-4 space-y-1">
                <template v-if="menuStyle === 'grouped'">
                    <div v-for="(items, groupName) in groupedMenuItems" :key="groupName" class="mb-5">
                        <p v-if="showGroupTitles" class="px-3 mb-1.5 text-[10px] font-bold uppercase tracking-widest flex items-center gap-2"
                          style="color: var(--text-muted); opacity: 0.6;">
                          <span class="w-1.5 h-1.5 rounded-full" :style="{ background: getGroupAccent(groupName) }"></span>
                          {{ groupName }}
                        </p>
                        <Link v-for="item in items" :key="item.label" :href="item.href"
                            class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm transition-all duration-150"
                            :style="isActive(item.href) 
                                ? { background: 'var(--bg-sidebar-active)', color: 'var(--text-sidebar-active)' } 
                                : { color: 'var(--text-sidebar)', opacity: 0.8 }"
                            :title="!sidebarExpanded && activeLayout === 'slim' ? item.label : ''">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 transition-all duration-200"
                              :style="isActive(item.href)
                                ? { background: `linear-gradient(135deg, ${getGroupAccent(item.group)}, ${getGroupAccent(item.group)}dd)` }
                                : { background: 'var(--bg-hover)' }">
                              <span class="w-5 h-5 flex items-center justify-center transition-all duration-200"
                                :style="isActive(item.href)
                                  ? { color: '#ffffff' }
                                  : { color: groupColors[item.group]?.hex || 'var(--text-muted)', opacity: 0.7 }"
                                v-html="getIcon(item.id)"></span>
                            </div>
                            <span v-show="sidebarExpanded || activeLayout !== 'slim'" class="truncate">{{ item.label }}</span>
                        </Link>
                    </div>
                </template>
                <template v-else>
                    <Link v-for="item in allowedMenuItems" :key="item.label" :href="item.href"
                        class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm transition-all duration-150"
                        :style="isActive(item.href) 
                            ? { background: 'var(--bg-sidebar-active)', color: 'var(--text-sidebar-active)' } 
                            : { color: 'var(--text-sidebar)', opacity: 0.8 }"
                        :title="!sidebarExpanded && activeLayout === 'slim' ? item.label : ''">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 transition-all duration-200"
                          :style="isActive(item.href)
                            ? { background: `linear-gradient(135deg, ${getGroupAccent(item.group)}, ${getGroupAccent(item.group)}dd)` }
                            : { background: 'var(--bg-hover)' }">
                          <span class="w-5 h-5 flex items-center justify-center transition-all duration-200"
                            :style="isActive(item.href)
                              ? { color: '#ffffff' }
                              : { color: groupColors[item.group]?.hex || 'var(--text-muted)', opacity: 0.7 }"
                            v-html="getIcon(item.id)"></span>
                        </div>
                        <span v-show="sidebarExpanded || activeLayout !== 'slim'" class="truncate">{{ item.label }}</span>
                    </Link>
                </template>
            </nav>

            <!-- Sidebar Footer -->
            <div v-if="showSidebar" class="px-3 py-3 border-t flex-shrink-0" :style="{ borderColor: 'var(--border-color)' }"></div>
        </aside>

        <!-- Floating toggle button (when sidebar is hidden) -->
        <button v-if="sidebarHidden && !isMobile" @click="emit('toggle-sidebar')"
            class="fixed top-20 left-3 z-50 w-10 h-10 rounded-xl flex items-center justify-center shadow-lg hover:shadow-xl transition-all"
            :style="{ background: 'var(--primary)' }" title="Tampilkan Sidebar">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <!-- Mobile sidebar backdrop -->
        <div v-if="isMobile && mobileOpen" @click="$emit('close-mobile')" class="fixed inset-0 z-30 bg-black/50 backdrop-blur-sm lg:hidden"></div>

        <!-- MAIN CONTENT -->
        <div class="flex-1 flex flex-col min-h-screen" :class="{ 'lg:ml-0': !showSidebar }" :style="mainContentStyle">

            <!-- TOP HEADER -->
            <header v-if="showTopHeader" ref="headerRef"
                class="sticky top-0 z-20 border-b backdrop-blur-xl transition-all duration-200"
                :style="{ background: 'var(--bg-header)', borderColor: 'var(--border-light)' }">
                <div class="flex items-center justify-between h-14 lg:h-16 px-3 sm:px-6 lg:px-8" :style="{ maxWidth: 'var(--layout-content-max-width)' }">
                    <!-- LEFT -->
                    <div class="flex items-center gap-2 lg:gap-4">
                        <button v-if="showSidebar" @click="$emit('toggle-mobile')" 
                            class="lg:hidden w-9 h-9 rounded-xl flex items-center justify-center transition-colors"
                            style="color: var(--text-muted); background: var(--bg-hover);">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                        <Logo v-if="activeLayout === 'classic'" :link="route('dashboard')" size="sm" theme="dark" />
                        <span class="hidden lg:block text-xs font-semibold uppercase tracking-wider px-2 py-1 rounded-lg" 
                            style="background: var(--primary-soft); color: var(--primary);">
                            {{ $page.props.auth.user.role }} Panel
                        </span>
                    </div>

                    <!-- CENTER: Menu -->
                    <nav v-if="activeLayout === 'classic'" class="hidden lg:flex items-center gap-0.5">
                        <Link v-for="item in topbarPrimaryItems" :key="item.label" :href="item.href"
                            class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-semibold transition-all whitespace-nowrap"
                            :style="{
                                color: isActive(item.href)
                                  ? (groupColors[item.group]?.hex || 'var(--primary)')
                                  : 'var(--text-secondary)',
                                background: isActive(item.href)
                                  ? (groupColors[item.group]?.light || 'var(--primary-soft)')
                                  : 'transparent'
                            }">
                            <span class="w-4 h-4 flex items-center justify-center" v-html="getIcon(item.id)"></span>
                            {{ item.label }}
                        </Link>
                        <Dropdown v-if="topbarOverflowItems.length" align="left" width="64">
                            <template #trigger>
                                <button class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1"
                                    style="color: var(--text-muted);">
                                    <span>⋯</span><span class="hidden xl:inline">Lainnya</span>
                                </button>
                            </template>
                            <template #content>
                                <div v-for="item in topbarOverflowItems" :key="item.label">
                                    <DropdownLink :href="item.href">
                                        <div class="flex items-center gap-2.5 text-sm">
                                            <span class="w-4 h-4" v-html="getIcon(item.id)"></span>
                                            <span>{{ item.label }}</span>
                                        </div>
                                    </DropdownLink>
                                </div>
                            </template>
                        </Dropdown>
                    </nav>

                    <!-- RIGHT -->
                    <div class="flex items-center gap-1.5 lg:gap-2.5 ml-auto">
                        <ThemeSwitcher />
                        <!-- Clock & Date -->
                        <div class="hidden sm:flex items-center gap-2 px-2.5 py-1.5 rounded-xl text-[10px] font-semibold border"
                          :style="{ background: 'var(--bg-hover)', borderColor: 'var(--border-color)', color: 'var(--text-muted)' }">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>{{ currentTime }}</span>
                        </div>
                        <Dropdown v-if="(activeLayout === 'classic' || activeLayout === 'modern') && branches.length > 1" align="left" width="56">
                            <template #trigger>
                                <button class="hidden sm:flex items-center gap-2 px-2.5 py-1.5 rounded-xl border text-xs font-medium transition-all"
                                    style="background: var(--bg-hover); border-color: var(--border-light); color: var(--text-secondary);">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    <span class="max-w-[80px] truncate">{{ currentBranch?.name || 'Cabang' }}</span>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                            </template>
                            <template #content>
                                <div class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-muted">Cabang</div>
                                <template v-for="branch in branches" :key="branch.id">
                                    <DropdownLink v-if="branch.id !== currentBranch?.id" @click="switchBranch(branch.id)">
                                        <div class="flex items-center gap-2.5">{{ branch.name }}</div>
                                    </DropdownLink>
                                </template>
                            </template>
                        </Dropdown>

                        <div v-if="planName && planName !== 'Enterprise' && $page.props.auth.user.role === 'owner'" class="hidden md:flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl text-[10px] font-semibold border" 
                            style="background: var(--primary-soft); border-color: var(--border-focus); color: var(--primary);">
                            <span class="w-1.5 h-1.5 rounded-full" style="background: var(--primary);"></span>
                            {{ planName }}
                        </div>

                        <!-- User -->
                        <Dropdown align="right" width="52">
                            <template #trigger>
                                <button class="flex items-center gap-2 pl-2 pr-1 py-1 rounded-xl transition-all border border-transparent" style="background: var(--bg-hover);">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold text-white" style="background: var(--primary);">{{ userInitials }}</div>
                                    <span class="hidden lg:block text-xs font-medium max-w-[80px] truncate" style="color: var(--text-primary);">{{ $page.props.auth.user.name }}</span>
                                    <svg class="w-3 h-3" style="color: var(--text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                            </template>
                            <template #content>
                                <div class="px-4 py-3 border-b" style="border-color: var(--border-light);">
                                    <p class="text-sm font-bold" style="color: var(--text-primary);">{{ $page.props.auth.user.name }}</p>
                                    <p class="text-xs text-muted">{{ $page.props.auth.user.email }}</p>
                                </div>
                                <DropdownLink :href="route('user.profile')"><div class="flex items-center gap-2.5 text-sm"><span class="w-4 h-4 flex items-center justify-center" v-html="getIcon('users')"></span> Profil Saya</div></DropdownLink>
                                <DropdownLink v-if="canManage" :href="route('pengaturan.index')"><div class="flex items-center gap-2.5 text-sm"><span class="w-4 h-4 flex items-center justify-center" v-html="getIcon('settings')"></span> Pengaturan</div></DropdownLink>
                                <div class="border-t my-1" style="border-color: var(--border-light);"></div>
                                <DropdownLink :href="route('logout')" method="post" as="button">
                                    <div class="flex items-center gap-2.5 text-sm" style="color: var(--danger);"><span class="w-4 h-4 flex items-center justify-center" v-html="getIcon('logout')"></span> Keluar</div>
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </div>

                <!-- Mobile Menu (Classic) -->
                <div v-if="isMobile && mobileOpen && activeLayout === 'classic'" class="lg:hidden border-t" style="border-color: var(--border-light);">
                    <div class="max-h-[70vh] overflow-y-auto px-3 py-3 space-y-1">
                        <Link v-for="item in allowedMenuItems" :key="item.label" :href="item.href" @click="$emit('close-mobile')"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all"
                            :style="isActive(item.href) ? { background: 'var(--primary)', color: '#fff' } : { color: 'var(--text-secondary)' }">
                            <span class="w-4 h-4" v-html="getIcon(item.id)"></span>
                            <span>{{ item.label }}</span>
                        </Link>
                    </div>
                </div>
            </header>

            <!-- PAGE CONTENT -->
            <main class="flex-1">
                <Transition name="page" mode="out-in">
                    <div :key="$page.url">
                        <div v-if="$slots.header" class="border-b" style="background: var(--bg-card); border-color: var(--border-light);">
                            <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                                <slot name="header" />
                            </div>
                        </div>
                        <div class="max-w-7xl mx-auto py-5 sm:py-6 px-3 sm:px-6 lg:px-8">
                            <slot />
                        </div>
                    </div>
                </Transition>
            </main>
        </div>

        <!-- GLOBAL SEARCH MODAL -->
        <Teleport to="body">
            <div v-if="searchOpen" class="fixed inset-0 z-[9999] flex items-start justify-center pt-[15vh]" @click.self="closeSearch">
                <div class="w-full max-w-lg mx-4 rounded-2xl border overflow-hidden animate-scale-in shadow-2xl"
                    :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
                    <div class="flex items-center gap-3 px-5 py-4 border-b" :style="{ borderColor: 'var(--border-light)' }">
                        <svg class="w-5 h-5 flex-shrink-0 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input ref="searchInput" v-model="searchQuery" placeholder="Cari menu, servis, pelanggan, produk..." 
                            class="flex-1 border-0 bg-transparent text-sm outline-none" style="color: var(--text-primary);" autofocus
                            @keydown.escape="closeSearch" @keydown.down.prevent="searchIndex++" @keydown.up.prevent="searchIndex--" @keydown.enter="navigateSelected" />
                        <kbd class="text-[10px] font-medium px-1.5 py-0.5 rounded-md border" style="color: var(--text-muted); background: var(--bg-hover); border-color: var(--border-color);">ESC</kbd>
                    </div>
                    <div class="p-2 max-h-80 overflow-y-auto">
                        <div v-if="searching" class="px-3 py-6 text-center text-xs text-muted">Mencari...</div>
                        <div v-else-if="combinedSearchResults.length === 0 && searchQuery.length >= 2" class="px-3 py-8 text-center text-sm text-muted">Tidak ada hasil</div>
                        <div v-for="(item, idx) in combinedSearchResults" :key="idx" @click="navigateTo(item.url)"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl cursor-pointer transition-all duration-150"
                            :style="idx === searchIndex ? { background: 'var(--primary-soft)' } : {}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-base" style="background: var(--bg-hover);">
                                <span>{{ item.icon || '🔍' }}</span>
                            </div>
                            <div>
                                <p class="text-sm font-medium" style="color: var(--text-primary);">{{ item.label }}</p>
                                <p class="text-xs text-muted">{{ item.description }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
        <Toast ref="toastRef" />
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import Logo from '@/Components/Logo.vue';
import ThemeSwitcher from '@/Components/ThemeSwitcher.vue';
import Toast from '@/Components/Toast.vue';
import { setToastInstance } from '@/Composables/useToast.js';
import { getIcon } from '@/Components/Icons.js';

const props = defineProps({
    activeLayout: { type: String, default: 'modern' },
    menuStyle: { type: String, default: 'expanded' },
    allowedMenuItems: { type: Array, default: () => [] },
    groupedMenuItems: { type: Object, default: () => ({}) },
    topMenuItems: { type: Array, default: () => [] },
    topbarPrimaryItems: { type: Array, default: () => [] },
    topbarOverflowItems: { type: Array, default: () => [] },
    branches: { type: Array, default: () => [] },
    currentBranch: { type: Object, default: null },
    planName: { type: String, default: '' },
    userInitials: { type: String, default: '' },
    canManage: { type: Boolean, default: false },
    mobileOpen: { type: Boolean, default: false },
    searchOpen: { type: Boolean, default: false },
    sidebarPosition: { type: String, default: 'left' },
    sidebarHidden: { type: Boolean, default: false },
    visibleGroups: { type: Array, default: null },
});

const emit = defineEmits(['toggle-mobile', 'close-mobile', 'open-search', 'close-search', 'toggle-sidebar']);

const groupColors = {
  'Utama':     { accent: 'var(--primary)', light: 'var(--primary-soft)', hex: '#7c3aed' },
  'Transaksi': { accent: '#10b981', light: 'rgba(16, 185, 129, 0.12)', hex: '#10b981' },
  'Manajemen': { accent: '#3b82f6', light: 'rgba(59, 130, 246, 0.12)', hex: '#3b82f6' },
  'Operasional': { accent: 'var(--primary)', light: 'var(--primary-soft)', hex: '#7c3aed' },
  'Keuangan': { accent: '#10b981', light: 'rgba(16, 185, 129, 0.12)', hex: '#10b981' },
  'Sistem & Laporan': { accent: '#3b82f6', light: 'rgba(59, 130, 246, 0.12)', hex: '#3b82f6' },
};

function getGroupAccent(group) {
  return groupColors[group]?.accent || 'var(--primary)';
}

const page = usePage();
const toastRef = ref(null);
const sidebarRef = ref(null);
const headerRef = ref(null);
const searchInput = ref(null);
const searchQuery = ref('');
const searchIndex = ref(0);
const isMobile = ref(false);
const sidebarExpanded = ref(props.activeLayout !== 'slim');
const currentTime = ref(formatCurrentTime());

function formatCurrentTime() {
    const timezone = page.props.timezone || 'UTC';
    const now = new Date();
    const opts = { timeZone: timezone, weekday: 'long', day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' };
    const formatter = new Intl.DateTimeFormat('id-ID', opts);
    return formatter.format(now);
}

let timeInterval;
const checkMobile = () => {
    isMobile.value = window.innerWidth < 1024;
};
onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
    window.addEventListener('keydown', handleKeydown);
    timeInterval = setInterval(() => {
        currentTime.value = formatCurrentTime();
    }, 60000);
    if (toastRef.value) {
        setToastInstance(toastRef.value);
    }
});

watch(() => page.props.flash, (flash) => {
    if (!flash) return;
    if (flash.success) toastRef.value?.add('success', flash.success);
    if (flash.error) toastRef.value?.add('error', flash.error);
    if (flash.warning) toastRef.value?.add('warning', flash.warning);
    if (flash.info) toastRef.value?.add('info', flash.info);
}, { immediate: true, deep: true });
onUnmounted(() => {
    window.removeEventListener('resize', checkMobile);
    window.removeEventListener('keydown', handleKeydown);
    if (timeInterval) clearInterval(timeInterval);
});

const handleKeydown = (e) => {
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault();
        emit('open-search');
    }
};

watch(() => props.searchOpen, async (open) => {
    if (open) {
        await nextTick();
        searchInput.value?.focus();
    } else {
        searchQuery.value = '';
    }
});

const showSidebar = computed(() => ['pro', 'elegant', 'slim', 'modern'].includes(props.activeLayout));
const showTopHeader = computed(() => ['classic', 'modern', 'slim'].includes(props.activeLayout));
const showBranchText = computed(() => props.activeLayout !== 'slim' || sidebarExpanded.value);
const showGroupTitles = computed(() => props.activeLayout !== 'slim' || sidebarExpanded.value);

const sidebarWidth = computed(() => {
    if (props.activeLayout === 'slim') return sidebarExpanded.value ? 'w-64' : 'w-[64px]';
    if (props.activeLayout === 'elegant') return 'w-72';
    if (props.activeLayout === 'pro') return 'w-64';
    if (props.activeLayout === 'modern') return 'w-60';
    return 'w-64';
});

const mainContentStyle = computed(() => {
    if (isMobile.value) return {};
    if (!showSidebar.value) return {};
    const widths = { modern: '240px', pro: '256px', elegant: '288px', slim: sidebarExpanded.value ? '256px' : '64px' };
    return { marginLeft: widths[props.activeLayout] || '0px' };
});

const switchBranch = (branchId) => {
    router.visit(route('branches.index'), { data: { branch_id: branchId } });
};

const isActive = (href) => {
    const base = route('dashboard').replace(/\/+$/, '');
    const routeName = href.replace(base, '').replace(/^\//, '');
    return route().current(routeName + '*');
};

const searchResults = ref([]);
const searching = ref(false);
let searchDebounce = null;

watch(searchQuery, (q) => {
    if (!q || q.length < 2) {
        searchResults.value = [];
        return;
    }
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(async () => {
        searching.value = true;
        try {
            const res = await fetch(route('search') + '?q=' + encodeURIComponent(q), {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            searchResults.value = data.results || [];
        } catch (e) {
            searchResults.value = [];
        } finally {
            searching.value = false;
        }
    }, 300);
});

const combinedSearchResults = computed(() => {
    if (!searchQuery.value) return [];
    const q = searchQuery.value.toLowerCase();
    const menuMatches = props.allowedMenuItems.filter(item =>
        item.label.toLowerCase().includes(q) ||
        item.description?.toLowerCase().includes(q)
    ).map(m => ({
        type: 'menu',
        icon: '📌',
        label: m.label,
        description: 'Menu Navigasi',
        url: m.href,
    }));
    return [...menuMatches, ...searchResults.value];
});

const navigateTo = (href) => {
    searchQuery.value = '';
    emit('close-search');
    router.visit(href);
};

const navigateSelected = () => {
    const items = combinedSearchResults.value;
    if (items[searchIndex.value]) navigateTo(items[searchIndex.value].url);
};

const closeSearch = () => {
    searchQuery.value = '';
    emit('close-search');
};
</script>

<style>
.page-enter-active, .page-leave-active {
    transition: opacity 0.15s ease, transform 0.15s ease;
}
.page-enter-from {
    opacity: 0;
    transform: translateY(6px);
}
.page-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}
</style>