<template>
    <Teleport to="body">
        <div
            v-if="modelValue"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
            :class="{ 'overflow-y-auto py-8': scrollable }"
            @click.self="emit('update:modelValue', false)"
        >
            <div
                class="rounded-2xl shadow-2xl p-5 w-full mx-3 border"
                :class="maxWidthClass"
                :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }"
            >
                <slot />
            </div>
        </div>
    </Teleport>
</template>

<script setup>
import { computed } from 'vue';

/**
 * Dialog/Modal reusable (standar) — mereplikasi persis struktur modal
 * yang sebelumnya diduplikasi di banyak halaman (Teleport + overlay + panel).
 * maxWidth: 'sm' (max-w-sm) | 'lg' (max-w-lg)
 * scrollable: menambahkan overflow-y-auto py-8 pada overlay.
 */
const props = defineProps({
    modelValue: { type: Boolean, default: false },
    maxWidth: { type: String, default: 'sm' },
    scrollable: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);

const maxWidthClass = computed(() => (props.maxWidth === 'lg' ? 'max-w-lg' : 'max-w-sm'));
</script>
