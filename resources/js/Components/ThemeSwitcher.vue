<template>
    <KButton  @click="toggle" class="w-7 h-7 rounded-lg flex items-center justify-center transition-all"
        :style="{ background: 'var(--bg-hover)', color: 'var(--text-muted)' }"
        :title="isDark ? 'Mode Terang' : 'Mode Gelap'">
        <svg v-if="isDark" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
        <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
        </svg>
    </KButton>
</template>

<script setup>
import KButton from '@/Components/KButton.vue';

import { ref, onMounted } from 'vue';

const isDark = ref(false);
const STORAGE_KEY = 'theme';

function applyTheme(dark) {
    isDark.value = dark;
    document.documentElement.classList.add('theme-transition');
    document.documentElement.classList.toggle('dark', dark);
    localStorage.setItem(STORAGE_KEY, dark ? 'dark' : 'light');
    setTimeout(() => {
        document.documentElement.classList.remove('theme-transition');
    }, 400);
}

function toggle() {
    applyTheme(!isDark.value);
}

onMounted(() => {
    const stored = localStorage.getItem(STORAGE_KEY);
    if (stored === 'dark') {
        isDark.value = true;
        document.documentElement.classList.add('dark');
    } else if (stored === 'light') {
        isDark.value = false;
    } else {
        isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
        document.documentElement.classList.toggle('dark', isDark.value);
    }
});
</script>
