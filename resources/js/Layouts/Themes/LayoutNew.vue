<template>
    <div class="min-h-screen flex" style="background: var(--bg-app);">
        <Sidebar
            :active-layout="activeLayout"
            :menu-style="menuStyle"
            :allowed-menu-items="allowedMenuItems"
            :grouped-menu-items="groupedMenuItems"
            :sidebar-position="sidebarPosition"
            :sidebar-expanded="sidebarExpanded"
            :is-mobile="isMobile"
            :mobile-open="mobileOpen"
            :branches="branches"
            :current-branch="currentBranch"
            :show-sidebar="showSidebar"
            :sidebar-hidden="sidebarHidden"
            @close-mobile="$emit('close-mobile')"
            @switch-branch="switchBranch"
        />

        <!-- Floating toggle button (when sidebar is hidden) -->
        <KButton  v-if="sidebarHidden && !isMobile" @click="$emit('toggle-sidebar')"
            class="fixed top-20 left-3 z-50 w-10 h-10 rounded-xl flex items-center justify-center shadow-lg hover:shadow-xl transition-all"
            :style="{ background: 'var(--primary)' }" title="Tampilkan Sidebar">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </KButton>

        <!-- Mobile sidebar backdrop -->
        <div v-if="isMobile && mobileOpen" @click="$emit('close-mobile')" class="fixed inset-0 z-30 bg-black/50 backdrop-blur-sm lg:hidden"></div>

        <!-- MAIN CONTENT -->
        <div class="flex-1 flex flex-col min-h-screen" :class="{ 'lg:ml-0': !showSidebar }" :style="mainContentStyle">
            <HeaderBar
                v-if="showTopHeader"
                :active-layout="activeLayout"
                :topbar-primary-items="topbarPrimaryItems"
                :topbar-overflow-items="topbarOverflowItems"
                :allowed-menu-items="allowedMenuItems"
                :branches="branches"
                :current-branch="currentBranch"
                :plan-name="planName"
                :user-initials="userInitials"
                :can-manage="canManage"
                :is-mobile="isMobile"
                :mobile-open="mobileOpen"
                :show-sidebar="showSidebar"
                @toggle-mobile="$emit('toggle-mobile')"
                @close-mobile="$emit('close-mobile')"
                @switch-branch="switchBranch"
            />

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

        <GlobalSearch :open="searchOpen" :allowed-menu-items="allowedMenuItems" @close="$emit('close-search')" />
        <Toast ref="toastRef" />
    </div>
</template>

<script setup>
import KButton from '@/Components/KButton.vue';

import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import Sidebar from '@/Layouts/Themes/Sidebar.vue';
import HeaderBar from '@/Layouts/Themes/HeaderBar.vue';
import GlobalSearch from '@/Layouts/Themes/GlobalSearch.vue';
import Toast from '@/Components/Toast.vue';
import { setToastInstance } from '@/Composables/useToast.js';

const props = defineProps({
    activeLayout: { type: String, default: 'modern' },
    menuStyle: { type: String, default: 'expanded' },
    allowedMenuItems: { type: Array, default: () => [] },
    groupedMenuItems: { type: Object, default: () => ({}) },
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
});

const emit = defineEmits(['toggle-mobile', 'close-mobile', 'open-search', 'close-search', 'toggle-sidebar']);

const page = usePage();
const toastRef = ref(null);
const isMobile = ref(false);
const sidebarExpanded = ref(props.activeLayout !== 'slim');

const checkMobile = () => {
    isMobile.value = window.innerWidth < 1024;
};

onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
    window.addEventListener('keydown', handleKeydown);
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
});

const handleKeydown = (e) => {
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault();
        emit('open-search');
    }
};

const showSidebar = computed(() => ['pro', 'elegant', 'slim', 'modern'].includes(props.activeLayout));
const showTopHeader = computed(() => ['classic', 'modern', 'slim'].includes(props.activeLayout));

const mainContentStyle = computed(() => {
    if (isMobile.value) return {};
    if (!showSidebar.value) return {};
    const widths = { modern: '240px', pro: '256px', elegant: '288px', slim: sidebarExpanded.value ? '256px' : '64px' };
    return { marginLeft: widths[props.activeLayout] || '0px' };
});

const switchBranch = (branchId) => {
    router.visit(route('branches.index'), { data: { branch_id: branchId } });
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