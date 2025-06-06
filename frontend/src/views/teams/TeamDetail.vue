<template>
  <MainLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center space-x-4">
          <button @click="$router.go(-1)" class="btn-secondary p-2">
            <ArrowLeftIcon class="w-5 h-5" />
          </button>
          <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ team?.name }}</h1>
            <div class="flex items-center space-x-4 mt-2">
              <span
                :class="[
                  'px-3 py-1 rounded-full text-sm font-medium',
                  team?.is_active ? 'bg-success-100 text-success-800' : 'bg-gray-100 text-gray-800',
                ]"
              >
                {{ team?.is_active ? 'Activo' : 'Inactivo' }}
              </span>
              <span v-if="team?.short_name" class="text-gray-600">{{ team.short_name }}</span>
            </div>
          </div>
        </div>

        <div class="flex items-center space-x-3">
          <RouterLink v-if="canManageTeam" :to="`/teams/${team?.id}/roster`" class="btn-secondary">
            <UsersIcon class="w-4 h-4 mr-2" />
            Gestionar plantilla
          </RouterLink>

          <button v-if="canManageTeam" @click="showEditModal = true" class="btn-primary">
            <PencilIcon class="w-4 h-4 mr-2" />
            Editar equipo
          </button>
        </div>
      </div>
    </template>

    <!-- Loading State -->
    <div v-if="isLoading" class="space-y-6">
      <div class="card p-6">
        <div class="animate-pulse space-y-4">
          <div class="h-4 bg-gray-200 rounded w-3/4"></div>
          <div class="h-4 bg-gray-200 rounded w-1/2"></div>
          <div class="h-32 bg-gray-200 rounded"></div>
        </div>
      </div>
    </div>

    <!-- Team Details -->
    <div v-else-if="team" class="space-y-8">
      <!-- Team Overview -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Description -->
          <div class="card p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Acerca de este equipo</h2>
            <p class="text-gray-700 leading-relaxed">
              {{ team.description || 'No hay descripción disponible' }}
            </p>
          </div>

          <!-- Recent Matches -->
          <div class="card p-6">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-xl font-semibold text-gray-900">Partidos recientes</h2>
              <RouterLink
                :to="`/teams/${team.id}/matches`"
                class="text-primary-600 hover:text-primary-700 text-sm font-medium"
              >
                Ver todos
              </RouterLink>
            </div>

            <div v-if="recentMatches.length" class="space-y-4">
              <div
                v-for="match in recentMatches"
                :key="match.id"
                class="flex items-center justify-between p-4 bg-gray-50 rounded-lg"
              >
                <div class="flex items-center space-x-4">
                  <div class="flex items-center space-x-3">
                    <div class="text-center">
                      <div class="font-medium text-gray-900">{{ match.home_team?.name }}</div>
                      <div class="text-xs text-gray-500">Local</div>
                    </div>
                    <div class="text-center">
                      <div class="text-xl font-bold text-gray-900">
                        {{ match.home_score }} - {{ match.away_score }}
                      </div>
                      <div class="text-xs text-gray-500">Final</div>
                    </div>
                    <div class="text-center">
                      <div class="font-medium text-gray-900">{{ match.away_team?.name }}</div>
                      <div class="text-xs text-gray-500">Visitante</div>
                    </div>
                  </div>
                </div>
                <div class="text-right">
                  <div class="text-sm text-gray-600">{{ formatDate(match.match_date) }}</div>
                  <div class="text-xs text-gray-500">{{ match.tournament?.name }}</div>
                </div>
              </div>
            </div>

            <div v-else class="text-center py-8 text-gray-500">
              <PlayIcon class="w-12 h-12 mx-auto mb-2 text-gray-300" />
              <p>No hay partidos recientes</p>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Team Stats -->
          <div class="card p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Estadísticas del equipo</h3>
            <div class="space-y-4">
              <div class="flex justify-between">
                <span class="text-gray-600">Fundado</span>
                <span class="font-medium">{{ team.founded_year || 'Desconocido' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Jugadores</span>
                <span class="font-medium">{{ team.players_count || 0 }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Torneos</span>
                <span class="font-medium">{{ activeTournaments }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">% Victorias</span>
                <span class="font-medium">{{ teamStats.win_rate || 0 }}%</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Total goles</span>
                <span class="font-medium">{{ teamStats.goals_for || 0 }}</span>
              </div>
            </div>
          </div>

          <!-- Contact Info -->
          <div class="card p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Información de contacto</h3>
            <div class="space-y-3">
              <div class="flex items-center">
                <UserIcon class="w-5 h-5 text-gray-400 mr-3" />
                <div>
                  <div class="font-medium text-gray-900">
                    {{ team.manager?.name || 'Sin gestor asignado' }}
                  </div>
                  <div class="text-sm text-gray-600">Gestor del equipo</div>
                </div>
              </div>
              <div class="flex items-center">
                <EnvelopeIcon class="w-5 h-5 text-gray-400 mr-3" />
                <div class="text-sm text-gray-600">
                  {{ team.contact_email || 'Sin correo proporcionado' }}
                </div>
              </div>
              <div v-if="team.contact_phone" class="flex items-center">
                <PhoneIcon class="w-5 h-5 text-gray-400 mr-3" />
                <div class="text-sm text-gray-600">{{ team.contact_phone }}</div>
              </div>
              <div v-if="team.home_venue" class="flex items-center">
                <MapPinIcon class="w-5 h-5 text-gray-400 mr-3" />
                <div class="text-sm text-gray-600">{{ team.home_venue }}</div>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="card p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Acciones rápidas</h3>
            <div class="space-y-3">
              <RouterLink
                :to="`/teams/${team.id}/roster`"
                class="btn-secondary w-full text-center block"
              >
                <UsersIcon class="w-4 h-4 mr-2 inline" />
                Ver plantilla
              </RouterLink>
              <RouterLink
                :to="`/teams/${team.id}/statistics`"
                class="btn-secondary w-full text-center block"
              >
                <ChartBarIcon class="w-4 h-4 mr-2 inline" />
                Ver estadísticas
              </RouterLink>
              <RouterLink
                :to="`/teams/${team.id}/matches`"
                class="btn-secondary w-full text-center block"
              >
                <PlayIcon class="w-4 h-4 mr-2 inline" />
                Historial de partidos
              </RouterLink>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabs Navigation -->
      <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              'py-2 px-1 border-b-2 font-medium text-sm transition-colors',
              activeTab === tab.id
                ? 'border-primary-500 text-primary-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
            ]"
          >
            {{ getTabName(tab.name) }}
            <span
              v-if="tab.count !== undefined"
              class="ml-2 px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600"
            >
              {{ tab.count }}
            </span>
          </button>
        </nav>
      </div>

      <!-- Tab Content -->
      <div class="min-h-[400px]">
        <!-- Players Tab -->
        <div v-if="activeTab === 'players'" class="space-y-6">
          <div
            v-if="teamPlayers.length"
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
          >
            <div
              v-for="player in teamPlayers"
              :key="player.id"
              class="card p-6 hover:shadow-card-hover transition-shadow"
            >
              <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                  <div
                    class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center mr-4"
                  >
                    <UserIcon class="w-6 h-6 text-primary-600" />
                  </div>
                  <div>
                    <h3 class="font-semibold text-gray-900">{{ player.user?.name }}</h3>
                    <p class="text-sm text-gray-600">
                      {{ player.pivot?.position || 'Sin posición' }}
                    </p>
                  </div>
                </div>
                <div class="text-center">
                  <div class="text-lg font-bold text-primary-600">
                    {{ player.pivot?.jersey_number || '-' }}
                  </div>
                  <div class="text-xs text-gray-500">Dorsal</div>
                </div>
              </div>

              <div class="space-y-2 text-sm text-gray-600 mb-4">
                <div v-if="player.pivot?.is_captain" class="flex items-center">
                  <StarIcon class="w-4 h-4 mr-2 text-warning-500" />
                  <span class="font-medium text-warning-700">Capitán del equipo</span>
                </div>
                <div class="flex justify-between">
                  <span>Se unió:</span>
                  <span class="font-medium">{{ formatDate(player.pivot?.joined_date) }}</span>
                </div>
              </div>

              <RouterLink
                :to="`/players/${player.id}`"
                class="btn-secondary text-xs px-3 py-1 w-full text-center block"
              >
                Ver perfil
              </RouterLink>
            </div>
          </div>

          <div v-else class="text-center py-12">
            <UsersIcon class="w-12 h-12 text-gray-300 mx-auto mb-4" />
            <h3 class="text-lg font-medium text-gray-900 mb-2">No hay jugadores registrados</h3>
            <p class="text-gray-600">Comienza a construir tu equipo</p>
            <RouterLink
              v-if="canManageTeam"
              :to="`/teams/${team.id}/roster`"
              class="btn-primary mt-4"
            >
              Agregar jugadores
            </RouterLink>
          </div>
        </div>

        <!-- Tournaments Tab -->
        <div v-if="activeTab === 'tournaments'" class="space-y-6">
          <div v-if="teamTournaments.length" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div
              v-for="tournament in teamTournaments"
              :key="tournament.id"
              class="card p-6 hover:shadow-card-hover transition-shadow"
            >
              <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-900">{{ tournament.name }}</h3>
                <span
                  :class="getTournamentStatusClass(tournament.status)"
                  class="px-2 py-1 text-xs font-medium rounded-full"
                >
                  {{ formatTournamentStatus(tournament.status) }}
                </span>
              </div>

              <div class="space-y-2 text-sm text-gray-600 mb-4">
                <div class="flex justify-between">
                  <span>Deporte:</span>
                  <span class="font-medium">{{ tournament.sport_type }}</span>
                </div>
                <div class="flex justify-between">
                  <span>Formato:</span>
                  <span class="font-medium">{{
                    formatTournamentType(tournament.tournament_type)
                  }}</span>
                </div>
                <div class="flex justify-between">
                  <span>Fecha de inicio:</span>
                  <span class="font-medium">{{ formatDate(tournament.start_date) }}</span>
                </div>
              </div>

              <RouterLink
                :to="`/tournaments/${tournament.id}`"
                class="btn-secondary text-xs px-3 py-1 w-full text-center block"
              >
                Ver torneo
              </RouterLink>
            </div>
          </div>

          <div v-else class="text-center py-12">
            <TrophyIcon class="w-12 h-12 text-gray-300 mx-auto mb-4" />
            <h3 class="text-lg font-medium text-gray-900 mb-2">Sin torneos</h3>
            <p class="text-gray-600">Este equipo no ha participado en ningún torneo</p>
          </div>
        </div>

        <!-- Statistics Tab -->
        <div v-if="activeTab === 'statistics'" class="space-y-6">
          <!-- Real Statistics from API -->
          <div v-if="hasStatistics" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="card p-6 text-center">
              <div class="text-3xl font-bold text-primary-600">
                {{ teamStats.total_matches || 0 }}
              </div>
              <div class="text-sm text-gray-600">Total partidos</div>
            </div>
            <div class="card p-6 text-center">
              <div class="text-3xl font-bold text-success-600">{{ teamStats.wins || 0 }}</div>
              <div class="text-sm text-gray-600">Victorias</div>
            </div>
            <div class="card p-6 text-center">
              <div class="text-3xl font-bold text-warning-600">{{ teamStats.draws || 0 }}</div>
              <div class="text-sm text-gray-600">Empates</div>
            </div>
            <div class="card p-6 text-center">
              <div class="text-3xl font-bold text-danger-600">{{ teamStats.losses || 0 }}</div>
              <div class="text-sm text-gray-600">Derrotas</div>
            </div>
          </div>

          <!-- More detailed stats if available -->
          <div v-if="hasStatistics" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="card p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Estadísticas ofensivas</h3>
              <div class="space-y-3">
                <div class="flex justify-between">
                  <span class="text-gray-600">Goles a favor</span>
                  <span class="font-medium">{{ teamStats.goals_for || 0 }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Goles en contra</span>
                  <span class="font-medium">{{ teamStats.goals_against || 0 }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Diferencia de goles</span>
                  <span class="font-medium">{{ teamStats.goal_difference || 0 }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Porterías a cero</span>
                  <span class="font-medium">{{ teamStats.clean_sheets || 0 }}</span>
                </div>
              </div>
            </div>

            <div class="card p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Rendimiento</h3>
              <div class="space-y-3">
                <div class="flex justify-between">
                  <span class="text-gray-600">% Victorias</span>
                  <span class="font-medium">{{ teamStats.win_rate || 0 }}%</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Goles por partido</span>
                  <span class="font-medium">{{ teamStats.goals_per_match || 0 }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Goles encajados por partido</span>
                  <span class="font-medium">{{ teamStats.goals_conceded_per_match || 0 }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- No statistics available -->
          <div v-if="!hasStatistics" class="text-center py-12">
            <ChartBarIcon class="w-12 h-12 text-gray-300 mx-auto mb-4" />
            <h3 class="text-lg font-medium text-gray-900 mb-2">No hay estadísticas disponibles</h3>
            <p class="text-gray-600">Las estadísticas aparecerán después de jugar partidos</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="text-center py-12">
      <ExclamationTriangleIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
      <h3 class="text-lg font-medium text-gray-900 mb-2">Equipo no encontrado</h3>
      <p class="text-gray-600 mb-6">{{ error }}</p>
      <RouterLink to="/teams" class="btn-primary">Volver a equipos</RouterLink>
    </div>
  </MainLayout>
</template>

<script>
/**
 * Team Detail Page Component - API ONLY VERSION
 * Shows comprehensive team information with REAL players, tournaments, and statistics
 */

import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import {
  ArrowLeftIcon,
  PencilIcon,
  UsersIcon,
  UserIcon,
  EnvelopeIcon,
  PhoneIcon,
  MapPinIcon,
  PlayIcon,
  ChartBarIcon,
  TrophyIcon,
  StarIcon,
  ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/stores/auth'
import { teamAPI, apiHelpers } from '@/services/api'
import MainLayout from '@/components/layout/MainLayout.vue'

export default {
  name: 'TeamDetail',
  components: {
    MainLayout,
    ArrowLeftIcon,
    PencilIcon,
    UsersIcon,
    UserIcon,
    EnvelopeIcon,
    PhoneIcon,
    MapPinIcon,
    PlayIcon,
    ChartBarIcon,
    TrophyIcon,
    StarIcon,
    ExclamationTriangleIcon,
  },
  setup() {
    const route = useRoute()
    const authStore = useAuthStore()

    // Data
    const team = ref(null)
    const teamPlayers = ref([])
    const teamTournaments = ref([])
    const recentMatches = ref([])
    const teamStats = ref({})
    const isLoading = ref(false)
    const error = ref('')
    const activeTab = ref('players')
    const showEditModal = ref(false)

    // Computed
    const canManageTeam = computed(() => {
      return authStore.isAdmin || team.value?.manager_id === authStore.user?.id
    })

    const activeTournaments = computed(() => {
      return teamTournaments.value.filter((t) =>
        ['registration_open', 'in_progress'].includes(t.status),
      ).length
    })

    const hasStatistics = computed(() => {
      return teamStats.value && Object.keys(teamStats.value).length > 0
    })

    const tabs = computed(() => [
      { id: 'players', name: 'players', count: teamPlayers.value.length },
      { id: 'tournaments', name: 'tournaments', count: teamTournaments.value.length },
      { id: 'statistics', name: 'statistics' },
    ])

    /**
     * Fetch team data
     */
    const fetchTeam = async () => {
      isLoading.value = true
      error.value = ''

      try {
        const response = await teamAPI.getById(route.params.id)

        if (apiHelpers.isSuccess(response)) {
          const data = apiHelpers.getData(response)
          team.value = data
          recentMatches.value = data.recent_matches || []
        } else {
          error.value = 'Team not found'
        }
      } catch (err) {
        console.error('Failed to fetch team:', err)
        error.value = apiHelpers.handleError(err)
      } finally {
        isLoading.value = false
      }
    }

    /**
     * Fetch team roster
     */
    const fetchRoster = async () => {
      try {
        const response = await teamAPI.getRoster(route.params.id)

        if (apiHelpers.isSuccess(response)) {
          const data = apiHelpers.getData(response)
          teamPlayers.value = data.players || []
        } else {
          // No hay datos - mostrar lista vacía
          teamPlayers.value = []
        }
      } catch (err) {
        console.error('Failed to fetch roster:', err)
        teamPlayers.value = []
      }
    }

    /**
     * Fetch team statistics - SOLO DATOS REALES
     */
    const fetchStatistics = async () => {
      try {
        const response = await teamAPI.getStatistics(route.params.id)

        if (apiHelpers.isSuccess(response)) {
          const data = apiHelpers.getData(response)
          teamStats.value = data.overall_statistics || {}
          teamTournaments.value = data.tournament_statistics || []
        } else {
          // No statistics available - leave empty
          teamStats.value = {}
          teamTournaments.value = []
        }
      } catch (err) {
        console.error('Failed to fetch statistics:', err)
        // No fallback data - just empty objects
        teamStats.value = {}
        teamTournaments.value = []
      }
    }

    /**
     * Get tab name in Spanish
     */
    const getTabName = (tabKey) => {
      const tabNames = {
        players: 'Jugadores',
        tournaments: 'Torneos',
        statistics: 'Estadísticas'
      }
      return tabNames[tabKey] || tabKey
    }

    /**
     * Format tournament status
     */
    const formatTournamentStatus = (status) => {
      const statusMap = {
        draft: 'Borrador',
        registration_open: 'Registro abierto',
        in_progress: 'En progreso',
        completed: 'Completado',
        cancelled: 'Cancelado',
      }
      return statusMap[status] || status
    }

    /**
     * Get tournament status CSS classes
     */
    const getTournamentStatusClass = (status) => {
      const classMap = {
        draft: 'bg-gray-100 text-gray-800',
        registration_open: 'bg-success-100 text-success-800',
        in_progress: 'bg-primary-100 text-primary-800',
        completed: 'bg-secondary-100 text-secondary-800',
        cancelled: 'bg-danger-100 text-danger-800',
      }
      return classMap[status] || classMap.draft
    }

    /**
     * Format tournament type
     */
    const formatTournamentType = (type) => {
      const typeMap = {
        league: 'Liga',
        knockout: 'Eliminación directa',
        group_knockout: 'Grupos + Eliminación',
      }
      return typeMap[type] || type
    }

    /**
     * Format date for display
     */
    const formatDate = (dateString) => {
      if (!dateString) return 'N/A'
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
      })
    }

    // Initialize
    onMounted(() => {
      fetchTeam()
      fetchRoster()
      fetchStatistics()
    })

    return {
      authStore,
      team,
      teamPlayers,
      teamTournaments,
      recentMatches,
      teamStats,
      isLoading,
      error,
      activeTab,
      showEditModal,
      canManageTeam,
      activeTournaments,
      hasStatistics,
      tabs,
      getTabName,
      formatTournamentStatus,
      getTournamentStatusClass,
      formatTournamentType,
      formatDate,
    }
  },
}
</script>
