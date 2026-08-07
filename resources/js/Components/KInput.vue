<template>
    <component
        ref="rootEl"
        :is="tag"
        :value="bindValue"
        :type="as === 'input' ? type : undefined"
        :rows="as === 'textarea' ? rows : undefined"
        :placeholder="placeholder"
        :disabled="disabled"
        :class="classes"
        :style="computedStyle"
        @input="onInput"
        @change="onChange"
    >
        <slot />
    </component>
</template>

<script setup>
import { ref, computed, useAttrs } from 'vue';

/**
 * Input/select/textarea reusable (standar).
 * Meneruskan class/style/attr dari parent sehingga output HTML & tampilan
 * identik dengan elemen asli. Jika parent TIDAK memberi class, dipakai
 * default styling konsisten (dipakai komponen Services).
 *
 * as: 'input' | 'select' | 'textarea'
 * size: sm (px-2 py-1.5 text-xs) | md (px-3 py-2 text-sm) | lg (px-3 py-2.5 text-sm)
 */
const props = defineProps({
    modelValue: { type: [String, Number], default: '' },
    as: { type: String, default: 'input' },
    type: { type: String, default: 'text' },
    placeholder: { type: String, default: '' },
    rows: { type: [String, Number], default: 2 },
    disabled: { type: Boolean, default: false },
    size: { type: String, default: 'md' },
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

// Untuk input file, binding value tidak bermakna — jangan set value.
const bindValue = computed(() => {
    if (props.as === 'input' && props.type === 'file') return undefined;
    return props.modelValue;
});

const tag = computed(() => {
    if (props.as === 'select') return 'select';
    if (props.as === 'textarea') return 'textarea';
    return 'input';
});

const sizeClass = computed(() => {
    if (props.size === 'sm') return 'px-2 py-1.5 text-xs';
    if (props.size === 'lg') return 'px-3 py-2.5 text-sm';
    return 'px-3 py-2 text-sm';
});

const baseClasses = computed(() => {
    const list = ['rounded-lg', 'border', 'font-semibold', 'transition-all', sizeClass.value];
    list.push(props.widthClass || 'w-full');
    if (props.as === 'textarea') list.push('resize-none');
    if (props.extraClass) list.push(props.extraClass);
    return list;
});

const classes = computed(() => (hasParentClass.value ? [] : baseClasses.value));

const inputStyle = {
    background: 'var(--bg-input)',
    color: 'var(--text-primary)',
    borderColor: 'var(--border-color)',
    boxShadow: 'var(--shadow-xs)',
};
const computedStyle = computed(() => (hasParentStyle.value ? undefined : inputStyle));

function onInput(e) {
    emitValue(e.target.value);
}

function onChange(e) {
    emitValue(e.target.value);
}

function emitValue(raw) {
    let value = raw;
    // Samakan perilaku dengan modifier .number milik Vue (looseToNumber):
    // string kosong tetap string kosong, bukan NaN.
    if (props.modelModifiers.number) {
        const n = parseFloat(value);
        value = Number.isNaN(n) ? value : n;
    }
    emit('update:modelValue', value);
}
</script>
