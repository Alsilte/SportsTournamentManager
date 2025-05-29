import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useLocaleStore = defineStore('locale', () => {
  // DEFINIR availableLocales PRIMERO (antes de usarlo)
  const availableLocales = [
    { 
      code: 'en', 
      name: 'English', 
      nativeName: 'English',
      flag: '🇺🇸',
      dir: 'ltr'
    },
    { 
      code: 'es', 
      name: 'Español', 
      nativeName: 'Español',
      flag: '🇪🇸',
      dir: 'ltr'
    }
  ]

  /**
   * Obtener idioma por defecto del navegador
   */
  function getDefaultLocale() {
    const browserLocale = navigator.language.split('-')[0]
    const supportedCodes = availableLocales.map(l => l.code)
    return supportedCodes.includes(browserLocale) ? browserLocale : 'en'
  }
  
  // AHORA SÍ definir currentLocale (después de availableLocales)
  const currentLocale = ref(localStorage.getItem('locale') || getDefaultLocale())
  
  /**
   * Cambiar idioma con persistencia
   */
  const setLocale = (locale) => {
    if (!availableLocales.find(l => l.code === locale)) {
      console.warn(`Locale '${locale}' not supported`)
      return false
    }
    
    currentLocale.value = locale
    localStorage.setItem('locale', locale)
    document.documentElement.lang = locale
    
    return true
  }
  
  // Getters computados
  const currentLocaleInfo = computed(() => 
    availableLocales.find(l => l.code === currentLocale.value) || availableLocales[0]
  )
  
  const isRTL = computed(() => 
    currentLocaleInfo.value.dir === 'rtl'
  )
  
  const currentFlag = computed(() => 
    currentLocaleInfo.value.flag
  )
  
  const currentName = computed(() => 
    currentLocaleInfo.value.name
  )

  return {
    // Estado
    currentLocale,
    availableLocales,
    
    // Getters
    currentLocaleInfo,
    currentFlag,
    currentName,
    isRTL,
    
    // Acciones
    setLocale
  }
})