/**
 * ═══════════════════════════════════════════════════════════
 * DASHBOARD WIDGET REGISTRY
 * ═══════════════════════════════════════════════════════════
 *
 * Setiap widget mendaftarkan diri dengan persyaratan:
 *   - roles: ['owner', 'admin', ...]       → role yang boleh melihat
 *   - features: ['services', 'sales', ...]  → minimal satu harus 'full'/'read_only'
 *   - permissions: ['manage_finance', ...]  → minimal satu harus dimiliki
 *   - businessTypes: ['full_service', ...]  → tipe bisnis yang didukung
 *   - denyBusinessTypes: ['retail_only']    → tipe bisnis yang DITOLAK
 *
 * Registry.resolve() mengembalikan daftar widget yang boleh ditampilkan.
 *
 * Usage:
 *   import { registry } from '@/Enterprise/Dashboard/DashboardWidgetRegistry'
 *   const widgets = registry.resolve(userRole, planAccess, rolePermissions, businessType)
 */

import { markRaw } from 'vue';

class DashboardWidgetRegistry {
  constructor() {
    /** @type {WidgetDefinition[]} */
    this._widgets = [];
  }

  /**
   * Register a widget.
   * @param {WidgetDefinition} def
   */
  register(def) {
    // Validate
    if (!def.id) throw new Error('Widget must have an "id"');
    if (!def.component) throw new Error('Widget must have a "component"');
    if (!def.title) def.title = def.id;

    // Defaults
    def.roles = def.roles || [];                       // empty = all roles
    def.features = def.features || [];                 // empty = no feature gate
    def.permissions = def.permissions || [];           // empty = no permission gate
    def.businessTypes = def.businessTypes || [];       // empty = all types
    def.denyBusinessTypes = def.denyBusinessTypes || []; // empty = none denied
    def.denyRoles = def.denyRoles || [];               // empty = none denied
    def.priority = def.priority || 50;                 // default priority
    def.cols = def.cols || 1;
    def.rows = def.rows || 1;
    def.static = def.static !== false;                 // static = always shows, no feature/permission gate

    this._widgets.push(markRaw(def));
    return this;
  }

  /**
   * Register multiple widgets at once.
   * @param {WidgetDefinition[]} defs
   */
  registerAll(defs) {
    defs.forEach(d => this.register(d));
    return this;
  }

  /**
   * Resolve visible widgets for current user context.
   *
   * @param {string} userRole          - e.g. 'owner', 'technician'
   * @param {Object} planAccess        - e.g. { services: 'full', sales: 'none' }
   * @param {string[]} rolePermissions - e.g. ['manage_finance', 'work_on_services']
   * @param {string} businessType      - e.g. 'full_service', 'retail_only'
   * @returns {WidgetDefinition[]} sorted by priority
   */
  resolve(userRole, planAccess = {}, rolePermissions = [], businessType = 'full_service') {
    return this._widgets
      .filter(w => this._isVisible(w, userRole, planAccess, rolePermissions, businessType))
      .sort((a, b) => a.priority - b.priority);
  }

  /**
   * Get all registered widgets (for debugging).
   */
  getAll() {
    return [...this._widgets];
  }

  // ── Private ──

  _isVisible(widget, userRole, planAccess, rolePermissions, businessType) {
    // Static widgets always visible
    if (widget.static) return true;

    // Role deny check
    if (widget.denyRoles.length > 0 && widget.denyRoles.includes(userRole)) {
      return false;
    }

    // Role allow check (if specified)
    if (widget.roles.length > 0 && !widget.roles.includes(userRole)) {
      return false;
    }

    // Business type deny
    if (widget.denyBusinessTypes.length > 0 && widget.denyBusinessTypes.includes(businessType)) {
      return false;
    }

    // Business type allow (if specified)
    if (widget.businessTypes.length > 0 && !widget.businessTypes.includes(businessType)) {
      return false;
    }

    // Feature gate: at least one feature must be accessible
    if (widget.features.length > 0) {
      const hasFeature = widget.features.some(f => {
        const level = planAccess[f];
        return level === 'full' || level === 'read_only';
      });
      if (!hasFeature) return false;
    }

    // Permission gate: at least one permission must be held
    if (widget.permissions.length > 0) {
      const hasPermission = widget.permissions.some(p => rolePermissions.includes(p));
      if (!hasPermission) return false;
    }

    return true;
  }
}

// Singleton
export const registry = new DashboardWidgetRegistry();

/**
 * @typedef {Object} WidgetDefinition
 * @property {string} id              - Unique widget ID
 * @property {string} title           - Display title
 * @property {Object} component       - Vue component (markRaw'd)
 * @property {string[]} [roles]       - Allowed roles (empty = all)
 * @property {string[]} [denyRoles]   - Denied roles
 * @property {string[]} [features]    - Required feature keys
 * @property {string[]} [permissions] - Required permission keys
 * @property {string[]} [businessTypes]   - Allowed business types
 * @property {string[]} [denyBusinessTypes] - Denied business types
 * @property {number} [priority=50]   - Sort priority (lower = first)
 * @property {number} [cols=1]        - Grid columns span
 * @property {number} [rows=1]        - Grid rows span
 * @property {boolean} [static=true]  - If true, bypasses feature/permission gate
 * @property {Object} [props]         - Extra props to pass to widget
 */
