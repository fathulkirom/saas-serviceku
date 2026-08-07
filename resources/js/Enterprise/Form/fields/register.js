import { fieldRegistry } from '../FormRegistry.js';

/**
 * ═══════════════════════════════════════════════════════════
 * DEFAULT FIELD TYPE REGISTRATIONS
 * ═══════════════════════════════════════════════════════════
 * 
 * All 40+ field types are registered here.
 * Each maps a type string → Vue component + defaults.
 * 
 * To add a custom field type: fieldRegistry.register('mytype', MyComponent)
 */

// ── Reuse existing Enterprise form components where possible ──
import SkFloatingInput from '@/Enterprise/Components/Form/FloatingInput.vue';
import SkAutocomplete from '@/Enterprise/Components/Form/Autocomplete.vue';
import SkSwitch from '@/Enterprise/Components/Form/Switch.vue';
import SkCurrencyInput from '@/Enterprise/Components/Form/CurrencyInput.vue';
import SkFileUpload from '@/Enterprise/Components/Form/FileUpload.vue';
import KSelect from '@/Components/KSelect.vue';
import KTextarea from '@/Components/KTextarea.vue';
import KCheckbox from '@/Components/KCheckbox.vue';

// ── Simple input wrapper (text, number, email, phone, password, etc.) ──
const TextInput = {
  template: `<input :value="modelValue" @input="$emit('update:modelValue', $event.target.value)" @blur="$emit('blur')" :type="type || 'text'" :placeholder="placeholder" :disabled="disabled" :readonly="readonly" :min="min" :max="max" :step="step" :maxlength="maxLength" class="w-full rounded-xl border transition-all duration-200 outline-none h-11 text-sm px-3.5 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-50" :style="{ background: disabled ? 'var(--bg-hover)' : 'var(--bg-input)', color: 'var(--text-primary)', borderColor: error ? 'var(--danger)' : 'var(--border-color)' }" />`,
  props: ['modelValue', 'type', 'placeholder', 'disabled', 'readonly', 'min', 'max', 'step', 'maxLength', 'error', 'prefix', 'suffix'],
  emits: ['update:modelValue', 'blur'],
};

// ── Textarea ──
const TextareaInput = {
  template: `<KTextarea :modelValue="modelValue" @update:modelValue="$emit('update:modelValue', $event)" @blur="$emit('blur')" :placeholder="placeholder" :disabled="disabled" :readonly="readonly" :rows="rows || 3" :style="{ borderColor: error ? 'var(--danger)' : undefined }" />`,
  components: { KTextarea },
  props: ['modelValue', 'placeholder', 'disabled', 'readonly', 'rows', 'error'],
  emits: ['update:modelValue', 'blur'],
};

// ── Select ──
const SelectInput = {
  template: `<KSelect :modelValue="modelValue" @update:modelValue="$emit('update:modelValue', $event)" :disabled="disabled" :style="{ borderColor: error ? 'var(--danger)' : undefined }"><option value="">{{ placeholder || 'Pilih...' }}</option><option v-for="opt in options" :key="opt.value ?? opt" :value="opt.value ?? opt">{{ opt.label ?? opt }}</option></KSelect>`,
  components: { KSelect },
  props: ['modelValue', 'options', 'placeholder', 'disabled', 'error'],
  emits: ['update:modelValue', 'blur'],
};

// ── Date Input ──
const DateInput = {
  template: `<input type="date" :value="modelValue" @input="$emit('update:modelValue', $event.target.value)" @blur="$emit('blur')" :disabled="disabled" class="w-full rounded-xl border transition-all duration-200 outline-none h-11 text-sm px-3.5 focus:border-indigo-400" :style="{ background: 'var(--bg-input)', color: 'var(--text-primary)', borderColor: error ? 'var(--danger)' : 'var(--border-color)' }" />`,
  props: ['modelValue', 'disabled', 'error'],
  emits: ['update:modelValue', 'blur'],
};

// ── Color Picker ──
const ColorInput = {
  template: `<div class="flex items-center gap-2"><input type="color" :value="modelValue || '#2563EB'" @input="$emit('update:modelValue', $event.target.value)" class="w-11 h-11 rounded-lg border cursor-pointer" :style="{ borderColor: 'var(--border-color)' }" /><input :value="modelValue" @input="$emit('update:modelValue', $event.target.value)" class="flex-1 rounded-xl border px-3 py-2 text-sm font-mono" :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-input)' }" /></div>`,
  props: ['modelValue'],
  emits: ['update:modelValue'],
};

// ═══════════════════════════════════════════════════════════
// REGISTER ALL DEFAULT FIELD TYPES
// ═══════════════════════════════════════════════════════════

fieldRegistry.registerAll({
  text:       { component: TextInput, defaults: { type: 'text' } },
  number:     { component: TextInput, defaults: { type: 'number' } },
  email:      { component: TextInput, defaults: { type: 'email' } },
  password:   { component: TextInput, defaults: { type: 'password' } },
  phone:      { component: TextInput, defaults: { type: 'tel' } },
  url:        { component: TextInput, defaults: { type: 'url' } },
  color:      { component: ColorInput },
  date:       { component: DateInput },
  time:       { component: TextInput, defaults: { type: 'time' } },
  datetime:   { component: TextInput, defaults: { type: 'datetime-local' } },
  textarea:   { component: TextareaInput },
  select:     { component: SelectInput },
  currency:   { component: SkCurrencyInput },
  floating:   { component: SkFloatingInput },
  autocomplete:{ component: SkAutocomplete },
  switch:     { component: SkSwitch },
  checkbox:   { component: KCheckbox },
  file:       { component: SkFileUpload },
  photo:      { component: SkFileUpload, defaults: { accept: 'image/*', preview: true, multiple: false } },
  gallery:    { component: SkFileUpload, defaults: { accept: 'image/*', multiple: true } },
  otp:        { component: TextInput, defaults: { type: 'text', maxLength: 6, placeholder: '000000' } },
  barcode:    { component: TextInput, defaults: { type: 'text' } },
  json:       { component: TextareaInput, defaults: { rows: 5 } },
  markdown:   { component: TextareaInput, defaults: { rows: 8 } },
  code:       { component: TextareaInput, defaults: { rows: 10 } },
  signature:  { component: SkFileUpload, defaults: { accept: 'image/*', preview: true, multiple: false, label: 'Tanda Tangan' } },
  map:        { component: TextInput, defaults: { type: 'text', placeholder: 'Latitude, Longitude' } },
});

export default fieldRegistry;
