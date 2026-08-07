/**
 * ═══════════════════════════════════════════════════════════
 * DATA REGISTRY (Frontend)
 * ═══════════════════════════════════════════════════════════
 * 
 * Maps column types → cell renderer components.
 * Register custom column renderers here.
 */

import { markRaw } from 'vue';

class ColumnRendererRegistry {
  constructor() {
    this._renderers = new Map();
  }

  register(type, component, defaults = {}) {
    this._renderers.set(type, { component: markRaw(component), defaults });
    return this;
  }

  registerAll(map) {
    Object.entries(map).forEach(([type, config]) => {
      this.register(type, config.component, config.defaults || {});
    });
    return this;
  }

  getComponent(type) {
    return this._renderers.get(type)?.component || null;
  }

  has(type) {
    return this._renderers.has(type);
  }
}

export const columnRendererRegistry = new ColumnRendererRegistry();

/**
 * DataRegistry — Table-level config registry.
 */
class DataRegistry {
  constructor() {
    this._tables = new Map();
  }

  register(tableId, config = {}) {
    this._tables.set(tableId, config);
    return this;
  }

  get(tableId) {
    return this._tables.get(tableId) || {};
  }
}

export const dataRegistry = new DataRegistry();
