<template>
    <header
        class="sticky top-0 z-20 border-b backdrop-blur-xl transition-all duration-200"
        :style="{ background: 'var(--bg-header)', borderColor: 'var(--border-light)' }">
        <div class="flex items-center justify-between h-14 lg:h-16 px-3 sm:px-6 lg:px-8" :style="{ maxWidth: 'var(--layout-content-max-width)' }">
            <!-- LEFT -->
            <div class="flex items-center gap-2 lg:gap-4">
                <KButton  v-if="showSidebar" @click="$emit('toggle-mobile')" 
                    class="lg:hidden w-9 h-9 rounded-xl flex items-center justify-center transition-colors"
                    style="color: var(--text-muted); background: var(--bg-hover);">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </KButton>
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
                        <KButton  class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1"
                            style="color: var(--text-muted);">
                            <span>⋯</span><span class="hidden xl:inline">Lainnya</span>
                        </KButton>
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
                        <KButton  class="hidden sm:flex items-center gap-2 px-2.5 py-1.5 rounded-xl border text-xs font-medium transition-all"
                            style="background: var(--bg-hover); border-color: var(--border-light); color: var(--text-secondary);">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span class="max-w-[80px] truncate">{{ currentBranch?.name || 'Cabang' }}</span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </KButton>
                    </template>
                    <template #content>
                        <div class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-muted">Cabang</div>
                        <template v-for="branch in branches" :key="branch.id">
                            <DropdownLink v-if="branch.id !== currentBranch?.id" @click="emit('switch-branch', branch.id)">
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
                        <KButton  class="flex items-center gap-2 pl-2 pr-1 py-1 rounded-xl transition-all border border-transparent" style="background: var(--bg-hover);">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold text-white" style="background: var(--primary);">{{ userInitials }}</div>
                            <span class="hidden lg:block text-xs font-medium max-w-[80px] truncate" style="color: var(--text-primary);">{{ $page.props.auth.user.name }}</span>
                            <svg class="w-3 h-3" style="color: var(--text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </KButton>
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
</template>

<script setup>
import KButton from '@/Components/KButton.vue';

import { ref, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import Logo from '@/Components/Logo.vue';
import ThemeSwitcher from '@/Components/ThemeSwitcher.vue';
import { getIcon } from '@/Components/Icons.js';
import { groupColors, isActive } from '@/Composables/layoutHelpers.js';

const props = defineProps({
    activeLayout: { type: String, default: 'modern' },
    topbarPrimaryItems: { type: Array, default: () => [] },
    topbarOverflowItems: { type: Array, default: () => [] },
    allowedMenuItems: { type: Array, default: () => [] },
    branches: { type: Array, default: () => [] },
    currentBranch: { type: Object, default: null },
    planName: { type: String, default: '' },
    userInitials: { type: String, default: '' },
    canManage: { type: Boolean, default: false },
    isMobile: { type: Boolean, default: false },
    mobileOpen: { type: Boolean, default: false },
    showSidebar: { type: Boolean, default: true },
});

const emit = defineEmits(['toggle-mobile', 'close-mobile', 'switch-branch']);

const page = usePage();
const currentTime = ref(formatCurrentTime());

function formatCurrentTime() {
    const timezone = page.props.timezone || 'UTC';
    const now = new Date();
    const opts = { timeZone: timezone, weekday: 'long', day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' };
    const formatter = new Intl.DateTimeFormat('id-ID', opts);
    return formatter.format(now);
}

let timeInterval;
onMounted(() => {
    timeInterval = setInterval(() => {
        currentTime.value = formatCurrentTime();
    }, 60000);
});
onUnmounted(() => {
    if (timeInterval) clearInterval(timeInterval);
});
</script>
