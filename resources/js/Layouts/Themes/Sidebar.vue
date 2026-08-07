<template>
    <aside v-if="showSidebar && !sidebarHidden"
        :class="[
            'fixed lg:static inset-y-0 z-40 flex flex-col transition-all duration-300 ease-out shadow-2xl shadow-slate-950/10 lg:shadow-none',
            sidebarWidth,
            sidebarPosition === 'right' ? 'right-0 border-l' : 'left-0 border-r',
            isMobile && !mobileOpen ? '-translate-x-full lg:translate-x-0' : 'translate-x-0'
        ]"
        :style="{ background: 'var(--bg-sidebar)', borderColor: 'var(--border-color)' }">

        <!-- Logo -->
        <div class="flex h-16 flex-shrink-0 items-center gap-2.5 border-b px-4" :style="{ borderColor: 'rgba(255,255,255,.10)' }">
            <Logo :link="route('dashboard')" size="sm" theme="dark" />
            <span v-if="sidebarExpanded || activeLayout !== 'slim'" class="truncate text-sm font-black text-white">
                {{ $page.props.tenant?.name || 'ServiceKU' }}
            </span>
            <KButton  v-if="isMobile" @click="$emit('close-mobile')" class="ml-auto w-7 h-7 rounded-lg flex items-center justify-center" style="color: var(--text-muted);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </KButton>
        </div>

        <!-- Branch Switcher (only if multi_branch is available) -->
        <div v-if="branches.length > 1" class="flex-shrink-0 border-b px-3 py-2.5" :style="{ borderColor: 'rgba(255,255,255,.10)' }">
            <Dropdown align="left" width="56">
                <template #trigger>
                    <KButton  class="w-full flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-xs font-medium transition-all"
                        :style="{ background: 'var(--bg-hover)', color: 'var(--text-sidebar)' }">
                        <div class="w-6 h-6 rounded-lg flex items-center justify-center text-[10px] font-bold text-white flex-shrink-0" style="background: var(--primary);">
                            {{ currentBranch?.name?.charAt(0) || 'P' }}
                        </div>
                        <span v-if="showBranchText" class="truncate max-w-[100px]">{{ currentBranch?.name || 'Pilih Cabang' }}</span>
                        <svg v-if="showBranchText" class="w-3 h-3 ml-auto" style="color: var(--text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </KButton>
                </template>
                <template #content>
                    <div class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-muted">Cabang</div>
                    <template v-for="branch in branches" :key="branch.id">
                        <DropdownLink v-if="branch.id !== currentBranch?.id" @click="emit('switch-branch', branch.id)">
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
        <nav class="flex-1 space-y-1 overflow-y-auto px-2.5 py-4">
            <template v-if="menuStyle === 'grouped'">
                <div v-for="(items, groupName) in groupedMenuItems" :key="groupName" class="mb-5">
                    <p v-if="showGroupTitles" class="px-3 mb-1.5 text-[10px] font-bold uppercase tracking-widest flex items-center gap-2"
                      style="color: var(--text-muted); opacity: 0.6;">
                      <span class="w-1.5 h-1.5 rounded-full" :style="{ background: getGroupAccent(groupName) }"></span>
                      {{ groupName }}
                    </p>
                    <Link v-for="item in items" :key="item.label" :href="item.href"
                        class="flex items-center gap-2.5 rounded-lg px-3 py-2.5 text-sm font-bold transition-all duration-150"
                        :style="isActive(item.href) 
                            ? { background: 'var(--bg-sidebar-active)', color: 'var(--text-sidebar-active)' } 
                            : { color: 'var(--text-sidebar)', opacity: 0.8 }"
                        :title="!sidebarExpanded && activeLayout === 'slim' ? item.label : ''">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 transition-all duration-200"
                          :style="isActive(item.href)
                            ? { background: getGroupAccent(item.group) }
                            : { background: 'rgba(255,255,255,.06)' }">
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
                    class="flex items-center gap-2.5 rounded-lg px-3 py-2.5 text-sm font-bold transition-all duration-150"
                    :style="isActive(item.href) 
                        ? { background: 'var(--bg-sidebar-active)', color: 'var(--text-sidebar-active)' } 
                        : { color: 'var(--text-sidebar)', opacity: 0.8 }"
                    :title="!sidebarExpanded && activeLayout === 'slim' ? item.label : ''">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 transition-all duration-200"
                      :style="isActive(item.href)
                        ? { background: getGroupAccent(item.group) }
                        : { background: 'rgba(255,255,255,.06)' }">
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
        <div v-if="showSidebar" class="flex-shrink-0 border-t px-3 py-3" :style="{ borderColor: 'rgba(255,255,255,.10)' }"></div>
    </aside>
</template>

<script setup>
import KButton from '@/Components/KButton.vue';

import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import Logo from '@/Components/Logo.vue';
import { getIcon } from '@/Components/Icons.js';
import { groupColors, getGroupAccent, isActive } from '@/Composables/layoutHelpers.js';

const props = defineProps({
    activeLayout: { type: String, default: 'modern' },
    menuStyle: { type: String, default: 'expanded' },
    allowedMenuItems: { type: Array, default: () => [] },
    groupedMenuItems: { type: Object, default: () => ({}) },
    sidebarPosition: { type: String, default: 'left' },
    sidebarExpanded: { type: Boolean, default: true },
    isMobile: { type: Boolean, default: false },
    mobileOpen: { type: Boolean, default: false },
    branches: { type: Array, default: () => [] },
    currentBranch: { type: Object, default: null },
    showSidebar: { type: Boolean, default: true },
    sidebarHidden: { type: Boolean, default: false },
});

const emit = defineEmits(['close-mobile', 'switch-branch']);

const showBranchText = computed(() => props.activeLayout !== 'slim' || props.sidebarExpanded);
const showGroupTitles = computed(() => props.activeLayout !== 'slim' || props.sidebarExpanded);

const sidebarWidth = computed(() => {
    if (props.activeLayout === 'slim') return props.sidebarExpanded ? 'w-64' : 'w-[64px]';
    if (props.activeLayout === 'elegant') return 'w-72';
    if (props.activeLayout === 'pro') return 'w-64';
    if (props.activeLayout === 'modern') return 'w-60';
    return 'w-64';
});
</script>
