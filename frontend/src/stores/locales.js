import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'

export const useLocaleStore = defineStore('locale', () => {
  const currentLocale = ref(localStorage.getItem('locale') || 'en')

  const setLocale = (locale) => {
    currentLocale.value = locale
    localStorage.setItem('locale', locale)

    // Actualizar i18n
    const { locale: i18nLocale } = useI18n()
    i18nLocale.value = locale

    // Actualizar idioma del documento
    document.documentElement.lang = locale
  }

  const availableLocales = [
    { code: 'en', name: 'English', flag: '🇺🇸' },
    { code: 'es', name: 'Español', flag: '🇪🇸' },
  ]

  return {
    currentLocale,
    availableLocales,
    setLocale,
  }
})
