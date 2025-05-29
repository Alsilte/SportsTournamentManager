/**
 * Main application entry point
 * Initializes Vue app with router, store, i18n and global configurations
 */

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import i18n from './i18n'

import App from './App.vue'
import router from './router'

// Import global styles
import './assets/main.css'

// Create Vue application instance
const app = createApp(App)

// Install plugins
app.use(createPinia())
app.use(router)
app.use(i18n) // Add i18n support

// Global error handler
app.config.errorHandler = (err, vm, info) => {
  console.error('Global error:', err)
  console.error('Component info:', info)
}

// Initialize locale store after mounting
app.config.globalProperties.$initLocale = () => {
  const { useLocaleStore } = require('@/stores/locale')
  const localeStore = useLocaleStore()
  localeStore.initialize()
}

// Mount the application
app.mount('#app')

// Initialize locale after app is mounted
setTimeout(() => {
  const { useLocaleStore } = require('@/stores/locale')
  const localeStore = useLocaleStore()
  
  // Sync i18n with locale store
  const { locale } = i18n.global
  locale.value = localeStore.currentLocale
}, 0)