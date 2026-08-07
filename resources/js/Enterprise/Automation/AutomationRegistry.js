/**
 * ═══════════════════════════════════════════════════════════
 * AUTOMATION REGISTRY (Frontend)
 * ═══════════════════════════════════════════════════════════
 */

import { markRaw } from 'vue';

class AutomationRegistry {
  constructor() {
    this._triggers = new Map();
    this._actions = new Map();
    this._conditions = new Map();
    this._automations = new Map();
  }

  registerTrigger(type, config = {}) {
    this._triggers.set(type, { label: config.label || type, ...config });
    return this;
  }

  registerAction(type, config = {}) {
    this._actions.set(type, { label: config.label || type, ...config });
    return this;
  }

  registerCondition(operator, config = {}) {
    this._conditions.set(operator, { label: config.label || operator, ...config });
    return this;
  }

  registerAllTriggers(map) { Object.entries(map).forEach(([k, v]) => this.registerTrigger(k, v)); return this; }
  registerAllActions(map) { Object.entries(map).forEach(([k, v]) => this.registerAction(k, v)); return this; }
  registerAllConditions(map) { Object.entries(map).forEach(([k, v]) => this.registerCondition(k, v)); return this; }

  getTrigger(type) { return this._triggers.get(type) || { label: type }; }
  getAction(type) { return this._actions.get(type) || { label: type }; }
  getCondition(op) { return this._conditions.get(op) || { label: op }; }

  allTriggers() { return Array.from(this._triggers.entries()).map(([k, v]) => ({ value: k, ...v })); }
  allActions() { return Array.from(this._actions.entries()).map(([k, v]) => ({ value: k, ...v })); }
  allConditions() { return Array.from(this._conditions.entries()).map(([k, v]) => ({ value: k, ...v })); }
}

export const automationRegistry = new AutomationRegistry();

// Register defaults
automationRegistry.registerAllTriggers({
  'record.created': { label: 'Record Created', icon: '📝', category: 'Record' },
  'record.updated': { label: 'Record Updated', icon: '✏️', category: 'Record' },
  'record.deleted': { label: 'Record Deleted', icon: '🗑️', category: 'Record' },
  'status.changed': { label: 'Status Changed', icon: '🔄', category: 'Status' },
  'field.changed': { label: 'Field Changed', icon: '📋', category: 'Status' },
  'schedule': { label: 'Schedule', icon: '⏰', category: 'Time' },
  'webhook': { label: 'Webhook', icon: '🌐', category: 'External' },
  'manual': { label: 'Manual', icon: '👆', category: 'External' },
  'payment.success': { label: 'Payment Success', icon: '💳', category: 'Business' },
  'invoice.paid': { label: 'Invoice Paid', icon: '💰', category: 'Business' },
  'stock.low': { label: 'Stock Low', icon: '📦', category: 'Business' },
  'service.finished': { label: 'Service Finished', icon: '✅', category: 'Business' },
  'customer.created': { label: 'Customer Created', icon: '👤', category: 'Business' },
});

automationRegistry.registerAllActions({
  'update_record': { label: 'Update Record', icon: '✏️', category: 'Record' },
  'create_record': { label: 'Create Record', icon: '➕', category: 'Record' },
  'delete_record': { label: 'Delete Record', icon: '🗑️', category: 'Record', danger: true },
  'send_whatsapp': { label: 'Send WhatsApp', icon: '💬', category: 'Communication' },
  'send_email': { label: 'Send Email', icon: '📧', category: 'Communication' },
  'push_notification': { label: 'Push Notification', icon: '🔔', category: 'Communication' },
  'create_task': { label: 'Create Task', icon: '📋', category: 'Task' },
  'add_timeline': { label: 'Add Timeline', icon: '🕐', category: 'Record' },
  'create_activity': { label: 'Create Activity', icon: '📊', category: 'Record' },
  'change_status': { label: 'Change Status', icon: '🔄', category: 'Record' },
  'assign_technician': { label: 'Assign Technician', icon: '👨‍🔧', category: 'Record' },
  'webhook': { label: 'Call Webhook', icon: '🌐', category: 'External' },
});

automationRegistry.registerAllConditions({
  eq: { label: 'Equals', type: 'comparison' },
  neq: { label: 'Not Equals', type: 'comparison' },
  gt: { label: 'Greater Than', type: 'comparison' },
  lt: { label: 'Less Than', type: 'comparison' },
  contains: { label: 'Contains', type: 'text' },
  empty: { label: 'Is Empty', type: 'state' },
  not_empty: { label: 'Is Not Empty', type: 'state' },
  in: { label: 'In List', type: 'list' },
  role: { label: 'User Role Is', type: 'user' },
  permission: { label: 'Has Permission', type: 'user' },
  branch: { label: 'Branch Is', type: 'org' },
});
