/**
 * ═══════════════════════════════════════════════════════════
 * REPORTING REGISTRY (Frontend)
 * ═══════════════════════════════════════════════════════════
 */

class ReportRegistry {
  constructor() {
    this._reports = new Map();
  }

  register(id, config = {}) { this._reports.set(id, config); return this; }
  get(id) { return this._reports.get(id) || {}; }
  has(id) { return this._reports.has(id); }
}

export const reportRegistry = new ReportRegistry();
