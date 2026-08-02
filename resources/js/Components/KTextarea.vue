<template>
    <textarea
        ref="rootEl"
        :value="modelValue"
        :rows="rows"
        :disabled="disabled"
        :class="classes"
        :style="computedStyle"
        @input="onInput"
        @change="onChange"
    ></textarea>
</template>

<script setup>
import { ref, computed, useAttrs } from 'vue';

/**
 * Textarea reusable (standar). Meneruskan class/style/attr dari parent
 * sehingga output HTML & tampilan identik dengan <textarea> biasa.
 * Jika parent tidak memberi class, dipakai default styling konsisten.
 */
const props = defineProps({
    modelValue: { type: [String, Number], default: '' },
    rows: { type: [String, Number], default: 3 },
    disabled: { type: Boolean, default: false },
    size: { type: String, default: 'md' }, // sm | md | lg
    widthClass: { type: String, default: '' },
    extraClass: { type: String, default: '' },
    modelModifiers: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['update:modelValue']);
const attrs = useAttrs();
const rootEl = ref(null);

defineExpose({
    focus: () => rootEl.value?.focus(),
    blur: () => rootEl.value?.blur(),
    select: () => rootEl.value?.select(),
});

const hasParentClass = computed(() => !!attrs.class);
const hasParentStyle = computed(() => !!attrs.style);

const sizeClass = computed(() => {
    if (props.size === 'sm') return 'px-2 py-1.5 text-xs';
    if (props.size === 'lg') return 'px-3 py-2.5 text-sm';
    return 'px-3 py-2 text-sm';
});

const baseClasses = computed(() => {
    const list = ['rounded-xl', 'border', 'transition-all', 'resize-none', sizeClass.value];
    list.push(props.widthClass || 'w-full');
    if (props.extraClass) list.push(props.extraClass);
    return list;
});

const classes = computed(() => (hasParentClass.value ? [] : baseClasses.value));

const inputStyle = {
    background: 'var(--bg-input)',
    color: 'var(--text-primary)',
    borderColor: 'var(--border-color)',
};
const computedStyle = computed(() => (hasParentStyle.value ? undefined : inputStyle));

function emitValue(raw) {
    let value = raw;
    if (props.modelModifiers.number) {
        const n = parseFloat(value);
        value = Number.isNaN(n) ? value : n;
    }
    emit('update:modelValue', value);
}

function onInput(e) { emitValue(e.target.value); }
function onChange(e) { emitValue(e.target.value); }
</script>
