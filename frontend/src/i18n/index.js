import { createI18n } from 'vue-i18n'
import en from './locales/en.json'
import es from './locales/es.json'

const messages = {
  en,
  es
}

// Detectar idioma del navegador o usar localStorage
const getBrowserLocale = () => {
  const locale = localStorage.getItem('locale') || navigator.language.split('-')[0]
  return ['en', 'es'].includes(locale) ? locale : 'en'
}

const i18n = createI18n({
  legacy: false, // Para usar Composition API
  locale: getBrowserLocale(),
  fallbackLocale: 'en',
  messages,
  globalInjection: true,
  silentTranslationWarn: true, // Silenciar warnings de traducciones faltantes en desarrollo
  silentFallbackWarn: true,
  formatFallbackMessages: true
})

export default i18n

// Función auxiliar para cambiar idioma globalmente
export const changeLocale = (locale) => {
  if (messages[locale]) {
    i18n.global.locale.value = locale
    localStorage.setItem('locale', locale)
    document.documentElement.lang = locale
    return true
  }
  return false
}

// Función para obtener traducciones anidadas
export const getNestedTranslation = (key, fallback = key) => {
  try {
    return i18n.global.t(key)
  } catch (error) {
    console.warn(`Translation key "${key}" not found`)
    return fallback
  }
}