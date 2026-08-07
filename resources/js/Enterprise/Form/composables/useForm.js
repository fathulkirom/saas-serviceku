import { ref, reactive, computed, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { fieldRegistry } from '../FormRegistry.js';

/**
 * useForm — Universal Form Engine Composable.
 * 
 * Handles: values, validation, dirty tracking, autosave, undo/redo, submit.
 * Reads schema from page.props.formSchema.
 */
export function useForm(options = {}) {
  const page = usePage();
  const schema = computed(() => page.props.formSchema || options.schema || {});
  const fields = computed(() => schema.value?.fields || []);
  const sections = computed(() => schema.value?.sections || []);
  const actions = computed(() => schema.value?.actions || []);

  // ── Form Values ──
  const values = reactive({});
  const originalValues = ref({});
  const errors = reactive({});
  const isSubmitting = ref(false);
  const isSaved = ref(false);
  const lastSavedAt = ref(null);

  // ── Initialize values from schema ──
  function initValues() {
    fields.value.forEach(f => {
      if (!(f.key in values)) {
        values[f.key] = f.value ?? f.default ?? '';
      }
    });
    originalValues.value = { ...values };
  }

  watch(fields, initValues, { immediate: true });

  // ── Dirty Tracking ──
  const dirtyFields = computed(() => {
    return fields.value.filter(f => {
      const current = values[f.key];
      const original = originalValues.value[f.key];
      return JSON.stringify(current) !== JSON.stringify(original);
    });
  });

  const isDirty = computed(() => dirtyFields.value.length > 0);
  const dirtyCount = computed(() => dirtyFields.value.length);

  // ── Validation ──
  function validate(fieldKey = null) {
    const rules = schema.value?.validation?.rules || {};
    const newErrors = {};

    const fieldsToValidate = fieldKey
      ? fields.value.filter(f => f.key === fieldKey)
      : fields.value;

    fieldsToValidate.forEach(f => {
      const fieldRules = rules[f.key] || [];
      const value = values[f.key];

      fieldRules.forEach(rule => {
        const error = checkRule(rule, value, f, values);
        if (error) {
          newErrors[f.key] = newErrors[f.key] || [];
          newErrors[f.key].push(error);
        }
      });

      // Required check
      if (f.required && (value === '' || value === null || value === undefined)) {
        newErrors[f.key] = newErrors[f.key] || [];
        newErrors[f.key].push(`${f.label} wajib diisi.`);
      }
    });

    // Merge errors
    Object.keys(errors).forEach(k => delete errors[k]);
    Object.entries(newErrors).forEach(([k, v]) => {
      errors[k] = v;
    });

    return Object.keys(newErrors).length === 0;
  }

  function checkRule(rule, value, field, allValues) {
    if (typeof rule === 'string') {
      if (rule === 'required' && (!value || value === '')) return `${field.label} wajib diisi.`;
      if (rule === 'email' && value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return 'Format email tidak valid.';
      if (rule === 'numeric' && value && isNaN(Number(value))) return 'Harus berupa angka.';
    }
    return null;
  }

  function clearErrors(fieldKey = null) {
    if (fieldKey) {
      delete errors[fieldKey];
    } else {
      Object.keys(errors).forEach(k => delete errors[k]);
    }
  }

  // ── Field Visibility (conditional) ──
  function isFieldVisible(field) {
    if (field.hidden) return false;
    if (!field.conditions || !field.conditions.length) return true;
    return field.conditions.every(cond => evaluateCondition(cond, values));
  }

  function evaluateCondition(cond, vals) {
    const fieldVal = vals[cond.field];
    switch (cond.operator) {
      case 'eq': return fieldVal === cond.value;
      case 'neq': return fieldVal !== cond.value;
      case 'in': return Array.isArray(cond.value) && cond.value.includes(fieldVal);
      case 'not_in': return Array.isArray(cond.value) && !cond.value.includes(fieldVal);
      case 'gt': return Number(fieldVal) > Number(cond.value);
      case 'lt': return Number(fieldVal) < Number(cond.value);
      case 'filled': return !!fieldVal;
      case 'empty': return !fieldVal;
      default: return true;
    }
  }

  // ── Visible fields ──
  const visibleFields = computed(() => fields.value.filter(f => isFieldVisible(f)));

  // ── Submit ──
  async function submit(action = 'save') {
    if (!validate()) return false;

    isSubmitting.value = true;

    try {
      const endpoint = schema.value?.endpoint || window.location.href;
      const method = schema.value?.method || 'POST';

      const response = await fetch(endpoint, {
        method,
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': page.props.csrf_token || '',
          'Accept': 'application/json',
        },
        body: JSON.stringify({ ...values, _action: action }),
      });

      if (!response.ok) {
        const errData = await response.json().catch(() => ({}));
        if (errData.errors) {
          Object.entries(errData.errors).forEach(([k, msgs]) => {
            errors[k] = Array.isArray(msgs) ? msgs : [msgs];
          });
        }
        throw new Error(errData.message || 'Gagal menyimpan.');
      }

      const result = await response.json();
      isSaved.value = true;
      lastSavedAt.value = new Date();
      originalValues.value = { ...values };

      // Handle redirect
      if (action === 'save_and_close' && result.redirect) {
        router.visit(result.redirect);
      } else if (action === 'save_and_new' && result.redirect) {
        router.visit(result.redirect);
      }

      return result;
    } catch (e) {
      if (!Object.keys(errors).length) {
        errors._form = [e.message || 'Gagal menyimpan.'];
      }
      return false;
    } finally {
      isSubmitting.value = false;
    }
  }

  // ── Undo / Redo ──
  const history = ref([]);
  const historyIndex = ref(-1);

  function pushHistory() {
    const snapshot = JSON.stringify(values);
    history.value = history.value.slice(0, historyIndex.value + 1);
    history.value.push(snapshot);
    historyIndex.value = Math.min(history.value.length - 1, 50);
  }

  function undo() {
    if (historyIndex.value <= 0) return;
    historyIndex.value--;
    Object.assign(values, JSON.parse(history.value[historyIndex.value]));
  }

  function redo() {
    if (historyIndex.value >= history.value.length - 1) return;
    historyIndex.value++;
    Object.assign(values, JSON.parse(history.value[historyIndex.value]));
  }

  const canUndo = computed(() => historyIndex.value > 0);
  const canRedo = computed(() => historyIndex.value < history.value.length - 1);

  // ── Autosave ──
  let autosaveTimer = null;

  function startAutosave(intervalMs = 5000) {
    stopAutosave();
    autosaveTimer = setInterval(() => {
      if (isDirty.value && !isSubmitting.value) {
        submit('autosave');
      }
    }, intervalMs);
  }

  function stopAutosave() {
    if (autosaveTimer) clearInterval(autosaveTimer);
  }

  // ── Field Helper ──
  function getFieldComponent(type) {
    return fieldRegistry.getComponent(type);
  }

  function getFieldDefaults(type) {
    return fieldRegistry.getDefaults(type);
  }

  // ── Reset ──
  function reset() {
    Object.assign(values, originalValues.value);
    clearErrors();
  }

  function discard() {
    reset();
    history.value = [];
    historyIndex.value = -1;
  }

  return {
    // Data
    schema, fields, sections, actions, values, errors,
    visibleFields,

    // State
    isDirty, dirtyCount, dirtyFields,
    isSubmitting, isSaved, lastSavedAt,

    // Validation
    validate, clearErrors,

    // Actions
    submit, reset, discard,

    // Undo/Redo
    undo, redo, canUndo, canRedo, pushHistory,

    // Autosave
    startAutosave, stopAutosave,

    // Field
    getFieldComponent, getFieldDefaults,
    isFieldVisible,

    // History
    history, historyIndex,
  };
}
