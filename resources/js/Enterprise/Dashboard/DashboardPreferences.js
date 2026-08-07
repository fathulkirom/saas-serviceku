/**
 * ═══════════════════════════════════════════════════════════
 * DASHBOARD PREFERENCES (localStorage)
 * ═══════════════════════════════════════════════════════════
 *
 * Menyimpan preferensi dashboard per user:
 *   - Widget mana yang ditampilkan/disembunyikan
 *   - Urutan widget
 *   - Ukuran widget (cols)
 *
 * Tersimpan di localStorage dengan key: `sk-dash-prefs-{userId}`
 */

const STORAGE_PREFIX = 'sk-dash-prefs-';

function getStorageKey(userId) {
  return STORAGE_PREFIX + (userId || 'anonymous');
}

/**
 * Load preferences from localStorage.
 * @param {string|number} userId
 * @returns {Object} { hidden: string[], order: string[], sizes: Object }
 */
export function loadPreferences(userId) {
  try {
    const raw = localStorage.getItem(getStorageKey(userId));
    if (!raw) return { hidden: [], order: [], sizes: {} };
    return JSON.parse(raw);
  } catch {
    return { hidden: [], order: [], sizes: {} };
  }
}

/**
 * Save preferences to localStorage.
 * @param {string|number} userId
 * @param {Object} prefs
 */
export function savePreferences(userId, prefs) {
  try {
    localStorage.setItem(getStorageKey(userId), JSON.stringify(prefs));
  } catch {
    // localStorage full or unavailable
  }
}

/**
 * Toggle widget visibility.
 */
export function toggleWidget(userId, widgetId) {
  const prefs = loadPreferences(userId);
  const idx = prefs.hidden.indexOf(widgetId);
  if (idx >= 0) {
    prefs.hidden.splice(idx, 1);
  } else {
    prefs.hidden.push(widgetId);
  }
  savePreferences(userId, prefs);
  return prefs;
}

/**
 * Check if widget is hidden.
 */
export function isWidgetHidden(userId, widgetId) {
  const prefs = loadPreferences(userId);
  return prefs.hidden.includes(widgetId);
}

/**
 * Save widget order.
 */
export function saveWidgetOrder(userId, orderedIds) {
  const prefs = loadPreferences(userId);
  prefs.order = orderedIds;
  savePreferences(userId, prefs);
  return prefs;
}

/**
 * Save widget size (cols).
 */
export function saveWidgetSize(userId, widgetId, cols) {
  const prefs = loadPreferences(userId);
  prefs.sizes = prefs.sizes || {};
  prefs.sizes[widgetId] = cols;
  savePreferences(userId, prefs);
  return prefs;
}

/**
 * Reset all preferences.
 */
export function resetPreferences(userId) {
  localStorage.removeItem(getStorageKey(userId));
}
