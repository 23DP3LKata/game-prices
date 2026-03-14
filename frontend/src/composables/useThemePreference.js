import { ref, watch } from 'vue'

const THEME_STORAGE_KEY = 'ui_theme'

function getInitialTheme() {
  if (typeof window === 'undefined') {
    return 'light'
  }

  const storedTheme = window.localStorage.getItem(THEME_STORAGE_KEY)
  return storedTheme === 'dark' ? 'dark' : 'light'
}

const selectedTheme = ref(getInitialTheme())

watch(selectedTheme, (theme) => {
  const normalizedTheme = theme === 'dark' ? 'dark' : 'light'

  if (normalizedTheme !== theme) {
    selectedTheme.value = normalizedTheme
    return
  }

  if (typeof window !== 'undefined') {
    window.localStorage.setItem(THEME_STORAGE_KEY, normalizedTheme)
  }
})

export function useThemePreference() {
  return selectedTheme
}
