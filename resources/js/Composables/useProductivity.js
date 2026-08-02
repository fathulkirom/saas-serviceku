import { ref, watch, onMounted } from 'vue'

/**
 * useRecentItems — localStorage-based recent items tracker.
 * Usage: const { items, addItem } = useRecentItems('recent-customers', 20)
 */
export function useRecentItems(key, maxItems = 20) {
  const items = ref(JSON.parse(localStorage.getItem(key) || '[]'))

  function addItem(item) {
    items.value = [item, ...items.value.filter(i => i.id !== item.id)].slice(0, maxItems)
  }

  watch(items, (val) => localStorage.setItem(key, JSON.stringify(val)), { deep: true })

  function removeItem(id) { items.value = items.value.filter(i => i.id !== id) }
  function clear() { items.value = []; localStorage.removeItem(key) }

  return { items, addItem, removeItem, clear }
}

/**
 * useFavorites — localStorage-based favorites.
 */
export function useFavorites(key) {
  const favorites = ref(JSON.parse(localStorage.getItem(key) || '[]'))

  function toggle(item) {
    const idx = favorites.value.findIndex(f => f.id === item.id)
    if (idx >= 0) favorites.value.splice(idx, 1)
    else favorites.value.push(item)
  }

  function isFavorite(id) { return favorites.value.some(f => f.id === id) }

  watch(favorites, (val) => localStorage.setItem(key, JSON.stringify(val)), { deep: true })

  return { favorites, toggle, isFavorite }
}

/**
 * useAutoSave — auto-save form drafts to localStorage every N ms.
 */
export function useAutoSave(formKey, formData, interval = 3000) {
  const isSaving = ref(false)
  const lastSaved = ref(null)
  let timer = null

  function start() {
    timer = setInterval(() => {
      if (formData && Object.keys(formData).length > 0) {
        isSaving.value = true
        localStorage.setItem(`draft:${formKey}`, JSON.stringify(formData))
        lastSaved.value = new Date()
        setTimeout(() => isSaving.value = false, 500)
      }
    }, interval)
  }

  function stop() { clearInterval(timer) }
  function loadDraft() {
    const saved = localStorage.getItem(`draft:${formKey}`)
    return saved ? JSON.parse(saved) : null
  }
  function clearDraft() { localStorage.removeItem(`draft:${formKey}`) }

  onMounted(() => start())

  return { isSaving, lastSaved, start, stop, loadDraft, clearDraft }
}

/**
 * useSmartSuggestions — context-aware suggestions based on history.
 */
export function useSmartSuggestions(context, fetchFn) {
  const suggestions = ref([])
  const loading = ref(false)

  async function load(query) {
    if (!query || query.length < 2) { suggestions.value = []; return }
    loading.value = true
    try {
      const result = await fetchFn(query, context)
      suggestions.value = result.slice(0, 8)
    } finally {
      loading.value = false
    }
  }

  return { suggestions, loading, load }
}

/**
 * useKeyboardShortcuts — register global keyboard shortcuts.
 */
export function useKeyboardShortcuts() {
  const shortcuts = new Map()

  function register(key, handler, description = '') {
    shortcuts.set(key.toLowerCase(), { handler, description })
  }

  function handleKeydown(e) {
    // Don't trigger when typing in inputs
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT') {
      if (e.key === 'Escape') e.target.blur()
      return
    }

    const key = (e.ctrlKey || e.metaKey ? 'ctrl+' : '') + e.key.toLowerCase()
    const shortcut = shortcuts.get(key)
    if (shortcut) { e.preventDefault(); shortcut.handler() }
  }

  onMounted(() => window.addEventListener('keydown', handleKeydown))
  // Cleanup implicit via component unmount

  return { register, shortcuts }
}
