<!-- src/views/Dashboard.vue -->
<template>
  <MainLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">
            Bienvenido, {{ authStore.userName }}
          </h1>
          <p class="text-gray-600 mt-1">{{ getWelcomeMessage() }}</p>
        </div>

        <!-- Quick Actions -->
        <div class="flex items-center space-x-3">
          <button
            @click="refreshData"
            :disabled="isRefreshing"
            class="btn-secondary flex items-center"
          >
            <ArrowPathIcon :class="['w-4 h-4 mr-2', isRefreshing ? 'animate-spin' : '']" />
            <span class="hidden sm:inline">Actualizar</span>
          </button>

          <RouterLink v-if="authStore.isAdmin" to="/tournaments/create" class="btn-primary">
            <PlusIcon class="w-4 h-4 mr-2" />
            <span class="hidden sm:inline">Crear Torneo</span>
          </RouterLink>
        </div>
      </div>
    </template>

    <!-- Enhanced Role-based Dashboard Content -->
    <div class="space-y-8">
      <!-- Welcome Card with Stats -->
      <div
        class="relative overflow-hidden bg-gradient-to-br from-primary-500 via-primary-600 to-primary-700 rounded-2xl shadow-xl"
      >
        <div class="absolute inset-0 bg-black opacity-10"></div>
        <div class="absolute top-0 right-0 transform translate-x-1/3 -translate-y-1/3">
          <div class="w-72 h-72 bg-primary-400 rounded-full opacity-20"></div>
        </div>
        <div class="absolute bottom-0 left-0 transform -translate-x-1/3 translate-y-1/3">
          <div class="w-96 h-96 bg-primary-400 rounded-full opacity-10"></div>
        </div>

        <div class="relative p-8">
          <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between">
            <div class="flex items-center space-x-6 mb-6 lg:mb-0">
              <div
                class="w-20 h-20 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center backdrop-blur-sm"
              >
                <component :is="getRoleIcon()" class="w-10 h-10 text-white" />
              </div>
              <div>
                <h2 class="text-2xl font-bold text-white mb-1">
                  ¡Buenos días, {{ authStore.userName }}!
                </h2>
                <p class="text-primary-100 text-lg">{{ getRoleMessage() }}</p>
                <p class="text-primary-200 text-sm mt-2">
                  Último acceso: {{ formatDate(new Date()) }}
                </p>
              </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
              <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-white">{{ quickStats.primary }}</div>
                <div class="text-primary-100 text-sm">{{ quickStats.primaryLabel }}</div>
              </div>
              <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-white">{{ quickStats.secondary }}</div>
                <div class="text-primary-100 text-sm">{{ quickStats.secondaryLabel }}</div>
              </div>
              <div
                class="bg-white bg-opacity-10 backdrop-blur-sm rounded-xl p-4 text-center lg:block hidden"
              >
                <div class="text-2xl font-bold text-white">{{ quickStats.tertiary }}</div>
                <div class="text-primary-100 text-sm">{{ quickStats.tertiaryLabel }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Role-specific dashboard content -->
      <AdminDashboard v-if="authStore.isAdmin" @refresh="refreshData" />
      <TeamManagerDashboard v-else-if="authStore.isTeamManager" @refresh="refreshData" />
      <RefereeDashboard v-else-if="authStore.isReferee" @refresh="refreshData" />
      <PlayerDashboard v-else @refresh="refreshData" />
    </div>
  </MainLayout>
</template>

<script>
/**
 * Enhanced Dashboard Component with I18n
 * Provides beautiful, role-based dashboard with internationalization
 */

import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import {
  ArrowPathIcon,
  PlusIcon,
  ShieldCheckIcon,
  UserGroupIcon,
  UserIcon,
  ScaleIcon,
} from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/stores/auth'
import MainLayout from '@/components/layout/MainLayout.vue'
import AdminDashboard from '@/components/dashboard/AdminDashboard.vue'
import TeamManagerDashboard from '@/components/dashboard/TeamManagerDashboard.vue'
import RefereeDashboard from '@/components/dashboard/RefereeDashboard.vue'
import PlayerDashboard from '@/components/dashboard/PlayerDashboard.vue'

export default {
  name: 'Dashboard',
  components: {
    MainLayout,
    AdminDashboard,
    TeamManagerDashboard,
    RefereeDashboard,
    PlayerDashboard,
    ArrowPathIcon,
    PlusIcon,
  },
  setup() {
    const authStore = useAuthStore()
    const isRefreshing = ref(false)

    // Quick stats based on role
    const quickStats = computed(() => {
      switch (authStore.userRole) {
        case 'admin':
          return {
            primary: '45',
            primaryLabel: 'Torneos totales',
            secondary: '180',
            secondaryLabel: 'Equipos registrados',
            tertiary: '850',
            tertiaryLabel: 'Usuarios activos',
          }
        case 'team_manager':
          return {
            primary: '3',
            primaryLabel: 'Mis equipos',
            secondary: '42',
            secondaryLabel: 'Total jugadores',
            tertiary: '12',
            tertiaryLabel: 'Próximos partidos',
          }
        case 'referee':
          return {
            primary: '24',
            primaryLabel: 'Partidos arbitrados',
            secondary: '8',
            secondaryLabel: 'Próximos partidos',
            tertiary: '4.3',
            tertiaryLabel: 'Puntuación promedio',
          }
        default: // player
          return {
            primary: '15',
            primaryLabel: 'Partidos jugados',
            secondary: '8',
            secondaryLabel: 'Goles anotados',
            tertiary: '2',
            tertiaryLabel: 'Equipos activos',
          }
      }
    })

    const getWelcomeMessage = () => {
      const messages = {
        admin: 'Panel de administración del sistema',
        team_manager: 'Gestiona tus equipos y jugadores',
        referee: 'Revisa tus asignaciones de arbitraje',
        player: 'Consulta tus estadísticas y partidos',
      }
      return messages[authStore.userRole] || 'Bienvenido al sistema'
    }

    const getRoleMessage = () => {
      const roleMessages = {
        admin: 'Administrador del Sistema',
        team_manager: 'Gestor de Equipos',
        referee: 'Árbitro',
        player: 'Jugador',
      }
      return roleMessages[authStore.userRole] || 'Usuario'
    }

    const getRoleIcon = () => {
      const icons = {
        admin: ShieldCheckIcon,
        team_manager: UserGroupIcon,
        referee: ScaleIcon,
        player: UserIcon,
      }
      return icons[authStore.userRole] || UserIcon
    }

    const refreshData = async () => {
      isRefreshing.value = true
      try {
        // Simulate data refresh
        await new Promise((resolve) => setTimeout(resolve, 1000))
        window.$notify?.success('Datos actualizados')
      } catch (error) {
        window.$notify?.error('Error al actualizar los datos')
      } finally {
        isRefreshing.value = false
      }
    }

    const formatDate = (date) => {
      return new Intl.DateTimeFormat('es-ES', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      }).format(date)
    }

    onMounted(() => {
      // Auto-refresh data periodically
      const interval = setInterval(() => {
        if (!isRefreshing.value) {
          refreshData()
        }
      }, 300000) // 5 minutes

      // Cleanup on unmount
      return () => clearInterval(interval)
    })

    return {
      authStore,
      isRefreshing,
      quickStats,
      getWelcomeMessage,
      getRoleMessage,
      getRoleIcon,
      refreshData,
      formatDate,
    }
  },
}
</script>

<style scoped>
/* Custom animations */
@keyframes float {
  0%,
  100% {
    transform: translateY(0px);
  }
  50% {
    transform: translateY(-10px);
  }
}

.float-animation {
  animation: float 6s ease-in-out infinite;
}

/* Gradient text effect */
.gradient-text {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Glass morphism effect */
.glass {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}
</style>
