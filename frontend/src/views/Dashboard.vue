<!-- src/views/Dashboard.vue -->
<template>
  <MainLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">
            {{ $t('dashboard.welcome', { name: authStore.userName }) }}
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
            <span class="hidden sm:inline">{{ $t('common.refresh') }}</span>
          </button>

          <RouterLink v-if="authStore.isAdmin" to="/tournaments/create" class="btn-primary">
            <PlusIcon class="w-4 h-4 mr-2" />
            <span class="hidden sm:inline">{{ $t('tournaments.create') }}</span>
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
                  {{ $t('dashboard.goodMorning') }}, {{ authStore.userName }}!
                </h2>
                <p class="text-primary-100 text-lg">{{ getRoleMessage() }}</p>
                <p class="text-primary-200 text-sm mt-2">
                  {{ $t('dashboard.lastLogin') }}: {{ formatDate(new Date()) }}
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
// ✅ IMPORTS CORREGIDOS - usar la estructura existente
import { tournamentAPI, teamAPI, apiHelpers } from '@/services/api'

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
    const { t } = useI18n()
    const authStore = useAuthStore()
    const isRefreshing = ref(false)
    const realStats = ref({
      tournaments: 0,
      teams: 0,
      players: 0,
      matches: 0,
      myTeams: 0,
      myTournaments: 0
    })

    // Cargar estadísticas reales desde API
    const loadRealStats = async () => {
      try {
        const promises = []
        
        // Estadísticas según el rol
        if (authStore.isAdmin) {
          promises.push(
            tournamentAPI.getAll({ per_page: 1 }).then(res => {
              if (apiHelpers.isSuccess(res)) {
                realStats.value.tournaments = res.data.total || 0
              }
            }),
            teamAPI.getAll({ per_page: 1 }).then(res => {
              if (apiHelpers.isSuccess(res)) {
                realStats.value.teams = res.data.total || 0
              }
            })
          )
        } else if (authStore.isTeamManager) {
          promises.push(
            teamAPI.getAll({ manager_id: authStore.user.id }).then(res => {
              if (apiHelpers.isSuccess(res)) {
                realStats.value.myTeams = res.data.data?.length || 0
              }
            })
          )
        }
        
        await Promise.all(promises)
      } catch (err) {
        console.error('Error loading dashboard stats:', err)
      }
    }

    // Quick stats basado en datos reales
    const quickStats = computed(() => {
      switch (authStore.userRole) {
        case 'admin':
          return {
            primary: realStats.value.tournaments.toString(),
            primaryLabel: t('dashboard.stats.totalTournaments'),
            secondary: realStats.value.teams.toString(),
            secondaryLabel: t('dashboard.stats.registeredTeams'),
            tertiary: realStats.value.players.toString(),
            tertiaryLabel: t('dashboard.stats.activeUsers'),
          }
        case 'team_manager':
          return {
            primary: realStats.value.myTeams.toString(),
            primaryLabel: t('dashboard.stats.myTeams'),
            secondary: '0', // TODO: Implementar conteo real de jugadores
            secondaryLabel: t('dashboard.stats.totalPlayers'),
            tertiary: '0', // TODO: Implementar conteo real de partidos
            tertiaryLabel: t('dashboard.stats.upcomingMatches'),
          }
        case 'referee':
          return {
            primary: '0', // TODO: Implementar estadísticas de árbitro
            primaryLabel: t('dashboard.stats.matchesOfficiated'),
            secondary: '0',
            secondaryLabel: t('dashboard.stats.upcomingMatches'),
            tertiary: '0',
            tertiaryLabel: t('dashboard.stats.averageRating'),
          }
        default: // player
          return {
            primary: '0', // TODO: Implementar estadísticas de jugador
            primaryLabel: t('dashboard.stats.matchesPlayed'),
            secondary: '0',
            secondaryLabel: t('dashboard.stats.goalsScored'),
            tertiary: '0',
            tertiaryLabel: t('dashboard.stats.activeTeams'),
          }
      }
    })

    const getWelcomeMessage = () => {
      const messages = {
        admin: t('dashboard.adminWelcome'),
        team_manager: t('dashboard.managerWelcome'),
        referee: t('dashboard.refereeWelcome'),
        player: t('dashboard.playerWelcome'),
      }
      return messages[authStore.userRole] || t('dashboard.defaultWelcome')
    }

    const getRoleMessage = () => {
      const roleMessages = {
        admin: t('dashboard.adminRole'),
        team_manager: t('dashboard.managerRole'),
        referee: t('dashboard.refereeRole'),
        player: t('dashboard.playerRole'),
      }
      return roleMessages[authStore.userRole] || t('dashboard.userRole')
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
        await loadRealStats()
        window.$notify?.success(t('notifications.dataRefreshed'))
      } catch (error) {
        window.$notify?.error(t('notifications.refreshError'))
      } finally {
        isRefreshing.value = false
      }
    }

    const formatDate = (date) => {
      return new Intl.DateTimeFormat(authStore.locale || 'en', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      }).format(date)
    }

    onMounted(() => {
      loadRealStats()
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
