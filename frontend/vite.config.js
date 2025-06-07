// vite.config.js
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'node:url'

export default defineConfig({
  plugins: [vue()],
  
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  },
  
  server: {
    port: 5173,
    host: '0.0.0.0',
    cors: true,
    proxy: {
      '/api/v1': {
        target: 'http://localhost:8000',
        changeOrigin: true,
        secure: false,
        ws: true,
        rewrite: (path) => path,  // No reescribir la ruta
        configure: (proxy, _options) => {
          proxy.on('error', (err, _req, _res) => {
            console.log('❌ Proxy error:', err);
          });
          proxy.on('proxyReq', (proxyReq, req, _res) => {
            console.log('🔄 Proxying:', `${req.method} ${req.url} -> http://localhost:8000${req.url}`);
          });
          proxy.on('proxyRes', (proxyRes, req, _res) => {
            console.log('✅ Proxy response:', proxyRes.statusCode, req.url);
          });
        },
      }
    }
  },
  
  build: {
    outDir: 'dist',
      base: './',
    sourcemap: true,
    rollupOptions: {
      output: {
        manualChunks: {
          'vendor': ['vue', 'vue-router', 'pinia'],
          'ui': ['@headlessui/vue', '@heroicons/vue'],
          'utils': ['axios']
        }
      }
    }
  },
  
  define: {
    __VUE_PROD_DEVTOOLS__: false,
    __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: false
  }
})