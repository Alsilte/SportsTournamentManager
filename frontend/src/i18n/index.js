import { createI18n } from 'vue-i18n'
import en from './locales/en.json'
import es from './locales/es.json'

const messages = {
  en,
  es,
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
})

export default i18n
