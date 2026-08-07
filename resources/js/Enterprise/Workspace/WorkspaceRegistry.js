/**
 * ═══════════════════════════════════════════════════════════
 * WORKSPACE REGISTRY (Frontend)
 * ═══════════════════════════════════════════════════════════
 * 
 * Frontend mirror of the backend WorkspaceRegistry.
 * Modules register their workspace UI configuration here.
 * 
 * Each registration includes:
 *   - Tab components (rendered by the workspace engine)
 *   - Sidebar widgets (rendered in the sidebar panel)
 *   - Inspector sections (rendered in the inspector panel)
 *   - Action handlers (executed when toolbar actions are clicked)
 *   - Shortcut handlers
 */

import { markRaw } from 'vue';

class FrontendWorkspaceRegistry {
  constructor() {
    /** @type {Map<string, WorkspaceUIConfig>} */
    this._configs = new Map();
  }

  /**
   * Register UI configuration for a workspace module.
   * 
   * @param {string} id - Module ID (e.g. 'service', 'inventory')
   * @param {WorkspaceUIConfig} config
   */
  register(id, config) {
    if (!id) throw new Error('Workspace ID is required');
    if (!config.tabs) config.tabs = {};
    if (!config.sidebarWidgets) config.sidebarWidgets = {};
    if (!config.inspectorSections) config.inspectorSections = {};
    if (!config.actionHandlers) config.actionHandlers = {};
    if (!config.shortcutHandlers) config.shortcutHandlers = {};

    this._configs.set(id, markRaw(config));
    return this;
  }

  /**
   * Get tab component by workspace + tab ID.
   * @returns {object|null} Vue component
   */
  getTabComponent(workspaceId, tabId) {
    return this._configs.get(workspaceId)?.tabs[tabId] || null;
  }

  /**
   * Get sidebar widget component by workspace + widget ID.
   */
  getSidebarWidget(workspaceId, widgetId) {
    return this._configs.get(workspaceId)?.sidebarWidgets[widgetId] || null;
  }

  /**
   * Get inspector section component.
   */
  getInspectorSection(workspaceId, sectionId) {
    return this._configs.get(workspaceId)?.inspectorSections[sectionId] || null;
  }

  /**
   * Get action handler function.
   */
  getActionHandler(workspaceId, actionId) {
    return this._configs.get(workspaceId)?.actionHandlers[actionId] || null;
  }

  /**
   * Get shortcut handler.
   */
  getShortcutHandler(workspaceId, shortcutAction) {
    return this._configs.get(workspaceId)?.shortcutHandlers[shortcutAction] || null;
  }

  /**
   * Check if a workspace is registered.
   */
  has(workspaceId) {
    return this._configs.has(workspaceId);
  }

  /**
   * Get the full config for a workspace.
   */
  get(workspaceId) {
    return this._configs.get(workspaceId) || null;
  }
}

// Singleton
export const workspaceRegistry = new FrontendWorkspaceRegistry();

/**
 * @typedef {Object} WorkspaceUIConfig
 * @property {Object<string, object>} tabs - Tab ID → Vue component
 * @property {Object<string, object>} sidebarWidgets - Widget ID → Vue component
 * @property {Object<string, object>} inspectorSections - Section ID → Vue component
 * @property {Object<string, Function>} actionHandlers - Action ID → handler function
 * @property {Object<string, Function>} shortcutHandlers - Shortcut action → handler
 */
