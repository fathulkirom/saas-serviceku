<template>
    <input
        ref="rootEl"
        type="checkbox"
        :checked="isChecked"
        :value="value"
        :disabled="disabled"
        @change="onChange"
    />
</template>

<script setup>
import { ref, computed } from 'vue';

/**
 * Checkbox reusable (standar).
 * Mendukung:
 *  - v-model boolean (modelValue = true/false)
 *  - v-model array (modelValue = array, value = item value)
 *  - controlled (:checked + @change, tanpa v-model)
 *  - true-value/false-value (seperti native v-model checkbox)
 * Class/style/id/name dari parent diteruskan (inheritAttrs).
 */
const props = defineProps({
    modelValue: { type: [Boolean, Array], default: undefined },
    value: { type: [String, Number], default: undefined },
    checked: { type: Boolean, default: undefined },
    disabled: { type: Boolean, default: false },
    trueValue: { type: [String, Number], default: undefined },
    falseValue: { type: [String, Number], default: undefined },
});

const emit = defineEmits(['update:modelValue']);
const rootEl = ref(null);

defineExpose({
    focus: () => rootEl.value?.focus(),
    blur: () => rootEl.value?.blur(),
    click: () => rootEl.value?.click(),
});

const isChecked = computed(() => {
    if (props.checked !== undefined) return props.checked;
    if (props.trueValue !== undefined) return props.modelValue === props.trueValue;
    if (Array.isArray(props.modelValue)) return props.modelValue.includes(props.value);
    return !!props.modelValue;
});

function onChange(e) {
    if (props.trueValue !== undefined) {
        emit('update:modelValue', e.target.checked ? props.trueValue : props.falseValue);
        return;
    }
    if (Array.isArray(props.modelValue)) {
        const arr = [...props.modelValue];
        const idx = arr.indexOf(props.value);
        if (e.target.checked && idx === -1) arr.push(props.value);
        if (!e.target.checked && idx > -1) arr.splice(idx, 1);
        emit('update:modelValue', arr);
        return;
    }
    emit('update:modelValue', e.target.checked);
}
</script>
