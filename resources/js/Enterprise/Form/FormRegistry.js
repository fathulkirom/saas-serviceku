/**
 * ═══════════════════════════════════════════════════════════
 * FORM REGISTRY (Frontend)
 * ═══════════════════════════════════════════════════════════
 * 
 * Maps field types → Vue components.
 * Register custom field types here.
 */

import { markRaw } from 'vue';

class FormFieldRegistry {
  constructor() {
    this._fields = new Map();
  }

  /**
   * Register a field type.
   * @param {string} type - e.g. 'text', 'currency', 'select'
   * @param {object} component - Vue component
   * @param {object} defaults - Default props for this field type
   */
  register(type, component, defaults = {}) {
    this._fields.set(type, { component: markRaw(component), defaults });
    return this;
  }

  /**
   * Register multiple field types.
   */
  registerAll(map) {
    Object.entries(map).forEach(([type, config]) => {
      this.register(type, config.component, config.defaults || {});
    });
    return this;
  }

  /**
   * Get component for a field type.
   */
  getComponent(type) {
    return this._fields.get(type)?.component || null;
  }

  /**
   * Get default props for a field type.
   */
  getDefaults(type) {
    return this._fields.get(type)?.defaults || {};
  }

  /**
   * Check if field type is registered.
   */
  has(type) {
    return this._fields.has(type);
  }

  /**
   * Get all registered field types.
   */
  types() {
    return Array.from(this._fields.keys());
  }
}

// Singleton
export const fieldRegistry = new FormFieldRegistry();

/**
 * ═══════════════════════════════════════════════════════════
 * FORM REGISTRY — Form-level registration.
 * ═══════════════════════════════════════════════════════════
 * 
 * Maps form IDs → form configurations (extra context, custom submit handlers, etc.)
 */
class FormRegistry {
  constructor() {
    this._forms = new Map();
  }

  register(formId, config = {}) {
    this._forms.set(formId, config);
    return this;
  }

  get(formId) {
    return this._forms.get(formId) || {};
  }

  has(formId) {
    return this._forms.has(formId);
  }
}

export const formRegistry = new FormRegistry();
