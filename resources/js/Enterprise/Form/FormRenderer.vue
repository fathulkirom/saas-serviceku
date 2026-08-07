<template>
  <div class="min-h-screen flex flex-col" :style="{ background: 'var(--bg-app)' }">
    
    <!-- ═══════════ FORM TOOLBAR ═══════════ -->
    <FormToolbar
      :title="schema?.title || 'Form'"
      :actions="toolbarActions"
      :isDirty="isDirty"
      :isSubmitting="isSubmitting"
      :canUndo="canUndo"
      :canRedo="canRedo"
      :lastSavedAt="lastSavedAt"
      :dirtyCount="dirtyCount"
      @action="handleAction"
      @undo="undo"
      @redo="redo"
    />

    <!-- ═══════════ FORM BODY ═══════════ -->
    <div class="flex-1 flex overflow-hidden">
      <div class="flex-1 overflow-y-auto">
        <div class="max-w-4xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">

          <!-- Validation Summary -->
          <ValidationSummary :errors="errors" @clear="clearErrors()" />

          <!-- Sections -->
          <div v-for="section in sections" :key="section.id" v-show="section.visible !== false">
            <FormSectionRenderer
              :section="section"
              :fields="getSectionFields(section.id)"
              :values="values"
              :errors="errors"
              :isSubmitting="isSubmitting"
              @update:field="(key, val) => { values[key] = val; pushHistory(); }"
              @blur="(key) => validate(key)"
            />
          </div>

          <!-- Ungrouped fields -->
          <FormSectionRenderer
            v-if="ungroupedFields.length"
            :fields="ungroupedFields"
            :values="values"
            :errors="errors"
            :isSubmitting="isSubmitting"
            :section="{ id: 'main', label: '', cols: 2 }"
            @update:field="(key, val) => { values[key] = val; pushHistory(); }"
            @blur="(key) => validate(key)"
          />
        </div>
      </div>

      <!-- Sidebar (optional) -->
      <div v-if="hasSidebar" class="w-72 flex-shrink-0 border-l overflow-y-auto p-4 hidden xl:block space-y-4"
        :style="{ borderColor: 'var(--border-light)', background: 'var(--bg-card)' }">
        <slot name="sidebar" :values="values" :errors="errors" />
      </div>
    </div>

    <!-- ═══════════ FORM FOOTER ═══════════ -->
    <FormFooter
      :actions="footerActions"
      :isDirty="isDirty"
      :isSubmitting="isSubmitting"
      @action="handleAction"
    />

  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useForm } from '@/Enterprise/Form/composables/useForm.js';
import FormToolbar from './FormToolbar.vue';
import FormSectionRenderer from './FormSection.vue';
import FormFooter from './FormFooter.vue';
import ValidationSummary from './ValidationSummary.vue';

const props = defineProps({
  formSchema: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['submit', 'action']);

const {
  schema, fields, sections, actions, values, errors,
  isDirty, dirtyCount, isSubmitting, lastSavedAt,
  isFieldVisible,
  validate, submit, undo, redo, canUndo, canRedo, pushHistory,
  clearErrors,
} = useForm({ schema: props.formSchema });

// ── Sections → Fields mapping ──
function getSectionFields(sectionId) {
  return fields.value.filter(f => f.section === sectionId && isFieldVisible(f));
}

const ungroupedFields = computed(() =>
  fields.value.filter(f => !f.section && isFieldVisible(f))
);

const hasSidebar = computed(() => !!props.formSchema?.config?.showSidebar);

// ── Actions by position ──
const toolbarActions = computed(() => actions.value.filter(a => a.position !== 'footer'));
const footerActions = computed(() => actions.value.filter(a => a.position === 'footer'));

// ── Action handler ──
function handleAction(actionId) {
  if (actionId === 'save' || actionId === 'save_and_close' || actionId === 'save_and_new' || actionId === 'save_draft') {
    submit(actionId);
  }
  emit('action', actionId, values);
}
</script>
