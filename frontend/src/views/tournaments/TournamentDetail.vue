<template>
  <MainLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center space-x-4">
          <button @click="$router.go(-1)" class="btn-secondary p-2">
            <ArrowLeftIcon class="w-5 h-5" />
          </button>
          <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ tournament?.name }}</h1>
            <div class="flex items-center space-x-4 mt-2">
              <span
                :class="getStatusBadgeClass(tournament?.status)"
                class="px-3 py-1 rounded-full text-sm font-medium"
              >
                {{ formatStatus(tournament?.status) }}
              </span>
              <span class="text-gray-600">{{ tournament?.sport_type }}</span>
            </div>
          </div>
        </div>

        <div class="flex items-center space-x-3">
          <button
            v-if="tournament?.status === 'registration_open' && authStore.canManageTeams"
            @click="showRegistrationModal = true"
            class="btn-primary"
          >
            <PlusIcon class="w-4 h-4 mr-2" />
            Registrar Equipo
          </button>

          <!-- Edit Tournament Button -->
          <button
            v-if="canEditTournament"
            @click="showEditModal = true"
            class="btn-secondary"
          >
            <PencilIcon class="w-4 h-4 mr-2" />
            Editar Torneo
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

    <!-- Tournament Details -->
    <div v-else-if="tournament" class="space-y-8">
      <!-- Tournament Info -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Description -->
          <div class="card p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Acerca de este torneo</h2>
            <p class="text-gray-700 leading-relaxed">
              {{ tournament.description || 'No hay descripción disponible' }}
            </p>
          </div>

          <!-- Tournament Schedule -->
          <div class="card p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Cronograma</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <h3 class="text-sm font-medium text-gray-700 mb-2">Período de Inscripciones</h3>
                <div class="space-y-1">
                  <div class="flex items-center text-sm text-gray-600">
                    <CalendarIcon class="w-4 h-4 mr-2" />
                    <span>Inicio: {{ formatDate(tournament.registration_start) }}</span>
                  </div>
                  <div class="flex items-center text-sm text-gray-600">
                    <CalendarIcon class="w-4 h-4 mr-2" />
                    <span>Fin: {{ formatDate(tournament.registration_end) }}</span>
                  </div>
                </div>
              </div>

              <div>
                <h3 class="text-sm font-medium text-gray-700 mb-2">Período del Torneo</h3>
                <div class="space-y-1">
                  <div class="flex items-center text-sm text-gray-600">
                    <CalendarIcon class="w-4 h-4 mr-2" />
                    <span>Inicio: {{ formatDate(tournament.start_date) }}</span>
                  </div>
                  <div v-if="tournament.end_date" class="flex items-center text-sm text-gray-600">
                    <CalendarIcon class="w-4 h-4 mr-2" />
                    <span>Fin: {{ formatDate(tournament.end_date) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Rules (if available) -->
          <div v-if="tournament.rules" class="card p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Reglas</h2>
            <div class="text-gray-700 whitespace-pre-line">{{ tournament.rules }}</div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Quick Stats -->
          <div class="card p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Estadísticas del Torneo</h3>
            <div class="space-y-4">
              <div class="flex justify-between">
                <span class="text-gray-600">Formato</span>
                <span class="font-medium">{{
                  formatTournamentType(tournament.tournament_type)
                }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Equipos</span>
                <span class="font-medium"
                  >{{ tournament.registered_teams_count || 0 }}/{{ tournament.max_teams }}</span
                >
              </div>
              <div v-if="tournament.location" class="flex justify-between">
                <span class="text-gray-600">Ubicación</span>
                <span class="font-medium">{{ tournament.location }}</span>
              </div>
              <div v-if="tournament.prize_pool" class="flex justify-between">
                <span class="text-gray-600">Premio</span>
                <span class="font-medium">${{ formatMoney(tournament.prize_pool) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Creado por</span>
                <span class="font-medium">{{ tournament.creator?.name }}</span>
              </div>
            </div>

            <!-- Registration Progress -->
            <div v-if="tournament.status === 'registration_open'" class="mt-6">
              <div class="flex justify-between text-sm text-gray-600 mb-2">
                <span>Progreso de inscripciones</span>
                <span>{{ Math.round(registrationProgress) }}%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div
                  class="bg-primary-600 h-2 rounded-full transition-all duration-300"
                  :style="{ width: `${registrationProgress}%` }"
                ></div>
              </div>
            </div>
          </div>

          <!-- Contact Info -->
          <div class="card p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Contacto</h3>
            <div class="space-y-3">
              <div class="flex items-center">
                <UserIcon class="w-5 h-5 text-gray-400 mr-3" />
                <div>
                  <div class="font-medium text-gray-900">{{ tournament.creator?.name }}</div>
                  <div class="text-sm text-gray-600">Organizador del torneo</div>
                </div>
              </div>
              <div class="flex items-center">
                <EnvelopeIcon class="w-5 h-5 text-gray-400 mr-3" />
                <div class="text-sm text-gray-600">
                  {{ tournament.creator?.email || 'No disponible' }}
                </div>
              </div>
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
            {{ getTabName(tab.id) }}
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
        <!-- Teams Tab -->
        <div v-if="activeTab === 'teams'" class="space-y-6">
          <div
            v-if="registeredTeams.length"
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
          >
            <div
              v-for="team in registeredTeams"
              :key="team.id"
              class="card p-6 hover:shadow-card-hover transition-shadow"
            >
              <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-900">{{ team.name }}</h3>
                <span
                  :class="getTeamStatusClass(team.pivot?.status)"
                  class="px-2 py-1 text-xs font-medium rounded-full"
                >
                  {{ formatTeamStatus(team.pivot?.status) }}
                </span>
              </div>
              <div class="space-y-2 text-sm text-gray-600">
                <div class="flex justify-between">
                  <span>Jugadores:</span>
                  <span class="font-medium">{{ team.players_count || 0 }}</span>
                </div>
                <div class="flex justify-between">
                  <span>Registrado:</span>
                  <span class="font-medium">{{ formatDate(team.pivot?.registration_date) }}</span>
                </div>
              </div>
              <div class="mt-4">
                <RouterLink
                  :to="`/teams/${team.id}`"
                  class="btn-secondary text-xs px-3 py-1 w-full text-center block"
                >
                  Ver equipo
                </RouterLink>
              </div>
            </div>
          </div>

          <div v-else class="text-center py-12">
            <UserGroupIcon class="w-12 h-12 text-gray-300 mx-auto mb-4" />
            <h3 class="text-lg font-medium text-gray-900 mb-2">No hay equipos registrados</h3>
            <p class="text-gray-600">Sé el primero en registrarte</p>
          </div>
        </div>

        <!-- Matches Tab -->
        <div v-if="activeTab === 'matches'" class="space-y-6">
          <div v-if="matches.length" class="space-y-4">
            <div v-for="match in matches" :key="match.id" class="card p-6">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-6">
                  <div class="text-center">
                    <div class="text-sm font-medium text-gray-900">
                      {{ formatMatchDate(match.match_date) }}
                    </div>
                    <div class="text-xs text-gray-500">{{ formatMatchTime(match.match_date) }}</div>
                  </div>
                  <div class="flex items-center space-x-4">
                    <div class="text-right">
                      <div class="font-medium text-gray-900">{{ match.home_team?.name }}</div>
                      <div class="text-xs text-gray-500">Local</div>
                    </div>
                    <div class="text-center">
                      <div
                        v-if="match.status === 'completed'"
                        class="text-xl font-bold text-gray-900"
                      >
                        {{ match.home_score }} - {{ match.away_score }}
                      </div>
                      <div v-else class="text-gray-400 font-bold">VS</div>
                    </div>
                    <div class="text-left">
                      <div class="font-medium text-gray-900">{{ match.away_team?.name }}</div>
                      <div class="text-xs text-gray-500">Visitante</div>
                    </div>
                  </div>
                </div>
                <div class="text-right">
                  <div v-if="match.venue" class="text-sm text-gray-600">{{ match.venue }}</div>
                  <div class="text-xs text-gray-500">{{ match.round || 'Ronda por determinar' }}</div>
                  <RouterLink
                    :to="`/matches/${match.id}`"
                    class="text-primary-600 hover:text-primary-700 text-sm font-medium mt-2 inline-block"
                  >
                    Ver detalles
                  </RouterLink>
                </div>
              </div>
            </div>
          </div>

          <div v-else class="text-center py-12">
            <PlayIcon class="w-12 h-12 text-gray-300 mx-auto mb-4" />
            <h3 class="text-lg font-medium text-gray-900 mb-2">No hay partidos programados</h3>
            <p class="text-gray-600">Los partidos aparecerán cuando se programen</p>
          </div>
        </div>

        <!-- Standings Tab -->
        <div v-if="activeTab === 'standings'" class="space-y-6">
          <div v-if="standings.length" class="card overflow-hidden">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Posición
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Equipo
                    </th>
                    <th
                      class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Jugados
                    </th>
                    <th
                      class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Ganados
                    </th>
                    <th
                      class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Empatados
                    </th>
                    <th
                      class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Perdidos
                    </th>
                    <th
                      class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      GF
                    </th>
                    <th
                      class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      GC
                    </th>
                    <th
                      class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      DG
                    </th>
                    <th
                      class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Puntos
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="(standing, index) in standings" :key="standing.id">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {{ index + 1 }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div class="font-medium text-gray-900">{{ standing.team?.name }}</div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                      {{ standing.played }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                      {{ standing.won }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                      {{ standing.drawn }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                      {{ standing.lost }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                      {{ standing.goals_for }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                      {{ standing.goals_against }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                      <span
                        :class="
                          standing.goal_difference >= 0 ? 'text-success-600' : 'text-danger-600'
                        "
                      >
                        {{ standing.goal_difference > 0 ? '+' : '' }}{{ standing.goal_difference }}
                      </span>
                    </td>
                    <td
                      class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 text-center"
                    >
                      {{ standing.points }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div v-else class="text-center py-12">
            <ChartBarIcon class="w-12 h-12 text-gray-300 mx-auto mb-4" />
            <h3 class="text-lg font-medium text-gray-900 mb-2">No hay tabla de posiciones disponible</h3>
            <p class="text-gray-600">La tabla aparecerá cuando comiencen los partidos</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="text-center py-12">
      <ExclamationTriangleIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
      <h3 class="text-lg font-medium text-gray-900 mb-2">Torneo no encontrado</h3>
      <p class="text-gray-600 mb-6">{{ error }}</p>
      <RouterLink to="/tournaments" class="btn-primary">Volver a torneos</RouterLink>
    </div>

    <!-- Team Registration Modal -->
    <TeamRegistrationModal
      v-if="showRegistrationModal && tournament"
      :tournament="tournament"
      @close="showRegistrationModal = false"
      @success="handleRegistrationSuccess"
    />
  </MainLayout>
</template>

<script>
/**
 * Tournament Detail Page Component
 * Shows comprehensive tournament information with teams, matches, and standings
 */

import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import {
  ArrowLeftIcon,
  PlusIcon,
  PencilIcon,
  CalendarIcon,
  UserIcon,
  EnvelopeIcon,
  UserGroupIcon,
  PlayIcon,
  ChartBarIcon,
  ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/stores/auth'
import { tournamentAPI, apiHelpers } from '@/services/api'
import MainLayout from '@/components/layout/MainLayout.vue'
import TeamRegistrationModal from '@/components/tournaments/TeamRegistrationModal.vue'

export default {
  name: 'TournamentDetail',
  components: {
    MainLayout,
    TeamRegistrationModal,
    ArrowLeftIcon,
    PlusIcon,
    PencilIcon,
    CalendarIcon,
    UserIcon,
    EnvelopeIcon,
    UserGroupIcon,
    PlayIcon,
    ChartBarIcon,
    ExclamationTriangleIcon,
  },
  setup() {
    const route = useRoute()
    const authStore = useAuthStore()

    // Data
    const tournament = ref(null)
    const registeredTeams = ref([])
    const matches = ref([])
    const standings = ref([])
    const isLoading = ref(false)
    const error = ref('')
    const activeTab = ref('teams')
    const showRegistrationModal = ref(false)

    // Computed
    const registrationProgress = computed(() => {
      if (!tournament.value) return 0
      const registered = tournament.value.registered_teams_count || 0
      const max = tournament.value.max_teams || 1
      return Math.min((registered / max) * 100, 100)
    })

    const tabs = computed(() => [
      { id: 'teams', name: 'Teams', count: registeredTeams.value.length },
      { id: 'matches', name: 'Matches', count: matches.value.length },
      { id: 'standings', name: 'Standings' },
    ])

    // Check if user can edit tournament
    const canEditTournament = computed(() => {
      return authStore.isAuthenticated && 
             (authStore.isAdmin || tournament.value?.created_by === authStore.user?.id) &&
             !['completed'].includes(tournament.value?.status)
    })

    /**
     * Fetch tournament data
     */
    const fetchTournament = async () => {
      isLoading.value = true
      error.value = ''

      try {
        const response = await tournamentAPI.getById(route.params.id)

        if (apiHelpers.isSuccess(response)) {
          const data = apiHelpers.getData(response)
          tournament.value = data
          registeredTeams.value = data.teams || []
          matches.value = data.matches || []
          standings.value = data.standings || []
        } else {
          error.value = 'Tournament not found'
        }
      } catch (err) {
        console.error('Failed to fetch tournament:', err)
        error.value = apiHelpers.handleError(err)
      } finally {
        isLoading.value = false
      }
    }

    /**
     * Get tab name in Spanish
     */
    const getTabName = (tabId) => {
      const tabNames = {
        teams: 'Equipos',
        matches: 'Partidos',
        standings: 'Tabla'
      }
      return tabNames[tabId] || tabId
    }

    /**
     * Handle successful team registration
     */
    const handleRegistrationSuccess = () => {
      showRegistrationModal.value = false
      fetchTournament() // Refresh data
      window.$notify?.success('Equipo registrado exitosamente!')
    }

    /**
     * Handle tournament update
     */
    const handleTournamentUpdated = (updatedTournament) => {
      tournament.value = updatedTournament
      showEditModal.value = false
      window.$notify?.success('Torneo actualizado exitosamente!')
    }

    /**
     * Format tournament status
     */
    const formatStatus = (status) => {
      const statusMap = {
        draft: 'Borrador',
        registration_open: 'Inscripciones Abiertas',
        in_progress: 'En Progreso',
        completed: 'Completado',
        cancelled: 'Cancelado',
      }
      return statusMap[status] || status
    }

    /**
     * Get status badge CSS classes
     */
    const getStatusBadgeClass = (status) => {
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
     * Format team registration status
     */
    const formatTeamStatus = (status) => {
      const statusMap = {
        pending: 'Pendiente',
        approved: 'Aprobado',
        rejected: 'Rechazado',
      }
      return statusMap[status] || status
    }

    /**
     * Get team status CSS classes
     */
    const getTeamStatusClass = (status) => {
      const classMap = {
        pending: 'bg-warning-100 text-warning-800',
        approved: 'bg-success-100 text-success-800',
        rejected: 'bg-danger-100 text-danger-800',
      }
      return classMap[status] || classMap.pending
    }

    /**
     * Format date for display
     */
    const formatDate = (dateString) => {
      if (!dateString) return 'Por determinar'
      return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
      })
    }

    /**
     * Format match date
     */
    const formatMatchDate = (dateString) => {
      return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
      })
    }

    /**
     * Format match time
     */
    const formatMatchTime = (dateString) => {
      return new Date(dateString).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
      })
    }

    /**
     * Format money amount
     */
    const formatMoney = (amount) => {
      return Number(amount).toLocaleString()
    }

    // Initialize
    onMounted(() => {
      fetchTournament()
    })

    return {
      authStore,
      tournament,
      registeredTeams,
      matches,
      standings,
      isLoading,
      error,
      activeTab,
      showRegistrationModal,
      registrationProgress,
      tabs,
      canEditTournament,
      getTabName,
      handleRegistrationSuccess,
      handleTournamentUpdated,
      formatStatus,
      getStatusBadgeClass,
      formatTournamentType,
      formatTeamStatus,
      getTeamStatusClass,
      formatDate,
      formatMatchDate,
      formatMatchTime,
      formatMoney,
    }
  },
}
</script>