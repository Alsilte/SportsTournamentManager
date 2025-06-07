<template>
  <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-40">
    <nav class="container-custom">
      <div class="flex justify-between items-center h-16">
        <!-- Logo and Brand -->
        <div class="flex items-center space-x-4">
          <RouterLink 
            to="/" 
            class="flex items-center space-x-2 hover:opacity-80 transition-opacity"
          >
            <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center">
              <img src="../../../public/logo.png" alt="Logo">
            </div>
            <span class="font-bold text-xl text-gray-900 hidden sm:block">
              Tournament Manager
            </span>
          </RouterLink>
        </div>

        <!-- Desktop Navigation -->
        <div class="hidden md:flex items-center space-x-8">
          <!-- Public Navigation -->
          <RouterLink 
            v-for="item in publicNavItems" 
            :key="item.name"
            :to="item.to"
            class="nav-link"
            active-class="nav-link-active"
          >
            <component :is="item.icon" class="w-4 h-4 mr-2" />
            {{ item.label }}
          </RouterLink>

          <!-- Authentication Section -->
          <div v-if="!authStore.isAuthenticated" class="flex items-center space-x-4">
            <RouterLink to="/login" class="nav-link">
              Iniciar Sesión
            </RouterLink>
            <RouterLink to="/register" class="btn-primary">
              Registrarse
            </RouterLink>
          </div>

          <!-- User Menu (Authenticated) -->
          <div v-else class="relative">
            <Menu as="div" class="relative">
              <MenuButton class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100 transition-colors">
                <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center">
                  <UserIcon class="w-5 h-5 text-primary-600" />
                </div>
                <span class="text-sm font-medium text-gray-700">
                  {{ authStore.userName }}
                </span>
                <ChevronDownIcon class="w-4 h-4 text-gray-500" />
              </MenuButton>
              
              <transition
                enter-active-class="transition duration-100 ease-out"
                enter-from-class="transform scale-95 opacity-0"
                enter-to-class="transform scale-100 opacity-100"
                leave-active-class="transition duration-75 ease-in"
                leave-from-class="transform scale-100 opacity-100"
                leave-to-class="transform scale-95 opacity-0"
              >
                <MenuItems class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 focus:outline-none z-50">
                  <div class="py-1">
                    <!-- User Info -->
                    <div class="px-4 py-3 border-b border-gray-200">
                      <p class="text-sm font-medium text-gray-900">{{ authStore.userName }}</p>
                      <p class="text-sm text-gray-500">{{ authStore.userEmail }}</p>
                      <p class="text-xs text-primary-600 capitalize mt-1">{{ getRoleLabel(authStore.userRole) }}</p>
                    </div>

                    <!-- Menu Items -->
                    <MenuItem v-for="item in userMenuItems" :key="item.name" v-slot="{ active }">
                      <RouterLink
                        :to="item.to"
                        :class="[
                          active ? 'bg-gray-50' : '',
                          'flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50'
                        ]"
                      >
                        <component :is="item.icon" class="w-4 h-4 mr-3 text-gray-400" />
                        {{ item.label }}
                      </RouterLink>
                    </MenuItem>

                    <!-- Logout -->
                    <MenuItem v-slot="{ active }">
                      <button
                        @click="handleLogout"
                        :class="[
                          active ? 'bg-gray-50' : '',
                          'flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-50'
                        ]"
                      >
                        <ArrowRightOnRectangleIcon class="w-4 h-4 mr-3 text-gray-400" />
                        Cerrar Sesión
                      </button>
                    </MenuItem>
                  </div>
                </MenuItems>
              </transition>
            </Menu>
          </div>
        </div>

        <!-- Mobile Menu Button -->
        <div class="md:hidden flex items-center space-x-2">
          <button
            @click="toggleMobileMenu"
            class="p-2 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-colors"
          >
            <Bars3Icon v-if="!mobileMenu.isOpen.value" class="w-6 h-6" />
            <XMarkIcon v-else class="w-6 h-6" />
          </button>
        </div>
      </div>
    </nav>
  </header>
</template>

<script>
/**
 * Navigation Header Component
 * Main navigation bar with responsive design, user authentication, and i18n support
 */

import { inject } from 'vue'
import { RouterLink } from 'vue-router'
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue'
import {
  TrophyIcon,
  UserIcon,
  ChevronDownIcon,
  Bars3Icon,
  XMarkIcon,
  ArrowRightOnRectangleIcon,
  HomeIcon,
  CalendarIcon,
  UserGroupIcon,
  UsersIcon,
  PlayIcon,
  CogIcon,
  ChartBarIcon
} from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/stores/auth'

export default {
  name: 'NavigationHeader',
  components: {
    Menu,
    MenuButton,
    MenuItems,
    MenuItem,
    TrophyIcon,
    UserIcon,
    ChevronDownIcon,
    Bars3Icon,
    XMarkIcon,
    ArrowRightOnRectangleIcon,
    HomeIcon,
    CalendarIcon,
    UserGroupIcon,
    UsersIcon,
    PlayIcon,
    CogIcon,
    ChartBarIcon
  },
  setup() {
    const authStore = useAuthStore()
    const mobileMenu = inject('mobileMenu')

    // Public navigation items
    const publicNavItems = [
      { 
        name: 'Home', 
        to: '/', 
        icon: HomeIcon,
        label: 'Inicio'
      },
      { 
        name: 'Tournaments', 
        to: '/tournaments', 
        icon: CalendarIcon,
        label: 'Torneos'
      },
      { 
        name: 'Teams', 
        to: '/teams', 
        icon: UserGroupIcon,
        label: 'Equipos'
      },
      { 
        name: 'Players', 
        to: '/players', 
        icon: UsersIcon,
        label: 'Jugadores'
      },
      { 
        name: 'Matches', 
        to: '/matches', 
        icon: PlayIcon,
        label: 'Partidos'
      }
    ]

    // User menu items (authenticated users)
    const userMenuItems = [
      { 
        name: 'Dashboard', 
        to: '/dashboard', 
        icon: ChartBarIcon,
        label: 'Panel de Control'
      },
      { 
        name: 'Profile', 
        to: '/profile', 
        icon: UserIcon,
        label: 'Perfil'
      },
      { 
        name: 'Settings', 
        to: '/settings', 
        icon: CogIcon,
        label: 'Configuración'
      }
    ]

    const getRoleLabel = (role) => {
      const roleLabels = {
        admin: 'Administrador',
        manager: 'Gestor',
        player: 'Jugador',
        referee: 'Árbitro'
      }
      return roleLabels[role] || role
    }

    const toggleMobileMenu = () => {
      mobileMenu.toggle()
    }

    const handleLogout = async () => {
      await authStore.logout()
    }

    return {
      authStore,
      mobileMenu,
      publicNavItems,
      userMenuItems,
      toggleMobileMenu,
      handleLogout,
      getRoleLabel
    }
  }
}
</script>

<style scoped>
.nav-link {
  @apply flex items-center text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors;
}

.nav-link-active {
  @apply text-primary-600;
}
</style>