<template>
    <footer class="bg-white border-t border-gray-200 mt-auto">
      <div class="container-custom py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
          <!-- Brand Section -->
          <div class="col-span-1 md:col-span-2">
            <div class="flex items-center space-x-2 mb-4">
              <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center">
              <img src="../../../public/logo.png" alt="Logo">
              </div>
              <span class="font-bold text-xl text-gray-900">
                Compitio
              </span>
            </div>
            <p class="text-gray-600 mb-4 max-w-md">
              La plataforma completa para gestionar torneos deportivos, equipos y jugadores de manera eficiente.
            </p>
            <div class="flex space-x-4">
              <a href="#" class="text-gray-400 hover:text-gray-600 transition-colors">
                <span class="sr-only">Facebook</span>
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/>
                </svg>
              </a>
              <a href="#" class="text-gray-400 hover:text-gray-600 transition-colors">
                <span class="sr-only">Twitter</span>
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"/>
                </svg>
              </a>
              <a href="#" class="text-gray-400 hover:text-gray-600 transition-colors">
                <span class="sr-only">GitHub</span>
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/>
                </svg>
              </a>
            </div>
          </div>
  
          <!-- Quick Links -->
          <div>
            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">
              Enlaces Rápidos
            </h3>
            <ul class="space-y-3">
              <li>
                <RouterLink to="/tournaments" class="text-gray-600 hover:text-gray-900 transition-colors">
                  Torneos
                </RouterLink>
              </li>
              <li>
                <RouterLink to="/teams" class="text-gray-600 hover:text-gray-900 transition-colors">
                  Equipos
                </RouterLink>
              </li>
              <li>
                <RouterLink to="/players" class="text-gray-600 hover:text-gray-900 transition-colors">
                  Jugadores
                </RouterLink>
              </li>
              <li>
                <RouterLink to="/matches" class="text-gray-600 hover:text-gray-900 transition-colors">
                  Partidos
                </RouterLink>
              </li>
            </ul>
          </div>
  
          <!-- Support -->
          <div>
            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">
              Soporte
            </h3>
            <ul class="space-y-3">
              <li>
                <a href="#" class="text-gray-600 hover:text-gray-900 transition-colors">
                  Centro de Ayuda
                </a>
              </li>
              <li>
                <a href="#" class="text-gray-600 hover:text-gray-900 transition-colors">
                  Documentación
                </a>
              </li>
              <li>
                <a href="#" class="text-gray-600 hover:text-gray-900 transition-colors">
                  Contáctanos
                </a>
              </li>
              <li>
                <a href="#" class="text-gray-600 hover:text-gray-900 transition-colors">
                  Política de Privacidad
                </a>
              </li>
            </ul>
          </div>
        </div>
  
        <!-- Bottom Section -->
        <div class="mt-8 pt-8 border-t border-gray-200 flex flex-col md:flex-row justify-between items-center">
          <p class="text-gray-500 text-sm">
            © {{ currentYear }} Compitio. Todos los derechos reservados.
          </p>
          
        </div>
      </div>
    </footer>
  </template>
  
  <script>
  /**
   * Application Footer Component
   * Provides consistent footer across all pages
   */
  
  import { computed } from 'vue'
  import { TrophyIcon } from '@heroicons/vue/24/outline'
  
  export default {
    name: 'AppFooter',
    components: {
      TrophyIcon
    },
    setup() {
      const currentYear = computed(() => new Date().getFullYear())
  
      return {
        currentYear
      }
    }
  }
  </script>