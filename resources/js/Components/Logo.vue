<template>
    <Link v-if="link" :href="link" class="flex items-center gap-2" :class="containerClass">
        <img v-if="showImage"
            :src="logoSrc"
            :alt="altText"
            class="h-9 w-auto"
            @error="onImgError" />
        <span v-else
            class="flex items-center justify-center font-bold text-white shadow-premium"
            :class="iconSizeClass + ' ' + iconBgClass">
            {{ initials }}
        </span>
        <span v-if="showText" class="font-bold" :class="textClass">{{ text }}</span>
        <span v-if="badge" class="px-2 py-0.5 rounded-md text-[10px] font-semibold" :class="badgeClass">{{ badge }}</span>
    </Link>
    <div v-else class="flex items-center gap-2" :class="containerClass">
        <img v-if="showImage"
            :src="logoSrc"
            :alt="altText"
            class="h-9 w-auto"
            @error="onImgError" />
        <span v-else
            class="flex items-center justify-center font-bold text-white shadow-premium"
            :class="iconSizeClass + ' ' + iconBgClass">
            {{ initials }}
        </span>
        <span v-if="showText" class="font-bold" :class="textClass">{{ text }}</span>
        <span v-if="badge" class="px-2 py-0.5 rounded-md text-[10px] font-semibold" :class="badgeClass">{{ badge }}</span>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    link: { type: String, default: null },
    text: { type: String, default: 'ServiceKU' },
    altText: { type: String, default: 'ServiceKU Logo' },
    showText: { type: Boolean, default: true },
    badge: { type: String, default: null },
    size: { type: String, default: 'md' }, // sm, md, lg
    theme: { type: String, default: 'light' }, // light, dark
    logoPath: { type: String, default: '/images/logo.svg' },
});

const imgError = ref(false);

const showImage = computed(() => {
    return props.logoPath && !imgError.value;
});

const logoSrc = computed(() => props.logoPath);

const initials = computed(() => {
    return props.text.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
});

const sizeMap = {
    sm: { icon: 'w-7 h-7 text-xs', text: 'text-sm', iconBg: 'rounded-lg' },
    md: { icon: 'w-9 h-9 text-sm', text: 'text-lg', iconBg: 'rounded-xl' },
    lg: { icon: 'w-12 h-12 text-base', text: 'text-2xl', iconBg: 'rounded-2xl' },
};

const themeMap = {
    light: { iconBgExtra: 'bg-gradient-to-br from-premium-500 to-premium-700', textColor: 'text-dark-900', badgeBg: 'bg-premium-500/20 text-premium-300' },
    dark: { iconBgExtra: 'bg-gradient-to-br from-premium-400 to-premium-600', textColor: 'text-white', badgeBg: 'bg-premium-500/20 text-premium-300' },
};

const s = sizeMap[props.size] || sizeMap.md;
const t = themeMap[props.theme] || themeMap.light;

const iconSizeClass = s.icon;
const iconBgClass = `${s.iconBg} ${t.iconBgExtra}`;
const textClass = `${s.text} ${t.textColor}`;
const badgeClass = t.badgeBg;
const containerClass = '';

function onImgError() {
    imgError.value = true;
}
</script>
