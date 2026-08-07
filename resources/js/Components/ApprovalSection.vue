<template>
    <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-zinc-100 flex items-center justify-between">
            <h3 class="font-bold text-zinc-900">{{ title }}</h3>
            <span v-if="count > 0" class="text-xs font-semibold px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700">{{ count }} item</span>
        </div>
        <slot v-if="count > 0" />
        <p v-else class="px-6 py-10 text-center text-sm text-zinc-400">{{ empty }}</p>
    </div>
</template>

<script setup>
defineProps({
    title: { type: String, default: '' },
    count: { type: Number, default: 0 },
    empty: { type: String, default: 'Tidak ada data.' },
});
</script>
