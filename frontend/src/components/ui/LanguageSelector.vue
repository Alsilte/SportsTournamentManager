<template>
    <div class="relative" :class="containerClass">
      <Menu as="div" class="relative inline-block text-left">
        <div>
          <MenuButton 
            class="inline-flex w-full justify-center items-center gap-x-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors duration-200"
            :class="buttonClass"
          >
            <span class="text-lg">{{ getCurrentFlag() }}</span>
            <span v-if="variant !== 'icon-only'" class="ml-1 hidden sm:block">{{ getCurrentName() }}</span>
            <ChevronDownIcon v-if="variant !== 'icon-only'" class="-mr-1 h-4 w-4 text-gray-400" />
          </MenuButton>
        </div>
  
        <transition
          enter-active-class="transition ease-out duration-100"
          enter-from-class="transform opacity-0 scale-95"
          enter-to-class="transform opacity-100 scale-100"
          leave-active-class="transition ease-in duration-75"
          leave-from-class="transform opacity-100 scale-100"
          leave-to-class="transform opacity-0 scale-95"
        >
          <MenuItems class="absolute right-0 z-50 mt-2 w-48 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
            <div class="py-1">
              <!-- Header del menú -->
              <div class="px-4 py-2 border-b border-gray-100">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('common.language') }}
                </p>
              </div>
              
              <!-- Opciones de idioma -->
              <MenuItem 
                v-for="locale in localeStore.availableLocales" 
                :key="locale.code"
                v-slot="{ active }"
              >
                <button
                  @click="changeLocale(locale.code)"
                  :class="[
                    'group flex w-full items-center px-4 py-3 text-sm transition-colors duration-150',
                    active ? 'bg-gray-50 text-gray-900' : 'text-gray-700',
                    localeStore.currentLocale === locale.code 
                      ? 'bg-primary-50 text-primary-700 font-medium' 
                      : 'hover:bg-gray-50'
                  ]"
                  :disabled="isChanging"
                >
                  <!-- Flag y nombre -->
                  <div class="flex items-center flex-1">
                    <span class="text-xl mr-3">{{ locale.flag }}</span>
                    <div class="flex flex-col items-start">
                      <span class="font-medium">{{ locale.name }}</span>
                      <span class="text-xs text-gray-500">{{ locale.nativeName }}</span>
                    </div>
                  </div>
                  
                  <!-- Indicador de selección -->
                  <CheckIcon 
                    v-if="localeStore.currentLocale === locale.code"
                    class="ml-2 h-4 w-4 text-primary-600 flex-shrink-0"
                  />
                  
                  <!-- Loading indicator -->
                  <div
                    v-if="isChanging && changingTo === locale.code"
                    class="ml-2 w-4 h-4 border-2 border-primary-600 border-t-transparent rounded-full animate-spin flex-shrink-0"
                  ></div>
                </button>
              </MenuItem>
            </div>
          </MenuItems>
        </transition>
      </Menu>
    </div>
  </template>
  
  <script>
  /**
   * Language Selector Component
   * Provides an elegant dropdown to switch between available languages
   * with smooth transitions and visual feedback
   */
  
  import { ref, computed } from 'vue'
  import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue'
  import { ChevronDownIcon, CheckIcon } from '@heroicons/vue/24/outline'
  import { useLocaleStore } from '@/stores/locale'
  import { useI18n } from 'vue-i18n'
  
  export default {
    name: 'LanguageSelector',
    components: {
      Menu,
      MenuButton, 
      MenuItems,
      MenuItem,
      ChevronDownIcon,
      CheckIcon
    },
    props: {
      // Variant del botón: 'default' | 'compact' | 'icon-only'
      variant: {
        type: String,
        default: 'default',
        validator: (value) => ['default', 'compact', 'icon-only'].includes(value)
      },
      // Mostrar solo en desktop
      hideOnMobile: {
        type: Boolean,
        default: false
      }
    },
    setup(props) {
      const { t, locale } = useI18n()
      const localeStore = useLocaleStore()
      
      // Estados locales
      const isChanging = ref(false)
      const changingTo = ref(null)
      
      /**
       * Cambiar idioma con feedback visual
       */
      const changeLocale = async (newLocale) => {
        if (newLocale === localeStore.currentLocale || isChanging.value) {
          return
        }
        
        isChanging.value = true
        changingTo.value = newLocale
        
        try {
          // Simular pequeño delay para mostrar loading
          await new Promise(resolve => setTimeout(resolve, 300))
          
          // Cambiar idioma en i18n
          locale.value = newLocale
          
          // Cambiar idioma en el store
          localeStore.setLocale(newLocale)
          
          // Mostrar notificación de éxito
          window.$notify?.success(
            t('notifications.languageChanged'),
            { duration: 2000 }
          )
          
        } catch (error) {
          console.error('Error changing language:', error)
          window.$notify?.error(t('notifications.errorOccurred'))
        } finally {
          isChanging.value = false
          changingTo.value = null
        }
      }
      
      /**
       * Obtener la bandera del idioma actual
       */
      const getCurrentFlag = () => {
        return localeStore.availableLocales.find(
          l => l.code === localeStore.currentLocale
        )?.flag || '🇺🇸'
      }
      
      /**
       * Obtener el nombre del idioma actual
       */
      const getCurrentName = () => {
        return localeStore.availableLocales.find(
          l => l.code === localeStore.currentLocale
        )?.name || 'English'
      }
      
      /**
       * Classes del botón según variant
       */
      const buttonClass = computed(() => {
        const baseClasses = 'inline-flex justify-center items-center transition-colors duration-200'
        
        switch (props.variant) {
          case 'compact':
            return `${baseClasses} gap-x-1 rounded-md bg-transparent px-2 py-1 text-sm hover:bg-gray-100`
          case 'icon-only':
            return `${baseClasses} rounded-full bg-transparent p-2 hover:bg-gray-100`
          default:
            return `${baseClasses} gap-x-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50`
        }
      })
      
      /**
       * Classes del contenedor según hideOnMobile
       */
      const containerClass = computed(() => {
        return props.hideOnMobile ? 'hidden sm:block' : ''
      })
      
      return {
        localeStore,
        isChanging,
        changingTo,
        changeLocale,
        getCurrentFlag,
        getCurrentName,
        buttonClass,
        containerClass
      }
    }
  }
  </script>
  
  <style scoped>
  /* Animaciones adicionales para transiciones suaves */
  .language-item-enter-active,
  .language-item-leave-active {
    transition: all 0.2s ease;
  }
  
  .language-item-enter-from,
  .language-item-leave-to {
    opacity: 0;
    transform: translateX(-10px);
  }
  
  /* Loading spinner personalizado */
  @keyframes spin {
    to {
      transform: rotate(360deg);
    }
  }
  
  .animate-spin {
    animation: spin 1s linear infinite;
  }
  </style>