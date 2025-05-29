import { createApp } from 'vue'
import { createPinia } from 'pinia'
import i18n from './i18n'

import App from './App.vue'
import router from './router'
import './assets/main.css'

const app = createApp(App)

app.use(createPinia())
app.use(router)
app.use(i18n) // ← Añadir i18n

app.config.errorHandler = (err, vm, info) => {
  console.error('Global error:', err)
  console.error('Component info:', info)
}

app.mount('#app')
