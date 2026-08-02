<template>
    <Teleport to="body">
        <div v-if="open" class="fixed inset-0 z-[9999] flex items-start justify-center pt-[15vh]" @click.self="closeSearch">
            <div class="w-full max-w-lg mx-4 rounded-2xl border overflow-hidden animate-scale-in shadow-2xl"
                :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
                <div class="flex items-center gap-3 px-5 py-4 border-b" :style="{ borderColor: 'var(--border-light)' }">
                    <svg class="w-5 h-5 flex-shrink-0 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <KInput  ref="searchInput" v-model="searchQuery" placeholder="Cari menu, servis, pelanggan, produk..." 
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
</template>

<script setup>
import KInput from '@/Components/KInput.vue';

import { ref, computed, watch, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    open: { type: Boolean, default: false },
    allowedMenuItems: { type: Array, default: () => [] },
});

const emit = defineEmits(['close']);

const searchInput = ref(null);
const searchQuery = ref('');
const searchIndex = ref(0);
const searchResults = ref([]);
const searching = ref(false);
let searchDebounce = null;

watch(() => props.open, async (open) => {
    if (open) {
        await nextTick();
        searchInput.value?.focus();
    } else {
        searchQuery.value = '';
    }
});

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
    emit('close');
    router.visit(href);
};

const navigateSelected = () => {
    const items = combinedSearchResults.value;
    if (items[searchIndex.value]) navigateTo(items[searchIndex.value].url);
};

const closeSearch = () => {
    searchQuery.value = '';
    emit('close');
};
</script>
