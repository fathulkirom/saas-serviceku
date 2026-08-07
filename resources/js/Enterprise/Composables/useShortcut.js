import { onMounted, onUnmounted } from 'vue';

/**
 * Enterprise Keyboard Shortcut System.
 *
 * Usage:
 *   useShortcut('k', () => openSearch(), { ctrl: true })
 *   useShortcut('Escape', () => closeModal())
 *   useShortcut('s', save, { ctrl: true, preventDefault: true })
 *
 * Supports:
 *   - Single key: 'Escape', 'Enter', 'ArrowUp'
 *   - Modifiers: ctrl, shift, alt, meta
 *   - preventDefault option
 *   - Scoped to specific element via `target` ref
 */
export function useShortcut(key, handler, options = {}) {
  const {
    ctrl = false,
    shift = false,
    alt = false,
    meta = false,
    preventDefault = true,
    target = null,
  } = options;

  const normalizedKey = key.toLowerCase();
  const modifierKeys = ['control', 'shift', 'alt', 'meta'];

  function onKeydown(e) {
    // Skip if typing in input/textarea/contenteditable (unless explicitly targeted)
    if (!target) {
      const tag = e.target?.tagName?.toLowerCase();
      const isEditable = tag === 'input' || tag === 'textarea' || tag === 'select' || e.target?.isContentEditable;
      // Allow Escape always, and Ctrl+K (global search)
      if (isEditable && normalizedKey !== 'escape' && !(ctrl && normalizedKey === 'k')) {
        return;
      }
    }

    const keyMatch = e.key.toLowerCase() === normalizedKey;
    const ctrlMatch = !!e.ctrlKey === ctrl;
    const shiftMatch = !!e.shiftKey === shift;
    const altMatch = !!e.altKey === alt;
    const metaMatch = !!e.metaKey === meta;

    if (keyMatch && ctrlMatch && shiftMatch && altMatch && metaMatch) {
      if (preventDefault) e.preventDefault();
      handler(e);
    }
  }

  const el = target || document;
  onMounted(() => el.addEventListener('keydown', onKeydown));
  onUnmounted(() => el.removeEventListener('keydown', onKeydown));

  return { onKeydown };
}

/**
 * Pre-built shortcuts untuk ServiceKU.
 */
export const SHORTCUTS = {
  GLOBAL_SEARCH: { key: 'k', ctrl: true, label: 'Ctrl+K', description: 'Pencarian global' },
  SAVE: { key: 's', ctrl: true, label: 'Ctrl+S', description: 'Simpan' },
  PRINT: { key: 'p', ctrl: true, label: 'Ctrl+P', description: 'Cetak' },
  CLOSE: { key: 'Escape', label: 'Esc', description: 'Tutup modal/drawer' },
  NEXT: { key: 'ArrowRight', label: '→', description: 'Navigasi berikutnya' },
  PREV: { key: 'ArrowLeft', label: '←', description: 'Navigasi sebelumnya' },
  NEW: { key: 'n', ctrl: true, label: 'Ctrl+N', description: 'Buat baru' },
  HELP: { key: '/', label: '/', description: 'Bantuan shortcut' },
};
