// 🔧 CONFIGURACIÓN CORRECTA DE VITE PARA VUE.JS STANDALONE

// ==========================================
// PROBLEMA IDENTIFICADO:
// ==========================================
// Tu vite.config.js actual usa "laravel-vite-plugin" que es para:
// - Laravel con Blade templates + Vite
// - NO para Vue.js standalone

// Necesitas la configuración para Vue.js puro + Vite

// ==========================================
// SOLUCIÓN: REEMPLAZAR vite.config.js COMPLETO
// ==========================================

// frontend/vite.config.js (REEMPLAZAR TODO EL CONTENIDO)
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'node:url'

export default defineConfig({
    plugins: [
        vue({
            template: {
                compilerOptions: {
                    // Tratar todos los tags que empiecen con ion- como custom elements
                    isCustomElement: (tag) => tag.startsWith('ion-')
                }
            }
        })
    ],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./src', import.meta.url))
        }
    },
    server: {
        port: 5173,        // ← Cambiar aquí el puerto
        host: true,        // Permite acceso desde otras IPs
        open: true,        // Abre automáticamente el navegador
        cors: true,        // Habilita CORS
        proxy: {
            '/api': {
                target: 'http://localhost:8000',
                changeOrigin: true,
                secure: false
            }
        }
    },
    build: {
        outDir: 'dist',
        assetsDir: 'assets',
        sourcemap: true
    },
    css: {
        postcss: './postcss.config.js'
    }
})