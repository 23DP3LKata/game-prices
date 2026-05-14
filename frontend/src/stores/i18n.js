import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import en from '../i18n/en.json'
import lv from '../i18n/lv.json'

const messages = {
  ENG: en,
  LV: lv,
}

export const useI18nStore = defineStore('i18n', () => {
  const selected = ref(localStorage.getItem('lang') || 'LV')

  const setLanguage = (lang) => {
    if (!messages[lang]) return
    selected.value = lang
    localStorage.setItem('lang', lang)
  }

  const t = (key) => {
    const dict = messages[selected.value] || messages.ENG
    return dict[key] ?? key
  }

  return { selected, setLanguage, t }
})
