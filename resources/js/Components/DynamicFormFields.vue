<template>
    <div v-if="fields.length > 0" class="space-y-4">
        <div v-for="field in fields" :key="field.id" class="space-y-1">
            <label class="text-xs font-semibold" :class="field.is_required ? 'required' : ''">
                {{ field.label }}
                <span v-if="field.is_required" class="text-red-500">*</span>
            </label>

            <!-- Text -->
            <input v-if="field.type === 'text'"
                v-model="formData[fieldKey(field.id)]"
                :required="field.is_required"
                class="input text-sm mt-1"
                :placeholder="field.label" />

            <!-- Number -->
            <input v-else-if="field.type === 'number'"
                v-model.number="formData[fieldKey(field.id)]"
                type="number"
                class="input text-sm mt-1"
                :placeholder="field.label" />

            <!-- Textarea -->
            <textarea v-else-if="field.type === 'textarea'"
                v-model="formData[fieldKey(field.id)]"
                :required="field.is_required"
                class="input text-sm mt-1"
                rows="3"
                :placeholder="field.label"></textarea>

            <!-- Date -->
            <input v-else-if="field.type === 'date'"
                v-model="formData[fieldKey(field.id)]"
                type="date"
                class="input text-sm mt-1" />

            <!-- Dropdown -->
            <select v-else-if="field.type === 'dropdown'"
                v-model="formData[fieldKey(field.id)]"
                :required="field.is_required"
                class="input text-sm mt-1">
                <option value="" disabled>Pilih {{ field.label }}</option>
                <option v-for="opt in (field.options || [])" :key="opt" :value="opt">
                    {{ opt }}
                </option>
            </select>

            <!-- Checkbox -->
            <div v-else-if="field.type === 'checkbox'" class="flex items-center gap-2 mt-1">
                <input type="checkbox"
                    v-model="formData[fieldKey(field.id)]"
                    :true-value="'1'"
                    :false-value="'0'"
                    class="rounded border-gray-300" />
                <span class="text-sm text-gray-600">{{ field.label }}</span>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    fields: { type: Array, default: () => [] },
    formData: { type: Object, default: () => ({}) },
});

function fieldKey(id) {
    return `custom_field_${id}`;
}

function getFormData() {
    const data = {};
    props.fields.forEach(f => {
        const val = props.formData[fieldKey(f.id)];
        if (val !== undefined && val !== '') {
            data[fieldKey(f.id)] = val;
        }
    });
    return data;
}

defineExpose({ getFormData });
</script>
