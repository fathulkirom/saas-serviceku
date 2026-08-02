<template>
    <input
        ref="rootEl"
        type="radio"
        :checked="checked"
        :value="value"
        :disabled="disabled"
        @change="onChange"
    />
</template>

<script setup>
import { ref, computed } from 'vue';

/**
 * Radio reusable (standar). modelValue = nilai terpilih; value = nilai opsi ini.
 * Class/style/name dari parent diteruskan (inheritAttrs).
 */
const props = defineProps({
    modelValue: { type: [String, Number], default: '' },
    value: { type: [String, Number], default: undefined },
    disabled: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);
const rootEl = ref(null);

defineExpose({
    focus: () => rootEl.value?.focus(),
    blur: () => rootEl.value?.blur(),
    click: () => rootEl.value?.click(),
});

const checked = computed(() => props.modelValue === props.value);

function onChange() {
    emit('update:modelValue', props.value);
}
</script>
